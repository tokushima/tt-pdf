<?php
/**
 * PDFlib 11 PHP Stub for IDE autocompletion
 *
 * PDFlib extension (php_pdflib) が提供するクラスのスタブファイル。
 * このファイルは実行されず、VSCode (Intelephense / PHP Intelephense) の補完用。
 *
 * @see https://www.pdflib.com/
 * @version PDFlib 11.0.0
 */

/**
 * PDFlib で発生する例外
 */
class PDFlibException extends \Exception {
	/**
	 * 最後に発生した例外のエラー番号を取得する
	 *
	 * @return int エラー番号
	 */
	public function get_errnum(): int { return 0; }

	/**
	 * 最後に発生した例外のエラーメッセージを取得する
	 *
	 * @return string エラーメッセージ
	 */
	public function get_errmsg(): string { return ''; }

	/**
	 * 例外を発生させたAPIメソッド名を取得する
	 *
	 * @return string APIメソッド名
	 */
	public function get_apiname(): string { return ''; }
}


/**
 * PDFlib - PDF生成ライブラリ
 *
 * PDFlib GmbH が提供する PDF 生成用 PHP 拡張のクラス。
 * 座標系は左下原点 (pt単位)。
 *
 * @see https://www.pdflib.com/documentation/
 */
class PDFlib {

	// =========================================================================
	// Setup
	// =========================================================================

	/**
	 * グローバルオプションを設定する
	 *
	 * よく使うオプション:
	 * - "license=<key>" : ライセンスキーの設定
	 * - "stringformat=utf8" : 文字列をUTF-8で扱う
	 * - "SearchPath={<path>}" : リソース検索パス
	 * - "FontOutline={<alias>=<fontfile>}" : フォントファイルのエイリアス登録
	 *
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function set_option(string $optlist): void {}

	/**
	 * オプション値を取得する
	 *
	 * @param string $keyword 取得するキーワード
	 * @param string $optlist オプションリスト
	 * @return float 要求されたオプション値
	 */
	public function get_option(string $keyword, string $optlist): float { return 0.0; }

	/**
	 * 文字列インデックスから文字列値を取得する
	 *
	 * @param int $idx 文字列インデックス (get_option等で取得)
	 * @param string $optlist オプションリスト
	 * @return string 文字列値
	 */
	public function get_string(int $idx, string $optlist): string { return ''; }

	// =========================================================================
	// Document
	// =========================================================================

	/**
	 * PDFドキュメントを新規作成する
	 *
	 * ファイル名が空文字の場合はメモリ内にPDFを生成し、get_buffer()で取得可能。
	 *
	 * よく使うオプション:
	 * - "compatibility=1.7" : PDFバージョン (1.4, 1.5, 1.6, 1.7, 2.0)
	 * - "optimize=true" : オブジェクトの再利用による最適化
	 * - "linearize=true" : Web表示用に最適化（リニアライズ）
	 *
	 * @param string $filename 出力ファイルパス (空文字でメモリ出力)
	 * @param string $optlist オプションリスト
	 * @return int 成功時は1以上、エラー時は0
	 */
	public function begin_document(string $filename, string $optlist): int { return 0; }

	/**
	 * PDFドキュメントを閉じる
	 *
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function end_document(string $optlist): void {}

	/**
	 * PDFバッファの内容を取得する (begin_documentでファイル名を空にした場合)
	 *
	 * @return string バイナリPDFデータ
	 */
	public function get_buffer(): string { return ''; }

	/**
	 * ドキュメント情報フィールドを設定する
	 *
	 * @param string $key フィールド名 ("Author", "Creator", "Title", "Subject", "Keywords")
	 * @param string $value 値
	 * @return void
	 */
	public function set_info(string $key, string $value): void {}

	// =========================================================================
	// Page
	// =========================================================================

	/**
	 * 新しいページを追加する
	 *
	 * @param float $width ページ幅 (pt)
	 * @param float $height ページ高さ (pt)
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function begin_page_ext(float $width, float $height, string $optlist): void {}

	/**
	 * 現在のページを終了する
	 *
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function end_page_ext(string $optlist): void {}

	/**
	 * 中断されたページを再開する
	 *
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function resume_page(string $optlist): void {}

	/**
	 * 現在のページを中断する (後でresume_pageで再開可能)
	 *
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function suspend_page(string $optlist): void {}

	// =========================================================================
	// Font
	// =========================================================================

	/**
	 * フォントを検索して読み込む
	 *
	 * @param string $fontname フォント名またはファイル名
	 * @param string $encoding エンコーディング ("unicode", "winansi" 等)
	 * @param string $optlist オプションリスト ("embedding=true" でフォント埋め込み)
	 * @return int フォントハンドル
	 */
	public function load_font(string $fontname, string $encoding, string $optlist): int { return 0; }

	/**
	 * 現在のフォントとサイズを設定する
	 *
	 * @param int $font フォントハンドル
	 * @param float $fontsize フォントサイズ (pt)
	 * @return void
	 */
	public function setfont(int $font, float $fontsize): void {}

	/**
	 * フォントの詳細情報を問い合わせる
	 *
	 * @param int $font フォントハンドル
	 * @param string $keyword 問い合わせキーワード
	 * @param string $optlist オプションリスト
	 * @return float 要求されたフォントプロパティの値
	 */
	public function info_font(int $font, string $keyword, string $optlist): float { return 0.0; }

	/**
	 * 未使用のフォントハンドルを閉じる
	 *
	 * @param int $font フォントハンドル
	 * @return void
	 */
	public function close_font(int $font): void {}

	// =========================================================================
	// Text Output
	// =========================================================================

