<?php
namespace Form2;

use Form2\FormManager;
use Form2\FormObj;
use Form2\Validator;
//负责表示要素对象
class FormElement {

/**
 * フォームインスタンスの参照
 * @var resource 
 * @link http://
 */
	private $form = null;

/**
 * 要素名
 * @var string 
 * @link http://
 */
	private $name = null;

/**
 * 要素タイプ
 * @var string 
 * @link http://
 */
	private $type = null;

/**
 * 要素値(候補含む、checkbox,radio,selectなど)
 * @var resource 
 * @link http://
 */
	private $val = null;

/**
 * 要素値
 * @var resource 
 * @link http://
 */
	private $value = null;

/**
 * 出力モード
 * @var string 
 * @link http://
 */
	private $mode = "input";

/**
 * インプットモードカスタマイズ
 * @var resource 
 * @link http://
 */
	private $input_formater = null;

/**
 * 確認モードカスタマイズ
 * @var resource 
 * @link http://
 */
	private $confirm_formater = null;

/**
 * バリデーションルールキュー
 * @var array 
 * @link http://
 */
	private $queue = array();

/**
 * バリデーションエラーメッセージ
 * @var string 
 * @link http://
 */
	public $error = "";

/**
 * 要素の属性
 * @var array 
 * @link http://
 */
	private $attrs = array();
	

/**
 * 要素の属性アクセサー、値の設定または参照
 * @param string $name 属性名
 * @param array $value 属性値
 * @return
 */
	public function __call($name, $value) {
		if(empty($value)) {
			return $this->get($name);
		} else {
			return $this->set($name, $value[0]);
		}
	}


/**
 * 要素の属性アクセサー、値の設定
 * @param string $name 属性名
 * @param array $value 属性値
 * @return
 */
	public function set($name, $value) {
		if(property_exists($this, $name)) {
			$this->{$name} = $value;
		} else {
			$this->attrs[$name] = $value;
		}
		return $this;
	}


/**
 * 要素の属性アクセサー、値の参照
 * @param string $name 属性名
 * @return
 */
	public function get($name) {
		if(isset($this->attrs[$name])) {
			return $this->attrs[$name];
		} elseif(isset($this->{$name})) {
			return $this->{$name};
		} elseif($name === "value") {
			return $this->get_value();
		}
	}

/**
 * 要素のclass追加
 * @param array $class class名
 * @return
 */
	public function add_class($class) {
		$cls = explode(" ", $this->get("class"));
		if(!in_array($class, $cls)) {
			$cls[] = $class;
		}
		$cls = join(" ", $cls);
		$this->set("class", $cls);
		return $this;
	}

/**
 * 要素のclass削除
 * @param array $class class名
 * @return
 */
	public function remove_class($class) {
		$cls = explode(" ", $this->get("class"));
		if(in_array($class, $cls)) {
			$cls = array_diff($cls, array($class));
		}
		$cls = join(" ", $cls);
		$this->set("class", $cls);
		return $this;
	}

/**
 * 要素値の参照
 * @return
 */
	private function get_value() {
		return isset($this->value) ? $this->value : $this->form->get_data($this->name);
	}

/**
 * 要素値の廃棄
 * @return
 */
	public function clear() {
		$this->value = null;
	}

/**
 * 要素の生成
 * @param object $form 親フォームのインスタンス参照
 * @param string $name 要素名
 * @param integer $type 要素タイプ
 * @param mix $val 要素値(checkbox, radio, selectなど用)
 * @param string/integer $default 要素の初期値 
 * @return
 */
	public function __construct($form, $name, $type, $val = null, $default = null) {
		$this->form = $form;
		$this->name = $name;
		$this->type = $type;
		$this->val = $val;
		if($default !== null ) {
			$this->value = $default;
		}
	}
	

/**
 * バリデーションルール設定
 * @param integer $rule バリデーションチェッカールール値
 * @param string $error_message エラーメッセージ
 * @return
 */
	public function must_be($rule, $error_message = null) {
		$this->queue[] = array(
			"rule" => $rule, "message" => $error_message
		);
		return $this;
	}

/**
 * バリデーションルールを解除する
 * @param integer $rule バリデーションチェッカールール値
 * @return
 */
	public function remove_must($rule = null) {
		if(empty($rule)) {
			$this->queue = array();
		} else {
			unset($this->queue[$rule]);
		}
		return $this;
	}
	
/**
 * バリデーション処理
 * @return
 */
	public function validata() {
		$value = $this->get_value();
		if($this->type === "file") {
			if(is_array($value) && isset($value["size"])) {
				$value = $value["size"];
			} 
		}
		if(isset($this->attrs["maxlength"])) {
			if($this->attrs["maxlength"] < mb_strlen($value, "UTF-8")) {
				$this->error = "<span class='myform_error'>※入力内容が長すぎです。{$this->attrs['maxlength']}文字以内にしてください</span>";
				return false;
			}
		}
		foreach($this->queue as $set) {
			$result = Validator::myFormCheck($value, $set);
			if($result["status"] == "error") {
				$this->error = "<span class='myform_error'>".$result["message"]."</span>";
				return false;
			}
		}
		return true;
	}

/**
 * 要素を強制的にエラーにする
 * @param string $error_message
 * @return
 */
	public function force_error($error_message = null) {
		$this->error = "<span class='myform_error'>" . $error_message . "</span>";
		$this->form->force_error();
		return $this;
	}
	
/**
 * 要素を確認モードにする
 * @return
 */
	public function confirm_mode() {
		$this->mode = "confirm";
		return $this;
	}


/**
 * 要素をインプットモードにする
 * @return
 */
	public function input_mode() {
		$this->mode = "input";
		return $this;
	}
	

/**
 * インプットカスタマイズを設定する
 * @param closure $formater インプットカスタマイズ
 * @return
 */
	public function input($formater) {
		$this->input_formater = $formater;
		return $this;
	}
	

/**
 * 確認カスタマイズを設定する
 * @param closure $formater 確認カスタマイズ
 * @return
 */
	public function confirm($formater) {
		$this->confirm_formater = $formater;
		return $this;
	}
	

/**
 * 要素を出力する
 * @return
 */
	public function __toString() {
		$value = $this->get_value();
		$value = FormManager::escape($value);
		switch($this->mode) {
			case "input": return $this->input_element($value); break;
			case "confirm": return $this->confirm_element($value); break;
		}
	}


/**
 * 要素をインプットモードで出力する
 * @param string/integer $value 要素値
 * @return
 */
	private function input_element($value) {
		if(is_callable($this->input_formater)) {
			return call_user_func_array($this->input_formater, array($this->val, $value, $this->attrs));
		}
		$attr = FormManager::attr_format($this->attrs);
		switch($this->type) {
			case "checkbox": return $this->make_checkbox($value, $attr); break;
			case "radio": return $this->make_radio($value, $attr); break;
			case "select": return $this->make_select($value, $attr); break;
			case "textarea": return $this->make_textarea($value, $attr); break;
			case "file": return $this->make_file($value, $attr); break;
			default: return $this->make_default($value, $attr); break;
		}
	}


/**
 * checkboxのインプットモード
 * @param string/integer $value 要素値
 * @param array 要素の属性
 * @return
 */
	private function make_checkbox($value = null, $attr) {
		$html = array();
		foreach($this->val as $key => $val) {
			if($value !== null && in_array($val, $value)) {
				$html[] = "<label class='form_label form_checkbox'><input type='checkbox' name='{$this->name}[]' value='{$val}' {$attr} checked>" . $key . "</label>";
			} else {
				$html[] = "<label class='form_label form_checkbox'><input type='checkbox' name='{$this->name}[]' value='{$val}' {$attr}>" . $key . "</label>";
			}
		}
		return join("", $html);
	}


/**
 * radioのインプットモード
 * @param string/integer $value 要素値
 * @param array 要素の属性
 * @return
 */
	private function make_radio($value = null, $attr) {
		$html = array();
		foreach($this->val as $key => $val) {
			if($value !== null && $val == $value) {
				$html[] = "<label class='form_label form_radio'><input type='radio' name='{$this->name}' value='{$val}' {$attr} checked>" . $key . "</label>";
			} else {
				$html[] = "<label class='form_label form_radio'><input type='radio' name='{$this->name}' value='{$val}' {$attr}>" . $key . "</label>";
			}
		}
		return join("", $html);
	}


/**
 * selectのインプットモード
 * @param string/integer $value 要素値
 * @param array 要素の属性
 * @return
 */
	private function make_select($value = null, $attr) {
		$html = array("<select name='{$this->name}' {$attr}>");
		foreach($this->val as $key => $val) {
			if($value !== null && $val == $value) {
				$html[] = "<option value='{$val}' selected>" . $key . "</option>";
			} else {
				$html[] = "<option value='{$val}'>" . $key . "</option>";
			}
		}
		$html[] = "</select>";
		return join("", $html);
	}


/**
 * テキストエリアのインプットモード
 * @param string/integer $value 要素値
 * @param array 要素の属性
 * @return
 */
	private function make_textarea($value = null, $attr) {
		if($value === null) {
			$value = $this->val;
		}
		$html = array(
			"<textarea name='{$this->name}' {$attr}>",
			$value,
			"</textarea>"
		);
		return join("", $html);		
	}


/**
 * 一般要素のインプットモード
 * @param string/integer $value 要素値
 * @param array 要素の属性
 * @return
 */
	private function make_default($value = null, $attr) {
		if($value === null) {
			$value = $this->val;
		}
		$html =	"<input type='{$this->type}' name='{$this->name}' value='{$value}' {$attr}>";
		return $html;
	}

/**
 * ファイル要素のインプットモード
 * @param string/integer $value 要素値
 * @param array 要素の属性
 * @return
 */
	private function make_file($value = null, $attr) {
		if($value === null) {
			$value = $this->val;
		}
		if(empty($value)) {
			$value = "";
		}
		if(is_array($value) ) {
			$html = array("<label class='form_label form_{$this->type}'>");
			foreach($value as $key => $val) {
				$html[] = "<input type='hidden' name='{$this->name}[{$key}]' value='{$val}'>";
			}
			if(isset($value["link"])) {
				list($width, $height) = FormObj::img_size();
				$style = "";
				if(isset($width) || isset($height)) {
					$style = " style='max-width:{$width}; max-height:{$height}'";
				}
				$html[] = "<img src='{$value['link']}'{$style}>";
			}
			$html[] = "</label>";
			$html[] = "<input type='{$this->type}' name='{$this->name}' {$attr}>";
			$html = join("", $html);
		} else {
			$html =	"<input type='{$this->type}' name='{$this->name}' value='{$value}' {$attr}>";
		}
		return $html;
	}
	

/**
 * 要素の確認モード
 * @param string/integer $value 要素値
 * @return
 */
	private function confirm_element($value = null) {
		if(is_callable($this->confirm_formater)) {
			return call_user_func_array($this->confirm_formater, array($this->val, $value, $this->attrs));
		}
		switch($this->type) {
			case "checkbox": return $this->confirm_multi_label($value); break;
			case "radio": case "select": return $this->confirm_single_label($value); break;
			case "password": return $this->confirm_password($value); break;
			case "file" : return $this->confirm_file($value); break;
			case "textarea": return $this->confirm_textarea($value); break;
			default: return $this->confirm_default($value); break;
		}
	}		


/**
 * 複数候補の選択要素の確認モード
 * @param string/integer $value 要素値
 * @return
 */
	private function confirm_multi_label($value) {
		$html = array();
		foreach($this->val as $key => $val) {
			if($value !== null && in_array($val, $value)) {
				$html[] = "<label class='form_label form_{$this->type}'><input type='hidden' name='{$this->name}[]' value='{$val}'>" . nl2br($key) . "</label>";
			}
		}
		return join("", $html);
	}
	

/**
 * 複数候補の単一選択要素の確認モード
 * @param string/integer $value 要素値
 * @return
 */
	private function confirm_single_label($value) {
		$html = "";
		foreach($this->val as $key => $val) {
			if($value !== null && $val == $value) {
				$html = "<label class='form_label form_{$this->type}'><input type='hidden' name='{$this->name}' value='{$value}'>" . nl2br($key) . "</label>";
				break;
			}
		}
		return $html;
	}
	
/**
 * パスワードの確認モード
 * @param string/integer $value 要素値
 * @return
 */
	private function confirm_password($value) {
		return "<label class='form_label form_{$this->type}'><input type='hidden' name='{$this->name}' value='{$value}'>" . str_pad("", strlen($value), "*") . "</label>";
	}

/**
 * 単一候補の単一選択要素の確認モード
 * @param string/integer $value 要素値
 * @return
 */
	private function confirm_default($value) {
		return "<label class='form_label form_{$this->type}'><input type='hidden' name='{$this->name}' value='{$value}'>" . nl2br($value) . "</label>";
	}

/**
 * textarea要素の確認モード(javascript editor運用の配慮)
 * @param string/integer $value 要素値
 * @return
 */
	private function confirm_textarea($value) {
		return "<div><label class='form_label form_{$this->type}'><input type='hidden' name='{$this->name}' value='{$value}'>" . nl2br(htmlspecialchars_decode($value ,ENT_QUOTES)) . "</label></div>";
	}


/**
 * file要素の確認モード
 * @param string/integer $value 要素値
 * @return
 */
	private function confirm_file($value) {
		if(is_array($value)) {
			$html = array("<label class='form_label form_{$this->type}'>");
			foreach($value as $key => $val) {
				$html[] = "<input type='hidden' name='{$this->name}[{$key}]' value='{$val}'>";
			}	
			if(isset($value["link"])) {
				list($width, $height) = FormObj::img_size();
				$style = "";
				if(isset($width) || isset($height)) {
					$style = " style='max-width:{$width}; max-height:{$height}'";
				}
				$html[] = "<img src='{$value['link']}'{$style}>";
			}
			return join("", $html);
		} else {
			return "<label class='form_label form_{$this->type}'><input type='hidden' name='{$this->name}' value='{$value}'>" . nl2br($value) . "</label>";
		}
	}

}
