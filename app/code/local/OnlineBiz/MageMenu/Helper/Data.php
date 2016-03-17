<?php
/**
 * OnlineBiz_MageMenu_Helper_Data
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function isActivated()
	{
		return OnlineBiz_ObBase_Helper_Data::isActivated('OnlineBiz_MageMenu', Mage::getStoreConfig('magemenu/general/key'),'magemenu/general/enabled');
	}
	
	
    public function getAttributeHiddenFields()
    {
        if (Mage::registry('attribute_type_hidden_fields')) {
            return Mage::registry('attribute_type_hidden_fields');
        } else {
            return array();
        }
    }

    public function getAttributeDisabledTypes()
    {
        if (Mage::registry('attribute_type_disabled_types')) {
            return Mage::registry('attribute_type_disabled_types');
        } else {
            return array();
        }
    }
}