<?php
namespace classes\authorize;

require_once dirname(__DIR__, 3) . '/includes/authorize_constants.php';

use includes\authorize\AuthorizeConstants;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use net\authorize\api\constants\ANetEnvironment;

class Refund {
    public static function extractTransactionData($response) {
        // Try to decode as JSON first
        $jsonData = json_decode($response, true);
        
        if ($jsonData && isset($jsonData['transactionResponse'])) {
            $transResponse = $jsonData['transactionResponse'];
            
            return [
                'type' => 'json',
                'trans_id' => $transResponse['transId'] ?? null,
                'account_number' => $transResponse['accountNumber'] ?? null,
                'account_type' => $transResponse['accountType'] ?? null,
                'auth_code' => $transResponse['authCode'] ?? null,
                'response_code' => $transResponse['responseCode'] ?? null,
                'amount' => null // Amount not typically in response, need to get from your DB or original request
            ];
        }
        
        // Check for pipe-delimited format
        if (is_string($response) && strpos($response, '|') !== false) {
            $parts = explode('|', $response);
            
            // Based on your example: '3|1|6|The credit card number is invalid.||P|0||child registration->8253:8257|96.00|CC|auth_capture||martin|j||j||j|j||||||||||||||||||FE2FF863D1E86ED6A70637A350F32918|||||||||||||XXXXj|||||||||||||||||'
            // Common Authorize.Net pipe format positions:
            // 0: Response Code (1=approved, 2=declined, 3=error)  
            // 1: Response Subcode
            // 2: Response Reason Code  
            // 3: Response Reason Text
            // 4: Authorization Code
            // 5: AVS Result Code
            // 6: Transaction ID
            // 9: Invoice Number/Description
            // 10: Amount
            
            if (count($parts) > 10) {
                return [
                    'type' => 'pipe',
                    'trans_id' => !empty($parts[6]) ? $parts[6] : null,
                    'account_number' => null, // Not typically in pipe format
                    'account_type' => null,
                    'auth_code' => !empty($parts[4]) ? $parts[4] : null,
                    'response_code' => !empty($parts[0]) ? $parts[0] : null,
                    'response_text' => !empty($parts[3]) ? $parts[3] : null,
                    'amount' => !empty($parts[10]) ? $parts[10] : null,
                    'avs_code' => !empty($parts[5]) ? $parts[5] : null
                ];
            }
        }
        
        // If not JSON or pipe, try colon format: "message:authCode:transId:amount"
        if (is_string($response) && strpos($response, ':') !== false) {
            $parts = explode(':', $response);
            
            if (count($parts) >= 3) {
                return [
                    'type' => 'colon',
                    'trans_id' => $parts[2] ?? null,
                    'auth_code' => $parts[1] ?? null,
                    'account_number' => null,
                    'account_type' => null,
                    'response_code' => null,
                    'amount' => isset($parts[3]) ? $parts[3] : null
                ];
            }
        }
        
        return null;
    }