	/**
	 * Textflowオブジェクトを作成する
	 *
	 * テキストとインラインオプションからTextflowを生成。
	 * 生成したTextflowは fit_textflow() で配置する。
	 *
	 * よく使うオプション:
	 * - "fontname=<name>" : フォント名
	 * - "fontsize=<size>" : フォントサイズ
	 * - "encoding=unicode" : エンコーディング
	 * - "embedding=true" : フォント埋め込み
	 * - "fillcolor={rgb <r> <g> <b>}" : テキスト色
	 * - "alignment=left|center|right|justify" : 行揃え
	 * - "leading=<size>" : 行間
	 * - "charspacing=<value>" : 文字間隔
	 * - "charref=true" : 文字参照を有効にする
	 * - "hyphenchar=none" : ハイフネーションを無効にする
	 *
	 * @param string $text テキスト (htmlentitiesでエスケープ済みのテキスト)
	 * @param string $optlist オプションリスト
	 * @return int Textflowハンドル。エラー時は0
	 */
	public function create_textflow(string $text, string $optlist): int { return 0; }

	/**
	 * Textflowの次の部分をフォーマットして配置する
	 *
	 * よく使うオプション:
	 * - "verticalalign=top|center|bottom" : 垂直方向の配置
	 * - "firstlinedist=ascender" : 最初の行の基準
	 * - "lastlinedist=descender" : 最後の行の基準
	 * - "rotate=<angle>" : 回転角度
	 *
	 * @param int $textflow Textflowハンドル
	 * @param float $llx 左下X座標 (pt)
	 * @param float $lly 左下Y座標 (pt)
	 * @param float $urx 右上X座標 (pt)
	 * @param float $ury 右上Y座標 (pt)
	 * @param string $optlist オプションリスト
	 * @return string 終了理由 ("_stop", "_nextpage", "_boxfull", "_boxempty" 等)
	 */
	public function fit_textflow(int $textflow, float $llx, float $lly, float $urx, float $ury, string $optlist): string { return ''; }

	/**
	 * 既存のTextflowにテキストとオプションを追加する
	 *
	 * @param int $textflow Textflowハンドル (0で新規作成)
	 * @param string $text 追加テキスト
	 * @param string $optlist オプションリスト
	 * @return int Textflowハンドル。エラー時は0
	 */
	public function add_textflow(int $textflow, string $text, string $optlist): int { return 0; }

	/**
	 * Textflowの状態を問い合わせる
	 *
	 * @param int $textflow Textflowハンドル
	 * @param string $keyword 問い合わせキーワード
	 * @return float 要求されたTextflowパラメータの値
	 */
	public function info_textflow(int $textflow, string $keyword): float { return 0.0; }

	/**
	 * Textflowを削除してリソースを解放する
	 *
	 * @param int $textflow Textflowハンドル
	 * @return void
	 */
	public function delete_textflow(int $textflow): void {}

	/**
	 * テキストを1行配置する
	 *
	 * @param string $text テキスト
	 * @param float $x X座標 (pt)
	 * @param float $y Y座標 (pt)
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function fit_textline(string $text, float $x, float $y, string $optlist): void {}

	/**
	 * テキスト幅を計算する
	 *
	 * @param string $text テキスト
	 * @param int $font フォントハンドル
	 * @param float $fontsize フォントサイズ
	 * @return float テキストの幅 (pt)
	 */
	public function stringwidth(string $text, int $font, float $fontsize): float { return 0.0; }

	/**
	 * テキスト行のメトリクスを問い合わせる (出力なし)
	 *
	 * @param string $text テキスト
	 * @param string $keyword 問い合わせキーワード
	 * @param string $optlist オプションリスト
	 * @return float 要求されたメトリクス値
	 */
	public function info_textline(string $text, string $keyword, string $optlist): float { return 0.0; }

	/**
	 * 現在位置にテキストを出力する
	 *
	 * @param string $text テキスト
	 * @return void
	 */
	public function show(string $text): void {}

	/**
	 * 指定座標にテキストを出力する
	 *
	 * @param string $text テキスト
	 * @param float $x X座標
	 * @param float $y Y座標
	 * @return void
	 */
	public function show_xy(string $text, float $x, float $y): void {}

	/**
	 * 次の行にテキストを出力する
	 *
	 * @param string $text テキスト
	 * @return void
	 */
	public function continue_text(string $text): void {}

	/**
	 * テキスト出力の位置を設定する
	 *
	 * @param float $x X座標
	 * @param float $y Y座標
	 * @return void
	 */
	public function set_text_pos(float $x, float $y): void {}

	/**
	 * テキストフィルター・テキスト外観オプションを設定する
	 *
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function set_text_option(string $optlist): void {}

	// =========================================================================
	// Table
	// =========================================================================

	/**
	 * テーブルにセルを追加する
	 *
	 * @param int $table テーブルハンドル (0で新規作成)
	 * @param int $column カラム番号 (1から)
	 * @param int $row 行番号 (1から)
	 * @param string $text セルのテキスト
	 * @param string $optlist オプションリスト
	 * @return int テーブルハンドル
	 */
	public function add_table_cell(int $table, int $column, int $row, string $text, string $optlist): int { return 0; }

	/**
	 * テーブルをページ上に配置する
	 *
	 * @param int $table テーブルハンドル
	 * @param float $llx 左下X座標
	 * @param float $lly 左下Y座標
	 * @param float $urx 右上X座標
	 * @param float $ury 右上Y座標
	 * @param string $optlist オプションリスト
	 * @return string 終了理由
	 */
	public function fit_table(int $table, float $llx, float $lly, float $urx, float $ury, string $optlist): string { return ''; }

