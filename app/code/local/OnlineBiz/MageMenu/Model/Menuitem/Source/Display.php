<?php
/**
 * OnlineBiz_MageMenu_Model_Menuitem_Source_Display
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Model_Menuitem_Source_Display extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{ 
	const DISPLAY_NAME 			= 'name';
	const DISPLAY_IMAGE 		= 'image';
	const DISPLAY_ICON 			= 'icon';
	const DISPLAY_BACKGROUND 	= 'background';
	
    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
    	return array(
    		self::DISPLAY_NAME 			=> Mage::helper('magemenu')->__('Name'),
    		self::DISPLAY_IMAGE 		=> Mage::helper('magemenu')->__('Image'),
    		self::DISPLAY_ICON 			=> Mage::helper('magemenu')->__('Icon And Name'),
    		self::DISPLAY_BACKGROUND 	=> Mage::helper('magemenu')->__('Background And Name'),
    	);
    }
    
    public function getAllOptions()
    {
    	return $this->getOptionArray();
    }
}