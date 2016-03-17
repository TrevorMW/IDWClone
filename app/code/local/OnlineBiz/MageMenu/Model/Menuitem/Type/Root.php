<?php
/**
 * OnlineBiz_MageMenu_Model_Menuitem_Type_Root
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Model_Menuitem_Type_Root extends OnlineBiz_MageMenu_Model_Menuitem_Type_Abstract
{
	protected $_identifier = 'root';
	protected $_model = null;
	
	public function isValid()
	{
	    return true;
	}
}