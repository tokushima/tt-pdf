<?php
namespace tt\pdf;
/**
 * PDFlib
 * @author tokushima
 * 
 * @see http://www.pdflib.jp/product/download/pdflib/
 * @see https://www.infotek.co.jp/pdflib/pdflib_info.html
 * @see https://www.infotek.co.jp/pdflib/pdflib/pdflib_cookbook.html
 */
class PDFlib{
	static private $pvfkeys = 0;
	
	private $pdf;
	private $pages = 0;
	private $current_page_size = [0,0];
	private $K100 = false;
	private $debug = false;
	private $load_pdf = [];
	
	/**
	 * 
	 * @param number $pdfversion 作成するPDFバージョン
	 * @param boolean $compress PDFオブジェクトを圧縮する （PDF-1.5以降のバージョン）
	 * @throws \LogicException
	 */
	public function __construct($pdfversion=null,$compress=false){
		$this->pdf = new \PDFlib();
		
		/**
		 * @param string ライセンス
		 */
		$license = \ebi\Conf::get('license',\tt\pdf\PDF::get_license());
		if(!empty($license)){
			$this->pdf->set_option('license='.$license);
		}
		$this->pdf->set_option('stringformat=utf8'); // 文字列をUTF-8で渡すことをPDFlib に知らせる
		
		$opt = [];
		if(!empty($pdfversion)){
			$opt[] = 'compatibility='.$pdfversion;
		}
		if(!$compress){
			$opt[] = 'objectstreams=none';
		}
		
		if($this->pdf->begin_document('',implode(' ',$opt)) == 0){
			throw new \LogicException($this->pdf->get_errmsg());
		}
	}
	
	/**
	 * #000000をK100とする
	 * @param boolean $boolean
	 * @return $this
	 */
	public function K100($boolean){
		$this->K100 = (boolean)$boolean;
		return $this;
	}
	
	/**
	 * フォントを追加する
	 * @param string $fontfile
	 * @param string $alias
	 * 
	 * @return $this
	 */
	public function add_font($fontfile,$alias=null){
		$alias = empty($alias) ? preg_replace('/^(.+?)\.$/','\\1',$fontfile) : $alias;
		$this->pdf->set_option(sprintf('FontOutline={%s=%s}',$alias,$fontfile));
		
		return $this;
	}
	
	/**
	 *　PDFlibでの扱いはptなのでmmからptに計算する
	 * @return number[]
	 */
	private function mm2pt($arg){
		$result = [];
		foreach((is_array($arg) ? $arg : func_get_args()) as $mm){
			$result[] = \ebi\Calc::mm2pt((float)$mm);
		}
		return $result;
	}
	
	/**
	 * Defines the author of the document
	 * @param string $author
	 * @return $this
	 */
	public function set_author($author){
		$this->pdf->set_info('Author',$author);
		return $this;
	}
	
	/**
	 * Defines the creator of the document
	 * @param string $creator
	 * @return $this
	 */
	public function set_creator($creator){
		$this->pdf->set_info('Creator',$creator);
		return $this;
	}
	
	/**
	 * Defines the title of the document
	 * @param string $title
	 * @return $this
	 */
	public function set_title($title){
		$this->pdf->set_info('Title',$title);
		return $this;
		
	}
	/**
	 * Defines the subject of the document
	 * @param string $subject
	 * @return $this
	 */
	public function set_subject($subject){
		$this->pdf->set_info('Subject',$subject);
		return $this;
	}
	
