<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

class Amasty_Preorder_Block_Product_View_Composite_Bundle extends Amasty_Preorder_Block_Product_View_Composite
{
    protected $_bundleOptionsData;
    protected $_bundleSelectionsData;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/ampreorder/product_view_composite_bundle.phtml');
    }

    protected function getBundleSelectionsData()
    {
        if (is_null($this->_bundleSelectionsData)) {
            $this->prepareBundleData();
        }
        return $this->_bundleSelectionsData;
    }

    protected function getBundleOptionsData()
    {
        if (is_null($this->_bundleOptionsData)) {
            $this->prepareBundleData();
        }
        return $this->_bundleOptionsData;
    }

    protected function prepareBundleData()
    {
        $this->_bundleSelectionsData = array();
        $this->_bundleOptionsData = array();

        /** @var Mage_Bundle_Model_Product_Type $typeInstance */
        $typeInstance = $this->_product->getTypeInstance();

        $optionIds = $typeInstance->getOptionsIds($this->_product);
        foreach ($optionIds as $optionId) {
            $this->_bundleOptionsData[$optionId] = array(
                'isSingle' => null,
                'selectionCount' => 0, // for a while
                'isPreorder' => null,
                'message' => null,
            );
        }

        $selections = $typeInstance->getSelectionsCollection($optionIds, $this->_product);
        foreach ($selections as $selection) {
            /** @var Mage_Bundle_Model_Selection $selection */
            $productId = $selection->getProductId();

            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::getModel('catalog/product');
            $product->load($productId);

            $isPreorder = $this->_helper->getIsProductPreorder($product);
            $message = $this->_helper->getProductPreorderNote($product);

            $this->_bundleSelectionsData[$selection->getSelectionId()] = array(
                'isPreorder' => $isPreorder,
                'message' => $message,
                'optionId' => $selection->getOptionId(),
            );

            // Update option record
            $optionRecord = &$this->_bundleOptionsData[$selection->getOptionId()];
            $optionRecord['selectionCount']++;
            $optionRecord['isSingle'] = $optionRecord['selectionCount'] == 1;

            if ($optionRecord['isSingle']) {
                $optionRecord['isPreorder'] = $isPreorder;
                $optionRecord['message'] = $message;
            } else {
                // Have to analyze selections on frontend in order to find out
                $optionRecord['isPreorder'] = null;
                $optionRecord['message'] = null;
            }
        }
    }
}