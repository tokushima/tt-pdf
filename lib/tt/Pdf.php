<?php
namespace tt;

class Pdf{
	private $pdf;
	
	
	/**
	 * ページ書き出し
	 * @param string $pdffile
	 * @param integer $page_no
	 * @param string $output
	 * @throws \ebi\exception\InvalidArgumentException
	 */
	public static function split($pdffile,$page_no,$output){
		$self = new self();
		$self->pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
		$page_cnt = $self->pdf->setSourceFile($pdffile);
		
		if($page_no > $page_cnt){
			throw new \ebi\exception\InvalidArgumentException('Maximum page exceeded');
		}
		
		$template_id = $self->pdf->importPage($page_no);
		$info = $self->pdf->getImportedPageSize($template_id);
		
		$self->pdf->AddPage($info['orientation'],[$info['width'],$info['height']]);
		$self->pdf->useTemplate($template_id);
		
		return $self;
	}
	
	public static function html($html){
		$self = new self();
		$self->pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
		$self->pdf->AddPage();
		
		// kozminproregular / kozgopromedium
		$self->pdf->SetFont('kozminproregular','',20);
		$self->pdf->writeHTML($html);
		
		return $self;
	}
	
	/**
	 * ファイルに書き出す
	 * @param string $filename
	 */
	public function write($filename){
		$this->pdf->Output($filename,'F');
	}
	
	/**
	 * 出力
	 * @param string $filename
	 */
	public function output($filename=null){
		if(empty($filename)){
			$filename = date('Ymd_his').'.pdf';
		}
		$this->pdf->Output($filename,'I');
	}
	
	/**
	 * ダウンロード
	 * @param string $filename
	 */
	public function download($filename=null){
		if(empty($filename)){
			$filename = date('Ymd_his').'.pdf';
		}
		$this->pdf->Output($filename,'D');
	}
}
