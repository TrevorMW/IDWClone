/**
 * @author Amasty Team
 * @copyright Copyright (c) 2014-2015 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

var PreorderStateBundle = Class.create(PreorderState, {
    initialize : function($super) {
        $super();
        this.options = {};
    },

    bindEvents : function () {
        var preorderState = this;

        $('product_addtocart_form').on('change', '.radio, .checkbox, .bundle-option-select', function(event) {
            preorderState.update();
        });
    },

    getPreorderState : function () {
        var isPreorder = false;

        for (var id in this.options) {
            if (this.options[id].getPreorderState()) {
                isPreorder = true;
                break;
            }
        }

        return isPreorder;
    },

    setOptionsData : function(options) {
        for (var id in options) {
            if (options[id].isSingle) {
                var option = new PreorderStateBundleOptionSingle(id);
                option.message = options[id].message;
                option.isPreorder = options[id].isPreorder;
            } else {
                var option = new PreorderStateBundleOptionMultiple(id);
            }

            this.options[id] = option;
        }
    },

    setSelectionsData : function(selections) {
        for (var id in selections) {
            var selection = selections[id];
            var option = this.options[selection.optionId];
            if (!option.isSingle) {
                option.setSelectionParameters(id, selection.isPreorder, selection.message);
            }
        }
    },

    update : function ($super) {
        for (var id in this.options) {
            this.options[id].updateNote();
        }
        $super();
    }
});

var PreorderStateBundleOption = Class.create({
    initialize : function (optionId) {
        this.optionId = optionId;
        this.container = null;
        this.isMessageVisible = false;
    },

    disable : function () {
        this.container.removeChild(this.element);
        this.isMessageVisible = false;
    },

    enable : function () {
        this.element = this.generateElement();
        this.getContainer().appendChild(this.element);
        this.isMessageVisible = true;
    },

    generateElement : function() {
        var el = document.createElement('span');
        el.addClassName('ampreorder_note');
        el.innerHTML = this.getMessage();
        return el;
    },

    getContainer : function () {
        if (!this.container) {
            var input = $('bundle-option-' + this.optionId + '-qty-input');
            if (input) {
                this.container = input.parentNode.parentNode;
            }
        }

        if (!this.container) {
            var input = $$('.bundle-option-' + this.optionId);
            if (input.length) {
                this.container = input[0].parentNode.parentNode;
            }
        }

        if (!this.container) {
            this.container = $$('.product-options')[0];
        }
        return this.container;
    },

    updateNote : function () {
        var state = this.getPreorderState();
        if (typeof state === 'undefined') {
            return;
        }
        if (state) {
            if (this.isMessageVisible) {
                this.disable();
            }
            this.enable();
        } else if (!state && this.isMessageVisible) {
            this.disable();
        }
    }
});

var PreorderStateBundleOptionSingle = Class.create(PreorderStateBundleOption, {
    initialize : function ($super, optionId) {
        $super(optionId);
        this.isSingle = true;
        this.message = null;
        this.isPreorder = null;
    },

    getMessage : function () {
        return this.message;
    },

    getPreorderState : function () {
        return this.isPreorder;
    }
});

var PreorderStateBundleOptionMultiple = Class.create(PreorderStateBundleOption, {
    initialize : function ($super, optionId) {
        $super(optionId);
        this.selectionPreorderMap = {};
        this.selectionMessageMap = {};
    },

    getMessage : function() {
        return this.selectionMessageMap[this.getSelectedId()];
    },

    getPreorderState : function () {
        var selectionId = this.getSelectedId();
        return selectionId && this.selectionPreorderMap[selectionId];
    },

    getSelectedId : function () {
        var selectedId = null;

        var select = $('bundle-option-' + this.optionId);
        if (select) {
            selectedId = parseInt(select.getValue());
        }

        if (selectedId === null) {
            for (var id in this.selectionPreorderMap) {
                if (this.selectionPreorderMap[id]) {
                    var radio = $$('#bundle-option-' + this.optionId + '-' + id + ':checked');
                    if (radio.length) {
                        selectedId = id;
                        break;
                    }
                }
            }
        }

        return selectedId;
    },

    setSelectionParameters : function (selectionId, isPreorder, message) {
        this.selectionPreorderMap[selectionId] = isPreorder;
        this.selectionMessageMap[selectionId] = message;
    }

});
