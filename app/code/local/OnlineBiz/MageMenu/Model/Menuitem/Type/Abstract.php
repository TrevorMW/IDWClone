<?php
/**
 * OnlineBiz_MageMenu_Model_Menuitem_Type_Abstract
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
abstract class OnlineBiz_MageMenu_Model_Menuitem_Type_Abstract 
	extends Varien_Object 
{	
	protected $_identifier = null;
	protected $_model = null;
	protected $_isLoaded = false;
	protected $_object = null;
	
	protected $_valid = false;
	protected $_errors = array();
	
	
	public function isActive()
	{
		return false;
	}
	
	public function load()
	{
		if(is_null($this->_object)) {
			$this->_object = Mage::getModel($this->_model)->load($this->_identifier);
			$this->_afterLoad();
		}
		return $this->_object;
	}
	
	protected function _afterLoad()
	{
		$this->addData($this->_object->getData());
		return $this;
	}
	
	public function isValid()
	{
		if( ! $this->getData('link_to')) {
			$this->addError(Mage::helper('magemenu')->__('Link type is not defined'));
		}
		return (bool) ( ! $this->hasErrors());
	}
	
	public function checkForWarnings()
	{
		return $this;
	}
	
	public function getUrl($addBaseUrl = false)
	{
		return '';
	}
	
    protected function _addBaseUrl($url = null)
    {
    	return $url ? Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK) . $url : null;
    }
    
	public function addError($msg = null)
	{
		return $this->setData('errors', array_merge(array($msg), $this->getData('errors') ? $this->getData('errors') : array()));
	}
	
	public function addWarning($msg = null)
	{
		return $this->setData('warnings', array_merge(array($msg), $this->getData('warnings') ? $this->getData('warnings') : array()));
	}
	
	public function getLinkId()
	{
		return $this->getData('link_to_' . $this->_identifier);
	}
	
}