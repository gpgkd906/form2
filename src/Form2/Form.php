<?php
namespace Form2;

use Exception;
/**
 *　フォーム自動生成ライブラリ
 */
class Form {
    
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
	private $attrs = array("id" => "", "method" => "POST", "action" => "", "accept-charset" => "UTF-8", "enctype" => "multipart/form-data" );

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
	private $allData = null;

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
     *
     * @api
     * @var mixed $fieldset 
     * @access private
     * @link
     */
    private $fieldsets = null;

    /**
     * ライブラリ用データのキーの配列
     * @var array 
     * @link http://
     */
	private $except = array("form_id" => true, "form_mode" => true, "submit" => true, "reset" => true, 'csrf' => true);

    /**
     *
     * @api
     * @var mixed $csrfExpire
     * @access public
     * @link
     */
    public $csrfExpire = 1800;
    
    /**
     *
     * @api
     * @var mixed $message 
     * @access private
     * @link
     */
    private $message = [];

    /**
     * 
     * @api
     * @param mixed $message
     * @return mixed $message
     * @link
     */
    public function setMessage ($message)
    {
        return $this->message = $message;
    }

    /**
     * 
     * @api
     * @return mixed $message
     * @link
     */
    public function getMessage ()
    {
        return $this->message;
    }

    public function addMessage ($name, $message)
    {
        return $this->message[$name] = $message;
    }
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
        $this->initCsrf();
		$this->set("id", $id);
	}

    /**
     * フォームの開始タグ出力
     * @return
     */
	public function start() {
		$attr = FormManager::attr_format($this->attrs);
		$html = [
            "<form {$attr}>",
            $this->form_id, $this->form_mode, $this->csrf,
		];
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
	public function append($type = "text", $name, $val = null, $default = null, $elementClass = null) {
		$element = $this->isolate($type, $name, $val, $default, $elementClass);
        $this->elements[$name] = $element;
        $element->setForm($this);
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
     * 要素を生成するが、formに追加せずに要素だけを返る、フォームの自動生成などで使われる
     * フォーム内でキャッチしないので、アプリでキャッチしなければなりません
     * @param string $type 要素タイプ
     * @param integer $name 要素ネーム
     * @param mix $val 要素の値
     * @param resource 要素の初期値
     * @return object
     */
	public function isolate($type = "text", $name, $val = null, $default = null, $elementClass = null) {
        if($elementClass === null) {
            $elementClass = __NAMESPACE__ . '\Element\\' . ucfirst($type);
            if(!class_exists($elementClass)) {
                if(class_exists($type)) {
                    $elementClass = $type;
                } else {
                    $elementClass = __NAMESPACE__ . '\Element\\FormElement';
                }
            }
        }
        If(!class_exists($elementClass)) {
            throw new Exception(sprintf('不明なFormElement: %s', $elementClass));
        }
		return new $elementClass($name, $type, $val, $default);
	}

    /**
     * ライブラリ用データも含む、全ての入力データを返す
     * @return
     */
	private function allData() {
		if($this->allData == null) {
			switch(strtolower($this->attrs["method"])) {
            case "post": $data = $_POST; break;
            case "get" : $data = $_GET; break;
			}
			$this->allData = empty($data) ? $this->preprocess_data : array_merge($this->preprocess_data, $data);
		}
		return $this->allData;
	}

    /**
     * ユーザ入力データを返す、キーを指定する場合は指定されたデータが返されるが、その以外の場合は全データ返される。
     * @param string $name データキー
     * @return
     */
	public function getData($name = null, $scope = null, $defaultNull = false) {
		if($this->request_data == null) {
			$data = $this->allData();
			foreach($this->except as $key => $except) {
				unset($data[$key]);
			}
			$this->request_data = $data;
		}
        $data = $this->request_data;
        if($scope !== null && isset($data[$scope])) {
            $data = $data[$scope];
        }
        if($name !== null && isset($data[$name])) {
            $data = $data[$name];            
        } else {
            if($defaultNull) {
                $data = null;
            }
        }
        return $data;
	}

    /**
     * データ値を上書きする
     * @param string $name 上書きしたいデータのキー
     * @param mix $val 上書きしたいデータの値
     * @return
     */
	public function set_data($name, $val) {
		/* if(!empty($this->request_data)) { */
		/* 	$this->request_data[$name] = $val; */
		/* 	$this->allData[$name] = $val; */
		/* } else { */
		/* 	$this->preprocess_data[$name] = $val; */
		/* } */
		/* if(isset($this->elements[$name])) { */
		/* 	$this->elements[$name]->value($val); */
		/* } */
	}

    /**
     * データを一気に上書きする
     * @param array $data 上書きするデータ
     * @return
     */
	public function assign($data) {
		/* if(is_array($data)) { */
		/* 	foreach($data as $name => $val) { */
		/* 		$this->set_data($name, nl2br($val)); */
		/* 	} */
		/* } */
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
		$this->allData = null;
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
		$data = $this->allData();
		if(isset($data["reset"])) {
			return $this->force_error();
		}
		$elements = array_diff_key($this->elements, $this->except);
        if(isset($this->elements['csrf'])) {
            if($data['csrf'] !== $this->getCsrfValue()) {
                return $this->elements['csrf']->force_error('CSRF認証失敗');
            }
        }
		foreach($elements as $name => $element) {
            $scope = $element->getScope();
            if($scope && isset($data[$scope])) {
                $_data = $data[$scope];
            } else {
                $_data = $data;
            }
			if(isset($_data[$name])) {
				$element->value($_data[$name]);
			}
			if($element->validata() === false) {
                $this->addMessage($name, $element->error);
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
			$data = $this->allData();
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
			$data = $this->allData();
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
			$data = $this->allData();
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
			$data = $this->getData();
            foreach($this->getFieldsets() as $fieldset) {
                $fieldset->onSubmit();
            }
			if(is_callable($callback)) {
				return call_user_func($callback, $data, $this);
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
			$data = $this->getData();
			//完了処理かどか?
			if(is_callable($complete) && ($this->completed() || $confirm === false)) {
				$this->completed = true;
				return call_user_func($complete, $data, $this);
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

    private function initCsrf()
    {
        if($this->getCsrfValue()) {
            $csrf = $this->getCsrfValue();
        } else {
            $csrf = hash('sha256', $this->getCsrfKey() . uniqid(mt_rand()));
        }
        $this->append('hidden', 'csrf', $csrf);
        $this->setCsrfValue($csrf);
    }

    private function getCsrfKey()
    {
        return __NAMESPACE__ . $this->id . 'csrf';
    }

    private function getCsrfValue()
    {
        if(!isset($_SESSION)) {
            session_start();
            $session = $_SESSION;
            session_write_close();
            unset($_SESSION);
        } else {
            $session = $_SESSION;
        }
        if(isset($session[$this->getCsrfkey()])) {
            $csrfItem = $session[$this->getCsrfkey()];
            if(!isset($csrfItem['expire'])) {
                return null;
            }
            if($csrfItem['expire'] < $_SERVER['REQUEST_TIME']) {
                return null;
            }
            return isset($csrfItem['value']) ? $csrfItem['value'] : null;
        } else {
            return null;
        }
    }

    private function setCsrfValue($csrf)
    {
        if($this->getCsrfValue()) {
            $expire = null;
        } else {
            $expire = $_SERVER['REQUEST_TIME'] + $this->csrfExpire;
        }
        $csrfKey = $this->getCsrfKey();
        if(!isset($_SESSION)) {
            session_start();
            if(isset($expire)) {
                $_SESSION[$csrfKey]['expire'] = $expire;
            }
            $_SESSION[$csrfKey]['value'] = $csrf;            
            session_write_close();
        } else {
            $_SESSION[$csrfKey]['expire'] = $expire;
            $_SESSION[$csrfKey]['value'] = $csrf;
        }
    }

    public function addFieldset($fieldset)
    {
        if(!$fieldset instanceof Fieldset) {
            //パラメタがconfigのであれば
            if(is_array($fieldset)) {
                $class = null;
                if(isset($fieldset['class'])) {
                    $class = $fieldset['class'];
                }
                if($class === null && !class_exists($class)){
                    $class = __NAMESPACE__ . '\Fieldset';
                }
                $fieldset = new $class($this, $fieldset);                
            } else {
                //パラメタはクラスのであれば
                if(class_exists($fieldset)) {
                    $fieldset = new $fieldset($this);
                }
            }            
        }
        $fieldset->initialization();
        $this->fieldsets[$fieldset->getName()] = $fieldset;
        return $fieldset;
    }

    /**
     * 
     * @api
     * @param mixed $fieldset
     * @return mixed $fieldset
     * @link
     */
    public function setFieldsets ($fieldsets)
    {
        return $this->fieldsets = $fieldsets;
    }

    /**
     * 
     * @api
     * @return mixed $fieldset
     * @link
     */
    public function getFieldsets ()
    {
        return $this->fieldsets;
    }

    public function getFieldset($name)
    {
        if(isset($this->fieldsets[$name])) {
            return $this->fieldsets[$name];
        }
    }
}
