<?php
namespace tt\pdf;

class Pdf{
	private $pdf;
	private $width;
	private $height;
	private $work;
	private $last_error_file;
	
	public function __construct($width,$height){
		if($width !== __FILE__){
			$this->pdf = static::create_instance();
			$this->pdf->AddPage(($width > $height) ? 'L' : 'P',[$width,$height]);
			
			$this->width = $width;
			$this->height = $height;
		}
	}
	
	/**
	 * 画像を追加
	 * @param number $x mm
	 * @param number $y mm
	 * @param number $dpi
	 * @param string $img
	 * @throws \ebi\exception\ImageException
	 */
	public function add_image($x,$y,$dpi,$img){
		list($x,$y) = $this->xy($x,$y);
		
		$info = \ebi\Image::get_info($img);
		$width = ($info['width'] / $dpi * 25.4);
		$height = ($info['height'] / $dpi * 25.4);
		
		if($info['mime'] !== 'image/jpeg' && $info['mime'] !== 'image/png'){
			throw new \ebi\exception\ImageException('image not supported');
		}
		$this->pdf->Image($img,$x,$y,$width,$height,'','','',true);
	}
	
	/**
	 * SVGを追加
	 * @param number $x mm
	 * @param number $y mm
	 * @param number $width mm
	 * @param number $height mm
	 * @param string $img
	 */
	public function add_svg($x,$y,$width,$height,$img){
		list($x,$y) = $this->xy($x,$y);
		
		$this->pdf->ImageSVG($img,$x,$y,$width,$height);
	}
	
	/**
	 * PDFを追加
	 * @param number $x mm
	 * @param number $y mm
	 * @param string $file
	 * @throws \ebi\exception\AccessDeniedException
	 */
	public function add_pdf($x,$y,$file){
		if(!is_file($file)){
			throw new \ebi\exception\AccessDeniedException($file.' not found');
		}
		$this->pdf->setSourceFile($file);
		$this->pdf->useTemplate($this->pdf->importPage(1),$x,$y);
	}
	
	/**
	 * 線
	 * @param number $sx mm
	 * @param number $sy mm
	 * @param number $ex mm
	 * @param number $ey mm
	 * @param number $bordersize mm
	 */
	public function add_line($sx,$sy,$ex,$ey,$bordersize=0.2){
		list($sx,$sy) = $this->xy($sx,$sy);
		list($ex,$ey) = $this->xy($ex,$ey);
		
		$this->pdf->SetLineWidth($bordersize);
		$this->pdf->Line($sx,$sy,$ex,$ey);
		$this->pdf->SetLineStyle(['width'=>0.2,'color'=>[0,0,0]]);
	}
	
