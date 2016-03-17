<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

class Amasty_Preorder_Block_Product_View_Composite_Configurable extends Amasty_Preorder_Block_Product_View_Composite
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/ampreorder/product_view_composite_configurable.phtml');
    }

    protected function getConfigurablePreorderMap()
    {
        /** @var Mage_Catalog_Model_Product_Type_Configurable $typeInstance */
        $typeInstance = $this->_product->getTypeInstance();

        $elementaryProducts = $typeInstance->getUsedProducts(null, $this->_product);

        $map = array();
        foreach ($elementaryProducts as $product) {
            /** @var Mage_Catalog_Model_Product $product */
            $map[$product->getId()] = $this->_helper->getIsProductPreorder($product);
        }

        return $map;
    }

    protected function getConfigurableMessageMap()
    {
        /** @var Mage_Catalog_Model_Product_Type_Configurable $typeInstance */
        $typeInstance = $this->_product->getTypeInstance();

        $elementaryProducts = $typeInstance->getUsedProducts(null, $this->_product);

        $map = array();
        foreach ($elementaryProducts as $product) {
            /** @var Mage_Catalog_Model_Product $product */
            $map[$product->getId()] = $this->_helper->getProductPreorderNote($product);
        }

        return $map;
    }

    protected function getConfigurableCartLabelMap()
    {
        /** @var Mage_Catalog_Model_Product_Type_Configurable $typeInstance */
        $typeInstance = $this->_product->getTypeInstance();

        $elementaryProducts = $typeInstance->getUsedProducts(null, $this->_product);

        $map = array();
        foreach ($elementaryProducts as $product) {
            /** @var Mage_Catalog_Model_Product $product */
            $map[$product->getId()] = $this->_helper->getProductPreorderCartLabel($product);
        }

        return $map;
    }
}