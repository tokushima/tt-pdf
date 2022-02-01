<?php
namespace tt\pdf;
/**
 * Tcpdf(Fpdi)
 * @author tokushima
 *
 */
class Tcpdf{
	private $pdf;
	private $pages = 0;
	private $current_page_size = [0,0];	
	private $K100 = false;
	private $font_names = [];
	
	public function __construct(?float $pdf_version=null){
		$mb_internal_encoding = mb_internal_encoding();
		$this->pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
		mb_internal_encoding($mb_internal_encoding);
		
		$this->pdf->setPageUnit('mm');
		$this->pdf->setPrintHeader(false);
		$this->pdf->setPrintFooter(false);
		$this->pdf->SetMargins(0,0,0);
		$this->pdf->SetAutoPageBreak(false);
		$this->pdf->setFontSubsetting(true);
		
		if(!empty($pdf_version)){
			$this->pdf->setPDFVersion($pdf_version);
		}
	}
	
	/**
	 * #000000をK100とする
	 */
	public function K100(bool $boolean): self{
		$this->K100 = (boolean)$boolean;
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
	 * @param $fontfile フォントファイル (***.php)
	 * @param $alias 
	 * 
	 * フォントファイルの生成:
	 *  > vendor/tecnickcom/tcpdf/tools/tcpdf_addfont.php -t TrueTypeUnicode -f 32 -i *****.ttf -o [OUTDIR]
	 *  -t: TrueTypeUnicode, TrueType, Type1, CID0JP, CID0KR, CID0CS, CID0CT
	 */
	public function add_font(string $fontfile, ?string $alias=null): self{
		if(substr($fontfile,-4) !== '.php'){
			$path = realpath($fontfile);
			if($path === false){
				throw new \tt\pdf\exception\AccessDeniedException($fontfile.' not found');
			}
			$dir = dirname($path);
			$fontfile = (($dir !== '/') ? $dir : '').'/'.
				strtolower(
					preg_replace('/[^\w]/','',preg_replace('/^(.+)\.\w+$/','\\1',basename($fontfile)))
				).'.php';
		}
		$alias = empty($alias) ? preg_replace('/^(.+?)\.$/','\\1',$fontfile) : $alias;
		$this->pdf->AddFont($alias,null,$fontfile);
		
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
	 * Defines the author of the document
	 */
	public function set_author(string $author): self{
		$this->pdf->SetAuthor($author);
		return $this;
	}
	
	/**
	 * Defines the creator of the document
	 */
	public function set_creator(string $creator): self{
		$this->pdf->SetCreator($creator);
		return $this;
	}
	
	/**
	 * Defines the title of the document
	 */
	public function set_title(string $title): self{
		$this->pdf->SetTitle($title);
		return $this;
	}
	/**
	 * Defines the subject of the document
	 */
	public function set_subject(string $subject): self{
		$this->pdf->SetSubject($subject);
		return $this;
	}
	
	
	/**
	 * ページを追加
	 */
	public function add_page(float $width, float $height): self{
		$this->pdf->AddPage(($width > $height) ? 'L' : 'P',[$width,$height]);
		$this->current_page_size = [$width,$height];
		$this->pages++;

		return $this;
	}
	
	private function rotate(float $x, float $y, array $opt): void{
		if(($angle = $opt['angle'] ?? 0) !== 0){
			$this->pdf->StartTransform();
			$this->pdf->Rotate(360 - $angle,$x,$y); // 右回転として計算
		}
	}
	
	/**
	 * 画像を追加
	 * 
	 * opt:
	 *  int $angle 回転角度
	 *  int $dpi DPI
	 */
	public function add_image(float $x, float $y, string $filepath, array $opt=[]): self{
		$info = \ebi\Image::get_info($filepath);
		
		if($info['mime'] !== 'image/jpeg' && $info['mime'] !== 'image/png'){
			throw new \tt\pdf\exception\ImageException('image not supported');
		}
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
	 * 
	 * opt:
	 *  integer $angle 回転角度
	 */
	public function add_svg(float $x, float $y, float $width, float $height, string $filepath, array$opt=[]): self{
		$this->rotate($x, $y, $opt);
		
		$this->pdf->ImageSVG($filepath,$x,$y,$width,$height);
		
		$this->pdf->StopTransform();

		return $this;
	}
	
	/**
	 * SVGを文字列で追加
	 */
	public function add_svg_string(float $x, float $y, float $width, float $height, string $svg_string, $opt=[]): self{
		$this->rotate($x, $y, $opt);
		
		$this->pdf->ImageSVG('@'.$svg_string,$x,$y,$width,$height);
		
		$this->pdf->StopTransform();
		return $this;
	}
	
	/**
	 * PDFを追加
	 * 
	 * opt:
	 *  integer $angle 回転角度
	 *  float $scale 拡大率
	 *  integer $page_no 追加するページ番号
	 *  
	 */
	public function add_pdf(float $x, float $y, string $filepath, array $opt=[]): self{
		if(!is_file($filepath)){
			throw new \tt\pdf\exception\AccessDeniedException($filepath.' not found');
		}
		$this->rotate($x, $y, $opt);
		
		$width_pt = $height_pt = null;
		$scale = $opt['scale'] ?? 0;
		$page_no = $opt['page_no'] ?? 1;
		self::set_source($this->pdf, $filepath);
		$template_id = $this->pdf->importPage($page_no);

		if(!empty($scale)){
			$size = $this->pdf->getImportedPageSize($template_id);
			$width_pt = $size['width'] * $scale;
			$height_pt = $size['height'] * $scale;
		}
		$this->pdf->useTemplate($template_id,$x,$y,$width_pt,$height_pt);
		
		$this->pdf->StopTransform();
		return $this;
	}
	
	/**
	 * 線
	 * 
	 * opt:
	 *  string $border_color 線の色 #FFFFFF
	 *  float $border_width 線の太さ mm
	 *  float[] $dash 点線の長さ [5,2] mm
	 */
	public function add_line(float $sx, float $sy, float $ex, float $ey, array$opt=[]): self{
		$border_width = $opt['border_width'] ?? 0.1;
		$border_color = $this->color_dec($opt['border_color'] ?? ($opt['color'] ?? '#000000'));
		$style = [
			'width'=>$border_width,
			'color'=>$border_color,
		];

		if(isset($opt['dash']) && is_array($opt['dash'])){
			$style['dash'] = implode(',',$opt['dash']);
		}
		$this->pdf->SetLineStyle($style);
		$this->pdf->Line($sx,$sy,$ex,$ey);
		
		// reset
		$this->pdf->SetLineStyle([
			'width'=>0.2,
			'color'=>$this->color_dec('#000000'),
			'dash'=>0,
		]);
		
		return $this;
	}
	
	/**
	 * 矩形
	 * 
	 * opt:
	 *  boolean $fill true: 塗りつぶす
	 *  string $color 色 #000000 
	 *  string $border_color 線の色 #FFFFFF
	 *  float $border_width 線の太さ mm
	 * 
	 * @return $this
	 */
	public function add_rect(float $x, float $y, float $width, float $height, array $opt=[]): self{
		$style = ($opt['fill'] ?? false) ? 'F' : 'D';
		$color = $opt['color'] ?? '#000000';
		$color_rgb = $this->color_dec($color);
		$border_width = $opt['border_width'] ?? null;
		$border_rgb = $this->color_dec($opt['border_color'] ?? $color);
		
		$border_style = [];
		if($border_width !== null || $style === 'D'){
			$border_style = [
				'all'=>[
					'width'=>$border_width ?? 0.2,
					'color'=>$border_rgb,
				],
			];
			if($style === 'F'){
				$style = 'FD';
			}
		}
		
		$this->pdf->Rect($x,$y,$width,$height,$style,$border_style,$color_rgb);
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
		$style = ($opt['fill'] ?? false) ? 'F' : 'D';
		$color = $opt['color'] ?? '#000000';
		$color_rgb = $this->color_dec($color);
		$border_width = $opt['border_width'] ?? null;
		$border_rgb = $this->color_dec($opt['border_color'] ?? $color);
		
		$r = $diameter / 2;
		$x = $x + $r;
		$y = $y + $r;
		
		$border_style = [];
		if($border_width !== null || $style === 'D'){
			$border_style = [
				'width'=>$border_width ?? 0.2,
				'color'=>$border_rgb,
			];
			if($style === 'F'){
				$style = 'FD';
			}
		}
		
		$border_style = [
			'width'=>$border_width ?? 0.2,
			'color'=>$border_rgb,
		];
		$this->pdf->Ellipse($x, $y, $r,'',0,0,360,$style,$border_style,$color_rgb);
		
		return $this;
	}
		
	/**
	 * カラーモードからRGB（10進数）を返す
	 * @return R,G,B
	 */
	private function color_dec(string $color_code): array{
		if(is_array($color_code)){
			return [
				((float)$color_code[0] ?? 0) * 100,
				((float)$color_code[1] ?? 0) * 100,
				((float)$color_code[2] ?? 0) * 100,
				((float)$color_code[3] ?? 0) * 100
			];
		}
		if(substr($color_code,0,1) == '#'){
			$color_code = substr($color_code,1);
		}
		if($this->K100 && $color_code == '000000'){
			return [0,0,0,100];
		}
		$r = hexdec(substr($color_code,0,2));
		$g = hexdec(substr($color_code,2,2));
		$b = hexdec(substr($color_code,4,2));
		
		return [$r,$g,$b];
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
	 *  int $angle 回転角度
	 */
	public function add_textbox(float $x, float $y, float $width, float $height, string $text, array $opt=[]): self{
		$this->rotate($x, $y, $opt);
		
		$font_family = $opt['font_family'] ?? 'kozminproregular';
		$font_family = $this->font_names[$font_family] ?? $font_family;
		
		$font_size = $opt['font_size'] ?? 8;
		$color_code = $opt['color'] ?? '#000000';
		$text_spacing = $opt['text_spacing'] ?? 0;
		$text_leading = $opt['text_leading'] ?? $font_size;
		$align = $opt['align'] ?? 0;
		$valign = $opt['valign'] ?? 0;
		
		$this->pdf->SetFont($font_family,'',$font_size);
		$this->pdf->SetFontSpacing($text_spacing);
		
		$color_dec = $this->color_dec($color_code);
		$this->pdf->SetTextColor($color_dec[0],$color_dec[1],$color_dec[2]);
		
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
			0,
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
	 * ファイルに書き出す
	 */
	public function write(string $filename): void{
		if($this->pages === 0){
			throw new \tt\pdf\exception\NoPagesException();
		}
		$filename = \ebi\Util::path_absolute(getcwd(), $filename);
		\ebi\Util::mkdir(dirname($filename));
		
		$this->pdf->Output($filename,'F');
	}
	
	/**
	 * 出力
	 */
	public function output(?string $filename=null): void{
		if($this->pages === 0){
			throw new \tt\pdf\exception\NoPagesException();
		}
		if(empty($filename)){
			$filename = date('Ymd_his').'.pdf';
		}
		$this->pdf->Output($filename,'I');
	}
	
	/**
	 * ダウンロード
	 */
	public function download(?string $filename=null): void{
		if($this->pages === 0){
			throw new \tt\pdf\exception\NoPagesException();
		}
		if(empty($filename)){
			$filename = date('Ymd_his').'.pdf';
		}
		$this->pdf->Output($filename,'D');
	}
	
	/**
	 * ページサイズ mm
	 * @return array [page=>[width,height]]
	 */
	public static function get_page_size(string $filename): array{
		$pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
		$page_size = [];
		
		for($page=1;$page<=self::set_source($pdf, $filename);$page++){
			$template_id = $pdf->importPage($page);
			$size = $pdf->getImportedPageSize($template_id);
			
			$page_size[$page] = [$size['width'],$size['height']];
		}
		return $page_size;
	}
	
	private static function set_source(\setasign\Fpdi\Tcpdf\Fpdi $pdf, string $filename): int{
		try{
			return $pdf->setSourceFile($filename);
		}catch(\setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException $e){
			if($e->getCode() === \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::ENCRYPTED){
				throw new \tt\pdf\exception\EncryptedPdfDocumentException();
			}else if($e->getCode() === \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException::COMPRESSED_XREF){
				throw new \tt\pdf\exception\CompressionDocumentException();
			}
			throw $e;
		}catch(\Exception $e){
			throw new \tt\pdf\exception\AccessDeniedException();
		}
	}
	
	/**
	 * ページ毎に抽出
	 */
	public static function split(string $filename, int $start=1, int $end=null, ?float $pdf_version=null): \Generator{
		$page_size = self::get_page_size($filename);
		$num_pages = sizeof($page_size);
		
		if(empty($start)){
			$start = 1;
		}
		if(empty($end) || $num_pages < $end){
			$end = $num_pages;
		}
		for($page=$start;$page<=$end;$page++){
			$self = new static($pdf_version);
			
			self::set_source($self->pdf, $filename);
			$template_id = $self->pdf->importPage($page);
			$info = $self->pdf->getImportedPageSize($template_id);
			
			$self->add_page($info['width'],$info['height']);
			$self->pdf->useTemplate($template_id);
			
			yield $page=>$self;
		}
	}
}