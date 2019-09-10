<?php
namespace tt;

class Pdf{
	/**
	 * ページ書き出し
	 * @param string $pdffile
	 * @param integer $page_no
	 * @param string $output
	 * @throws \ebi\exception\InvalidArgumentException
	 */
	public static function split($pdffile,$page_no,$output){
		$pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
		$page_cnt = $pdf->setSourceFile($pdffile);
		
		if($page_no > $page_cnt){
			throw new \ebi\exception\InvalidArgumentException('Maximum page exceeded');
		}
		
		$template_id = $pdf->importPage($page_no);
		$info = $pdf->getImportedPageSize($template_id);
		
		$pdf->AddPage($info['orientation'],[$info['width'],$info['height']]);
		$pdf->useTemplate($template_id);
		$pdf->Output($output,'F');
	}
}
