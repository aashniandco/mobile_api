  
    // price filter code 25-08-2023
    require(['jquery', 'Fermion_Pagelayout/js/nouislider'],function($, noUiSlider){
    (function() {
        $(document).ready(function () {

        // sticky filter css 31-08-2023
        /*stick filter end*/
        
        /*stick filter end*/
        /*initializing of noUiSlider for mobile and desktop*/
            if (!$('body').hasClass('no-search-result')) {
                var availableFilters = getFilterValues();
                var appliedMobileFilters = availableFilters;
                var minimumPrice = Math.floor(Number(jQuery("#min-price").val())); 
                var maximumPrice = Math.floor(Number(jQuery("#max-price").val()));

                var minHiddenPrice = Math.round(parseFloat(jQuery("#min-price").val()));
                var maxHiddenPrice = Math.round(parseFloat(jQuery("#max-price").val()));

                var priceRange = availableFilters.priceFilter || [minimumPrice, maximumPrice];
                var minimumPriceForSlide = Math.floor(Number(priceRange[0]));
                var maximumPriceForSlide = Math.floor(Number(priceRange[1]));    
                
                    var snapSlider = document.getElementById('slider-snap');
                    noUiSlider.create(snapSlider, {
                       start: [minimumPriceForSlide, maximumPriceForSlide],
                       connect: true,
                       range: {
                           'min': minimumPrice,
                           'max': maximumPrice
                       }
                    });

                    var snapValues = [
                       document.getElementById('slider-snap-value-lower'),
                       document.getElementById('slider-snap-value-upper')
                    ];
                    var snapInput = [
                       document.getElementById('price-min-val'),
                       document.getElementById('price-max-val')
                    ];
            
                    snapSlider.noUiSlider.on('update', function(values, handle) {
                    snapValues[handle].innerHTML = Math.floor(values[handle]);
                    snapInput[handle].value = Math.floor(values[handle]);
                    jQuery(".product-value-one .value-lower").text(Math.floor(values[0]));
                    jQuery(".product-value-two .value-upper").text(Math.floor(values[1]));
                });

                function filterData() {
                    clearTimeout(filterTimer); 
                    filterTimer = setTimeout(function () {
                        var minPrice = Math.floor(parseFloat(minPriceInput.value));
                        var maxPrice = Math.floor(parseFloat(maxPriceInput.value));
                        //console.log("AJAX call for filtering data for price range: " + minPrice + " - " + maxPrice);
                        setFilter("firstAttemptedFilter", "priceFilter");
                        applyFilters("priceFilter", minPrice + '+' + maxPrice);

                        //breadcrum
                        var bread_txt = minPrice + '-' + maxPrice;
                        var filterType = "priceFilter";
                        if (jQuery('.amshopby-filter-current ol').find("li[data-container='priceFilter']").length) {
                            jQuery('.amshopby-filter-current ol').find("li[data-container='priceFilter']").find(".amshopby-filter-value").text(bread_txt); 
                        } else {
                            jQuery('.amshopby-filter-current ol').append('<li class="item amshopby-item" data-am-js="shopby-item" data-container="'+filterType+'" data-info="'+bread_txt+'" data-value="'+bread_txt+'" ><a class="amshopby-remove" href="javascript:void(0)" title="Remove '+filterType.replace("Filter", "")+' '+bread_txt+'"></a><span class="amshopby-filter-name">'+filterType.replace("Filter", "")+'</span><div class="amshopby-filter-value">'+bread_txt+'</div></li>');
                        }
                        jQuery(".block-actions.filter-actions").show();
                        //breadcrum end

                    }, 300);
                }

                var filterTimer;

                snapSlider.noUiSlider.on('slide', function (values, handle) {
                    var value = values[handle];
                    if (handle) {
                        maxPriceInput.value = Math.floor(value);
                    } else {
                        minPriceInput.value = Math.floor(value);
                    }
                    filterData(); 
                });

                snapSlider.noUiSlider.on('change', function (values, handle) {
                    var value = values[handle];
                    if (handle) {
                        maxPriceInput.value = Math.floor(value);
                    } else {
                        minPriceInput.value = Math.floor(value);
                    }
                    filterData(); 
                });

                var minPriceInput = document.getElementById('price-min-val');
                var maxPriceInput = document.getElementById('price-max-val');

                minPriceInput.addEventListener('change', function () {
                    var enteredValue = Math.round(parseFloat(this.value));
                    if (enteredValue < minHiddenPrice) {
                        this.value = minHiddenPrice;
                    } else if (enteredValue > maxHiddenPrice) {
                        this.value = maxHiddenPrice;
                    }
                    snapSlider.noUiSlider.set([Math.round(this.value), null]);
                    filterData();
                });

                maxPriceInput.addEventListener('change', function () {
                    var enteredValue = Math.round(parseFloat(this.value));
                    if (enteredValue > maxHiddenPrice) {
                        this.value = maxHiddenPrice;
                    } else if (enteredValue < minHiddenPrice) {
                        this.value = minHiddenPrice;
                    }
                    snapSlider.noUiSlider.set([null, Math.round(this.value)]);
                    filterData();
                });
                // giving issue for inputs
                // jQuery("#price-min-val, #price-max-val").on('input', function() {
                //     updateSliderFromInputs();
                // });
                // function updateSliderFromInputs() {
                //     var minPrice = Math.floor(parseFloat(jQuery("#price-min-val").val()));
                //     var maxPrice = Math.floor(parseFloat(jQuery("#price-max-val").val()));
                //     snapSlider.noUiSlider.set([minPrice, maxPrice]);
                // }


                jQuery(".price-range").on('input', "#price-min-val, #price-max-val", function() {
                    jQuery(".price-range").find(".range-error").hide();
                });


        
    // price filter end  

        $(document).ready(function () {
            
              setFiltersOnHtml(1); 

            if (window.innerWidth <= 1112) {
                jQuery('.filter-drop').on("click",function() {
                    console.log("open");
                    jQuery('.filter-options').toggle();
                });
            }


            jQuery(document.body).on('click','.plusminus', function(){
                if(jQuery(this).hasClass('active') == false){
                    jQuery(this).addClass('active');
                    jQuery(this).parent().siblings('.items.am-filter-items-attr_category_ids.am-labels-folding').slideDown();
                }
                else{
                    jQuery(this).removeClass('active');
                    jQuery(this).parent().siblings('.items.am-filter-items-attr_category_ids.am-labels-folding').slideUp();
                }
            });
            $(window).on({
                 scroll : function(e){
                        $('#mgs-ajax-loading').hide();
                if(isOnScreen(jQuery('footer')) || isOnScreen(jQuery("#product-wrapper .first-element"))){ 
                     jQuery("#product-wrapper .product-item").removeClass('first-element');
                    var stopAjax = jQuery('#stopAjax').val();
                    var isRequestSent = jQuery('#isRequestSent').val();
                    console.log("request sent---",isRequestSent,stopAjax);
                    if (isRequestSent != 1 && stopAjax != 1) {
                        console.log("----inside--");
                        //console.log("current page---",currentPage);
                        var currentPage = Number(jQuery('#current_page').val()) + 1; 
                        jQuery('#current_page').val(currentPage);
                        var availableFilters = getFilterValues();
                        console.log('--after get filter-');
                        callFilterApi(availableFilters, currentPage, '', 1);           
                    }


              
                           
                            }
            }
        });




            jQuery(document).on('click','.filter-options-title',function(){
                console.log('---click-----====-');
                jQuery(this).siblings('.filter-options-content').toggle();  
                jQuery(this).parent().toggleClass('active');   

            });


             



            jQuery(".filter-header-designer").on({
                click : function() {
                    addAndRemoveBreadcrumbs(jQuery(this));           
                    var designers = [];
                    jQuery(".filter-header-designer").find("input:checked").each(function() {
                        designers.push(jQuery(this).val());
                    });
                    var filterValue = jQuery(this).attr("data-info");        
                    
                    setFilter("firstAttemptedFilter", "designerFilter");
                    applyFilters("designerFilter", designers.join("+"),null,null,filterValue);
                }
            },".highlight-check");

            jQuery(".filter-header-patterns").on({
                click : function() {
                    addAndRemoveBreadcrumbs(jQuery(this));           
                    var patterns = [];
                    jQuery(".filter-header-patterns").find("input:checked").each(function() {
                        patterns.push(jQuery(this).val());
                    });
                    var filterValue = jQuery(this).attr("data-info");        

                    setFilter("firstAttemptedFilter", "patternsFilter");
                    applyFilters("patternsFilter", patterns.join("+"),null,null,filterValue);
                }
            },".highlight-check");

            


            /*Color filter for desktop*/
            jQuery(".filter-header-color").on({
                click : function() {   
                    addAndRemoveBreadcrumbs(jQuery(this));         
                    var colors = [];
                    jQuery(".filter-header-color").find("input:checked").each(function() {
                        colors.push(jQuery(this).val());
                    });       
                    var filterValue = jQuery(this).attr("data-info");
                         
                    setFilter("firstAttemptedFilter", "colorFilter");
                    applyFilters("colorFilter", colors.join("+"),null,null,filterValue);
                }
            },".highlight-check");

            /*kid filter for desktop*/
            jQuery(".filter-header-kid").on({
                click : function() {   
                    addAndRemoveBreadcrumbs(jQuery(this));         
                    var kids = [];
                    jQuery(".filter-header-kid").find("input:checked").each(function() {
                        kids.push(jQuery(this).val());
                    });       
                    var filterValue = jQuery(this).attr("data-info");
                         
                    setFilter("firstAttemptedFilter", "kidFilter");
                    applyFilters("kidFilter", kids.join("+"));
                }
            },".highlight-check");

             /*Size filter for desktop*/
                jQuery(".filter-header-size").on({
                    click : function() {     
                        addAndRemoveBreadcrumbs(jQuery(this));       
                        var sizes = [];
                        jQuery(".filter-header-size").find("input:checked").each(function() {
                            sizes.push(jQuery(this).val());
                        });        
                        
                        setFilter("firstAttemptedFilter", "sizeFilter");
                        applyFilters("sizeFilter", sizes.join("+"));
                    }
                },".highlight-check");

                /*bridal filter for desktop*/
                jQuery(".filter-header-bridal").on({
                    click : function() {
                        addAndRemoveBreadcrumbs(jQuery(this));
                        var bridal = [];
                        jQuery(".filter-header-bridal").find("input:checked").each(function() {
                            bridal.push(jQuery(this).val());
                        });              
                        setFilter("firstAttemptedFilter", "bridalFilter");
                        applyFilters("bridalFilter", bridal.join("+"));
                    }
                },".highlight-check");

 /*gender filter for desktop*/
                jQuery(".filter-header-gender").on({
                    click : function() {
                        addAndRemoveBreadcrumbs(jQuery(this));
                        var genders = [];
                        jQuery(".filter-header-gender").find("input:checked").each(function() {
                            genders.push(jQuery(this).val());
                        });              
                        // removePriceFilter();
                        setFilter("firstAttemptedFilter", "genderFilter");
                        applyFilters("genderFilter", genders.join("+"));
                    }
                },".highlight-check");

                /*tags filter for desktop*/
                jQuery(".filter-header-tags").on({
                    click : function() {
                        addAndRemoveBreadcrumbs(jQuery(this));
                        var tags = [];
                        jQuery(".filter-header-tags").find("input:checked").each(function() {
                            tags.push(jQuery(this).val());
                        });              
                        // removePriceFilter();
                        setFilter("firstAttemptedFilter", "tagsFilter");
                        applyFilters("tagsFilter", tags.join("+"));
                    }
                },".highlight-check");

            /*Occasion filter for desktop*/
                jQuery(".filter-header-occasion").on({
                    click : function() {
                        addAndRemoveBreadcrumbs(jQuery(this));
                        var occasions = [];
                        jQuery(".filter-header-occasion").find("input:checked").each(function() {
                            occasions.push(jQuery(this).val());
                        });              
                        // removePriceFilter();
                        setFilter("firstAttemptedFilter", "occasionFilter");
                        applyFilters("occasionFilter", occasions.join("+"));
                    }
                },".highlight-check");

                // theme filter for desktop
                jQuery(".filter-header-theme").on({
                    click : function() {
                        addAndRemoveBreadcrumbs(jQuery(this));
                        var theme = [];
                        jQuery(".filter-header-theme").find("input:checked").each(function() {
                            theme.push(jQuery(this).val());
                        });   
                        console.log (theme);        
                        setFilter("firstAttemptedFilter", "themeFilter");
                        applyFilters("themeFilter", theme.join("+"));
                    }
                },".highlight-check");


                /*Category filter for desktop*/
                jQuery(".filter-header-category").on({
                    click : function() {
                      if(jQuery(this).attr("data-child") == '1'){
                           if(jQuery(this).is(":checked")){
                               jQuery(jQuery(this).siblings('.sub-catgory').find('.sub-check')).each(function(i)
                               {
                                   jQuery(this).prop( "checked", true);
                               });
                           }else{
                               jQuery(jQuery(this).siblings('.sub-catgory').find('.sub-check')).each(function(i)
                               {
                                   jQuery(this).prop( "checked", false );
                               }); 
                           }
                       }
                       var category = [];

                       addAndRemoveBreadcrumbs(jQuery(this)); 

                        jQuery(".filter-header-category").find("input.highlight-check").each(function() {

                          
                           if(jQuery(this).siblings('.sub-catgory').find('.sub-check').length == jQuery(this).siblings('.sub-catgory').find('.sub-check:checked').length && jQuery(this).attr("data-child") == '1'){
                               category.push(jQuery(this).val());
                           }else if(jQuery(this).attr("data-child") == '1'){
                               jQuery(this).siblings('.sub-catgory').find('.sub-check:checked').each(function(){
                                   category.push(jQuery(this).val());
                               });
                           }else{
                               if(jQuery(this).is(":checked")){
                                console.log("-------checked--parent -cat---");
                                   category.push(jQuery(this).val());
                               }   
                           }
                           
                       });


                        setFilter("firstAttemptedFilter", "categoryFilter");
                        applyFilters("categoryFilter", category.join("+"));
                    }
                },".highlight-check");


                jQuery(".filter-header-category").on({
                      click : function() {      
                      addAndRemoveBreadcrumbs(jQuery(this));      
                          
                          var category = [];
                          

                          jQuery(".filter-header-category").find("input.highlight-check").each(function() {

                             console.log("--value---",jQuery(this).val());
                              if(jQuery(this).siblings('.sub-catgory').find('.sub-check').length == jQuery(this).siblings('.sub-catgory').find('.sub-check:checked').length && jQuery(this).attr("data-child") == '1'){
                                console.log("-----here----1--");
                                  category.push(jQuery(this).val());
                              }else if(jQuery(this).attr("data-child") == '1'){
                                 console.log("-----here----2--");
                                  jQuery(this).siblings('.sub-catgory').find('.sub-check:checked').each(function(){
                                      category.push(jQuery(this).val());
                                  });
                              }else{
                                console.log("-----here----3--");
                                  if(jQuery(this).is(":checked")){
                                    console.log("-----here----4--");
                                      category.push(jQuery(this).val());
                                  }   
                              }
                              
                          });
                          console.log("cateogyrfiler",category);
                          // removePriceFilter();
                          setFilter("firstAttemptedFilter", "categoryFilter");
                          applyFilters("categoryFilter", category.join("+"));
                      }
                  },".sub-check");


                /*Occasion filter for desktop*/
                jQuery(".filter-header-a-co-edits").on({
                    click : function() {
                        addAndRemoveBreadcrumbs(jQuery(this));
                        var acoedit = [];
                        jQuery(".filter-header-a-co-edits").find("input:checked").each(function() {
                            acoedit.push(jQuery(this).val());
                        });              
                        setFilter("firstAttemptedFilter", "acoeditFilter");
                        applyFilters("acoeditFilter", acoedit.join("+"));
                    }
                },".highlight-check");

                /*Occasion filter for desktop*/
                jQuery(".filter-header-delivery").on({
                    click : function() {
                        addAndRemoveBreadcrumbs(jQuery(this));
                        var delivery = [];
                        jQuery(".filter-header-delivery").find("input:checked").each(function() {
                            delivery.push(jQuery(this).val());
                        });              
                        setFilter("firstAttemptedFilter", "deliveryFilter");
                        applyFilters("deliveryFilter", delivery.join("+"));
                    }
                },".highlight-check");




                jQuery(document).on("click", "#sorter", function () {
                    applyFilters("sorting", jQuery(this).val(), null, 2);
                });




                 jQuery(document).on({
                    click : function() {
                        var text = jQuery(this).closest("li").attr("data-info");
                        var filterType = jQuery(this).closest("li").attr("data-container");
                        var filterValue = jQuery(this).closest("li").attr("data-value");  
                       
                        // price changes 28-08-2023
                        if(filterType == "priceFilter")
                        {
                                setFilter("priceFilter", "");
                                setFilter("firstAttemptedFilter", "");
                                var minPrice = jQuery("#min-price").val();
                                var maxPrice = jQuery("#max-price").val();
                                snapSlider.noUiSlider.set([Number(minPrice), Number(maxPrice)]);
                                jQuery(this).parent("li.item.amshopby-item").remove();
                                 if (!jQuery('#am-shopby-container ol.amshopby-items').find("li").length) {
                                    jQuery(".block-actions.filter-actions").hide();   
                                    jQuery(".filter-options-item").find(".filter-options-content:visible").hide();
                                    jQuery(".filter-options-item").removeClass("active");
                                    setFilter("firstAttemptedFilter", "");
                                }           
                                var availableFilters = getFilterValues();                
                               
                                // jQuery("#product-wrapper.products.wrapper.grid.products-grid ol.products.list.items.product-items").empty();
                                currentPage = 1;
                                stopAjax = 0;
                                callFilterApi(availableFilters, currentPage);
                        }
                        else{

                         if(jQuery(document).find("#narrow-by-list.filter-options .highlight-check[data-info=\""+text+"\"]").attr('data-child') == 1){

                            jQuery(document).find("#narrow-by-list.filter-options .highlight-check[data-info=\""+text+"\"]").siblings('.sub-catgory').find('.sub-check').prop("checked", false);
                            }


                            jQuery(document).find("#narrow-by-list.filter-options .highlight-check[data-info=\""+text+"\"]").prop("checked", false); 

                            jQuery(document).find("#narrow-by-list.filter-options .sub-check[data-info=\""+text+"\"]").prop("checked", false);              
                            
                            jQuery(this).parent("li.item.amshopby-item").remove();
                            if (!jQuery('#am-shopby-container ol.amshopby-items').find("li").length) {
                                jQuery(".block-actions.filter-actions").hide();   
                                jQuery(".filter-options-item").find(".filter-options-content:visible").hide();
                                jQuery(".filter-options-item").removeClass("active");
                                setFilter("firstAttemptedFilter", "");
                            }
                            removeFiltersFromBreadCrumbRequest(filterType, filterValue);
                        }   
                    }
                }, ".amshopby-remove");

                 
           
        /*price filter for desktop 24-08-2023*/
            /*remove price filter from url*/
            function removePriceFilter() {
                var availableFilters = getFilterValues();
                console.log('removefilter');
                jQuery.each(availableFilters, function(filterType, filterValues) {
                    if (filterType == "firstAttemptedFilter" && filterValues[0] == "priceFilter") {
                        setFilter("firstAttemptedFilter", "");
                    }
                });
                if (window.innerWidth > 679) {
                    setFilter("priceFilter", "");
                    jQuery(".sort-item-list").find("li[data-filter='priceFilter']").remove();
                } else {
                    if (appliedMobileFilters.priceFilter) {
                        delete appliedMobileFilters.priceFilter;    
                    }
                }
            }
        

        });
        // price filer changes 24-08-2023
        /*function to get applied filters from url for mobile and desktop*/
        function getFilterValues() {
            var appliedFilters = {};

            if (history.pushState) {
                var params = new URLSearchParams(window.location.search);   
                params.forEach(function(filterValues,filter) {
                    if (filter == "sorting") {
                        appliedFilters[filter] = filterValues;    
                    } else if (filter == "q") {
                        return true;
                    } else {
                        appliedFilters[filter] = splitValues(filterValues);    
                    }
                });
                return appliedFilters;
            }
        }

       
    
    
     function setFiltersOnHtml(onLoadRequest) {
        
        var availableFilters = getFilterValues();
       
       
        showElementSelected(availableFilters, 1);   
                    
    }  
    function removeFiltersFromBreadCrumbRequest(filterType, filterValue) {
        var params = new URLSearchParams(window.location.search);   
        params.forEach(function(filterValues,filter) {
            if (filter == filterType) {                
                var filteredValues = filterValues.split('+').filter(function(value) {                    
                    return value != filterValue;                    
                });
                applyFilters(filterType, filteredValues.join("+"), 1);    
            } else if (filter == "q") {
                return true;
            }
        });
    }
    function applyFilters(filterType, filters, breadCrumbRequest = null) {          
        jQuery('#amasty-shopby-overlay').show();
        setFilter(filterType,filters);
        // var availableFilters = getFilterValues();
        // if (breadCrumbRequest != 1) {
        //     checkAndRemoveFirstAttemptedFilter(availableFilters);
        // }   
        var availableFilters = getFilterValues();
        appliedMobileFilters = availableFilters;
        if (!availableFilters.categoryFilter) {
            availableFilters.categoryFilter = '';
        }                 
        //console.log("------------apply filter-------------");             
        //jQuery(".products.list.items.product-items").empty();
        jQuery('#currentPage').val(1);
        jQuery('#current_page').val(1);
        
        jQuery('#stopAjax').val(0);
        callFilterApi(availableFilters, 1);
        jQuery(document).scrollTop(0);
    }


        function setMobileFilters(filterType, filterValues) {                  
        appliedMobileFilters.firstAttemptedFilter = [filterType];
        if (filterValues.length < 1) {            
            delete appliedMobileFilters[filterType];
        } else {            
            appliedMobileFilters[filterType] = filterValues;
        }        
        // jQuery(".product-listing").empty();
        jQuery('#currentPage').val(1);
        jQuery('#stopAjax').val(0);
        currentPage = 1;
        stopAjax = 0;

              
        callFilterApi(appliedMobileFilters, currentPage);
    }


     function setFilter(key,value) {
        if (history.pushState) {
            var params = new URLSearchParams(window.location.search);
            if (value != '') {
                params.set(key, value);    
            } else {
                params.delete(key);
            }
            var newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?' + params.toString();
            window.history.pushState({path:newUrl},'',newUrl);
        }
    }


    function callFilterApi(availableFilters, currentPage, onLoadRequest = null, onScrollRequest = null) {                            
        // if (window.innerWidth < 679 && onLoadRequest != 1) {
        //     if (!availableFilters.categoryFilter) {
        //         availableFilters.categoryFilter = '';
        //     }
        // }

        var requestedData = {
            pageNo : currentPage,
            currentCategoryId : currentCategoryId,
            mediaUrl : mediaUrl,
            isAjax : 1,
            filterToApply : availableFilters
        };

        if (onScrollRequest != null) {
            requestedData.isOnScrollRequest = 1;
        }

        // if (jQuery("#is_searched_activity").val() == 0) {
        //     requestedData.countryCode = jQuery("#hidden-country-code").val();
        // }

        getProductDataOnPage(requestedData, availableFilters, onLoadRequest, onScrollRequest);
    }

    function isOnScreen(elem) {        
        if( elem.length == 0 ) {
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



    function getFilterValues() {
        var appliedFilters = {};

        if (history.pushState) {
            var params = new URLSearchParams(window.location.search);   
            params.forEach(function(filterValues,filter) {
                if (filter == "sorting") {
                    appliedFilters[filter] = filterValues;    
                } else if (filter == "q") {
                    return true;
                } else {
                    appliedFilters[filter] = splitValues(filterValues);    
                }
            });
            return appliedFilters;
        }
    }


    function callFilterApi(availableFilters, currentPage, onLoadRequest = null, onScrollRequest = null, mob_cl_req = null) {    
        console.log(availableFilters);                        
        var requestedData = {
            p_no : currentPage,
            c_id : jQuery('#cat-id').val(),
            c_level : jQuery('#cat-level').val(),
            c_has_child : jQuery('#cat-has-child').val(),
            c_name : jQuery('#cat-name').val(),            
            c_par_id : jQuery('#cat-pare-id').val(),
            filt_to_apply : availableFilters,
            cat_url_key : jQuery('#cat-url-key').val(),
            cat_url_path : jQuery('#cat-url-path').val(),
            cat_path : jQuery('#cat-path').val(),
        };

        if (onScrollRequest != null) {
            requestedData.scroll_req = 1;
        }

        getProductDataOnPage(requestedData, availableFilters, onLoadRequest, onScrollRequest, mob_cl_req);
    }


    var dataContainer = document.getElementById('data-container');

    if (dataContainer) {
        var plpListingUrl = dataContainer.getAttribute('data-plp-listing-url');
        var searchPageRequestUrl = dataContainer.getAttribute('data-search-pagerequest-url');
        var blockCurrency = dataContainer.getAttribute('data-block-currency');
        var catId = dataContainer.getAttribute('data-cat-id');

        console.log('PLP Debug:', plpListingUrl);
        console.log('PLP Debug:', blockCurrency);
        console.log('PLP Debug:', searchPageRequestUrl);
        console.log('PLP Debug:', catId);
    }

    function getProductDataOnPage(requestedData,appliedFilters,onLoadRequest = null, onScrollRequest= null, mob_cl_req = null) {
        console.log('product data on page---');
        var pageNo = typeof requestedData.p_no != 'undefined' ? requestedData.p_no : '';
        if (jQuery("#is_searched_activity").val() == 0) {  
             
            jQuery.ajax({
                type : "GET",
                url : plpListingUrl,
                data : {is_ajax : 1, req_data : requestedData, is_search : 0},
                dataType : "json",    
                beforeSend : function() {
                    //$('#mgs-ajax-loading').show();   
                    jQuery("#isRequestSent").val(1);                 
                    if (Number(jQuery("#no-of-pages").val()) == 1 && onScrollRequest == 1) {  
                        jQuery("#stopAjax").val(1);                      
                        return false;
                    }
                    //jQuery('#amasty-shopby-overlay').show();
                },
                success : function(response) {
                  // $('#mgs-ajax-loading').hide();
                    if (response.error == 0) {
                        /* handle product grid */
                        if (response.no_of_pages > 0) {
                            
                            if(pageNo == 1) {
                                console.log("-------------replace----html-----");
                                jQuery('#product-wrapper.products.wrapper.grid.products-grid ol.products.list.items.product-items').html(response.prod_grid_html);
                            } else {
                                console.log("--------------append---html-----");
                            jQuery('#product-wrapper.products.wrapper.grid.products-grid ol.products.list.items.product-items').append(response.prod_grid_html);
                            }
                           
                          
                            jQuery('#ol_newly_added_products_in_productGrid').html(response.prod_grid_html);
                            jQuery("#no-of-pages").val(response.no_of_pages);
                            var i = 0;
                            var items = [];
                            var itemArray = [];
                            var itemlistschemaArr = [];
                            jQuery("#ol_newly_added_products_in_productGrid .item.product.product-item-info.product-item").each(function() {
                                var dataSku = jQuery(this).data('sku');
                                var dataShortDesc = jQuery(this).data('shortdesc');
                                var dataBrand = jQuery(this).data('brand');
                                var dataCategory = jQuery(this).data('category');
                                var dataValue = jQuery(this).data('value');
                    
                                var item = {
                                    dataSku: dataSku,
                                    dataShortDesc: dataShortDesc,
                                    dataBrand: dataBrand,
                                    dataCategory: dataCategory,
                                    dataValue: dataValue
                                }

                                items.push(item);
                            });
                            console.log(items);
                            items.forEach(function(item){
                                var arr = {
                                    item_id :  item.dataSku,
                                    item_name : item.dataShortDesc,
                                    affiliation : "",
                                    coupon : "",
                                    discount : "",
                                    index : i,
                                    item_brand : item.dataBrand,
                                    item_category : item.dataCategory,
                                    item_variant : "",
                                    price : item.dataValue,
                                    quantity : 1
                                }
                                itemArray.push(arr);
                                var itemlistschema={
                                    '@type':'ListItem',
                                    'position': i,
                                    'url': item.dataUrl,
                                    'name': item.dataBrand,
                                    'description': item.dataShortDesc
                                }
                                itemlistschemaArr.push(itemlistschema);
                                i++;
                            });
                            //gtag("event", "view_item_list", {
                                //currency: "<?php //echo $this->getLayout()->createBlock('\Magento\Directory\Block\Currency')->getCurrentCurrencyCode(); ?>",
                                //items : itemArray
                            //});
                           // itemlist for further 12 product
                            var el = document.createElement('script');
                            el.type = 'application/ld+json';

                            el.text = JSON.stringify({
                                   "@context": "https://schema.org",
                                    "@type": "ItemList",
                                    "itemListElement": itemlistschemaArr
                                });
                            document.getElementsByTagName('head')[0].appendChild(el);
                        } else {
                            if(pageNo == 1) {
                                console.log("-------------replace----html-----");
                                jQuery("#product-wrapper.products.wrapper.grid.products-grid ol.products.list.items.product-items").html("<p>There are no products matching the Selection</p>");
                            } else {
                                console.log("--------------append---html-----");
                            jQuery("#product-wrapper.products.wrapper.grid.products-grid ol.products.list.items.product-items").append("<p>There are no products matching the Selection</p>");
                            }
                            
                            jQuery("#stopAjax").val(1);     
                           
                        }

                        /* handle filter sections */                        
                        if (response.hasOwnProperty("filt_facets") && Object.keys(response.filt_facets).length > 0) {                                                                                   
                            showAvailableFilterSections(response.filt_facets, appliedFilters, mob_cl_req);
                            showElementSelected(appliedFilters, onLoadRequest);                                                        
                        }                        
                    } else {
                        
                         if(pageNo == 1) {
                                console.log("-------------replace----html-----");
                                jQuery("#product-wrapper.products.wrapper.grid.products-grid ol.products.list.items.product-items").html("<p>"+response.msg+"</p>");

                            } else {
                                console.log("--------------append---html-----");
                        jQuery("#product-wrapper.products.wrapper.grid.products-grid ol.products.list.items.product-items").append("<p>"+response.msg+"</p>");

                            }
                        jQuery("#no-of-pages").val(0);
                        jQuery("#stopAjax").val(1);     
                    }                                                      
                },
                complete : function() {

                    if (Number(jQuery("#no-of-pages").val()) == Number(jQuery('current_page').val())) {                        
                        jQuery("#stopAjax").val(1);                          
                    }
                    jQuery("#isRequestSent").val(0); 
                    jQuery('#amasty-shopby-overlay').hide();
                    jQuery('#mgs-ajax-loading').hide();
                }
            });
        } else {
            var searchedText = jQuery("#search").val();
            console.log("search text"+searchedText);
            jQuery.ajax({
                type : "GET",
                url : searchPageRequestUrl,
                data : {is_ajax : 1, req_data : requestedData, is_search : 1,q:searchedText},
                dataType : "json",    
                beforeSend : function() {   
                    jQuery("#isRequestSent").val(1);                 
                    if (Number(jQuery("#no-of-pages").val()) == 1 && onScrollRequest == 1) {  
                        jQuery("#stopAjax").val(1);                      
                        return false;
                    }
                    jQuery('#amasty-shopby-overlay').show();
                },
                success : function(response) {
                    var pageNo = typeof requestedData.p_no != 'undefined' ? requestedData.p_no : '';
                    if (response.error == 0) {
                        /* handle product grid */
                        if (response.no_of_pages > 0) {

                            
                            
                            if(pageNo == 1) {
                                jQuery('#product-wrapper.products.wrapper.grid.products-grid ol.products.list.items.product-items').html(response.prod_grid_html);
                            } else {
                            jQuery('#product-wrapper.products.wrapper.grid.products-grid ol.products.list.items.product-items').append(response.prod_grid_html);
                            }

                            
                            

                            jQuery('#ol_newly_added_products_in_productGrid').html(response.prod_grid_html);
                            jQuery("#no-of-pages").val(response.no_of_pages);
                            var i = 0;
                            var items = [];
                            var itemArray = [];
                            jQuery("#ol_newly_added_products_in_productGrid .item.product.product-item-info.product-item").each(function() {
                                var dataSku = jQuery(this).data('sku');
                                var dataShortDesc = jQuery(this).data('shortdesc');
                                var dataBrand = jQuery(this).data('brand');
                                var dataCategory = jQuery(this).data('category');
                                var dataValue = jQuery(this).data('value');
                    
                                var item = {
                                    dataSku: dataSku,
                                    dataShortDesc: dataShortDesc,
                                    dataBrand: dataBrand,
                                    dataCategory: dataCategory,
                                    dataValue: dataValue
                                }

                                items.push(item);
                            });
                            console.log(items);
                            items.forEach(function(item){
                                var arr = {
                                    item_id :  item.dataSku,
                                    item_name : item.dataShortDesc,
                                    affiliation : "",
                                    coupon : "",
                                    discount : "",
                                    index : i,
                                    item_brand : item.dataBrand,
                                    item_category : item.dataCategory,
                                    item_variant : "",
                                    price : item.dataValue,
                                    quantity : 1
                                }
                                itemArray.push(arr);
                                i++;
                            });
                            gtag("event", "view_item_list", {
                                currency: blockCurrency,
                                items : itemArray
                            });
                        } else {

                            if(pageNo == 1) {
                                jQuery("#product-wrapper.products.wrapper.grid.products-grid ol.products.list.items.product-items").html("<p>There are no products matching the Selection</p>");
                            } else {
                            jQuery("#product-wrapper.products.wrapper.grid.products-grid ol.products.list.items.product-items").append("<p>There are no products matching the Selection</p>");
                            }
                           
                            jQuery("#stopAjax").val(1);     
                           
                        }

                        /* handle filter sections */                        
                        if (response.hasOwnProperty("filt_facets") && Object.keys(response.filt_facets).length > 0) {                                                                                   
                            showAvailableFilterSections(response.filt_facets, appliedFilters, mob_cl_req);
                            showElementSelected(appliedFilters, onLoadRequest);                                                        
                        }                        
                    } else {

                         if(pageNo == 1) {
                               jQuery("#product-wrapper.products.wrapper.grid.products-grid ol.products.list.items.product-items").html("<p>"+response.msg+"</p>");
                            } else {
                        jQuery("#product-wrapper.products.wrapper.grid.products-grid ol.products.list.items.product-items").append("<p>"+response.msg+"</p>");
                            }
                        
                        jQuery("#no-of-pages").val(0);
                        jQuery("#stopAjax").val(1);     
                    }                                                      
                },
                complete : function() {
                   
                    if (Number(jQuery("#no-of-pages").val()) == Number(jQuery('current_page').val())) {                        
                        jQuery("#stopAjax").val(1);                          
                    }
                    jQuery("#isRequestSent").val(0); 
                    jQuery('#amasty-shopby-overlay').hide();
                    jQuery('#mgs-ajax-loading').hide();
                }
            });
        }
    }

    function splitValues(filterValues) {
        return filterValues.split("+");
    }


    function showElementSelected(availableFilters, onLoadRequest = null) {              
        if (availableFilters.categoryFilter) {
            availableFilters.categoryFilter.forEach(function(id) {
                /*show for desktop*/
                jQuery(".filter-header-category").find("input[value='"+id+"']").attr("checked", "checked");
                jQuery(".filter-header-category").find("input[value='"+id+"']").siblings(".items.sub-catgory").find(".sub-check").each(function(){
                    jQuery(this).prop("checked", true);
                })
                    jQuery(".filter-header-category").find(".filter-options-content").show();
                    jQuery(".filter-header-category").addClass("active");
                                    
                jQuery(".filter-plot .heading").find("input[value='"+id+"']").attr("checked", "checked");
                jQuery(".filter-plot .heading").find("input[value='"+id+"']").parents().siblings('.filters').find('.filter-scroll .filter-content .sub-check').each(function() {
                    jQuery(this).prop( "checked", true);
                })
                jQuery(".filter-header-category-mobile").find("input[value='"+id+"']").attr("checked", "checked");
            });
            
        } 

        // if (availableFilters.sorting) {            
        //     if(availableFilters.sorting == "new-arrival"){
        //       jQuery(".filter-sort .sort-text").text("New Arrival");
        //             jQuery(".filter-sort .sort-text").addClass("new-arrival-sort");
        //             jQuery(".filter-sort .sortByList li[data-value='new-arrival']").hide();   
        //     }
        // }

        if (availableFilters.designerFilter) {
            availableFilters.designerFilter.forEach(function(id) {
                /*show for desktop*/

                jQuery(".filter-header-designer").find("input[value='"+id+"']").attr("checked", "checked");
                jQuery(".filter-header-designer-mobile").find("input[value='"+id+"']").attr("checked", "checked");
                    jQuery(".filter-header-designer").find(".filter-options-content").show();
                    jQuery(".filter-header-designer").addClass("active");

                
            });
           
        }


         if (availableFilters.patternsFilter) {
            availableFilters.patternsFilter.forEach(function(id) {
                /*show for desktop*/

                jQuery(".filter-header-patterns").find("input[value='"+id+"']").attr("checked", "checked");
                jQuery(".filter-header-patterns-mobile").find("input[value='"+id+"']").attr("checked", "checked");
                    jQuery(".filter-header-patterns").find(".filter-options-content").show();
                    jQuery(".filter-header-patterns").addClass("active");

                
            });
           
        }

        if (availableFilters.occasionFilter) {
            availableFilters.occasionFilter.forEach(function(id) {
                /*show for desktop*/
                jQuery(".filter-header-occasion").find("input[value='"+id+"']").attr("checked", "checked");
                jQuery(".filter-header-occasion-mobile").find("input[value='"+id+"']").attr("checked", "checked");
                jQuery(".filter-header-occasion").find(".filter-options-content").show();
                jQuery(".filter-header-occasion").addClass("active");
            });
           
        } 
        if (availableFilters.genderFilter) {
            availableFilters.genderFilter.forEach(function(id) {
                /*show for desktop*/
                jQuery(".filter-header-gender").find("input[value='"+id+"']").attr("checked", "checked");
                jQuery(".filter-header-gender-mobile").find("input[value='"+id+"']").attr("checked", "checked");
                jQuery(".filter-header-gender").find(".filter-options-content").show();
                jQuery(".filter-header-gender").addClass("active");
            });
           
        }  
        if (availableFilters.kidFilter) {
            availableFilters.kidFilter.forEach(function(id) {
                /*show for desktop*/
                jQuery(".filter-header-kid").find("input[value='"+id+"']").attr("checked", "checked");
                jQuery(".filter-header-kid-mobile").find("input[value='"+id+"']").attr("checked", "checked");
                jQuery(".filter-header-kid").find(".filter-options-content").show();
                jQuery(".filter-header-kid").addClass("active"); 
            });
           
        } 
         if (availableFilters.tagsFilter) {
            availableFilters.tagsFilter.forEach(function(id) {
                /*show for desktop*/
                jQuery(".filter-header-tags").find("input[value='"+id+"']").attr("checked", "checked");
                jQuery(".filter-header-tags-mobile").find("input[value='"+id+"']").attr("checked", "checked");
                jQuery(".filter-header-tags").find(".filter-options-content").show();
                jQuery(".filter-header-tags").addClass("active");
            });
           
        }  

       

       

        if (availableFilters.colorFilter) {
            availableFilters.colorFilter.forEach(function(id) {
                /*show for desktop*/
                jQuery(".filter-header-color").find("input[value='"+id+"']").attr("checked", "checked");
                jQuery(".filter-header-color-mobile").find("input[value='"+id+"']").attr("checked", "checked");
                jQuery(".filter-header-color").find(".filter-options-content").show();
                jQuery(".filter-header-color").addClass("active"); 
            });
           
        } 
        if (availableFilters.bridalFilter) {
            availableFilters.bridalFilter.forEach(function(id) {
                /*show for desktop*/
                jQuery(".filter-header-bridal").find("input[value='"+id+"']").attr("checked", "checked");
                jQuery(".filter-header-bridal-mobile").find("input[value='"+id+"']").attr("checked", "checked");
                jQuery(".filter-header-bridal").find(".filter-options-content").show();
                jQuery(".filter-header-bridal").addClass("active");
            });
           
        } 


        if (availableFilters.sizeFilter) {
            availableFilters.sizeFilter.forEach(function(id) {
                /*show for desktop*/
                 /*show for desktop*/
                jQuery(".filter-header-size").find("input[value='"+id+"']").attr("checked", "checked");
                jQuery(".filter-header-size-mobile").find("input[value='"+id+"']").attr("checked", "checked");
                jQuery(".filter-header-size").find(".filter-options-content").show();
                jQuery(".filter-header-size").addClass("active"); 
            });
            
        } 

        if (availableFilters.acoeditFilter) {
            availableFilters.acoeditFilter.forEach(function(id) {
                /*show for desktop*/
                jQuery(".filter-header-a-co-edits").find("input[value='"+id+"']").attr("checked", "checked");
                jQuery(".filter-header-a-co-edits-mobile").find("input[value='"+id+"']").attr("checked", "checked");
                jQuery(".filter-header-a-co-edits").find(".filter-options-content").show();
                jQuery(".filter-header-a-co-edits").addClass("active"); 
            });
           
        } 

        if (availableFilters.deliveryFilter) {
            availableFilters.deliveryFilter.forEach(function(id) {
                /*show for desktop*/
                jQuery(".filter-header-delivery").find("input[value='"+id+"']").attr("checked", "checked");
                jQuery(".filter-header-delivery-mobile").find("input[value='"+id+"']").attr("checked", "checked");
                jQuery(".filter-header-delivery").find(".filter-options-content").show();
                jQuery(".filter-header-delivery").addClass("active"); 
            });
           
        }

        if (availableFilters.themeFilter) {
            availableFilters.themeFilter.forEach(function(id) {
                /*show for desktop*/
                jQuery(".filter-header-theme").find("input[value='"+id+"']").attr("checked", "checked");
                jQuery(".filter-header-theme-mobile").find("input[value='"+id+"']").attr("checked", "checked");
                jQuery(".filter-header-theme").find(".filter-options-content").show();
                jQuery(".filter-header-theme").addClass("active"); 
            });
           
        }

        // price filter
        if (availableFilters.priceFilter) {            
            // priceRange = availableFilters.priceFilter;
            /*for desktop*/
            if (window.innerWidth > 679) {
             
                jQuery(".price-section").css("display","block");
                jQuery(".price-section").prev(".heading").addClass("active");    
            } else {
                /*for mobile*/
                // jQuery("#slider-snap-value-lower-m").text(priceRange[0]);   
                // jQuery("#slider-snap-value-upper-m").text(priceRange[1]);
                // jQuery("#price-min-val-m").val(priceRange[0]);
                // jQuery("#price-max-val-m").val(priceRange[1]);    
            }
            // snapSlider.noUiSlider.set([priceRange[0], priceRange[1]]);
        }



        console.log("onload request::",onLoadRequest);

        

       

        
        
        if (onLoadRequest == 1) {
            showFiltersBreadCrumbsOnLoad(availableFilters);        
        }
    }


    /*function to show applied filters breadcrumbs on page load*/
    function showFiltersBreadCrumbsOnLoad(availableFilters) {
        if (availableFilters != null) {

            console.log("----availableFilters----",availableFilters);
            jQuery.each(availableFilters, function(filterType, filterValues) {            
                
                    if (jQuery(document).find("li[data-filter='"+filterType+"']").length && filterType != "priceFilter") {
                        jQuery(document).find("li[data-filter='"+filterType+"']").each(function() {
                            var element = jQuery(this);
                            filterValues.forEach(function(value) {
                                console.log("values-----",value);
                                if (element.find(".highlight-check[value='"+value+"']").length) {
                                    var text = element.find(".highlight-check[value='"+value+"']").attr("data-info");


                                    jQuery('#am-shopby-container.amshopby-filter-current.filter-current ol.amshopby-items.items').append('<li class="item amshopby-item" data-am-js="shopby-item" data-container="'+filterType+'Filter" data-info="'+text+'" data-value="'+value+'"><a class="amshopby-remove" href="javascript:void(0)" title="Remove '+filterType+' '+text+'"></a><span class="amshopby-filter-name">'+filterType.replace("Filter", "")+'                    </span><div class="amshopby-filter-value">'+text+'                    </div></li>');

                                               
                                } 



                                                          
                            });
                        });    

                        jQuery(document).find("li.child-li[data-filter='"+filterType+"']").each(function() {
                            var element = jQuery(this);
                            filterValues.forEach(function(value) {
                                console.log("values-----",value);
                                if (element.find(".sub-check[value='"+value+"']").length) {
                                    var text = element.find(".sub-check[value='"+value+"']").attr("data-info");


                                    jQuery('#am-shopby-container.amshopby-filter-current.filter-current ol.amshopby-items.items').append('<li class="item amshopby-item" data-am-js="shopby-item" data-container="'+filterType+'" data-info="'+text+'" data-value="'+value+'"><a class="amshopby-remove" href="javascript:void(0)" title="Remove '+filterType.replace("Filter", "")+' '+text+'"></a><span class="amshopby-filter-name">'+filterType.replace("Filter", "")+'                    </span><div class="amshopby-filter-value">'+text+'                    </div></li>');

                                               
                                } 



                                                          
                            });
                        });  
                        jQuery(".block-actions.filter-actions").show();
                       
                    }else if (filterType == "priceFilter") {
                        minPrice = filterValues[0];
                        maxPrice = filterValues[1]; 
                        console.log(minPrice+'onload1');                   
                        console.log(maxPrice+'onload2');                   
                        var text = minPrice+'-'+maxPrice;
                        jQuery('#am-shopby-container.amshopby-filter-current.filter-current ol.amshopby-items.items').append('<li class="item amshopby-item" data-am-js="shopby-item" data-container="'+filterType+'" data-info="'+text+'" data-value="'+text+'"><a class="amshopby-remove" href="javascript:void(0)" title="Remove '+filterType.replace("Filter", "")+' '+text+'"></a><span class="amshopby-filter-name">'+filterType.replace("Filter", "")+'                    </span><div class="amshopby-filter-value">'+text+'                    </div></li>');
                        jQuery(".block-actions.filter-actions").show();
                        
                        
                    }
                 
            });

            
        }
        
    }

    function showAvailableFilterSections(filteredSection, appliedFiltersJson = null, mob_cl_req = null) {    
        console.log("filteredSection",filteredSection); 
        console.log("appliedFiltersJson",appliedFiltersJson);                        
        var sections = filteredSection;        
        var appliedFilterType = '';        
         
        if (appliedFiltersJson.hasOwnProperty("firstAttemptedFilter")) {
            var appliedFilterType = appliedFiltersJson.firstAttemptedFilter[0];            
        }

        /*for Desktop*/ 
            console.log("applied filter type",appliedFilterType);                            
            if (appliedFilterType != '') {                       
                jQuery("#narrow-by-list.filter-options").find(".filter-options-item").not(".filter-options-item[data-info='"+appliedFilterType+"']").hide();  
                //hidding after 3 filter applies
                jQuery(".mobile-filter-block").find(".select-category").not(".select-category[data-info='"+appliedFilterType+"']").hide(); 
            } else {      
                jQuery(".filter-options-item").hide();
                jQuery(".filter-options-item").removeClass("active"); 
                jQuery(".select-category").hide();
                jQuery(".select-category").removeClass("active");     
            }
           
                   
            if (appliedFilterType != "categoryFilter") {                
                if (sections.hasOwnProperty("categories") && Object.keys(sections.categories).length > 0) {                      
                    jQuery(".filter-header-category").show();                                         
                    jQuery(".filter-header-category-mobile-left").show();                                       
                    developFilterSections("category", "categoryFilter", sections.categories,sections.child_categories);
                } else {
                    jQuery(".filter-header-category").hide();    
                    jQuery(".filter-header-category-mobile-left").hide();
                }    
            }               
            
            if (appliedFilterType != "designerFilter") {
                if (sections.hasOwnProperty("designers") && Object.keys(sections.designers).length > 1) {                
                    jQuery(".filter-header-designer").show();   
                    jQuery(".filter-header-designer-mobile-left").show();  
                    developFilterSections("designer", "designerFilter", sections.designers);
                } else {                
                    jQuery(".filter-header-designer").hide();  
                    jQuery(".filter-header-designer-mobile-left").hide();  

                }    
            }


            if (appliedFilterType != "patternsFilter") {
                if(!(sections.hasOwnProperty("categories") && Object.keys(sections.categories).length > 0)){
                if (sections.hasOwnProperty("patterns") && Object.keys(sections.patterns).length > 0) {                
                    jQuery(".filter-header-patterns").show();   
                    jQuery(".filter-header-patterns-mobile-left").show();  
                    developFilterSections("patterns", "patternsFilter", sections.patterns);
                } else {                
                    jQuery(".filter-header-patterns").hide();  
                    jQuery(".filter-header-patterns-mobile-left").hide();  

                }    
            }

            }

            if (appliedFilterType != "genderFilter") {
                if (sections.hasOwnProperty("genders") && Object.keys(sections.genders).length > 0) {                
                    jQuery(".filter-header-gender").show();
                    jQuery(".filter-header-gender-mobile-left").show();
                    developFilterSections("gender", "genderFilter", sections.genders); 
                } else {   
                    console.log("===hide gender filter===");             
                   jQuery(".filter-header-gender").hide();    
                   jQuery(".filter-header-gender-mobile-left").hide();

                }    
            }

            if (appliedFilterType != "tagsFilter") {
                if (sections.hasOwnProperty("tags") && Object.keys(sections.tags).length > 0) {                
                    jQuery(".filter-header-tags").show();
                    jQuery(".filter-header-tags-mobile-left").show();
                    developFilterSections("tags", "tagsFilter", sections.tags); 
                } else {   
                    console.log("===hide tags filter===");             
                   jQuery(".filter-header-tags").hide();    
                   jQuery(".filter-header-tags-mobile-left").hide(); 
                }    
            }

            if (appliedFilterType != "occasionFilter") {
                if (sections.hasOwnProperty("occasions") && Object.keys(sections.occasions).length > 0) {                
                    jQuery(".filter-header-occasion").show();
                    jQuery(".filter-header-occasion-mobile-left").show();
                    developFilterSections("occasion", "occasionFilter", sections.occasions); 
                } else {   
                    console.log("===hide occasion filter===");             
                   jQuery(".filter-header-occasion").hide();    
                   jQuery(".filter-header-occasion-mobile-left").hide();

                }    
            }
            if (appliedFilterType != "bridalFilter") {
                if (sections.hasOwnProperty("bridal") && Object.keys(sections.bridal).length > 0) {                
                    jQuery(".filter-header-bridal").show();
                    jQuery(".filter-header-bridal-mobile-left").show();
                    developFilterSections("bridal", "bridalFilter", sections.bridal); 
                } 
                // else {   
                //     console.log("===hide bridal filter===");             
                //    jQuery(".filter-header-bridal").hide();    
                // }    
            }
            
            


            if (appliedFilterType != "sizeFilter") {                
                if (sections.hasOwnProperty("sizes") && Object.keys(sections.sizes).length > 0) {                
                    jQuery(".filter-header-size").show();
                    jQuery(".filter-header-size-mobile-left").show();
                    developFilterSections("size", "sizeFilter", sections.sizes);   
                } else {                
                    jQuery(".filter-header-size").hide();
                    jQuery(".filter-header-size-mobile-left").hide();
                }   
            }
             
           
            
            if (appliedFilterType != "colorFilter") {
                if (sections.hasOwnProperty("colors") && Object.keys(sections.colors).length > 0) {                
                    jQuery(".filter-header-color").show();
                    jQuery(".filter-header-color-mobile-left").show();
                    developFilterSections("color", "colorFilter", sections.colors);                      
                } else {                
                    jQuery(".filter-header-color").hide();       
                    jQuery(".filter-header-color-mobile-left").hide();       
                }    
            } 


            if (appliedFilterType != "kidFilter") {
                if (sections.hasOwnProperty("kids") && Object.keys(sections.kids).length > 0) {                
                    jQuery(".filter-header-kid").show();
                    jQuery(".filter-header-kids-mobile-left").show();
                    developFilterSections("kid", "kidFilter", sections.kids);                      
                } else {                
                    jQuery(".filter-header-kid").hide();       
                    jQuery(".filter-header-kids-mobile-left").hide();     
                }    
            } 

            if (appliedFilterType != "deliveryFilter") {
                if (sections.hasOwnProperty("delivery_times") && Object.keys(sections.delivery_times).length > 0) {                
                    jQuery(".filter-header-delivery").show();
                    jQuery(".filter-header-delivery-mobile-left").show();
                    developFilterSections("delivery", "deliveryFilter", sections.delivery_times);                      
                } else {                
                    jQuery(".filter-header-delivery").hide();
                    jQuery(".filter-header-delivery-mobile-left").hide();    
                }    
            }

            if (appliedFilterType != "themeFilter") {                
                if (sections.hasOwnProperty("themes") && Object.keys(sections.themes).length > 0) {                
                    jQuery(".filter-header-theme").show();
                    jQuery(".filter-header-theme-mobile-left").show();
                    developFilterSections("theme", "themeFilter", sections.themes);   
                } else {                
                    jQuery(".filter-header-theme").hide();
                    jQuery(".filter-header-theme-mobile-left").hide();
                }   
            }

            // price filter
            if (sections.hasOwnProperty("min_price") && sections.hasOwnProperty("max_price")) {                            
                if (Number(sections.min_price)  == Number(sections.max_price)) {   console.log("--------------price filter hide-----");          
                    //jQuery(".filter-header-price").hide();
                }  else {
                    jQuery(".filter-header-price").show();
                    jQuery(".filter-header-price-mobile-left").show();
                    if (appliedFilterType != "priceFilter") {                        
                        var lowestPrice = parseInt(sections.min_price);
                        var highestPrice = parseInt(sections.max_price);                
                        var priceRange = {
                            lowestPrice : lowestPrice,
                            highestPrice : highestPrice,
                        }
                        console.log(typeof(lowestPrice));
                        console.log(typeof(highestPrice));
                        jQuery("#min-price").val(lowestPrice);
                        jQuery("#price-min-val").val(lowestPrice);
                        jQuery("#price-min-val-m").val(lowestPrice);//for mobile
                        jQuery("#slider-snap-value-lower").text(lowestPrice);
                        jQuery("#slider-snap-value-lower-m").text(lowestPrice); //for mobile 

                        jQuery("#max-price").val(highestPrice);
                        jQuery("#price-max-val").val(highestPrice);
                        jQuery("#price-max-val-m").val(highestPrice);//for mobile
                        jQuery("#slider-snap-value-upper").text(highestPrice);
                        jQuery("#slider-snap-value-upper-m").text(highestPrice);//for mobile 
                        snapSlider.noUiSlider.updateOptions({
                            start: [lowestPrice, highestPrice],
                            range: {
                              min: lowestPrice,
                              max: highestPrice
                            }
                        }, false);     

                        //for mobile
                        snapSliderMobile.noUiSlider.updateOptions({
                            start: [lowestPrice, highestPrice],
                            range: {
                              min: lowestPrice,
                              max: highestPrice
                            }
                        }, false);              
                    }
                }
            } 



            if (appliedFilterType != "acoeditFilter") {
                if (sections.hasOwnProperty("a_co_edit") && Object.keys(sections.a_co_edit).length > 0) {    
                                
                    jQuery(".filter-header-a-co-edits").show();
                    jQuery(".filter-header-a-co-edits-mobile-left").show();
                    developFilterSections("a-co-edits", "acoeditFilter", sections.a_co_edit);                      
                } else {                
                    jQuery(".filter-header-a-co-edits").hide();
                    jQuery(".filter-header-a-co-edits-mobile-left").hide();              
                }    
            }

            // price filter 28-08-2023
            if (appliedFilterType != '') {                       
                if (appliedFilterType != "priceFilter") {                    
                   // jQuery(".filter-block").find(".filter-section").find("label").not("label[data-value='"+appliedFilterType+"']").parents(".filter-section").hide();             
                        }  
               // } else {                                                                       
                 //   jQuery(".filter-block").find(".filter-section").hide().find(".heading").removeClass("active"); 
                }

            /*for Mobile*/
        if (window.innerWidth < 679) {
            if (appliedFilterType != '') {
                if (appliedFilterType != "priceFilter" && appliedFilterType != "shipsInFilter") {
                   // jQuery(".mobile-filter-block").find(".filter-data-block").find("label").not("label[data-filter='"+appliedFilterType+"']").parents(".filter-data").removeClass("active");                
                }
            } else {                
               // jQuery(".mobile-filter-block").find(".filter-data-block").find(".filter-data").removeClass("active");
            }

        }                             
                      

        
    }

     /* sort json by value */
    function sortByValue(jsObj) {
        var sortedArray = [];        
        for(var i in jsObj) {
            sortedArray.push([jsObj[i], i]);
        }
        return sortedArray.sort();
    }

     function developFilterSections(type = null, filt_type, filters = [],childfilters = []) {  
    
        console.log("developer filter section ",type);
       
        if(filt_type != 'sizeFilter' && filt_type != 'deliveryFilter'){
             var sort_res = sortByValue(filters);
        }else{
            var sortedArray = [];        
            for(var i in filters) {
                sortedArray.push([filters[i], i]);
            }
             var sort_res = sortedArray;
        }
                console.log(filters);
                console.log(sort_res);

                var dataContainer = document.getElementById('data-container');
                var getcatId =  dataContainer.getAttribute('data-cat-id');

                console.log('Category ID: ' +getcatId);

                // jwellery id on live 6023 on stage 4069
                if (type == 'category' && getcatId == 6023) {
                    console.log("Original array of arrays:", sort_res);

                    let desiredOrder = ["6028","6024","6050","6031","6085","6039","6064","6034","6047","6042","6037","6045","6079"];

                    let sortedItems = desiredOrder.map(key => {
                        let foundItem = sort_res.find(item => item[0] === key);
                        return foundItem ? [foundItem[0], foundItem[1]] : null;
                    }).filter(item => item !== null);

                    let remainingItems = sort_res.filter(item => !desiredOrder.includes(item[0]));

                    sort_res = [...sortedItems, ...remainingItems];

                    console.log("Sorted output array of arrays:", sort_res);
                }

                console.log(sort_res);
        

        jQuery("#narrow-by-list.filter-options").find(".filter-header-"+type).find(".filter-options-content .items").find('.item').remove();
        if(type == 'category'){
                jQuery("#tab-1 .all-filters").empty();
            }

            jQuery(".mobile-filter-block").find(".filter-header-"+type+"-mobile").find(".items").find(".filter-content").remove();

        


        var filt_html = '';
        var filt_html_mobile = '';
        var filt_html_mobile_label = '';

       
        sort_res.forEach(function(ele_arr) {
            var isChild = 0;
            var childHtml = '';
            var filt_html_mobile_child = '';
            
            if(type == 'category'){
                console.log('----developer category filter----');
                var childCatArr = childfilters[ele_arr[1]];
                console.log("typeof ::",typeof childCatArr);
                if(typeof childCatArr !== 'undefined'){
                    console.log("typeof ::inside");
                    isChild = 1;
                    filt_html_mobile_child +='<div class="filter-scroll">';

                    childHtml += '<ol class="items am-filter-items-attr_category_ids am-labels-folding sub-catgory" style="margin-left: 20px;display:none;">';
                  for (var key in childCatArr){
                    if (childCatArr.hasOwnProperty(key)) {
                        childHtml += '<li class="child-li item item-Sunglasses Lehengas" data-label="'+childCatArr[key]+'" data-value="category" data-filter="categoryFilter" style="border-bottom: none;padding: 0;"> <input class="sub-check" name="amshopby[cat][]" value="'+key+'" type="checkbox" data-info="'+childCatArr[key]+'" ><a class="am-filter-item-6315aa5a7f9f7 amshopby-filter-parent" data-am-js="filter-item-category-amShopbyFilterCategoryLabelsFolding" href="javascript:void(0)"><span class="label" style="font-size: 13px;">'+childCatArr[key]+'</span></a></li>';

                            filt_html_mobile_child += '<label class="filter-content" data-filter="' + filt_type + '">';
                            filt_html_mobile_child += '<input name="amshopby[cat][]" value="' + key + '" type="checkbox" class="sub-check highlight-check" data-info="' + childCatArr[key] + '">';
                            filt_html_mobile_child += '<div class="check-box"></div>';
                            filt_html_mobile_child += '<a class="text">' + childCatArr[key] + '</a>';
                            filt_html_mobile_child += '</label>';
                    }
                  }
                  childHtml += '</ol>';
                  filt_html_mobile_child +='</div>';
                }
                var plusminus = '';
                var showPlusSign = '';
                if(isChild == 1){
                    showPlusSign = '<div class="show-plus-sign"></div>';
                    plusminus = '<span class="plusminus"></span>';
                }
                filt_html += '<li class="item item-'+ele_arr[0]+'" data-label="'+ele_arr[0]+'"  data-value="'+type+'"> <input class="highlight-check" name="amshopby['+type+'][]" value="'+ele_arr[1]+'"  type="checkbox" data-info="'+ele_arr[0]+'" data-child="'+isChild+'">     <a class="am-filter-item-6315aa5a7f9f7                    amshopby-filter-parent"  href="javascript:void(0)"> <span class="label">'+ele_arr[0]+'</span>'+plusminus+'</a>'+childHtml+'</li>';

                    filt_html_mobile += '<div class="filter-plot">';
                    filt_html_mobile += '<div class="heading">';
                    filt_html_mobile += '<input type="checkbox" name="amshopby[cat][]" value="' + +ele_arr[1] + '" class="checkAll highlight-check" data-info="' + ele_arr[0] + '">';
                    filt_html_mobile += '<a href="#" class="menu-slot">' + ele_arr[0] + '</a>';
                    filt_html_mobile += showPlusSign;
                    filt_html_mobile += '</div>';
                    filt_html_mobile += '<div class="filters filter-height designer-scroll filter-header-category-mobile">';
                    filt_html_mobile += '<div class="search-form">';
                    filt_html_mobile += '<div class="input">';
                    filt_html_mobile += '<button class="btn"></button>';
                    filt_html_mobile += '<input type="text" name="" placeholder="Search Sub Category" class="sub-category-search-input">';
                    filt_html_mobile += '</div>';
                    filt_html_mobile += '</div>';
                    filt_html_mobile += filt_html_mobile_child;
                    filt_html_mobile += '</div>';
                    filt_html_mobile += '</div>';


                // console.log("---------child filter ----------",filt_html);

            }else{
                console.log("developer filter section inside---");
                 if(filt_type == 'sizeFilter' || filt_type == 'deliveryFilter'){
                     filt_html += '<li class="item item-'+ele_arr[0]+'" data-label="'+ele_arr[0]+'"  data-value="'+type+'"> <input class="highlight-check" name="amshopby['+type+'][]" value="'+ele_arr[0]+'"  type="checkbox" data-info="'+ele_arr[0]+'">     <a class="am-filter-item-6315aa5a7f9f7                    amshopby-filter-parent"  href="javascript:void(0)"> <span class="label">'+ele_arr[0]+'</span></a></li>';

                        filt_html_mobile_label += '<label class="filter-content" data-filter="' + filt_type + '">';
                        filt_html_mobile_label += '<input name="amshopby[' + type + '][]" value="' + ele_arr[0] + '" type="checkbox" class="highlight-check" data-info="' + ele_arr[0] + '">';
                        filt_html_mobile_label += '<div class="check-box"></div>';
                        filt_html_mobile_label += '<a class="text">' + ele_arr[0] + '</a>';
                        filt_html_mobile_label += '</label>';

                 }else{
                     filt_html += '<li class="item item-'+ele_arr[0]+'" data-label="'+ele_arr[0]+'"  data-value="'+type+'"> <input class="highlight-check" name="amshopby['+type+'][]" value="'+ele_arr[1]+'"  type="checkbox" data-info="'+ele_arr[0]+'">     <a class="am-filter-item-6315aa5a7f9f7                    amshopby-filter-parent"  href="javascript:void(0)"> <span class="label">'+ele_arr[0]+'</span></a></li>';
                     
                        filt_html_mobile_label += '<label class="filter-content" data-filter="' + filt_type + '">';
                        filt_html_mobile_label += '<input name="amshopby[' + type + '][]" value="' + ele_arr[1] + '" type="checkbox" class="highlight-check" data-info="' + ele_arr[0] + '">';
                        filt_html_mobile_label += '<div class="check-box"></div>';
                        filt_html_mobile_label += '<a class="text">' + ele_arr[0] + '</a>';
                        filt_html_mobile_label += '</label>';
                 }
               
                
                
            }


        });
        jQuery("#narrow-by-list.filter-options").find(".filter-header-"+type).find(".filter-options-content .items").append(filt_html);
        // Append mobile filter HTML to the mobile filter container
                   if(type == 'category'){
                        // //console.log('type '+type +' filt_html_mobile ' +filt_html_mobile);
                        jQuery("#tab-1 .all-filters").append(filt_html_mobile);
                    }

            jQuery(".mobile-filter-block").find(".filter-header-" + type + "-mobile").find(".items").append(filt_html_mobile_label);
        
        
    }


    function addAndRemoveBreadcrumbs(element) {
        if (jQuery(document).find(".highlight-check:checked").length > 0) {
            jQuery("#layered-filter-block .block-actions.filter-actions").show();
        } else {
            jQuery("#layered-filter-block .block-actions.filter-actions").hide();
        }
        console.log("add and remove breadcrum");
        var text = element.siblings("a.amshopby-filter-parent").find(".label").text();
        var filterType = element.parents("li").attr("data-value");
        var filterValue = element.val();
        console.log("text",filterType);
        if(filterType == 'category' && element.hasClass('sub-check')){
            if(element.is(":checked")){
                
                if(element.parents('.sub-catgory').find('.sub-check').length == element.parents('.sub-catgory').find('.sub-check:checked').length){

                    filterValue = element.parents('.sub-catgory').siblings('input.highlight-check').val();
                    jQuery('#am-shopby-container.amshopby-filter-current.filter-current ol.amshopby-items.items li[data-value="'+element.val()+'"]').remove(); 
                   
                    element.parents('.sub-catgory').find('.sub-check').each(function(){
                        jQuery('#am-shopby-container.amshopby-filter-current.filter-current ol.amshopby-items.items li[data-value="'+jQuery(this).val()+'"]').remove(); 
                    });


                     
                    text = element.parents('.sub-catgory').siblings('.amshopby-filter-parent').find('.label').text();
                    console.log("length-------",jQuery('#am-shopby-container.amshopby-filter-current.filter-current ol.amshopby-items.items li[data-value="'+filterValue+'"]').length);
                    if(jQuery('#am-shopby-container.amshopby-filter-current.filter-current ol.amshopby-items.items li[data-value="'+filterValue+'"]').length == 0){
                     jQuery('#am-shopby-container.amshopby-filter-current.filter-current ol.amshopby-items.items').append('<li class="item amshopby-item" data-am-js="shopby-item" data-container="'+filterType+'Filter" data-info="'+text+'" data-value="'+filterValue+'"><a class="amshopby-remove" href="javascript:void(0)" title="Remove '+filterType+' '+text+'"></a><span class="amshopby-filter-name">'+filterType+'                    </span><div class="amshopby-filter-value">'+text+'                    </div></li>');
                    }
                }else{
                    // text = element.parents("li.item").find(".label").text();
                    

                    jQuery('#am-shopby-container.amshopby-filter-current.filter-current ol.amshopby-items.items').append('<li class="item amshopby-item" data-am-js="shopby-item" data-container="'+filterType+'Filter" data-info="'+text+'" data-value="'+filterValue+'"><a class="amshopby-remove" href="javascript:void(0)" title="Remove '+filterType+' '+text+'"></a><span class="amshopby-filter-name">'+filterType+'                    </span><div class="amshopby-filter-value">'+text+'                    </div></li>');
                }
            }else{

                if(element.parents('.sub-catgory').find('.sub-check').length == element.parents('.sub-catgory').find('.sub-check:not(:checked)').length){
                    text = element.parents('.sub-catgory').siblings('.amshopby-filter-parent').find('.label').text();
                    
                    jQuery('#am-shopby-container.amshopby-filter-current.filter-current ol.amshopby-items.items li[data-info="'+text+'"]').remove(); 
                    
                     jQuery('#am-shopby-container.amshopby-filter-current.filter-current ol.amshopby-items.items li[data-value="'+element.val()+'"]').remove(); 
                }else{

                    jQuery('#am-shopby-container.amshopby-filter-current.filter-current ol.amshopby-items.items li[data-value="'+element.val()+'"]').remove(); 
                }

                
            }
                
        }else if(filterType == 'category'){ 
           console.log("---add and remove breadcrum -- 1--");
            if(element.attr("data-child") == '1'){
                console.log("---add and remove breadcrum -- 2--");
                element.siblings('.sub-catgory').find('.sub-check').each(function(){
                    console.log("------inside----2--",jQuery(this).val());
                    jQuery('#am-shopby-container.amshopby-filter-current.filter-current ol.amshopby-items.items li[data-value="'+jQuery(this).val()+'"]').remove(); 
                });
            }

            if(element.is(":checked")){
                jQuery('#am-shopby-container.amshopby-filter-current.filter-current ol.amshopby-items.items').append('<li class="item amshopby-item" data-am-js="shopby-item" data-container="'+filterType+'Filter" data-info="'+text+'" data-value="'+filterValue+'"><a class="amshopby-remove" href="javascript:void(0)" title="Remove '+filterType+' '+text+'"></a><span class="amshopby-filter-name">'+filterType+'                    </span><div class="amshopby-filter-value">'+text+'                    </div></li>');
                
            }else{
                jQuery('#am-shopby-container.amshopby-filter-current.filter-current ol.amshopby-items.items li[data-info="'+text+'"]').remove(); 
            }
        }else{
            console.log("---add and remove breadcrum -- else--");
            if(element.is(":checked")){
                console.log("inside if ");
                jQuery('#am-shopby-container.amshopby-filter-current.filter-current ol.amshopby-items.items').append('<li class="item amshopby-item" data-am-js="shopby-item" data-container="'+filterType+'Filter" data-info="'+text+'" data-value="'+filterValue+'"><a class="amshopby-remove" href="javascript:void(0)" title="Remove '+filterType+' '+text+'"></a><span class="amshopby-filter-name">'+filterType+'                    </span><div class="amshopby-filter-value">'+text+'                    </div></li>');
            } else {
                jQuery('#am-shopby-container.amshopby-filter-current.filter-current ol.amshopby-items.items li[data-info="'+text+'"]').remove(); 
            }  
        }
    }
// -----------------------------------mobile filter jquery-----------------

    // ---------------------html integrity jQuery----------------------------
            jQuery('.btn-sort-by').on('click', function(){
                jQuery('.sort-cat-content').fadeIn()
                jQuery('.sort-cat-block').fadeIn();
                jQuery('body').css({overflow: 'hidden'});
            });

            jQuery('.sort-cat-block').on('click', function(){
                jQuery('.sort-cat-content').fadeOut()
                jQuery('.sort-cat-block').fadeOut();
                jQuery('body').css({overflow: 'auto'});
            });


        jQuery('.left-content .filter-tab .select-category').click(function(){

            var tab_id = jQuery(this).attr('data-tab');
            ////console.log(tab_id);

            jQuery('.left-content .filter-tab .select-category').removeClass('active');
            jQuery(this).addClass('active');

            jQuery('.right-content .filter-data-block .filter-data').removeClass('active');
            jQuery('#'+tab_id).addClass('active');

            jQuery(".all-filters .filter-plot").show();
            jQuery(".search-input").val('');
            // show all lables on tab changes
            jQuery('.filter-option .filter-content').show();
        });

        

        jQuery(document).on("click", '.show-plus-sign', function(e) {
            // Check if the element does not have the class show-minus-sign
            if (!jQuery(this).hasClass('show-minus-sign')) {
                // Add the show-minus-sign class and active class to parent
                jQuery(this).addClass('show-minus-sign');
                jQuery(this).parent().addClass('active');
            } else {
                // Remove the show-minus-sign class and active class from parent
                jQuery(this).removeClass('show-minus-sign');
                jQuery(this).parent().removeClass('active');
            }
            // Stop the event from propagating further
            e.stopPropagation();
        });

        jQuery(document).on("click", '.show-plus-sign', function(e) {
            jQuery(this).closest('.filter-plot').find('.filters').toggleClass('showdrop');
        });



        jQuery(".btn-filter-by").click(function() {
            jQuery(".mobile-filter-block").show();
            jQuery('html,body').css({overflow:'hidden'});
        });

        jQuery(".cancel-btn").click(function() {
            // jQuery(".mobile-filter-block").hide();
            jQuery('html,body').css({overflow: 'auto'});
        });

        jQuery(".apply-btn").click(function() {
            // jQuery(".mobile-filter-block").hide();
            jQuery('html,body').css({overflow: 'auto'});
        });

        
    // ---------------------html integrity jQuery----------------------------
    // ---------------------noUiSlider for mobile filter start----

            var availableFiltersMobile = getFilterValues();
            var minimumPriceMobile = Math.floor(Number(jQuery("#min-price").val())); 
            var maximumPriceMobile = Math.floor(Number(jQuery("#max-price").val()));

            var minHiddenPriceMobile = Math.round(parseFloat(jQuery("#min-price").val()));
            var maxHiddenPriceMobile = Math.round(parseFloat(jQuery("#max-price").val()));

            var priceRangeMobile = availableFiltersMobile.priceFilter || [minimumPriceMobile, maximumPriceMobile];
            var minimumPriceForSlideMobile = Math.floor(Number(priceRangeMobile[0]));
            var maximumPriceForSlideMobile = Math.floor(Number(priceRangeMobile[1]));    

            var snapSliderMobile = document.getElementById('slider-snap-m');
            noUiSlider.create(snapSliderMobile, {
                start: [minimumPriceForSlideMobile, maximumPriceForSlideMobile],
                connect: true,
                range: {
                    'min': minimumPriceMobile,
                    'max': maximumPriceMobile
                }
            });

            var snapValuesMobile = [
                document.getElementById('slider-snap-value-lower-m'),
                document.getElementById('slider-snap-value-upper-m')
            ];
            var snapInputMobile = [
                document.getElementById('price-min-val-m'),
                document.getElementById('price-max-val-m')
            ];

            snapSliderMobile.noUiSlider.on('update', function(values, handle) {
                snapValuesMobile[handle].innerHTML = Math.floor(values[handle]);
                snapInputMobile[handle].value = Math.floor(values[handle]);
                jQuery(".product-value-one .value-lower").text(Math.floor(values[0]));
                jQuery(".product-value-two .value-upper").text(Math.floor(values[1]));
            });

            function filterDataMobile() {
                clearTimeout(filterTimerMobile); 
                filterTimerMobile = setTimeout(function () {
                    var minPriceMobile = Math.floor(parseFloat(minPriceInputMobile.value));
                    var maxPriceMobile = Math.floor(parseFloat(maxPriceInputMobile.value));
                    //console.log("AJAX call for filtering data for price range: " + minPriceMobile + " - " + maxPriceMobile);
                    jQuery('#mgs-ajax-loading').show();
                    // setFilter("firstAttemptedFilter", "priceFilter");
                    // applyFilters("priceFilter", minPriceMobile + '+' + maxPriceMobile);
                    setMobileFilters("priceFilter", [minPriceMobile, maxPriceMobile]);
                    var filterJson = {type:'priceFilter',action:true,min_price:minPriceMobile,max_price:maxPriceMobile};
                    filterHistory.push(filterJson);
                }, 300);
            }

            var filterTimerMobile;

            snapSliderMobile.noUiSlider.on('slide', function (values, handle) {
                var valueMobile = values[handle];
                if (handle) {
                    maxPriceInputMobile.value = Math.floor(valueMobile);
                } else {
                    minPriceInputMobile.value = Math.floor(valueMobile);
                }
                filterDataMobile(); 
            });

            snapSliderMobile.noUiSlider.on('change', function (values, handle) {
                var valueMobile = values[handle];
                if (handle) {
                    maxPriceInputMobile.value = Math.floor(valueMobile);
                } else {
                    minPriceInputMobile.value = Math.floor(valueMobile);
                }
                filterDataMobile(); 
            });

            var minPriceInputMobile = document.getElementById('price-min-val-m');
            var maxPriceInputMobile = document.getElementById('price-max-val-m');

            minPriceInputMobile.addEventListener('change', function () {
                var enteredValueMobile = Math.round(parseFloat(this.value));
                if (enteredValueMobile < minHiddenPriceMobile) {
                    this.value = minHiddenPriceMobile;
                } else if (enteredValueMobile > maxHiddenPriceMobile) {
                    this.value = maxHiddenPriceMobile;
                }
                snapSliderMobile.noUiSlider.set([Math.round(this.value), null]);
                filterDataMobile();
            });

            maxPriceInputMobile.addEventListener('change', function () {
                var enteredValueMobile = Math.round(parseFloat(this.value));
                if (enteredValueMobile > maxHiddenPriceMobile) {
                    this.value = maxHiddenPriceMobile;
                } else if (enteredValueMobile < minHiddenPriceMobile) {
                    this.value = minHiddenPriceMobile;
                }
                snapSliderMobile.noUiSlider.set([null, Math.round(this.value)]);
                filterDataMobile();
            });
            // giving issue for inputs
            // jQuery("#price-min-val-m, #price-max-val-m").on('input', function() {
            //     updateSliderFromInputsMobile();
            // });

            // function updateSliderFromInputsMobile() {
            //     var minPriceMobile = Math.floor(parseFloat(jQuery("#price-min-val-m").val()));
            //     var maxPriceMobile = Math.floor(parseFloat(jQuery("#price-max-val-m").val()));
            //     snapSliderMobile.noUiSlider.set([minPriceMobile, maxPriceMobile]);
            // }

            jQuery(".price-range").on('input', "#price-min-val-m, #price-max-val-m", function() {
                jQuery(".price-range").find(".range-error").hide();
            });


    // ---------------------noUiSlider for mobile filter end----
    // code for mobile view sorting and filters start
    jQuery('.sort-cat-block .select-sort-item').click(function() {
        var selectedRadioButton = $(this).find('.select-radio:checked');
        var selectedText = $(this).find('.text').text();

        var selectedValue = selectedRadioButton.data('value');

        
        if(selectedValue != undefined && selectedText!= undefined){
        //     //console.log('Selected value:', selectedValue);
        //     //console.log('Selected text:', selectedText);
            jQuery('#mgs-ajax-loading').show();
            applyFilters("sorting", selectedValue, null, 2);
            $('.sort-data .text-field').text(selectedText);
        }
        
        
    });
    // code for mobile view sorting and filters end


    // Function to handle click event on checkbox for mobile filter except category
   

    var filterHistory=[];
    jQuery(document).on('click','.mobile-filter-block .right-content .filter-data-block .filter-data:not("#tab-1") .filters .filter-content .highlight-check' , function() {
        var values = [];
        jQuery(this).parents('ol.items').find("input:checked").each(function() {
            values.push(jQuery(this).val());
        });
        var filterValue = jQuery(this).attr("data-info");
        var filterType = jQuery(this).parent(".filter-content").attr("data-filter");
        isFilterChange = 1;
        isMobileClearAllFiter = 0;
        setMobileFilters(filterType, values);
       
        var filterJson = {value:jQuery(this).val(),type:filterType,action:jQuery(this).prop('checked')};
        filterHistory.push(filterJson);
        
        console.log("history",filterHistory);
        // setFilter("firstAttemptedFilter", filterType);
        // applyFilters(filterType, values.join("+"), null, null, filterValue);
        jQuery('#mgs-ajax-loading').show();
    });


    // // Function to handle click event on main category checkbox
    jQuery(document).on('click','.mobile-filter-block .right-content .filter-data-block .filter-data#tab-1 .heading .highlight-check' , function() {
        //console.log("----0--------------");
            var filterJson = {value:jQuery(this).val(),type:'categoryFilter',action:jQuery(this).prop('checked')};
                filterHistory.push(filterJson);
            if(jQuery(this).attr("data-child") == '1'){
               
               if(jQuery(this).is(":checked")){
                 //console.log("----if--------------");
                   jQuery(jQuery(this).parent().siblings('.filters').find('.filter-scroll .filter-content .sub-check')).each(function(i)
                   {
                        //console.log("----checked--------------");
                        
                       jQuery(this).prop( "checked", true);
                       var filterJson = {value:jQuery(this).val(),type:'categoryFilter',action:jQuery(this).prop('checked')};
                        filterHistory.push(filterJson);
                   });
               }else{
                 //console.log("----else--------------");
                   jQuery(jQuery(this).parent().siblings('.filters').find('.filter-scroll .filter-content .sub-check')).each(function(i)
                   {
                     //console.log("--else--checked--------------");
                       jQuery(this).prop( "checked", false );
                        var filterJson = {value:jQuery(this).val(),type:'categoryFilter',action:jQuery(this).prop('checked')};
                        filterHistory.push(filterJson);
                   }); 
               }
            }
            
            var category = [];

            jQuery(".mobile-filter-block .right-content .filter-data-block .filter-data#tab-1 .heading input.highlight-check").each(function() {
                if(jQuery(this).parent().siblings('.filters').find('.filter-scroll .filter-content .sub-check').length == jQuery(this).parent().siblings('.filters').find('.filter-scroll .filter-content .sub-check:checked').length && jQuery(this).attr("data-child") == '1'){
                   category.push(jQuery(this).val());
                }else if(jQuery(this).attr("data-child") == '1'){
                   jQuery(this).parent().siblings('.filters').find('.filter-scroll .filter-content .sub-check:checked').each(function(){
                       category.push(jQuery(this).val());
                   });
                }else{
                   if(jQuery(this).is(":checked")){
                    //console.log("-------checked--parent -cat---");
                       category.push(jQuery(this).val());
                   }   
               }
               
           });

            console.log("history",filterHistory);
            isFilterChange = 1;
            isMobileClearAllFiter = 0;
            setMobileFilters("categoryFilter", category);
            // setFilter("firstAttemptedFilter", "categoryFilter");
            // applyFilters("categoryFilter", category.join("+"));
            jQuery('#mgs-ajax-loading').show();


        
    });




    jQuery(".mobile-filter-block .right-content .filter-data-block .filter-data#tab-1").on({
              click : function() {      
                 
                  
                    var category = [];
                  
                    var filterJson = {value:jQuery(this).val(),type:'categoryFilter',action:jQuery(this).prop('checked')};
                    filterHistory.push(filterJson);
                    jQuery(".filter-data#tab-1 .heading").find("input.highlight-check").each(function() {

                     //console.log("--value---",jQuery(this).val());
                      if(jQuery(this).parent().siblings('.filters').find('.filter-scroll .filter-content .sub-check').length == jQuery(this).parent().siblings('.filters').find('.filter-scroll .filter-content .sub-check:checked').length && jQuery(this).attr("data-child") == '1'){
                        //console.log("-----here----1--");
                          category.push(jQuery(this).val());

                      }else if(jQuery(this).attr("data-child") == '1'){
                         //console.log("-----here----2--");
                          jQuery(this).parent().siblings('.filters').find('.filter-scroll .filter-content .sub-check:checked').each(function(){
                              category.push(jQuery(this).val());
                          });
                      }else{
                        //console.log("-----here----3--");
                          if(jQuery(this).is(":checked")){
                            //console.log("-----here----4--");
                              category.push(jQuery(this).val());
                          }   
                      }
                      
                  });
                  console.log("history",filterHistory);
                  // removePriceFilter();
                  // setFilter("firstAttemptedFilter", "categoryFilter");
                  // applyFilters("categoryFilter", category.join("+"));
                    isFilterChange = 1;
                    isMobileClearAllFiter = 0;
                    setMobileFilters("categoryFilter", category);
                  jQuery('#mgs-ajax-loading').show();
              }
          },".sub-check");

    
    /*apply filters for mobile*/
    jQuery(document).on({
        click : function() {
            if(appliedMobileFilters.length !== 0){
                applyChangesOnApply();
            }else{
                jQuery('.mobile-filter-block').hide();
            }
           
            filterHistory = [];
        }
    }, ".filter-actions .apply-btn");



    /*cancel action for mobile*/
    var isFilterChange = 0;
    var isMobileClearAllFiter = 0;
    jQuery(document).on({
        click : function() {
            if (filterHistory.length !== 0) {
                filterHistory.forEach(function(item) {
                    var type = item.type;
                    var action = item.action ? false : true;
                    var filterValue = typeof item.value != 'undefined' ? item.value: '' ;
                    var minPrice = typeof item.min_price != 'undefined' ? item.min_price : '';
                    var maxPrice = typeof item.max_price != 'undefined' ? item.max_price : '';
                    if(type == 'priceFilter'){
                        snapSliderMobile.noUiSlider.set([minPrice, null]);
                        snapSliderMobile.noUiSlider.set([null, maxPrice]);
                        // noUiSlider.create(snapSliderMobile, {
                        //     start: [minimumPriceForSlideMobile, maximumPriceForSlideMobile],
                        //     connect: true,
                        //     range: {
                        //         'min': minPrice,
                        //         'max': maxPrice
                        //     }
                        // });

                    }else{
                        jQuery('.wrap .mobile-filter-block .filter-data[data-filter="'+type+'"] input.highlight-check[value="'+filterValue+'"]').prop('checked',action);
                    }
                    

                    
                    //code to revert price filter
                });
            
                filterHistory = [];
                appliedMobileFilters = {};
           
                // var vars = [], hash;
                // var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
                // for(var i = 0; i < hashes.length; i++)
                // {
                //     hash = hashes[i].split('=');
                //     var filterKey = hash[0];

                //     var filterValue = hash[1];
                //     if (filterKey.indexOf('Filter') > -1) {
                //         if(filterKey == 'firstAttemptedFilter'){
                //             appliedMobileFilters.firstAttemptedFilter = [filterValue];
                //         }else{

                //             var optionValues = filterValue.split('%2B');
                            
                            
                //             appliedMobileFilters[filterKey] = optionValues;
                //         }   
                //     }
                    
                // }

            jQuery('#currentPage').val(1);
            jQuery('#stopAjax').val(0);
            currentPage = 1;
            stopAjax = 0;
            appliedMobileFilters = getFilterValues();
            callFilterApi(appliedMobileFilters, currentPage);
            appliedMobileFilters = getFilterValues();

        }
        jQuery('.mobile-filter-block').hide();
        }
    }, ".filter-actions .cancel-btn");

    var filterTypes = ["categoryFilter","kidFilter","patternsFilter","genderFilter","tagsFilter","bridalFilter","designerFilter","colorFilter","sizeFilter","acoeditFilter","occasionFilter","deliveryFilter","priceFilter","themeFilter","sorting"];

    function applyChangesOnApply() {
        deleteFiltersFromUrl();            


        if (isMobileClearAllFiter != 1) {
              //console.log(appliedMobileFilters.firstAttemptedFilter[0]);
            jQuery.each(appliedMobileFilters, function(filterType, filterValues) {
                //console.log(filterValues);
                if (filterValues != '' && filterValues.length > 0) {
                    if (filterTypes.includes(filterType)) {
                        setFilter("firstAttemptedFilter", appliedMobileFilters.firstAttemptedFilter[0]);
                        console.log("filter type ",filterType);
                        if(filterType == 'sorting'){
                             setFilter(filterType, filterValues);  
                        }else{
                        setFilter(filterType, filterValues.join('+'));    
                    }   
                }
                }
            }); 
        }               
        jQuery('.mobile-filter-block').hide();
        isFilterChange = 0;
        isMobileClearAllFiter = 0;  
    }

    function deleteFiltersFromUrl() {        
        if (history.pushState) {            
            var newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.pushState({path:newUrl},'',newUrl);
        }
    }




    // Function to handle search input for mobile filter except category
    jQuery(document).on('input','.search-input' , function() {
    // jQuery('.search-input').on('input', function() {
        var searchText = jQuery(this).val().toLowerCase(); // Get the input value and convert it to lowercase
        var searchContainer = jQuery(this).closest('.filter-data'); // Find the closest container for the search input

        // Loop through each filter label within the container
        searchContainer.find('.filter-option .filter-content').each(function() {
            var label = jQuery(this);
            var labelText = label.find('a.text').text().toLowerCase(); // Get the text of the label and convert it to lowercase
            if (labelText.includes(searchText)) { // Check if the label text contains the search text
                label.show(); // Show the label if it matches the search
            } else {
                label.hide(); // Otherwise, hide the label
            }
        });
    });




    // Function to handle search input for main categories
    jQuery(document).on('input','.category-search-input' , function() {
    // jQuery('.category-search-input').on('input', function() {
        var searchText = jQuery(this).val().toLowerCase(); // Get the input value and convert it to lowercase
        var searchContainer = jQuery(this).closest('.filter-data'); // Find the closest container for the search input
        
        // Loop through each filter plot within the container
        searchContainer.find('.filter-plot').each(function() {
            var filterPlot = jQuery(this);
            var heading = filterPlot.find('.heading'); // Get the heading of the filter plot
            
            // Check if the heading text contains the search text
            var headingText = heading.find('a.menu-slot').text().toLowerCase();
            if (headingText.includes(searchText)) {
                filterPlot.show(); // Show the filter plot if it matches the search
            } else {
                filterPlot.hide(); // Otherwise, hide the filter plot
            }
        });
    });


    // Function to handle search input for subcategories

    jQuery(document).on('input','.sub-category-search-input' , function() {
    // jQuery('.sub-category-search-input').on('input', function() {
        var searchText = jQuery(this).val().toLowerCase(); // Get the input value and convert it to lowercase
        var filterScroll = jQuery(this).parents('.filters'); // Find the closest filters container
        
        // Loop through each filter content within the filter scroll container
        filterScroll.find('.filter-scroll .filter-content').each(function() {
            var filterContent = jQuery(this);
            
            // Check if the label text contains the search text
            var labelText = filterContent.find('a.text').text().toLowerCase();
            if (labelText.includes(searchText)) {
                filterContent.show(); // Show the filter content if it matches the search
            } else {
                filterContent.hide(); // Otherwise, hide the filter content
            }
        });
    });

    }
// -----------------------------------mobile filter jquery-----------------
    });
    })(jQuery);
});

   

