<?php
/**
 * OnlineBiz_MageMenu_Model_Resource_Eav_Mysql4_Abstract
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
abstract class OnlineBiz_MageMenu_Model_Resource_Eav_Mysql4_Abstract extends Mage_Eav_Model_Entity_Abstract
{
    /**
     * Store firstly set attributes to filter selected attributes when used specific store_id
     *
     * @var array
     */
    protected $_attributes   = array();

    /**
     * Redeclare attribute model
     *
     * @return string
     */
    protected function _getDefaultAttributeModel()
    {
        return 'magemenu/resource_eav_attribute';
    }

    /**
     * Returns default Store ID
     *
     * @return int
     */
    public function getDefaultStoreId()
    {
        return Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
    }

    /**
     * Check whether the attribute is Applicable to the object
     *
     * @param Varien_Object $object
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @return boolean
     */
    protected function _isApplicableAttribute ($object, $attribute)
    {
        $applyTo = (array) $attribute->getApplyTo();
        return count($applyTo) == 0 || in_array($object->getTypeId(), $applyTo);
    }

    /**
     * Check whether attribute instance (attribute, backend, frontend or source) has method and applicable
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract|Mage_Eav_Model_Entity_Attribute_Backend_Abstract|Mage_Eav_Model_Entity_Attribute_Frontend_Abstract|Mage_Eav_Model_Entity_Attribute_Source_Abstract $instance
     * @param string $method
     * @param array $args array of arguments
     * @return boolean
     */
    protected function _isCallableAttributeInstance($instance, $method, $args)
    {
        if ($instance instanceof Mage_Eav_Model_Entity_Attribute_Backend_Abstract
            && ($method == 'beforeSave' || $method = 'afterSave')
        ) {
            $attributeCode = $instance->getAttribute()->getAttributeCode();
            if (isset($args[0]) && $args[0] instanceof Varien_Object && $args[0]->getData($attributeCode) === false) {
                return false;
            }
        }

        return parent::_isCallableAttributeInstance($instance, $method, $args);
    }



    /**
     * Retrieve select object for loading entity attributes values
     * Join attribute store value
     *
     * @param Varien_Object $object
     * @param string $table
     * @return Varien_Db_Select
     */
    protected function _getLoadAttributesSelect($object, $table)
    {
        /**
         * This condition is applicable for all cases when we was work in not single
         * store mode, customize some value per specific store view and than back
         * to single store mode. We should load correct values
         * Added to support > 1.4.0
         */
    	if(version_compare(Mage::getVersion(), '1.4.0.0', '>')) {
	        if (Mage::app()->isSingleStoreMode()) {
	            $storeId = Mage::app()->getStore(true)->getId();
	        }
	        else {
	            $storeId = $object->getStoreId();
	        }
	
	        $setId  = $object->getAttributeSetId();
	        $storeIds = array($this->getDefaultStoreId());
	        if ($storeId != $this->getDefaultStoreId()) {
	            $storeIds[] = $storeId;
	        }
	        $select = $this->_getReadAdapter()->select()
	            ->from(array('attr_table' => $table))
	            ->where('attr_table.'.$this->getEntityIdField().'=?', $object->getId())
	            ->where('attr_table.store_id IN (?)', $storeIds);
	        if ($setId) {
	            $select->join(
	                array('set_table' => $this->getTable('eav/entity_attribute')),
	                'attr_table.attribute_id=set_table.attribute_id AND set_table.attribute_set_id=' . intval($setId),
	                array()
	            );
	        }
	        return $select;
    	}
    	
        /**
         * This condition is applicable for all cases when we was work in not single
         * store mode, customize some value per specific store view and than back
         * to single store mode. We should load correct values
         */    	
        if (Mage::app()->isSingleStoreMode()) {
            $storeId = Mage::app()->getStore(true)->getId();
        } else {
            $storeId = $object->getStoreId();
        }

        $select = $this->_read->select()
            ->from(array('default' => $table));
        if ($setId = $object->getAttributeSetId()) {
            $select->join(
                array('set_table' => $this->getTable('eav/entity_attribute')),
                'default.attribute_id=set_table.attribute_id AND '
                    . 'set_table.attribute_set_id=' . intval($setId),
                array()
            );
        }

        $joinCondition = 'main.attribute_id=default.attribute_id AND '
            . $this->_read->quoteInto('main.store_id=? AND ', intval($storeId))
            . $this->_read->quoteInto('main.'.$this->getEntityIdField() . '=?', $object->getId());

        $select->joinLeft(
            array('main' => $table),
            $joinCondition,
            array(
                'store_value_id' => 'value_id',
                'store_value'    => 'value'
            ))
            ->where('default.'.$this->getEntityIdField() . '=?', $object->getId())
            ->where('default.store_id=?', $this->getDefaultStoreId());

        return $select;
    }
    

    /**
     * Initialize attribute value for object
     *
     * @param   Varien_Object $object
     * @param   array $valueRow
     * @return  Mage_Eav_Model_Entity_Abstract
     */
    protected function _setAttribteValue($object, $valueRow)
    {
    	if(version_compare(Mage::getVersion(), '1.4.0.0', '>')) {
	        $attribute = $this->getAttribute($valueRow['attribute_id']);
	        if ($attribute) {
	            $attributeCode = $attribute->getAttributeCode();
	            $isDefaultStore = $valueRow['store_id'] == $this->getDefaultStoreId();
	            if (isset($this->_attributes[$valueRow['attribute_id']])) {
	                if ($isDefaultStore) {
	                    $object->setAttributeDefaultValue($attributeCode, $valueRow['value']);
	                }
	                else {
	                    $object->setAttributeDefaultValue(
	                        $attributeCode,
	                        $this->_attributes[$valueRow['attribute_id']]['value']
	                    );
	                }
	            }
	            else {
	                $this->_attributes[$valueRow['attribute_id']] = $valueRow;
	            }
	
	            $value   = $valueRow['value'];
	            $valueId = $valueRow['value_id'];
	
	            $object->setData($attributeCode, $value);
	            if (!$isDefaultStore) {	            	
	                $object->setExistsStoreValueFlag($attributeCode);
	            }
	            $attribute->getBackend()->setValueId($valueId);
	        }
	        return $this;
    	}
    	#print_r($valueRow);

    	
	        parent::_setAttribteValue($object, $valueRow);
	        $attribute = $this->getAttribute($valueRow['attribute_id']);
	        if ($attribute) {
	            $attributeCode = $attribute->getAttributeCode();
	            if (isset($valueRow['store_value'])) {
	                $object->setAttributeDefaultValue($attributeCode, $valueRow['value']);
	                $object->setData($attributeCode, $valueRow['store_value']);
	                $attribute->getBackend()->setValueId($valueRow['store_value_id']);
	            }
	            
	            if(isset($valueRow['store_id']) && $valueRow['store_id'] != $this->getDefaultStoreId()) {
	            	#$object->setExistsStoreValueFlag($attributeCode);
	            }
	            #$object->setExistsStoreValueFlag($attributeCode);
	        }
	        return $this;
    }

    protected function _setAttributeValue($object, $valueRow)
    {
    	return $this->_setAttribteValue($object, $valueRow);
    }
    
    /**
     * Insert entity attribute value
     *
     * Insert attribute value we do only for default store
     *
     * @param   Varien_Object $object
     * @param   Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param   mixed $value
     * @return  Mage_Eav_Model_Entity_Abstract
     */
    protected function _insertAttribute($object, $attribute, $value)
    {
        $entityIdField = $attribute->getBackend()->getEntityIdField();
        $row = array(
            $entityIdField  => $object->getId(),
            'entity_type_id'=> $object->getEntityTypeId(),
            'attribute_id'  => $attribute->getId(),
            'value'         => $this->_prepareValueForSave($value, $attribute),
            'store_id'      => $this->getDefaultStoreId()
        );
        $fields = array();
        $values = array();
        foreach ($row as $k => $v) {
            $fields[] = $this->_getWriteAdapter()->quoteIdentifier('?', $k);
            $values[] = $this->_getWriteAdapter()->quoteInto('?', $v);
        }
        $sql = sprintf('INSERT IGNORE INTO %s (%s) VALUES(%s)',
            $this->_getWriteAdapter()->quoteIdentifier($attribute->getBackend()->getTable()),
            join(',', array_keys($row)),
            join(',', $values));
        $this->_getWriteAdapter()->query($sql);
        if (!$lastId = $this->_getWriteAdapter()->lastInsertId()) {
            $select = $this->_getReadAdapter()->select()
                ->from($attribute->getBackend()->getTable(), 'value_id')
                ->where($entityIdField . '=?', $row[$entityIdField])
                ->where('entity_type_id=?', $row['entity_type_id'])
                ->where('attribute_id=?', $row['attribute_id'])
                ->where('store_id=?', $row['store_id']);
            $lastId = $select->query()->fetchColumn();
        }
        if ($object->getStoreId() != $this->getDefaultStoreId()) {
            $this->_updateAttribute($object, $attribute, $lastId, $value);
        }
        return $this;
    }

    /**
     * Update entity attribute value
     *
     * @param   Varien_Object $object
     * @param   Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param   mixed $valueId
     * @param   mixed $value
     * @return  Mage_Eav_Model_Entity_Abstract
     */
    protected function _updateAttribute($object, $attribute, $valueId, $value)
    {
        /**
         * If we work in single store mode all values should be saved just
         * for default store id
         * In this case we clear all not default values
         */
        if (Mage::app()->isSingleStoreMode()) {
            $this->_getWriteAdapter()->delete(
                $attribute->getBackend()->getTable(),
                $this->_getWriteAdapter()->quoteInto('attribute_id=?', $attribute->getId()) .
                $this->_getWriteAdapter()->quoteInto(' AND entity_id=?', $object->getId()) .
                $this->_getWriteAdapter()->quoteInto(' AND store_id!=?', Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
            );
        }

        /**
         * Update attribute value for store
         */
        if ($attribute->isScopeStore()) {
            $this->_updateAttributeForStore($object, $attribute, $value, $object->getStoreId());
        }

        /**
         * Update attribute value for website
         */
        elseif ($attribute->isScopeWebsite()) {
            if ($object->getStoreId() == 0) {
                $this->_updateAttributeForStore($object, $attribute, $value, $object->getStoreId());
            } else {
                if (is_array($object->getWebsiteStoreIds())) {
                    foreach ($object->getWebsiteStoreIds() as $storeId) {
                        $this->_updateAttributeForStore($object, $attribute, $value, $storeId);
                    }
                }
            }
        }
        else {
            $this->_getWriteAdapter()->update($attribute->getBackend()->getTable(),
                array('value' => $this->_prepareValueForSave($value, $attribute)),
                'value_id='.(int)$valueId
            );
        }
        return $this;
    }

    /**
     * Update attribute value for specific store
     *
     * @param   Mage_Catalog_Model_Abstract $object
     * @param   object $attribute
     * @param   mixed $value
     * @param   int $storeId
     * @return  Mage_Catalog_Model_Resource_Eav_Mysql4_Abstract
     */
    protected function _updateAttributeForStore($object, $attribute, $value, $storeId)
    {
        $entityIdField = $attribute->getBackend()->getEntityIdField();
        $select = $this->_getWriteAdapter()->select()
            ->from($attribute->getBackend()->getTable(), 'value_id')
            ->where('entity_type_id=?', $object->getEntityTypeId())
            ->where("$entityIdField=?",$object->getId())
            ->where('store_id=?', $storeId)
            ->where('attribute_id=?', $attribute->getId());
        /**
         * When value for store exist
         */
        if ($valueId = $this->_getWriteAdapter()->fetchOne($select)) {
            $this->_getWriteAdapter()->update($attribute->getBackend()->getTable(),
                array('value' => $this->_prepareValueForSave($value, $attribute)),
                'value_id='.$valueId
            );
        }
        else {
            $this->_getWriteAdapter()->insert($attribute->getBackend()->getTable(), array(
                $entityIdField  => $object->getId(),
                'entity_type_id'=> $object->getEntityTypeId(),
                'attribute_id'  => $attribute->getId(),
                'value'         => $this->_prepareValueForSave($value, $attribute),
                'store_id'      => $storeId
            ));
        }

        return $this;
    }

    /**
     * Delete entity attribute values
     *
     * @param   Varien_Object $object
     * @param   string $table
     * @param   array $info
     * @return  Varien_Object
     */
    protected function _deleteAttributes($object, $table, $info)
    {
        $entityIdField      = $this->getEntityIdField();
        $globalValues       = array();
        $websiteAttributes  = array();
        $storeAttributes    = array();

        /**
         * Separate attributes by scope
         */
        foreach ($info as $itemData) {
            $attribute = $this->getAttribute($itemData['attribute_id']);
            if ($attribute->isScopeStore()) {
                $storeAttributes[] = $itemData['attribute_id'];
            }
            elseif ($attribute->isScopeWebsite()) {
                $websiteAttributes[] = $itemData['attribute_id'];
            }
            else {
                $globalValues[] = $itemData['value_id'];
            }
        }

        /**
         * Delete global scope attributes
         */
        if (!empty($globalValues)) {
            $condition = $this->_getWriteAdapter()->quoteInto('value_id IN (?)', $globalValues);
            $this->_getWriteAdapter()->delete($table, $condition);
        }

        $condition = $this->_getWriteAdapter()->quoteInto("$entityIdField=?", $object->getId())
            . $this->_getWriteAdapter()->quoteInto(' AND entity_type_id=?', $object->getEntityTypeId());
        /**
         * Delete website scope attributes
         */
        if (!empty($websiteAttributes)) {
            $storeIds = $object->getWebsiteStoreIds();
            if (!empty($storeIds)) {
                $delCondition = $condition
                    . $this->_getWriteAdapter()->quoteInto(' AND attribute_id IN(?)', $websiteAttributes)
                    . $this->_getWriteAdapter()->quoteInto(' AND store_id IN(?)', $storeIds);
                $this->_getWriteAdapter()->delete($table, $delCondition);
            }
        }

        /**
         * Delete store scope attributes
         */
        if (!empty($storeAttributes)) {
            $delCondition = $condition
                . $this->_getWriteAdapter()->quoteInto(' AND attribute_id IN(?)', $storeAttributes)
                . $this->_getWriteAdapter()->quoteInto(' AND store_id =?', $object->getStoreId());
            $this->_getWriteAdapter()->delete($table, $delCondition);;
        }
        return $this;
    }

    protected function _getOrigObject($object)
    {
        $className  = get_class($object);
        $origObject = new $className();
        $origObject->setData(array());
        $origObject->setStoreId($object->getStoreId());
        $this->load($origObject, $object->getData($this->getEntityIdField()));
        return $origObject;
    }

    /**
     * Collect original data
     *
     * @deprecated after 1.5.1.0
     *
     * @param Varien_Object $object
     * @return array
     */
    protected function _collectOrigData($object)
    {
    	return;
        $this->loadAllAttributes($object);

        if ($this->getUseDataSharing()) {
            $storeId = $object->getStoreId();
        } else {
            $storeId = $this->getStoreId();
        }

        $allStores = Mage::getConfig()->getStoresConfigByPath('system/store/id', array(), 'code');
        $data = array();

        foreach ($this->getAttributesByTable() as $table=>$attributes) {
            $entityIdField = current($attributes)->getBackend()->getEntityIdField();

            $select = $this->_read->select()
                ->from($table)
                ->where($this->getEntityIdField()."=?", $object->getId());

            $where = $this->_read->quoteInto("store_id=?", $storeId);

            $globalAttributeIds = array();
            foreach ($attributes as $attrCode=>$attr) {
                if ($attr->getIsGlobal()) {
                    $globalAttributeIds[] = $attr->getId();
                }
            }
            if (!empty($globalAttributeIds)) {
                $where .= ' or '.$this->_read->quoteInto('attribute_id in (?)', $globalAttributeIds);
            }
            $select->where($where);

            $values = $this->_read->fetchAll($select);

            if (empty($values)) {
                continue;
            }
            foreach ($values as $row) {
                $data[$this->getAttribute($row['attribute_id'])->getName()][$row['store_id']] = $row;
            }
            foreach ($attributes as $attrCode=>$attr) {

            }
        }

        return $data;
    }

}
