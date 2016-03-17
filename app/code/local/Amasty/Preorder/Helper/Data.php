<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */
class Amasty_Preorder_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function processNewOrder(Mage_Sales_Model_Order $order)
    {
        if (is_null($order->getId())) {
            $order->save();
        }

        /** @var Mage_Sales_Model_Entity_Order_Item_Collection $itemCollection */
        $itemCollection = Mage::getResourceModel('sales/order_item_collection');
        $itemCollection->setOrderFilter($order->getId());

        $orderIsPreorder = false;
        foreach ($itemCollection as $item) {
            /** @var Mage_Sales_Model_Order_Item $item */

            $orderItemIsPreorder = $this->getOrderItemIsPreorder($item);
            $this->saveOrderItemPreorderFlag($item, $orderItemIsPreorder);

            $orderIsPreorder |= $orderItemIsPreorder;
        }

        /** @var Amasty_Preorder_Model_Order_Preorder $orderPreorder */
        $orderPreorder = Mage::getModel('ampreorder/order_preorder');

        $orderPreorder->setOrderId($order->getId());
        $orderPreorder->setIsPreorder($orderIsPreorder);
        if ($orderIsPreorder) {
            $warningText = Mage::getStoreConfig('ampreorder/general/orderpreorderwarning');
            $orderPreorder->setWarning($warningText);
            $order->setData('preorder_warning', $warningText);
        }

        $orderPreorder->save();
    }

    protected function getOrderItemIsPreorder(Mage_Sales_Model_Order_Item $orderItem)
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product');
        $product->load($orderItem->getProductId());
        $result = $this->getIsProductPreorder($product);

        if (!$result) {
            foreach($orderItem->getChildrenItems() as $childItem) {
                $result = $this->getOrderItemIsPreorder($childItem);
                if ($result) {
                    break;
                }
            }
        }

        return $result;
    }

    protected function saveOrderItemPreorderFlag(Mage_Sales_Model_Order_Item $orderItem, $isPreorder)
    {
        /** @var Amasty_Preorder_Model_Order_Item_Preorder $orderItemPreorder */
        $orderItemPreorder = Mage::getModel('ampreorder/order_item_preorder');

        $orderItemPreorder->setOrderItemId($orderItem->getId());
        $orderItemPreorder->setIsPreorder($isPreorder);

        $orderItemPreorder->save();
    }

    public function getQuoteItemIsPreorder(Mage_Sales_Model_Quote_Item $item)
    {
        $product = $item->getProduct();

        if ($product->isComposite()) {
            $productTypeInstance = $product->getTypeInstance();

            if ($productTypeInstance instanceof Mage_Catalog_Model_Product_Type_Configurable) {
                /** @var Mage_Catalog_Model_Product_Type_Configurable $productTypeInstance */

                /** @var Mage_Sales_Model_Quote_Item_Option $option */
                $option = $item->getOptionByCode('simple_product');
                $simpleProduct = $option->getProduct();
                return $this->getIsProductPreorder($simpleProduct);
            }

            if ($productTypeInstance instanceof Mage_Bundle_Model_Product_Type) {
                /** @var Mage_Bundle_Model_Product_Type $productTypeInstance */

                $isPreorder = false;
                foreach ($item->getChildren() as $childItem) {
                    if ($this->getQuoteItemIsPreorder($childItem)) {
                        $isPreorder = true;
                        break;
                    }
                }
                return $isPreorder;
            }
        } else {
            return $this->getIsProductPreorder($product);
        }

        return false;
    }

    public function getIsProductPreorder(Mage_Catalog_Model_Product $product)
    {
        if ($product->isComposite()) {
            $result = $this->getIsCompositeProductPreorder($product);
        } else {
            $result = $this->getIsSimpleProductPreorder($product);
        }

        return $result;
    }

    protected function getIsCompositeProductPreorder(Mage_Catalog_Model_Product $product)
    {
        if (!Mage::getStoreConfig('ampreorder/additional/discovercompositeoptions'))
        {
            // We never know what options customer will select
            return false;
        }

        $typeId = $product->getTypeId();
        $typeInstance = $product->getTypeInstance();

        switch ($typeId) {
            case 'grouped':
                $result = $this->getIsGroupedProductPreorder($typeInstance);
                break;

            case 'configurable':
                $result = $this->getIsConfigurableProductPreorder($typeInstance);
                break;

            case 'bundle':
                $result = $this->getIsBundleProductPreorder($typeInstance);
                break;

            default:
                Mage::log('Cannot determinate pre-order status of product of unknown product type: ' . $typeId, Zend_Log::WARN);
                $result = false;
        }

        // Still have no implementation for bundles
        return $result;
    }

    protected function getIsGroupedProductPreorder(Mage_Catalog_Model_Product_Type_Grouped $typeInstance)
    {
        $elementaryProducts = $typeInstance->getAssociatedProducts();

        if (count($elementaryProducts) == 0) {
            return false;
        }

        $result = true; // for a while
        foreach ($elementaryProducts as $elementary) {
            if (!$this->getIsSimpleProductPreorder($elementary)) {
                $result = false;
                break;
            }
        }

        return $result;
    }

    protected function getIsConfigurableProductPreorder(Mage_Catalog_Model_Product_Type_Configurable $typeInstance)
    {
        $elementaryProducts = $typeInstance->getUsedProducts();

        if (count($elementaryProducts) == 0) {
            return false;
        }

        $result = true; // for a while
        foreach ($elementaryProducts as $elementary) {
            /** @var Mage_Catalog_Model_Product $elementary */
            if (!$this->getIsSimpleProductPreorder($elementary)) {
                $result = false;
                break;
            }
        }

        return $result;
    }

    protected function getIsBundleProductPreorder(Mage_Bundle_Model_Product_Type $typeInstance)
    {
        $optionIds = array();
        $optionSelectionCounts = array();
        $optionPreorder = array();

        $options = $typeInstance->getOptionsCollection();
        foreach ($options as $option) {
            /** @var Mage_Bundle_Model_Option $option */
            if (!$option->getRequired()) {
                continue;
            }

            $id = $option->getId();
            $optionIds[] = $id;
            $optionSelectionCounts[$id] = 0; // for a while
            $optionPreorder[$id] = true; // for a while
        }
        if (!$optionIds) {
            return false;
        }

        $selections = $typeInstance->getSelectionsCollection($optionIds);
        $products = $this->getProductCollectionBySelectionsCollection($selections);
        foreach ($selections as $selection) {
            /** @var Mage_Bundle_Model_Selection $selection */

            /** @var Mage_Catalog_Model_Product $product */
            $product = $products->getItemById($selection->getProductId());

            $isPreorder = $this->getIsSimpleProductPreorder($product);
            $optionId = $selection->getOptionId();
            $optionSelectionCounts[$optionId]++;
            if (!$isPreorder) {
                $optionPreorder[$optionId] = false;
            }
        }

        $result = false; // for a while
        foreach ($optionPreorder as $id => $isPreorder) {
            if ($isPreorder && $optionSelectionCounts[$id] > 0) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * @param Mage_Bundle_Model_Mysql4_Selection_Collection $selections
     * @return Mage_Catalog_Model_Resource_Product_Collection $products
     */
    protected function getProductCollectionBySelectionsCollection($selections)
    {
        $productIds = array();
        foreach ($selections as $selection) {
            /** @var Mage_Bundle_Model_Selection $selection */
            $productIds[] = $selection->getProductId();
        }

        /** @var Mage_Catalog_Model_Product $model */
        $model = Mage::getModel('catalog/product');
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = $model->getCollection();
        $collection->addFieldToFilter('entity_id', array('in', $productIds));

        return $collection;
    }

    protected function getIsSimpleProductPreorder(Mage_Catalog_Model_Product $product)
    {
        /** @var Mage_CatalogInventory_Model_Stock_Item $inventory */
        $inventory = Mage::getModel('cataloginventory/stock_item');
        $inventory->loadByProduct($product);

        $isPreorder = $inventory->getBackorders() == Amasty_Preorder_Model_Rewrite_CatalogInventory_Source_Backorders::BACKORDERS_PREORDER;
        $disabledByQty = $this->disableForPositiveQty() && $inventory->getQty() > 0;

        $result = $isPreorder && !$disabledByQty;

        return $result;
    }

    public function getOrderIsPreorderFlagByIncrementId($incrementId)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order');
        $order->load((int) $incrementId, 'increment_id');

        if (!$order->getId()) {
            $message = 'Preorder: Cannot load order by incrementId = ' . $incrementId;
            Mage::log($message, Zend_Log::ALERT);
            return false;
        }

        return $this->getOrderIsPreorderFlag($order);
    }

    public function getOrderIsPreorderFlag(Mage_Sales_Model_Order $order)
    {
        if (is_null($order)) {
            Mage::log('Preorder: Cannot load preorder flag for null order. Processing as a regular order.', Zend_Log::ALERT);
            return false;
        }

        /** @var Amasty_Preorder_Model_Resource_Order_Preorder_Collection $orderPreorderCollection */
        $orderPreorderCollection = Mage::getModel('ampreorder/order_preorder')->getCollection();
        $orderPreorderCollection->addFieldToFilter('order_id', $order->getId());
        $orderPreorderCollection->addFieldToSelect('is_preorder');

        /** @var Amasty_Preorder_Model_Order_Preorder $orderPreorder */
        $orderPreorder = $orderPreorderCollection->getFirstItem();

        return is_object($orderPreorder) ? $orderPreorder->getIsPreorder() : false;
    }

    public function getOrderPreorderWarning($orderId)
    {
        /** @var Amasty_Preorder_Model_Resource_Order_Preorder $orderPreorderResource */
        $orderPreorderResource = Mage::getResourceModel('ampreorder/order_preorder');
        $warning = $orderPreorderResource->getWarningByOrderId($orderId);
        if (is_null($warning)) {
            $warning = Mage::getStoreConfig('ampreorder/general/orderpreorderwarning');
        }

        return $warning;
    }

    public function getOrderItemIsPreorderFlag($itemId)
    {
        /** @var Amasty_Preorder_Model_Resource_Order_Preorder_Collection $orderItemPreorderCollection */
        $orderItemPreorderCollection = Mage::getModel('ampreorder/order_item_preorder')->getCollection();
        $orderItemPreorderCollection->addFieldToFilter('order_item_id', $itemId);
        $orderItemPreorderCollection->addFieldToSelect('is_preorder');

        /** @var Amasty_Preorder_Model_Order_Preorder $orderItemPreorder */
        $orderItemPreorder = $orderItemPreorderCollection->getFirstItem();

        return is_object($orderItemPreorder) ? $orderItemPreorder->getIsPreorder() : false;
    }

    public function getQuoteItemPreorderNote(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        if ($quoteItem->getProductType() == 'configurable') {
            $option = $quoteItem->getOptionByCode('simple_product');
            $simpleProduct = $option->getProduct();
            return $this->getProductPreorderNote($simpleProduct);
        } else {
            return $this->getProductPreorderNote($quoteItem->getProduct());
        }
    }

    public function getProductPreorderNote(Mage_Catalog_Model_Product $product)
    {
        $template = $product->getData('preorder_note');
        if (is_null($template)) {
            $resource = $product->getResource();
            $template = $resource->getAttributeRawValue($product->getId(), 'preorder_note', Mage::app()->getStore());
        }

        if ($template == "") {
            $template = Mage::getStoreConfig('ampreorder/general/defaultpreordernote');
        }

        /** @var Amasty_Preorder_Helper_Templater $templater */
        $templater = Mage::helper('ampreorder/templater');
        $note = $templater->process($template, $product);

        return $note;
    }

    public function getProductPreorderCartLabel(Mage_Catalog_Model_Product $product)
    {
        $template = $product->getData('preorder_cart_label');
        if (is_null($template)) {
            $resource = $product->getResource();
            $template = $resource->getAttributeRawValue($product->getId(), 'preorder_cart_label', Mage::app()->getStore());
        }

        if ($template == "") {
            $template = Mage::getStoreConfig('ampreorder/general/addtocartbuttontext');
        }

        /** @var Amasty_Preorder_Helper_Templater $templater */
        $templater = Mage::helper('ampreorder/templater');
        $note = $templater->process($template, $product);

        return $note;
    }

    public function preordersEnabled()
    {
        return Mage::getStoreConfig('ampreorder/functional/enabled');
    }

    public function disableForPositiveQty()
    {
        return Mage::getStoreConfig('ampreorder/functional/allowemptyqty') && Mage::getStoreConfig('ampreorder/functional/disableforpositiveqty');
    }
}