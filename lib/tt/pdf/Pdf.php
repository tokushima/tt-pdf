<?php
namespace tt\pdf;

class Pdf{
	private $pdf;
	
	public function __construct(){
		$mb_internal_encoding = mb_internal_encoding();
		$this->pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
		mb_internal_encoding($mb_internal_encoding);
		
		$this->pdf->setPageUnit('mm');
		$this->pdf->setPrintHeader(false);
		$this->pdf->setPrintFooter(false);
		$this->pdf->SetMargins(0,0,0);
		$this->pdf->SetAutoPageBreak(false);
		$this->pdf->setFontSubsetting(true);
	}
	
	/**
	 * Defines the author of the document
	 * @param string $author
	 */
	public function set_author($author){
		$this->pdf->SetAuthor($author);
	}
	
	/**
	 * Defines the creator of the document
	 * @param string $creator
	 */
	public function set_creator($creator){
		$this->pdf->SetCreator($creator);
	}
	
	/**
	 * Defines the title of the document
	 * @param string $title
	 */
	public function set_title($title){
		$this->pdf->SetTitle($title);
	}
	/**
	 * Defines the subject of the document
	 * @param string $subject
	 */
	public function set_subject($subject){
		$this->pdf->SetSubject($subject);
	}
	
	
	/**
	 * ページを追加
	 * @param number $width
	 * @param number $height
	 * @return $this
	 */
	public function add_page($width,$height){
		$this->pdf->AddPage(($width > $height) ? 'L' : 'P',[$width,$height]);
		return $this;
	}
	
	private function rotate($x,$y,array $opt){
		if(($opt['angle'] ?? 0) !== 0){
			$this->pdf->StartTransform();
			$this->pdf->Rotate($opt['angle'],$x,$y);
		}
	}
	
	/**
	 * 画像を追加
	 * @param number $x mm
	 * @param number $y mm
	 * @param string $filepath
	 * @param mixed{} $opt
	 * 
	 * opt:
	 *  integer $angle 回転角度
	 *  integer $dpi DPI
	 *  
	 * @throws \ebi\exception\ImageException
	 * @return $this
	 */
	public function add_image($x,$y,$filepath,$opt=[]){
		$info = \ebi\Image::get_info($filepath);
		
		if($info['mime'] !== 'image/jpeg' && $info['mime'] !== 'image/png'){
			throw new \ebi\exception\ImageException('image not supported');
		}
		
		list($x,$y) = $this->xy($x,$y);
		$this->rotate($x, $y, $opt);
		
		$dpi = $opt['dpi'] ?? 72;
		$width = ($info['width'] / $dpi * 25.4);
		$height = ($info['height'] / $dpi * 25.4);
		
		$this->pdf->Image($filepath,$x,$y,$width,$height);
		$this->pdf->StopTransform();
		return $this;
	}
	
	/**
	 * SVGを追加
	 * @param number $x mm
	 * @param number $y mm
	 * @param number $width mm
	 * @param number $height mm
	 * @param string $filepath
	 * @param mixed{} $opt
	 * 
	 * opt:
	 *  integer $angle 回転角度
	 *  
	 * @return $this
	 */
	public function add_svg($x,$y,$width,$height,$filepath,$opt=[]){
		list($x,$y) = $this->xy($x,$y);
		$this->rotate($x, $y, $opt);
		
		$this->pdf->ImageSVG($filepath,$x,$y,$width,$height);
		
		$this->pdf->StopTransform();
		return $this;
	}
	
	private function add_svg_string($x,$y,$width,$height,$svgstring,$opt=[]){
		list($x,$y) = $this->xy($x,$y);
		$this->rotate($x, $y, $opt);
		
		$this->pdf->ImageSVG('@'.$svgstring,$x,$y,$width,$height);
		
		$this->pdf->StopTransform();
		return $this;
	}
	
	/**
	 * PDFを追加
	 * @param number $x mm
	 * @param number $y mm
	 * @param string $filepath
	 * @param mixed{} $opt
	 * 
	 * opt:
	 *  integer $angle 回転角度
	 *  
	 * @throws \ebi\exception\AccessDeniedException
	 * @return $this
	 */
	public function add_pdf($x,$y,$filepath,$opt=[]){
		if(!is_file($filepath)){
			throw new \ebi\exception\AccessDeniedException($filepath.' not found');
		}
		list($x,$y) = $this->xy($x,$y);
		$this->rotate($x, $y, $opt);
		
		$this->pdf->setSourceFile($filepath);
		$this->pdf->useTemplate($this->pdf->importPage(1),$x,$y,$opt['width'] ?? null,$opt['height'] ?? null);
		
		$this->pdf->StopTransform();
		return $this;
	}
	
