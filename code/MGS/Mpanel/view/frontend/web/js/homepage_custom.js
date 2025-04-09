$(document).ready(function() {

    $('.toggle-menu').on('click', function(event) {
         event.preventDefault();
        
        var $currentDropdown = $(this).next('.dropdown-menu-plot');

        $currentDropdown.toggleClass('active');
        $('.dropdown-menu-plot').not($currentDropdown).removeClass('active');
    });

    $('.app-footer-wrapper .mobile-menu').click(function(){
      if ($('html').hasClass('nav-open')) {
        $('html').removeClass('nav-open');
        setTimeout(function () {
          $('html').removeClass('nav-before-open');
        }, 300);
      } else {
        $('html').addClass('nav-before-open');
        setTimeout(function () {
          $('html').addClass('nav-open');
        }, 42);
      }
    });

    var userAgent = navigator.userAgent;
    if(userAgent == 'android' || userAgent == 'ios'){
      $('.app-footer-wrapper .overlay-content-wrap.mobile-search').on('click', function(){
        $('.top-header.native-search .search-block-native').fadeIn();
        $('#mobileSearchInpNative').focus();
        $('body').css('overflow-y','hidden');
      });
      $('.top-header .contain .search-block-native .search-form-native .input .close-btn').on('click',function(){
        $('.top-header.native-search .search-block-native').fadeOut();
        $('#mobileSearchInpNative').val('');
        $('body').css('overflow-y','');
      });
    }
    else{
      $('#aashnisticky .bottom-header-content .bottom-header-wrap .menu-content #mobileSearchPop').on('click',function(){
        $('#aashnisticky .bottom-header-content .search-block').fadeIn();
        $('#mobileSearchInp').focus();
        $('body').css('overflow-y','hidden');
      });
      $('.bottom-header-content .search-block .search-forms .input .close-btn').on('click',function(){
        $('#aashnisticky .bottom-header-content .search-block').fadeOut();
        $('#mobileSearchInp').val('');
        $('body').css('overflow-y','');
      });
    }

    $('.close-nav-button').click(function(){
      $('html').removeClass('nav-open');
      setTimeout(function () {
        $('html').removeClass('nav-before-open');
      }, 300);
    });

    $('.nav-toggle, .app-footer-wrapper .mobile-menu').on("click",function() {
        $('.main-page-wrapper').addClass('menu-open');
        $('body').css('overflow','hidden');
    });

    // When close button is clicked, remove 'menu-open' class from '.main-page-wrapper'
    $('.close-nav-button').on("click",function() {
        $('.main-page-wrapper').removeClass('menu-open');
        $('body').css('overflow','auto');
    });



    $('.switcher-toggle').on("click", function() {
        $('.INR-ul-wrap').toggleClass('INR-ul-active');

    });



    $('.currency-selector').on("click", function() {
        $('.currency-dropdown-menu').toggleClass('dropdown-active');
    });




      
    $('.toggle-menu').on('click', function() {
      var $clickedIcon = $(this).find('span');

      // Check if the clicked icon is already 'fa-minus'
      if ($clickedIcon.hasClass('fa-minus')) {
          // If already 'fa-minus', switch back to 'fa-plus'
          $clickedIcon.removeClass('fa-minus').addClass('fa-plus');
      } else {
          // Reset all icons to 'fa-plus'
          $('.toggle-menu span').removeClass('fa-minus').addClass('fa-plus');
          
          // Switch the clicked icon to 'fa-minus'
          $clickedIcon.removeClass('fa-plus').addClass('fa-minus');
      }
    });
// ------------------------search start----------------------
let utm_url = "?";
let utm_url_set = false;
const queryString = window.location.search;
const urlParams = new URLSearchParams(queryString);

if (urlParams.has('utm_source') || urlParams.has('utm_medium') || urlParams.has('utm_campaign') || urlParams.has('utm_content')) {
  const params = [];

  if (urlParams.has('utm_source') && urlParams.get('utm_source') !== "") {
    const utm_source = encodeURIComponent(urlParams.get('utm_source'));
    params.push(`utm_source=${utm_source}`);
  }

  if (urlParams.has('utm_medium') && urlParams.get('utm_medium') !== "") {
    const utm_medium = encodeURIComponent(urlParams.get('utm_medium'));
    params.push(`utm_medium=${utm_medium}`);
  }

  if (urlParams.has('utm_campaign') && urlParams.get('utm_campaign') !== "") {
    const utm_campaign = encodeURIComponent(urlParams.get('utm_campaign'));
    params.push(`utm_campaign=${utm_campaign}`);
  }

  if (urlParams.has('utm_content') && urlParams.get('utm_content') !== "") {
    const utm_content = encodeURIComponent(urlParams.get('utm_content'));
    params.push(`utm_content=${utm_content}`);
  }

  if (params.length > 0) {
    utm_url += params.join("&");
    utm_url_set = true;
  }
}

utm_url = utm_url_set ? utm_url : "";
//console.log(utm_url);

$('#mobileSearchPop').on('click',function(){
  $('.search-block').fadeIn();
  $('#mobileSearchInp').focus();
});
$('.bottom-header-content .search-block .search-forms .input .close-btn').on('click',function(){
  $('.search-block').fadeOut();
  $('#mobileSearchInp').val('');
});
//*******************************************

// ************Mobile Search Result*************
$('#mobileSearchInp').click(function(){
  if($('#mobileSearchInp').val() != ''){
    searchForMobile($(this));
  }
});

$('#search-form1').on("input", function(){
    searchForDesktop();
});

$('#search-form1').keypress(function(event) {
  if(event.which ==13){
    var searchText = $('#search-form1').val();
    if(searchText.length > 2){
      $.ajax({
        url: '/pagelayout/search/autosuggest?q='+encodeURIComponent(searchText),
        type: 'GET',
        dataType: 'json',
        success: function(data){
          if(data.designer_url){
            var urlAll = data.designer_url
          }
          else{
            var urlAll = data.urlAll;
          }
          if(utm_url!='')
          {
            window.location.href = urlAll+utm_url.replace(/\?/g, '&');
          }
          else
          {
            window.location.href = urlAll;
          }
        }
      });
    }
  }
});

$('#mobileSearchInp').on("input", function(){
  searchForMobile($(this));
});

$('#mobileSearchInp').keypress(function(event) {
  var searchText = $(this).val();
  if(event.which ==13){
    if(searchText != '' && searchText.length > 2){
      if(currentRequest != null){
        currentRequest.abort();
      }
      currentRequest = $.ajax({
        url: '/pagelayout/search/autosuggest?q='+encodeURIComponent(searchText),
        type: 'GET',
        dataType: 'json',
        success: function(data){
          if(data.designer_url){
            var urlAll = data.designer_url;
          }
          else{
            var urlAll = data.urlAll;
          }
          window.location.href = urlAll;
        }
      });
    }
  }
});

$('body').on("input","#mobileSearchInpNative", function(){
  searchForMobile($(this));
});

$('body').on("keydown","#mobileSearchInpNative", function(event){
  if (event.which === 13) {
    if($(".search-result a").length > 0){
      window.location.href = $(".search-result a").attr('href');  
    }
  }
});

var currentRequest = null;
function searchForMobile(searchArea){
  var searchText = $(searchArea).val();
  if(searchText.length > 2){
    if(currentRequest != null){
      currentRequest.abort();
    }
    currentRequest = $.ajax({
      url: '/pagelayout/search/autosuggest?q='+encodeURIComponent(searchText),
      type: 'GET',
      dataType: 'json',
      success: function(data){
        // console.log(data);
        // console.log('Console Data');

        var searchHtml = '';
        if(data.designer_url){
          var urlAll = data.designer_url;
        }
        else{
          var urlAll = data.urlAll;
        }
        if (userAgent == "android" || userAgent == "ios") {
            $(".top-header.native-search .contain .search-block-native .search-block-content").css("display", "block");
        } else {
            $(".bottom-header-content .search-block .search-forms .search-block-content").css("display", "block");
        }
        $("#mobileSearchBtn").click(function () {
            var search = $("#mobileSearchInp").val();
            if (search.length > 2) {
                window.location.href = urlAll;
            }
        });
        if(data.noResults){
            searchHtml += '<div class="search-no-result-found"><div class="no-result-found"><div class="no-result-text">Sorry, there are no results for</div><div class="content-keyword">"'+searchText+'"</div><div class="content-try-new-search">Try a new search</div></div></div>';
            $(".search-block-content").html(searchHtml);
            return false;
        }
        searchHtml +='<div class="search-content search-product">';
        searchHtml +='<div class="left-block"> <div class="category-list"> <div class="tit">'+data.title_text+'</div>'+data.SearchCategoryHtml;
        searchHtml += '</div></div>';
        searchHtml += '<div class="right-block">';
        var searchitems = $.parseJSON(JSON.stringify(data.indices));
        var totalItems = data.totalItems;
        $.each(searchitems, function(i, item) {
          //console.log("step8");
          var items=item.items;
          $.each(items , function(i , product){
            //console.log("step9");
            //console.log(product);
            var name = product.name;
            var url = product.url+utm_url.replace(/\?/g, '&');
            var description = product.description;
            var price = product.price;
            var imageUrl = product.image;
            searchHtml += '<div class="search-column"> <a href="'+url+'"> <div class="img-pod"> <img src="'+imageUrl+'"> </div> <div class="content"> <div class="designer-name">'+name+'</div>  <div class="desc">'+description+'</div> </a> <div class="price">'+price+'</div> </div> </div>';

          });
        });
                        // searchHtml += '</div>'
                        
                        // var categories = {};
                        // var headerNames = {};
        var i = 0;

                        
                        // searchHtml += '<div class="category-list"> <div class="tit">OCCASIONS</div> <div class="cat-list">';
                        // $.each(data.occasions, function( index, value ) {  
          //console.log(value);          
          //console.log('-------value--------');
          // var Url = 'https://aashniandco.com/new-in.html?firstAttemptedFilter=occasionFilter&occasionFilter='+value.Id 
          // var designer = value.name !== false ? value.name : '';
          // // console.log(typeof designer);
          // // console.log("te");

          // if(designer == undefined || designer == 'undefined'){

          //  searchHtml += ''

          // }else{

          //  searchHtml += '<a href='+Url+'><div class="list">'+designer+'</div></a>'
          //}
        //});
                        searchHtml +='</div> </div> </div>'

        searchHtml +='<div class="search-result"> <a href="'+urlAll+'" class="text">View all results</a> </div>';
        //console.log('Nayaab 1');
        //console.log(searchHtml);
        if(userAgent == 'android' || userAgent == 'ios'){
          $(".top-header.native-search .contain .search-block-native .search-block-content").html(searchHtml);
        }
        else{
        $(".bottom-header-content .search-block .search-forms .search-block-content").html(searchHtml);
        }
      }
    });
  }
  else{
    //console.log("Result Empty No Search Found");
    $(".bottom-header-content .search-block .search-forms .search-block-content").css('display', 'none');
  }
}

function searchForDesktop(){
  var searchText = $('#search-form1').val();
  if(searchText.length > 2){
    if(currentRequest != null){
      currentRequest.abort();
    }
    currentRequest = $.ajax({
      url: '/pagelayout/search/autosuggest?q='+encodeURIComponent(searchText),
      type: 'GET',
      dataType: 'json',
      success: function(data){
        // console.log(data);
        // console.log('Console Data');

        var searchHtml = '';
        $('.search-form .form-search form .search-block-content').addClass('addBefore');
        $(".search-form  .form-search form .search-block-content").css('display', 'block');
        if(data.noResults){
            searchHtml += '<div class="search-no-result-found"><div class="no-result-found"><div class="no-result-text">Sorry, there are no results for</div><div class="content-keyword">"'+searchText+'"</div><div class="content-try-new-search">Try a new search</div></div></div>';
            $(".search-block-content").html(searchHtml);
            return false;
        }
        searchHtml +='<div class="search-content search-product">';
        searchHtml +='<div class="left-block"> <div class="category-list"> <div class="tit">'+data.title_text+'</div>'+data.SearchCategoryHtml;
        searchHtml +='</div> </div>'
        searchHtml += '<div class="right-block"><div class="tit">PRODUCTS</div>';
        var searchitems = $.parseJSON(JSON.stringify(data.indices));
        var totalItems = data.totalItems;
        if(data.designer_url){
          var urlAll = data.designer_url;
        }
        else{
          var urlAll = data.urlAll;
        }
        $.each(searchitems, function(i, item) {
          console.log("step8");
          var items=item.items;
          $.each(items , function(i , product){
            console.log("step9");
            console.log(product);
            var name = product.name;
            var url = product.url+utm_url.replace(/\?/g, '&');
            var description = product.description;
            var price = product.price;
            var imageUrl = product.image;
            searchHtml += '<div class="search-column"> <a href="'+url+'"> <div class="img-pod"> <img src="'+imageUrl+'"> </div> <div class="content"> <div class="designer-name">'+name+'</div>  <div class="desc">'+description+'</div> </a> <div class="price">'+price+'</div> </div> </div>';

          });
        });
        // searchHtml += '</div>'
        // searchHtml +='<div class="left-block"> <div class="category-list"> <div class="tit">SEARCH FROM PAGES</div>'+data.SearchCategoryHtml;
        var categories = {};
        var headerNames = {};
        var i = 0;

        // searchHtml += '</div>';
        // searchHtml += '<div class="category-list"> <div class="tit">OCCASIONS</div> <div class="cat-list">';
        // $.each(data.occasions, function( index, value ) {  
        //   console.log(value);          
        //   console.log('-------value--------');
        //   var Url = 'https://aashniandco.com/new-in.html?firstAttemptedFilter=occasionFilter&occasionFilter='+value.Id 
        //   var designer = value.name !== false ? value.name : '';
        //   // console.log(typeof designer);
        //   // console.log("te");

        //   if(designer == undefined || designer == 'undefined'){

        //     searchHtml += ''

        //   }else{

        //     searchHtml += '<a href='+Url+'><div class="list">'+designer+'</div></a>'
        //   }
        // });
        searchHtml +='</div> </div> </div>'

        searchHtml +='<div class="search-result"> <a href="'+urlAll+'" class="text">View all results</a> </div>';
        console.log('Nayaab 1');
        console.log(searchHtml);
        $(".search-form  .form-search form .search-block-content").html(searchHtml);
        $('.search-wrap .search-plot i.fa-search').click(function(){
          var search = $('#search-form1').val();
          if(search.length > 2){
            window.location.href = urlAll;
          }
        })
      }
    });
  }
  else{
    //console.log("Result Empty No Search Found");
    $('.search-form .form-search form .search-block-content').removeClass('addBefore');
    $(".bottom-header-content .search-block .search-forms .search-block-content").css('display', 'none');
  }
}

jQuery('body').click(function(e) {
  if (!jQuery(e.target).closest('.search-block-content').length && jQuery(".search-block-content").is(':visible')){
    jQuery(".search-block-content").hide();
  }
});
// ---------------search end ----------------

    var baseurl = $('#base_url').val();
    html = '';

    function getQueryParam(param) {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    var token = getQueryParam('token');

    if(token != null && token != ''){
      $.ajax({
        url: '/customer/account/loginPost',
        method: 'post',
        async:'false',
        data: {'isAjax':1,'token':token},
        success: function(res){
          callSession();
          var sections = ['cart'];
          customerData.invalidate(sections);
          customerData.reload(sections, true);
        },
        error:function(err){
          callSession();
        }
      });
    }else{
      callSession();
    }

    function callSession(){
      $.ajax({
        url: baseurl + 'session.php',
        method: 'post',
        success: function(result){
          res = JSON.parse(result);
          if(res.is_logged_in){
            html = "<span>"+res.welcomeMessage+"</span>";
            $('.greet.welcome').html(html);
            // var loginUrl = baseurl+'customer/account/';
            var loginhtml = '<a class="h7_signout" href="customer/account/logout/"><i class="fa fa-unlock-alt" aria-hidden="true"></i></a>';
            // jQuery('.middle-header-content .middle-header-wrp .login-custom-link .right-icon').prepend(loginhtml);
            jQuery('.middle-header-content .middle-header-wrp .login-custom-link .right-icon .header_wishlist').css('width', '40px');
            jQuery(loginhtml).insertAfter('.middle-header-content .middle-header-wrp .login-custom-link .right-icon .header_wishlist');
            var mobileLoginHtmlInMenu = '<a href="customer/account/logout/">Sign Out</a>';
            var mobileLoginHtml = "<a href='customer/account/logout'><span class='fa fa-user' aria-hidden='true'></span><span> Sign Out</span> </a>";
            jQuery('.main-page-wrapper .page-wrapper .new-header .sticky-header .top-header-content .sign-in.m_login').html(mobileLoginHtml);
          }else{
            // var loginUrl = baseurl+'customer/account/login';
            // var loginhtml = '<a href="'+loginUrl+'"><span class="fa fa-user" aria-hidden="true"></span></a>';
            var mobileLoginHtmlInMenu = '<a href="customer/account/login/">Sign In</a>';
          }

        //minicart count update and minicart pop code start
                        
         if (res.cart_item_count !== undefined && res.cart_item_count !== null) {
          console.log("Cart items count:", res.cart_item_count);  // Log cart count
          $('.counter-number').text(res.cart_item_count);  // Update the cart count in the frontend
        } else {
          console.log("No cart items found.");
          $('.counter-number').text(0);  // Set to 0 if no cart items
        }

          // // Handling cart items in mini-cart start
            if (res.cart_items && res.cart_items.length > 0) {
              
                var cartHTML = '';
                res.cart_items.forEach(function(item) {

                    cartHTML += `
                    <div class="cart-product">
                        <div class="cart-product-left-plot">
                            <div class="minicart-items-wrapper">
                                <div class="cart-product-name"><a href="${item.product_url}">${item.product_name}</a></div>
                                <div class="cart-product-size">
                                    <span class="size-txt">Size:</span>
                                    <span class="size-values">${item.product_size}</span>
                                </div>
                                <div class="cart-product-price"><span class="price">₹${(parseFloat(item.product_price) || 0).toFixed(2)}</span></div>

                                <div class="qty-details">
                                    <label for="">Qty:</label>
                                    <input type="number" value="${item.product_qty}" readonly>
                                </div>
                            </div>
                            <div class="icon">
                                <a href="#"><i class="fa fa-edit"></i></a>
                                <a href="#"><i class="fa fa-remove"></i></a>
                            </div>
                        </div>
                        <div class="cart-product-right-plot">
                            <div class="cart-product-image">
                                <a href="${item.product_url}"><img src="${item.product_image}" alt="${item.product_name}"></a>
                            </div>
                        </div>
                    </div>`;
                });

                // Append cart items to the minicart
                $('.minicart-items-wrapper').html(cartHTML);

                // Update the total price
                $('.total-price').text('₹' + (parseFloat(res.grand_total) || 0).toFixed(2));

              } else {
                $('.minicart-items-wrapper').html('<p>No items in cart</p>');
                $('.total-price').text('₹0.00');
            }


        // Handling cart items in mini-cart start
            if (res.cart_items && res.cart_items.length > 0) {
                console.log("append----");
                var cartHTML = '';
                res.cart_items.forEach(function(item) {
                  console.log("append1----",item);
                    cartHTML += `
                    <div class="cart-product">
                        <div class="cart-product-left-plot">
                            <div class="minicart-items-wrapper">
                                <div class="cart-product-name"><a href="${item.product_url}">${item.product_name}</a></div>
                                <div class="cart-product-size">
                                    <span class="size-txt">Size:</span>
                                    <span class="size-values">${item.product_size}</span>
                                </div>
                                <div class="cart-product-price"><span class="price">₹${(parseFloat(item.product_price) || 0).toFixed(2)}</span></div>
                                <div class="qty-details">
                                    <label for="">Qty:</label>
                                    <input type="number" value="${item.product_qty}" readonly>
                                </div>
                            </div>
                            <div class="icon">
                                <a href="${item.configure_url}" ><i class="fa fa-edit"></i></a>
                                <a href="#"><i class="fa fa-remove" data-item-id="${item.item_id}"></i></a>
                            </div>
                        </div>
                        <div class="cart-product-right-plot">
                            <div class="cart-product-image">
                                <a href="${item.product_url}"><img src="${item.product_image}" alt="${item.product_name}"></a>
                            </div>
                        </div>
                    </div>`;
                });

                // Create the full minicart HTML structure
                var minicartHTML = `
                <div class="block-minicart" id="minicart" style="display:none;">
                    <div class="minicart-content-wrapper">
                        <div class="add-block-content">
                            <div class="cart-product-wrap">
                                    ${cartHTML}
                                </div>
                                <div class="cart-product-total">
                                    <div class="total-txt">Total:</div>
                                    <div class="total-price">₹${(parseFloat(res.grand_total) || 0).toFixed(2)}</div>
                                </div>
                                <div class="go-to-cart">
                                    <a class="viewcart" href="#">
                                        Go To Cart
                                    </a>
                                </div>
                            
                        </div>
                    </div>
                </div>`;

                // Append the full minicart after the counter number
                $('.right-icon').append(minicartHTML);
            } else {
                var emptyCartHTML = `
                <div class="block-minicart">
                    <div class="minicart-content-wrapper">
                        <div class="add-block-content">
                            <div class="cart-product-wrap">
                                <div class="minicart-items-wrapper">
                                    <p>No items in cart</p>
                                </div>
                                <div class="cart-product-total">
                                    <div class="total-txt">Total:</div>
                                    <div class="total-price">₹0.00</div>
                                </div>
                                <div class="go-to-cart">
                                    <a class="viewcart" href="#">
                                        Go To Cart
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;

                // Append the empty cart message after the counter number
                $('.right-icon').append(emptyCartHTML);
            }


            // Handling cart items in mini-cart end

          // jQuery('#sticky_login_link .search-form').after(loginhtml);
          jQuery('#main-Accountcontent .top-links .mobile-menu.signin-signout').html(mobileLoginHtmlInMenu);
          // if(res.form_key && jQuery("input[name='form_key']").length > 0){
          //   setTimeout(function(){
          //     jQuery("input[name='form_key']").val(res.form_key);
            // },1000);

          }

        });
      }

  
    // Show the mini-cart on hover
    // $('.right-icon').hover(
    //     function() {
    //         $('#minicart').stop(true, true).fadeIn(200); // Show mini-cart on hover
    //     },
    //     function() {
    //         $('#minicart').stop(true, true).fadeOut(200); // Hide mini-cart when not hovering
    //     }
    // );

    // // Optional: If you want to hide the mini-cart when clicking outside of it
    // $(document).on('click', function(event) {
    //     if (!$(event.target).closest('.right-icon, #minicart').length) {
    //         $('#minicart').fadeOut(200);
    //     }
    // });

     
    // // Click event to toggle the minicart
    // $('.showcart').on('click', function(e) {
    //     e.preventDefault(); // Prevent default link behavior
    //     $('.block-minicart').toggle(); // Toggle the minicart visibility
    // });

    // // Close minicart when clicking outside
    // $(document).on('click', function(e) {
    //     var $target = $(e.target);
    //     if (!$target.closest('.block-minicart').length && !$target.closest('.showcart').length) {
    //         $('.block-minicart').hide(); // Hide minicart if clicked outside
    //     }
    // });


   
 var isHoveringMinicart = false; // Tracks if the minicart is being hovered
var isClickToggled = false;    // Tracks if minicart was toggled via click

// Toggle minicart visibility on click of the .showcart button
$('.showcart').on('click', function (e) {
    e.preventDefault(); // Prevent default action
    e.stopPropagation(); // Prevent event bubbling
    isClickToggled = !isClickToggled; // Toggle the click state

    if (isClickToggled) {
        $('#minicart').fadeIn(200);   // Show minicart when toggled via click
    } else {
        $('#minicart').fadeOut(200);  // Hide minicart when clicked again
    }
});

// Close minicart when clicking outside of it (but not when hovering)
$(document).on('click', function (e) {
    if (!$(e.target).closest('#minicart').length && !$(e.target).closest('.showcart').length) {
        if (!isHoveringMinicart && isClickToggled) {
            $('#minicart').fadeOut(200); // Close minicart if clicked outside
            isClickToggled = false;     // Reset click toggle state
        }
    }
});

// Keep the minicart open while hovering over it
$('.right-icon .showcart').hover(
    function () {
        isHoveringMinicart = true;  // Minicart is hovered
        $('#minicart').stop(true, true).fadeIn(200); // Ensure it stays open when hovered
    },
    function () {
        isHoveringMinicart = false; // Hover has ended
        if (!isClickToggled) {      // Only close minicart if not toggled by click
            $('#minicart').fadeOut(200);
        }
    }
);

// Ensure button hover doesn't interfere with click toggle
$('.showcart').hover(
    function () {
        isHoveringMinicart = true;  // Prevent hide when hovering the button after click
    },
    function () {
        isHoveringMinicart = false; // Reset hover state when not hovering button
    }
);

      // const confirmBtn = document.getElementById('confirmBtn');
      //    confirmBtn.addEventListener('click', () => {
      //      alert('Confirm button clicked!');
      //    });
       
         // Cancel button click event - closes the popup
         const cancelBtn = document.getElementById('cancelBtn');
         if(cancelBtn){
            cancelBtn.addEventListener('click', () => {
             document.getElementById('popup').style.display = 'none';
            });
         }
       
         // Close the popup by clicking outside the card
         const popupBackground = document.getElementById('popup');
         if(popupBackground){
            popupBackground.addEventListener('click', (e) => {
             if (e.target === popupBackground) {
               popupBackground.style.display = 'none';
             }
           });
         }
        
       
        let itemIdToRemove = null;
        let formKey = $('#formKey').val();
    
       console.log('Form Key:', formKey);

        // Attach click event to the dynamically generated remove buttons
        $(document).on('click', '.fa.fa-remove', function(e) {
            e.preventDefault();
            itemIdToRemove = $(this).data('item-id'); // Get the item_id from data attribute
            console.log("Item ID to remove:", itemIdToRemove);
            $('#popup').fadeIn(); // Show confirmation popup
        });

        // Cancel button to hide the popup
        $('#cancelBtn').on('click', function() {
            $('#popup').fadeOut(); // Close the pop-up
        });

        // Confirm button to remove the item
        $('#confirmBtn').on('click', function() {

            if (itemIdToRemove) {
                // Make the AJAX request to remove the item
                $.ajax({
                    url: 'checkout/sidebar/removeItem/', // Your Magento 2 remove item URL
                    type: 'POST',
                    data: {
                        item_id: itemIdToRemove, // Pass the item ID
                        form_key: formKey // Include the form key
                    },
                    success: function(response) {
                      console.log(response);
                      if (response.success) {
                          console.log("Item removed successfully");
                          location.reload(); // Refresh the page
                      } else {
                          console.error("Error:", response.error_message); // Handle errors
                          alert(response.error_message); // Display the error to the user
                      }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error removing item: " + error);
                    }
                });

                $('#popup').fadeOut(); // Close the popup after the AJAX request is triggered
            }
        });

        // Optional: Hide the mini-cart when clicking outside of it
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.block-minicart, .fa-remove').length) {
                $('#minicart').fadeOut(); // Hide minicart if clicked outside
            }
        });
    

    

     //minicart count update and minicart pop code end
         
    var newsletterSubBtn = jQuery('.footer-container-wrapper .top-footer .top-footer-wrap .top-footer-container .news-letter-wrap .subscribe-form .actions-subscribe .subscribe-btn');
    var newsletterEmail = jQuery('.footer-container-wrapper .top-footer .top-footer-wrap .top-footer-container .news-letter-wrap .subscribe-form .newsletter .newsletter-input input');
    var newsletterEmailError = jQuery('.footer-container-wrapper .top-footer .top-footer-wrap .top-footer-container .news-letter-wrap .subscribe-form .newsletter .newsletter-input #newsletter-footer-error');

    jQuery(newsletterSubBtn).click(function(e){
      newsletterEmailError.hide();
      var email = newsletterEmail.val();
      if(email.length > 0){
        if(isEmail(email)){
          jQuery.ajax({
            url: 'newsletter/subscriber/new',
            method: 'post',
            data: {'email': email},
            success: function(response){
              if(!response.error){
                jQuery('.footer-container-wrapper .top-footer .top-footer-wrap .top-footer-container .news-letter-wrap .subscribe-form').hide();
                jQuery('.messages .message.message-error.error div').removeClass('error');
                jQuery('.messages .message.message-error.error div').addClass('success');
              }
              else{
                jQuery('.messages .message.message-error.error div').removeClass('success');
                jQuery('.messages .message.message-error.error div').addClass('error');
              }
              jQuery('.messages .message.message-error.error div').html(response.message);
            }
          })  
        }
        else{
          newsletterEmailError.html('Enter valid email!');
          newsletterEmailError.show();
        }
      }
      else{
        newsletterEmailError.html('This is a required field.');
        newsletterEmailError.show();
      }
    });

    $('.news-letter-wrap .subscribe-form .newsletter .newsletter-input input').on('keypress', function(event) {
        if (event.which === 13) {
            event.preventDefault();
            $(newsletterSubBtn).trigger('click');
        }
    });

    function isEmail(email) {
      var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
      return regex.test(email);
    }


      //newsletter popup js start-------

      // Get modal element
      const modal = document.getElementById('customModal');
      // Get close button
      const closeBtn = document.querySelector('.modal-content .content-wrapper .close-btn');
      const closeBtnMobile = document.querySelector('.modal-content-mobile .content-wrapper-mobile .close-btn');

      // Listen for close click
      closeBtn.addEventListener('click', () => {
          modal.style.display = 'none';
      });

      closeBtnMobile.addEventListener('click', () => {
        modal.style.display = 'none';
      });

      // Listen for outside click
      window.addEventListener('click', (e) => {
          if (e.target === modal) {
              modal.style.display = 'none';
          }
      });

      // Show popup after page load with a delay, only if cookie is not set
      // if ($(window).width() > 991) {
          if (getCookie('newsletterpopup') != 'nevershow') {
              setTimeout(function() {
                  modal.style.display = 'block';
              }, 4500); // Show popup after 60 seconds
          }
      // }

      // Function to get cookie value
      function getCookie(cname) {
          var name = cname + "=";
          var ca = document.cookie.split(';');
          for (var i = 0; i < ca.length; i++) {
              var c = ca[i];
              while (c.charAt(0) == ' ') c = c.substring(1);
              if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
          }
          return "";
      }

      // Attach the dontShowPopup function to the window object
      window.dontShowPopup = function(el) {
          if ($('#' + el).prop('checked')) {
              var d = new Date();
              d.setTime(d.getTime() + (24 * 60 * 60 * 1000 * 365)); // 1-year expiration
              var expires = "expires=" + d.toUTCString();
              document.cookie = 'newsletterpopup=nevershow; ' + expires + '; path=/';
          }
      };

      $('.form-subscribe').on('submit', function(e) {
          e.preventDefault(); // Prevent default form submission

          var form = $(this);
          var actionUrl = form.attr('action'); // Get the form action URL

          $.ajax({
              type: 'POST',
              url: actionUrl,
              data: form.serialize(), // Send form data
              success: function(response) {
                  // Handle success (you can show a success message, close the modal, etc.)
                  console.log('Subscription successful:', response);
                  modal.style.display = 'none'; // Close modal on success
              },
              error: function(error) {
                  // Handle error
                  console.error('Subscription failed:', error);
              }
          });
      });
               
      //newsletter popup js end-------

    //code to hide contextual footer in native application after page loading
    const agent = getQueryParam('agent');
    console.log("nativeapp :: agent -> " + agent);
    if((agent != '' || agent != null || agent != undefined) && (agent === 'android' || agent === 'ios')){
      console.log("nativeapp :: setting cookie aashni_app");
      setCookie('aashni_app', agent, 180,'.aashniandco.com','/',true);
    }
    if (agent === 'android' || agent === 'ios' || getCookie('aashni_app') == 'android' || getCookie('aashni_app') == 'ios') {
      console.log("nativeapp :: hiding contextual footer");
      $(".middle-footer").hide();
    }

    function setCookie(cname, cvalue, exdays, domain, path,secureflag) {
      console.log('Setting cookie: ' + cname + '=' + cvalue);
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
      // if (httponly) {
      //   cookieString += ";httponly=" + httponly;
      // }
      if (secureflag) {
        cookieString += ";secure=" ;
      }
      document.cookie = cookieString;
    }
});

    function openmenu(evt, menutabs) {
      var i, tabcontentmenus, tablinks;
      tabcontentmenus = document.getElementsByClassName("tabcontentmenus");
      for (i = 0; i < tabcontentmenus.length; i++) {
          tabcontentmenus[i].style.display = "none";
      }
      tablinks = document.getElementsByClassName("tablinks");
      for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active-tabs", "");
      }
      document.getElementById(menutabs).style.display = "block";
      evt.currentTarget.className += " active-tabs";
    }


var myScrollFunc = function() {
  var y = window.scrollY;
  if (y < lasty && y >= 100 && !jQuery('body').hasClass('catalog-product-view')) {
      document.getElementById('aashnisticky').style.position = 'fixed';
      document.getElementById('aashnisticky').style.zIndex = '100';
      document.getElementById('aashnisticky').style.right = '0';
      document.getElementById('aashnisticky').style.top = '0';
      document.getElementById('aashnisticky').style.left = '0';
      document.getElementById('aashnisticky').style.width = '100%';
      document.getElementById('aashnisticky').style.background = '#fff';

      if (jQuery(document).width() > 834 && jQuery('body').hasClass('pagelayout-listing-index')) {
          if (jQuery('.products.list.items.product-items li').length > 0) {
              if (isOnScreen('footer')) {
                  var element = document.getElementsByTagName("footer")[0];
                  var rect = element.getBoundingClientRect();
                  var filterBottom = window.innerHeight - rect.y + 10;
                  if (!jQuery('.sidebar-main').hasClass('filter-sticky')) {
                      jQuery('.sidebar-main').addClass('filter-sticky');
                  }
                  jQuery('.sidebar-main').css("bottom", filterBottom + "px");
                  jQuery('.sidebar-main').css("top", "unset");
                  //jQuery('.sidebar-main').removeClass('filter-sticky');
              } else {
                  if (!jQuery('.sidebar-main').hasClass('filter-sticky')) {
                      jQuery('.sidebar-main').addClass('filter-sticky');
                  }
                  jQuery('.sidebar-main').css("bottom", "unset");
                  var filterTop = jQuery('#aashnisticky').height() + 10;
                  jQuery('.sidebar-main').css("top", filterTop + "px");
              }
          } else {
              jQuery('.sidebar-main').css("top", "unset");
              jQuery('.sidebar-main').css("bottom", "unset");
              jQuery('.sidebar-main').removeClass('filter-sticky');
          }
      }
  } else {
      jQuery('.sidebar-main').css("top", "unset");
      jQuery('.sidebar-main').css("bottom", "unset");
      jQuery('.sidebar-main').removeClass('filter-sticky');
      document.getElementById('aashnisticky').style.position = '';
      document.getElementById('aashnisticky').style.zIndex = '';
      document.getElementById('aashnisticky').style.right = '';
      document.getElementById('aashnisticky').style.top = '';
      document.getElementById('aashnisticky').style.left = '';
      document.getElementById('aashnisticky').style.width = '';
      document.getElementById('aashnisticky').style.background = '';
  }
  lasty = y;
};
var lasty = 0;
window.addEventListener("scroll", myScrollFunc);

function isOnScreen(elem) {
  // if the element doesn't exist, abort
  if (elem.length == 0) {
      return;
  }
  var $window = jQuery(window)
  var viewport_top = $window.scrollTop()
  var viewport_height = $window.height()
  var viewport_bottom = viewport_top + viewport_height
  var $elem = jQuery(elem)
  var top = $elem.offset().top
  var height = $elem.height()
  var bottom = top + height


  return (top >= viewport_top && top < viewport_bottom) ||
      (bottom > viewport_top && bottom <= viewport_bottom) ||
      (height > viewport_height && top <= viewport_top && bottom >= viewport_bottom)
}

var myScrollFunc = function() {
    var y = window.scrollY;
 
    // For sticky_logo
    if (y >= 150) {
        document.getElementById('sticky_logo').style.width = '70%';
        document.getElementById('sticky_logo').style.margin = 'auto';
    } else {
        document.getElementById('sticky_logo').style.width = '';
        document.getElementById('sticky_logo').style.margin = '';
    }
 
    // For login-custom-link
    var loginLinks = document.querySelectorAll('.login-custom-link');
    loginLinks.forEach(function(link) {
        if (y >= 150) {
            link.style.padding = '0px';
        } else {
            link.style.padding = '';
        }
    });
};
 
window.addEventListener("scroll", myScrollFunc);