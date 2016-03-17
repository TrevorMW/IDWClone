<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */
class Amasty_Preorder_Model_Rewrite_CatalogInventory_Source_Backorders extends Mage_CatalogInventory_Model_Source_Backorders
{
    const BACKORDERS_PREORDER = 101;

    public function toOptionArray()
    {
        $data = parent::toOptionArray();
        $data[] = array('value' => self::BACKORDERS_PREORDER, 'label'=> Mage::helper('ampreorder')->__('Allow Pre-Orders'));

        return $data;
    }
}
