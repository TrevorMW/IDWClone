jQuery.noConflict();

var sku_generator = {
  sku_box: null,
  original_attr_number: null,
  new_sku:'',
  set_data:function( el, num )
  {
    if( el[0] != undefined )
    {
      this.sku_box              = el;
      this.original_attr_number = num;
    }
  },
  clear_sku:function( completely )
  {
    if( completely )
    {
      this.remove_sku_html();
    }
    else
    {
      this.add_intermediate_message()
    }
  },
  add_intermediate_message: function()
  {
    this.sku_box.html('No SKU available.');
  },
  add_sku:function()
  {
    if( this.new_sku != '' )
    {
      this.sku_box.html( this.new_sku );
    }
  },
  remove_sku_html:function()
  {
    this.sku_box.html('');
  }
}

function addNewConfigurableProductMethods()
{
  if (typeof (Product) !== "undefined" && typeof (Product.Config) !== "undefined")
  {
      /* override default configure element function */
      Product.Config.addMethods({
        configureElement: function(element)
        {
            this.reloadOptionLabels(element);

            if (element.value)
            {
                _disable_cart_button = false;

                for (var k = 0; k < element.config.options.length; k++)
                {
                    if (element.config.options[k].id == element.value && typeof (element.config.options[k].optionSaleable) !== "undefined" && !element.config.options[k].optionSaleable)
                    {
                        _disable_cart_button = true;
                    }
                }

                /* disable the add to cart button when item is not saleable */
                if (_disable_cart_button)
                {
                    jQuery("#product_addtocart_form .button.btn-cart").attr("disabled", "disabled").addClass("disabled");
                }
                else
                {
                    jQuery("#product_addtocart_form .button.btn-cart").removeAttr("disabled").removeClass("disabled");
                }
                /* eof disable the add to cart button when item is not saleable */


                this.state[element.config.id] = element.value;
                if (element.nextSetting)
                {
                  element.nextSetting.disabled = false;
                  this.fillSelect(element.nextSetting);
                  this.resetChildren(element.nextSetting);
                }

            }
            else
            {
              this.resetChildren(element);
            }

            this.reloadPrice();

            /* swatches extra functions */
            window.enableSwatchesOptions(element.config.id);
            window.resetLabels(element.config.id);
            window.switchGallery(element.config.id);
            /*eof swatches extra functions */
        }
      });

      Product.Config.addMethods({
        getOptionLabel: function(option, price)
        {
            var price = parseFloat(price);

            if (this.taxConfig.includeTax)
            {
                var tax = price / (100 + this.taxConfig.defaultTax) * this.taxConfig.defaultTax;
                var excl = price - tax;
                var incl = excl * (1 + (this.taxConfig.currentTax / 100));
            }
            else
            {
                var tax = price * (this.taxConfig.currentTax / 100);
                var excl = price;
                var incl = excl + tax;
            }

            if (this.taxConfig.showIncludeTax || this.taxConfig.showBothPrices)
            {
                price = incl;
            }
            else
            {
                price = excl;
            }

            var str = option.label;

            /* added out of stock label */
            if (typeof option.optionSaleable !== "undefined" && !option.optionSaleable)
                str += window.out_of_stock_string();

            if (price)
            {
                if (this.taxConfig.showBothPrices)
                {
                  str += ' ' + this.formatPrice(excl, true) + ' (' + this.formatPrice(price, true) + ' ' + this.taxConfig.inclTaxTitle + ')';
                }
                else
                {
                  str += ' ' + this.formatPrice(price, true);
                }
            }

            return str;
        }
      });

      Product.Config.addMethods({
        fillSelect: function(element)
        {
            var attributeId = element.id.replace(/[a-z]*/, '');
            var options = this.getAttributeOptions(attributeId);
            this.clearSelect(element);
            element.options[0] = new Option(this.config.chooseText, '');
            var prevConfig = false;

            if (element.prevSetting)
            {
                prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
            }

            //_all_options_not_saleable = true;
            if (options)
            {
                var index = 1;
                for (var i = 0; i < options.length; i++)
                {
                    var allowedProducts = [];
                    if (prevConfig)
                    {
                        for (var j = 0; j < options[i].products.length; j++)
                        {
                            if (prevConfig.config.allowedProducts  && prevConfig.config.allowedProducts.indexOf(options[i].products[j]) > -1)
                            {
                                allowedProducts.push(options[i].products[j]);
                            }
                        }
                    }
                    else
                    {
                        allowedProducts = options[i].products.clone();
                    }

                    //console.debug(allowedProducts);
                    /*To add an out of stock label*/
                    _option_saleable = false;
                    if (allowedProducts.size() > 0 && typeof (this.config.saleableProducts) !== "undefined")
                    {
                        for (var k = 0; k < allowedProducts.length; k++)
                        {
                            if (this.config.saleableProducts[allowedProducts[k]] === true)
                            {
                                _option_saleable = true;
                                break;
                            }
                        }
                    }
                    options[i].optionSaleable = _option_saleable;

                    //if(_option_saleable) _all_options_not_saleable = false;
                    /*eof To add an out of stock label*/


                    if (allowedProducts.size() > 0)
                    {
                        options[i].allowedProducts = allowedProducts;
                        element.options[index] = new Option(this.getOptionLabel(options[i], options[i].price), options[i].id);

                        if (typeof options[i].price !== 'undefined')
                        {
                            element.options[index].setAttribute('price', options[i].price);
                        }

                        if( !options[i].optionSaleable)
                        {
                                element.options[index].setAttribute('disabled', 'disabled');
                        }
                        element.options[index].config = options[i];
                        index++;
                    }
                }
            }
        }
      });

      Product.Config.addMethods({
        reloadOptionLabels: function(element)
        {
            var selectedPrice;
            if( typeof element.options[element.selectedIndex]!=="undefined" && element.options[element.selectedIndex].config && !this.config.stablePrices)
            {
                selectedPrice = parseFloat(element.options[element.selectedIndex].config.price)
            }
            else
            {
                selectedPrice = 0;
            }

            for(var i=0;i<element.options.length;i++)
            {
                if(element.options[i].config)
                {
                    element.options[i].text = this.getOptionLabel(element.options[i].config, element.options[i].config.price-selectedPrice);
                }
            }
        }
      });



      if( typeof window.mng_Config !== "undefined" )
      {
        ajax_data = {};

        jQuery.each(window.mng_Config.attributes, function(att_id)
        {
            _container = jQuery("ul#swatches-options-" + this.id);

            ajax_data[this.code] = null;

            _container.find("a").on("click", function()
            {
              var selected = _container.closest('.product-swatches-container').find('a.selected');

              if( jQuery(this).hasClass("active") && !jQuery(this).hasClass("selected") )
              {
                jQuery(this).closest("ul").find("a").removeClass("selected");
                jQuery(this).addClass("selected");
                jQuery("dt#label-attribute-" + att_id + " label span").hide();
                jQuery("dt#label-attribute-" + att_id + " span.selected-label").text(" : " + jQuery(this).data("original-title"));

                jQuery("#product-options-wrapper dl dd select#attribute" + att_id).val(jQuery(this).attr("rel"));


                if( window._configureElement )
                {
                  window.spConfig.configureElement( $('attribute' + att_id ) );
                }

                /*reset value to true*/
                window._configureElement = true;
                _value = jQuery(this).attr("rel");

                ajax_data[window.mng_Config.attributes[att_id].code] = _value


                sku_generator.set_data( jQuery('[data-product-sku]'), jQuery('#product-options-wrapper').find('.configurable-label').length );

                var all_selected = jQuery('#product-options-wrapper').find('a.selected'),
                    options      = window.mng_Config.attributes[att_id].options,
                    data         = {}

                // START SKU GENERATION
                if( sku_generator.original_attr_number >= 2 )
                {
                  jQuery.ajax({
                    url: "/sku_generator.php",
                    type: "POST",
                    data: ajax_data,
                    success: function(data)
                    {
                      var resp = jQuery.parseJSON(data);

                      if( resp.status )
                      {
                        sku_generator.new_sku = resp.new_sku;
                      }

                      sku_generator.add_sku();
                    }
                  });

                }
                else
                {
                  for(var j = 0 ; options.length > j; j++ )
                  {
                    if( options[j].id == _value )
                    {
                      sku_generator.new_sku = options[j].sku;
                    }
                  }
                }

                sku_generator.add_sku();
              }

              return false;
            })



            /*
              .on("mouseenter", function()
              {
                  if (jQuery(this).closest("ul").hasClass("has-swatches"))
                  {
                      jQuery(this).siblings("span.tooltip-container").addClass("on");
                  }
              }).on("mouseleave", function()
              {
                  if (jQuery(this).closest("ul").hasClass("has-swatches"))
                  {
                      jQuery(this).siblings("span.tooltip-container").removeClass("on");
                  }
              });

            */

        });
      }
  }

  /* select click to activate swatches actions */
  jQuery(document).on("change", "#product-options-wrapper dd select.configurable-option-select", function()
  {
      /* find swatch/label to click */
      _swatch = jQuery(".product-swatches-container ul li a#swatches_option_value_" + jQuery("option:selected", this).val());

      if (_swatch.length > 0)
      {
          window._configureElement = false;/* do not trigger the configureElement function again */
          _swatch.click();
      }
  });
};

