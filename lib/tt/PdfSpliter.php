<?php
namespace tt;

class PdfSpliter{
	private $page;
	private $orientation;
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
	 * 向き 
	 * @return string
	 */
	public function orientation(){
		return $this->orientation;
	}
	
	/**
	 * ページの幅
	 * @return number
	 */
	public function width(){
		return $this->width;
	}
	
	/**
	 * ページの高さ
	 * @return number
	 */
	public function height(){
		return $this->height;
	}
	
	/**
	 * ページの抜き出し
	 * @param string $pdffile
	 * @throws \ebi\exception\InvalidArgumentException
	 */
	public static function pages($pdffile,$start=1,$end=null){
		$pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
		
		try{
			$page_cnt = $pdf->setSourceFile($pdffile);
		}catch(\Exception $e){
			throw new \ebi\exception\AccessDeniedException($e->getMessage());
		}
		
		if(empty($end)){
			$end = $page_cnt;
		}
		for($page=$start;$page<=$end;$page++){
			$self = new self();
			$self->pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
			$page_cnt = $self->pdf->setSourceFile($pdffile);
			
			$template_id = $self->pdf->importPage($page);
			$info = $self->pdf->getImportedPageSize($template_id);
			
			$self->page = $page;
			$self->orientation = $info['orientation'];
			$self->width = $info['width'];
			$self->height = $info['height'];
			
			$self->pdf->AddPage($self->orientation,[$self->width,$self->height]);
			$self->pdf->useTemplate($template_id);
			
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
