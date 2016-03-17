<?php
/**
 * OnlineBiz_MageMenu_Model_Menuitem_Source_Font_Weight
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Model_Menuitem_Source_Font_Weight extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{ 
    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
    	return array(
    		'' 			=> Mage::helper('magemenu')->__('Standard'),
    		'normal' 	=> Mage::helper('magemenu')->__('Normal'),
    		'lighter' 	=> Mage::helper('magemenu')->__('Lighter'),
    		'bold' 		=> Mage::helper('magemenu')->__('Bold'),
    		'bolder' 	=> Mage::helper('magemenu')->__('Bolder'),
    	);
    }
    
    public function getAllOptions()
    {
    	return $this->getOptionArray();
    }
}