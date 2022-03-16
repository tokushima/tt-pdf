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
	static private $pvf_keys = 0;
	
	private $pdf;
	private $pages = 0;
	private $current_page_size = [0,0];
	private $K100 = false;
	private $load_pdf = [];
	
	/**
	 * 
	 * @param $pdf_version 作成するPDFバージョン
	 * @param $compress PDFオブジェクトを圧縮する （PDF-1.5以降のバージョン）
	 * @param $license PDFlibのライセンス
	 */
	public function __construct(?float $pdf_version=null, bool $compress=false, string $license=null){
		$this->pdf = new \PDFlib();
		$license = $license ?? \ebi\Conf::get('license');
		
		if(!empty($license)){
			$this->pdf->set_option('license='.$license);
		}
		$this->pdf->set_option('stringformat=utf8'); // 文字列をUTF-8で渡すことをPDFlib に知らせる
		
		$opt = [];
		if(!empty($pdf_version)){
			$opt[] = 'compatibility='.$pdf_version;
		}
		if(!$compress){
			$opt[] = 'objectstreams=none';
		}
		
		if($this->pdf->begin_document('', implode(' ',$opt)) == 0){
			throw new \LogicException($this->pdf->get_errmsg());
		}
	}
	
	/**
	 * #000000をK100とする
	 */
	public function K100(bool $boolean): self{
		$this->K100 = (bool)$boolean;
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
		list($w,$h) = $this->current_page_size();
		
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
	private function mm2pt(...$args): array{
		$result = [];
		foreach($args as $mm){
			$result[] = \tt\image\Calc::mm2pt((float)$mm);
		}
		return $result;
	}
	
	/**
	 * @return $this
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
		[$width, $height] = $this->mm2pt($width,$height);
		
		$this->end_page();
		$this->pdf->begin_page_ext($width, $height, '');
		
		$this->current_page_size = [$width, $height];
		$this->pages++;
		
		return $this;
	}
	
	/**
	 * 画像を追加
	 *
	 * opt:
	 *  int $rotate 回転角度
	 *  int $dpi DPI
	 */
	public function add_image(float $x, float $y, string $filepath, array $opt=[]): self{
		[$x, $y] = $this->mm2pt($x,$y);
		$info = \ebi\Image::get_info($filepath);
		
		if($info['mime'] !== 'image/jpeg' && $info['mime'] !== 'image/png'){
			throw new \tt\pdf\exception\ImageException('image not supported');
		}
		$image = $this->pdf->load_image('auto',$filepath,'');
		
		if($image === 0){
			throw new \tt\pdf\exception\AccessDeniedException();
		}

		$dpi = $opt['dpi'] ?? 72;
		$rotate = $opt['rotate'] ?? 0;
		$width = \tt\image\Calc::px2pt($info['width'],$dpi);
		$height = \tt\image\Calc::px2pt($info['height'],$dpi);
		
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
		[$x, $y, $width, $height] = $this->mm2pt($x,$y,$width,$height);
		
		$image = $this->pdf->load_graphics('auto',$filepath,'');
		
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
		[$x, $y, $width, $height] = $this->mm2pt($x,$y,$width,$height);
		
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
		
		list($disp_x,$disp_y) = $this->disp($x, $y, $width, $height, $rotate);
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
		[$x, $y] = $this->mm2pt($x,$y);
		
		$rotate = $opt['rotate'] ?? 0;
		$scale = $opt['scale'] ?? 0;
		$page_no = $opt['page_no'] ?? 1;

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
		[$sx, $sy, $ex, $ey] = $this->mm2pt($sx,$sy,$ex,$ey);
		
		$border_width = $opt['border_width'] ?? null;
		$border_color = $this->color_val($opt['border_color'] ?? ($opt['color'] ?? '#000000'));
		
		$this->pdf->save();
		
		$this->pdf->setcolor('fillstroke',$border_color[0],$border_color[1],$border_color[2],$border_color[3],$border_color[4] ?? 0);
		$this->pdf->setlinewidth(\tt\image\Calc::mm2pt($border_width ?? 0.1));

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
	 * 
	 * opt:
	 *  bool $fill true: 塗りつぶす
	 *  string $color 色 #000000
	 *  string $border_color 線の色 #FFFFFF
	 *  float $border_width 線の太さ mm
	 */
	public function add_rect(float $x, float $y, float $width, float $height, array $opt=[]): self{
		[$x, $y, $width, $height] = $this->mm2pt($x,$y,$width,$height);
		
		$style = ($opt['fill'] ?? false) ? 'F' : 'D';
		$color = $opt['color'] ?? '#000000';
		$border_width = $opt['border_width'] ?? null;
		$border_color = $this->color_val($opt['border_color'] ?? $color);
		
		$this->pdf->save();
		
		if($border_width !== null || $style === 'D'){
			$border_width = ($border_width === null) ? 0.2 : $border_width;
			
			$this->pdf->setcolor('fillstroke',$border_color[0],$border_color[1],$border_color[2],$border_color[3],$border_color[4] ?? 0);
			$this->pdf->setlinewidth(\tt\image\Calc::mm2pt($border_width));
			
			if($style === 'F'){
				$style = 'FD';
			}
		}
		if($style[0] === 'F'){
			$fill_color = $this->color_val($color);
			$this->pdf->setcolor('fill',$fill_color[0],$fill_color[1],$fill_color[2],$fill_color[3],$fill_color[4] ?? 0);
		}
		
		[$disp_x, $disp_y] = $this->disp($x, $y, $width, $height, 0);
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
	 *
	 * opt:
	 *  bool $fill true: 塗りつぶす
	 *  string $color 色 #000000
	 *  string $border_color 線の色 #FFFFFF
	 *  float $border_width 線の太さ mm
	 */
	public function add_circle(float $x, float $y, float $diameter, array $opt=[]): self{
		[$x, $y, $diameter] = $this->mm2pt($x,$y,$diameter);
		
		$style = ($opt['fill'] ?? false) ? 'F' : 'D';
		$color = $opt['color'] ?? '#000000';
		$border_width = $opt['border_width'] ?? null;
		$border_color = $this->color_val($opt['border_color'] ?? $color);
		
		$this->pdf->save();
		
		if($border_width !== null || $style === 'D'){
			$border_width = ($border_width === null) ? 0.2 : $border_width;
			
			$this->pdf->setcolor('fillstroke',$border_color[0],$border_color[1],$border_color[2],$border_color[3],$border_color[4] ?? 0);
			$this->pdf->setlinewidth(\tt\image\Calc::mm2pt($border_width));
			
			if($style === 'F'){
				$style = 'FD';
			}
		}
		if($style[0] === 'F'){
			$fill_color = $this->color_val($color);
			$this->pdf->setcolor('fill',$fill_color[0],$fill_color[1],$fill_color[2],$fill_color[3],$fill_color[4] ?? 0);
		}
		
		[$disp_x, $disp_y] = $this->disp($x, $y, $diameter, $diameter, 0);
		
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
		[$x, $y, $width, $height] = $this->mm2pt($x,$y,$width,$height);
		
		$font_family = $opt['font_family'] ?? 'HiraKakuProN-W3';
		$font_size = $opt['font_size'] ?? 8;
		$color_code = $opt['color'] ?? '#000000';
		$text_spacing = $opt['text_spacing'] ?? 0;
		$text_leading = $opt['text_leading'] ?? $font_size;
		$align = $opt['align'] ?? 0;
		$valign = $opt['valign'] ?? 0;
		$rotate = $opt['rotate'] ?? $opt['rotate'] ?? 0;
		
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
		
		$fitoptlist = sprintf(
			'firstlinedist=ascender lastlinedist=descender '.
			'rotate=%s '.
			'verticalalign=%s',
			$this->rotate2world($rotate),
			($valign === 0 ? 'top' : ($valign === 1 ? 'center' : 'bottom'))
		);
		$textflow = $this->pdf->create_textflow(htmlentities($text, ENT_XML1), $optlist);
		
		list($disp_x,$disp_y,$disp_x2,$disp_y2) = $this->disp($x, $y, $width, $height, $rotate);
		$this->pdf->fit_textflow($textflow,$disp_x, $disp_y, $disp_x2,$disp_y2,$fitoptlist);
		
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
	
	/**
	 * ファイルに書き出す
	 */
	public function write(string $filename): void{
		$this->close_pdf();
		
		$filename = \ebi\Util::path_absolute(getcwd(), $filename);
		\ebi\Util::mkdir(dirname($filename));
		
		\ebi\Util::file_write($filename, $this->pdf->get_buffer());
	}
	
	/**
	 * 出力
	 */
	public function output(?string $filename=null): void{
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
	 */
	public function download(?string $filename=null): void{
		$this->close_pdf();

		if(empty($filename)){
			$filename = date('Ymd_his').'.pdf';
		}
		\ebi\HttpFile::attach([$filename, $this->pdf->get_buffer()]);
	}
	
	private function end_page(): void{
		if($this->pages > 0){
			$this->pdf->end_page_ext('');
		}
	}
	
	private function close_pdf(): void{
		if($this->pages === 0){
			throw new \tt\pdf\exception\NoPagesException();
		}
		$this->end_page();
		$this->pdf->end_document('');
	}
	
	/**
	 * 各ページのサイズ
	 * @return [page=>[width,height]]
	 */
	public static function get_page_size(string $filename): array{
		$self = new static();
		$doc_id = $self->load_pdf($filename);
		$pages = (int)$self->pdf->pcos_get_number($doc_id, 'length:pages');
		$page_size = [];
		
		for($index=0;$index<$pages;$index++){
			$width = $self->pdf->pcos_get_number($doc_id, sprintf('pages[%d]/width',$index));
			$height = $self->pdf->pcos_get_number($doc_id, sprintf('pages[%d]/height',$index));
			
			$page_size[$index + 1] = [
				\tt\image\Calc::pt2mm($width),
				\tt\image\Calc::pt2mm($height),
			];
		}
		return $page_size;
	}
	
	/**
	 * ページ毎に抽出
	 */
	public static function split(string $filename, int $start_page=1, ?int $end_page=null, ?float $pdf_version=null): \Generator{
		$page_size = self::get_page_size($filename);
		$num_pages = sizeof($page_size);
		
		if(empty($start)){
			$start = 1;
		}
		if(empty($end) || $num_pages < $end){
			$end = $num_pages;
		}
		
		for($page=$start;$page<=$end;$page++){
			$inst = new static($pdf_version);
			
			$doc_id = $inst->load_pdf($filename);
			$image = $inst->pdf->open_pdi_page($doc_id,$page,'');
			
			$width_pt = $inst->pdf->info_pdi_page($image,'width','');
			$height_pt = $inst->pdf->info_pdi_page($image,'height','');
			
			$inst->add_page(\tt\image\Calc::pt2mm($width_pt), \tt\image\Calc::pt2mm($height_pt));
			
			$inst->pdf->fit_pdi_page($image,0,0,'');
			$inst->pdf->close_pdi_page($image);
			
			yield $page=>$inst;
		}
	}
}
