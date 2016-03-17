<?php
/**
 * OnlineBiz_MageMenu_Model_Menuitem_Type_External
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Model_Menuitem_Type_External extends OnlineBiz_MageMenu_Model_Menuitem_Type_Abstract
{
	protected $_identifier = 'external';
	protected $_model = null;
	
	public function isActive()
	{
		return $this->getData('link_to_external');
	}
	
	public function getUrl($addBaseUrl = false)
	{
    	$url = $this->getData('link_to_external');
    	return ! preg_match('/^(http\:\/\/)/is', $url) ? 'http://' . $url : $url;
	}
	
	public function isValid()
	{
		if( ! preg_match('/\./', $this->getData('link_to_external'))) {
			$this->addWarning(Mage::helper('magemenu')->__('External link is incorrect'));
			$valid = false;			
		}
		
		return parent::isValid();
	}
}