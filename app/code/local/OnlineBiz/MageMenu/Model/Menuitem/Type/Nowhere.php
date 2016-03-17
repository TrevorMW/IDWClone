<?php
/**
 * OnlineBiz_MageMenu_Model_Menuitem_Type_Nowhere
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Model_Menuitem_Type_Nowhere extends OnlineBiz_MageMenu_Model_Menuitem_Type_Abstract
{
	protected $_identifier = 'nowhere';
	protected $_model = null;
	
	public function isActive()
	{
		return true;
	}
	
	public function getUrl($addBaseUrl = false)
	{
		return '#';
	}
	
}