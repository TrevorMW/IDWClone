<?php

class Wholesale_Customer_Model_Customer_Extension extends Mage_Customer_Model_Customer
{
  const XML_PATH_WHOLESALE_EMAIL_TEMPLATE = 'customer/create_wholesale/confirm';

  public function sendNewAccountEmail($type = 'registered', $backUrl = '', $storeId = '0')
  {
    $types = array(
      'registered'    => self::XML_PATH_REGISTER_EMAIL_TEMPLATE,
      'confirmed'     => self::XML_PATH_CONFIRMED_EMAIL_TEMPLATE,
      'confirmation'  => self::XML_PATH_CONFIRM_EMAIL_TEMPLATE,
      'new_wholesale' => self::XML_PATH_WHOLESALE_EMAIL_TEMPLATE
    );

    if (!isset($types[$type]))
    {
      Mage::throwException( Mage::helper('customer')->__('Wrong transactional account email type') );
    }

    if(!$storeId)
    {
      $storeId = $this->_getWebsiteStoreId($this->getSendemailStoreId());
    }

    $this->_sendEmailTemplate( $types[$type], parent::XML_PATH_REGISTER_EMAIL_IDENTITY,  array('customer' => $this, 'back_url' => $backUrl), $storeId );

    return $this;
  }






}