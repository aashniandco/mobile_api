$(function(){
  // Login/wishlist/Logout $
    $(document).on('click','.profile-icon',function(e){
        e.preventDefault();
        var isLoggedIn = $('#isLoggedIn').val();
        if(isLoggedIn==1){
            window.location.href = '/customer/account/';
            
        }else{
            window.location.href = '/customer/account/login/';
        }
    });

    $(document).on('click','.wishlist-icon',function(e){
        e.preventDefault();
        var isLoggedIn = $('#isLoggedIn').val();
        if(isLoggedIn==1){
            window.location.href = '/wishlist';
            
        }else{
            window.location.href = '/customer/account/login/';
        }
    });
    $(document).on('click','.unlock-icon',function(e){
        e.preventDefault();
        var isLoggedIn = $('#isLoggedIn').val();
        if(isLoggedIn==1){
            window.location.href = '/customer/account/logout/';
            
        }else{
            window.location.href = '/customer/account/login/';
        }
    });
    /* hamburger tabs open*/    
    
    $('.tabs li').click(function(){
      var tab_id = $(this).attr('data-tab');

      $('.tabs li').removeClass('current');
      $('.card-block-tab ').removeClass('current');

      $(this).addClass('current');
      $("#"+tab_id).addClass('current');
    });

     /* hamburger tabs ends*/ 

    // on click hamburger start here


    if($('.left-sec .humburger-icon').length > 0)
        {
          $('.humburger-icon').on('click',function(){
              $('.mobile-menu').animate({left: '0'},300);
              $('.mobile-menu').fadeIn();
              $('.popup-cover-black').fadeIn();
              
             $('html, body').css({overflow: 'hidden', position: 'fixed'});
          });

          $('.close-menu').on('click',function(){
              // $('html, body').css({overflow: 'auto', position: 'unset'});
              $('.mobile-menu').animate({left: '-90%'},300);
              $('.popup-cover-black').fadeOut();
              // $('.mobile-menu').fadeIn();
          });
        }

    // on click hamburger ends here 

    // on click hamburger dropdown start here

    $('.mobile-menu .menu-card-wrap .navigation .nav .static-menu a.dropdown-toggle').on('click',function(e){
      e.preventDefault();
      if($(this).attr('aria-expanded') == 'false'){
        $(this).attr('aria-expanded','true');
        $(this).parent().find('.menu-block').slideDown();
        $(this).addClass('active');
        console.log('dropdown');
      }
      else{
        $(this).attr('aria-expanded','false');
        $(this).parent().find('.menu-block').slideUp();
        $(this).removeClass('active');
      }
      
    });

    // on click hamburger dropdown ends here

       // Search Bar
  //     $(document).on('click','.account-block .search,.asset-plot .search', function(){
  //   $('.search-wrap').show();
  //   $('#search_input').focus();

  // });

  // $(document).on('click','.search-block .close', function(){
  //   $('.search-wrap').hide();
  //   $('#search_input').val('');
  //   $('.search-content').hide();

  // });

  $(document).on("keyup","#search_input", function(e) {
    var keyword = $(this).val().toUpperCase();

    if(keyword.length > 0)
    {
      //$('.search-content').show();
      if(e.which == 13)
      {
        $('.search-icon').click();
      }
    }
    
  });

$(document).on('click','.search-icon',function(){
  console.log('Step1------');
  var eq = $('#search_input').val();
   console.log(eq+' :: search');
  if(eq != '' || eq != 'undefined' || eq != undefined)
  { 
    console.log('Step2------');
    var baseUrl = $('#websitebaseUrl').val();
    var searchUrl = baseUrl+'catalogsearch/result/'+'?q='+eq;
console.log(searchUrl+' :: searchURL');
// return false;
    window.location.href = searchUrl;
  } 

});

//Auto suggest
    $(document).on("keyup",'#search_input', function() {
    var keyword = $(this).val();    

    if(keyword.length >= 2)
    {
      var searchText = $('#search_input').val();
     // var form_key = $('#form_key_input').val();
      var searchData = {text : searchText};

      if(searchText != '')
      {
        $.ajax({
          url: '/pagelayout/search/autosuggest?q='+searchText,
          type: 'POST',
          data: searchData,
          dataType : 'json',
          headers: {
            "AllowRequest":"1"
          },
          success: function(response){
            console.log(response);
          }
        });
      }
    }


  });


       
    

    });    
    


    