	/**
	 * 線
	 * @param number $sx mm
	 * @param number $sy mm
	 * @param number $ex mm
	 * @param number $ey mm
	 * @param mixed{} $opt 
	 * 
	 * opt:
	 *  string $border_color 線の色 #FFFFFF
	 *  number $border_width 線の太さ mm
	 *  string $dash 破線パターン 1 or 1,2 mm
	 *  
	 * @return $this
	 */
	public function add_line($sx,$sy,$ex,$ey,$opt=[]){
		list($sx,$sy) = $this->xy($sx,$sy);
		list($ex,$ey) = $this->xy($ex,$ey);
		
		$border_width = $opt['border_width'] ?? 0.2;
		$border_color = $this->color2rgb($opt['border_color'] ?? ($opt['color'] ?? '#000000'));
		$border_dash = $opt['dash'] ?? null;
		
		$this->pdf->SetLineStyle([
			'width'=>$border_width,
			'color'=>$border_color,
			'dash'=>$border_dash,
		]);
		$this->pdf->Line($sx,$sy,$ex,$ey);
		
		// reset
		$this->pdf->SetLineStyle([
			'width'=>0.2,
			'color'=>$this->color2rgb('#000000'),
			'dash'=>0,
		]);
		
		return $this;
	}
	
	/**
	 * 矩形
	 * @param number $x mm
	 * @param number $y mm
	 * @param number $w mm
	 * @param number $h mm
	 * @param mixed{} $opt
	 * 
	 * opt:
	 *  boolean $fill true: 塗りつぶす
	 *  string $color 色 #000000 
	 *  string $border_color 線の色 #FFFFFF
	 *  number $border_width 線の太さ mm
	 *  string $dash 破線パターン 1 or 1,2 mm
	 * 
	 * @return $this
	 */
	public function add_rect($x,$y,$w,$h,$opt=[]){
		$style = ($opt['fill'] ?? false) ? 'F' : 'D';
		$color = $opt['color'] ?? '#000000';
		$color_rgb = $this->color2rgb($color);
		$border_width = $opt['border_width'] ?? null;
		$border_rgb = $this->color2rgb($opt['border_color'] ?? $color);
		$border_dash = $opt['dash'] ?? null;
		
		$border_style = [];
		if($border_width !== null || $style === 'D'){
			$border_style = [
				'all'=>[
					'width'=>$border_width ?? 0.2,
					'color'=>$border_rgb,
					'dash'=>$border_dash,
				],
			];
			
			if($style === 'F'){
				$style = 'FD';
			}
		}
		
		$this->rotate($x, $y, $opt);
		$this->pdf->Rect($x,$y,$w,$h,$style,$border_style,$color_rgb);
		$this->pdf->StopTransform();
		return $this;
	}
	
	/**
	 * 円
	 * @param number $x mm
	 * @param number $y mm
	 * @param number $diameter 直径 mm
	 * @param mixed{} $opt 
	 * 
	 * opt:
	 *  boolean $fill true: 塗りつぶす
	 *  string $color 色 #000000 
	 *  string $border_color 線の色 #FFFFFF
	 *  number $border_width 線の太さ mm
	 *  string $dash 破線パターン 1 or 1,2 mm
	 * 
	 * @return $this
	 */
	public function add_circle($x,$y,$diameter,$opt=[]){
		$style = ($opt['fill'] ?? false) ? 'F' : 'D';
		$color = $opt['color'] ?? '#000000';
		$color_rgb = $this->color2rgb($color);
		$border_width = $opt['border_width'] ?? null;
		$border_rgb = $this->color2rgb($opt['border_color'] ?? $color);
		$border_dash = $opt['dash'] ?? null;
		
		$r = $diameter / 2;
		$x = $x + $r;
		$y = $y + $r;
		
		$border_style = [];
		if($border_width !== null || $style === 'D'){
			$border_style = [
				'width'=>$border_width ?? 0.2,
				'color'=>$border_rgb,
				'dash'=>$border_dash,
			];
			if($style === 'F'){
				$style = 'FD';
			}
		}
		
		$border_style = [
			'width'=>$border_width ?? 0.2,
			'color'=>$border_rgb,
			'dash'=>$border_dash,
		];
		$this->pdf->Ellipse($x, $y, $r,'',0,0,360,$style,$border_style,$color_rgb);
		return $this;
	}
	
