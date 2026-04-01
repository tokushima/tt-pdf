<?php
namespace tt\pdf;

/**
 * 用紙サイズと単位変換
 */
class Unit{
	// 1pt = 0.352778mm, 1mm = 2.834645pt, 1in = 25.4mm = 72pt

	/**
	 * 用紙定義 [width(mm), height(mm), 日本語名]
	 */
	private const PAPERS = [
		// ISO A series
		'A0'  => [841, 1189, 'A0'],
		'A1'  => [594, 841, 'A1'],
		'A2'  => [420, 594, 'A2'],
		'A3'  => [297, 420, 'A3'],
		'A4'  => [210, 297, 'A4'],
		'A5'  => [148, 210, 'A5'],
		'A6'  => [105, 148, 'A6 (文庫本)'],
		'A7'  => [74, 105, 'A7'],
		'A8'  => [52, 74, 'A8'],
		'A9'  => [37, 52, 'A9'],
		'A10' => [26, 37, 'A10'],

		// ノビ（印刷用 余白・トンボ付き）
		'A3_NOBI' => [329, 483, 'A3ノビ'],
		'A2_NOBI' => [466, 660, 'A2ノビ'],
		'A1_NOBI' => [660, 917, 'A1ノビ'],

		// 大判インクジェット ロール幅
		'ROLL_17' => [431.8, 0, '17インチロール'],
		'ROLL_24' => [610, 0, '24インチロール'],
		'ROLL_36' => [914.4, 0, '36インチロール'],
		'ROLL_42' => [1067, 0, '42インチロール'],
		'ROLL_44' => [1118, 0, '44インチロール'],
		'ROLL_60' => [1524, 0, '60インチロール'],

		// 大判インクジェット 定型カットシート
		'SUPER_A3' => [329, 483, 'スーパーA3'],
		'SUPER_B'  => [330, 483, 'スーパーB'],
		'13X19'    => [330.2, 482.6, '13x19インチ'],
		'17X22'    => [431.8, 558.8, '17x22インチ'],
		'24X36'    => [610, 914.4, '24x36インチ'],

		// 写真プリント用（インクジェット）
		'PHOTO_A4_BORDERLESS'       => [210, 297, 'A4フチなし'],
		'PHOTO_A3_BORDERLESS'       => [297, 420, 'A3フチなし'],
		'PHOTO_A3_NOBI_BORDERLESS'  => [329, 483, 'A3ノビフチなし'],
		'PHOTO_6CUT'                => [203, 254, '六切'],
		'PHOTO_4CUT'                => [254, 305, '四切'],
		'PHOTO_W4CUT'               => [254, 365, 'ワイド四切'],

		// ISO B series
		'B0'  => [1000, 1414, 'B0 (ISO)'],
		'B1'  => [707, 1000, 'B1 (ISO)'],
		'B2'  => [500, 707, 'B2 (ISO)'],
		'B3'  => [353, 500, 'B3 (ISO)'],
		'B4'  => [250, 353, 'B4 (ISO)'],
		'B5'  => [176, 250, 'B5 (ISO)'],
		'B6'  => [125, 176, 'B6 (ISO)'],
		'B7'  => [88, 125, 'B7 (ISO)'],
		'B8'  => [62, 88, 'B8 (ISO)'],
		'B9'  => [44, 62, 'B9 (ISO)'],
		'B10' => [31, 44, 'B10 (ISO)'],

		// JIS B series
		'JIS_B0'  => [1030, 1456, 'B0 (JIS B列本判)'],
		'JIS_B1'  => [728, 1030, 'B1 (JIS)'],
		'JIS_B2'  => [515, 728, 'B2 (JIS)'],
		'JIS_B3'  => [364, 515, 'B3 (JIS)'],
		'JIS_B4'  => [257, 364, 'B4 (JIS)'],
		'JIS_B5'  => [182, 257, 'B5 (JIS)'],
		'JIS_B6'  => [128, 182, 'B6 (JIS 単行本)'],
		'JIS_B7'  => [91, 128, 'B7 (JIS 手帳)'],
		'JIS_B8'  => [64, 91, 'B8 (JIS)'],
		'JIS_B9'  => [45, 64, 'B9 (JIS)'],
		'JIS_B10' => [32, 45, 'B10 (JIS)'],

		// North American
		'LETTER'  => [215.9, 279.4, 'レター'],
		'LEGAL'   => [215.9, 355.6, 'リーガル'],
		'TABLOID' => [279.4, 431.8, 'タブロイド'],
		'LEDGER'  => [431.8, 279.4, 'レジャー'],

		// 日本の定形封筒
		'ENVELOPE_CHOU3'   => [120, 235, '長形3号封筒'],
		'ENVELOPE_CHOU4'   => [90, 205, '長形4号封筒'],
		'ENVELOPE_KAKU2'   => [240, 332, '角形2号封筒'],
		'ENVELOPE_KAKU3'   => [216, 277, '角形3号封筒'],
		'ENVELOPE_KAKU_A4' => [228, 312, '角形A4号封筒'],
		'ENVELOPE_YOU4'    => [105, 235, '洋形4号封筒'],

		// 日本のはがき
		'HAGAKI'   => [100, 148, 'はがき'],
		'HAGAKI_W' => [148, 200, '往復はがき'],

		// 名刺・カード
		'BUSINESS_CARD'    => [55, 91, '名刺 (日本サイズ)'],
		'BUSINESS_CARD_US' => [50.8, 88.9, '名刺 (欧米サイズ)'],
		'CREDIT_CARD'      => [53.98, 85.6, 'クレジットカード'],

		// 写真
		'PHOTO_L'   => [89, 127, 'L判'],
		'PHOTO_2L'  => [127, 178, '2L判'],
		'PHOTO_KG'  => [102, 152, 'KGサイズ'],
		'PHOTO_DSC' => [89, 119, 'DSCサイズ'],
		'PHOTO_6X4' => [152.4, 101.6, '6x4インチ'],

		// チラシ・フライヤー（仕上がりサイズ）
		'FLYER_A4' => [210, 297, 'チラシ A4'],
		'FLYER_A5' => [148, 210, 'チラシ A5'],
		'FLYER_A6' => [105, 148, 'チラシ A6'],
		'FLYER_B5' => [182, 257, 'チラシ B5'],

		// チケット
		'TICKET' => [174, 74, 'チケット'],

		// CD/DVD
		'CD_JACKET'  => [120, 120, 'CDジャケット'],
		'DVD_JACKET' => [183, 273, 'DVDジャケット'],

		// インスタントカメラ（チェキ）
		'INSTAX_MINI'         => [54, 86, 'チェキ (instax mini)'],
		'INSTAX_SQUARE'       => [72, 86, 'チェキスクエア (instax SQUARE)'],
		'INSTAX_WIDE'         => [108, 86, 'チェキワイド (instax WIDE)'],
		'INSTAX_MINI_IMAGE'   => [46, 62, 'チェキ 画像領域'],
		'INSTAX_SQUARE_IMAGE' => [62, 62, 'チェキスクエア 画像領域'],
		'INSTAX_WIDE_IMAGE'   => [99, 62, 'チェキワイド 画像領域'],

		// プリントシール（プリクラ）
		'PURIKURA_L'    => [40, 55, 'プリクラ 大'],
		'PURIKURA_M'    => [30, 40, 'プリクラ 中'],
		'PURIKURA_S'    => [22, 27, 'プリクラ 小'],
		'PURIKURA_SEAL' => [150, 105, 'プリクラシール台紙'],

		// ポラロイド
		'POLAROID_CLASSIC'       => [88, 107, 'ポラロイド (クラシック)'],
		'POLAROID_CLASSIC_IMAGE' => [79, 79, 'ポラロイド 画像領域'],
		'POLAROID_GO'            => [67, 54, 'ポラロイド Go'],

		// ラベル・シール
		'LABEL_A4_SHEET' => [210, 297, 'ラベルシート A4'],
		'LABEL_STANDARD' => [66, 33.9, 'ラベル (標準)'],
		'LABEL_ADDRESS'  => [91, 55, '宛名ラベル'],
		'LABEL_SHIPPING' => [99.1, 67.7, '配送ラベル'],

		// モバイルプリンター
		'CANON_SELPHY_L'      => [89, 119, 'Canon SELPHY Lサイズ'],
		'CANON_SELPHY_CARD'   => [54, 86, 'Canon SELPHY カードサイズ'],
		'CANON_SELPHY_SQUARE' => [68, 68, 'Canon SELPHY スクエア'],
	];

