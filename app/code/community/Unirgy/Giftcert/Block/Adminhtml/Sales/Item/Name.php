<?php
/**
 * Unirgy_Giftcert extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @copyright  Copyright (c) 2008 Unirgy LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @author     Boris (Moshe) Gurevich <moshe@unirgy.com>
 */
class Unirgy_Giftcert_Block_Adminhtml_Sales_Item_Name extends Mage_Adminhtml_Block_Sales_Items_Column_Name
{
    public function getOrderOptions()
    {
        $result = parent::getOrderOptions();
        /** @noinspection PhpUndefinedMethodInspection */
        Mage::helper('ugiftcert')->addOrderItemCertOptions($result, $this->getItem());

        return $result;
    }

    public function getFormattedOption($value)
    {
        $return = parent::getFormattedOption($value);
        if (is_array($return)) {
            foreach ($return as $k => $v) {
                $return[$k] = $this->htmlEscape($v);
            }
        } elseif (!$return && is_string($value)) {
            $return['value'] = $this->htmlEscape($value);
        }

        return $return;
    }
}
