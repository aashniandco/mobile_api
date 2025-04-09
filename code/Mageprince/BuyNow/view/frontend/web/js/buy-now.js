define([
    'jquery'
], function ($) {
    "use strict";
    return function (config, element) {
        $(element).click(function () {
            console.log("Buy now button clicked");
            var form = $(config.form),
                baseUrl = form.attr('action'),
                addToCartUrl = 'checkout/cart/add',
                buyNowCartUrl = 'buynow/cart/index',
                buyNowUrl = baseUrl.replace(addToCartUrl, buyNowCartUrl);
            var product_val = $('#product_addtocart_form input[name="product"]').val(),
                selected_configurable_option_val = $('#product_addtocart_form input[name="selected_configurable_option"]').val(),
                related_product_val = $('#product_addtocart_form input[name="related_product"]').val(),
                item_val = $('#product_addtocart_form input[name="item"]').val(),
                super_attribute_val = { 141 : $('#product_addtocart_form select').val()},
                form_key_val = $('#product_addtocart_form input[name="form_key"]').val();
                console.log("Buy now Before Ajax");
                console.log(buyNowUrl);

            var p_type = $('#p_type').val();
            console.log("reload");
            var dataArr = {
                action_url : buyNowUrl.replace(/\\/g, ''),
                product : product_val,
                selected_configurable_option : selected_configurable_option_val,
                related_product : related_product_val,
                item : item_val,
                super_attribute : super_attribute_val,
                form_key : form_key_val,
                qty : 1,
                ajax : 1
            };
            console.log("reload1");
            // parent.location.reload();
             console.log("reload2");
            if(p_type == 'mageworx_giftcards'){
                 console.log("reload3");
                $('#mail-to-email').parent().children('#mail-to-email-error').remove();
                $('.warnings #invalid').css('display', 'none');
                var otherAmount = jQuery('#other_amount').attr('data-value');
                var cardAmount = otherAmount ? 'other_amount' : jQuery('#card-amount').val();
                var mailFrom = jQuery('#mail-from').val();
                var mailTo = jQuery('#mail-to').val();
                var mailtoEmail = jQuery('#mail-to-email').val();
                var mail_message = jQuery('#mail-message').val();
                var devliveryDate = jQuery('#delivery-date').val();
                var imageUrl = jQuery('.owl-item .item .image-item.active').data('src');
                var amountCheck = otherAmount ? otherAmount : cardAmount;
                dataArr.card_amount = cardAmount;
                dataArr.mail_from = mailFrom;
                dataArr.mail_to = mailTo;
                dataArr.mail_to_email = mailtoEmail;
                dataArr.mail_message = mail_message;
                dataArr.mail_delivery_date_user_value = devliveryDate;
                dataArr.card_amount_other = otherAmount;
                dataArr.image_url = imageUrl;
                console.log(dataArr);
                if(amountCheck != '' && amountCheck != undefined){
                    if(mailtoEmail != '' && mailtoEmail != null && mailtoEmail != undefined && validateEmail(mailtoEmail)){
                        $.ajax({
                            url: buyNowUrl,
                            type: 'post',
                            data: dataArr,
                            success: function(response){
                                console.log(response);
                                 console.log("reload4");
                                response = JSON.parse(response);
                                console.log("succuss in ajax--");console.log(response.backUrl)
                                if(response.backUrl){
                                     console.log("reload5");
                                    setCookie('buynow', response.quote_id, 365,'.aashniandco.com','/',true);
                                    location.href=response.backUrl;
                                }
                            },
                            error: function(){console.log("error in ajax");
                                location.href = "checkout/"
                            }
                        })
                    }
                    else{
                        $('#mail-to-email').parent().append('<div for="mail-to-email" generated="true" class="mage-error" id="mail-to-email-error">Enter a valid Email.</div>');
                    }
                }
                else{
                    $('.warnings #invalid').css('display', 'block');
                }
            }
            else{
                $.ajax({
                    url: buyNowUrl,
                    type: 'post',
                    data: dataArr,
                    success: function(response){
                        response = JSON.parse(response);
                        if(response.quote_id != '' && response.quote_id != null && response.quote_id != undefined){
                            setCookie('buynow', response.quote_id, 365,'.aashniandco.com','/',true);
                        }

                        if(response.backUrl){
                            parent.location.href =response.backUrl;
                        }
                    },
                    error: function(){console.log("error in ajax");
                        parent.location.href = "checkout/"
                    }
                })
            }
        });
    }


    function validateEmail(mail){
        var validMailRegex = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
        var validEmail = validMailRegex.test(mail);{
            if(validEmail){
                return true;
            }
            else{
                return false;
            }
        }
    }
    function setCookie(cname, cvalue, exdays, domain, path,secureflag) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires=" + d.toUTCString();
        var cookieString = cname + "=" + cvalue + ";" + expires;
        if (domain) {
            cookieString += ";domain=" + domain;
        }
        if (path) {
            cookieString += ";path=" + path;
        }
        if (secureflag) {
            cookieString += ";secure=" ;
        }
        document.cookie = cookieString;
    }
});
