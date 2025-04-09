/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'mage/translate',
    'mage/validation',
    'Magento_Catalog/catalog/product/composite/configure'
], function ($, $t) {

    $.validator.addMethod('mageworx-giftcards-min-amount', function (value) {
        return value >= productConfigure.mageworxGiftcardsConfig.minAmount;
    }, $t('Entered amount is too low'));

    $.validator.addMethod('mageworx-giftcards-max-amount', function (value) {
        if (productConfigure.mageworxGiftcardsConfig.maxAmount === 0) {
            return true;
        }

        return value <= productConfigure.mageworxGiftcardsConfig.maxAmount;
    }, $t('Entered amount is too high'));
});