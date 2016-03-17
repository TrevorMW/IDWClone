<?php require_once 'app/Mage.php';
Mage::app();

$attr_data = $_POST;
$results   = array( 'status' => false, 'message' => null, 'new_sku' => null );
$details   = array();

if( $attr_data != null && count( $attr_data ) >= 2 )
{
  $products = Mage::getModel('catalog/product')->getCollection();
  $products->addAttributeToSelect('name')->addAttributeToSelect('sku');

  foreach( $attr_data as $k => $id )
  {
    $products->addFieldToFilter( array( array( 'attribute' => $k, 'eq' => (int) $id ) ) );
  }

  foreach ( $products as $product )
  {
    $details[] = array( 'Name' => $product->getData('name'), 'sku' => $product->getData('sku') );
  }

  if( count( $details ) == 1 )
  {
    $results['status']  = true;
    $results['new_sku'] = $details[0]['sku'];
  }
  else
  {
    $results['status']  = false;
    $results['message'] = 'No SKU found.';
    $results['new_sku'] = null;
  }
}
else
{
  $results['status']  = true;
  $results['message'] = '';
  $results['new_sku'] = null;
}

echo json_encode( $results );
