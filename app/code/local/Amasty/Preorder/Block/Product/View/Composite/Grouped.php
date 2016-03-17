<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

class Amasty_Preorder_Block_Product_View_Composite_Grouped extends Amasty_Preorder_Block_Product_View_Composite
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/ampreorder/product_view_composite_grouped.phtml');
    }

    protected function getGroupPreorderMap()
    {
        /** @var Mage_Catalog_Model_Product_Type_Grouped $typeInstance */
        $typeInstance = $this->_product->getTypeInstance();

        $elementaryProducts = $typeInstance->getAssociatedProducts($this->_product);

        $map = array();
        foreach ($elementaryProducts as $product) {
            /** @var Mage_Catalog_Model_Product $product */
            $map[$product->getId()] = $this->_helper->getIsProductPreorder($product);
        }

        return $map;
    }
}