	/**
	 * テーブル情報を問い合わせる
	 *
	 * @param int $table テーブルハンドル
	 * @param string $keyword 問い合わせキーワード
	 * @return float 要求されたテーブルパラメータの値
	 */
	public function info_table(int $table, string $keyword): float { return 0.0; }

	/**
	 * テーブルを削除してリソースを解放する
	 *
	 * @param int $table テーブルハンドル
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function delete_table(int $table, string $optlist): void {}

	// =========================================================================
	// Graphics State
	// =========================================================================

	/**
	 * 現在のグラフィック状態をスタックに保存する
	 *
	 * restore() とペアで使用する。
	 *
	 * @return void
	 */
	public function save(): void {}

	/**
	 * スタックから最後に保存したグラフィック状態を復元する
	 *
	 * save() とペアで使用する。
	 *
	 * @return void
	 */
	public function restore(): void {}

	/**
	 * グラフィック外観オプションを設定する
	 *
	 * よく使うオプション:
	 * - "dasharray={<on> <off>}" : 破線パターン (pt単位)
	 * - "linecap=0|1|2" : 線端スタイル
	 * - "linejoin=0|1|2" : 接合スタイル
	 *
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function set_graphics_option(string $optlist): void {}

	/**
	 * グラフィック状態オブジェクトを作成する
	 *
	 * よく使うオプション:
	 * - "opacityfill=<0-1>" : 塗りの透明度
	 * - "opacitystroke=<0-1>" : 線の透明度
	 *
	 * @param string $optlist オプションリスト
	 * @return int グラフィック状態ハンドル
	 */
	public function create_gstate(string $optlist): int { return 0; }

	/**
	 * グラフィック状態オブジェクトを適用する
	 *
	 * @param int $gstate グラフィック状態ハンドル
	 * @return void
	 */
	public function set_gstate(int $gstate): void {}

	// =========================================================================
	// Color
	// =========================================================================

	/**
	 * 色空間と色を設定する
	 *
	 * @param string $fstype 対象 ("fill", "stroke", "fillstroke")
	 * @param string $colorspace 色空間 ("rgb", "cmyk", "gray", "spot", "pattern")
	 * @param float $c1 色成分1 (RGB: R 0-1, CMYK: C 0-1, Gray: 0-1)
	 * @param float $c2 色成分2 (RGB: G 0-1, CMYK: M 0-1)
	 * @param float $c3 色成分3 (RGB: B 0-1, CMYK: Y 0-1)
	 * @param float $c4 色成分4 (CMYK: K 0-1, それ以外は0)
	 * @return void
	 */
	public function setcolor(string $fstype, string $colorspace, float $c1, float $c2, float $c3, float $c4): void {}

	// =========================================================================
	// Drawing (Path)
	// =========================================================================

	/**
	 * 現在のポイントを移動する (パスの開始点)
	 *
	 * @param float $x X座標 (pt)
	 * @param float $y Y座標 (pt)
	 * @return void
	 */
	public function moveto(float $x, float $y): void {}

	/**
	 * 現在のポイントから指定座標まで直線を描く
	 *
	 * @param float $x X座標 (pt)
	 * @param float $y Y座標 (pt)
	 * @return void
	 */
	public function lineto(float $x, float $y): void {}

	/**
	 * ベジェ曲線を描く (3つの制御点)
	 *
	 * @param float $x1 制御点1 X
	 * @param float $y1 制御点1 Y
	 * @param float $x2 制御点2 X
	 * @param float $y2 制御点2 Y
	 * @param float $x3 終点 X
	 * @param float $y3 終点 Y
	 * @return void
	 */
	public function curveto(float $x1, float $y1, float $x2, float $y2, float $x3, float $y3): void {}

	/**
	 * 矩形パスを描く
	 *
	 * @param float $x 左下X座標 (pt)
	 * @param float $y 左下Y座標 (pt)
	 * @param float $width 幅 (pt)
	 * @param float $height 高さ (pt)
	 * @return void
	 */
	public function rect(float $x, float $y, float $width, float $height): void {}

	/**
	 * 円パスを描く
	 *
	 * @param float $x 中心X座標 (pt)
	 * @param float $y 中心Y座標 (pt)
	 * @param float $r 半径 (pt)
	 * @return void
	 */
	public function circle(float $x, float $y, float $r): void {}

	/**
	 * 楕円パスを描く
	 *
	 * @param float $x 中心X座標
	 * @param float $y 中心Y座標
	 * @param float $rx X方向の半径
	 * @param float $ry Y方向の半径
	 * @return void
	 */
	public function ellipse(float $x, float $y, float $rx, float $ry): void {}

	/**
	 * 反時計回りの円弧を描く
	 *
	 * @param float $x 中心X座標
	 * @param float $y 中心Y座標
	 * @param float $r 半径
	 * @param float $alpha 開始角度 (度)
	 * @param float $beta 終了角度 (度)
	 * @return void
	 */
	public function arc(float $x, float $y, float $r, float $alpha, float $beta): void {}

	/**
	 * 時計回りの円弧を描く
	 *
	 * @param float $x 中心X座標
	 * @param float $y 中心Y座標
	 * @param float $r 半径
	 * @param float $alpha 開始角度 (度)
	 * @param float $beta 終了角度 (度)
	 * @return void
	 */
	public function arcn(float $x, float $y, float $r, float $alpha, float $beta): void {}

	/**
	 * 現在のパスを閉じる (始点に戻る)
	 *
	 * @return void
	 */
	public function closepath(): void {}

