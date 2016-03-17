<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

class Amasty_Preorder_Block_Rewrite_Bundle_Adminhtml_Sales_Order_View_Items_Renderer extends Mage_Bundle_Block_Adminhtml_Sales_Order_View_Items_Renderer
{
    /**
     * @param Mage_Sales_Model_Order_Item $item
     * @return string
     */
    public function getValueHtml($item)
    {
        $result = parent::getValueHtml($item);

        /** @var Amasty_Preorder_Helper_Data $helper */
        $helper = Mage::helper('ampreorder');
        $isPreorder = $helper->getOrderItemIsPreorderFlag($item->getId());

        if ($isPreorder) {
            /** @var Amasty_Preorder_Helper_Html $htmlHelper */
            $htmlHelper = Mage::helper('ampreorder/html');
            $result .= ' ' . $htmlHelper->getPreorderTag();
        }

        return $result;
    }
}