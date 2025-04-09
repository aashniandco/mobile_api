define([
        "jquery", "Magento_Ui/js/modal/modal", "Magento_Customer/js/customer-data"
    ], function($, modal, customerData){
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            modalClass:'delete-confirm-modal',
            buttons: []
        }
        var popup = modal(options, $('#delete-confirm-modal'));
        $("body").on('click', '#delete-account-btn', function(){
            $("#delete-confirm-modal").modal("openModal");
        });
        $("body").on('click', '#dcm-cancel-btn', function(){
             $("#delete-confirm-modal").modal("closeModal");
        });

        $("body").on('click', "#dcm-confirm-btn", function(){
            $('#mgs-ajax-loading').show();
            var cust_id = $(this).data('value');
            var data = {
                'is_ajax' : 1,
                'customer_id': cust_id ? cust_id : null
            }
            $.ajax({
                url:"/pagelayout/account/deleteaction",
                data: data,
                type: 'POST',
                success: function(response){
                    $("#delete-confirm-modal").modal("closeModal");
                    $('#mgs-ajax-loading').hide();
                    resp = JSON.parse(response);
                    console.log(resp);
                    if(resp.status){
                        setTimeout(function() {
                            window.location.href='/';
                        }, 3000);
                    }
                    var sections = ['cart'];
                    customerData.invalidate(sections);
                    customerData.reload(sections, true);
                },
                complete: function(data){
                    $("#delete-confirm-modal").modal("closeModal");
                    $('#mgs-ajax-loading').hide();
                }
            });
        })
    }
)