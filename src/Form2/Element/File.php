<?php
namespace Form2\Element;

class File extends FormElement {

    /**
     * ファイル要素のインプットモード
     * @param string/integer $value 要素値
     * @param array 要素の属性
     * @return
     */
	public function makeInput($value = null, $attr) {
		if($value === null) {
			$value = $this->val;
		}
		if(empty($value)) {
			$value = "";
		}
        $name = $this->getElementName();
		if(is_array($value) ) {
			$html = array("<label class='form_label form_{$this->type}'>");
			foreach($value as $key => $val) {
				$html[] = "<input type='hidden' name='{$name}[{$key}]' value='{$val}'>";
			}
			if(isset($value["link"])) {
				$style = "";
				if(isset($width) || isset($height)) {
					$style = " style='max-width:{$width}; max-height:{$height}'";
				}
				$html[] = "<img src='{$value['link']}'{$style}>";
			}
			$html[] = "</label>";
			$html[] = "<input type='{$this->type}' name='{$name}' {$attr}>";
			$html = join("", $html);
		} else {
			$html =	"<input type='{$this->type}' name='{$name}' value='{$value}' {$attr}>";
		}
		return $html;
	}

    /**
     * file要素の確認モード
     * @param string/integer $value 要素値
     * @return
     */
	public function makeConfirm($value) {
        $name = $this->getElementName();
		if(is_array($value)) {
			$html = array("<label class='form_label form_{$this->type}'>");
			foreach($value as $key => $val) {
				$html[] = "<input type='hidden' name='{$name}[{$key}]' value='{$val}'>";
			}	
			if(isset($value["link"])) {
				list($width, $height) = Form::img_size();
				$style = "";
				if(isset($width) || isset($height)) {
					$style = " style='max-width:{$width}; max-height:{$height}'";
				}
				$html[] = "<img src='{$value['link']}'{$style}>";
			}
			return join("", $html);
		} else {
			return "<label class='form_label form_{$this->type}'><input type='hidden' name='{$name}' value='{$value}'>" . nl2br($value) . "</label>";
		}
	}
}
