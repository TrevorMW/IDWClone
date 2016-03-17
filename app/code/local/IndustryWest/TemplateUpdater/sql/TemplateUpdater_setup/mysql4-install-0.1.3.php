<?php

$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$setup->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'thin_banner_text', array(
    'group'             => 'General',
    'type'              => 'varchar',//can be int, varchar, decimal, text, datetime
    'backend'           => '',
    'frontend_input'    => '',
    'frontend'          => '',
    'label'             => 'Thin Banner Message',
    'input'             => 'text',
    'default'           => '',
    'class'             => '',
    'source'            => '', //this is necessary for select and multilelect, for the rest leave it blank
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL, //scope can be SCOPE_STORE or SCOPE_GLOBAL or SCOPE_WEBSITE
    'visible'           => true,
    'frontend_class'    => '',
    'required'          => false,//or true
    'user_defined'      => true,
    'position'          => 100, //any number will do
));

$setup->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'show_thin_banner', array(
    'group'             => 'General',
    'type'              => 'int',//can be int, varchar, decimal, text, datetime
    'backend'           => '',
    'frontend_input'    => '',
    'frontend'          => '',
    'label'             => 'Show Message?',
    'input'             => 'select',
    'default'           => 0,
    'class'             => '',
    'source'            => 'eav/entity_attribute_source_boolean',//this is necessary for select and multilelect, for the rest leave it blank
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,//scope can be SCOPE_STORE or SCOPE_GLOBAL or SCOPE_WEBSITE
    'visible'           => true,
    'frontend_class'    => '',
    'required'          => false,//or true
    'user_defined'      => true,
    'position'          => 100, //any number will do
));

$installer->endSetup();