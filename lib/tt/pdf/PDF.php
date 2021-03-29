<?php
namespace tt\pdf;

class PDF{
	private $lib;
	
	private static function libtype(){
		/**
		 * 利用するライブラリ
		 * @param string $libtype tcpdf or pdflib
		 */
		$libtype = strtolower(\ebi\Conf::get('libtype','tcpdf'));
		
		if($libtype !== 'tcpdf' && $libtype !== 'pdflib'){
			throw new \ebi\exception\InvalidConfigException();
		}
		return $libtype;
	}
	
	function __construct($pdfversion=null){
		$this->lib = (self::libtype() === 'pdflib') ? 
			new \tt\pdf\PDFlib($pdfversion) :
			new \tt\pdf\Tcpdf($pdfversion);
	}
	
	/**
	 * ライセンス
	 * @return string
	 */
	public static function get_license(){
		/**
		 * @param string ライセンス
		 */
		$license = \ebi\Conf::get('license');
		return $license;
	}
	
	/**
	 * #000000をK100とする
	 * @param boolean $boolean
	 * @return $this
	 */
	public function K100($boolean){
		$this->lib->K100($boolean);
		return $this;
	}
	/**
	 * フォントを追加する
	 * @param string $fontfile フォントファイル
	 * @param string $alias
	 *
	 * @return $this
	 */
	public function add_font($fontfile,$alias=null){
		$this->lib->add_font($fontfile,$alias);
		return $this;
	}
	/**
	 * Defines the author of the document
	 * @param string $author
	 * @return $this
	 */
	public function set_author($author){
		$this->lib->set_author($author);
		return $this;
	}
	
	/**
	 * Defines the creator of the document
	 * @param string $creator
	 * @return $this
	 */
	public function set_creator($creator){
		$this->lib->set_creator($creator);
		return $this;
	}
	
	/**
	 * Defines the title of the document
	 * @param string $title
	 * @return $this
	 */
	public function set_title($title){
		$this->lib->set_title($title);
		return $this;
	}
	/**
	 * Defines the subject of the document
	 * @param string $subject
	 * @return $this
	 */
	public function set_subject($subject){
		$this->lib->set_subject($subject);
		return $this;
	}
	/**
	 * ページを追加
	 * @param float $width mm
	 * @param float $height mm
	 * @return $this
	 */
	public function add_page($width,$height){
		$this->lib->add_page($width, $height);
		return $this;
	}
	
	/**
	 * 画像を追加
	 * @param float $x mm
	 * @param float $y mm
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
		$this->lib->add_image($x, $y, $filepath, $opt);
		return $this;
	}
	/**
	 * SVGを追加
	 * @param float $x mm
	 * @param float $y mm
	 * @param float $width mm
	 * @param float $height mm
	 * @param string $filepath
	 * @param mixed{} $opt
	 *
	 * opt:
	 *  integer $angle 回転角度
	 *
	 * @return $this
	 */
	public function add_svg($x,$y,$width,$height,$filepath,$opt=[]){
		$this->lib->add_svg($x, $y, $width, $height, $filepath, $opt);
		return $this;
	}
	
	/**
	 * PDFを追加
	 * @param float $x mm
	 * @param float $y mm
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
		$this->lib->add_pdf($x, $y, $filepath, $opt);
	}
	
	/**
	 * 線
	 * @param float $sx mm
	 * @param float $sy mm
	 * @param float $ex mm
	 * @param float $ey mm
	 * @param mixed{} $opt
	 *
	 * opt:
	 *  string $border_color 線の色 #FFFFFF
	 *  float $border_width 線の太さ mm
	 *
	 * @return $this
	 */
	public function add_line($sx,$sy,$ex,$ey,$opt=[]){
		$this->lib->add_line($sx, $sy, $ex, $ey, $opt);
	}
	
	/**
	 * 矩形
	 * @param float $x mm
	 * @param float $y mm
	 * @param float $width mm
	 * @param float $height mm
	 * @param mixed{} $opt
	 *
	 * opt:
	 *  boolean $fill true: 塗りつぶす
	 *  string $color 色 #000000
	 *  string $border_color 線の色 #FFFFFF
	 *  float $border_width 線の太さ mm
	 *
	 * @return $this
	 */
	public function add_rect($x,$y,$width,$height,$opt=[]){
		$this->lib->add_rect($x, $y, $width, $height, $opt);
	}
	
	/**
	 * 円
	 * @param float $x mm
	 * @param float $y mm
	 * @param float $diameter 直径 mm
	 * @param mixed{} $opt
	 *
	 * opt:
	 *  boolean $fill true: 塗りつぶす
	 *  string $color 色 #000000
	 *  string $border_color 線の色 #FFFFFF
	 *  float $border_width 線の太さ mm
	 *
	 * @return $this
	 */
	public function add_circle($x,$y,$diameter,$opt=[]){
		$this->lib->add_circle($x, $y, $diameter, $opt);
		return $this;
	}

