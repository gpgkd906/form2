<?php
namespace Form2\Element;

class Textarea extends FormElement {

    /**
     * テキストエリアのインプットモード
     * @param string/integer $value 要素値
     * @param array 要素の属性
     * @return
     */
	public function makeInput($value = null, $attr) {
		if($value === null) {
			$value = $this->val;
		}
        $name = $this->getElementName();
		$html = array(
			"<textarea name='{$name}' {$attr}>",
			$value,
			"</textarea>"
		);
		return join("", $html);		
	}

    /**
     * textarea要素の確認モード(javascript editor運用の配慮)
     * @param string/integer $value 要素値
     * @return
     */
	public function makeConfirm($value) {
        $name = $this->getElementName();
		return "<div><label class='form_label form_{$this->type}'><input type='hidden' name='{$name}' value='{$value}'>" . nl2br(htmlspecialchars_decode($value ,ENT_QUOTES)) . "</label></div>";
	}
}