    public static function getTransactionFromDatabase($transactionId, $pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM transaction WHERE id = ?");
            $stmt->execute([$transactionId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public static function processRefundFromDatabase($dbTransactionId, $refundAmount = null, $pdo = null, $sandbox = false, $transId = null, $debug = false) {
        // Your Authorize.Net credentials
        $loginId = AuthorizeConstants::GetMerchantLoginID($sandbox); // true for sandbox
        $transactionKey = AuthorizeConstants::GetMerchantTransactionKey($sandbox); // true for sandbox
        
        if (!$transId) {
            $dbTransaction = self::getTransactionFromDatabase($dbTransactionId, $pdo);
            if (!$dbTransaction) {
                return [
                    'success' => false,
                    'error' => 'Transaction not found in database'
                ];
            }
            
            // Extract transaction data from response
            $transactionData = self::extractTransactionData($dbTransaction['response']);
            if (!$transactionData || !$transactionData['trans_id']) {
                return [
                    'success' => false,
                    'error' => 'Could not extract transaction data from response'
                ];
            }
        } else {
            $transactionData['trans_id'] = $transId;
            $transactionData['type'] = 'string';
        }
        
        // Set up merchant authentication
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($loginId);
        $merchantAuthentication->setTransactionKey($transactionKey);
        
        $originalAmount = null;
        
        // If we have JSON data with card info, use it directly
        if ($transactionData['type'] === 'json' && $transactionData['account_number']) {
            
            // For JSON responses, we might need to get the original amount from your database
            // or make a quick API call just for the amount
            $getDetailsRequest = new AnetAPI\GetTransactionDetailsRequest();
            $getDetailsRequest->setMerchantAuthentication($merchantAuthentication);
            $getDetailsRequest->setTransId($transactionData['trans_id']);
            
            $detailsController = new AnetController\GetTransactionDetailsController($getDetailsRequest);
            $detailsResponse = $detailsController->executeWithApiResponse(AuthorizeConstants::GetApiEndpoint($sandbox));
            
            if ($detailsResponse != null && $detailsResponse->getMessages()->getResultCode() == "Ok") {
                $originalAmount = $detailsResponse->getTransaction()->getAuthAmount();
            } else {
                return [
                    'success' => false,
                    'error' => 'Could not retrieve original transaction amount from Authorize.Net'
                ];
            }
            
            // Set up payment method using data from JSON response
            $creditCard = new AnetAPI\CreditCardType();
            $creditCard->setCardNumber($transactionData['account_number']); // Already masked like XXXX8998
            $creditCard->setExpirationDate("XXXX");
            
        } else {
            // For colon and pipe formats, we need to get full details from Authorize.Net
            $getDetailsRequest = new AnetAPI\GetTransactionDetailsRequest();
            $getDetailsRequest->setMerchantAuthentication($merchantAuthentication);
            $getDetailsRequest->setTransId($transactionData['trans_id']);
            
            $detailsController = new AnetController\GetTransactionDetailsController($getDetailsRequest);
            $detailsResponse = $detailsController->executeWithApiResponse(AuthorizeConstants::GetApiEndpoint($sandbox));
            
            if ($detailsResponse == null || $detailsResponse->getMessages()->getResultCode() != "Ok") {
                return [
                    'success' => false,
                    'error' => 'Could not retrieve original transaction details from Authorize.Net',
                    'authorize_trans_id' => $transactionData['trans_id'],
                    'response_format' => $transactionData['type']
                ];
            }
            
            $originalTransaction = $detailsResponse->getTransaction();
            $originalAmount = $originalTransaction->getAuthAmount();
            
            // Set up payment method using original card info from API
            $creditCard = new AnetAPI\CreditCardType();
            $creditCard->setCardNumber($originalTransaction->getPayment()->getCreditCard()->getCardNumber());
            $creditCard->setExpirationDate("XXXX");
        }
        
        $paymentType = new AnetAPI\PaymentType();
        $paymentType->setCreditCard($creditCard);
        
        // Create the refund transaction request
        $transactionRequest = new AnetAPI\TransactionRequestType();
        $transactionRequest->setTransactionType("refundTransaction");
        $transactionRequest->setAmount($refundAmount ?? $originalAmount);
        $transactionRequest->setPayment($paymentType);
        $transactionRequest->setRefTransId($transactionData['trans_id']);
        
        // Create the API request
        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId("refund_" . time());
        $request->setTransactionRequest($transactionRequest);
        
        // Execute the refund
        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse(AuthorizeConstants::GetApiEndpoint($sandbox));
        if ($debug) {
            echo "<pre>"; print_r($response); echo "</pre>";
            exit;
        }
        
        // Process the response
        if ($response != null && $response->getMessages()->getResultCode() == "Ok") {
            $tresponse = $response->getTransactionResponse();
            
            if ($tresponse != null && $tresponse->getMessages() != null) {
                $result = [
                    'success' => true,
                    'db_transaction_id' => $dbTransactionId,
                    'original_authorize_trans_id' => $transactionData['trans_id'],
                    'refund_transaction_id' => $tresponse->getTransId(),
                    'refund_amount' => $refundAmount ?? $originalAmount,
                    'original_amount' => $originalAmount,
                    'response_code' => $tresponse->getResponseCode(),
                    'message' => $tresponse->getMessages()[0]->getDescription(),
                    'used_json_data' => $transactionData['type'] === 'json'
                ];
                
                // Optionally update your database with refund info
                try {
                    $updateStmt = $pdo->prepare("UPDATE transaction SET refund_trans_id = ?, refund_amount = ?, refund_date = NOW() WHERE id = ?");
                    $updateStmt->execute([
                        $tresponse->getTransId(),
                        $refundAmount ?? $originalAmount,
                        $dbTransactionId
                    ]);
                } catch (PDOException $e) {
                    $result['db_update_error'] = $e->getMessage();
                }
                
                return $result;
            } else {
                // Transaction failed
                $errorText = $tresponse->getErrors() != null 
                    ? $tresponse->getErrors()[0]->getErrorText() 
                    : 'Unknown transaction error';
                
                return [
                    'success' => false,
                    'error' => $errorText,
                    'error_code' => $tresponse->getErrors() != null 
                        ? $tresponse->getErrors()[0]->getErrorCode() 
                        : null,
                    'authorize_trans_id' => $transactionData['trans_id']
                ];
            }
        } else {
            // API call failed
            $errorText = $response != null && $response->getMessages() != null
                ? $response->getMessages()->getMessage()[0]->getText()
                : 'API call failed';
                
            return [
                'success' => false,
                'error' => $errorText,
                'authorize_trans_id' => $transactionData['trans_id']
            ];
        }
    }

    // Test the extraction function
    public static function testExtraction() {
        // Test JSON format
        $jsonResponse = '{"transactionResponse":{"responseCode":"1","authCode":"123196","avsResultCode":"P","cvvResultCode":"","cavvResultCode":"","transId":"81181116636","refTransID":"","transHash":"","testRequest":"0","accountNumber":"XXXX8998","accountType":"Visa","messages":[{"code":"1","description":"This transaction has been approved."}],"transHashSha2":"","profile":{"customerProfileId":"1519789911","customerPaymentProfileId":"716666891"},"SupplementalDataQualificationIndicator":3,"networkTransId":"305237580016463"},"refId":"1756138000","messages":{"resultCode":"Ok","message":[{"code":"I00001","text":"Successful."}]}}';
        
        $jsonData = self::extractTransactionData($jsonResponse);
        echo "JSON format test:\n";
        echo "  Transaction ID: " . $jsonData['trans_id'] . "\n";
        echo "  Account Number: " . $jsonData['account_number'] . "\n";
        echo "  Account Type: " . $jsonData['account_type'] . "\n";
        echo "  Auth Code: " . $jsonData['auth_code'] . "\n\n";
        
        // Test string format
        $stringResponse = "This transaction has been approved.:111466:42897152076:10.00";
        $stringData = self::extractTransactionData($stringResponse);
        echo "String format test:\n";
        echo "  Transaction ID: " . $stringData['trans_id'] . "\n";
        echo "  Auth Code: " . $stringData['auth_code'] . "\n";
        echo "  Amount: " . $stringData['amount'] . "\n";
    }
}
?>