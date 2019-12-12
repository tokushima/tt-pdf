<?php
namespace tt\pdf;

class Spliter extends \tt\pdf\Pdf{
	private $page;
	private $width;
	private $height;
	
	
	/**
	 * ページ番号
	 * @return integer
	 */
	public function page(){
		return $this->page;
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
	 * 総ページ数を取得
	 * @param string $pdffile
	 * @return integer
	 */
	public static function get_num_pages($pdffile){
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
	 * @throws \ebi\exception\AccessDeniedException
	 */
	public static function split($pdffile,$start=1,$end=null,$pdfversion=null){
		$num_pages = self::get_num_pages($pdffile);
		
		if(empty($start)){
			$start = 1;
		}
		if(empty($end) || $num_pages < $end){
			$end = $num_pages;
		}
		for($page=$start;$page<=$end;$page++){
			$self = new static();
			$self->pdf->setSourceFile($pdffile);
			
			$template_id = $self->pdf->importPage($page);
			$info = $self->pdf->getImportedPageSize($template_id);
			
			$self->page = $page;
			$self->width = $info['width'];
			$self->height = $info['height'];
			
			$self->pdf->AddPage($info['orientation'],[$self->width,$self->height]);
			$self->pdf->useTemplate($template_id);
			
			yield $self;
		}
	}
}
