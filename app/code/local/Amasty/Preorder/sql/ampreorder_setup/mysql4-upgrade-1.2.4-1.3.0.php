<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

/** @var Amasty_Preorder_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$orderPreorderTable = $installer->getTable('ampreorder/order_preorder');

$installer->run("ALTER TABLE `{$orderPreorderTable}` ADD `warning` VARCHAR(2048) NULL DEFAULT NULL");

$installer->endSetup();