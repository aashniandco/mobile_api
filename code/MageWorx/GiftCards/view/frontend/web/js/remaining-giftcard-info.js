define([
    'jquery',
    'underscore',
    'mage/template',
    'mage/translate',
    'priceUtils',
    'priceBox',
    'jquery/ui',
    'jquery/jquery.parsequery',
    'domReady!'
], function ($, _, mageTemplate, $t, priceUtils) {
    'use strict';

    $.widget('mage.remainingGiftCardInfo', {
        options: {},

        _create: function () {
            if (this.options.isPaypalExpress) {
                this.updateRemainingInfo(this);
            } else {
                $(this.options.blockSummaryPlaceholder).on('change', this.updateRemainingInfo.bind(this));
            }
        },

        updateRemainingInfo: function (element) {
            var $this = this;

            $.ajax({
                showLoader: true,
                url: this.options.remainingInfoUpdateUrl,
                type: "POST"
            }).done(function (data) {
                var appliedInfoHtml = mageTemplate($this.options.remainigGiftcardsInfoTemplate, {
                    'appliedGiftcards': data.giftcards,
                    '$t': $t,
                    'currencyFormat': $this.options.currencyFormat,
                    'priceUtils': priceUtils
                });
                $($this.options.remainigGiftcardsInfoSelector).html(appliedInfoHtml).show();
            });
        }
    });

    return $.mage.remainingGiftCardInfo;
});
