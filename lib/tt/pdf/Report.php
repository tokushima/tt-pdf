<?php
namespace tt\pdf;

class Report extends \tt\pdf\Pdf{
	public function __construct(){
		parent::__construct();
		
		$this->pdf->AddPage('P',\ebi\Calc::get_size_mm('A4'));
		$this->pdf->SetMargins(0,0,0);
		$this->text_style();
	}
	
	public function text_style($fontsize=12,$style='',$font='kozgopromedium'){
		$this->pdf->SetFont($font,$style,$fontsize);
	}
	
	public function text($x,$y,$text){
		$this->pdf->SetXY($x,$y);
		$lines = explode(PHP_EOL,$text);
		
		while(!empty($lines)){
			$line = array_shift($lines);
			$this->pdf->Cell(0,0,$line,0,empty($lines) ? 0 : 1);
		}
		
	}
	
	public function textbox($x,$y,$width,$height,$text,$border=0,$aling='L'){
		$this->pdf->SetXY($x,$y);
		$this->pdf->Cell($width,$height,$text,$border,0,$aling);
	}
	
	public function image($x,$y,$width,$height,$img){
		$info = \ebi\Image::get_info($img);
		
		if($info['mime'] !== 'image/jpeg' && $info['mime'] !== 'image/png'){
			throw new \ebi\exception\ImageException('image not supported');
		}
		if($info['orientation'] == \ebi\Image::ORIENTATION_LANDSCAPE){
			list($w,$h) = [\ebi\Calc::mm2px($width,72),null];
		}else{
			list($w,$h) = [null,\ebi\Calc::mm2px($height,72)];
		}
		$this->pdf->Image($img,$x,$y,$w,$h);
	}
	
	public function line($sx,$sy,$ex,$ey){
		$this->pdf->Line($sx,$sy,$ex,$ey);
	}
}