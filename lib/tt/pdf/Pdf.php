<?php
namespace tt\pdf;

class Pdf{
	protected $pdf;
	
	public function __construct(){
		$this->pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
		
		// 境界線を出さない
		$this->pdf->setPrintHeader(false);
		$this->pdf->setPrintFooter(false);
	}	
	
	protected function before(){
	}
	protected function after(){
	}
	
	/**
	 * ファイルに書き出す
	 * @param string $filename
	 */
	public function write($filename){
		$this->before();
		
		$filename = \ebi\Util::path_absolute(getcwd(), $filename);
		\ebi\Util::mkdir(dirname($filename));
		
		$this->pdf->Output($filename,'F');
		
		$this->after();
	}
	
	/**
	 * 出力
	 * @param string $filename
	 */
	public function output($filename=null){
		$this->before();
		
		if(empty($filename)){
			$filename = date('Ymd_his').'.pdf';
		}
		$this->pdf->Output($filename,'I');
		
		$this->after();
	}
	
	/**
	 * ダウンロード
	 * @param string $filename
	 */
	public function download($filename=null){
		$this->before();
		
		if(empty($filename)){
			$filename = date('Ymd_his').'.pdf';
		}
		$this->pdf->Output($filename,'D');
		
		$this->after();
	}
}