	/**
	 * パスを現在の線色・線幅でストロークする
	 *
	 * @return void
	 */
	public function stroke(): void {}

	/**
	 * パス内部を現在の塗り色で塗りつぶす
	 *
	 * @return void
	 */
	public function fill(): void {}

	/**
	 * パスを塗りつぶしてからストロークする
	 *
	 * @return void
	 */
	public function fill_stroke(): void {}

	/**
	 * パスを閉じてストロークする
	 *
	 * @return void
	 */
	public function closepath_stroke(): void {}

	/**
	 * パスを閉じて塗りつぶしてストロークする
	 *
	 * @return void
	 */
	public function closepath_fill_stroke(): void {}

	/**
	 * パスをクリッピングパスとして使用する
	 *
	 * @return void
	 */
	public function clip(): void {}

	/**
	 * パスを出力せずに終了する
	 *
	 * @return void
	 */
	public function endpath(): void {}

	/**
	 * 線幅を設定する
	 *
	 * @param float $width 線幅 (pt)
	 * @return void
	 */
	public function setlinewidth(float $width): void {}

	// =========================================================================
	// Path Objects
	// =========================================================================

	/**
	 * パスオブジェクトにポイントを追加する
	 *
	 * @param int $path パスハンドル (0で新規作成)
	 * @param float $x X座標
	 * @param float $y Y座標
	 * @param string $type ポイントタイプ ("move", "line", "curve" 等)
	 * @param string $optlist オプションリスト
	 * @return int パスハンドル
	 */
	public function add_path_point(int $path, float $x, float $y, string $type, string $optlist): int { return 0; }

	/**
	 * パスオブジェクトを描画する
	 *
	 * @param int $path パスハンドル
	 * @param float $x X座標
	 * @param float $y Y座標
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function draw_path(int $path, float $x, float $y, string $optlist): void {}

	/**
	 * パスオブジェクトのメトリクスを問い合わせる
	 *
	 * @param int $path パスハンドル
	 * @param string $keyword 問い合わせキーワード
	 * @param string $optlist オプションリスト
	 * @return float 要求された値
	 */
	public function info_path(int $path, string $keyword, string $optlist): float { return 0.0; }

	/**
	 * パスオブジェクトを削除する
	 *
	 * @param int $path パスハンドル
	 * @return void
	 */
	public function delete_path(int $path): void {}

	// =========================================================================
	// Coordinate System Transformation
	// =========================================================================

	/**
	 * 座標系を回転する
	 *
	 * @param float $phi 回転角度 (度、反時計回り)
	 * @return void
	 */
	public function rotate(float $phi): void {}

	/**
	 * 座標系を拡大縮小する
	 *
	 * @param float $sx X方向のスケール
	 * @param float $sy Y方向のスケール
	 * @return void
	 */
	public function scale(float $sx, float $sy): void {}

	/**
	 * 座標系の原点を移動する
	 *
	 * @param float $tx X方向の移動量 (pt)
	 * @param float $ty Y方向の移動量 (pt)
	 * @return void
	 */
	public function translate(float $tx, float $ty): void {}

	/**
	 * 座標系をせん断変換する
	 *
	 * @param float $alpha X方向のせん断角度
	 * @param float $beta Y方向のせん断角度
	 * @return void
	 */
	public function skew(float $alpha, float $beta): void {}

	/**
	 * 変換行列を適用する
	 *
	 * @param float $a 行列要素
	 * @param float $b 行列要素
	 * @param float $c 行列要素
	 * @param float $d 行列要素
	 * @param float $e 行列要素
	 * @param float $f 行列要素
	 * @return void
	 */
	public function concat(float $a, float $b, float $c, float $d, float $e, float $f): void {}

	/**
	 * 変換行列を明示的に設定する
	 *
	 * @param float $a 行列要素
	 * @param float $b 行列要素
	 * @param float $c 行列要素
	 * @param float $d 行列要素
	 * @param float $e 行列要素
	 * @param float $f 行列要素
	 * @return void
	 */
	public function setmatrix(float $a, float $b, float $c, float $d, float $e, float $f): void {}

	/**
	 * 相対ベクトルで座標系を整列する
	 *
	 * @param float $dx X方向
	 * @param float $dy Y方向
	 * @return void
	 */
	public function align(float $dx, float $dy): void {}

	// =========================================================================
	// Image
	// =========================================================================

	/**
	 * 画像ファイルを読み込む
	 *
	 * @param string $imagetype 画像タイプ ("auto", "jpeg", "png", "tiff", "gif", "bmp" 等)
	 * @param string $filename ファイルパス
	 * @param string $optlist オプションリスト ("iccprofile=<handle>" 等)
	 * @return int 画像ハンドル。エラー時は0
	 */
	public function load_image(string $imagetype, string $filename, string $optlist): int { return 0; }

	/**
	 * 画像またはテンプレートをページ上に配置する
	 *
	 * よく使うオプション:
	 * - "dpi=<value>" : 解像度
	 * - "scale=<value>" : 拡大縮小率
	 * - "rotate=<angle>" : 回転角度
	 * - "boxsize={<w> <h>}" : ボックスサイズ
	 * - "position=center" : 配置位置
	 * - "fitmethod=meet|slice|entire|clip" : フィット方法
	 *
	 * @param int $image 画像ハンドル
	 * @param float $x X座標 (pt)
	 * @param float $y Y座標 (pt)
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function fit_image(int $image, float $x, float $y, string $optlist): void {}

	/**
	 * 画像のメトリクスを問い合わせる
	 *
	 * @param int $image 画像ハンドル
	 * @param string $keyword 問い合わせキーワード
	 * @param string $optlist オプションリスト
	 * @return float 要求された値
	 */
	public function info_image(int $image, string $keyword, string $optlist): float { return 0.0; }

