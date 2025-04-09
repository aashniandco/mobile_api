/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

define(['ko'], function (ko) {
    'use strict';

    return {
        status: ko.observable(false),
        balance: ko.observable(false),
        validTill: ko.observable(false),
        isValid: ko.observable(false),
        isChecked: ko.observable(false)
    };
});