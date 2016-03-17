<?php

$installer = $this;
/* @var $installer OnlineBiz_MageMenu_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();

// Update Entities...
$installer->installEntities();

// Modify Groups and Attributes for Menu
$entityTypeId     = $installer->getEntityTypeId('magemenu');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

// Update General Group
$installer->updateAttributeGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'attribute_group_name',
    'General Information'
);
$installer->updateAttributeGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'sort_order',
    '10'
);

// Add groups
$groups = array(
    'design'    => array(
        'name'  => 'Custom Options',
        'sort'  => 20,
        'id'    => null
    )
);

foreach ($groups as $k => $groupProp) {
    $installer->addAttributeGroup($entityTypeId, $attributeSetId, $groupProp['name'], $groupProp['sort']);
    $groups[$k]['id'] = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, $groupProp['name']);
}


// update attributes group and sort
$installer->getOrder(0);
$attributes = array(
	// General tab
    'link_to' => array(
        'group' => 'general',
        'sort'  => $installer->getOrder(),
    ),
    'link_to_category' => array(
        'group' => 'general',
        'sort'  => $installer->getOrder(),
    ),
    'link_to_cms_page' => array(
        'group' => 'general',
        'sort'  => $installer->getOrder(),
    ),
    'link_to_product' => array(
        'group' => 'general',
        'sort'  => $installer->getOrder(),
    ),
    'link_to_internal' => array(
        'group' => 'general',
        'sort'  => $installer->getOrder(),
    ),
    'link_to_external' => array(
        'group' => 'general',
        'sort'  => $installer->getOrder(),
    ),
    'link_target' => array(
        'group' => 'general',
        'sort'  => $installer->getOrder(),    
    ),
    'name' => array(
        'group' => 'general',
        'sort'  => $installer->getOrder(),
    ),
    'is_active' => array(
        'group' => 'general',
        'sort'  => $installer->getOrder(),
	),
	 'group_access' => array(
        'group' => 'general',
        'sort'  => $installer->getOrder(),
	),

    
    // Design tab
    'template' => array(
        'group' => 'design',
        'sort'  => $installer->getOrder(10),
    ),
	'popup_width' => array(
        'group' => 'design',
        'sort'  => $installer->getOrder(),
    ),
	'popup_column' => array(
        'group' => 'design',
        'sort'  => $installer->getOrder(),
    ),
	'popup_column_width' => array(
        'group' => 'design',
        'sort'  => $installer->getOrder(),
    ),
    'cmsblock' => array(
        'group' => 'design',
        'sort'  => $installer->getOrder(),
    ),
    'display_mode' => array(
        'group' => 'design',
        'sort'  => $installer->getOrder(),
    ),
    'display_name' => array(
        'group' => 'design',
        'sort'  => $installer->getOrder(),
    ),
    'image' => array(
        'group' => 'design',
        'sort'  => $installer->getOrder(),
	),
    'font_weight' => array(
        'group' => 'design',
        'sort'  => $installer->getOrder(),
    ),
    'font_color' => array(
        'group' => 'design',
        'sort'  => $installer->getOrder(),
    ),
    'custom_css_class' => array(
        'group' => 'design',
        'sort'  => $installer->getOrder(),
    ),
);

$groups['general']['id'] = $attributeGroupId;
foreach ($attributes as $attributeCode => $attributeProp) {
    $installer->addAttributeToGroup(
        $entityTypeId,
        $attributeSetId,
        $groups[$attributeProp['group']]['id'],
        $attributeCode,
        $attributeProp['sort']
    );
}

$installer->endSetup();
