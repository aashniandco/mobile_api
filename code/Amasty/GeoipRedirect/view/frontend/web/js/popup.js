define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/url',
    'mage/translate'
], function ($, modal, urlBuilder, $t) {
    'use strict';

    return function (config, element) {
        if (!$.sessionStorage.get('popupShowed')) {
            config.buttons = [
                {
                    text: $t('Accept'),
                    'class': 'action action-primary accept',
                    click: function () {
                        $.ajax({
                            method: "POST",
                            url: urlBuilder.build('geoipredirect/redirect/accept'),
                            async: false
                        }).done(function () {
                            $.sessionStorage.set('popupShowed', '1');
                            location.reload();
                        });
                    }
                }
            ];

            if (config.decline) {
                config.buttons.push({
                    text: $t('Decline'),
                    'class': 'action action-primary',
                    click: function () {
                        $.ajax({
                            method: "POST",
                            url: urlBuilder.build('geoipredirect/redirect/decline'),
                            async: false
                        }).done(function () {
                            $.sessionStorage.set('popupShowed', '1');
                            popup.closeModal();
                        });
                    }
                });
            }
            config.clickableOverlay = false;
            config.modalClass = "amredirect-modal";
            config.focus = ".accept";
            var popup = modal(config, element);

            $('.amredirect-modal').find('.action-close').remove();
            popup.openModal();
            $('.amredirect-modal').css('z-index', 100001).attr('tabindex', '1');
            $('.modals-overlay').css('z-index', 100000);
        }

        return false;
    };
});