	/**
	 * mm -> pt
	 */
	public static function mm2pt(float ...$mm): array{
		$result = [];
		foreach($mm as $v){
			$result[] = $v * 2.83465;
		}
		return $result;
	}

	/**
	 * pt -> mm
	 */
	public static function pt2mm(float ...$pt): array{
		$result = [];
		foreach($pt as $v){
			$result[] = $v / 2.83465;
		}
		return $result;
	}

	/**
	 * inch -> mm
	 */
	public static function in2mm(float ...$in): array{
		$result = [];
		foreach($in as $v){
			$result[] = $v * 25.4;
		}
		return $result;
	}

	/**
	 * mm -> inch
	 */
	public static function mm2in(float ...$mm): array{
		$result = [];
		foreach($mm as $v){
			$result[] = $v / 25.4;
		}
		return $result;
	}

	/**
	 * inch -> pt
	 */
	public static function in2pt(float ...$in): array{
		$result = [];
		foreach($in as $v){
			$result[] = $v * 72;
		}
		return $result;
	}

	/**
	 * pt -> inch
	 */
	public static function pt2in(float ...$pt): array{
		$result = [];
		foreach($pt as $v){
			$result[] = $v / 72;
		}
		return $result;
	}

	/**
	 * px -> pt
	 */
	public static function px2pt(float $px, float $dpi=72): float{
		return ($px / $dpi * 72);
	}

