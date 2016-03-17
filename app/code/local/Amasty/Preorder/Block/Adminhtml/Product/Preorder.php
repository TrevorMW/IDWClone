<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */
class Amasty_Preorder_Block_Adminhtml_Product_Preorder extends Mage_Core_Block_Template
{
    const TEMPLATE_GENERIC = 'amasty/ampreorder/product_preorder_edit.phtml';
    const TEMPLATE_MASS = 'amasty/ampreorder/product_preorder_massedit.phtml';

    /** @var  Mage_Catalog_Model_Product */
    protected $_product;

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    public function setProduct(Mage_Catalog_Model_Product $product = null)
    {
        $this->_product = $product;

        $template = is_object($product) ?
            self::TEMPLATE_GENERIC :
            self::TEMPLATE_MASS;
        $this->setTemplate($template);
    }

    protected function getNote()
    {
        $result = is_object($this->_product) ? $this->_product->getData('preorder_note') : null;
        return $result;
    }

    protected function getCartLabel()
    {
        $result = is_object($this->_product) ? $this->_product->getData('preorder_cart_label') : null;
        return $result;
    }

    protected function getDefaultBackordersValue()
    {
        return Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_ITEM . 'backorders');
    }
}