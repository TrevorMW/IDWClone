<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */
class Amasty_Preorder_Block_Note extends Mage_Core_Block_Template
{
    const TEMPLATE_GENERIC = 'amasty/ampreorder/note_generic.phtml';
    const TEMPLATE_CART = 'amasty/ampreorder/note_cart.phtml';

    /** @var  Mage_Catalog_Model_Product */
    protected $_product;

    /** @var  Mage_Sales_Model_Quote_Item */
    protected $_quoteItem;

    /** @var  Amasty_Preorder_Helper_Data */
    protected $_helper;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate(self::TEMPLATE_GENERIC);
        $this->_helper = Mage::helper('ampreorder');
    }

    public function setProduct(Mage_Catalog_Model_Product $product)
    {
        $this->_quoteItem = null;
        $this->_product = $product;
    }

    public function setQuoteItem(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        $this->_quoteItem = $quoteItem;
        $this->_product = $quoteItem->getProduct();
    }

    protected function getPreorderNote()
    {
        return isset($this->_quoteItem)
            ? $this->_helper->getQuoteItemPreorderNote($this->_quoteItem)
            : $this->_helper->getProductPreorderNote($this->_product);
    }

    protected function getDefaultBackordersValue()
    {
        return Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_ITEM . 'backorders');
    }

    protected function _toHtml()
    {
        return $this->isVisible() ? parent::_toHtml() : '';
    }

    protected function isVisible()
    {
        return isset($this->_quoteItem)
            ? $this->_helper->getQuoteItemIsPreorder($this->_quoteItem)
            : $this->_helper->getIsProductPreorder($this->_product);
    }
}