function swatchesSelectDefaultValueOnHash()
{
    /* get default values from window hash */
    if (window.location.hash)
    {
        var _hash = location.hash.slice(1);

        jQuery.each(_hash.split('&'), function(c, q)
        {
            var i = q.split('='); /*retrieve the att-option pair, the parameters must be arranged as the options*/

            if ( parseInt(i[0]) > 0)
            {
                jQuery("#attribute-" + i[0].toString() + "-container.product-swatches-container #swatches-options-" + i[0].toString() + " a.active#swatches_option_value_" + i[1].toString()).click();
            }
        });
    }
    else if (typeof mng_Config !== "undefined" && typeof mng_Config.defaultValues !== "undefined")
    {
        jQuery.each(mng_Config.defaultValues, function(att_id)
        {
            /* if the select has a selected value and the value is active */
            jQuery("#attribute-" + att_id + "-container.product-swatches-container #swatches-options-" + att_id + " a.active#swatches_option_value_" + mng_Config.defaultValues[att_id]).click();
        });
    }
};

var _content_is_hidden = false;
var _configureElement  = true;

jQuery(document).ready(function()
{
  jQuery(document).on("mouseenter", "ul.attribute-swatches.product-list li a", function()
  {
    var src = '';
    _item = jQuery(this).closest('li.item');

    if( jQuery(this).attr("rel") )
    {
      _item.find('.product-image > img.catalog-product-image').attr("src", jQuery(this).attr("rel") );
    }

    _item.find('.product-image, .product-name a').attr("href", jQuery(this).attr("href") );

    jQuery(this).closest('ul.attribute-swatches li').find('span').addClass("on");

  }
  ).on("mouseleave", "ul.attribute-swatches.product-list li a", function()
  {
      jQuery(this).closest('ul.attribute-swatches li').find('span').removeClass("on");
  });

  jQuery(document).on('click', '.product-image-thumbs a', function(event)
  {
    event.preventDefault();
    jQuery("img#image").attr("src", jQuery(this).attr("href") );
    jQuery("a#main-image-link").attr("href", jQuery(this).attr("rel"));

    if (jQuery.fn.CloudZoom !== undefined)
    {
      jQuery('#main-image-link').CloudZoom();
    }
  });

});