	/**
	 * 画像またはテンプレートを閉じる
	 *
	 * @param int $image 画像ハンドル
	 * @return void
	 */
	public function close_image(int $image): void {}

	// =========================================================================
	// SVG / Vector Graphics
	// =========================================================================

	/**
	 * ベクターグラフィックスファイル (SVG等) を読み込む
	 *
	 * @param string $type グラフィックスタイプ ("auto", "svg" 等)
	 * @param string $filename ファイルパス (PVFファイル名も可)
	 * @param string $optlist オプションリスト
	 * @return int グラフィックスハンドル。エラー時は0
	 */
	public function load_graphics(string $type, string $filename, string $optlist): int { return 0; }

	/**
	 * ベクターグラフィックスをページ上に配置する
	 *
	 * よく使うオプション:
	 * - "boxsize={<w> <h>}" : ボックスサイズ
	 * - "position=center" : 配置位置
	 * - "fitmethod=meet" : フィット方法
	 * - "rotate=<angle>" : 回転角度
	 *
	 * @param int $graphics グラフィックスハンドル
	 * @param float $x X座標 (pt)
	 * @param float $y Y座標 (pt)
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function fit_graphics(int $graphics, float $x, float $y, string $optlist): void {}

	/**
	 * ベクターグラフィックスのメトリクスを問い合わせる
	 *
	 * @param int $graphics グラフィックスハンドル
	 * @param string $keyword 問い合わせキーワード
	 * @param string $optlist オプションリスト
	 * @return float 要求された値
	 */
	public function info_graphics(int $graphics, string $keyword, string $optlist): float { return 0.0; }

	/**
	 * ベクターグラフィックスを閉じる
	 *
	 * @param int $graphics グラフィックスハンドル
	 * @return void
	 */
	public function close_graphics(int $graphics): void {}

	// =========================================================================
	// PDI (PDF Import)
	// =========================================================================

	/**
	 * 既存のPDFドキュメントを読み込む
	 *
	 * @param string $filename PDFファイルパス
	 * @param string $optlist オプションリスト
	 * @return int PDIドキュメントハンドル
	 */
	public function open_pdi_document(string $filename, string $optlist): int { return 0; }

	/**
	 * PDIドキュメントのページを開く
	 *
	 * @param int $doc PDIドキュメントハンドル
	 * @param int $pagenumber ページ番号 (1から)
	 * @param string $optlist オプションリスト
	 * @return int ページハンドル
	 */
	public function open_pdi_page(int $doc, int $pagenumber, string $optlist): int { return 0; }

	/**
	 * PDIページをページ上に配置する
	 *
	 * @param int $page ページハンドル
	 * @param float $x X座標 (pt)
	 * @param float $y Y座標 (pt)
	 * @param string $optlist オプションリスト ("scale=<value>", "rotate=<angle>" 等)
	 * @return void
	 */
	public function fit_pdi_page(int $page, float $x, float $y, string $optlist): void {}

	/**
	 * PDIページのメトリクスを問い合わせる
	 *
	 * よく使うキーワード: "width", "height"
	 *
	 * @param int $page ページハンドル
	 * @param string $keyword 問い合わせキーワード ("width", "height" 等)
	 * @param string $optlist オプションリスト
	 * @return float 要求されたページメトリクスの値 (pt)
	 */
	public function info_pdi_page(int $page, string $keyword, string $optlist): float { return 0.0; }

	/**
	 * PDIページハンドルを閉じてリソースを解放する
	 *
	 * @param int $page ページハンドル
	 * @return void
	 */
	public function close_pdi_page(int $page): void {}

	/**
	 * PDIドキュメントを閉じる
	 *
	 * @param int $doc PDIドキュメントハンドル
	 * @return void
	 */
	public function close_pdi_document(int $doc): void {}

	// =========================================================================
	// pCOS (PDF Object Access)
	// =========================================================================

	/**
	 * pCOSパスの数値を取得する
	 *
	 * よく使うパス:
	 * - "length:pages" : ページ数
	 * - "pages[<n>]/width" : ページ幅 (pt)
	 * - "pages[<n>]/height" : ページ高さ (pt)
	 *
	 * @param int $doc PDIドキュメントハンドル
	 * @param string $path pCOSパス
	 * @return float pCOSパスで指定されたオブジェクトの数値
	 */
	public function pcos_get_number(int $doc, string $path): float { return 0.0; }

	/**
	 * pCOSパスの文字列値を取得する
	 *
	 * よく使うパス:
	 * - "pdfversionstring" : PDFバージョン文字列
	 *
	 * @param int $doc PDIドキュメントハンドル
	 * @param string $path pCOSパス
	 * @return string pCOSパスで指定されたオブジェクトの文字列
	 */
	public function pcos_get_string(int $doc, string $path): string { return ''; }

	/**
	 * pCOSパスのストリームデータを取得する
	 *
	 * @param int $doc PDIドキュメントハンドル
	 * @param string $optlist オプションリスト
	 * @param string $path pCOSパス
	 * @return string ストリームデータ
	 */
	public function pcos_get_stream(int $doc, string $optlist, string $path): string { return ''; }

	// =========================================================================
	// ICC Profile
	// =========================================================================

	/**
	 * ICCプロファイルを読み込む
	 *
	 * @param string $profilename プロファイル名またはファイルパス
	 * @param string $optlist オプションリスト
	 * @return int プロファイルハンドル。エラー時は0
	 */
	public function load_iccprofile(string $profilename, string $optlist): int { return 0; }

