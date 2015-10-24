<?php
namespace Form2;

use Form2\Element\FormElementInterface;
use Exception;
/**
 *　フォーム自動生成ライブラリ
 */
class Fieldset {
    
    /**
     *
     * @api
     * @var mixed $elements 
     * @access private
     * @link
     */
    private $elements = [];

    /**
     *
     * @api
     * @var mixed $name 
     * @access private
     * @link
     */
    private $name = null;

    /**
     *
     * @api
     * @var mixed $data 
     * @access private
     * @link
     */
    private $data = null;

    /**
     *
     * @api
     * @var mixed $form 
     * @access private
     * @link
     */
    private $form = null;

    /**
     *
     * @api
     * @var mixed $fieldset 
     * @access private
     * @link
     */
    private $fieldset = null;

    public function __construct($form, $fieldset = [])
    {
        if(isset($fieldset['name'])) {
            $this->setName($fieldset['name']);
            unset($fieldset['name']);
        }
        $this->setForm($form);
        if(!empty($fieldset)) {
            $this->setFieldset($fieldset);
        }
        //データ配置
        $data = $form->getData();
        $name = $this->getName();
        if(isset($data[$name])) {
            $this->setData($data[$name]);
        }
    }

    /**
     * 
     * @api
     * @param mixed $fieldset
     * @return mixed $fieldset
     * @link
     */
    public function setFieldset ($fieldset)
    {
        return $this->fieldset = $fieldset;
    }

    /**
     * 
     * @api
     * @return mixed $fieldset
     * @link
     */
    public function getFieldset ()
    {
        return $this->fieldset;
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
     * 
     * @api
     * @param mixed $elements
     * @return mixed $elements
     * @link
     */
    public function setElements ($elements)
    {
        return $this->elements = $elements;
    }

    /**
     * 
     * @api
     * @return mixed $elements
     * @link
     */
    public function getElements ()
    {
        return $this->elements;
    }

    /**
     * 
     * @api
     * @param   
     * @param    
     * @return
     * @link
     */
    public function addElement (FormElementInterface $element)
    {
        $element->setScope($this->getName());
        $this->elements[] = $element;
    }

    /**
     * 
     * @api
     * @param mixed $data
     * @return mixed $data
     * @link
     */
    public function setData ($data)
    {
        return $this->data = $data;
    }

    /**
     * 
     * @api
     * @return mixed $data
     * @link
     */
    public function getData ()
    {
        return $this->data;
    }

    /**
     * 
     * @api
     * @param string $name
     * @return
     * @link
     */
    public function setName ($name)
    {
        return $this->name = $name;
    }

    /**
     * 
     * @api
     * @return mixed $name
     * @link
     */
    public function getName ()
    {
        if($this->name === null) {
            $className = explode('\\', static::class);
            $className = array_pop($className);
            $name = str_replace('Fieldset', '', $className);
            if(empty($name)) {
                $name = 'default';
            }
            $this->name = $name;
        }
        return $this->name;
    }
    
    public function initialization()
    {
        $form = $this->getForm();
        foreach($this->getFieldset() as $name => $field) {
            $value = isset($field['value']) ? $field['value'] : null;
            $element = $form->append($field['type'], $name, $value);
            $element->set('name', $name);
            if(isset($field['validator'])) {
                foreach($field['validator'] as list($rule, $message)) {
                    $element->addValidator($rule, $message);
                }
            }
            if(isset($field['attrs'])) {
                foreach($field['attrs'] as $key => $val) {
                    $element->set($key, $val);
                }
            }
            $this->addElement($element);
        }
        $this->onInit();
    }
    
    public function onInit() {}   
}