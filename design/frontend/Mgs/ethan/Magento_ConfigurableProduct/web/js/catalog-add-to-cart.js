/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
require([
    'jquery'
], function ($) {
    'use strict';

    /**
     * Add selected configurable attributes to redirect url
     *
     * @see Magento_Catalog/js/catalog-add-to-cart
     */
    $('body').on('catalogCategoryAddToCartRedirect', function (event, data) {
        $(data.form).find('select[name*="super"]').each(function (index, item) {
            data.redirectParameters.push(item.config.id + '=' + $(item).val());
        });
    });

    $('body').on('click', '.size-select-btn', function() {
        const sizeBtns = $('.size-select-btn');
        const sizedropdown = $('.super-attribute-select');
        var sizeVal = $(this).val();
        sizeBtns.css({
	        'background-color': '#fff',
	        'color': '#000'
	    });
        $(this).css({
	        'background-color': '#000',
	        'color': '#fff'
	    });
        sizedropdown.find('option').each(function() {
        	if(sizeVal === $(this).val()){
        		sizedropdown.val(sizeVal).trigger('change');
        		sizedropdown.prop("disabled", false);
        	}
        });

        var itemId = $(this).closest('.size-select-outer-div').data('item');
        const deliveryDisplay = $(".delivery-time-simple-products");
        $('.simpleProductDeliveryTimes').each(function() {
        	if(itemId === $(this).data('item')){
        		$('.pdp-icon-legend').hide();
        		var deliverValue = $(this).val();
        		deliveryDisplay.html(deliverValue);
        		if($(this).data('option-id') == 6038){
        			$('.pdp-icon-legend').show();
        		}
        	}
        });
    });

    $(document).ready(function() {
    	var $tooltip;
	    $('body').on('mouseenter', '.size-select-btn', function(event) {
	    	$tooltip = $(this).next('.tooltip.pdp-size-tooltip');
		    $tooltip.show();
		}).on('mouseleave', '.size-select-btn', function() {
		    $tooltip.hide();
		});
    })
});