	// =========================================================================
	// PVF (PDFlib Virtual Filesystem)
	// =========================================================================

	/**
	 * メモリ上のデータから仮想ファイルを作成する
	 *
	 * SVGなどの文字列データをファイルとして扱える。
	 *
	 * @param string $filename 仮想ファイル名 (例: "pvf/image_0")
	 * @param string $data ファイルデータ
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function create_pvf(string $filename, string $data, string $optlist): void {}

	/**
	 * 仮想ファイルを削除する
	 *
	 * @param string $filename 仮想ファイル名
	 * @return int ファイルがロック中なら0、それ以外は1
	 */
	public function delete_pvf(string $filename): int { return 0; }

	/**
	 * 仮想ファイルのプロパティを問い合わせる
	 *
	 * @param string $filename 仮想ファイル名
	 * @param string $keyword 問い合わせキーワード
	 * @return float 要求された値
	 */
	public function info_pvf(string $filename, string $keyword): float { return 0.0; }

	// =========================================================================
	// Template
	// =========================================================================

	/**
	 * テンプレート定義を開始する
	 *
	 * @param float $width テンプレート幅 (pt, 0で後から設定)
	 * @param float $height テンプレート高さ (pt, 0で後から設定)
	 * @param string $optlist オプションリスト
	 * @return int テンプレートハンドル
	 */
	public function begin_template_ext(float $width, float $height, string $optlist): int { return 0; }

	/**
	 * テンプレート定義を終了する
	 *
	 * @param float $width テンプレート幅 (pt, 0で変更なし)
	 * @param float $height テンプレート高さ (pt, 0で変更なし)
	 * @return void
	 */
	public function end_template_ext(float $width, float $height): void {}

	// =========================================================================
	// Layer
	// =========================================================================

	/**
	 * レイヤーを定義する
	 *
	 * @param string $name レイヤー名
	 * @param string $optlist オプションリスト
	 * @return int レイヤーハンドル
	 */
	public function define_layer(string $name, string $optlist): int { return 0; }

	/**
	 * レイヤーを開始する
	 *
	 * @param int $layer レイヤーハンドル
	 * @return void
	 */
	public function begin_layer(int $layer): void {}

	/**
	 * すべてのアクティブなレイヤーを終了する
	 *
	 * @return void
	 */
	public function end_layer(): void {}

	/**
	 * レイヤーの依存関係を定義する
	 *
	 * @param string $type 依存タイプ
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function set_layer_dependency(string $type, string $optlist): void {}

	// =========================================================================
	// Bookmark / Action / Annotation
	// =========================================================================

	/**
	 * ブックマークを作成する
	 *
	 * @param string $text ブックマークテキスト
	 * @param string $optlist オプションリスト
	 * @return int ブックマークハンドル
	 */
	public function create_bookmark(string $text, string $optlist): int { return 0; }

	/**
	 * アクションを作成する
	 *
	 * @param string $type アクションタイプ ("GoTo", "URI", "JavaScript" 等)
	 * @param string $optlist オプションリスト
	 * @return int アクションハンドル
	 */
	public function create_action(string $type, string $optlist): int { return 0; }

	/**
	 * 注釈を作成する
	 *
	 * @param float $llx 左下X座標
	 * @param float $lly 左下Y座標
	 * @param float $urx 右上X座標
	 * @param float $ury 右上Y座標
	 * @param string $type 注釈タイプ ("Link", "Text" 等)
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function create_annotation(float $llx, float $lly, float $urx, float $ury, string $type, string $optlist): void {}

	/**
	 * 名前付きデスティネーションを作成する
	 *
	 * @param string $name デスティネーション名
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function add_nameddest(string $name, string $optlist): void {}

	// =========================================================================
	// Form Fields
	// =========================================================================

	/**
	 * フォームフィールドを作成する
	 *
	 * @param float $llx 左下X座標
	 * @param float $lly 左下Y座標
	 * @param float $urx 右上X座標
	 * @param float $ury 右上Y座標
	 * @param string $name フィールド名
	 * @param string $type フィールドタイプ ("textfield", "checkbox", "radiobutton" 等)
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function create_field(float $llx, float $lly, float $urx, float $ury, string $name, string $type, string $optlist): void {}

	/**
	 * フォームフィールドグループを作成する
	 *
	 * @param string $name グループ名
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function create_fieldgroup(string $name, string $optlist): void {}

	// =========================================================================
	// Shading
	// =========================================================================

	/**
	 * シェーディング (グラデーション) を定義する
	 *
	 * @param string $type シェーディングタイプ ("axial", "radial")
	 * @param float $x0 開始X
	 * @param float $y0 開始Y
	 * @param float $x1 終了X
	 * @param float $y1 終了Y
	 * @param float $c1 色成分1
	 * @param float $c2 色成分2
	 * @param float $c3 色成分3
	 * @param float $c4 色成分4
	 * @param string $optlist オプションリスト
	 * @return int シェーディングハンドル
	 */
	public function shading(string $type, float $x0, float $y0, float $x1, float $y1, float $c1, float $c2, float $c3, float $c4, string $optlist): int { return 0; }

	/**
	 * シェーディングパターンを定義する
	 *
	 * @param int $shading シェーディングハンドル
	 * @param string $optlist オプションリスト
	 * @return int パターンハンドル
	 */
	public function shading_pattern(int $shading, string $optlist): int { return 0; }

	/**
	 * シェーディングで領域を塗りつぶす
	 *
	 * @param int $shading シェーディングハンドル
	 * @return void
	 */
	public function shfill(int $shading): void {}

