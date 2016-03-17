<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

class Amasty_Preorder_Model_Rewrite_CatalogInventory_Stock_Item extends Mage_CatalogInventory_Model_Stock_Item
{
    public function checkQty($qty)
    {
        $result = parent::checkQty($qty);
        if ($result) {
            return $result;
        }

        /** @var Amasty_Preorder_Helper_Data $helper */
        $helper = Mage::helper('ampreorder');

        $preordersEnabled = $helper->preordersEnabled();
        $isPreorder = $this->getBackorders() == Amasty_Preorder_Model_Rewrite_CatalogInventory_Source_Backorders::BACKORDERS_PREORDER;
        $emptyQtyAllowed = Mage::getStoreConfig('ampreorder/functional/allowemptyqty');

        $result = $preordersEnabled && $isPreorder && $emptyQtyAllowed;

        return $result;
    }

    /**
     * Rewrote to allow pre-order items became Out of Stock back in some cases
     *
     * @param float|null $qty
     * @return bool
     */
    public function verifyStock($qty = null)
    {
        if ($qty === null) {
            $qty = $this->getQty();
        }

        if ($qty <= $this->getMinQty()) {
            if ($this->getBackorders() == Mage_CatalogInventory_Model_Stock::BACKORDERS_NO) {
                // regular case
                return false;
            }

            $isPreorder = $this->getBackorders() == Amasty_Preorder_Model_Rewrite_CatalogInventory_Source_Backorders::BACKORDERS_PREORDER;
            if ($isPreorder) {
                $emptyQtyAllowed = Mage::getStoreConfig('ampreorder/functional/allowemptyqty');
                return $emptyQtyAllowed;
            }
        }

        return true;
    }
}
