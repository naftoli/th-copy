<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Increase PHP timeout since we may make multiple DocRaptor calls
set_time_limit(300);

require_once __DIR__ . '/../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function fixRelativeUrls($html) {
    $base = 'https://mashpia.com';
    $html = preg_replace('/\bsrc="(\/(?!\/)[^"]*)"/',  'src="'  . $base . '$1"', $html);
    $html = preg_replace('/\bhref="(\/(?!\/)[^"]*)"/', 'href="' . $base . '$1"', $html);
    $html = preg_replace("/\\bsrc='(\\/(?!\\/)[^']*)'/",  "src='"  . $base . "$1'", $html);
    $html = preg_replace("/\\bhref='(\\/(?!\\/)[^']*)'/", "href='" . $base . "$1'", $html);
    return $html;
}

/**
 * Extract the <head> block from the full HTML so each chunk has proper styles/fonts.
 */
function extractHead($html) {
    if (preg_match('/<head[^>]*>(.*?)<\/head>/si', $html, $m)) {
        return '<head>' . $m[1] . '</head>';
    }
    return '<head></head>';
}

/**
 * Split the full HTML into per-student chunks.
 * The print script separates each student with a pipe | character.
 * Each chunk becomes its own complete HTML document.
 */
function splitIntoChunks($html, $head) {
    $chunks = [];

    // Extract just the body content
    if (preg_match('/<body[^>]*>(.*?)<\/body>/si', $html, $m)) {
        $body = $m[1];
    } else {
        $body = $html;
    }

    // Split on the pipe separator between student records
    $parts = explode('|', $body);

    foreach ($parts as $part) {
        $part = trim($part);
        if (empty($part)) continue;
        // Skip non-student parts (spinner div, grade-list div, main div wrapper, scripts)
        if (strpos($part, 'userDuch') === false) continue;

        // Wrap each student in a full HTML document with shared head
        $chunks[] = '<!DOCTYPE html><html>' . $head . '<body>' . $part . '</body></html>';
    }

    return $chunks;
}

/**
 * Send one HTML chunk to DocRaptor and return the raw PDF binary.
 */
function createDocraptorPdf($html) {
    $docraptor = new DocRaptor\DocApi();
    $docraptor->getConfig()->setUsername("CIrbbDsV2QqOc-ULQnQv");

    $doc = new DocRaptor\Doc();
    $doc->setTest(true);                   // set to true to get free watermarked test PDFs
    $doc->setDocumentContent($html);
    $doc->setName(time() . rand(1000, 9999) . ".pdf");
    $doc->setDocumentType("pdf");
    $doc->setJavascript(false);             // no JS needed for static student reports

    $prince_options = new DocRaptor\PrinceOptions();
    $prince_options->setBaseurl("https://mashpia.com");
    $doc->setPrinceOptions($prince_options);

    $response = $docraptor->createDoc($doc);
    
    // The DocRaptor SDK returns either an object with getData() or a raw string
    // depending on the SDK version and response type
    if (is_string($response)) {
        // Raw binary string returned directly — check it looks like a PDF
        if (substr($response, 0, 4) !== '%PDF') {
            throw new \Exception('DocRaptor returned unexpected content: ' . substr($response, 0, 200));
        }
        return $response;
    }
 
    if (is_object($response) && method_exists($response, 'getData')) {
        return $response->getData();
    }
 
    throw new \Exception('DocRaptor returned unrecognised response type: ' . gettype($response));
}

/**
 * Merge multiple PDF binaries into one using pdftk (available on most Linux servers).
 * Returns the path to the merged file, or null if pdftk is unavailable.
 */