	/**
	 * QR Code を追加
	 * @param number $x mm
	 * @param number $y mm
	 * @param number $width mm
	 * @param string $value
	 * @param mixed{} $opt
	 *
	 * opt:
	 *  string $color #000000
	 *  string $bgcolor #FFFFFF
	 *  number $padding (cell)
	 *  string $level L, M, Q, H (error correction level)
	 *  integer $angle 回転角度
	 *  
	 * @return $this
	 */
	public function add_qrcode($x,$y,$width,$value,$opt=[]){
		$type = 'QRCODE';
		$st = [
			'padding'=>($opt['padding']) ?? 'auto'
		];
		if(isset($opt['bgcolor'])){
			$st['bgcolor'] = $this->color2rgb($opt['bgcolor']);
		}
		if(isset($opt['color'])){
			$st['fgcolor'] = $this->color2rgb($opt['color']);
		}
		if(isset($opt['level'])){
			$type = $type.','.$opt['level'];
		}
		
		$this->rotate($x, $y, $opt);
		$this->pdf->write2DBarcode($value,$type,$x,$y,$width,$width,$st);
		$this->pdf->StopTransform();
		
		return $this;
	}
	
	/**
	 * JAN13バーコードを追加
	 * @param number $x mm
	 * @param number $y mm
	 * @param number $width mm
	 * @param number $height mm
	 * @param string $code
	 * @param mixed{} $opt
	 * 
	 * 	string $color #000000
	 * 	number $bar_height バーコードの高さ
	 * 	number $module_width 1モジュールの幅
	 *  boolean $show_text コード文字列を表示する
	 * 	number $font_size フォントサイズ
	 * 	string $font_family フォント名
	 *  integer $angle 回転角度
	 */
	public function add_jan13($x,$y,$width,$height,$code,$opt=[]){
		$this->add_svg_string(
			$x,
			$y,
			$width,
			$height,
			\ebi\Barcode::JAN13($code,$opt),
			$opt
		);
	}
	
	/**
	 * NW-7 (CODABAR)を追加
	 * @param number $x mm
	 * @param number $y mm
	 * @param number $width mm
	 * @param number $height mm
	 * @param string $code
	 * @param mixed{} $opt
	 *
	 * 	string $color #000000
	 * 	number $bar_height バーコードの高さ
	 * 	number $module_width 1モジュールの幅
	 *  boolean $show_text コード文字列を表示する
	 * 	number $font_size フォントサイズ
	 * 	string $font_family フォント名
	 *  integer $angle 回転角度
	 */
	public function add_nw7($x,$y,$width,$height,$code,$opt=[]){
		$this->add_svg_string(
			$x,
			$y,
			$width,
			$height,
			\ebi\Barcode::NW7($code,$opt),
			$opt
		);
	}
	
	/**
	 * ルーラーの追加
	 * @return $this
	 */
	public function add_ruler(){
		$w = $this->pdf->getPageWidth();
		$h = $this->pdf->getPageHeight();
		
		$this->add_line(0, 0, 0, 5);
		for($mm=0;$mm<=$w;$mm+=1){
			$l = ($mm % 100 === 0) ? 5 : (($mm % 10 === 0) ? 3 : (($mm % 5 === 0) ? 2 : 1));
			$this->add_line($mm, 0, $mm, $l);
		}
		for($mm=0;$mm<=$h;$mm+=1){
			$l = ($mm % 100 === 0) ? 5 : (($mm % 10 === 0) ? 3 : (($mm % 5 === 0) ? 2 : 1));
			$this->add_line(0, $mm, $l, $mm);
		}
		return $this;
	}
	
