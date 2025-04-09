define([
        "jquery", "Magento_Ui/js/modal/modal"
    ], function($, modal){
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            modalClass:'select-order-innercircle-modal',
            buttons: [{
                text: $.mage.__('Continue'),
                class: 'mymodal1',
                click: function () {
                    modal = this;
                    var selectedOrders = $('input[type="hidden"].selected-orders-data');
                    if(selectedOrders.length == 2){
                        var orderArr = [];
                        selectedOrders.each(function(){
                            orderArr.push($(this).data('order'));
                        })
                        $.ajax({
                            url:"/loyaltypoint/account/orderloyalty",
                            data:{
                                'orders' : orderArr,
                                'orderSelected': true
                            },
                            type: 'POST',
                            success: function(response){
                                $('.select-order-btn-body').remove();
                                resp = JSON.parse(response);
                                if(resp.status){ 
                                    $('.loyalty-point.loyalty-customer-detail td.level').html(resp.level);
                                    $('.loyalty-point.loyalty-customer-detail td.points').html(resp.total_points);
                                }
                                else{
                                    $('.error-msg-loyalty').html(resp.msg);
                                    $('.error-msg-loyalty').show();
                                }
                                modal.closeModal();
                            }
                        });
                    }
                }
            }]
        };

        var popup = modal(options, $('#modal-content-select-order'));
        $("#select-order-modal-button").on('click',function(){
            $("#modal-content-select-order").modal("openModal");
        });

        $('body').on('click', '.list-inline .pagination .item.pages-item-next a, .list-inline .pagination .item.pages-item-previous a', function(){
            var page = $(this).data('value');
            getOrderData(page);
        });

        $('body').on('input', '.list-inline .pagination .item.page-custom input.page-number', function(){
            var page = $(this).val();
            getOrderData(page);
        })

        $('body').on('click', '#modal-content-select-order table td.select-order .order-check', function(){
            order_id = $(this).val();
            increment_id = $(this).data('incrementid');
            var selectedOrders = $('input[type="hidden"].selected-orders-data');
            var selectedOrdersSpan = $('.view-selected-orders');
            if($(this).prop('checked')){
                if(selectedOrders.length == 2){
                    return false;
                }
                var hiddeninput = document.createElement('input');
                hiddeninput.className = 'selected-orders-data';
                hiddeninput.setAttribute('type', 'hidden');
                hiddeninput.setAttribute('data-order', order_id);
                $('.order-data-paginate').append(hiddeninput);

                span = document.createElement('span');
                span.textContent = increment_id;
                span.className = 'view-selected-orders';
                span.setAttribute('data-order', order_id)
                removebtn = document.createElement('a');
                removebtn.textContent = 'x';
                removebtn.setAttribute('href', '#');
                span.append(removebtn);
                $('.selected-orders-list').append(span); 
            }
            else{
                selectedOrders.each(function(){
                    if($(this).data('order') == order_id){
                        $(this).remove();
                    }
                })
                selectedOrdersSpan.each(function(){
                    if($(this).data('order') == order_id){
                        $(this).remove();
                    }
                })
            }
        });

        $('body').on('click', '.selected-orders-list .view-selected-orders a', function(){
            var selectedOrders = $('input[type="hidden"].selected-orders-data'),
            parentSpan = $(this).parent(),
            removeId = parentSpan.data('order');
            parentSpan.remove();
            selectedOrders.each(function(){
                if(removeId == $(this).data('order')){
                    $(this).remove();
                }
            });
            var checkboxSelection = $('input[type="checkbox"].order-check');
            checkboxSelection.each(function(){
                if(removeId == $(this).val()) {
                    $(this).prop('checked',false);
                }
            });
        })

        function getOrderData(page){
            var totalpages = parseInt($('.pagination-total-count').html());
            if(page <= 0 || page > totalpages){
                return false;
            }
            $('#mgs-ajax-loading').show();
            $.ajax({
                url:"/loyaltypoint/account/orderloyalty",
                data:{
                    'page' : page,
                    'paginate': true
                },
                type: 'POST',
                success: function(response){
                    $('#mgs-ajax-loading').hide();
                    resp = JSON.parse(response);
                    if(resp.orderHtml && resp.pagination){
                        $('.order-data-paginate').html(resp.orderHtml).append(resp.pagination);
                    }
                    var selectedOrdersSpan = $('.view-selected-orders');
                    var orderArr = [];
                    if(selectedOrdersSpan){
                        selectedOrdersSpan.each(function(){
                            var hiddeninput = document.createElement('input');
                            hiddeninput.className = 'selected-orders-data';
                            hiddeninput.setAttribute('type', 'hidden');
                            hiddeninput.setAttribute('data-order', $(this).data('order'));
                            $('.order-data-paginate').append(hiddeninput);
                            orderArr.push(parseInt($(this).data('order')));
                        })
                    }
                    var checkboxSelection = $('input[type="checkbox"].order-check');
                    checkboxSelection.each(function(){
                        if(orderArr.includes(parseInt($(this).val()))) {
                            $(this).prop('checked',true);
                        }
                    });
                }
            });
        }
    }
);