	/**
	 * pt -> px
	 */
	public static function pt2px(float $pt, float $dpi=72): float{
		return ($pt / 72 * $dpi);
	}

	/**
	 * px -> mm
	 */
	public static function px2mm(float $px, float $dpi=72): float{
		return ($px / $dpi * 25.4);
	}

	/**
	 * mm -> px
	 */
	public static function mm2px(float $mm, float $dpi=72): float{
		return ($mm / 25.4 * $dpi);
	}

	/**
	 * 用紙定義を取得
	 */
	private static function get_paper(string $name): array{
		$name = strtoupper($name);

		if(!isset(self::PAPERS[$name])){
			throw new \InvalidArgumentException('Unknown paper size: '.$name);
		}
		return self::PAPERS[$name];
	}

	/**
	 * 用紙サイズを返す (mm)
	 * @return float[] [width, height]
	 */
	public static function paper_size(string $name): array{
		$paper = self::get_paper($name);
		return [$paper[0], $paper[1]];
	}

	/**
	 * 用紙サイズを返す (pt)
	 * @return float[] [width, height]
	 */
	public static function paper_size_pt(string $name): array{
		[$w, $h] = self::paper_size($name);
		return self::mm2pt($w, $h);
	}

	/**
	 * 用紙サイズの日本での呼び名を返す
	 */
	public static function paper_name_ja(string $name): string{
		$paper = self::get_paper($name);
		return $paper[2];
	}

	/**
	 * 利用可能な用紙サイズ名の一覧
	 * @return string[]
	 */
	public static function paper_names(): array{
		return array_keys(self::PAPERS);
	}
}
