<?php
namespace Form2;

use Form2\FormManager;
use Form2\FormElement;
/**
 *　フォーム自動生成ライブラリ
 */
class FormObj {

/**
 * 要素インスタンスキャッシュプール
 * @var array 
 * @link http://
 */
	private $elements = array();

/**
 * フォームの属性配列
 * @var array 
 * @link http://
 */
	private $attrs=array("id" => "", "method" => "POST", "action" => "", "accept-charset" => "UTF-8", "enctype" => "multipart/form-data" );

/**
 * フォームがサブミットされたかどか
 * @var boolean 
 * @link http://
 */
	private $submitted = false;

/**
 * フォームが確認ページ生成されたかどか
 * @var boolean 
 * @link http://
 */
	private $confirmed = false;

/**
 * フォーム処理が完成されたかどか
 * @var boolean 
 * @link http://
 */
	private $completed = false;

/**
 * サブミットされたデータはバリデーションされたかどか
 * @var boolean 
 * @link http://
 */
	private $validated = false;

/**
 * バリデーション処理の結果
 * @var boolean 
 * @link http://
 */
	private $no_error = true;

/**
 * ライブラリ用データも含む、全ての入力データ
 * @var array 
 * @link http://
 */
	private $all_data = null;

/**
 * ユーザ入力データ
 * @var array 
 * @link http://
 */
	private $request_data = null;

/**
 * 追加するデータ
 * @var array 
 * @link http://
 */
	private $preprocess_data = array();

/**
 * ライブラリ用データのキーの配列
 * @var array 
 * @link http://
 */
	private $except = array("form_id" => true, "form_mode" => true, "submit" => true, "reset" => true);

/**
 * メール用ハンドラ
 * @var resource 
 * @link http://
 */
	private $mail_handler = null;

/**
 * form内生成された画像のサイズ
 * @var resource 
 * @link http://
 */
	private static $img_size = array("100%", "100%");

/**
 * 構造器、フォームオブジェクトを生成
 * @param string 
 * @return
 */
	public function __construct($id){
		$this->id=$id;
		$this->append("hidden", "form_id", $id);
		$this->append("hidden", "form_mode", "confirm");
		$this->append("submit", "submit", "確認する")->class("btn btn-success");
		$this->append("reset", "reset", "リセット")->class("btn btn-danger");
		$this->set("id", $id);
	}

/**
 * メールハンドラを設定(必須ではない)
 * @param object 
 * @return
 */
	public function use_mailer($mailer) {
		$this->mail_handler = $mailer;
	}


/**
 * フォームの開始タグ出力
 * @return
 */
	public function start() {
		$attr = FormManager::attr_format($this->attrs);
		$html = array("<form {$attr}>",
			$this->form_id, $this->form_mode
		);
		echo join(PHP_EOL, $html);
	}


/**
 * フォームの閉じタグ出力
 * @return
 */
	public function end() {
		echo "</form>";
	}

/**
 * フォームの属性設定
 * @param string $attr 属性名
 * @param mix $val 属性値
 * @return
 */
	public function set($attr, $val) {
		$this->attrs[$attr] = $val;
	}
	
/**
 * フォームに要素を追加
 * @param string $type 要素タイプ
 * @param integer $name 要素ネーム
 * @param mix $val 要素の値
 * @param resource 要素の初期値
 * @return object
 */
	public function append($type = "text", $name, $val = null, $default = null) {
		$this->elements[$name] = new FormElement($this, $name, $type, $val, $default);
		if($type == "file") {
			$this->set("enctype", 'multipart/form-data');
		}
		return $this->elements[$name];
	}

/**
 * フォームに要素を取り外す
 * @param integer $name 要素ネーム
 * @return element 取り外した要素
 */
	public function detach($name) {
		if(isset($this->elements[$name])) {
			$element = $this->elements[$name];
			unset($this->elements[$name]);
			return $element;
		}
	}
	
/**
 * 要素を生成するか、formに追加せずに要素だけを返る、フォームの自動生成などで使われる
 * フォーム内でキャッチしないので、アプリでキャッチしなければなりません
 * @param string $type 要素タイプ
 * @param integer $name 要素ネーム
 * @param mix $val 要素の値
 * @param resource 要素の初期値
 * @return object
 */
	public function isolate($type = "text", $name, $val = null, $default = null) {
		if($type == "file") {
			$this->set("enctype", 'multipart/form-data');
		}
		return new FormElement($this, $name, $type, $val, $default);
	}

/**
 * 画像サイズの調整・取得
 * @return
 */
	public static function img_size($width = null, $height = null) {
		if(isset($width)) {
			self::$img_size[0] = $width;
		} elseif(isset($height)) {
			self::$img_size[1] = $height;			
		} else {
			return self::$img_size;
		}
	}

/**
 * 画像サイズの調整をオフにする
 * @return
 */
	public function img_size_off($width = null, $height = null) {
		self::$img_size = array(null, null);
	}

/**
 * ライブラリ用データも含む、全ての入力データを返す
 * @return
 */
	private function all_data() {
		if($this->all_data == null) {
			switch(strtolower($this->attrs["method"])) {
				case "post": $data = $_POST; break;
				case "get" : $data = $_GET; break;
			}
			$this->all_data = empty($data) ? $this->preprocess_data : array_merge($this->preprocess_data, $data);
		}
		return $this->all_data;
	}

/**
 * ユーザ入力データを返す、キーを指定する場合は指定されたデータが返されるが、その以外の場合は全データ返される。
 * @param string $name データキー
 * @return
 */
	public function get_data($name = null) {
		if($this->request_data == null) {
			$data = $this->all_data();
			foreach($this->except as $key => $except) {
				unset($data[$key]);
			}
			$this->request_data = $data;
		}
		if($name === null) {
			return $this->request_data;
		} else {
			if(isset($this->request_data[$name])) {
				return $this->request_data[$name];
			}
		}
		return null;
	}

/**
 * データ値を上書きする
 * @param string $name 上書きしたいデータのキー
 * @param mix $val 上書きしたいデータの値
 * @return
 */
	public function set_data($name, $val) {
		if(!empty($this->request_data)) {
			$this->request_data[$name] = $val;
			$this->all_data[$name] = $val;
		} else {
			$this->preprocess_data[$name] = $val;
		}
		if(isset($this->elements[$name])) {
			$this->elements[$name]->value($val);
		}
	}

/**
 * データを一気に上書きする
 * @param array $data 上書きするデータ
 * @return
 */
	public function assign($data) {
		if(is_array($data)) {
			foreach($data as $name => $val) {
				$this->set_data($name, nl2br($val));
			}
		}
	}

/**
 * データを一気に廃棄する
 * @param array $data 上書きするデータ
 * @return
 */
	public function clear() {
		switch(strtolower($this->attrs["method"])) {
			case "post": $_POST = array_diff_key($_POST, $this->elements); break;
			case "get" : $_GET = array_diff_key($_GET, $this->elements); break;
		}
		$this->all_data = null;
		$this->request_data = null;
		$this->each(function($name, $element) {
				$element->clear();				
			});
	}

/**
 * 要素をループして操作する
 * @param array $call 要素に対する処理
 * @return
 */
	public function each($call) {
		$elements = array_diff_key($this->elements, $this->except);
		foreach($elements as $name => $element) {
			call_user_func($call, $name, $element);
		}
	}

/**
 * バリデーション処理、リセットデータの検知
 * @return
 */
	public function validata() {
		if($this->validated) {
			return $this->no_error;
		}
		$data = $this->all_data();
		if(isset($data["reset"])) {
			return $this->force_error();
		}
		$elements = array_diff_key($this->elements, $this->except);
		foreach($elements as $name => $element) {
			if(isset($data[$name])) {
				$element->value($data[$name]);
			}
			if($element->validata() === false) {
				$this->no_error = false;
			}
		}
		$this->validated = true;
		return $this->no_error;
	}

/**
 * 強制エラー
 * @return
 */
	public function force_error() {
		return $this->no_error = false;
	}

/**
 * サブミットされたかどかのチェック
 * @return
 */
	public function submitted() {
		if(!$this->submitted) {
			$data = $this->all_data();
			if(isset($data["form_id"]) && $data["form_id"] == $this->id) {
				$this->submitted = true;
			}
		}
		return $this->submitted;
	}
	
/**
 * 確認ページ処理されるかどかのチェック
 * @return
 */
	public function confirmed() {
		if(!$this->confirmed && empty($this->error)) {
			$data = $this->all_data();
			if(isset($data["form_id"]) && $data["form_id"] == $this->id && $data["form_mode"] == "confirm" && !isset($data["reset"])) {
				$this->confirmed = true;
			}
		}
		return $this->confirmed;		
	}

/**
 * 完了処理をされるかどかのチェック
 * @return
 */
	public function completed() {
		if(!$this->completed && empty($this->error)) {
			$data = $this->all_data();
			if(isset($data["form_id"]) && $data["form_id"] == $this->id && $data["form_mode"] == "complete" && !isset($data["reset"])) {
				$this->completed = true;
			}
		}
		return $this->completed;		
	}

/**
 * サブミット処理
 * @param closure $callback コールバック 
 * @return
 */
	public function submit($callback = null) {
		if($this->submitted()) {
			if(!$this->validata()) {
				return false;
			}
			$data = $this->get_data();
			if(is_callable($callback)) {
				return call_user_func($callback, $data, $this, $this->mail_handler);
			}
		}
	}
	
/**
 * 確認及び完了処理
 * @param closure コールバック
 * @param closure コールバック
 * @return
 */
	public function confirm($confirm = null, $complete = null) {
		if($this->submitted()) {
			if(!$this->validata()) {
				return false;
			}
			$data = $this->get_data();
			//完了処理かどか?
			if(is_callable($complete) && ($this->completed() || $confirm === false)) {
				$this->completed = true;
				return call_user_func($complete, $data, $this, $this->mail_handler);
			}
			//確認処理かどか?
			//$confirm : false =>　確認ページ生成しない, null => 確認ページ生成するが、callbackは実行しない
			if($confirm !== false && $this->confirmed()) {
				$this->confirm_config();
				if(is_callable($confirm)) {
					call_user_func($confirm, $data, $this);
				}
				return $this;
			}
		}
	}

/**
 * 確認ページ生成時必要の処理
 * @return
 */
	private function confirm_config() {
		$this->each(function($name, $element) {
				$element->confirm_mode();
			});
		$this->elements["form_mode"]->value("complete");
		$this->elements["submit"]->value("送信する");
		$this->elements["reset"]->type("submit")->value("戻る");
	}

/**
 * 生成した要素をアクセスする
 * @param string $name 要素名
 * @return
 */
	public function __get($name) {
		if(isset($this->elements[$name])) {
			return $this->elements[$name];
		}
	}

/**
 * 要素追加の部分関数
 * @param string $name 要素名
 * @param array $param 部分関数名
 * @return
 */
	public function __call($name, $param) {
		if(strpos($name, "add_") !== false) {
			$type = str_replace("add_", "", $name);
			array_unshift($param, $type);
			return call_user_func_array(array($this, "append"), $param);
		}
	}
}