	/**
	 * QR Code を追加
	 * @param float $x mm
	 * @param float $y mm
	 * @param float $size mm
	 * @param string $value
	 * @param mixed{} $opt
	 *
	 * opt:
	 *  string $color #000000
	 *  string $bgcolor #FFFFFF
	 *  float $padding (cell)
	 *  string $level L, M, Q, H (error correction level)
	 *  integer $angle 回転角度
	 *
	 * @return $this
	 */
	public function add_qrcode($x,$y,$size,$value,$opt=[]){
		$padding = $opt['padding'] ?? 4;
		
		$color_func = function($color_code){
			if(substr($color_code,0,1) == '#'){
				$color_code = substr($color_code,1);
			}
			if($this->lib->is_K100() && ($color_code === '000000')){
				return new \BaconQrCode\Renderer\Color\Cmyk(0, 0, 0, 1);
			}
			$r = hexdec(substr($color_code,0,2));
			$g = hexdec(substr($color_code,2,2));
			$b = hexdec(substr($color_code,4,2));
			
			return new \BaconQrCode\Renderer\Color\Rgb($r, $g, $b);
		};
		
		$renderer = new \BaconQrCode\Renderer\ImageRenderer(
			new \BaconQrCode\Renderer\RendererStyle\RendererStyle(
				400,
				$padding,
				null,
				null,
				\BaconQrCode\Renderer\RendererStyle\Fill::uniformColor(
					$color_func($opt['bgcolor'] ?? 'FFFFFF'),
					$color_func($opt['color'] ?? '000000')
				)
			),
			new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
		);
		
		$writer = new \BaconQrCode\Writer($renderer);
		$writer->writeString(
			$value,
			\BaconQrCode\Encoder\Encoder::DEFAULT_BYTE_MODE_ECODING,
			null,
			\BaconQrCode\Common\ErrorCorrectionLevel::L()
		);
		
		$this->lib->add_svg_string(
			$x,
			$y,
			$size,
			$size,
			$writer->writeString($value),
			$opt
		);
		return $this;
	}
	
	/**
	 * JAN13バーコードを追加
	 * @param float $x mm
	 * @param float $y mm
	 * @param float $width mm
	 * @param float $height mm
	 * @param string $code
	 * @param mixed{} $opt
	 *
	 * 	string $color #000000
	 * 	float $bar_height バーコードの高さ
	 * 	float $module_width 1モジュールの幅
	 *  boolean $show_text コード文字列を表示する
	 * 	float $font_size フォントサイズ
	 * 	string $font_family フォント名
	 *  integer $angle 回転角度
	 */
	public function add_jan13($x,$y,$width,$height,$code,$opt=[]){
		$this->lib->add_svg_string(
			$x,
			$y,
			$width,
			$height,
			\ebi\Barcode::JAN13($code,$opt),
			$opt
		);
		return $this;
	}
	
	/**
	 * NW-7 (CODABAR)を追加
	 * @param float $x mm
	 * @param float $y mm
	 * @param float $width mm
	 * @param float $height mm
	 * @param string $code
	 * @param mixed{} $opt
	 *
	 * 	string $color #000000
	 * 	float $bar_height バーコードの高さ
	 * 	float $module_width 1モジュールの幅
	 *  boolean $show_text コード文字列を表示する
	 * 	float $font_size フォントサイズ
	 * 	string $font_family フォント名
	 *  integer $angle 回転角度
	 *
	 * @return $this
	 */
	public function add_nw7($x,$y,$width,$height,$code,$opt=[]){
		$this->lib->add_svg_string(
			$x,
			$y,
			$width,
			$height,
			\ebi\Barcode::NW7($code,$opt),
			$opt
		);
		return $this;
	}
	/**
	 * ルーラーの追加
	 * @return $this
	 */
	public function add_ruler(){
		$this->lib->add_ruler();
		return $this;
	}
	
