<?php
namespace Form2\Element;

class Checkbox extends FormElement {

   /**
    * checkboxのインプットモード
    * @param string/integer $value 要素値
    * @param array 要素の属性
    * @return
    */
	public function makeInput($value = null, $attr) {
		$html = array();
        $name = $this->getElementName();
		foreach($this->val as $key => $val) {
			if($value !== null && in_array($val, $value)) {
				$html[] = "<label class='form_label form_checkbox'><input type='checkbox' name='{$name}[]' value='{$val}' {$attr} checked>" . $key . "</label>";
			} else {
				$html[] = "<label class='form_label form_checkbox'><input type='checkbox' name='{$name}[]' value='{$val}' {$attr}>" . $key . "</label>";
			}
		}
		return join("", $html);
	}

   /**
    * 複数候補の選択要素の確認モード
    * @param string/integer $value 要素値
    * @return
    */
	public function makeConfirm($value) {
		$html = array();
        $name = $this->getElementName();
		foreach($this->val as $key => $val) {
			if($value !== null && in_array($val, $value)) {
				$html[] = "<label class='form_label form_{$this->type}'><input type='hidden' name='{$name}[]' value='{$val}'>" . nl2br($key) . "</label>";
			}
		}
		return join("", $html);
	}
}
