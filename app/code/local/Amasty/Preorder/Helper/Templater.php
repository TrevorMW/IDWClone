<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */
class Amasty_Preorder_Helper_Templater extends Mage_Core_Helper_Abstract
{
    /** @var Mage_Catalog_Model_Product */
    protected $product;

    public function process($template, Mage_Catalog_Model_Product $product)
    {
        $this->product = $product;
        $result = preg_replace_callback('/\{([^\{\}]+)\}/', array($this, 'attributeReplaceCallback'), $template);
        return $result;
    }

    protected function attributeReplaceCallback($match)
    {
        $attributeCode = $match[1];
        $value = $this->product->getResource()->getAttributeRawValue($this->product->getId(), $attributeCode, Mage::app()->getStore());

        $attributes = $this->product->getResource()->getAttributesByCode();
        if (isset($attributes[$attributeCode])) {
            /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attribute */
            $attribute = $attributes[$attributeCode];
            $frontend = $attribute->getFrontendInput();

            if ($frontend == 'select') {
                $value = $attribute->getSource()->getOptionText($value);
            } else if ($frontend == 'date') {
                $value = Mage::helper('core')->formatDate($value, 'medium' , false);
            }
        }

        return ($value === false) ? '' : $value;
    }
}