function enableSwatchesOptions(select_id)
{
  _enable_refresh   = false;
  _disable_elements = false;

  if (select_id === "first") {
    _disable_elements = true;
    _enable_refresh = true;
  }

  jQuery("#product-options-wrapper dl dd select.super-attribute-select").each(function()
  {
    if ( _disable_elements )
    {
      jQuery(this).closest("dd").find(".product-swatches-container ul li a").removeClass("selected").removeClass("active");
    }

    _select = jQuery(this);
    if (_enable_refresh)
    {
      if (!jQuery(this).prop("disabled"))
      {
        jQuery("option", this).each(function()
        {
          jQuery("a#swatches_option_value_" + jQuery(this).val()).addClass("active");
        });
      }

      _enable_refresh = false;
    }

    if (jQuery(this).attr("id") == "attribute" + select_id)
    {
        _disable_elements = true;
        _enable_refresh = true;
    }

  });
};

function resetLabels(select_id)
{
    _reset = false;

    jQuery("#product-options-wrapper dl dt").each(function()
    {
      if (_reset == true)
      {
        jQuery("label span", this).show();
        jQuery("span.selected-label", this).text("");
      }

      if (jQuery(this).attr("id") == "label-attribute-" + select_id)
          _reset = true;
    });
};

function switchGallery(select_id)
{
  var item_num = jQuery("#product-options-wrapper dd select.configurable-option-select.switch-gallery#attribute" + select_id).length,
      slider = false;

  if ( item_num > 0 )
  {
    _classes = new Array();

    jQuery("#product-options-wrapper dd select.configurable-option-select.switch-gallery").each(function()
    {
      if (jQuery("option:selected", this).val() != "")
      {
        _classes.push(jQuery(this).attr("id") + "-" + jQuery("option:selected", this).val());
      }
    });

    _class = _classes.join(".");

    if( _class != 0 )
    {
      slick.slick('slickFilter', '.'+_class);
    }
    else
    {
      slick.slick('slickFilter', '.configurable')
    }

    var el = jQuery('[data-product-thumbs] li.slick-active').first().find('a'),
        href = el.attr('href'),
        zoom = el.attr('rel')

    jQuery('#image, [data-main-print-img]').attr('src', href);
    jQuery('#main-image-link').attr('href',zoom);

    if (jQuery.fn.CloudZoom !== undefined)
    {
      jQuery('#main-image-link').CloudZoom();
    }
  }

};
