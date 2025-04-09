
require(["jquery"], function(jQuery) {
console.log("--gajjjhg--");

jQuery(document).ready(function() {
console.log("--gajjjhg--");
	jQuery(document).on({
	     	click : function(e) {
	     		console.log("---gayatri---");
	     		var btnElement 	 = jQuery(this);
	     		var fileInput  	 = btnElement.prev("#files");
	     		var fileDataType = fileInput.attr("file-format");		     		
	     		if (fileInput.val() == undefined || fileInput.val() == null || fileInput.val() == '') {
	     			alert("please upload file");
	     			return;
	     		}

	     		var csvFile = fileInput[0].files[0];		     		
	     		var fd = new FormData();
	     		console.log("---step1---");
	     		fd.append("file", csvFile);	
	     		fd.append("fileDataType", fileDataType);
	     		console.log("---step2---");	     		
				jQuery.ajax({
			        type:'POST',
			        url : "/scripts/index_productinbulk.php",
			        data : fd,
			        dataTlype : "json",
			        processData : false,
			        contentType : false,
			        beforeSend : function() {
			        	jQuery('body').loader('show'); 
			        },
				    success:function(response) {	
			        	alert("updated");
			        },
				    // complete : function() {
				    // 	console.log("---step2---");	     		
			    	// 	jQuery('body').loader('hide');
				    // }    
		  		});
	     	}
	     }, "#submit");

              
});        
});