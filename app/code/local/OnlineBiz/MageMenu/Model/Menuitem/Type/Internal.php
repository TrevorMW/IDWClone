<?php
/**
 * OnlineBiz_MageMenu_Model_Menuitem_Type_Internal
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Model_Menuitem_Type_Internal extends OnlineBiz_MageMenu_Model_Menuitem_Type_Abstract
{
	protected $_identifier = 'internal';
	protected $_model = null;
	
	public function getUrl($addBaseUrl = false)
	{
		return $addBaseUrl && $this->getData('link_to_internal') 
					? $this->_addBaseUrl($this->getData('link_to_internal')) 
					: $this->getData('link_to_internal');
	}
	
	public function isActive()
	{
		return $this->getData('link_to_internal');
	}
	
}