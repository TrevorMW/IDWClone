<?php

class Fooman_Connect_Block_Adminhtml_Xero_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('foomanconnectGrid');
    }

    protected function _getCollectionClass()
    {
        return 'sales/order_collection';
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $this->setCollection($collection);
	return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('xero_export_status', array(
                'header'=> Mage::helper('foomanconnect')->__('Xero Status'),
                'width' => '80px',
                'type'  => 'options',
                'renderer'  => 'foomanconnect/adminhtml_xero_renderer_exportstatus',
                'options' =>$this->getExportStatusOptions(),
                'index' => 'xero_export_status'
        ));

        $this->addColumn('real_order_id', array(
                'header'=> Mage::helper('sales')->__('Order #'),
                'width' => '80px',
                'type'  => 'text',
                'index' => 'increment_id',
        ));

        $this->addColumn('status', array(
                'header' => Mage::helper('sales')->__('Status'),
                'index' => 'status',
                'type'  => 'options',
                'width' => '70px',
                'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                    'header'    => Mage::helper('sales')->__('Purchased from (store)'),
                    'index'     => 'store_id',
                    'type'      => 'store',
                    'store_view'=> true,
                    'display_deleted' => true,
            ));
        }

        $this->addColumn('created_at', array(
                'header' => Mage::helper('sales')->__('Purchased On'),
                'index' => 'created_at',
                'type' => 'datetime',
                'width' => '150px',
        ));

        $this->addColumn('customer_lastname', array(
                'header' => Mage::helper('sales')->__('Customer Lastname'),
                'index' => 'customer_lastname',
        ));

        $this->addColumn('base_grand_total', array(
                'header' => Mage::helper('sales')->__('G.T. (Base)'),
                'index' => 'base_grand_total',
                'type'  => 'currency',
                'currency' => 'base_currency_code',
        ));

        $this->addColumn('grand_total', array(
                'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
                'index' => 'grand_total',
                'type'  => 'currency',
                'currency' => 'order_currency_code',
        ));

         return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();

    }

    public function getExportStatusOptions() {
        $options = array();
        //$options[Fooman_Connect_Model_XeroOauth::CA_XERO_STATUS_NOT_EXPORTED] = Mage::helper('foomanconnect')->__('Not Exported');
        $options[Fooman_Connect_Model_XeroOauth::CA_XERO_STATUS_ORDER_EXPORTED]= Mage::helper('foomanconnect')->__('Exported');
        $options[Fooman_Connect_Model_XeroOauth::CA_XERO_STATUS_ORDER_WONT_EXPORT]= Mage::helper('foomanconnect')->__('Export Not Needed');
        $options[Fooman_Connect_Model_XeroOauth::CA_XERO_STATUS_ORDER_ATTEMPTED_BUT_FAILED] = Mage::helper('foomanconnect')->__('Attempted but failed');
        return $options;
    }


    public function getRowUrl($row) {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getId()));
        }
        return false;
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        $this->getMassactionBlock()->addItem('export_selected', array(
                'label'=> Mage::helper('foomanconnect')->__('Export Selected'),
                'url'  => $this->getUrl('*/*/exportSelected'),
        ));

        $this->getMassactionBlock()->addItem('set_as_exported_selected', array(
                'label'=> Mage::helper('foomanconnect')->__('Never export selected'),
                'url'  => $this->getUrl('*/*/neverExportSelected'),
        ));
    }
/*
    protected function _decodeFilter(&$value)
    {
        $value = $this->helper('adminhtml')->decodeFilter($value);
        echo $value;exit;
    }
 */
}