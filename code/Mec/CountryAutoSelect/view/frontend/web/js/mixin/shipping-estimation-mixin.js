define([
    'jquery',
    'Magento_Ui/js/form/form',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/model/cart/estimate-service',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Customer/js/model/customer',
    'mage/validation'
], function (
    $,
    Component,
    selectShippingAddress,
    addressConverter,
    estimateService,
    checkoutData,
    shippingRatesValidator,
    registry,
    quote,
    checkoutDataResolver,
    shippingService,
    customer
) {
    'use strict';

    return function (target) {
        return target.extend({
            initialize: function () {
                this._super();
                if (customer.isLoggedIn()) {
                    return;
                }

                registry.async('checkoutProvider')(function (checkoutProvider) {
                    var address, estimatedAddress;

                    shippingService.isLoading(false);

                    checkoutDataResolver.resolveEstimationAddress();
                    address = quote.isVirtual() ? quote.billingAddress() : quote.shippingAddress();

                    if (!address && quote.isVirtual()) {
                        address = addressConverter.formAddressDataToQuoteAddress(
                            checkoutData.getSelectedBillingAddress()
                        );
                    }
                    var selectedCountry = '';
                    $.each(checkoutProvider.dictionaries.country_id, function(index, item) {
                        if (item.is_default != undefined) {
                            selectedCountry = item.value;
                        }
                    });
                    if (address) {
                        estimatedAddress = address.isEditable() ?
                            addressConverter.quoteAddressToFormAddressData(address) :
                        {
                            // only the following fields must be used by estimation form data provider
                            'country_id': address.countryId,
                            region: address.region,
                            'region_id': address.regionId,
                            postcode: address.postcode
                        };

                        if (estimatedAddress.firstname == undefined && selectedCountry) {
                            estimatedAddress.country_id = selectedCountry;
                        }

                        checkoutProvider.set(
                            'shippingAddress',
                            $.extend({}, checkoutProvider.get('shippingAddress'), estimatedAddress)
                        );
                    }

                    if (!quote.isVirtual()) {
                        checkoutProvider.on('shippingAddress', function (shippingAddressData) {
                            checkoutData.setShippingAddressFromData(shippingAddressData);
                        });
                    } else {
                        checkoutProvider.on('shippingAddress', function (shippingAddressData) {
                            checkoutData.setBillingAddressFromData(shippingAddressData);
                        });
                    }
                });
            }
        });
    }
});