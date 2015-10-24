<?php
namespace Form2\Element;

class Select extends FormElement {

    /**
     * selectのインプットモード
     * @param string/integer $value 要素値
     * @param array 要素の属性
     * @return
     */
	public function makeInput($value = null, $attr) {
        $name = $this->getElementName();
		$html = array("<select name='{$name}' {$attr}>");
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
     * 複数候補の単一選択要素の確認モード
     * @param string/integer $value 要素値
     * @return
     */
	public function makeConfirm($value) {
		$html = "";
        $name = $this->getElementName();
		foreach($this->val as $key => $val) {
			if($value !== null && $val == $value) {
				$html = "<label class='form_label form_{$this->type}'><input type='hidden' name='{$name}' value='{$value}'>" . nl2br($key) . "</label>";
				break;
			}
		}
		return $html;
	}	
}
