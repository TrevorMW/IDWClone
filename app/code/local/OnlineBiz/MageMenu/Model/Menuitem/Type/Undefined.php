<?php
/**
 * OnlineBiz_MageMenu_Model_Menuitem_Type_Undefined
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Model_Menuitem_Type_Undefined extends OnlineBiz_MageMenu_Model_Menuitem_Type_Abstract
{
	protected $_identifier = 'undefined';
	protected $_model = null;
	
	public function isValid()
	{
	    return true;
	}
}