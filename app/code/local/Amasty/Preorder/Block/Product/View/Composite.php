<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */
abstract class Amasty_Preorder_Block_Product_View_Composite extends Mage_Core_Block_Template
{
    /** @var  Mage_Catalog_Model_Product */
    protected $_product;

    /** @var  Amasty_Preorder_Helper_Data */
    protected $_helper;

    protected $_defaultPreorderNote;
    public function __construct()
    {
        parent::__construct();
        $this->_helper = Mage::helper('ampreorder');
    }

    public function setProduct(Mage_Catalog_Model_Product $product)
    {
        $this->_product = clone $product;
    }

    protected function getPreorderCartLabel()
    {
        return Mage::getStoreConfig('ampreorder/general/addtocartbuttontext');
    }
}