/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'Magento_Ui/js/view/messages',
    'MageWorx_GiftCards/js/model/payment/gift-card-messages'
], function (Component, messageContainer) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function (config) {
            return this._super(config, messageContainer);
        }
    });
});