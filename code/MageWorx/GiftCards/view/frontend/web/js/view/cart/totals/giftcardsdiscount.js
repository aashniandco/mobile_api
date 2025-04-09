/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
/*global define*/
define(
       [
           'Magento_Checkout/js/view/summary/abstract-total',
           'Magento_Checkout/js/model/totals'
       ],
       function(Component, totalsService) {
           "use strict";

           return Component.extend({
            defaults: {
                template: 'MageWorx_GiftCards/cart/totals/giftcardsdiscount'
            },

            isDisplayed: function () {
                return this.getGiftCardsValue() != 0;
            },

            getCardsCode: function () {
                var giftcardsTotal = totalsService.getSegment('mageworx_giftcards');

                if (giftcardsTotal) {
                    return giftcardsTotal.title;
                }

                return '';
            },

            /**
             * @returns {null}
             */
            getCardsLabel: function () {
                return null;
            },

            getGiftCardsValue: function () {
                var giftcardsTotal = totalsService.getSegment('mageworx_giftcards');

                if (giftcardsTotal) {
                    return parseFloat(giftcardsTotal.value);
                }

                return 0;
            },

            getValue: function () {
                return this.getFormattedPrice(this.getGiftCardsValue());
            }
           });
        }
);