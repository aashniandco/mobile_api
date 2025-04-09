/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'uiComponent',
    'mage/storage',
    'mage/translate',
    'priceUtils',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/action/get-payment-information',
    'MageWorx_GiftCards/js/model/payment/gift-card-messages'
], function (
    $,
    Component,
    storage,
    $t,
    priceUtils,
    totals,
    quote,
    customer,
    urlBuilder,
    fullScreenLoader,
    errorProcessor,
    getPaymentInformationAction,
    messageContainer
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'MageWorx_GiftCards/payment/applied-gift-cards-info'
        },

        /**
         * Get information about applied gift cards
         *
         * @returns {Array}.
         */
        getAppliedGiftCards: function () {
            if (totals.getSegment('mageworx_giftcards')) {
                return JSON.parse(totals.getSegment('mageworx_giftcards')['extension_attributes']['front_options']);
            }

            return [];
        },

        /**
         * @return {Object|Boolean}
         */
        isAvailable: function () {
            return totals.getSegment('mageworx_giftcards') && totals.getSegment('mageworx_giftcards').value != 0;
        },

        /**
         * @param {*} price
         * @return {*|String}
         */
        getFormattedPrice: function (price) {
            return priceUtils.formatPrice(price, quote.getPriceFormat());
        },

        /**
         * @param {String} giftCardCode
         * @param {Object} event
         */
        removeGiftCard: function (giftCardCode, event) {
            event.preventDefault();

            if (giftCardCode) {
                var url;

                if (!customer.isLoggedIn()) {
                    url = urlBuilder.createUrl(
                        '/carts/guest-carts/:cartId/mw-giftcards/:giftCardCode',
                        {
                            cartId: quote.getQuoteId(),
                            giftCardCode: giftCardCode
                        }
                    );
                } else {
                    url = urlBuilder.createUrl(
                        '/carts/mine/mw-giftcards/:giftCardCode',
                        {
                            giftCardCode: giftCardCode
                        }
                    );
                }

                messageContainer.clear();
                fullScreenLoader.startLoader();

                storage.delete(
                    url
                ).done(
                    function (response) {
                        if (response) {
                            var deferred = $.Deferred();

                            totals.isLoading(true);
                            getPaymentInformationAction(deferred, messageContainer);
                            $.when(deferred).done(function () {totals.isLoading(false);});
                            messageContainer.addSuccessMessage({
                                'message': $t('Gift Card') + ' "' + giftCardCode + '" ' + $t('was removed.')
                            });
                        }
                    }
                ).fail(
                    function (response) {
                        totals.isLoading(false);
                        errorProcessor.process(response, messageContainer);
                    }
                ).always(
                    function () {
                        fullScreenLoader.stopLoader();
                    }
                )
            }
        }
    });
});