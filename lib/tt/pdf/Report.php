<?php
namespace tt\pdf;

class Report extends \tt\pdf\Pdf{
	private $font = 'kozgopromedium';
	
	/**
	 * 
	 * @param string $template テンプレートPDFファイルパス
	 * @throws \ebi\exception\AccessDeniedException
	 */
	public function __construct($template=null){
		parent::__construct();
		
		if(empty($template)){
			$this->pdf->AddPage('P',\ebi\Calc::get_size_mm('A4'));
			$this->pdf->SetMargins(0,0,0);
		}else{
			if(!is_file($template)){
				throw new \ebi\exception\AccessDeniedException($template.' not found');
			}
			$this->pdf->setSourceFile($template);
			$template_id = $this->pdf->importPage(1);
			$info = $this->pdf->getImportedPageSize($template_id);
			
			$this->pdf->AddPage($info['orientation'],[$info['width'],$info['height']]);
			$this->pdf->useTemplate($template_id);
		}
	}
	
	/**
	 * フォントの設定
	 * @param string $font
	 */
	public function font($font){
		$this->font = $font;
	}
	
	/**
	 * 
	 * @param number $fontsize
	 * @param string $style B: bold, I: italic, U: underline, D: line through, O: overline
	 */
	private function text_style($fontsize=12,$style=''){
		$this->pdf->SetFont($this->font,$style,$fontsize);
	}
	
	private function xy($x,$y,$dx=0,$dy=0){
		if($x < 0){
			$x = $this->pdf->getPageWidth() + $x - $dx;
		}
		if($y < 0){
			$y = $this->pdf->getPageHeight() + $y - $dy;
		}
		return [$x,$y];
	}
	
	
	/**
	 * テキスト
	 * @param number $x mm
	 * @param number $y mm
	 * @param string $text
	 * @param number $size
	 * @param string $style B: bold, I: italic, U: underline, D: line through, O: overline
	 */
	public function text($x,$y,$text,$size=12,$style=''){
		list($x,$y) = $this->xy($x,$y);
		
		if(!empty($size) || !empty($style)){
			$this->text_style($size,$style);
		}
		$this->pdf->SetXY($x,$y);
		$lines = explode(PHP_EOL,$text);
		
		while(!empty($lines)){
			$line = array_shift($lines);
			$this->pdf->SetXY($x,$y);
			$this->pdf->Cell(0,0,$line,0,empty($lines) ? 0 : 1);
			
			$y = $y + $this->pdf->getLastH();
		}
	}
	
	/**
	 * ルーラーの表示
	 */
	public function ruler(){
		$w = $this->pdf->getPageWidth();
		$h = $this->pdf->getPageHeight();
		
		$this->line(0, 0, 0, 5);
		for($mm=0;$mm<=$w;$mm+=1){
			$l = ($mm % 100 === 0) ? 5 : (($mm % 10 === 0 ) ? 3 : 1);
			$this->line($mm, 0, $mm, $l);
		}
		for($mm=0;$mm<=$h;$mm+=1){
			$l = ($mm % 100 === 0) ? 5 : (($mm % 10 === 0 ) ? 3 : 1);
			$this->line(0, $mm, $l, $mm);
		}
	}

	/**
	 * テキストボックス
	 * @param number $x mm
	 * @param number $y mm
	 * @param number $width
	 * @param number $height
	 * @param string $text
	 * @param number $size
	 * @param string $style B: bold, I: italic, U: underline, D: line through, O: overline
	 * @param string $border L: left, T: top, R: right, B: bottom
	 * @param string $align L: left, C: center, R: right align, J: justify
	 * @param number $bordersize mm
	 */
	public function textbox($x,$y,$width,$height,$text,$size='',$style='',$border=0,$align='C',$bordersize=0.2){
		list($x,$y) = $this->xy($x,$y);
		list($width,$height) = $this->xy($width,$height,$x,$y);
		
		if(!empty($size) || !empty($style)){
			$this->text_style($size,$style);
		}
		$this->pdf->SetLineWidth($bordersize);
		$this->pdf->SetXY($x,$y);
		$this->pdf->Cell($width,$height,$text,$border,0,$align);
		$this->resetLineStyle();
	}
	
	/**
	 * 画像
	 * @param number $x mm
	 * @param number $y mm
	 * @param number $width mm 0で自動計算
	 * @param number $height mm 0で自動計算
	 * @param string $img
	 * @throws \ebi\exception\ImageException
	 */
	public function image($x,$y,$width,$height,$img){
		list($x,$y) = $this->xy($x,$y);
		
		$info = \ebi\Image::get_info($img);
		
		if($info['mime'] !== 'image/jpeg' && $info['mime'] !== 'image/png'){
			throw new \ebi\exception\ImageException('image not supported');
		}
		$this->pdf->Image($img,$x,$y,$width,$height,'','','',true);
	}
	
	/**
	 * 線
	 * @param number $sx mm
	 * @param number $sy mm
	 * @param number $ex mm
	 * @param number $ey mm
	 * @param number $bordersize mm
	 */
	public function line($sx,$sy,$ex,$ey,$bordersize=0.2){
		list($sx,$sy) = $this->xy($sx,$sy);
		list($ex,$ey) = $this->xy($ex,$ey);
		
		$this->pdf->SetLineWidth($bordersize);
		$this->pdf->Line($sx,$sy,$ex,$ey);
		$this->resetLineStyle();
	}
	private function resetLineStyle(){
		$this->pdf->SetLineStyle(['width'=>0.2,'color'=>[0,0,0]]);
	}
	
	public function test(){
		$statement = [
			['カレンダー スタンダードA',1000,10],
			['フォトブック　エレガント',1000,10],
			['ポストカード　シンプル',1000,10],
		];
		$this->pdf->SetXY(0,70);
		$this->pdf->SetFont('kozgopromedium','B', 11);
		$this->pdf->setCellPaddings(2,1,2,1);
		$this->pdf->Cell(150, 0,'注文商品',0,0,'L');
		
		$this->pdf->SetFont('kozgopromedium','B', 11);
		$this->pdf->setCellPaddings(2,1,2,1);
		$this->pdf->Cell(50, 0,'価格',0,1,'R');
		
		
		foreach($statement as $item){
			$this->pdf->SetFont('kozgopromedium','', 11);
			$this->pdf->setCellPaddings(2,1,2,1);
			$this->pdf->Cell(150, 0,number_format($item[2]).'点　'.$item[0],0,0,'L');
		
			$this->pdf->SetFont('kozgopromedium','', 11);
			$this->pdf->setCellPaddings(2,1,2,1);
			$this->pdf->Cell(50, 0,'¥'.number_format($item[1] * $item[2]),0,1,'R');
		}
	}
}