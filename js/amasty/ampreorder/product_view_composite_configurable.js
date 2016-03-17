/**
 * @author Amasty Team
 * @copyright Copyright (c) 2014 Amasty (http://www.amasty.com)
 * @package Amasty_Preorder
 */

var PreorderNoteConfigurable = Class.create({
    initialize : function () {
        this.message = null;
        this.container = null;
        this.isVisible = false;
        this.originalContainerInnerHtml = null;
    },

    disable : function () {
        if (!this.isVisible) {
            return;
        }
        this.getContainer().innerHTML = this.originalContainerInnerHtml;
        this.isVisible = false;
    },

    enable : function () {
        var container = this.getContainer();
        if (!container) {
            return;
        }
        if (!this.isVisible) {
            this.originalContainerInnerHtml = container.innerHTML;
        }
        container.innerHTML = this.generateElement();
        this.isVisible = true;
    },

    generateElement : function () {
        return '<span class="ampreorder_note">' + this.message + '</span>';
    },

    getContainer : function () {
        if (!this.container) {
            this.container = $$('#product_addtocart_form .availability')[0];
        }
        if (!this.container) {
            this.container = $$('.availability')[0];
        }
        return this.container;
    },

    refresh : function (message) {
        this.message = message;
        if (this.isVisible) {
            this.disable();
            this.enable();
        }
    }
});

var PreorderStateConfigurable = Class.create(PreorderState, {
    initialize : function($super) {
        $super();
        this.preorderNote = new PreorderNoteConfigurable();
        this.messageMap = null;
        this.cartLabelMap = null;
    },

    bindEvents : function () {
        var preorderState = this;
        $$('.super-attribute-select').each(function(select) {
            Event.observe(select, 'change', function() { preorderState.update() });
        });
    },

    getPreorderState : function () {
        var simpleProductId = spConfig.getIdOfSelectedProduct();

        if (simpleProductId) {
            this.cartLabel = this.cartLabelMap[simpleProductId];
            this.preorderNote.refresh(this.messageMap[simpleProductId]);
        }

        return simpleProductId && this.preorderMap[simpleProductId];
    },

    hidePreorder : function ($super) {
        this.preorderNote.disable();
        $super();
    },

    showPreorder : function ($super) {
        this.preorderNote.enable();
        $super();
    }
});

Product.Config.prototype.getSelectedOptionsProductsUsingFrequencies = function () {
    var existingProducts = {};

    for(var i=this.settings.length-1;i>=0;i--)
    {
        var selected = this.settings[i].options[this.settings[i].selectedIndex];
        if (selected && selected.config)
        {
            for(var iproducts=0;iproducts<selected.config.products.length;iproducts++)
            {
                var usedAsKey = selected.config.products[iproducts]+"";
                if(existingProducts[usedAsKey]==undefined)
                {
                    existingProducts[usedAsKey]=1;
                }
                else
                {
                    existingProducts[usedAsKey]=existingProducts[usedAsKey]+1;
                }
            }
        }
    }

    return existingProducts;
};

Product.Config.prototype.getIdOfSelectedProduct = function()
{
    var existingProducts = this.getSelectedOptionsProductsUsingFrequencies();

    for (var keyValue in existingProducts)
    {
        for ( var keyValueInner in existingProducts)
        {
            if(Number(existingProducts[keyValueInner])<Number(existingProducts[keyValue]))
            {
                delete existingProducts[keyValueInner];
            }
        }
    }

    var sizeOfExistingProducts = 0;
    var currentSimpleProductId;
    for ( var keyValue in existingProducts)
    {
        currentSimpleProductId = keyValue;
        sizeOfExistingProducts++;
        if (sizeOfExistingProducts > 1) {
            break;
        }
    }

    return sizeOfExistingProducts == 1 ? currentSimpleProductId : null;
};