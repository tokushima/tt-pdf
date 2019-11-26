<?php
namespace tt\pdf;

class PdfSpliter{
	private $page_no;
	private $width;
	private $height;
	
	
	/**
	 * ページ番号
	 * @return integer
	 */
	public function page_no(){
		return $this->page_no;
	}
	
	/**
	 * ページの幅 (mm)
	 * @return number
	 */
	public function width(){
		return $this->width;
	}
	
	/**
	 * ページの高さ (mm)
	 * @return number
	 */
	public function height(){
		return $this->height;
	}
	
	/**
	 * ページ数を取得
	 * @param string $pdffile
	 * @return integer
	 */
	public static function get_page_count($pdffile){
		$pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
		try{
			return $pdf->setSourceFile($pdffile);
		}catch(\setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException $e){
			if($e->getCode() === \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::ENCRYPTED){
				throw new \tt\pdf\exception\EncryptedPdfDocumentException();
			}
			throw $e;
		}catch(\Exception $e){
			throw new \ebi\exception\AccessDeniedException();
		}
	}
	
	/**
	 * ページ毎に抽出
	 * @param string $pdffile
	 * @param integer $start start page
	 * @param integer $end end page
	 * @param number $pdfversion PDF version
	 * @throws \ebi\exception\AccessDeniedException
	 */
	public static function split($pdffile,$start=1,$end=null,$pdfversion=null){
		$page_cnt = self::get_page_count($pdffile);
		
		if(empty($start)){
			$start = 1;
		}
		if(empty($end) || $page_cnt < $end){
			$end = $page_cnt;
		}
		for($page_no=$start;$page_no<=$end;$page_no++){
			$self = new self();
			$self->pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
			$self->pdf->setSourceFile($pdffile);
			
			$template_id = $self->pdf->importPage($page_no);
			$info = $self->pdf->getImportedPageSize($template_id);
			
			$self->page_no = $page_no;
			$self->width = $info['width'];
			$self->height = $info['height'];
			
			$self->pdf->AddPage($info['orientation'],[$self->width,$self->height]);
			$self->pdf->useTemplate($template_id);
			
			if(!empty($pdfversion)){
				$self->pdf->setPDFVersion($pdfversion);
			}
			yield $self;
		}
	}
	
	/**
	 * ファイルに書き出す
	 * @param string $filename
	 */
	public function write($filename){
		\ebi\Util::mkdir(dirname($filename));
		
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
