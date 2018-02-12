<?
$content = $_POST['content'];

require_once '../MPDF/mpdf.php';
$m = new mPDF();
$m->WriteHTML($content);
$m->Output('statusReport.pdf', 'F');

/*
require_once '../html_to_pdf.php';

$hp = new HTML_TO_PDF($content);
$hp->saveFile('../pdf/status_report.pdf');
$hp->downloadFile('../pdf/status_report.pdf');
$result = $hp->convertHTML($content);

echo $result;	
/*
require_once '../pdfcrowd/pdfcrowd.php';
try
{   
    // create an API client instance
    $client = new Pdfcrowd("naftolir", "165af2db10b5431b75b4f8ea242356e4");
	
	// convert a web page and store the generated PDF into a $pdf variable
    $pdf = $client->convertHtml($content);
    
    // set HTTP response headers
    header("Content-Type: application/pdf");
    header("Cache-Control: no-cache");
    header("Accept-Ranges: none");
    header("Content-Disposition: attachment; filename=\"status_report.pdf\"");

    // send the generated PDF 
    echo $pdf;
}
catch(PdfcrowdException $why)
{
    echo "Pdfcrowd Error: " . $why;
}
 * 
 */
?>