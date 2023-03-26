<?php
namespace tt\pdf;
/**
 * PDFlib
 * 単位はmm
 * 
 * @see http://www.pdflib.jp/product/download/pdflib/
 * @see https://www.infotek.co.jp/pdflib/pdflib_info.html
 * @see https://www.infotek.co.jp/pdflib/pdflib/pdflib_cookbook.html
 */
class PDFlib{
	static private int $pvf_keys = 0;
	static private string $license = '';
	
	private \PDFlib $pdf;
	private int $pages = 0;
	private array $current_page_size = [0,0];
	private bool $K100 = false;
	private array $load_pdf = [];
	private bool $closed = false;
	
	public function __construct(string $filename, ?float $pdf_version=null){
		$this->pdf = new \PDFlib();
		
		if(!empty(self::$license)){
			$this->pdf->set_option('license='.self::$license);
		}
		$this->pdf->set_option('stringformat=utf8'); // 文字列をUTF-8で渡すことをPDFlib に知らせる
		
		$opt = ['optimize=true'];
		if(!empty($pdf_version)){
			$opt[] = 'compatibility='.$pdf_version;
		}

		if(!empty($filename)){
			if(!is_dir(dirname($filename))){
				mkdir(dirname($filename), 0777, true);
			}
		}
		if($this->pdf->begin_document($filename, implode(' ',$opt)) == 0){
			throw new \tt\pdf\exception\AccessDeniedException($this->pdf->get_errmsg());
		}
	}

	public static function set_license(string $license): void{
		self::$license = $license;
	}

	public static function font_check(string $text, string $font_family, int $font_size=8): bool{
		$optlist = sprintf(
			'embedding=true encoding=unicode '.
			'fontname=%s '.
			'fontsize=%s '.
			'hyphenchar=none '.
			'charref=true ',
			$font_family,
			$font_size
		);

		$pdf = new \PDFlib();
		$pdf->set_option('stringformat=utf8');
		$pdf->begin_document('', '');
		$pdf->begin_page_ext(100, 100, '');
		$textflow = $pdf->create_textflow(htmlentities($text, ENT_XML1), $optlist);
		$pdf->end_page_ext('');
		$pdf->end_document('');

		return ($textflow !== 0);
	}

	/**
	 * #000000をK100とする
	 */
	public function K100(bool $boolean): self{
		$this->K100 = $boolean;
		return $this;
	}
	
	public function is_K100(): bool{
		return $this->K100;
	}
	
	/**
	 * @return [x, y]
	 */
	public function current_page_size(): array{
		return $this->current_page_size;
	}

	/**
	 * フォントを追加する
	 */
	public function add_font(string $font_file, ?string $alias=null): self{
		$alias = empty($alias) ? preg_replace('/^(.+?)\.$/','\\1',$font_file) : $alias;
		$this->pdf->set_option(sprintf('FontOutline={%s=%s}',$alias,$font_file));
		
		return $this;
	}

