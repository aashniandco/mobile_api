/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * @api
 */
define([
    'jquery',
    'underscore',
    'mage/template',
    'mage/translate',
    'priceUtils',
    'priceBox',
    'jquery-ui-modules/widget',
    'jquery/jquery.parsequery'
], function ($, _, mageTemplate, $t, priceUtils) {
    'use strict';

    $.widget('mage.configurable', {
        options: {
            superSelector: '.super-attribute-select',
            selectSimpleProduct: '[name="selected_configurable_option"]',
            priceHolderSelector: '.price-box',
            spConfig: {},
            state: {},
            priceFormat: {},
            optionTemplate: '<%- data.label %>' +
            '<% if (typeof data.finalPrice.value !== "undefined") { %>' +
            ' <%- data.finalPrice.formatted %>' +
            '<% } %>',
            mediaGallerySelector: '[data-gallery-role=gallery-placeholder]',
            mediaGalleryInitial: null,
            slyOldPriceSelector: '.sly-old-price',
            normalPriceLabelSelector: '.normal-price .price-label',

            /**
             * Defines the mechanism of how images of a gallery should be
             * updated when user switches between configurations of a product.
             *
             * As for now value of this option can be either 'replace' or 'prepend'.
             *
             * @type {String}
             */
            gallerySwitchStrategy: 'replace',
            tierPriceTemplateSelector: '#tier-prices-template',
            tierPriceBlockSelector: '[data-role="tier-price-block"]',
            tierPriceTemplate: ''
        },
          
        /**
         * Creates widget
         * @private
         */
        _create: function () {
            // Initial setting of various option values
            this._initializeOptions();

            // Override defaults with URL query parameters and/or inputs values
            this._overrideDefaults();

            // Change events to check select reloads
            this._setupChangeEvents();

            // Fill state
            this._fillState();

            // Setup child and prev/next settings
            this._setChildSettings();

            // Setup/configure values to inputs
            this._configureForValues();

            $(this.element).trigger('configurable.initialized');
        },

        /**
         * Initialize tax configuration, initial settings, and options values.
         * @private
         */
        _initializeOptions: function () {
            var options = this.options,
                gallery = $(options.mediaGallerySelector),
                priceBoxOptions = $(this.options.priceHolderSelector).priceBox('option').priceConfig || null;

            if (priceBoxOptions && priceBoxOptions.optionTemplate) {
                options.optionTemplate = priceBoxOptions.optionTemplate;
            }

            if (priceBoxOptions && priceBoxOptions.priceFormat) {
                options.priceFormat = priceBoxOptions.priceFormat;
            }
            options.optionTemplate = mageTemplate(options.optionTemplate);
            options.tierPriceTemplate = $(this.options.tierPriceTemplateSelector).html();

            options.settings = options.spConfig.containerId ?
                $(options.spConfig.containerId).find(options.superSelector) :
                $(options.superSelector);

            options.values = options.spConfig.defaultValues || {};
            options.parentImage = $('[data-role=base-image-container] img').attr('src');

            this.inputSimpleProduct = this.element.find(options.selectSimpleProduct);

            gallery.data('gallery') ?
                this._onGalleryLoaded(gallery) :
                gallery.on('gallery:loaded', this._onGalleryLoaded.bind(this, gallery));

        },

        /**
         * Override default options values settings with either URL query parameters or
         * initialized inputs values.
         * @private
         */
        _overrideDefaults: function () {
            var hashIndex = window.location.href.indexOf('#');

            if (hashIndex !== -1) {
                this._parseQueryParams(window.location.href.substr(hashIndex + 1));
            }

            if (this.options.spConfig.inputsInitialized) {
                this._setValuesByAttribute();
            }

            this._setInitialOptionsLabels();
        },

        /**
         * Parse query parameters from a query string and set options values based on the
         * key value pairs of the parameters.
         * @param {*} queryString - URL query string containing query parameters.
         * @private
         */
        _parseQueryParams: function (queryString) {
            var queryParams = $.parseQuery({
                query: queryString
            });

            $.each(queryParams, $.proxy(function (key, value) {
                this.options.values[key] = value;
            }, this));
        },

        /**
         * Override default options values with values based on each element's attribute
         * identifier.
         * @private
         */
        _setValuesByAttribute: function () {
            this.options.values = {};
            $.each(this.options.settings, $.proxy(function (index, element) {
                var attributeId;

                if (element.value) {
                    attributeId = element.id.replace(/[a-z]*/, '');
                    this.options.values[attributeId] = element.value;
                }
            }, this));
        },

        /**
         * Set additional field with initial label to be used when switching between options with different prices.
         * @private
         */
        _setInitialOptionsLabels: function () {
            $.each(this.options.spConfig.attributes, $.proxy(function (index, element) {
                $.each(element.options, $.proxy(function (optIndex, optElement) {
                    this.options.spConfig.attributes[index].options[optIndex].initialLabel = optElement.label;
                }, this));
            }, this));
        },

        /**
         * Set up .on('change') events for each option element to configure the option.
         * @private
         */
        _setupChangeEvents: function () {
            $.each(this.options.settings, $.proxy(function (index, element) {
                $(element).on('change', this, this._configure);
            }, this));
        },

        /**
         * Iterate through the option settings and set each option's element configuration,
         * attribute identifier. Set the state based on the attribute identifier.
         * @private
         */
        _fillState: function () {
            $.each(this.options.settings, $.proxy(function (index, element) {
                var attributeId = element.id.replace(/[a-z]*/, '');

                if (attributeId && this.options.spConfig.attributes[attributeId]) {
                    element.config = this.options.spConfig.attributes[attributeId];
                    element.attributeId = attributeId;
                    this.options.state[attributeId] = false;
                }
            }, this));
        },

        /**
         * Set each option's child settings, and next/prev option setting. Fill (initialize)
         * an option's list of selections as needed or disable an option's setting.
         * @private
         */
        _setChildSettings: function () {
            var childSettings = [],
                settings = this.options.settings,
                index = settings.length,
                option;

            while (index--) {
                option = settings[index];

                if (index) {
                    option.disabled = true;
                } else {
                    this._fillSelect(option);

                    // custom function to set buttons for size selection
                    this._setSizeSelectButtons(option);
                }

                _.extend(option, {
                    childSettings: childSettings.slice(),
                    prevSetting: settings[index - 1],
                    nextSetting: settings[index + 1]
                });

                childSettings.push(option);
            }
        },

        /**
         * Setup for all configurable option settings. Set the value of the option and configure
         * the option, which sets its state, and initializes the option's choices, etc.
         * @private
         */
        _configureForValues: function () {
            if (this.options.values) {
                this.options.settings.each($.proxy(function (index, element) {
                    var attributeId = element.attributeId;

                    element.value = this.options.values[attributeId] || '';
                    this._configureElement(element);
                }, this));
            }
        },

        /**
         * Event handler for configuring an option.
         * @private
         * @param {Object} event - Event triggered to configure an option.
         */
        _configure: function (event) {
            event.data._configureElement(this);
        },

        /**
         * Configure an option, initializing it's state and enabling related options, which
         * populates the related option's selection and resets child option selections.
         * @private
         * @param {*} element - The element associated with a configurable option.
         */
        _configureElement: function (element) {
            this.simpleProduct = this._getSimpleProductId(element);

            if (element.value) {
                this.options.state[element.config.id] = element.value;

                if (element.nextSetting) {
                    element.nextSetting.disabled = false;
                    this._fillSelect(element.nextSetting);
                    this._resetChildren(element.nextSetting);
                } else {
                    if (!!document.documentMode) { //eslint-disable-line
                        this.inputSimpleProduct.val(element.options[element.selectedIndex].config.allowedProducts[0]);
                    } else {
                        this.inputSimpleProduct.val(element.selectedOptions[0].config.allowedProducts[0]);
                    }
                }
            } else {
                this._resetChildren(element);
            }

            this._reloadPrice();
            this._displayRegularPriceBlock(this.simpleProduct);
            this._displayTierPriceBlock(this.simpleProduct);
            this._displayNormalPriceLabel();
            this._changeProductImage();
        },

        /**
         * Change displayed product image according to chosen options of configurable product
         *
         * @private
         */
        _changeProductImage: function () {
            var images,
                initialImages = this.options.mediaGalleryInitial,
                galleryObject = $(this.options.mediaGallerySelector).data('gallery');

            if (!galleryObject) {
                return;
            }

            images = this.options.spConfig.images[this.simpleProduct];

            if (images) {
                images = this._sortImages(images);

                if (this.options.gallerySwitchStrategy === 'prepend') {
                    images = images.concat(initialImages);
                }

                images = $.extend(true, [], images);
                images = this._setImageIndex(images);

                galleryObject.updateData(images);

                $(this.options.mediaGallerySelector).AddFotoramaVideoEvents({
                    selectedOption: this.simpleProduct,
                    dataMergeStrategy: this.options.gallerySwitchStrategy
                });
            } else {
                galleryObject.updateData(initialImages);
                $(this.options.mediaGallerySelector).AddFotoramaVideoEvents();
            }

        },

        /**
         * Sorting images array
         *
         * @private
         */
        _sortImages: function (images) {
            return _.sortBy(images, function (image) {
                return image.position;
            });
        },

        /**
         * Set correct indexes for image set.
         *
         * @param {Array} images
         * @private
         */
        _setImageIndex: function (images) {
            var length = images.length,
                i;

            for (i = 0; length > i; i++) {
                images[i].i = i + 1;
            }

            return images;
        },

        /**
         * For a given option element, reset all of its selectable options. Clear any selected
         * index, disable the option choice, and reset the option's state if necessary.
         * @private
         * @param {*} element - The element associated with a configurable option.
         */
        _resetChildren: function (element) {
            if (element.childSettings) {
                _.each(element.childSettings, function (set) {
                    set.selectedIndex = 0;
                    set.disabled = true;
                });

                if (element.config) {
                    this.options.state[element.config.id] = false;
                }
            }
        },

        /**
         * Populates an option's selectable choices.
         * @private
         * @param {*} element - Element associated with a configurable option.
         */
        _fillSelect: function (element) {
            var attributeId = element.id.replace(/[a-z]*/, ''),
                options = this._getAttributeOptions(attributeId),
                prevConfig,
                index = 1,
                allowedProducts,
                allowedProductsByOption,
                allowedProductsAll,
                i,
                j,
                finalPrice = parseFloat(this.options.spConfig.prices.finalPrice.amount),
                optionFinalPrice,
                optionPriceDiff,
                optionPrices = this.options.spConfig.optionPrices,
                allowedOptions = [],
                indexKey,
                allowedProductMinPrice,
                allowedProductsAllMinPrice;

            this._clearSelect(element);
            element.options[0] = new Option('', '');
            element.options[0].innerHTML = this.options.spConfig.chooseText;
            prevConfig = false;

            if (element.prevSetting) {
                prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
            }

            if (options) {
                var customIndex = 0; //define custom variable here
                for (indexKey in this.options.spConfig.index) {
                    /* eslint-disable max-depth */
                    if (this.options.spConfig.index.hasOwnProperty(indexKey)) {
                        allowedOptions = allowedOptions.concat(_.values(this.options.spConfig.index[indexKey]));
                    }
                }

                if (prevConfig) {
                    allowedProductsByOption = {};
                    allowedProductsAll = [];

                    for (i = 0; i < options.length; i++) {
                        /* eslint-disable max-depth */
                        for (j = 0; j < options[i].products.length; j++) {
                            // prevConfig.config can be undefined
                            if (prevConfig.config &&
                                prevConfig.config.allowedProducts &&
                                prevConfig.config.allowedProducts.indexOf(options[i].products[j]) > -1) {
                                if (!allowedProductsByOption[i]) {
                                    allowedProductsByOption[i] = [];
                                }
                                allowedProductsByOption[i].push(options[i].products[j]);
                                allowedProductsAll.push(options[i].products[j]);
                            }
                        }
                    }

                    if (typeof allowedProductsAll[0] !== 'undefined' &&
                        typeof optionPrices[allowedProductsAll[0]] !== 'undefined') {
                        allowedProductsAllMinPrice = this._getAllowedProductWithMinPrice(allowedProductsAll);
                        finalPrice = parseFloat(optionPrices[allowedProductsAllMinPrice].finalPrice.amount);
                    }
                }

                for (i = 0; i < options.length; i++) {
                    if (prevConfig && typeof allowedProductsByOption[i] === 'undefined') {
                        continue; //jscs:ignore disallowKeywords
                    }

                    allowedProducts = prevConfig ? allowedProductsByOption[i] : options[i].products.slice(0);
                    optionPriceDiff = 0;

                    if (typeof allowedProducts[0] !== 'undefined' &&
                        typeof optionPrices[allowedProducts[0]] !== 'undefined') {
                        allowedProductMinPrice = this._getAllowedProductWithMinPrice(allowedProducts);
                        optionFinalPrice = parseFloat(optionPrices[allowedProductMinPrice].finalPrice.amount);
                        optionPriceDiff = optionFinalPrice - finalPrice;
                        options[i].label = options[i].initialLabel;

                        //if (optionPriceDiff !== 0) {
                        //    options[i].label += ' ' + priceUtils.formatPrice(
                        //        optionPriceDiff,
                        //        this.options.priceFormat,
                        //        true
                        //    );
                        //}
                    }

                    if (allowedProducts.length > 0 || _.include(allowedOptions, options[i].id)) {
                        options[i].allowedProducts = allowedProducts;
                        element.options[index] = new Option(this._getOptionLabel(options[i]), options[i].id);

                        if (typeof options[i].price !== 'undefined') {
                            element.options[index].setAttribute('price', options[i].price);
                        }

                        if (allowedProducts.length === 0) {
                            element.options[index].disabled = true;
                        }

                        element.options[index].config = options[i];
                        index++;
                        customIndex++;  //incremented
                    }

                    /* eslint-enable max-depth */
                }

                //add custom code here for preselect if single option avilable
                // if(customIndex > 0){
                //     setTimeout(function(){
                //         $('#attribute'+attributeId).val($('#attribute'+attributeId+' option:nth-child(2)').val()).trigger('change');
                //         $("#attribute"+attributeId).prop("disabled", false);
                //     },200);
                // }

            }
        },

        /**
         * Generate the label associated with a configurable option. This includes the option's
         * label or value and the option's price.
         * @private
         * @param {*} option - A single choice among a group of choices for a configurable option.
         * @return {String} The option label with option value and price (e.g. Black +1.99)
         */
        _getOptionLabel: function (option) {
            return option.label;
        },

        /**
         * Removes an option's selections.
         * @private
         * @param {*} element - The element associated with a configurable option.
         */
        _clearSelect: function (element) {
            var i;

            for (i = element.options.length - 1; i >= 0; i--) {
                element.remove(i);
            }
        },

        /**
         * Retrieve the attribute options associated with a specific attribute Id.
         * @private
         * @param {Number} attributeId - The id of the attribute whose configurable options are sought.
         * @return {Object} Object containing the attribute options.
         */
        _getAttributeOptions: function (attributeId) {
            if (this.options.spConfig.attributes[attributeId]) {
                return this.options.spConfig.attributes[attributeId].options;
            }
        },

        /**
         * Reload the price of the configurable product incorporating the prices of all of the
         * configurable product's option selections.
         */
        _reloadPrice: function () {
            $(this.options.priceHolderSelector).trigger('updatePrice', this._getPrices());
        },

        /**
         * Get product various prices
         * @returns {{}}
         * @private
         */
        _getPrices: function () {
            var prices = {},
                elements = _.toArray(this.options.settings),
                allowedProduct;

            _.each(elements, function (element) {
                var selected = element.options[element.selectedIndex],
                    config = selected && selected.config,
                    priceValue = {};

                if (config && config.allowedProducts.length === 1) {
                    priceValue = this._calculatePrice(config);
                } else if (element.value) {
                    allowedProduct = this._getAllowedProductWithMinPrice(config.allowedProducts);
                    priceValue = this._calculatePrice({
                        'allowedProducts': [
                            allowedProduct
                        ]
                    });
                }

                if (!_.isEmpty(priceValue)) {
                    prices.prices = priceValue;
                }
            }, this);

            return prices;
        },

        /**
         * Get product with minimum price from selected options.
         *
         * @param {Array} allowedProducts
         * @returns {String}
         * @private
         */
        _getAllowedProductWithMinPrice: function (allowedProducts) {
            var optionPrices = this.options.spConfig.optionPrices,
                product = {},
                optionMinPrice, optionFinalPrice;

            _.each(allowedProducts, function (allowedProduct) {
                optionFinalPrice = parseFloat(optionPrices[allowedProduct].finalPrice.amount);

                if (_.isEmpty(product) || optionFinalPrice < optionMinPrice) {
                    optionMinPrice = optionFinalPrice;
                    product = allowedProduct;
                }
            }, this);

            return product;
        },

        /**
         * Returns prices for configured products
         *
         * @param {*} config - Products configuration
         * @returns {*}
         * @private
         */
        _calculatePrice: function (config) {
            var displayPrices = $(this.options.priceHolderSelector).priceBox('option').prices,
                newPrices = this.options.spConfig.optionPrices[_.first(config.allowedProducts)];

            _.each(displayPrices, function (price, code) {
                if (newPrices[code]) {
                    displayPrices[code].amount = newPrices[code].amount - displayPrices[code].amount;
                }
            });

            return displayPrices;
        },

        /**
         * Returns Simple product Id
         *  depending on current selected option.
         *
         * @private
         * @param {HTMLElement} element
         * @returns {String|undefined}
         */
        _getSimpleProductId: function (element) {
            // TODO: Rewrite algorithm. It should return ID of
            //        simple product based on selected options.
            var allOptions = element.config.options,
                value = element.value,
                config;

            config = _.filter(allOptions, function (option) {
                return option.id === value;
            });
            config = _.first(config);

            return _.isEmpty(config) ?
                undefined :
                _.first(config.allowedProducts);

        },

        /**
         * Show or hide regular price block
         *
         * @param {*} optionId
         * @private
         */
        _displayRegularPriceBlock: function (optionId) {
            var shouldBeShown = true;

            _.each(this.options.settings, function (element) {
                if (element.value === '') {
                    shouldBeShown = false;
                }
            });

            if (shouldBeShown &&
                this.options.spConfig.optionPrices[optionId].oldPrice.amount !==
                this.options.spConfig.optionPrices[optionId].finalPrice.amount
            ) {
                $(this.options.slyOldPriceSelector).show();
            } else {
                $(this.options.slyOldPriceSelector).hide();
            }

            $(document).trigger('updateMsrpPriceBlock',
                [
                    optionId,
                    this.options.spConfig.optionPrices
                ]
            );
        },

        /**
         * Show or hide normal price label
         *
         * @private
         */
        _displayNormalPriceLabel: function () {
            var shouldBeShown = false;

            _.each(this.options.settings, function (element) {
                if (element.value === '') {
                    shouldBeShown = true;
                }
            });

            if (shouldBeShown) {
                $(this.options.normalPriceLabelSelector).show();
            } else {
                $(this.options.normalPriceLabelSelector).hide();
            }
        },

        /**
         * Callback which fired after gallery gets initialized.
         *
         * @param {HTMLElement} element - DOM element associated with gallery.
         */
        _onGalleryLoaded: function (element) {
            var galleryObject = element.data('gallery');

            this.options.mediaGalleryInitial = galleryObject.returnCurrentImages();
        },

        /**
         * Show or hide tier price block
         *
         * @param {*} optionId
         * @private
         */
        _displayTierPriceBlock: function (optionId) {
            var options, tierPriceHtml;

            if (typeof optionId != 'undefined' &&
                this.options.spConfig.optionPrices[optionId].tierPrices != [] // eslint-disable-line eqeqeq
            ) {
                options = this.options.spConfig.optionPrices[optionId];

                if (this.options.tierPriceTemplate) {
                    tierPriceHtml = mageTemplate(this.options.tierPriceTemplate, {
                        'tierPrices': options.tierPrices,
                        '$t': $t,
                        'currencyFormat': this.options.spConfig.currencyFormat,
                        'priceUtils': priceUtils
                    });
                    $(this.options.tierPriceBlockSelector).html(tierPriceHtml).show();
                }
            } else {
                $(this.options.tierPriceBlockSelector).hide();
            }
        },

        /**
         * Custom function to change the size selection dropdown to buttons
         * 
         */
        _setSizeSelectButtons: function(element){
            var attributeId = element.id.replace(/[a-z]*/, ''),
                options = this._getAttributeOptions(attributeId),
                itemArr = [];
            const sizeArr = {
                "XSmall": "XS",
                "Small": "S",
                "Medium": "M",
                "Large": "L",
                "Custom Made": "CM",
                "Free Size": "FS",
                "Euro Size 36": "EU 36",
                "Euro Size 37": "EU 37",
                "Euro Size 38": "EU 38",
                "Euro Size 39": "EU 39",
                "Euro Size 40": "EU 40",
                "Euro Size 41": "EU 41",
                "XLarge": "XL",
                "2-3 Years": "2-3 Y",
                "3-4 Years": "3-4 Y",
                "1-2 Years": "1-2 Y",
                "0-3 Months": "0-3 M",
                "3-6 Months": "3-6 M",
                "6-12 Months": "6-12 M",
                "4-5 Years": "4-5 Y",
                "5-6 Years": "5-6 Y",
                "Euro Size 42": "EU 42",
                "XXLarge": "XXL",
                "6-7 Years": "6-7 Y",
                "Bangle Size- 2.2\"": "2.2\"",
                "Bangle Size- 2.4\"": "2.4\"",
                "Bangle Size- 2.7\"": "2.7\"",
                "Euro Size 35": "EU 35",
                "7-8 Years": "7-8 Y",
                "1 Month-1 Year": "1M-1Y",
                "8-9 Years": "8-9 Y",
                "9-10 Years": "9-10 Y",
                "Bangle Size- 2.6\"": "2.6\"",
                "Euro Size 43": "EU 43",
                "Euro Size 44": "EU 44",
                "Euro Size 45": "EU 45",
                "Euro Size 46": "EU 46",
                "Bangle Size- 2.8\"": "2.8\"",
                "Bangle Size- 2.3\"": "2.3\"",
                "11-12 Years": "11-12 Y",
                "Euro Size 34": "EU 34",
                "Euro Size 28": "EU 28",
                "Euro Size 29": "EU 29",
                "Euro Size 30": "EU 30",
                "Euro Size 31": "EU 31",
                "Euro Size 32": "EU 32",
                "Euro Size 33": "EU 33",
                "10-11 Years": "10-11 Y",
                "Euro Size 23": "EU 23",
                "Euro Size 24": "EU 24",
                "Euro Size 25": "EU 25",
                "Euro Size 26": "EU 26",
                "Euro Size 27": "EU 27",
                "12-13 Years": "12-13 Y",
                "13-14 Years": "13-14 Y",
                "14-15 Years": "14-15 Y",
                "3XLarge": "3XL",
                "3 Litre": "3 L",
                "5 Litre": "5 L",
                "10 Litre": "10 L",
                "XXSmall": "XXS",
                "Euro Size 47": "EU 47",
                "Euro Size 48": "EU 48",
                "5XLarge": "5XL",
                "4XLarge": "4XL",
                "6XLarge": "6XL",
                "15-16 Years": "15-16 Y",
                "Euro Size 49": "EU 49"
            };
            var container = $('.field.configurable.required .control .super-attribute-select-btn-grp');
            for (var i = 0; i < options.length; i++) {
                var newDiv = document.createElement('div');
                var newBtn = document.createElement('button');
                var tooltip = document.createElement('div');
                var label = this._getOptionLabel(options[i]);
                
                if(options[i].allowedProducts === null || options[i].allowedProducts === undefined){
                    continue;
                }
                var item_id = options[i].allowedProducts[0];
                for(var key in sizeArr){
                    if(sizeArr.hasOwnProperty(key) && key === label){
                        newBtn.textContent = sizeArr[key];
                        break;
                    }
                }
                newBtn.className = 'size-select-btn';
                newDiv.className = 'size-select-outer-div';
                newBtn.setAttribute('type', 'button');
                newBtn.value = options[i].id;
                newDiv.setAttribute('data-item', item_id);
                itemArr.push(item_id);
                newDiv.append(newBtn);
                tooltip.className = 'tooltip pdp-size-tooltip'
                tooltip.textContent = label;
                newDiv.append(tooltip);
                container.append(newDiv);
            }
            $.ajax({
                url: '/pagelayout/pdp/pdpaction',
                type: 'post',
                data: {
                    'items' : itemArr
                },
                success: function(data){
                    const sizeBtnDivs = $('.size-select-outer-div');
                    var data = JSON.parse(data);
                    var rtsItems = data.rts;
                    var special_prices = [];
                    var entity_ids = [];
                    const result = [];

                    if(rtsItems.length > 0){
                        rtsItems.forEach(function(item) {
                            if(item['value'] == 6038){
                                var item_entity_id = item['entity_id'];
                                sizeBtnDivs.each(function() {
                                    if($(this).data('item') == item_entity_id){
                                        var rtsIconDiv = document.createElement('div');
                                        rtsIconDiv.className = 'rts-icon';
                                        var rtsIcon = document.createElement('i');
                                        rtsIcon.className = 'fa fa-truck';
                                        rtsIconDiv.append(rtsIcon);
                                        $(this).append(rtsIconDiv);
                                    }
                                });
                            }
                            var hiddeninput = document.createElement('input');
                            hiddeninput.className = 'simpleProductDeliveryTimes';
                            hiddeninput.setAttribute('type', 'hidden');
                            hiddeninput.setAttribute('data-item', item['entity_id']);
                            hiddeninput.setAttribute('data-option-id', item['value']);
                            hiddeninput.value = item['deliverytimes'];
                            container.append(hiddeninput);

                            result.push({ entityId: item['entity_id'], specialPrice: parseFloat(item['special_price']) });
                        });
                    }
                    
                    let smallestValue = Infinity;
                    let smallestIndex;

                    for (let i = 0; i < result.length; i++) {
                        const value = result[i].specialPrice;
                        if (value !== null && value < smallestValue) {
                            smallestValue = value;
                            smallestIndex = i;
                        }
                    }
                    
                    if (result[smallestIndex] && result[smallestIndex]['entityId'] !== undefined) {
                        $('.size-select-outer-div[data-item="'+result[smallestIndex]['entityId']+'"] .size-select-btn').click();
                    } else {
                        $('.size-select-btn').each(function() {
                            $(this).click();
                            return false;
                        });
                    }

                }
            });
            $(document).ready(function() {
                var divToCut = $('#product-size-chart-wrapper').detach();
                var lastSizeSelectOuterDiv = $('#btn-attribute141').children('.size-select-outer-div').last();
                divToCut.insertAfter(lastSizeSelectOuterDiv);
            });

        }
    });

    return $.mage.configurable;
});
