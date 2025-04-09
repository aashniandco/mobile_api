/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
/*global define*/
define(
    [
        'MageWorx_GiftCards/js/view/cart/totals/giftcardsdiscount'
    ],
    function (Component) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'MageWorx_GiftCards/summary/mageworxgiftcards'
            },
        });
    }
);