	/**
	 * ページを追加
	 * @param number $width
	 * @param number $height
	 * @return $this
	 */
	public function add_page($width,$height){
		list($width,$height) = $this->mm2pt($width,$height);
		
		$this->end_page();
		$this->pdf->begin_page_ext($width, $height,'');
		
		$this->current_page_size = [$width,$height];
		$this->pages++;
		
		return $this;
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
		list($x,$y) = $this->mm2pt($x,$y);
		$info = \ebi\Image::get_info($filepath);
		
		if($info['mime'] !== 'image/jpeg' && $info['mime'] !== 'image/png'){
			throw new \ebi\exception\ImageException('image not supported');
		}
		$image = $this->pdf->load_image('auto',$filepath,'');
		
		$dpi = $opt['dpi'] ?? 72;
		$angle = $opt['rotate'] ?? ($opt['angle'] ?? 0);
		$width = \ebi\Calc::px2pt($info['width'],$dpi);
		$height = \ebi\Calc::px2pt($info['height'],$dpi);
		
		$image_opt = sprintf(
			'rotate=%s '.
			'dpi=%s',
			$this->rotate2world($angle),
			$dpi
		);
		
		list($disp_x,$disp_y) = $this->disp($x, $y, $width, $height, $angle);
		$this->pdf->fit_image($image,$disp_x,$disp_y,$image_opt);
		
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
		list($x,$y,$width,$height) = $this->mm2pt($x,$y,$width,$height);
		
		$image = $this->pdf->load_graphics('auto',$filepath,'');
		
		$angle = $opt['rotate'] ?? ($opt['angle'] ?? 0);
		
		$image_opt = sprintf(
			'rotate=%s '.
			'boxsize={%s %s} '.
			'position=center '.
			'fitmethod=meet',
			$this->rotate2world($angle),
			$width,$height
		);
		
		list($disp_x,$disp_y) = $this->disp($x, $y, $width, $height, $angle);
		$this->pdf->fit_graphics($image,$disp_x,$disp_y,$image_opt);
		$this->pdf->close_graphics($image);
		
		return $this;
	}
	private function add_svg_string($x,$y,$width,$height,$svgstring,$opt=[]){
		list($x,$y,$width,$height) = $this->mm2pt($x,$y,$width,$height);
		
		$pvf_iamge = 'pvf/image_'.self::$pvfkeys++;
		$this->pdf->create_pvf($pvf_iamge,$svgstring,'');
		$image = $this->pdf->load_graphics('auto',$pvf_iamge,'');
		
		$angle = $opt['rotate'] ?? ($opt['angle'] ?? 0);
		
		$image_opt = sprintf(
			'rotate=%s '.
			'boxsize={%s %s} '.
			'position=center '.
			'fitmethod=meet',
			$this->rotate2world($angle),
			$width,$height
		);
		
		list($disp_x,$disp_y) = $this->disp($x, $y, $width, $height, $angle);
		$this->pdf->fit_graphics($image,$disp_x,$disp_y,$image_opt);
		$this->pdf->close_graphics($image);
		
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
	 *  number $scale 拡大率
	 *  integer $page_no 追加するページ番号
	 *
	 * @throws \ebi\exception\AccessDeniedException
	 * @return $this
	 */
	public function add_pdf($x,$y,$filepath,$opt=[]){
		if(!is_file($filepath)){
			throw new \ebi\exception\AccessDeniedException($filepath.' not found');
		}
		list($x,$y) = $this->mm2pt($x,$y);
		
		$angle = $opt['rotate'] ?? ($opt['angle'] ?? 0);
		$scale = $opt['scale'] ?? 0;
		$page_no = $opt['page_no'] ?? 1;

		$doc_id = $this->load_pdf($filepath);
		$image = $this->pdf->open_pdi_page($doc_id,$page_no,'');
		
		$width_pt = $this->pdf->info_pdi_page($image,'width','');
		$height_pt = $this->pdf->info_pdi_page($image,'height','');
		
		$image_opt = '';
		if(!empty($angle)){
			$image_opt = sprintf('rotate=%s ',$this->rotate2world($angle));
		}
		if(!empty($scale)){
			$image_opt = sprintf('scale=%s ',$scale);
			$width_pt *= $scale;
			$height_pt *= $scale;
		}
		
		list($disp_x,$disp_y) = $this->disp($x, $y, $width_pt, $height_pt, $angle);
		$this->pdf->fit_pdi_page($image,$disp_x,$disp_y,$image_opt);
		$this->pdf->close_pdi_page($image);
		
		return $this;
	}
	
	private function load_pdf($filepath){
		if(!is_file($filepath)){
			throw new \ebi\exception\AccessDeniedException();
		}
		
		$id = null;
		if(isset($this->load_pdf[$filepath])){
			$id = $this->load_pdf[$filepath];
		}else{
			$id = $this->pdf->open_pdi_document($filepath,'');
			$this->load_pdf[$filepath] = $id;
		}
		return $this->load_pdf[$filepath];
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
	 *  number[] $dash 点線の長さ [5,2] mm
	 *
	 * @return $this
	 */
	public function add_line($sx,$sy,$ex,$ey,$opt=[]){
		list($sx,$sy,$ex,$ey) = $this->mm2pt($sx,$sy,$ex,$ey);
		
		$border_width = $opt['border_width'] ?? null;
		$border_color = $this->color_val($opt['border_color'] ?? ($opt['color'] ?? '#000000'));
		
		$this->pdf->save();
		
		$this->pdf->setcolor('fillstroke',$border_color[0],$border_color[1],$border_color[2],$border_color[3],$border_color[4] ?? 0);
		$this->pdf->setlinewidth(\ebi\Calc::mm2pt($border_width ?? 0.2));

		if(isset($opt['dash']) && is_array($opt['dash'])){
			$this->pdf->set_graphics_option('dasharray={'.implode(' ',$this->mm2pt($opt['dash'])).'}');
		}
		
		$this->pdf->moveto($sx,$this->current_page_size[1] - $sy);
		$this->pdf->lineto($ex,$this->current_page_size[1] - $ey);
		$this->pdf->stroke();
		
		$this->pdf->restore();
		
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
	 *
	 * @return $this
	 */
	public function add_rect($x,$y,$width,$height,$opt=[]){
		list($x,$y,$width,$height) = $this->mm2pt($x,$y,$width,$height);
		
		$style = ($opt['fill'] ?? false) ? 'F' : 'D';
		$color = $opt['color'] ?? '#000000';
		$border_width = $opt['border_width'] ?? null;
		$border_color = $this->color_val($opt['border_color'] ?? $color);
		
		$this->pdf->save();
		
		if($border_width !== null || $style === 'D'){
			$border_width = ($border_width === null) ? 0.2 : $border_width;
			
			$this->pdf->setcolor('fillstroke',$border_color[0],$border_color[1],$border_color[2],$border_color[3],$border_color[4] ?? 0);
			$this->pdf->setlinewidth(\ebi\Calc::mm2pt($border_width));
			
			if($style === 'F'){
				$style = 'FD';
			}
		}
		if($style[0] === 'F'){
			$fill_color = $this->color_val($color);
			$this->pdf->setcolor('fill',$fill_color[0],$fill_color[1],$fill_color[2],$fill_color[3],$fill_color[4] ?? 0);
		}
		
		list($disp_x,$disp_y) = $this->disp($x, $y, $width, $height, 0);
		$this->pdf->rect($disp_x,$disp_y,$width,$height);
		
		if($style === 'D'){
			$this->pdf->stroke();
		}else if($style === 'F'){
			$this->pdf->fill();
		}else{
			$this->pdf->fill_stroke();
		}
		$this->pdf->restore();
		
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
	 *
	 * @return $this
	 */
	public function add_circle($x,$y,$diameter,$opt=[]){
		list($x,$y,$diameter) = $this->mm2pt($x,$y,$diameter);
		
		$style = ($opt['fill'] ?? false) ? 'F' : 'D';
		$color = $opt['color'] ?? '#000000';
		$border_width = $opt['border_width'] ?? null;
		$border_color = $this->color_val($opt['border_color'] ?? $color);
		
		$this->pdf->save();
		
		if($border_width !== null || $style === 'D'){
			$border_width = ($border_width === null) ? 0.2 : $border_width;
			
			$this->pdf->setcolor('fillstroke',$border_color[0],$border_color[1],$border_color[2],$border_color[3],$border_color[4] ?? 0);
			$this->pdf->setlinewidth(\ebi\Calc::mm2pt($border_width));
			
			if($style === 'F'){
				$style = 'FD';
			}
		}
		if($style[0] === 'F'){
			$fill_color = $this->color_val($color);
			$this->pdf->setcolor('fill',$fill_color[0],$fill_color[1],$fill_color[2],$fill_color[3],$fill_color[4] ?? 0);
		}
		
		list($disp_x,$disp_y) = $this->disp($x, $y, $diameter, $diameter, 0);
		
		// 左上を原点とする
		$r = $diameter / 2;
		$disp_x = $disp_x + $r;
		$disp_y = $disp_y + $r;
		$this->pdf->circle($disp_x,$disp_y,$r);
		
		if($style === 'D'){
			$this->pdf->stroke();
		}else if($style === 'F'){
			$this->pdf->fill();
		}else{
			$this->pdf->fill_stroke();
		}
		$this->pdf->restore();
		
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
		$renderer = new \BaconQrCode\Renderer\ImageRenderer(
			new \BaconQrCode\Renderer\RendererStyle\RendererStyle(400),
			new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
		);
		$writer = new \BaconQrCode\Writer($renderer);
		$writer->writeString($value);
		
		$this->add_svg_string(
			$x,
			$y,
			$width,
			$width,
			$writer->writeString($value),
			$opt
		);
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
		return $this;
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
	 *
	 * @return $this
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
		return $this;
	}
	


	
	/**
	 * ルーラーの追加
	 * @return $this
	 */
	public function add_ruler(){
		list($w,$h) = $this->current_page_size;
		
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
	 * @param number $mark トンボの長さ mm
	 * @param boolean $center センタートンボの表示
	 * @return $this
	 */
	public function add_trim_mark($x,$y,$w,$h,$mark=3,$center=false){
		$this->add_line($x, $y, $x, $y-$mark);
		$this->add_line($x, $y, $x-$mark, $y);
		$this->add_line($x, $y, $x, $y-$mark);
		$this->add_line($x, $y, $x-$mark, $y);
		
		$this->add_line($x+$w, $y, $x+$w, $y-$mark);
		$this->add_line($x+$w, $y, $x+$w+$mark, $y);
		$this->add_line($x+$w, $y, $x+$w, $y-$mark);
		$this->add_line($x+$w, $y, $x+$w+$mark, $y);
		
		$this->add_line($x, $y+$h, $x, $y+$h+$mark);
		$this->add_line($x, $y+$h, $x-$mark, $y+$h);
		$this->add_line($x, $y+$h, $x, $y+$h+$mark);
		$this->add_line($x, $y+$h, $x-$mark, $y+$h);
		
		$this->add_line($x+$w, $y+$h, $x+$w, $y+$h+$mark);
		$this->add_line($x+$w, $y+$h, $x+$w+$mark, $y+$h);
		$this->add_line($x+$w, $y+$h, $x+$w, $y+$h+$mark);
		$this->add_line($x+$w, $y+$h, $x+$w+$mark, $y+$h);
		
		if($center){
			$this->add_line($x, $y+($h/2)-($h/6), $x, $y+($h/2)+($h/6));
			$this->add_line($x+1, $y+($h/2), $x, $y+($h/2));
			
			$this->add_line($x+$w, $y+($h/2)-($h/6), $x+$w, $y+($h/2)+($h/6));
			$this->add_line($x+$w-1, $y+($h/2), $x+$w, $y+($h/2));
			
			$this->add_line($x+($w/2)-($w/6), $y, $x+($w/2)+($w/6), $y);
			$this->add_line($x+($w/2), $y+1, $x+($w/2), $y);
			
			$this->add_line($x+($w/2)-($w/6),$y+$h,$x+($w/2)+($w/6),$y+$h);
			$this->add_line($x+($w/2), $y+$h-1, $x+($w/2), $y+$h);
		}
		return $this;
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
	 * @return $this
	 */
	public function add_textbox($x,$y,$width,$height,$text,$opt=[]){
		list($x,$y,$width,$height) = $this->mm2pt($x,$y,$width,$height);
		
		$font_family = $opt['font_family'] ?? 'HiraKakuProN-W3';
		$font_size = $opt['font_size'] ?? 8;
		$color_code = $opt['color'] ?? '#000000';
		$text_spacing = $opt['text_spacing'] ?? 0;
		$text_leading = $opt['text_leading'] ?? $font_size;
		$align = $opt['align'] ?? 0;
		$valign = $opt['valign'] ?? 0;
		$angle = $opt['angle'] ?? 0;
		
		$optlist = sprintf(
			'embedding=true encoding=unicode '.
			'fontname=%s '.
			'fontsize=%s '.
			'charspacing=%s '.
			'leading=%s '.
			'alignment=%s '.
			'fillcolor={%s} '.
			'hyphenchar=none ',
			$font_family,
			$font_size,
			$text_spacing,
			$text_leading,
			($align === 0 ? 'left' : ($align === 1 ? 'center' : 'right')),
			implode(' ',$this->color_val($color_code))
		);
		
		$fitoptlist = sprintf(
			'firstlinedist=ascender lastlinedist=descender '.
			($this->debug ? 'showborder=true' : '').
			'rotate=%s '.
			'verticalalign=%s',
			$this->rotate2world($angle),
			($valign === 0 ? 'top' : ($valign === 1 ? 'center' : 'bottom'))
		);
		$textflow = $this->pdf->create_textflow($text, $optlist);
		
		list($disp_x,$disp_y,$disp_x2,$disp_y2) = $this->disp($x, $y, $width, $height, $angle);
		$this->pdf->fit_textflow($textflow,$disp_x, $disp_y, $disp_x2,$disp_y2,$fitoptlist);
		
		return $this;
	}
	
	private function color_val($color_code){
		if(is_array($color_code)){
			return [
				'cmyk',
				(float)$color_code[0] ?? 0,
				(float)$color_code[1] ?? 0,
				(float)$color_code[2] ?? 0,
				(float)$color_code[3] ?? 0
			];
		}
		if(substr($color_code,0,1) == '#'){
			$color_code = substr($color_code,1);
		}
		
		if($this->K100 && ($color_code === '000000' || $color_code === '000')){
			return ['cmyk',0,0,0,1];
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
		return [
			'rgb',
			$r === 0 ? 0 : ($r / 255),
			$g === 0 ? 0 : ($g / 255),
			$b === 0 ? 0 : ($b / 255),
		];
	}
	
	/**
	 * PDFlibでの扱いは左下起点なので左上起点から左下起点に計算する(単位はpt)
	 * 
	 * @param number $x pt
	 * @param number $y pt
	 * @param number $width pt
	 * @param number $height pt
	 * @param number $angle
	 * @return number[] [x1,y1,x2,y2]
	 */
	private function disp($x,$y,$width,$height,$angle=0){
		$base_x = 0;
		$base_y = $height * -1;
		
		$disp_x = $x;
		$disp_y = $this->current_page_size[1] - $y;
		
		$pos_x = ($base_x * cos($angle / 180 * M_PI)) - ($base_y * sin($angle / 180 * M_PI));
		$pos_y = ($base_x * sin($angle / 180 * M_PI)) + ($base_y * cos($angle / 180 * M_PI));
		
		return [
			$disp_x - $pos_x,
			$disp_y + $pos_y,
			$disp_x - $pos_x + $width,
			$disp_y + $pos_y + $height
		];
	}
	
	/**
	 * PDFlibでの扱いは左回転なので右回転から左回転に計算する
	 * @param number $angle
	 * @return number
	 */
	private function rotate2world($angle){
		return 360 - $angle;
	}
	
	/**
	 * ファイルに書き出す
	 * @param string $filename
	 */
	public function write($filename){
		$this->close_pdf();
		
		$filename = \ebi\Util::path_absolute(getcwd(), $filename);
		\ebi\Util::mkdir(dirname($filename));
		
		\ebi\Util::file_write($filename, $this->pdf->get_buffer());
	}
	
	/**
	 * 出力
	 * @param string $filename
	 */
	public function output($filename=null){
		$this->close_pdf();
		
		if(empty($filename)){
			$filename = date('Ymd_his').'.pdf';
		}
		$buf = $this->pdf->get_buffer();
		header('Content-type: application/pdf');
		header('Content-Length: '.strlen($buf));
		header('Content-Disposition: inline; filename='.$filename);
		
		print($buf);
	}
	
	/**
	 * ダウンロード
	 * @param string $filename
	 */
	public function download($filename=null){
		if(empty($filename)){
			$filename = date('Ymd_his').'.pdf';
		}
		\ebi\HttpFile::attach([$filename,$this->pdf->get_buffer()]);
	}
	
	private function end_page(){
		if($this->pages > 0){
			$this->pdf->end_page_ext('');
		}
	}
	
	private function close_pdf(){
		$this->end_page();
		$this->pdf->end_document('');
	}
	
	/**
	 * ページサイズ mm
	 * @param string $pdffile
	 * @return array [page=>[width,height]]
	 */
	public static function get_page_size($pdffile){
		$self = new static();
		$doc_id = $self->load_pdf($pdffile);
		$pages = (int)$self->pdf->pcos_get_number($doc_id,'length:pages');
		$page_size = [];
		
		for($index=0;$index<$pages;$index++){
			$width = $self->pdf->pcos_get_number($doc_id,sprintf('pages[%d]/width',$index));
			$height = $self->pdf->pcos_get_number($doc_id,sprintf('pages[%d]/height',$index));
			
			$page_size[$index + 1] = [
				\ebi\Calc::pt2mm($width),
				\ebi\Calc::pt2mm($height),
			];
		}
		return $page_size;
	}
	
	/**
	 * ページ毎に抽出
	 * @param string $pdffile
	 * @param integer $start start page
	 * @param integer $end end page
	 * @throws \ebi\exception\AccessDeniedException
	 */
	public static function split($pdffile,$start=1,$end=null,$pdfversion=null){
		$page_size = self::get_page_size($pdffile);
		$num_pages = sizeof($page_size);
		
		if(empty($start)){
			$start = 1;
		}
		if(empty($end) || $num_pages < $end){
			$end = $num_pages;
		}
		
		for($page=$start;$page<=$end;$page++){
			$inst = new static($pdfversion);
			
			$doc_id = $inst->load_pdf($pdffile);
			$image = $inst->pdf->open_pdi_page($doc_id,$page,'');
			
			$width_pt = $inst->pdf->info_pdi_page($image,'width','');
			$height_pt = $inst->pdf->info_pdi_page($image,'height','');
			
			$inst->add_page(\ebi\Calc::pt2mm($width_pt), \ebi\Calc::pt2mm($height_pt));
			
			$inst->pdf->fit_pdi_page($image,0,0,'');
			$inst->pdf->close_pdi_page($image);
			
			yield $page=>$inst;
		}
	}
}
