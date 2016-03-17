<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

/**
 * Class Amasty_Preorder_Model_Order_Preorder
 *
 * @method Amasty_Preorder_Model_Order_Preorder setOrderId(int $orderId)
 * @method int getOrderId()
 * @method Amasty_Preorder_Model_Order_Preorder setIsPreorder(int $isPreorder)
 * @method int getIsPreorder()
 * @method Amasty_Preorder_Model_Order_Preorder setWarning(string $warning)
 * @method string getWarning()
 */
class Amasty_Preorder_Model_Order_Preorder extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('ampreorder/order_preorder');
    }
}