	/**
	 * ルーラーの追加
	 */
	public function add_ruler(): self{
		[$w, $h] = $this->current_page_size();
		
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
	 *　PDFlibでの扱いはptなのでmmからptに計算する
	 */
	private static function mm2pt(...$args): array{
		$result = [];
		foreach($args as $mm){
			$result[] = $mm * 2.83465;
		}
		return $result;
	}
	private static function px2pt(int $px, float $dpi=72): float{
		return ($px / $dpi * 72);
	}
	private static function pt2mm(float $pt): float{
		return ($pt * 0.352778);
	}

	/**
	 * Defines the author of the document
	 */	
	public function set_author(string $author): self{
		$this->pdf->set_info('Author',$author);
		return $this;
	}
	
	/**
	 * Defines the creator of the document
	 */
	public function set_creator(string $creator): self{
		$this->pdf->set_info('Creator',$creator);
		return $this;
	}
	
	/**
	 * Defines the title of the document
	 */
	public function set_title(string $title): self{
		$this->pdf->set_info('Title',$title);
		return $this;
		
	}
	/**
	 * Defines the subject of the document
	 */
	public function set_subject(string $subject): self{
		$this->pdf->set_info('Subject',$subject);
		return $this;
	}
	
	/**
	 * ページを追加
	 */
	public function add_page(float $width, float $height): self{
		[$width, $height] = self::mm2pt($width, $height);
		
		$this->end_page();
		$this->pdf->begin_page_ext($width, $height, '');
		
		$this->current_page_size = [$width, $height];
		$this->pages++;
		
		return $this;
	}

	public function number_of_pages(): int{
		return $this->pages;
	}
	
	/**
	 * 画像を追加
	 *
	 * opt:
	 *  int $rotate 回転角度
	 *  int $dpi DPI
	 */
	public function add_image(float $x, float $y, string $filepath, array $opt=[]): self{
		if(!is_file($filepath)){
			throw new \tt\pdf\exception\AccessDeniedException($filepath.' does not exist');
		}
		[$x, $y] = self::mm2pt($x, $y);
		$info = getimagesize($filepath);
		$mime = $info['mime'] ?? null;
		$image_width = $info[0] ?? 0;
		$image_height = $info[1] ?? 0;
		
		if($mime !== 'image/jpeg' && $mime !== 'image/png'){
			throw new \tt\pdf\exception\ImageException('image not supported');
		}
		$image = $this->pdf->load_image('auto',$filepath,'');
		
		if($image === 0){
			throw new \tt\pdf\exception\AccessDeniedException($filepath.' does not exist');
		}

		$dpi = $opt['dpi'] ?? 72;
		$rotate = $opt['rotate'] ?? 0;
		$width = self::px2pt($image_width, $dpi);
		$height = self::px2pt($image_height, $dpi);
		
		$image_opt = sprintf(
			'rotate=%s '.
			'dpi=%s',
			$this->rotate2world($rotate),
			$dpi
		);
		
		[$disp_x, $disp_y] = $this->disp($x, $y, $width, $height, $rotate);
		$this->pdf->fit_image($image,$disp_x,$disp_y,$image_opt);
		
		return $this;
	}
	
	/**
	 * SVGを追加
	 *
	 * opt:
	 *  int $rotate 回転角度
	 */
	public function add_svg(float $x, float $y, float $width, float $height, string $filepath, array $opt=[]): self{
		[$x, $y, $width, $height] = self::mm2pt($x, $y, $width, $height);
		
		$image = $this->pdf->load_graphics('auto', $filepath, '');
		
		$rotate = $opt['rotate'] ?? 0;
		
		$image_opt = sprintf(
			'rotate=%s '.
			'boxsize={%s %s} '.
			'position=center '.
			'fitmethod=meet',
			$this->rotate2world($rotate),
			$width,$height
		);
		
		[$disp_x, $disp_y] = $this->disp($x, $y, $width, $height, $rotate);
		$this->pdf->fit_graphics($image,$disp_x,$disp_y,$image_opt);
		$this->pdf->close_graphics($image);
		
		return $this;
	}

	/**
	 * SVGを文字列で追加
	 */
	public function add_svg_string(float $x, float $y, float $width, float $height, string $svg_string, array $opt=[]): self{
		[$x, $y, $width, $height] = self::mm2pt($x, $y, $width, $height);
		
		$pvf_image = 'pvf/image_'.self::$pvf_keys++;
		$this->pdf->create_pvf($pvf_image, $svg_string, '');
		$image = $this->pdf->load_graphics('auto',$pvf_image,'');
		
		$rotate = $opt['rotate'] ?? 0;
		
		$image_opt = sprintf(
			'rotate=%s '.
			'boxsize={%s %s} '.
			'position=center '.
			'fitmethod=meet',
			$this->rotate2world($rotate),
			$width,$height
		);
		
		[$disp_x, $disp_y] = $this->disp($x, $y, $width, $height, $rotate);
		$this->pdf->fit_graphics($image,$disp_x,$disp_y,$image_opt);
		$this->pdf->close_graphics($image);
		
		return $this;
	}
	
	/**
	 * PDFを追加
	 *
	 * opt:
	 *  int $rotate 回転角度
	 *  float $scale 拡大率
	 *  int $page_no 追加するページ番号
	 */
	public function add_pdf(float $x, float $y, string $filepath, array $opt=[]): self{
		if(!is_file($filepath)){
			throw new \tt\pdf\exception\AccessDeniedException($filepath.' not found');
		}
		[$x, $y] = self::mm2pt($x, $y);
		
		$rotate = $opt['rotate'] ?? 0;
		$scale = $opt['scale'] ?? 0;
		$page_no = $opt['page_no'] ?? 1;

		try{
			$doc_id = $this->load_pdf($filepath);
			$image = $this->pdf->open_pdi_page($doc_id,$page_no,'');
			
			$width_pt = $this->pdf->info_pdi_page($image,'width','');
			$height_pt = $this->pdf->info_pdi_page($image,'height','');
			
			$image_opt = '';
			if(!empty($rotate)){
				$image_opt = sprintf('rotate=%s ',$this->rotate2world($rotate));
			}
			if(!empty($scale)){
				$image_opt = sprintf('scale=%s ',$scale);
				$width_pt *= $scale;
				$height_pt *= $scale;
			}
			
			[$disp_x, $disp_y] = $this->disp($x, $y, $width_pt, $height_pt, $rotate);
			$this->pdf->fit_pdi_page($image,$disp_x,$disp_y,$image_opt);
			$this->pdf->close_pdi_page($image);
		}catch(\PDFlibException $e){
			throw new \tt\pdf\exception\AccessDeniedException($filepath.' is missing page '.$page_no);
		}
		
		return $this;
	}
	
	private function load_pdf($filepath){
		if(!is_file($filepath)){
			throw new \tt\pdf\exception\AccessDeniedException();
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
	 *
	 * opt:
	 *  string $border_color 線の色 #FFFFFF
	 *  float $border_width 線の太さ mm
	 *  float[] $dash 点線の長さ [5,2] mm
	 */
	public function add_line(float $sx, float $sy, float $ex, float $ey, array $opt=[]): self{
		[$sx, $sy, $ex, $ey] = self::mm2pt($sx, $sy, $ex, $ey);
		
		$border_width = $opt['border_width'] ?? null;
		$border_color = $this->color_val($opt['border_color'] ?? ($opt['color'] ?? '#000000'));
		[$linewidth] = self::mm2pt($border_width ?? 0.1);
		
		$this->pdf->save();
		
		$this->pdf->setcolor('fillstroke',$border_color[0],$border_color[1],$border_color[2],$border_color[3],$border_color[4] ?? 0);
		$this->pdf->setlinewidth($linewidth);

		if(isset($opt['dash']) && is_array($opt['dash'])){
			$this->pdf->set_graphics_option('dasharray={'.implode(' ',self::mm2pt($opt['dash'])).'}');
		}
		
		$this->pdf->moveto($sx,$this->current_page_size[1] - $sy);
		$this->pdf->lineto($ex,$this->current_page_size[1] - $ey);
		$this->pdf->stroke();
		
		$this->pdf->restore();
		
		return $this;
	}
	
	/**
	 * 矩形
	 * 
	 * opt:
	 *  bool $fill true: 塗りつぶす
	 *  string $color 色 #000000
	 *  string $border_color 線の色 #FFFFFF
	 *  float $border_width 線の太さ mm
	 */
	public function add_rect(float $x, float $y, float $width, float $height, array $opt=[]): self{
		$this->pdf->save();

		if(($opt['fill'] ?? false) === false){
			$opt['border_width'] = $opt['border_width'] ?? $border_width ?? 0.1;
			$bw = $opt['border_width'];
			[$x, $y, $width, $height] = [$x + ($bw / 2), $y + ($bw / 2), $width - $bw, $height - $bw];
		}
		[$x, $y, $width, $height] = self::mm2pt($x, $y, $width, $height);
		
		$style = $this->set_style($opt);
		
		[$disp_x, $disp_y] = $this->disp($x, $y, $width, $height, 0);
		$this->pdf->rect($disp_x,$disp_y,$width,$height);
		
		$this->draw_style($style);

		$this->pdf->restore();
		
		return $this;
	}
	
	private function set_style(array $opt){
		$style = ($opt['fill'] ?? false) ? 'F' : 'D';
		$color = $opt['color'] ?? '#000000';
		$border_width = $opt['border_width'] ?? null;
		$border_color = $this->color_val($opt['border_color'] ?? $color);
		$opacity = $opt['opacity'] ?? null;
		
		if(!empty($opacity)){
			$gstate = $this->pdf->create_gstate('opacityfill='.$opacity.' opacitystroke='.$opacity);
			$this->pdf->set_gstate($gstate);
		}
		if($border_width !== null || $style === 'D'){
			[$linewidth] = self::mm2pt(($border_width === null) ? 0.2 : $border_width);
			
			$this->pdf->setcolor('fillstroke',$border_color[0],$border_color[1],$border_color[2],$border_color[3],$border_color[4] ?? 0);
			$this->pdf->setlinewidth($linewidth);
			
			if($style === 'F'){
				$style = 'FD';
			}
		}
		if($style[0] === 'F'){
			$fill_color = $this->color_val($color);
			$this->pdf->setcolor('fill',$fill_color[0],$fill_color[1],$fill_color[2],$fill_color[3],$fill_color[4] ?? 0);
		}
		return $style;
	}

	private function draw_style(string $style){
		if($style === 'D'){
			$this->pdf->stroke();
		}else if($style === 'F'){
			$this->pdf->fill();
		}else{
			$this->pdf->fill_stroke();
		}
	}

	/**
	 * 三角形
	 * opt:
	 *  bool $fill true: 塗りつぶす
	 *  string $color 色 #000000
	 *  string $border_color 線の色 #FFFFFF
	 *  float $border_width 線の太さ mm
	 */
	public function add_triangle(float $x1, float $y1, float $x2, float $y2, float $x3, float $y3, array $opt=[]): self{		
		$this->pdf->save();

		[$x1, $y1, $x2, $y2, $x3, $y3] = self::mm2pt($x1, $y1, $x2, $y2, $x3, $y3);
		
		$style = $this->set_style($opt);

		$this->pdf->moveto($x1, $this->current_page_size[1] - $y1);
		$this->pdf->lineto($x2, $this->current_page_size[1] - $y2);
		$this->pdf->lineto($x3, $this->current_page_size[1] - $y3);
		$this->pdf->closepath();

		$this->draw_style($style);

		$this->pdf->restore();
		
		return $this;
	}

	/**
	 * 円
	 *
	 * opt:
	 *  bool $fill true: 塗りつぶす
	 *  string $color 色 #000000
	 *  string $border_color 線の色 #FFFFFF
	 *  float $border_width 線の太さ mm
	 */
	public function add_circle(float $x, float $y, float $diameter, array $opt=[]): self{				
		$this->pdf->save();

		[$x, $y, $diameter] = self::mm2pt($x, $y, $diameter);		
		$style = $this->set_style($opt);
		
		[$disp_x, $disp_y] = $this->disp($x, $y, $diameter, $diameter, 0);
		
		// 左上を原点とする
		$r = $diameter / 2;
		$disp_x = $disp_x + $r;
		$disp_y = $disp_y + $r;
		$this->pdf->circle($disp_x,$disp_y,$r);
		
		$this->draw_style($style);
		$this->pdf->restore();
		
		return $this;
	}
	
	/**
	 * テキストボックスの追加
	 *
	 * opt:
	 *  int $align 0: LEFT, 1: CENTER, 2: RIGHT
	 *  int $valign 0: TOP, 1: MIDDLE, 2: BOTTOM
	 *  string $color #000000
	 *  string $font_family フォントファミリー
	 *  float $font_size フォントサイズ pt
	 *  float $text_spacing 文字間隔 pt
	 *  float $text_leading 行間隔 pt
	 *  int $rotate 回転角度
	 */
	public function add_textbox(float $x, float $y, float $width, float $height, string $text, array $opt=[]): self{
		[$x, $y, $width, $height] = self::mm2pt($x,$y,$width,$height);
		
		$font_family = $opt['font_family'] ?? 'HiraKakuProN-W3';
		$font_size = $opt['font_size'] ?? 8;
		$color_code = $opt['color'] ?? '#000000';
		$text_spacing = $opt['text_spacing'] ?? 0;
		$text_leading = $opt['text_leading'] ?? $font_size;
		$align = $opt['align'] ?? 0;
		$valign = $opt['valign'] ?? 0;
		$rotate = $opt['rotate'] ?? $opt['rotate'] ?? 0;
		$font_style = $opt['font_style'] ?? null; // normal, bold, italic, bolditalic
		
		$optlist = sprintf(
			'embedding=true encoding=unicode '.
			'fontname=%s '.
			'fontsize=%s '.
			'charspacing=%s '.
			'leading=%s '.
			'alignment=%s '.
			'fillcolor={%s} '.
			'hyphenchar=none '.
			'charref=true ',
			$font_family,
			$font_size,
			$text_spacing,
			$text_leading,
			($align === 0 ? 'left' : ($align === 1 ? 'center' : 'right')),
			implode(' ',$this->color_val($color_code))
		);
		if(!empty($font_style)){
			$optlist .= 'fontstyle='.$font_style.' ';
		}
		
		$fitoptlist = sprintf(
			'firstlinedist=ascender lastlinedist=descender '.
			'rotate=%s '.
			'verticalalign=%s',
			$this->rotate2world($rotate),
			($valign === 0 ? 'top' : ($valign === 1 ? 'center' : 'bottom'))
		);
		$textflow = $this->pdf->create_textflow(htmlentities($text, ENT_XML1), $optlist);
		
		if($textflow === 0){
			throw new \tt\pdf\exception\InvalidTextOptionException('Invalid Text Option: ('.$optlist.'), Value: '.$text);
		}
		[$disp_x, $disp_y, $disp_x2, $disp_y2] = $this->disp($x, $y, $width, $height, $rotate);
		$this->pdf->fit_textflow($textflow,$disp_x, $disp_y, $disp_x2, $disp_y2, $fitoptlist);
		
		return $this;
	}
	
	/**
	 * @param mixed $color_code (array|string)
	 */
	private function color_val($color_code): array{
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
		
		if($this->K100 && ($color_code === '000000')){
			return ['cmyk',0,0,0,1];
		}
		$r = hexdec(substr($color_code,0,2));
		$g = hexdec(substr($color_code,2,2));
		$b = hexdec(substr($color_code,4,2));
		
		return [
			'rgb',
			$r === 0 ? 0 : ($r / 255),
			$g === 0 ? 0 : ($g / 255),
			$b === 0 ? 0 : ($b / 255),
		];
	}
	
	/**
	 * PDFlibでの扱いは左下起点なので左上起点から左下起点に計算する(単位はpt)
	 */
	private function disp(float $x, float $y, float $width, float $height, int $rotate=0): array{
		$base_x = 0;
		$base_y = $height * -1;
		
		$disp_x = $x;
		$disp_y = $this->current_page_size[1] - $y;
		
		$pos_x = ($base_x * cos($rotate / 180 * M_PI)) - ($base_y * sin($rotate / 180 * M_PI));
		$pos_y = ($base_x * sin($rotate / 180 * M_PI)) + ($base_y * cos($rotate / 180 * M_PI));
		
		return [
			$disp_x - $pos_x,
			$disp_y + $pos_y,
			$disp_x - $pos_x + $width,
			$disp_y + $pos_y + $height
		];
	}
	
	/**
	 * PDFlibでの扱いは左回転なので右回転から左回転に計算する
	 */
	private function rotate2world(int $rotate): int{
		return 360 - $rotate;
	}
	
	private function end_page(): void{
		if($this->pages > 0){
			$this->pdf->end_page_ext('');
		}
	}
		
	/**
	 * PDFドキュメントを閉じてファイルに書き出す
	 */
	public function write(): void{
		if(!$this->closed){
			if($this->pages === 0){
				throw new \tt\pdf\exception\NoPagesException();
			}
			$this->end_page();
			$this->pdf->end_document('');

			$this->closed = true;
		}
	}
	
	/**
	 * 各ページのサイズ
	 * @return [page=>[width,height]]
	 */
	public static function get_page_size(string $filename): array{
		$self = new static('');
		$doc_id = $self->load_pdf($filename);
		$pages = (int)$self->pdf->pcos_get_number($doc_id, 'length:pages');
		$page_size = [];
		
		for($index=0;$index<$pages;$index++){
			$width = $self->pdf->pcos_get_number($doc_id, sprintf('pages[%d]/width', $index));
			$height = $self->pdf->pcos_get_number($doc_id, sprintf('pages[%d]/height', $index));
			
			$page_size[$index + 1] = [
				self::pt2mm($width),
				self::pt2mm($height),
			];
		}
		return $page_size;
	}

	/**
	 * PDFのバージョンを抽出
	 */
	public static function version(string $filename): float{
		$self = new static('');
		$doc_id = $self->load_pdf($filename);
		$pdf_version = (float)$self->pdf->pcos_get_string($doc_id, 'pdfversionstring');

		return $pdf_version;
	}
	
	/**
	 * 部分的にコピーする
	 */
	public static function copy(string $from_filename, string $to_filename, int $start_page, ?int $end_page=null): void{
		$self = new static($to_filename, self::version($from_filename));
		$doc_id = $self->load_pdf($from_filename);

		if($end_page === null){
			$end_page = (int)$self->pdf->pcos_get_number($doc_id, 'length:pages');
		}

		for($page=$start_page; $page<=$end_page; $page++){
			$index = $page - 1;

			$width_pt = $self->pdf->pcos_get_number($doc_id, sprintf('pages[%d]/width', $index));
			$height_pt = $self->pdf->pcos_get_number($doc_id, sprintf('pages[%d]/height', $index));
			$image = $self->pdf->open_pdi_page($doc_id, $page, '');

			$self->add_page(self::pt2mm($width_pt), self::pt2mm($height_pt));
			$self->pdf->fit_pdi_page($image, 0, 0, '');
			$self->pdf->close_pdi_page($image);
		}
		$self->write();
	}
}
