<?php
/**
 * OnlineBiz_MageMenu_Model_Menuitem_Source_Target
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Model_Menuitem_Source_Target extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{ 
    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
    	return array(
    		'_self' => Mage::helper('magemenu')->__('Same Window'),
    		'_blank' => Mage::helper('magemenu')->__('New Window'),
    	);
    }
    
    public function getAllOptions()
    {
    	return $this->getOptionArray();
    }
}