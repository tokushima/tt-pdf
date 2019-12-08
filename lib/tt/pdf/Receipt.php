<?php
namespace tt\pdf;

class Receipt extends \tt\pdf\Pdf{
	private $title;
	
	private $seal;
	private $order_date;
	private $order_no;
	private $shipping_date;
	private $statement = [];
	private $amount;
	
	public function __construct($title='領収書'){
		parent::__construct();
		
		$this->title = $title;
		$this->pdf->AddPage('P',\ebi\Calc::get_size_mm('A4'));
		$this->pdf->SetMargins(0,0,0);
	}
	
	public function seal($img_path){
		$info = \ebi\Image::get_info($img_path);
		
		if($info['mime'] !== 'image/jpeg' && $info['mime'] !== 'image/png'){
			throw new \ebi\exception\ImageException('image not supported');
		}
		$this->seal = $img_path;
	}
	public function order_date($date){
		$this->order_date = $date;
	}
	public function order_no($order_no){
		$this->order_no = $order_no;
	}
	public function shipping_date($date){
		$this->shipping_date = $date;
	}

	public function item($name,$price,$qty=1){
		$this->statement[] = [$name,$price,$qty];
	}
	public function amount($amount){
		$this->amount = $amount;
	}
	
	protected function before(){
		// w, h , text, border, LN, aling(L C R J), fill, link(url), stretch font stretch mode:
		
		if(!empty($this->seal)){
			$info = \ebi\Image::get_info($this->seal);
			
			if($info['orientation'] == \ebi\Image::ORIENTATION_LANDSCAPE){
				list($w,$h) = [\ebi\Calc::mm2px(20,72),null];
			}else{
				list($w,$h) = [null,\ebi\Calc::mm2px(10,72)];
			}
			$this->pdf->SetXY(0,0);
			$this->pdf->Image($this->seal,0,0,$w,$h);
		}
		
		$this->set_cell_text(160,0,'発行日',date('Y年m月d日'));
		
		
		$this->pdf->SetXY(0,30);
		$this->pdf->SetFont('kozgopromedium', 'B', 24);
		$this->pdf->Cell(0, 0,$this->title,0,true,'C');
		
		
		$this->pdf->SetXY(0,40);
		$this->pdf->SetFont('kozgopromedium', 'B', 12);
		$this->pdf->Cell(0, 0,'注文日',0,true,'L');
		$this->pdf->SetFont('kozgopromedium', 'B', 12);
		$this->pdf->Cell(0, 0,'注文日',0,true,'L');
		
		
		$this->pdf->SetXY(0,70);
		$this->pdf->SetFont('kozgopromedium','B', 11);
		$this->pdf->setCellPaddings(2,1,2,1);
		$this->pdf->Cell(150, 0,'注文商品',0,0,'L');
		
		$this->pdf->SetFont('kozgopromedium','B', 11);
		$this->pdf->setCellPaddings(2,1,2,1);
		$this->pdf->Cell(50, 0,'価格',0,1,'R');
		
		foreach($this->statement as $item){
			$this->pdf->SetFont('kozgopromedium','', 11);
			$this->pdf->setCellPaddings(2,1,2,1);
			$this->pdf->Cell(150, 0,number_format($item[2]).'点　'.$item[0],0,0,'L');
			
			$this->pdf->SetFont('kozgopromedium','', 11);
			$this->pdf->setCellPaddings(2,1,2,1);
			$this->pdf->Cell(50, 0,'¥'.number_format($item[1] * $item[2]),0,1,'R');
		}
		
		$this->pdf->lastPage();
	}
	
	protected function set_cell_text($x,$y,$label,$text,$fontsize=12){
		$this->pdf->SetXY($x,$y);
		$this->pdf->SetFont('kozgopromedium', 'B', 12);
		$this->pdf->Cell(100, 0,$label,0,0,'L');
		$this->pdf->SetFont('kozgopromedium', 'B', 12);
		$this->pdf->Cell(100, 0,$text,0,0,'L');
	}
}