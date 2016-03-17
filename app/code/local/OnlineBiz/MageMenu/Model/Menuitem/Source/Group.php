<?php
/**
 * OnlineBiz_MageMenu_Model_Menuitem_Source_Position
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Model_Menuitem_Source_Group extends Mage_Eav_Model_Entity_Attribute_Source_Table
{
    public function getAllOptions()
    {
        if (!$this->_options) {
			
            $this->_options = Mage::getResourceModel('customer/group_collection')
                ->load()
                ->toOptionArray()
            ;
        }
        return $this->_options;
    }
}
