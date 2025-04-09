/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    'Magento_Ui/js/grid/columns/column'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'MageWorx_GiftCards/grid/cells/order',
            fieldClass: {
                'data-grid-order-cell': true
            }
        },
        getHref: function (row, pid) {
            return row['order_used'][pid]['href']
        },
        getLabel: function (row, pid) {
            return row['order_used'][pid]['label']
        },
        getOrderUsedData: function (row) {
            if (!row || !row['order_used']) {
                return [];
            }

            return row['order_used'];
        },
        getTarget: function () {
            return '_blank';
        }
    });
});