	/**
	 * ルーラーの表示
	 */
	public function ruler(){
		$w = $this->pdf->getPageWidth();
		$h = $this->pdf->getPageHeight();
		
		$this->add_line(0, 0, 0, 5);
		for($mm=0;$mm<=$w;$mm+=1){
			$l = ($mm % 100 === 0) ? 5 : (($mm % 10 === 0) ? 3 : 1);
			$this->add_line($mm, 0, $mm, $l);
		}
		for($mm=0;$mm<=$h;$mm+=1){
			$l = ($mm % 100 === 0) ? 5 : (($mm % 10 === 0 ) ? 3 : 1);
			$this->add_line(0, $mm, $l, $mm);
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
	
	private static function create_instance(){
		$mb_internal_encoding = mb_internal_encoding();
		
		$pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetMargins(0,0,0);
		
		mb_internal_encoding($mb_internal_encoding);
		
		return $pdf;
	}
	
	/**
	 * テキストボックス
	 * @param number $x mm
	 * @param number $y mm
	 * @param number $width mm
	 * @param number $height mm
	 * @param string $text
	 * @param mixed{} $opt 
	 * 
	 * opt:
	 *  align: 0: LEFT, 1: CENTER, 2: RIGHT
	 *  valign: 0: TOP, 1: MIDDLE, 2: BOTTOM
	 *  font_name: フォントファミリー
	 *  font_size: フォントサイズ pt
	 *  color: #000000
	 *  text_spacing: 文字間隔 pt
	 *  text_leading: 行間隔 pt
	 *  angle: 回転角度
	 *  
	 * フォントの追加 (埋め込み型):
	 *  > vendor/tecnickcom/tcpdf/tools/tcpdf_addfont.php -t TrueTypeUnicode -f 32 -i *****.ttf
	 * 
	 */
	public function add_textbox($x,$y,$width,$height,$text,$opt=[]){
		if(!isset($this->work)){
			$this->work = static::create_instance();
		}
		
		list($x,$y) = $this->xy($x,$y);
		list($width,$height) = $this->xy($width,$height,$x,$y);
		$this->work->AddPage(($width > $height) ? 'L' : 'P',[$width,$height]);
		
		$align = $opt['align'] ?? 0;
		$valign = $opt['valign'] ?? 0;
		$font_name = $opt['font_name'] ?? 'kozminproregular';
		$font_size = $opt['font_size'] ?? 8;
		$color = $opt['color'] ?? '#000000';
		$text_spacing = $opt['text_spacing'] ?? 0;
		$text_leading = $opt['text_leading'] ?? 0;
		$angle = $opt['angle'] ?? 0;
		$style = '';
		
		list($r,$g,$b) = [hexdec(substr($color,1,2)),hexdec(substr($color,3,2)),hexdec(substr($color,5,2))];
		
		$this->pdf->setFontSubsetting(true);
		$this->pdf->SetFont($font_name,$style,$font_size);
		$this->pdf->SetTextColor($r,$g,$b);
		$this->pdf->SetFontSpacing($text_spacing);
		$this->work->SetFontSpacing($text_spacing);
		
		if($angle !== 0){
			$this->pdf->StartTransform();
			$this->pdf->Rotate($angle,0,0);
		}
		
		$text_h = 0;
		$lines = [];
		foreach(explode(PHP_EOL,$text) as $line){
			$next = '';
			
			while(true){
				$w = $this->pdf->GetStringWidth($line,$font_name,$style,$font_size);
				
				if($w >= $width){
					$next = mb_substr($line,-1).$next;
					$line = mb_substr($line,0,-1);
				}else{
					$this->work->Cell(0,0,$line);
					$h = $this->work->getLastH() + $text_leading;
					
					if($text_h + $h > $height){
						break;
					}
					$lines[] = [$line,$w,$h];
					$text_h += $h;
					
					if(empty($next)){
						break;
					}
					$line = $next;
					$next = '';
				}
			}
			if(!empty($next)){
				$this->work->Cell(0,0,$next);
				$h = $this->work->getLastH() + $text_leading;
				$w = $this->pdf->GetStringWidth($next,$font_name,$style,$font_size);
				
				if($text_h + $h > $height){
					break;
				}
				$lines[] = [$next,$w,$h];
				$text_h += $h;
			}
		}
		
		$ly = 0;
		if($valign === 1){
			$ly = ($height - $text_h) / 2;
		}else if($valign === 2){
			$ly = ($height - $text_h);
		}
		while(!empty($lines)){
			list($text,$w,$h) = array_shift($lines);
			
			$lx = 0;
			if($align === 1){
				$lx = ($width - $w) / 2;
			}else if($align === 2){
				$lx = ($width - $w) - $text_spacing;
			}
			$this->pdf->SetXY($x + $lx,$y + $ly);
			$this->pdf->Cell(0,0,$text,0,empty($lines) ? 0 : 1);
			
			$y = $y + $h;
		}
		$this->work->deletePage(1);
		$this->pdf->StopTransform();
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
	 * @return \tt\pdf\Pdf
	 */
	public function set_version($version){
		$this->pdf->setPDFVersion($version);
		return $this;
	}
	
	/**
	 * 幅
	 * @return number
	 */
	public function width(){
		return $this->width;
	}

	/**
	 * 高さ
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
		return static::set_source($pdf, $pdffile);
	}
	
	/**
	 * ページサイズ mm
	 * @param string $pdffile
	 * @param number $page
	 * @return array
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
			$pdf = static::create_instance();
			$pdf->setSourceFile($pdffile);
			$template_id = $pdf->importPage($page);
			$info = $pdf->getImportedPageSize($template_id);
			
			$pdf->AddPage($info['orientation'],[$info['width'],$info['height']]);
			$pdf->useTemplate($template_id);
			
			$self = new static(__FILE__,null);
			$self->pdf = $pdf;
			$self->width = $info['width'];
			$self->height = $info['height'];
			
			if(!empty($pdfversion)){
				$self->pdf->setPDFVersion($pdfversion);
			}
			yield $page=>$self;
		}
	}
}