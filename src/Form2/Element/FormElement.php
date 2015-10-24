<?php
namespace Form2\Element;

use Form2\Validator;
use Form2\FormManager;
//负责表示要素对象
class FormElement implements FormElementInterface {

    /**
     * フォームインスタンスの参照
     * @var resource 
     * @link http://
     */
	protected $form = null;

    /**
     * 要素名
     * @var string 
     * @link http://
     */
	protected $name = null;

    /**
     * 要素タイプ
     * @var string 
     * @link http://
     */
	protected $type = null;

    /**
     * 要素値(候補含む、checkbox,radio,selectなど)
     * @var resource 
     * @link http://
     */
	protected $val = null;

    /**
     * 要素値
     * @var mix 
     * @link http://
     */
	private $value = null;
 
    /**
     * html出力する時のタグname
     * @var mix 
     * @link http://
     */
    protected $elementName = null;
    /**
     * 出力モード
     * @var string 
     * @link http://
     */
	protected $mode = "input";

    /**
     * バリデーションルールキュー
     * @var array 
     * @link http://
     */
	protected $validators = array();

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
	protected $attrs = array();
	
    /**
     *
     * @api
     * @var mixed $scope 
     * @access private
     * @link
     */
    private $scope = null;

    /**
     * 
     * @api
     * @param mixed $scope
     * @return mixed $scope
     * @link
     */
    public function setScope ($scope)
    {
        return $this->scope = $scope;
    }

    /**
     * 
     * @api
     * @return mixed $scope
     * @link
     */
    public function getScope ()
    {
        return $this->scope;
    }

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
			return $this->getValue();
		}
	}

    /**
     * 
     * @api
     * @param mixed $form
     * @return mixed $form
     * @link
     */
    public function setForm ($form)
    {
        return $this->form = $form;
    }

    /**
     * 
     * @api
     * @return mixed $form
     * @link
     */
    public function getForm ()
    {
        return $this->form;
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
	protected function getValue() {
        if($this->value === null) {
            $this->value = $this->getForm()->getData($this->name, $this->getScope());
        }
        return $this->value;
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
	public function __construct($name, $type, $val = null, $default = null) {
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
	public function addValidator($rule, $error_message = null) {
		$this->validators[] = array(
			"rule" => $rule, "message" => $error_message
		);
		return $this;
	}

    /**
     * back compatibility
     */
    public function must_be($rule, $error_message)
    {
        return $this->addValidator($rule, $error_message);
    }

    /**
     * バリデーションルールを解除する
     * @param integer $rule バリデーションチェッカールール値
     * @return
     */
	public function remove_must($rule = null) {
		if(empty($rule)) {
			$this->validators = array();
		} else {
			unset($this->validators[$rule]);
		}
		return $this;
	}
	
    /**
     * バリデーション処理
     * @return
     */
	public function validata() {
		$value = $this->getValue();
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
		foreach($this->validators as $set) {
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
        if($this->getForm()) {
            $this->getForm()->force_error();
        }
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
     * 要素を出力する
     * @return
     */
	public function __toString() {
		$value = $this->getValue();
		$value = FormManager::escape($value);
		$attrs = FormManager::attr_format($this->attrs);
		switch($this->mode) {
        case "input": return $this->makeInput($value, $attrs); break;
        case "confirm": return $this->makeConfirm($value, $attrs); break;
		}
	}

    public function getElementName()
    {
        if($this->elementName === null) {
            if($this->getScope()) {
                $this->elementName = $this->getScope() . '[' . $this->get('name') . ']';
            } else {
                $this->elementName = $this->get('name');
            }
        }
        return $this->elementName;
    }

    /**
     * 一般要素のインプットモード 
     * @param string/integer $value 要素値
     * @param array 要素の属性
     * @return
     */
    public function makeInput($value = null, $attr) {
        if($value === null) {
             $value = $this->val;
        }
        $name = $this->getElementName();
        $html ="<input type='{$this->type}' name='{$name}' value='{$value}' {$attr}>";
        return $html;
    }

    public function makeConfirm($value)
    {
        $name = $this->getElementName();
		return "<label class='form_label form_{$this->type}'><input type='hidden' name='{$name}' value='{$value}'>" . nl2br($value) . "</label>";
    }
}