	/**
	 * トンボの追加
	 * @param number $x mm
	 * @param number $y mm
	 * @param number $w mm
	 * @param number $h mm 
	 * @param number $mark 角トンボの長さ mm 
	 * @param number $bleed ドブ幅 mm 
	 * @param boolean $center センタートンボの表示
	 */
	public function add_trim_mark($x,$y,$w,$h,$mark=9,$bleed=3,$center=true){
		$this->add_line($x, $y-$bleed, $x, $y-$bleed-$mark);
		$this->add_line($x, $y-$bleed, $x-$bleed-$mark, $y-$bleed);
		$this->add_line($x-$bleed, $y, $x-$bleed, $y-$bleed-$mark);
		$this->add_line($x-$bleed, $y, $x-$bleed-$mark, $y);
		
		$this->add_line($x+$w, $y-$bleed, $x+$w, $y-$bleed-$mark);
		$this->add_line($x+$w, $y-$bleed, $x+$w+$bleed+$mark, $y-$bleed);
		$this->add_line($x+$w+$bleed, $y, $x+$w+$bleed, $y-$bleed-$mark);
		$this->add_line($x+$w+$bleed, $y, $x+$w+$bleed+$mark, $y);
		
		$this->add_line($x, $y+$h+$bleed, $x, $y+$h+$bleed+$mark);
		$this->add_line($x, $y+$h+$bleed, $x-$bleed-$mark, $y+$h+$bleed);
		$this->add_line($x-$bleed, $y+$h, $x-$bleed, $y+$h+$bleed+$mark);
		$this->add_line($x-$bleed, $y+$h, $x-$bleed-$mark, $y+$h);
		
		$this->add_line($x+$w, $y+$h+$bleed, $x+$w, $y+$h+$bleed+$mark);
		$this->add_line($x+$w, $y+$h+$bleed, $x+$w+$bleed+$mark, $y+$h+$bleed);
		$this->add_line($x+$w+$bleed, $y+$h, $x+$w+$bleed, $y+$h+$bleed+$mark);
		$this->add_line($x+$w+$bleed, $y+$h, $x+$w+$bleed+$mark, $y+$h);
		
		if($center){
			$this->add_line($x-($bleed*2), $y+($h/2)-($h/6), $x-($bleed*2), $y+($h/2)+($h/6));
			$this->add_line($x-($bleed*2)+1, $y+($h/2), $x-($bleed*2)-$bleed, $y+($h/2));
			
			$this->add_line($x+$w+($bleed*2), $y+($h/2)-($h/6), $x+$w+($bleed*2), $y+($h/2)+($h/6));
			$this->add_line($x+$w+($bleed*2)-1, $y+($h/2), $x+$w+($bleed*2)+$bleed, $y+($h/2));
			
			$this->add_line($x+($w/2)-($w/6), $y-($bleed*2), $x+($w/2)+($w/6), $y-($bleed*2));
			$this->add_line($x+($w/2), $y-($bleed*2)+1, $x+($w/2), $y-($bleed*2)-$bleed);
			
			$this->add_line($x+($w/2)-($w/6),$y+$h+($bleed*2),$x+($w/2)+($w/6),$y+$h+($bleed*2));
			$this->add_line($x+($w/2), $y+$h+($bleed*2)-1, $x+($w/2), $y+$h+($bleed*2)+$bleed);
		}
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
	 * カラーモードからRGB（10進数）を返す
	 * @param string $color_code
	 * @return integer[] R,G,B
	 */
	private function color2rgb($color_code){
		if(substr($color_code,0,1) == '#'){
			$color_code = substr($color_code,1);
		}
		if(strlen($color_code) == 6){
			$r = hexdec(substr($color_code,0,2));
			$g = hexdec(substr($color_code,2,2));
			$b = hexdec(substr($color_code,4,2));
		}else{
			$r = hexdec(substr($color_code,0,1));
			$g = hexdec(substr($color_code,1,1));
			$b = hexdec(substr($color_code,2,1));
		}
		return [$r,$g,$b];
	}
	
	/**
	 * テキストボックスの追加
	 * @param number $x mm
	 * @param number $y mm
	 * @param number $width mm
	 * @param number $height mm
	 * @param string $text
	 * @param mixed{} $opt
	 * 
	 * opt:
	 *  integer $align 0: LEFT, 1: CENTER, 2: RIGHT
	 *  integer $valign 0: TOP, 1: MIDDLE, 2: BOTTOM
	 *  string $color #000000
	 *  string $font_family フォントファミリー
	 *  number $font_size フォントサイズ pt
	 *  number $text_spacing 文字間隔 pt
	 *  number $text_leading 行間隔 pt
	 *  integer $angle 回転角度
	 *  
	 * フォントの追加 (埋め込み型):
	 *  > vendor/tecnickcom/tcpdf/tools/tcpdf_addfont.php -t TrueTypeUnicode -f 32 -i *****.ttf
	 *  -t: TrueTypeUnicode, TrueType, Type1, CID0JP, CID0KR, CID0CS, CID0CT
	 * @return $this
	 */
	public function add_textbox($x,$y,$width,$height,$text,$opt=[]){
		list($x,$y) = $this->xy($x,$y);
		list($width,$height) = $this->xy($width,$height,$x,$y);
		$this->rotate($x, $y, $opt);
		
		$font_family = $opt['font_family'] ?? 'kozminproregular';
		$font_size = $opt['font_size'] ?? 8;
		$color = $opt['color'] ?? '#000000';
		$text_spacing = $opt['text_spacing'] ?? 0;
		$text_leading = $opt['text_leading'] ?? $font_size;
		$align = $opt['align'] ?? 0;
		$valign = $opt['valign'] ?? 0;
		
		list($r,$g,$b) = [hexdec(substr($color,1,2)),hexdec(substr($color,3,2)),hexdec(substr($color,5,2))];
		
		$this->pdf->SetFont($font_family,'',$font_size);
		$this->pdf->SetTextColor($r,$g,$b);
		$this->pdf->SetFontSpacing($text_spacing);
		$this->pdf->setCellPaddings(0,0,0,0);
		$this->pdf->setCellMargins(0,0,0,0);
		$this->pdf->setCellHeightRatio($text_leading / $font_size);
		
		$this->pdf->MultiCell(
			$width,
			$height,
			$text,
			false,
			($align == 0) ? 'L' : (($align == 1) ? 'C' : 'R'),
			false,
			1,
			$x,
			$y,
			true,
			0,
			false,
			false,
			$height,
			($valign == 0) ? 'T' : (($valign == 1) ? 'M' : 'B'),
			false
		);
		$this->pdf->StopTransform();
		
		if($opt['border'] ?? false || isset($opt['border_width'])){
			$this->add_rect($x, $y, $width, $height, $opt);
		}
		
		return $this;
	}
	
	/**
	 * ページの幅
	 * @param integer $page
	 * @return number
	 */
	public function get_width($page=null){
		return $this->pdf->getPageWidth($page);
	}
	
	/**
	 * ページの高さ
	 * @param integer $page
	 * @return number
	 */
	public function get_height($page=null){
		return $this->pdf->getPageHeight($page);
	}
	
	/**
	 * ファイルに書き出す
	 * @param string $filename
	 */
	public function write($filename){
		$filename = \ebi\Util::path_absolute(getcwd(), $filename);
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
	
	/**
	 * PDFバージョンを設定する
	 * 
	 * @param number $version
	 * @return $this
	 */
	public function set_version($version){
		$this->pdf->setPDFVersion($version);
		return $this;
	}
	
	/**
	 * 総ページ数を取得
	 * @param string $pdffile
	 * @return integer
	 */
	public static function get_num_pages($pdffile){
		$pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
		return self::set_source($pdf, $pdffile);
	}
	
	/**
	 * ページサイズ mm
	 * @param string $pdffile
	 * @param number $page
	 * @return number{}
	 */
	public static function get_size($pdffile,$page=1){
		$pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
		static::set_source($pdf, $pdffile);
		
		$template_id = $pdf->importPage($page);
		$info = $pdf->getImportedPageSize($template_id);
		
		return [
			'width'=>$info['width'],
			'height'=>$info['height'],
		];
	}
	
	private static function set_source($pdf,$pdffile){
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
			
			$self->add_page($info['width'],$info['height']);
			$self->pdf->useTemplate($template_id);
			
			if(!empty($pdfversion)){
				$self->pdf->setPDFVersion($pdfversion);
			}
			yield $page=>$self;
		}
	}
	
	/**
	 * 利用可能フォントリスト
	 * @return string[]
	 */
	public static function font_list(){
		new \setasign\Fpdi\Tcpdf\Fpdi();
		
		$fonts = [];
		$ref = new \ReflectionClass('Tcpdf');
		foreach(\ebi\Util::ls(dirname($ref->getFileName()).'/fonts') as $file){
			$fonts[preg_replace('/^(.+?)\..+$/','\\1',$file->getFilename())] = true;
		}
		ksort($fonts);
		
		return array_keys($fonts);
	}
}