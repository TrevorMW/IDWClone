<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */
class Amasty_Preorder_Model_Observer
{
    /** @var Amasty_Preorder_Helper_Html */
    protected $_htmlHelper;

    /** @var  Amasty_Preorder_Helper_Data */
    protected $_dataHelper;

    public function __construct()
    {
        $this->_htmlHelper = Mage::helper('ampreorder/html');
        $this->_dataHelper = Mage::helper('ampreorder');
    }

    public function onBlockAbstractToHtmlAfter(Varien_Event_Observer $observer) //core_block_abstract_to_html_after
    {
        $block = $observer->getBlock();
        $html = $observer->getTransport()->getHtml();

        if ($block instanceof Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Inventory || $block instanceof Mage_Adminhtml_Block_Catalog_Product_Edit_Action_Attribute_Tab_Inventory) {
            /** @var Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Inventory $block */

            $product = $block->getProduct();

            $html = $this->_htmlHelper->injectAdminProductPreorderNoteField($html, $product);
            $html = $this->_htmlHelper->injectAdminProductInventoryJS($html);
        }

        if ($block instanceof Mage_Adminhtml_Block_Sales_Items_Column_Name) {
            /** @var Mage_Adminhtml_Block_Sales_Items_Column_Name $block */

            $html = $this->_htmlHelper->injectAdminOrderItemPreorderStatus($html, $block->getItem());
        }

        if ($this->_dataHelper->preordersEnabled()) {
            if ($block instanceof Mage_Catalog_Block_Product_View) {
                /** @var Mage_Catalog_Block_Product_View $block */

                $product = $block->getProduct();
                $html = $this->injectProductViewCode($html, $product);
            }

            if ($block instanceof Mage_Catalog_Block_Product_List) {
                /** @var Mage_Catalog_Block_Product_List $block */

                $html = $this->_htmlHelper->injectProductListPreorderNote($html);
                $html = $this->_htmlHelper->injectProductListCartButtonLabel($html);
            }

            if ($block instanceof Mage_Checkout_Block_Cart) {
                /** @var Mage_Checkout_Block_Cart $block */

                $html = $this->_htmlHelper->injectCartPreorderNote($html);
            }
        }

        if ($block instanceof Mage_Sales_Block_Order_Recent || $block instanceof Mage_Sales_Block_Order_History) {
            /** @var  Mage_Sales_Block_Order_Recent $block */

            $html = $this->_htmlHelper->injectOrderListPreorderTag($html);
        }

        if ($block instanceof Mage_Sales_Block_Order_Info) {
            /** @var  Mage_Sales_Block_Order_Info $block */

            $order = $block->getOrder();
            $html = $this->_htmlHelper->injectOrderViewPreorderWarning($html, $order);
        }

        if ($block instanceof Mage_Sales_Block_Order_Email_Items) {
            /** @var  Mage_Sales_Block_Order_Email_Items $block */

            if (Mage::getStoreConfig('ampreorder/additional/autoaddwarningtoemail')) {
                $order = $block->getOrder();
                if (is_object($order)) {
                    $html = $this->_htmlHelper->injectEmailOrderConfirmationPreorderWarning($html, $order);
                }
            }
        }

        $observer->getTransport()->setHtml($html);
    }

    public function onSalesOrderPlaceAfter(Varien_Event_Observer $observer) //sales_order_place_after
    {
        if (!$this->_dataHelper->preordersEnabled()) {
            return;
        }

        $order = $observer->getEvent()->getOrder();

        $this->_dataHelper->processNewOrder($order);
    }

    public function onSalesOrderGridCollectionLoadBefore(Varien_Event_Observer $observer) //sales_order_grid_collection_load_before
    {
        if (Mage::app()->getRequest()->getControllerName() == 'customer') {
            // Strange fatal error at Customer -> Orders in EE
            return;
        }

        /** @var Mage_Sales_Model_Resource_Order_Collection $collection */
        $collection = $observer->getOrderGridCollection();
        $select = $collection->getSelect();


        if (strpos((string)$select, 'amasty_ampreorder_order_preorder') === false) {
            $select->joinLeft(
                array('preorder'=>$collection->getTable('ampreorder/order_preorder')),
                'preorder.order_id=main_table.entity_id',
                array('preorder'=>'is_preorder')
            );
        }
    }

    protected function injectProductViewCode($html, Mage_Catalog_Model_Product $product)
    {
        if ($product->isComposite()) {
            $html = $this->_htmlHelper->injectCompositeProductViewJS($html, $product);
        }

        $html = $this->_htmlHelper->injectCartButtonLabel($html, $product);
        $html = $this->_htmlHelper->injectProductViewPreorderNote($html, $product);

        if ($product->isGrouped()) {
            $html = $this->_htmlHelper->injectProductViewPreorderNote($html, $product);
        }

        return $html;
    }
}