	// =========================================================================
	// Pattern
	// =========================================================================

	/**
	 * パターン定義を開始する
	 *
	 * @param float $width パターン幅
	 * @param float $height パターン高さ
	 * @param string $optlist オプションリスト
	 * @return int パターンハンドル
	 */
	public function begin_pattern_ext(float $width, float $height, string $optlist): int { return 0; }

	/**
	 * パターン定義を終了する
	 *
	 * @return void
	 */
	public function end_pattern(): void {}

	// =========================================================================
	// Color Management
	// =========================================================================

	/**
	 * スポットカラーを作成する
	 *
	 * @param string $spotname スポットカラー名
	 * @return int カラーハンドル
	 */
	public function makespotcolor(string $spotname): int { return 0; }

	/**
	 * DeviceN色空間を作成する
	 *
	 * @param string $optlist オプションリスト
	 * @return int DeviceN色空間ハンドル。エラー時は0
	 */
	public function create_devicen(string $optlist): int { return 0; }

	// =========================================================================
	// Unicode
	// =========================================================================

	/**
	 * 文字列を任意のエンコーディングからUnicodeに変換する
	 *
	 * @param string $inputformat 入力フォーマット ("utf8", "utf16" 等)
	 * @param string $inputstring 入力文字列
	 * @param string $optlist オプションリスト
	 * @return string 変換されたUnicode文字列
	 */
	public function convert_to_unicode(string $inputformat, string $inputstring, string $optlist): string { return ''; }

	// =========================================================================
	// Encoding
	// =========================================================================

	/**
	 * カスタム8bitエンコーディングにグリフを追加する
	 *
	 * @param string $encoding エンコーディング名
	 * @param int $slot スロット番号
	 * @param string $glyphname グリフ名
	 * @param int $uv Unicodeコードポイント
	 * @return void
	 */
	public function encoding_set_char(string $encoding, int $slot, string $glyphname, int $uv): void {}

	// =========================================================================
	// Block Filling (PPS)
	// =========================================================================

	/**
	 * テキストブロックを可変データで埋める
	 *
	 * @param int $page ページハンドル
	 * @param string $blockname ブロック名
	 * @param string $text テキスト
	 * @param string $optlist オプションリスト
	 * @return int エラー時は0、成功時は1
	 */
	public function fill_textblock(int $page, string $blockname, string $text, string $optlist): int { return 0; }

	/**
	 * 画像ブロックを可変データで埋める
	 *
	 * @param int $page ページハンドル
	 * @param string $blockname ブロック名
	 * @param int $image 画像ハンドル
	 * @param string $optlist オプションリスト
	 * @return int エラー時は0、成功時は1
	 */
	public function fill_imageblock(int $page, string $blockname, int $image, string $optlist): int { return 0; }

	/**
	 * PDFブロックを可変データで埋める
	 *
	 * @param int $page ページハンドル
	 * @param string $blockname ブロック名
	 * @param int $contents コンテンツハンドル
	 * @param string $optlist オプションリスト
	 * @return int エラー時は0、成功時は1
	 */
	public function fill_pdfblock(int $page, string $blockname, int $contents, string $optlist): int { return 0; }

	/**
	 * グラフィックスブロックを可変データで埋める
	 *
	 * @param int $page ページハンドル
	 * @param string $blockname ブロック名
	 * @param int $graphics グラフィックスハンドル
	 * @param string $optlist オプションリスト
	 * @return int エラー時は0、成功時は1
	 */
	public function fill_graphicsblock(int $page, string $blockname, int $graphics, string $optlist): int { return 0; }

	// =========================================================================
	// Matchbox
	// =========================================================================

	/**
	 * マッチボックスの情報を問い合わせる
	 *
	 * @param string $boxname ボックス名
	 * @param int $num ボックス番号
	 * @param string $keyword 問い合わせキーワード
	 * @return float 要求された値
	 */
	public function info_matchbox(string $boxname, int $num, string $keyword): float { return 0.0; }

	// =========================================================================
	// Multimedia / 3D / Portfolio
	// =========================================================================

	/**
	 * マルチメディアアセットを読み込む
	 *
	 * @param string $type アセットタイプ
	 * @param string $filename ファイル名
	 * @param string $optlist オプションリスト
	 * @return int アセットハンドル。エラー時は0
	 */
	public function load_asset(string $type, string $filename, string $optlist): int { return 0; }

	/**
	 * 3Dデータを読み込む
	 *
	 * @param string $filename ファイル名
	 * @param string $optlist オプションリスト
	 * @return int 3Dハンドル。エラー時は0
	 */
	public function load_3ddata(string $filename, string $optlist): int { return 0; }

	/**
	 * 3Dビューを作成する
	 *
	 * @param string $username ユーザー名
	 * @param string $optlist オプションリスト
	 * @return int 3Dビューハンドル。エラー時は0
	 */
	public function create_3dview(string $username, string $optlist): int { return 0; }

	/**
	 * ポートフォリオにファイルを追加する
	 *
	 * @param int $folder フォルダハンドル
	 * @param string $filename ファイル名
	 * @param string $optlist オプションリスト
	 * @return int エラー時は0、成功時は1
	 */
	public function add_portfolio_file(int $folder, string $filename, string $optlist): int { return 0; }

	/**
	 * ポートフォリオにフォルダを追加する
	 *
	 * @param int $parent 親フォルダハンドル
	 * @param string $foldername フォルダ名
	 * @param string $optlist オプションリスト
	 * @return int フォルダハンドル
	 */
	public function add_portfolio_folder(int $parent, string $foldername, string $optlist): int { return 0; }