	/**
	 * トンボの追加
	 * @param float $x mm
	 * @param float $y mm
	 * @param float $w mm
	 * @param float $h mm
	 * @param array $opt
	 * 
	 * opt:
	 *  float $size mm
	 *  boolean $center センタートンボの描画
	 *  boolean $inner 内トンボ上を描画
	 * 
	 * @return $this
	 */
	public function add_trim_mark($x,$y,$w,$h,$opt=[]){
		$s = $opt['size'] ?? 3;
		$i = ($opt['inner'] ?? true) ? 0 : $s;
		$lopt = ['color'=>($opt['color'] ?? '#000000')];

		$this->add_line($x-$s, $y, $x+$s-$i, $y, $lopt);
		$this->add_line($x, $y-$s, $x, $y+$s-$i, $lopt);
		$this->add_line($x, $y+$s, $x-$s, $y+$s, $lopt);
		$this->add_line($x+$s, $y, $x+$s, $y-$s, $lopt);

		$this->add_line($w+$x+$s, $y, $w+$x-$s+$i, $y, $lopt);
		$this->add_line($w+$x, $y-$s, $w+$x, $y+$s-$i, $lopt);
		$this->add_line($w+$x-$s, $y, $w+$x-$s, $y-$s, $lopt);
		$this->add_line($w+$x, $y+$s, $w+$x+$s, $y+$s, $lopt);
		
		$this->add_line($x-$s, $h+$y, $x+$s-$i, $h+$y, $lopt);
		$this->add_line($x, $h+$y-$s+$i, $x, $h+$y+$s, $lopt);
		$this->add_line($x, $h+$y-$s, $x-$s, $h+$y-$s, $lopt);
		$this->add_line($x+$s, $h+$y, $x+$s, $h+$y+$s, $lopt);

		$this->add_line($w+$x+$s, $h+$y, $w+$x-$s+$i, $h+$y, $lopt);
		$this->add_line($w+$x, $h+$y+$s, $w+$x, $h+$y-$s+$i, $lopt);
		$this->add_line($w+$x, $h+$y-$s, $w+$x+$s, $h+$y-$s, $lopt);
		$this->add_line($w+$x-$s, $h+$y, $w+$x-$s, $h+$y+$s, $lopt);
		
		if($opt['center'] ?? false){
			$this->add_line($x-($s*2), $y+($h/2)-($h/6), $x-($s*2), $y+($h/2)+($h/6), $lopt);
			$this->add_line($x-($s*2)+1, $y+($h/2), $x-($s*2)-$s, $y+($h/2), $lopt);
			
			$this->add_line($x+$w+($s*2), $y+($h/2)-($h/6), $x+$w+($s*2), $y+($h/2)+($h/6), $lopt);
			$this->add_line($x+$w+($s*2)-1, $y+($h/2), $x+$w+($s*2)+$s, $y+($h/2), $lopt);
			
			$this->add_line($x+($w/2)-($w/6), $y-($s*2), $x+($w/2)+($w/6), $y-($s*2), $lopt);
			$this->add_line($x+($w/2), $y-($s*2)+1, $x+($w/2), $y-($s*2)-$s, $lopt);
			
			$this->add_line($x+($w/2)-($w/6),$y+$h+($s*2),$x+($w/2)+($w/6),$y+$h+($s*2), $lopt);
			$this->add_line($x+($w/2), $y+$h+($s*2)-1, $x+($w/2), $y+$h+($s*2)+$s, $lopt);
		}
		return $this;
	}
	
	/**
	 * テキストボックスの追加
	 * @param float $x mm
	 * @param float $y mm
	 * @param float $width mm
	 * @param float $height mm
	 * @param string $text
	 * @param mixed{} $opt
	 *
	 * opt:
	 *  integer $align 0: LEFT, 1: CENTER, 2: RIGHT
	 *  integer $valign 0: TOP, 1: MIDDLE, 2: BOTTOM
	 *  string $color #000000
	 *  string $font_family フォントファミリー
	 *  float $font_size フォントサイズ pt
	 *  float $text_spacing 文字間隔 pt
	 *  float $text_leading 行間隔 pt
	 *  integer $angle 回転角度
	 *
	 * @return $this
	 */
	public function add_textbox($x,$y,$width,$height,$text,$opt=[]){
		$this->lib->add_textbox($x, $y, $width, $height, $text, $opt);
		return $this;
	}
	
	/**
	 * ファイルに書き出す
	 * @param string $filename
	 */
	public function write($filename){
		$this->lib->write($filename);
	}
	
	/**
	 * 出力
	 * @param string $filename
	 */
	public function output($filename=null){
		$this->lib->output($filename);
	}
	/**
	 * ダウンロード
	 * @param string $filename
	 */
	public function download($filename=null){
		$this->lib->download($filename);
	}
	
	/**
	 * ページサイズ mm
	 * @param string $pdffile
	 * @return array [page=>[width,height]]
	 */
	public static function get_page_size($pdffile){
		return (self::libtype() === 'pdflib') ? 
			\tt\pdf\PDFlib::get_page_size($pdffile) :
			\tt\pdf\Tcpdf::get_page_size($pdffile);
	}
	
	/**
	 * ページ毎に抽出
	 * @param string $pdffile
	 * @param integer $start start page
	 * @param integer $end end page
	 * @throws \ebi\exception\AccessDeniedException
	 */
	public static function split($pdffile,$start=1,$end=null,$pdfversion=null){
		return (self::libtype() === 'pdflib') ?
			\tt\pdf\PDFlib::split($pdffile,$start,$end,$pdfversion) :
			\tt\pdf\Tcpdf::split($pdffile,$start,$end,$pdfversion);
	}
}