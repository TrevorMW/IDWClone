<?php
/**
 * OnlineBiz_MageMenu_Model_Menuitem_Source_Position
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Model_Menuitem_Source_Position extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	const POSITION_NOWHERE 					= 'nowhere';
	const POSITION_MENU_TOP 				= 'menu-top';
	const POSITION_MENU_BOTTOM 				= 'menu-bottom';
	const POSITION_CONTENT_TOP 				= 'content-top';
	const POSITION_SIDEBAR_RIGHT_TOP 		= 'sidebar-right-top';
	const POSITION_SIDEBAR_RIGHT_BOTTOM 	= 'sidebar-right-bottom';
	const POSITION_SIDEBAR_LEFT_TOP 		= 'sidebar-left-top';
	const POSITION_SIDEBAR_LEFT_BOTTOM 		= 'sidebar-left-bottom';
	const POSITION_FOOTER_TOP 				= 'footer-top';
	const POSITION_FOOTER_BOTTOM			= 'footer-bottom';
	const POSITION_PAGE_BOTTOM 				= 'page-bottom';
	
    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
    	return array(
     		self::POSITION_NOWHERE 					=> Mage::helper('magemenu')->__('Nowhere (For Manual Positioning)'),
     		self::POSITION_MENU_TOP 				=> Mage::helper('magemenu')->__('Before Top Menu'),
    		self::POSITION_MENU_BOTTOM 				=> Mage::helper('magemenu')->__('After Top Menu'),
    		self::POSITION_CONTENT_TOP 				=> Mage::helper('magemenu')->__('Content Top'),
    		self::POSITION_SIDEBAR_RIGHT_TOP 		=> Mage::helper('magemenu')->__('Sidebar Right Top'),
    		self::POSITION_SIDEBAR_RIGHT_BOTTOM 	=> Mage::helper('magemenu')->__('Sidebar Right Bottom'),
    		self::POSITION_SIDEBAR_LEFT_TOP 		=> Mage::helper('magemenu')->__('Sidebar Left Top'),
    		self::POSITION_SIDEBAR_LEFT_BOTTOM 		=> Mage::helper('magemenu')->__('Sidebar Left Bottom'),
    		self::POSITION_FOOTER_TOP 				=> Mage::helper('magemenu')->__('Footer Top'),
    		self::POSITION_FOOTER_BOTTOM			=> Mage::helper('magemenu')->__('Footer Bottom'),
    		self::POSITION_PAGE_BOTTOM 				=> Mage::helper('magemenu')->__('Page Bottom'),
    	);
    }
    
    public function getAllOptions()
    {
    	return $this->getOptionArray();
    }
}