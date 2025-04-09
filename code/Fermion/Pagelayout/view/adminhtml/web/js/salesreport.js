require(["jquery"], function(jQuery) {
    jQuery(document).ready(function() {
        jQuery('.salesreport').on('click', function() {
            var designer_name = jQuery('#designer_name').val();
            var product_type = jQuery("input[name='product_type']:checked").val();
            var startdate = jQuery('#startdate').val();
            var enddate = jQuery('#enddate').val();
            var filter = [];

            jQuery.each(jQuery("input[name='column']:checked"), function() {
                filter.push(jQuery(this).val());
            });

            jQuery(".csv_upload_form").show();
            jQuery('.downloadexportcsv').html('');

            jQuery.ajax({
                type: 'POST',
                url: "/admin_backend/pagelayout/report/GetResult",
                data: { filter: filter, designer_name: designer_name, product_type: product_type, startdate: startdate, enddate: enddate, isAjax: true },
                dataType: "json",
                success: function(response) {
                	console.log(response);
                    var messageElement = jQuery('.downloadexportcsv');

                    if (response.success) {
                        messageElement.html('Query Submitted.')
                            .hide()
                            .fadeIn()
                            .delay(3000)
                            .fadeOut("slow", function() {
                                jQuery(this).html('');
                            });
                    } else {
                        messageElement.html('Error: ' + response.message)
                            .hide()
                            .fadeIn()
                            .delay(3000)
                            .fadeOut("slow", function() {
                                jQuery(this).html('');
                            });
                    }
                },
                error: function(e, exception) {
                    console.log('AJAX error:', exception);
                },
                complete: function() {
                    console.log('AJAX request completed');
                }
            });
        });
    });
});
