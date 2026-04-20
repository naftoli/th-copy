<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Increase PHP timeout since we make multiple DocRaptor calls
set_time_limit(300);

require_once __DIR__ . '/../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use setasign\Fpdi\Fpdi;

// ── Helpers ──────────────────────────────────────────────────────────────────

function fixRelativeUrls($html) {
    $base = 'https://mashpia.com';
    $html = preg_replace('/\bsrc="(\/(?!\/)[^"]*)"/',  'src="'  . $base . '$1"', $html);
    $html = preg_replace('/\bhref="(\/(?!\/)[^"]*)"/', 'href="' . $base . '$1"', $html);
    $html = preg_replace("/\\bsrc='(\\/(?!\\/)[^']*)'/",  "src='"  . $base . "$1'", $html);
    $html = preg_replace("/\\bhref='(\\/(?!\\/)[^']*)'/", "href='" . $base . "$1'", $html);
    return $html;
}

function extractHead($html) {
    if (preg_match('/<head[^>]*>(.*?)<\/head>/si', $html, $m)) {
        return '<head>' . $m[1] . '</head>';
    }
    return '<head></head>';
}

/**
 * Split the full page HTML into one chunk per student.
 * The print script separates students with a pipe | character.
 */
function splitIntoChunks($html, $head) {
    $chunks = [];

    if (preg_match('/<body[^>]*>(.*?)<\/body>/si', $html, $m)) {
        $body = $m[1];
    } else {
        $body = $html;
    }

    $parts = explode('|', $body);

    foreach ($parts as $part) {
        $part = trim($part);
        if (empty($part)) continue;
        if (strpos($part, 'userDuch') === false) continue;
        $chunks[] = '<!DOCTYPE html><html>' . $head . '<body>' . $part . '</body></html>';
    }

    return $chunks;
}

// ── PDF generation ───────────────────────────────────────────────────────────

/**
 * Send one student HTML chunk to DocRaptor and return raw PDF binary.
 */
function createDocraptorPdf($html) {
    $docraptor = new DocRaptor\DocApi();
    $docraptor->getConfig()->setUsername("CIrbbDsV2QqOc-ULQnQv");

    $doc = new DocRaptor\Doc();
    $doc->setTest(false);                   // change to true for free watermarked test PDFs
    $doc->setDocumentContent($html);
    $doc->setName(time() . rand(1000, 9999) . ".pdf");
    $doc->setDocumentType("pdf");
    $doc->setJavascript(false);

    $prince_options = new DocRaptor\PrinceOptions();
    $prince_options->setBaseurl("https://mashpia.com");
    $doc->setPrinceOptions($prince_options);

    $response = $docraptor->createDoc($doc);

    if (is_string($response)) {
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

// ── PDF merging ───────────────────────────────────────────────────────────────

/**
 * Merge multiple PDF binaries into one file.
 * Uses FPDI + FPDF (pure PHP). Falls back to pdftk if FPDI unavailable.
 * Returns path to merged file, or null if neither method works.
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

    $mergedPath = $uploadDir . 'merged_' . time() . '.pdf';

    // ── Method 1: FPDI + FPDF (pure PHP) ─────────────────────────────────────
    if (class_exists('\setasign\Fpdi\Fpdi')) {
        try {
            $fpdi = new Fpdi();

            foreach ($tempFiles as $tempFile) {
                $pageCount = $fpdi->setSourceFile($tempFile);
                for ($p = 1; $p <= $pageCount; $p++) {
                    $tplId = $fpdi->importPage($p);
                    $size  = $fpdi->getTemplateSize($tplId);
                    $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                    $fpdi->AddPage($orientation, [$size['width'], $size['height']]);
                    $fpdi->useTemplate($tplId);
                }
            }

            $fpdi->Output($mergedPath, 'F');
            foreach ($tempFiles as $f) { @unlink($f); }

            if (file_exists($mergedPath)) {
                return $mergedPath;
            }
        } catch (\Exception $e) {
            error_log('FPDI merge failed: ' . $e->getMessage());
            // fall through to pdftk
        }
    }

    // ── Method 2: pdftk shell command ─────────────────────────────────────────
    $disabledFunctions = array_map('trim', explode(',', ini_get('disable_functions')));
    if (function_exists('exec') && !in_array('exec', $disabledFunctions)) {
        $fileList   = implode(' ', array_map('escapeshellarg', $tempFiles));
        $cmd        = "pdftk $fileList cat output " . escapeshellarg($mergedPath) . " 2>&1";
        $output     = [];
        $returnCode = 0;
        exec($cmd, $output, $returnCode);
        foreach ($tempFiles as $f) { @unlink($f); }

        if ($returnCode === 0 && file_exists($mergedPath)) {
            return $mergedPath;
        }
        error_log('pdftk merge failed (exit ' . $returnCode . '): ' . implode("\n", $output));
        return null;
    }

    // ── No merge method available ──────────────────────────────────────────────
    error_log('mergePdfs: Neither FPDI nor pdftk available. Sending as separate attachments.');
    foreach ($tempFiles as $f) { @unlink($f); }
    return null;
}

// ── Email ────────────────────────────────────────────────────────────────────

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

    // Clean up after sending
    if ($filePath && file_exists($filePath)) @unlink($filePath);
    foreach ($allFiles as $f) { if (file_exists($f)) @unlink($f); }

    return false;
}

// ── Main ──────────────────────────────────────────────────────────────────────

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

// Generate one PDF per student
$pdfBinaries = [];
$errors      = [];

foreach ($chunks as $i => $chunk) {
    try {
        $pdfBinaries[] = createDocraptorPdf($chunk);
    } catch (\Exception $e) {
        $errors[] = 'Student ' . ($i + 1) . ': ' . $e->getMessage();
    }
}

if (empty($pdfBinaries)) {
    echo json_encode(['success' => false, 'error' => 'All PDF generations failed: ' . implode('; ', $errors), 'message' => null]);
    exit;
}

// Merge all PDFs into one
$mergedPath = mergePdfs($pdfBinaries);

$uploadDir = __DIR__ . '/duch_pdf/';
$allFiles  = [];

if (!$mergedPath) {
    // Merge unavailable — save and attach chunks individually
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
    $count   = count($pdfBinaries);
    $warnMsg = !empty($errors) ? ' (warnings: ' . implode('; ', $errors) . ')' : '';
    echo json_encode([
        'success' => true,
        'error'   => null,
        'message' => "Generated $count PDF(s) and emailed successfully.$warnMsg"
    ]);
}