function mergePdfs($pdfBinaries) {
    $uploadDir = __DIR__ . '/duch_pdf/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Write each binary to a temp file
    $tempFiles = [];
    foreach ($pdfBinaries as $i => $binary) {
        $path = $uploadDir . 'chunk_' . time() . '_' . $i . '.pdf';
        file_put_contents($path, $binary);
        $tempFiles[] = $path;
    }

    // Try merging with pdftk
    $mergedPath = $uploadDir . 'merged_' . time() . '.pdf';
    $fileList   = implode(' ', array_map('escapeshellarg', $tempFiles));
    $cmd        = "pdftk $fileList cat output " . escapeshellarg($mergedPath) . " 2>&1";
    exec($cmd, $output, $returnCode);

    // Clean up temp chunk files
    foreach ($tempFiles as $f) {
        @unlink($f);
    }

    if ($returnCode === 0 && file_exists($mergedPath)) {
        return $mergedPath;
    }

    error_log('pdftk merge failed (exit ' . $returnCode . '): ' . implode("\n", $output));
    return null;
}

function emailToOhel($filePath = null, $allFiles = []) {
    $mail = new PHPMailer(true);
    $msg  = "<html><body>
        Please find the attached Duch PDF(s).<br /><br />
        To Unsubscribe please click <a href='http://mashpia.com/unsubscribe.php'>here</a>.<br /><br />
        Click <a href='http://mashpia.com/privacy.html'>here</a> for our Privacy Policy.<br /><br />
        Thank you,<br />Tzivos Hashem Team
    </body></html>";

    try {
        $mail->setFrom('cth@mashpia.com', 'Chayolei Tzivos Hashem');
        $mail->addAddress('naftoli@tzivoshashem.org');
        $mail->addReplyTo('cth@tzivoshashem.org', 'Chayolei Tzivos Hashem');
        $mail->isHTML(true);
        $mail->Subject = 'Duch';
        $mail->Body    = $msg;

        // Attach merged PDF if available, otherwise attach all individual chunks
        if ($filePath && file_exists($filePath)) {
            $mail->addAttachment($filePath);
        } elseif (!empty($allFiles)) {
            foreach ($allFiles as $f) {
                if (file_exists($f)) $mail->addAttachment($f);
            }
        }

        $mail->send();
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }

    // Clean up files after sending
    if ($filePath && file_exists($filePath)) @unlink($filePath);
    foreach ($allFiles as $f) { if (file_exists($f)) @unlink($f); }

    return false;
}

// ── Main ────────────────────────────────────────────────────────────────────

if (!isset($_POST['html'])) {
    echo json_encode(['success' => false, 'error' => 'No HTML received.', 'message' => null]);
    exit;
}

$html = $_POST['html'];
$html = fixRelativeUrls($html);

$head   = extractHead($html);
$chunks = splitIntoChunks($html, $head);

if (empty($chunks)) {
    echo json_encode(['success' => false, 'error' => 'Could not find any student records in the HTML.', 'message' => null]);
    exit;
}

// Generate one PDF per student chunk
$pdfBinaries = [];
$errors      = [];

foreach ($chunks as $i => $chunk) {
    try {
        $pdfBinaries[] = createDocraptorPdf($chunk);
    } catch (Exception $e) {
        $errors[] = 'Student ' . ($i + 1) . ': ' . $e->getMessage();
    }
}

if (empty($pdfBinaries)) {
    echo json_encode(['success' => false, 'error' => 'All PDF generations failed: ' . implode('; ', $errors), 'message' => null]);
    exit;
}

// Merge all PDFs into one file
$mergedPath = mergePdfs($pdfBinaries);

$uploadDir = __DIR__ . '/duch_pdf/';
$allFiles  = [];

if (!$mergedPath) {
    // pdftk unavailable — save chunks individually and attach them all
    foreach ($pdfBinaries as $i => $binary) {
        $path = $uploadDir . 'duch_' . time() . '_' . $i . '.pdf';
        file_put_contents($path, $binary);
        $allFiles[] = $path;
    }
}

$emailError = emailToOhel($mergedPath, $allFiles);

if ($emailError) {
    echo json_encode(['success' => false, 'error' => $emailError, 'message' => null]);
} else {
    $chunkCount = count($pdfBinaries);
    $warnMsg    = !empty($errors) ? ' (warnings: ' . implode('; ', $errors) . ')' : '';
    echo json_encode([
        'success' => true,
        'error'   => null,
        'message' => "Generated $chunkCount PDF(s) and emailed successfully.$warnMsg"
    ]);
}