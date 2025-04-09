function showOtherAmountBlock()
{
    if (jQuery('#card-amount').val() == 'other_amount') {
        jQuery('#other_amount').show();
    }
}

function cardAmountChangeAction()
{
    var otherAmount = jQuery('#other_amount');
    if (jQuery('#card-amount').val() == 'other_amount') {
        otherAmount.show();
        otherAmount.addClass('required-entry');
    } else {
        otherAmount.hide();
        otherAmount.removeClass('required-entry');
        jQuery('.warnings #min').hide();
        jQuery('.warnings #max').hide();
        jQuery('.warnings #invalid').hide();
    }
}

function otherAmountValidate(from, to)
{
    var otherAmount = jQuery('#other_amount');

    if (isNaN(otherAmount.val())) {
        otherAmount.val('');
        otherAmount.addClass('mage-error');
        jQuery('.warnings #max').hide();
        jQuery('.warnings #min').hide();
        jQuery('.warnings #invalid').show();
    } else if (otherAmount.val() < from && from > 0) {
        otherAmount.val('');
        otherAmount.addClass('mage-error');
        jQuery('.warnings #max').hide();
        jQuery('.warnings #min').show();
        jQuery('.warnings #invalid').hide();
    } else if (otherAmount.val() > to && to > 0) {
        otherAmount.val('');
        otherAmount.addClass('mage-error');
        jQuery('.warnings #min').hide();
        jQuery('.warnings #max').show();
        jQuery('.warnings #invalid').hide();
    } else {
        otherAmount.removeClass('mage-error');
        jQuery('.warnings #min').hide();
        jQuery('.warnings #max').hide();
        jQuery('.warnings #invalid').hide();
    }
}

function setCardAmountValue()
{
    var optionValue = jQuery('.amount-option.selected').data('value');
    var cardAmount  = jQuery('#card-amount');

    cardAmount.val(optionValue);
    cardAmount.trigger("change");
}

function amountOptionClickAction(elem)
{
    jQuery('.amount-option').removeClass("selected");
    jQuery(elem).addClass("selected");
    jQuery(elem).focus();
    setCardAmountValue();
}