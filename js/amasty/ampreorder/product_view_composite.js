/**
 * @author Amasty Team
 * @copyright Copyright (c) 2014 Amasty (http://www.amasty.com)
 * @package Amasty_Preorder
 */

function ampreorder_getDeep(element) {
    while (element && element.firstChild && element.firstChild.firstChild) {
        element = element.firstChild;
    }
    return element;
}

var CartLabelChanger = Class.create({
    selectButton : '#product_addtocart_form .btn-cart',

    isChanged : false,
    originalLabel : null,
    originalTitle : null,

    change : function (label) {
        var button = this.getButton();
        var labelContainer = this.getLabelContainer();
        if (!button || !labelContainer) {
            return;
        }

        if (!this.isChanged) {
            this.originalLabel = labelContainer.innerHTML;
            this.originalTitle = button.getAttribute('title');
            this.isChanged = true;
        }

        labelContainer.innerHTML = label;
        button.setAttribute('title', label);
    },

    getLabelContainer : function () {
        var labelContainer = this.getButton();
        return ampreorder_getDeep(labelContainer);
    },

    getButton : function () {
        var a = $$(this.selectButton);
        return a.length ? a[0] : null;
    },

    restore : function () {
        if (this.isChanged) {
            var button = this.getButton();
            var labelContainer = this.getLabelContainer();
            if (!button || !labelContainer) {
                return
            }

            labelContainer.innerHTML = this.originalLabel;
            button.setAttribute('title', this.originalTitle);
            this.isChanged = false;
        }
    }
});

var PreorderState = Class.create({
    initialize : function () {
        this.isPreorderVisible = false;
        this.cartLabelChanger = new CartLabelChanger();
        this.preorderMap = null;
        this.cartLabel = null;
        this.isStarted = false;
    },

    hidePreorder : function () {
        this.cartLabelChanger.restore();
        this.isPreorderVisible = false;
    },

    showPreorder : function () {
        this.cartLabelChanger.change(this.cartLabel);
        this.isPreorderVisible = true;
    },

    start : function () {
        if (!this.isStarted) {
            this.bindEvents();
            this.update();
            this.isStarted = true;
        }
    },

    update : function () {
        if (this.getPreorderState()) {
            this.showPreorder();
        } else {
            if (this.isPreorderVisible) {
                this.hidePreorder();
            }
        }
    }
});
