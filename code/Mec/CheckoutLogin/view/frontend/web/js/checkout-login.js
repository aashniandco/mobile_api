/**
 * Created by User on 12/18/2020.
 */
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";
     
    function main(config, element) {
        var $element = $(element);
        var AjaxUrl = config.AjaxUrl;
         
        var dataForm = $('#custom_login_checkout');
        dataForm.mage('validation', {});
		
		$('button#submit-button').click( function() { //can be replaced with any event
		var status = dataForm.validation('isValid'); //validates form and returns boolean
		if(status){
		console.log('form is validated...'); //form is valid
		//Do the AJAX call here
		}else{
		console.log('form is not validated...');
		}
		});
         
        /*$(document).on('click','.submit',function() {
             
            if (dataForm.valid()){
            event.preventDefault();
                var param = dataForm.serialize();
                alert(param);
                    $.ajax({
                        showLoader: true,
                        url: AjaxUrl,
                        data: param,
                        type: "POST"
                    }).done(function (data) {
						alert('submitted');
                        $('.note').html(data);
                        $('.note').css('color', 'red');
                        document.getElementById("contact-form").reset();
                        return true;
                    });
                }
            });*/
    };
return main;    
     
});
