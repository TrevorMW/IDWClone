<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

/** @var Amasty_Preorder_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$installer->addAttribute('catalog_product', 'preorder_note', array(
    'type'              => 'varchar',
    'backend'           => '',
    'frontend'          => '',
    'label'             => Mage::helper('ampreorder')->__('Pre-Order Note'),
    'input'             => 'hidden',
    'class'             => '',
    'source'            => '',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => false,
    'required'          => false,
    'user_defined'      => false,
    'default'           => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'apply_to'          => '',
    'is_configurable'   => false
));


// Create OrderPreorder table

$orderPreorderTable = $installer->getTable('ampreorder/order_preorder');
$orderTable = $installer->getTable('sales/order');

$installer->run(<<<EOT
CREATE TABLE IF NOT EXISTS `{$orderPreorderTable}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `is_preorder` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE `{$orderPreorderTable}`
  ADD CONSTRAINT `{$orderPreorderTable}_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `{$orderTable}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;
EOT
);


// Create OrderItemPreorderTable

$orderItemPreorderTable = $installer->getTable('ampreorder/order_item_preorder');
$orderItemTable = $installer->getTable('sales/order_item');

$installer->run(<<<EOT
CREATE TABLE IF NOT EXISTS `{$orderItemPreorderTable}` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_item_id` int(10) unsigned NOT NULL,
  `is_preorder` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_item_id` (`order_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `{$orderItemPreorderTable}`
  ADD CONSTRAINT `{$orderItemPreorderTable}_ibfk_1` FOREIGN KEY (`order_item_id`) REFERENCES `{$orderItemTable}` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE;
EOT
);

$installer->endSetup();