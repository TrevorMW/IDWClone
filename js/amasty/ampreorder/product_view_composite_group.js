/**
 * @author Amasty Team
 * @copyright Copyright (c) 2014 Amasty (http://www.amasty.com)
 * @package Amasty_Preorder
 */

var PreorderStateGroup = Class.create(PreorderState, {
    bindEvents : function () {
        var preorderState = this;
        $$('.qty').each(function(qty) {
            Event.observe(qty, 'change', function() { preorderState.update() });
        });
    },

    getPreorderState : function () {
        var isPreorder = false;
        var preorderState = this;

        $$('.qty').each(function(qty) {
            var quantity = parseInt(qty.getValue());
            if (quantity > 0) {
                var productId = qty.getAttribute('name').match(/\d+/)[0];
                if (preorderState.preorderMap[productId]) {
                    isPreorder = true;
                }
            }

            return !isPreorder;
        });

        return isPreorder;
    }
});
