/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'mage/storage',
    'mage/translate',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Customer/js/model/customer',
    'MageWorx_GiftCards/js/model/gift-card-info',
    'MageWorx_GiftCards/js/model/payment/gift-card-messages'
], function (
    $,
    ko,
    Component,
    storage,
    $t,
    getPaymentInformationAction,
    quote,
    urlBuilder,
    errorProcessor,
    totals,
    fullScreenLoader,
    customer,
    giftCardInfo,
    messageContainer
) {
    'use strict';

    var tmpFile = 'MageWorx_GiftCards/payment/mageworx-giftcards';
    var items = quote.getItems();
    for (var i = 0; i < items.length; i++) {
        if(items[i].name == 'Gift Card'){
            tmpFile = '';
        }
    }

    return Component.extend({
        defaults: {
            template: tmpFile,
            giftCartCodeFieldValue: ''
        },

        isLoading: ko.observable(false),
        giftCardInfo: giftCardInfo,

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe('giftCartCodeFieldValue');

            return this;
        },

        /**
         * Activate Gift Card
         */
        activateGiftCard: function () {

            giftCardInfo.isChecked(false);
            giftCardInfo.isValid(false);

            if (this.validate()) {
                var self = this,
                    url,
                    data = {};

                if (!customer.isLoggedIn()) {
                    url = urlBuilder.createUrl(
                        '/carts/guest-carts/:cartId/mw-giftcards/:giftCardCode',
                        {
                            cartId: quote.getQuoteId(),
                            giftCardCode: self.giftCartCodeFieldValue()
                        }
                    );
                } else {
                    url = urlBuilder.createUrl(
                        '/carts/mine/mw-giftcards/:giftCardCode',
                        {
                            giftCardCode: self.giftCartCodeFieldValue()
                        }
                    );
                    data.cartId = quote.getQuoteId()
                }

                messageContainer.clear();
                fullScreenLoader.startLoader();

                storage.put(
                    url,
                    JSON.stringify(data)
                ).done(
                    function (response) {
                        if (response) {
                            var deferred = $.Deferred();

                            totals.isLoading(true);
                            getPaymentInformationAction(deferred, messageContainer);
                            $.when(deferred).done(
                                function () {
                                    totals.isLoading(false);
                                }
                            );
                            messageContainer.addSuccessMessage({
                                'message': $t('Gift Card') + ' "' + self.giftCartCodeFieldValue() + '" ' + $t('was applied.')
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
        },

        /**
         * Check Gift Card Information
         */
        checkGiftCardInfo: function () {

            if (this.validate()) {

                var self = this;
                var form = '#mageworx-giftcards-form';
                var data = $(form).serialize();

                messageContainer.clear();
                this.isLoading(true);

                storage.get(
                    'mageworx_giftcards/cart/ajaxGiftCardInfo' + '?' + data
                ).done(
                    function (response) {

                        if (response.success) {
                            giftCardInfo.isChecked(true);
                            giftCardInfo.status(response.status);
                            giftCardInfo.balance(response.balance);
                            giftCardInfo.validTill(response.validTill);
                            giftCardInfo.isValid(true);
                        } else {
                            giftCardInfo.isValid(false);
                            messageContainer.addErrorMessage({
                                'message': response.message
                            });
                        }
                    }
                ).fail(
                    function (response) {
                        giftCardInfo.isValid(false);
                        errorProcessor.process(response, messageContainer);
                    }
                ).always(
                    function () {
                        self.isLoading(false);
                    }
                )
            }
        },

        /**
         * @return {jQuery}
         */
        validate: function () {
            var form = '#mageworx-giftcards-form';

            return $(form).validation() && $(form).validation('isValid');
        }
    });
});