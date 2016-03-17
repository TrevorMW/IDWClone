<?php
/**
 * OnlineBiz_MageMenu_Block_Menuitem_Node
 * @package OnlineBiz_MageMenu
 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Block_Menuitem_Node extends Mage_Core_Block_Template
{
	protected $_template = 'onlinebizsoft/magemenu/node.phtml';
	protected $_element = array();
	protected $_isActive = false;
	
	public function __construct()
	{		
		parent::__construct();
		if(Mage::helper('magemenu')->isActivated())
			$this->setTemplate($this->_template);
	}

	public function getElementOptions($element, $type = null)
	{
		if( ! isset($this->_element[$element]) || ($type && ! isset($this->_element[$element][$type]))) {
			return '';
		}
		
		$options = $type ? $this->_element[$element][$type] : $this->_element[$element];
		
		$html = array();
		if( ! $type) {
			foreach($options as $type => $options) {
				$html[] = $this->getElementOptions($element, $type); 
			}
			return count($html) ? ' ' . implode(' ', $html) : '';	
		}
		
		return $this->_parseElementOptions($type, $options);
	}
	
	protected function _parseElementOptions($type = null, $options = null)
	{
		if( ! $type || ! $options || ! is_array($options) || ! count($options)) {
			return '';
		}
		
		$_options = trim(implode(' ', $options));			
		switch($type) {
			case 'style':
				return 'style="' . $_options . '"';
			case 'class':
				return 'class="' . $_options . '"';
			case 'attribute':
				return $_options; 
		}
		return '';
	}
	
	
	public function addElementOptions($type = null, $element = null, array $options = array()) 
	{
		if(is_null($type)) {
			return $this;
		}
		
		if(is_array($type)) {
			foreach($type as $element => $_options) {
				foreach($_options as $type => $options) {
					$this->addElementOptions($type, $element, $options);
				}
			}
			return $this;
		}
		
		if( ! isset($this->_element[$element])) {
			$this->_element[$element] = array();
		}
		if( ! isset($this->_element[$element][$type])) {
			$this->_element[$element][$type] = array();
		}
		
		foreach($options as $name => $value) {
			if($this->_isValid($element, $type, $name, $value)) {
				$this->_element[$element][$type][] = $this->_getValue($type, $name, $value);
			}
		}
		return $this;
	}
	
	public function hasElementOptions($element) 
	{
		return (bool) isset($this->_element[$element])	&& count($this->_element[$element]);
	}
	
	protected function _isValid($element, $type, $name = null, $value = null)
	{
		if(is_null($value) || empty($value) || $value == '') {
			return false;
		}
		
		if(in_array($value, $this->_element[$element][$type])) {
			return false;
		}
		
		switch($type) {
			case 'style':
				return (bool) ( ! empty($name) && '#' != $value);
			case 'class':
				return (bool) ($value);
			case 'attribute':
				return (bool) ($name && $value);
		}
		return false;
	}
	
	protected function _getValue($type, $name = null, $value = null)
	{
		switch($type) {
			case 'style':
				return "{$name}:{$value};";
			case 'class':
				return $value;
			case 'attribute':
				return "{$name}=\"{$value}\"";
		}
		return false;
		
	}
	
	protected function _toHtml()
	{
		if( ! $this->isActive()) {
			return null;
		}
		return parent::_toHtml();
	}
 	
	public function isActive()
	{
		return (bool) $this->_isActive;
	}
	
	public function setIsActive($bool = true)
	{
		$this->_isActive = (bool) $bool;
		return $this;
	}
	
}