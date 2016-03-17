<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */
class Amasty_Preorder_Model_Resource_Order_Preorder extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('ampreorder/order_preorder', 'id');
    }

    public function getWarningByOrderId($orderId)
    {
        $connection = $this->getReadConnection();
        $table = $this->getTable('ampreorder/order_preorder');

        $select = $connection->select()->from($table)->where('order_id = ' . $orderId);

        $result = $connection->fetchRow($select);
        return $result['warning'];
    }
}