	// =========================================================================
	// POCA (PDF Container Object Access)
	// =========================================================================

	/**
	 * 新しいPDFコンテナオブジェクトを作成する
	 *
	 * @param string $optlist オプションリスト
	 * @return int コンテナハンドル
	 */
	public function poca_new(string $optlist): int { return 0; }

	/**
	 * PDFコンテナオブジェクトにオブジェクトを挿入する
	 *
	 * @param int $container コンテナハンドル
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function poca_insert(int $container, string $optlist): void {}

	/**
	 * PDFコンテナオブジェクトからオブジェクトを削除する
	 *
	 * @param int $container コンテナハンドル
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function poca_remove(int $container, string $optlist): void {}

	/**
	 * PDFコンテナオブジェクトを削除する
	 *
	 * @param int $container コンテナハンドル
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function poca_delete(int $container, string $optlist): void {}

	// =========================================================================
	// Tagged PDF / Structure Elements
	// =========================================================================

	/**
	 * 構造要素を開く
	 *
	 * @param string $tagname タグ名
	 * @param string $optlist オプションリスト
	 * @return int アイテムハンドル
	 */
	public function begin_item(string $tagname, string $optlist): int { return 0; }

	/**
	 * 構造要素を閉じる
	 *
	 * @param int $id アイテムハンドル
	 * @return void
	 */
	public function end_item(int $id): void {}

	/**
	 * 構造要素をアクティブにする
	 *
	 * @param int $id アイテムハンドル
	 * @return void
	 */
	public function activate_item(int $id): void {}

	/**
	 * マーク付きコンテンツシーケンスを開始する
	 *
	 * @param string $tagname タグ名
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function begin_mc(string $tagname, string $optlist): void {}

	/**
	 * マーク付きコンテンツシーケンスを終了する
	 *
	 * @return void
	 */
	public function end_mc(): void {}

	/**
	 * マーク付きコンテンツポイントを追加する
	 *
	 * @param string $tagname タグ名
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function mc_point(string $tagname, string $optlist): void {}

	// =========================================================================
	// Document Part Hierarchy (PDF/VT, PDF 2.0)
	// =========================================================================

	/**
	 * ドキュメントパート階層のノードを開始する
	 *
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function begin_dpart(string $optlist): void {}

	/**
	 * ドキュメントパート階層のノードを閉じる
	 *
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function end_dpart(string $optlist): void {}

	// =========================================================================
	// Type 3 Font
	// =========================================================================

	/**
	 * Type 3フォント定義を開始する
	 *
	 * @param string $fontname フォント名
	 * @param float $a フォント行列要素
	 * @param float $b フォント行列要素
	 * @param float $c フォント行列要素
	 * @param float $d フォント行列要素
	 * @param float $e フォント行列要素
	 * @param float $f フォント行列要素
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function begin_font(string $fontname, float $a, float $b, float $c, float $d, float $e, float $f, string $optlist): void {}

	/**
	 * Type 3フォント定義を終了する
	 *
	 * @return void
	 */
	public function end_font(): void {}

	/**
	 * Type 3フォントのグリフ定義を開始する
	 *
	 * @param int $uv Unicodeコードポイント
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function begin_glyph_ext(int $uv, string $optlist): void {}

	/**
	 * Type 3フォントのグリフ定義を終了する
	 *
	 * @return void
	 */
	public function end_glyph(): void {}

	// =========================================================================
	// Network / Download
	// =========================================================================

	/**
	 * ネットワークリソースからデータをダウンロードする
	 *
	 * @param string $filename ファイル名
	 * @param string $optlist オプションリスト
	 * @return int エラー時は0、成功時は1
	 */
	public function download(string $filename, string $optlist): int { return 0; }

	// =========================================================================
	// PDI Processing
	// =========================================================================

	/**
	 * インポートされたPDFドキュメントの特定要素を処理する
	 *
	 * @param int $doc PDIドキュメントハンドル
	 * @param int $page ページ番号
	 * @param string $optlist オプションリスト
	 * @return int エラー時は0、成功時は1
	 */
	public function process_pdi(int $doc, int $page, string $optlist): int { return 0; }

	// =========================================================================
	// Error Handling
	// =========================================================================

	/**
	 * 最後に発生した例外のAPIメソッド名を取得する
	 *
	 * @return string APIメソッド名
	 */
	public function get_apiname(): string { return ''; }

	/**
	 * 最後に発生した例外のエラーメッセージを取得する
	 *
	 * @return string エラーメッセージ
	 */
	public function get_errmsg(): string { return ''; }

	/**
	 * 最後に発生した例外のエラー番号を取得する
	 *
	 * @return int エラー番号
	 */
	public function get_errnum(): int { return 0; }

	// =========================================================================
	// Elliptical Arc
	// =========================================================================

	/**
	 * 3点で定義された円弧セグメントを描く
	 *
	 * @param float $x1 X座標1
	 * @param float $y1 Y座標1
	 * @param float $x2 X座標2
	 * @param float $y2 Y座標2
	 * @return void
	 */
	public function circular_arc(float $x1, float $y1, float $x2, float $y2): void {}

	/**
	 * 現在のポイントから楕円弧セグメントを描く
	 *
	 * @param float $x X座標
	 * @param float $y Y座標
	 * @param float $rx X方向の半径
	 * @param float $ry Y方向の半径
	 * @param string $optlist オプションリスト
	 * @return void
	 */
	public function elliptical_arc(float $x, float $y, float $rx, float $ry, string $optlist): void {}
}
