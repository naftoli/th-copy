<?
require 'fpdf17/fpdf.php';
$pdf = new FPDF();

ob_start();
require 'index.php';
$html = ob_get_contents();
ob_end_clean();

$pdf->WriteHTML($html);
$pdf->Output();
exit;
?>