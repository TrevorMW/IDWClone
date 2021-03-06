<?php

function showAvailability($_product)
{
  if (!$_product->isConfigurable())
        return false;

  $_availability_attribute = Mage::getStoreConfig("attributeswatches/productlist/availability");
  $_availability_configuration = Mage::getResourceModel('attributeswatches/attributes')->hasConfigurableAttributeSimple($_availability_attribute, $_product->getId());
  $_availability_html = "";

  if ( is_array($_availability_configuration) && count($_availability_configuration))
  {
    $_availability_html = '<ul class="attribute-availability">';

    foreach ($_availability_configuration as $_i)
    {
        $_availability_html .= '<li>' . $_i["label"] . '</li>';
    }

    $_availability_html .= '</ul>';
  }
    return $_availability_html;
}


function showSwatches( $_product, $_w, $_h )
{
  // $time_start = microtime(true);
  $_product_image = $_product_url = $_swatches_html ="";

  if ($_product->isConfigurable())
  {
    //return false;
    $_configurable_attributes         = Mage::getStoreConfig("attributeswatches/productlist/attributes");
    $_configurable_attributes_layered = explode(",",$_configurable_attributes);
    $_configurable_attributes         = str_replace(",", "','", $_configurable_attributes);

    /* to switch image and url based on layered navigation values */
    $_product_image = $_product_url = "";

    $_values_to_check = array();
    /* set product image based on selected layered navigation filter */

    if (Mage::getStoreConfig("attributeswatches/layerednavigation/switchimages"))
    {
      $_configurable_attributes_switch = Mage::getStoreConfig("attributeswatches/productlist/attributes");
      $_configurable_attributes_switch  = explode(",",$_configurable_attributes_switch);
      /* get selected value based on attribute */

      $_vals = array_intersect($_configurable_attributes_layered, $_configurable_attributes_switch);

      foreach ($_vals as $_att)
      {
        if(trim(Mage::app()->getRequest()->getParam($_att)))
        $_values_to_check[$_att] = trim(Mage::app()->getRequest()->getParam($_att));
      }
    }

    $_swatches = Mage::getResourceModel('attributeswatches/attributes')->hasConfigurableAttribute($_configurable_attributes, $_product->getId());

    if ($_swatches)
    {
      $_swatches_mode  = Mage::getStoreConfig("attributeswatches/productlist/mode");
      $_image_source   = Mage::getStoreConfig("attributeswatches/productlist/images");
      $_gallery_images = array();
      /* to get the attribute id */
      $_attribute_id   = 0;
      $_attribute_code = "";

      foreach ($_swatches as $_p => $_data)
      {
        $_attribute_id   = $_data["attribute_id"];
        $_attribute_code = $_data["attribute"];
        break;
      }


      if ($_image_source == "gallery")
      {
        $_gallery = $_product->load( $_product->getId() )->getSwatchesGalleryImages($_attribute_id);

        foreach ($_gallery as $_image)
        {
            $_gallery_images[$_image->getAttributeValue()] = $_image->getFile();
        }
      }

      /* loading products to be used for swatches */
      //$_swatches_html = $_attribute_id . $_product->getId();
      $_swatches_html = '<ul class="attribute-swatches product-list">';
      $counter        = 0;

      foreach ($_swatches as $product_id => $_option)
      {
        $counter++;

        if( $counter <= 6 )
        {
          $_swatch = $_thumbnail = "";

          if ($_swatches_mode == "swatches")
          {
            if ($_option["mode"] == 2)
            {
              $_swatch = "background-color:#" . $_option["color"];
            }
            elseif ($_option["mode"] == 1)
            {
              $_swatch = "background-image:url('" . Mage::getBaseUrl('media') . 'attributeswatches/' . $_option["filename"] . "');background-size: 100% auto;background-repeat:no-repeat;";
            }
            elseif ($_option["mode"] == 3)
            {
              $_swatch = "background-image:url('" . Mage::getBaseUrl('media') . 'attributeswatches/' . $_option["filename"] . "');background-repeat:repeat;";
            }
          }
          elseif ($_swatches_mode == "image")
          {
            if ($_image_source == "child")
            {
              $product = Mage::getModel('catalog/product')->load($product_id);
              $_swatch = "background-image:url('" . Mage::helper('catalog/image')->init($product, 'image')->resize(25) . "');";
            }
            elseif ($_image_source == "gallery")
            {
              if (isset($_gallery_images[$_option["value"]]) && trim($_gallery_images[$_option["value"]]))
              {
                $_swatch = "background-image:url('" . Mage::helper('catalog/image')->init($_product, 'thumbnail', $_gallery_images[$_option["value"]])->resize(25) . "');";
              }
            }
          }

          if ($_image_source == "child")
          {
            $product    = Mage::getModel('catalog/product')->load($product_id);
            $_thumbnail = Mage::helper('catalog/image')->init($product, 'image')->resize($_w, $_h);
          }
          elseif ($_image_source == "gallery")
          {
            if( isset( $_gallery_images[$_option["value"]] ) && trim($_gallery_images[$_option["value"]]) )
            {
              $_thumbnail = Mage::helper('catalog/image')->init($_product, 'thumbnail', $_gallery_images[$_option["value"]])->resize($_w, $_h);
            }
          }

          if( count($_values_to_check) )
          {
            foreach($_values_to_check as $_att => $_value)
            {
              if($_attribute_code == $_att  && $_value == $_option["value"])
              {
                $_product_image = (string) $_thumbnail;
                $_product_url   = (string)$_product->getProductUrl() . "#" . $_attribute_id.'='. $_option["value"];
                //$_product->getUrlModel()->getUrl($_product, array("_fragment"=> $_attribute_id.'='. $_option["value"] ));
              }
            }
          }

          $_swatches_html .='<li class="attribute-swatch-' . $counter . '">';
          $_swatches_html .='<a href="'. $_product->getProductUrl() . "#" . $_attribute_id.'='. $_option["value"] . '" class="' . $_attribute_id . "-" . $_option["value"] . '" rel="' . $_thumbnail . '" style="' . $_swatch . '">';
          $_swatches_html .='&nbsp;';
          $_swatches_html .='</a>';
          $_swatches_html .='<span class="tooltip-container"><span class="tooltip"><span>' . $_option["label"] . ' </span></span></span>';
          $_swatches_html .='</li>';
        }
      }

      $_swatches_html.='</ul>';

    }

    /* if no image is associated to the selected layered option, display the default image */
    if (!$_product_image)
    {
        $_product_image = Mage::helper('catalog/image')->init($_product, 'small_image')->resize($_w, $_h);
    }

    if(!$_product_url)
    {
        $_product_url = $_product->getProductUrl();
    }

  }
  else
  {
    $_swatches_html = "";
    $_product_image = (string)Mage::helper('catalog/image')->init($_product, 'small_image')->resize($_w, $_h);
    $_product_url   = $_product->getProductUrl();
  }

  /*to add alternate image - display on image hover*/
  $_alternate_image_source = trim(Mage::getStoreConfig("attributeswatches/productlist/alternate_image_source"));
  $_hover_image =false;

  if( $_alternate_image_source && $_product->getData( $_alternate_image_source ) && $_product->getData( $_alternate_image_source )!=="no_selection" )
  {
    $_hover_image = (string)Mage::helper('catalog/image')->init($_product, $_alternate_image_source)->resize($_w, $_h);
  }

  $_result = array("swatches" => $_swatches_html, "product_image" => $_product_image, "product_url" => $_product_url, "hover_image"=> $_hover_image);

  return $_result;
}
?>
