<?php 
namespace Form2;
/**
 *　フォーム自動生成ライブラリ
 */
class FormManager {

/**
 * フォームインスタンスキャッシュプール
 * @var array 
 * @link http://
 */
	private $storage = array();

/**
 * 最後に生成したフォームのid
 * @var string 
 * @link http://
 */
	private $last_id = null;

/**
 * 自動生成id用プリフィックス
 * @var string 
 * @link http://
 */
	private $id = "myform";

/**
 * 自動生成id時用ユニークid値
 * @var integer 
 * @link http://
 */
	private $count = 0;
	
/**
 * 再帰的にエスケープ、オブジェクトに無動作
 * @param mix 
 * @return
 */
	public static function escape($data) {
		if(is_array($data)){
			foreach($data as $key => $value){
				$data[$key]=self::escape($value);
			}
			return $data;
		}elseif(is_string($data)){
			//如何なる状況でもscriptタグを許しない
			if(strpos($data, "<script") !== false) {
				$data = preg_replace("/<script[\s\S]+?<\/script>/", "", $data);
			}
			return htmlspecialchars($data,ENT_QUOTES);
		}else{
			return $data;
		}
	}

/**
 * 再帰的にアンエスケープ、オブジェクトに無動作(現状はまともに動きません、理由は不明)
 * @param mix 
 * @return
 */
	public static function unescape($data) {
		if(is_array($data)){
			foreach($data as $key => $value){
				$data[$key]=self::unescape($value);
			}
			return $data;
		}elseif(is_string($data)){
			return htmlspecialchars_decode($data,ENT_QUOTES);
		}else{
			return $data;
		}
	}
	
/**
 * 生成用文字列をクオートする
 * @param string 
 * @return
 */
	public static function quote($val){
		return "'" . self::escape($val) . "'";
	}

/**
 * 要素の属性文字列を生成
 * @param array 
 * @return
 */
	public static function attr_format($attrs) {
		$attr = array();
		foreach($attrs as $name => $attr_value) {
			$name = self::escape($name);
			$attr_value = self::quote($attr_value);
			$attr[] = "{$name}={$attr_value}";
		}
		return join(" ", $attr);
	}

/**
 * フォームオブジェクトを生成する
 * @param string 
 * @return
 */
	public function create($id = null){
		if(empty($id)){
			$id = $this->id . "_" . (++$this->count);
		}
		if(!empty($this->storage[$id])) {
			trigger_error("FormHelper:requested form_id was used,old form should be overwrite", E_USER_NOTICE);
		}
		$this->last_id=$id;
		$this->storage[$id] = new Form($id);
		return $this->storage[$id];
	}

/**
 * 生成したフォームオブジェクトを取得する
 * @param string 
 * @return
 */
	public function find($id=null){
		if(empty($id)){
			$id = $this->last_id;
		}
		if(empty($this->storage[$id])){
			trigger_error("FormHelper:undefined Form", E_USER_NOTICE);
			return null;
		}
		return $this->storage[$id];
	}
	
}

