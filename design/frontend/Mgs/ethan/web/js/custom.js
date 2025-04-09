require(["jquery", "waypoints"], function (jQuery) {
    (function ($) {
        $.fn.appear = function (fn, options) {
            var settings = $.extend({ data: undefined, one: !0, accX: 0, accY: 0 }, options);
            return this.each(function () {
                var t = $(this);
                t.appeared = !1;
                if (!fn) {
                    t.trigger("appear", settings.data);
                    return;
                }
                var w = $(window);
                var check = function () {
                    if (!t.is(":visible")) {
                        t.appeared = !1;
                        return;
                    }
                    var a = w.scrollLeft();
                    var b = w.scrollTop();
                    var o = t.offset();
                    var x = o.left;
                    var y = o.top;
                    var ax = settings.accX;
                    var ay = settings.accY;
                    var th = t.height();
                    var wh = w.height();
                    var tw = t.width();
                    var ww = w.width();
                    if (y + th + ay >= b && y <= b + wh + ay && x + tw + ax >= a && x <= a + ww + ax) {
                        if (!t.appeared) t.trigger("appear", settings.data);
                    } else {
                        t.appeared = !1;
                    }
                };
                var modifiedFn = function () {
                    t.appeared = !0;
                    if (settings.one) {
                        w.unbind("scroll", check);
                        var i = $.inArray(check, $.fn.appear.checks);
                        if (i >= 0) $.fn.appear.checks.splice(i, 1);
                    }
                    fn.apply(this, arguments);
                };
                if (settings.one) t.one("appear", settings.data, modifiedFn);
                else t.bind("appear", settings.data, modifiedFn);
                w.scroll(check);
                $.fn.appear.checks.push(check);
                check();
            });
        };
        $.extend($.fn.appear, {
            checks: [],
            timeout: null,
            checkAll: function () {
                var length = $.fn.appear.checks.length;
                if (length > 0) while (length--) $.fn.appear.checks[length]();
            },
            run: function () {
                if ($.fn.appear.timeout) clearTimeout($.fn.appear.timeout);
                $.fn.appear.timeout = setTimeout($.fn.appear.checkAll, 20);
            },
        });
        $.each(["append", "prepend", "after", "before", "attr", "removeAttr", "addClass", "removeClass", "toggleClass", "remove", "css", "show", "hide"], function (i, n) {
            var old = $.fn[n];
            if (old) {
                $.fn[n] = function () {
                    var r = old.apply(this, arguments);
                    $.fn.appear.run();
                    return r;
                };
            }
        });
        $(document).ready(function () {
            $("[data-appear-animation]").each(function () {
                $(this).addClass("appear-animation");
                if ($(window).width() > 767) {
                    $(this).appear(
                        function () {
                            var delay = $(this).attr("data-appear-animation-delay") ? $(this).attr("data-appear-animation-delay") : 1;
                            if (delay > 1) $(this).css("animation-delay", delay + "ms");
                            $(this).addClass($(this).attr("data-appear-animation"));
                            $(this).addClass("animated");
                            setTimeout(function () {
                                $(this).addClass("appear-animation-visible");
                            }, delay);
                        },
                        { accX: 0, accY: -150 }
                    );
                } else {
                    $(this).addClass("appear-animation-visible");
                }
            });
            $(".nav-main-menu li.mega-menu-fullwidth.menu-2columns").hover(function () {
                if ($(window).width() > 1199) {
                    var position = $(this).position();
                    var widthMenu = $("#mainMenu").width() - position.left;
                    $(this).find("ul.dropdown-menu").width(widthMenu);
                }
            });
            $(".nav-main-menu .static-menu li > .toggle-menu a").click(function () {
                $(this).toggleClass("active");
                $(this).parent().parent().find("> ul").slideToggle();
            });
            jQuery(document).on("click", "#mobile_search button.button", function () {
                var searchstring = jQuery("#mobile_search .minisearch input").val();
                jQuery("#sticky_login_link form.minisearch input").val(searchstring);
                jQuery("#sticky_login_link .form.minisearch button.button").trigger("click");
            });
            $(".action.nav-toggle").click(function () {
                if ($("html").hasClass("nav-open")) {
                    $("html").removeClass("nav-open");
                    setTimeout(function () {
                        $("html").removeClass("nav-before-open");
                    }, 300);
                } else {
                    $("html").addClass("nav-before-open");
                    setTimeout(function () {
                        $("html").addClass("nav-open");
                    }, 42);
                }
            });
            $(".close-nav-button").click(function () {
                $("html").removeClass("nav-open");
                setTimeout(function () {
                    $("html").removeClass("nav-before-open");
                }, 300);
            });
            $(".checkout-extra > .block > .title").click(function () {
                $(".checkout-extra > .block > .title").removeClass("active");
                $(".checkout-extra > .block > .content").removeClass("active");
                $(this).addClass("active");
                $(this).parent().find("> .content").addClass("active");
            });
            $(document).on("click", ".sidebar.sidebar-additional .block .block-title .title", function (e) {
                $(this).toggleClass("active");
                $(this).parent().parent().find(".block-content").slideToggle();
            });
            $("#mobileSearchInp").keydown(function (event) {
                if (event.which === 13) {
                    $("#mobileSearchBtn").click();
                }
            });
            $(".switcher-toggle").click(function () {
                $(".dropdown-menu").toggle();
            });
            var cookieValue = getCook("UTM");
            let utm_url = "?";
            let utm_url_set = !1;
            const queryString = window.location.search;
            const urlParams = new URLSearchParams(queryString);
            if (urlParams.has("utm_source") || urlParams.has("utm_medium") || urlParams.has("utm_campaign") || urlParams.has("utm_content")) {
                const params = [];
                if (urlParams.has("utm_source") && urlParams.get("utm_source") !== "") {
                    const utm_source = encodeURIComponent(urlParams.get("utm_source"));
                    params.push(`utm_source=${utm_source}`);
                }
                if (urlParams.has("utm_medium") && urlParams.get("utm_medium") !== "") {
                    const utm_medium = encodeURIComponent(urlParams.get("utm_medium"));
                    params.push(`utm_medium=${utm_medium}`);
                }
                if (urlParams.has("utm_campaign") && urlParams.get("utm_campaign") !== "") {
                    const utm_campaign = encodeURIComponent(urlParams.get("utm_campaign"));
                    params.push(`utm_campaign=${utm_campaign}`);
                }
                if (urlParams.has("utm_content") && urlParams.get("utm_content") !== "") {
                    const utm_content = encodeURIComponent(urlParams.get("utm_content"));
                    params.push(`utm_content=${utm_content}`);
                }
                if (params.length > 0) {
                    utm_url += params.join("&");
                    utm_url_set = !0;
                }
            }
            utm_url = utm_url_set ? utm_url : "";
            console.log(utm_url);
            $("#sticky_login_link .search-form .form-search .minisearch #mobileSearchPop").click(function () {
                var searchText = $("#sticky_login_link .search-form .form-search #search_mini_form #search-form1").val();
                console.log(searchText);
                console.log(searchText.length);
                if (searchText.length > 2) {
                    $.ajax({
                        url: "/pagelayout/search/autosuggest?q=" + searchText,
                        type: "GET",
                        dataType: "json",
                        success: function (data) {
                            if (data.designer_url) {
                                var urlAll = data.designer_url;
                            } else {
                                var urlAll = data.urlAll;
                            }
                            if (cookieValue != "") {
                                window.location.href = urlAll + utm_url.replace(/\?/g, "&");
                            } else {
                                window.location.href = urlAll;
                            }
                        },
                    });
                }
            });
            var userAgent = navigator.userAgent;
            console.log("testua userAgent -> " + userAgent);
            if (userAgent == "android" || userAgent == "ios") {
                console.log("testua if");
                $(".scroll-to-top").hide();
                $("#aashnisticky .bottom-header-content .container .menu-content #mobileSearchPop").on("click", function () {
                    console.log("testua if native open");
                    $(".top-header.native-search .search-block-native").fadeIn();
                    $("#mobileSearchInpNative").focus();
                    $("html,body").css({ overflow: "hidden" });
                });
                $(".top-header.native-search .search-block-native .search-form-native .input .close-btn").on("click", function () {
                    $(".top-header.native-search .search-block-native").fadeOut();
                    $("#mobileSearchInpNative").val("");
                    $("html,body").css({ overflow: "" });
                });
                $("#aashnisticky .overlay-content-wrap.mobile-search").on("click", function () {
                    $(".top-header.native-search .search-block-native").fadeIn();
                    $("#mobileSearchInpNative").focus();
                    $("html,body").css({ overflow: "hidden" });
                });
                $("#aashnisticky .mobile-menu .fa-menu.icon").on("click", function () {
                    if ($("html").hasClass("nav-open")) {
                        $("html").removeClass("nav-open");
                        setTimeout(function () {
                            $("html").removeClass("nav-before-open");
                        }, 300);
                    } else {
                        $("html").addClass("nav-before-open");
                        setTimeout(function () {
                            $("html").addClass("nav-open");
                        }, 42);
                    }
                });
            } else {
                console.log("testua else");
                $("#aashnisticky .bottom-header-content .container .menu-content #mobileSearchPop").on("click", function () {
                    console.log("testua else normal open");
                    $(".search-block").fadeIn();
                    $("#mobileSearchInp").focus();
                    $("html,body").css({ overflow: "hidden" });
                });
                $(".bottom-header-content .search-block .search-forms .input .close-btn").on("click", function () {
                    $(".search-block").fadeOut();
                    $("#mobileSearchInp").val("");
                    $("html,body").css({ overflow: "" });
                });
            }
            $("#mobileSearchInp").click(function () {
                if ($("#mobileSearchInp").val() != "") {
                    searchForMobile($(this));
                }
            });
            $("#search-form1").click(function () {
                if ($("#mobileSearchInp").val() != "") {
                    searchForMobile($(this));
                }
            });
            $("body").on("keypress", ".middle-header-content #search-form1", function (event) {
                if (event.keyCode === 13) {
                    event.preventDefault();
                    var searchText = $(this).val();
                    if (searchText.length > 2) {
                        $.ajax({
                            url: "/pagelayout/search/autosuggest?q=" + encodeURIComponent(searchText),
                            type: "GET",
                            dataType: "json",
                            success: function (data) {
                                if (data.designer_url) {
                                    var urlAll = data.designer_url;
                                } else {
                                    var urlAll = data.urlAll;
                                }
                                if (utm_url != "") {
                                    window.location.href = urlAll + utm_url.replace(/\?/g, "&");
                                } else {
                                    window.location.href = urlAll;
                                }
                            },
                        });
                    }
                }
            });
            $("#mobileSearchInp").on("input", function () {
                searchForMobile($(this));
            });
            $("#mobileSearchInpNative").on("input", function () {
                searchForMobile($(this));
            });
            $("body").on("keydown", "#mobileSearchInpNative", function (event) {
                if (event.which === 13) {
                    if ($(".search-result a").length > 0) {
                        window.location.href = $(".search-result a").attr("href");
                    }
                }
            });
            var currentRequest = null;
            function searchForMobile(inpElement) {
                var searchText = inpElement.val();
                if (searchText.length > 2) {
                    if (currentRequest != null) {
                        currentRequest.abort();
                    }
                    currentRequest = $.ajax({
                        url: "/pagelayout/search/autosuggest?q=" + searchText,
                        type: "GET",
                        dataType: "json",
                        success: function (data) {
                            var searchHtml = "";
                            if (data.designer_url) {
                                var urlAll = data.designer_url;
                            } else {
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
                            if (data.noResults) {
                                searchHtml +=
                                    '<div class="search-no-result-found"><div class="no-result-found"><div class="no-result-text">Sorry, there are no results for</div><div class="content-keyword">"' +
                                    searchText +
                                    '"</div><div class="content-try-new-search">Try a new search</div></div></div>';
                                $(".search-block-content").html(searchHtml);
                                return false;
                            }
                            searchHtml += '<div class="search-content search-product">';
                            searchHtml += '<div class="left-block"> <div class="category-list"> <div class="tit">' + data.title_text + "</div>" + data.SearchCategoryHtml;
                            searchHtml += "</div></div>";
                            searchHtml += '<div class="right-block">';
                            var searchitems = $.parseJSON(JSON.stringify(data.indices));
                            var totalItems = data.totalItems;
                            $.each(searchitems, function (i, item) {
                                console.log("step8");
                                var items = item.items;
                                $.each(items, function (i, product) {
                                    console.log("step9");
                                    console.log(product);
                                    var name = product.name;
                                    var url = product.url + utm_url.replace(/\?/g, "&");
                                    var description = product.description;
                                    var price = product.price;
                                    var imageUrl = product.image;
                                    searchHtml +=
                                        '<div class="search-column"> <a href="' +
                                        url +
                                        '"> <div class="img-pod"> <img src="' +
                                        imageUrl +
                                        '"> </div> <div class="content"> <div class="designer-name">' +
                                        name +
                                        '</div>  <div class="desc">' +
                                        description +
                                        '</div> </a> <div class="price">' +
                                        price +
                                        "</div> </div> </div>";
                                });
                            });
                            var i = 0;
                            searchHtml += "</div> </div> </div>";
                            searchHtml += '<div class="search-result"> <a href="' + urlAll + '" class="text">View all results</a> </div>';
                            console.log("Nayaab 1");
                            console.log(searchHtml);
                            if (userAgent == "android" || userAgent == "ios") {
                                $(".top-header.native-search .contain .search-block-native .search-block-content").html(searchHtml);
                            } else {
                                $(".bottom-header-content .search-block .search-forms .search-block-content").html(searchHtml);
                            }
                        },
                    });
                } else {
                    console.log("Result Empty No Search Found");
                    $(".bottom-header-content .search-block .search-forms .search-block-content").css("display", "none");
                }
            }
            $("table#shopping-cart-table tbody tr td .field.qty .control.qty button#add").click(function () {
                if ($(this).prev().val() < 6) {
                    $(this)
                        .prev()
                        .val(+$(this).prev().val() + 1);
                }
            });
            $("table#shopping-cart-table tbody tr td .field.qty .control.qty button#sub").click(function () {
                if ($(this).next().val() > 1) {
                    if ($(this).next().val() > 1)
                        $(this)
                            .next()
                            .val(+$(this).next().val() - 1);
                }
            });
            var currentUrl = window.location.href;
            document.cookie = "currentUrl=" + currentUrl;
            var getUrlParameter = function getUrlParameter(sParam) {
                var sPageURL = window.location.search.substring(1),
                    sURLVariables = sPageURL.split("&"),
                    sParameterName,
                    i;
                for (i = 0; i < sURLVariables.length; i++) {
                    sParameterName = sURLVariables[i].split("=");
                    if (sParameterName[0] === sParam) {
                        return sParameterName[1] === undefined ? !0 : decodeURIComponent(sParameterName[1]);
                    }
                }
                return !1;
            };
            var utm_source = getUrlParameter("utm_source");
            let utm_url_cookie = "?";
            if (urlParams.has("utm_source") || urlParams.has("utm_medium") || urlParams.has("utm_campaign") || urlParams.has("utm_content")) {
                console.log(queryString);
                if (utm_url != "") {
                    console.log("fb");
                    document.cookie = "UTM=" + utm_url;
                }
            }
            function getCook(cookiename) {
                var cookiestring = RegExp(cookiename + "=[^;]+").exec(document.cookie);
                return decodeURIComponent(!!cookiestring ? cookiestring.toString().replace(/^[^=]+./, "") : "");
            }
            cookieValue = getCook("UTM");
            console.log(cookieValue);
            if (jQuery("body").hasClass("wishlist-index-index") == !0) {
                var curr_url = new URL(window.location.href);
                curr_url += cookieValue;
                window.history.replaceState(null, null, curr_url);
            }
            if (cookieValue != "") {
                console.log("if cookie");
                $("#sticky_logo .logo").addClass("utm_class");
                jQuery(".utm_class").each(function () {
                    var url = jQuery(this).attr("href");
                    if (url != "#" && url != undefined && url.indexOf("?utm_source") == -1) {
                        if (url.indexOf("?") > -1) {
                            url += cookieValue.replace(/\?/g, "&");
                        } else {
                            url += cookieValue;
                        }
                        jQuery(this).attr("href", url);
                    }
                });
            }
            function getUtmparamshomepage() {
                if (cookieValue != "") {
                    jQuery(".homepage_utm_class").each(function () {
                        var url = jQuery(this).attr("href");
                        if (jQuery("body").hasClass("cms-index-index") == !0) {
                            if (url.indexOf("?") > -1) {
                                url += cookieValue.replace(/\?/g, "&");
                            } else {
                                url += cookieValue;
                            }
                        }
                        jQuery(this).attr("href", url);
                    });
                }
            }
            setTimeout(getUtmparamshomepage(), 3000);
            if (jQuery("body.catalog-product-view").length > 0) {
                var designerId = jQuery("#p_designer").val();
                var patternsId = jQuery("#p_patterns").val();
                var genderId = jQuery("#p_gender").val();
                var colorId = jQuery("#p_color").val();
                var pId = jQuery("#p_id").val();
                var categoryId = jQuery("#p_category").val();
                var themeId = jQuery("#p_theme").val();
                var finalprice = jQuery("#p_finalprice").val();
                var kids = jQuery("#p_kids").val();
                var lastChildId = jQuery("#p_lastChildId").val();
                var recentlyViewedSkus = JSON.parse(localStorage.getItem('recentlyViewedSkus')) || [];
                var pdpUrl = window.location.href;
                jQuery.ajax({
                    url: "/pagelayout/pdp/suggestproduct",
                    type: "POST",
                    data: { designer: designerId, patterns: patternsId, gender: genderId, colors: colorId, categoryIds: categoryId, theme: themeId, pid: pId, pdp_url: pdpUrl, finalprice: finalprice, kidsId: kids, lastChildId: lastChildId, recentlyViewedSkus:recentlyViewedSkus },
                    beforeSend: function (e) {
                        jQuery(".body-popup-overlay").show();
                        e.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                    },
                    success: function (resp) {
                        var respobj = JSON.parse(resp);
                        if (respobj.pair_it_with.count != 0) {
                            jQuery(".column.main").append('<div class="content-heading"><h2 class="title"><span id="block-related-heading" role="heading" aria-level="2"> Pair It With</span><hr></h2></div>');
                            jQuery(".column.main").append('<div class="product-similar-color">' + respobj.pair_it_with.reshtml + "</div>");
                        }
                        if (respobj.ready_to_ship.count != 0) {
                            jQuery(".column.main").append('<div class="content-heading"><h2 class="title"><span id="block-related-heading" role="heading" aria-level="2"> Ready To Ship </span><hr></h2></div>');
                            jQuery(".column.main").append('<div class="product-ready-to-ship">' + respobj.ready_to_ship.reshtml + "</div>");
                        }
                        
                        if (respobj.new_arrivals.count != 0) {
                            jQuery(".column.main").append('<div class="content-heading"><h2 class="title"><span id="block-related-heading" role="heading" aria-level="2">New Arrivals</span><hr></h2></div>');
                            jQuery(".column.main").append('<div class="product-new-arrivals">' + respobj.new_arrivals.reshtml + "</div>");
                        }
                        if (respobj.recent_viewed.count != 0) {
                            jQuery(".column.main").append('<div class="content-heading"><h2 class="title"><span id="block-related-heading" role="heading" aria-level="2">Recent Viewed</span><hr></h2></div>');
                            jQuery(".column.main").append('<div class="product-recent-viewed">' + respobj.recent_viewed.reshtml + "</div>");
                        }
                        jQuery(".column.main").append(
                            '<div class="product-suggested-product"><div class="suggested-product-tab">' +
                                respobj.suggested_tab +
                                '</div><div id="category-tab" class="suggested-product-category tabcontent" style="display: block;">' +
                                respobj.you_may_also_like +
                                '</div><div id="related-tab" class="suggested-product-related tabcontent" style="display: none;">' +
                                respobj.more_from_designer +
                                "</div></div>"
                        );
                    },
                });
            }
            $("#read-more").on("click", function () {
                const hiddenText = $(".hidden-text-footer");
                if (hiddenText.is(":hidden")) {
                    hiddenText.show();
                    $(this).text("[Read Less]");
                } else {
                    hiddenText.hide();
                    $(this).text("[Read More]");
                }
            });
        });
    })(jQuery);
});
require(["jquery", "mgs_quickview"], function (jQuery) {
    (function ($) {
        $(document).ready(function () {
            $(".btn-loadmore").click(function () {
                var el = $(this);
                el.find("span").addClass("loading");
                url = $(this).attr("href");
                $.ajax({
                    url: url,
                    success: function (data, textStatus, jqXHR) {
                        var result = $.parseJSON(data);
                        if (result.content != "") {
                            $("#" + result.element_id).append(result.content);
                            $(".mgs-quickview").bind("click", function () {
                                var prodUrl = $(this).attr("data-quickview-url");
                                if (prodUrl.length) {
                                    reInitQuickview($, prodUrl);
                                }
                            });
                        }
                        $("form[data-role='tocart-form']").catalogAddToCart();
                        if (result.url) {
                            el.attr("href", result.url);
                        } else {
                            el.remove();
                        }
                    },
                });
                return !1;
            });
            jQuery("body").click(function (e) {
                if (!jQuery(e.target).closest(".search-block-content").length && jQuery(".search-block-content").is(":visible")) {
                    jQuery(".search-block-content").hide();
                }
            });
        });
        function setCookie(cname, cvalue, exdays, domain, path, secureflag) {
            console.log("Setting cookie: " + cname + "=" + cvalue);
            var d = new Date();
            d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
            var expires = "expires=" + d.toUTCString();
            var cookieString = cname + "=" + cvalue + ";" + expires;
            if (domain) {
                cookieString += ";domain=" + domain;
            }
            if (path) {
                cookieString += ";path=" + path;
            }
            if (secureflag) {
                cookieString += ";secure=";
            }
            document.cookie = cookieString;
        }
        function getCookie(cname) {
            console.log("Getting cookie: " + cname);
            var name = cname + "=";
            var ca = document.cookie.split(";");
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == " ") {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        }
        require(["jquery"], function ($) {
            $(document).ready(function () {
                const agent = getQueryParam("agent");
                console.log("nativeapp :: agent -> " + agent);
                if ((agent != "" || agent != null || agent != undefined) && (agent === "android" || agent === "ios")) {
                    console.log("nativeapp :: setting cookie aashni_app");
                    setCookie("aashni_app", agent, 180, ".aashniandco.com", "/", !0);
                }
                if (agent === "android" || agent === "ios" || getCookie("aashni_app") == "android" || getCookie("aashni_app") == "ios") {
                    console.log("nativeapp :: hiding contextual footer");
                    $(".bottom-footer").hide();
                }
                function getQueryParam(name) {
                    const urlParams = new URLSearchParams(window.location.search);
                    return urlParams.get(name);
                }

                function getDeviceType() {
                    const userAgent = navigator.userAgent.toLowerCase();
                    if (/mobile|android|ios|iphone|ipod|blackberry|webos|opera mini|opera mobi/.test(userAgent)) {
                        return "mobile";
                    } else if (/ipad|tablet/.test(userAgent)) {
                        return "tablet";
                    } else {
                        return "desktop";
                    }
                }
                if (getDeviceType() != "desktop") {
                    $(".exclamation-icon-wrap").click(function () {
                        $(".price-details-popup .price-details-popup-content").css({ display: "block", height: "auto" });
                        $(".price-details-popup .price-details-popup-overlay").css("display", "block");
                    });
                    $(".price-details-popup .price-details-popup-content .close-icon").click(function () {
                        $(".price-details-popup .price-details-popup-content").css({ display: "", height: "" });
                        $(".price-details-popup .price-details-popup-overlay").css("display", "none");
                    });
                }
            });
        });
        $(document).ready(function () {
            setTimeout(function () {
                if (getCookie("closed")) {
                    console.log("Cookie found. Hiding popup.");
                    $("#newsletter-plot-cookies").css("display", "none");
                } else {
                    console.log("Cookie not found. Showing popup.");
                    $("#newsletter-plot-cookies").css("display", "none");
                }
            }, 6000);
            $(".reject-all").click(function () {
                console.log("Reject button clicked. Setting cookie and hiding popup.");
                setCookie("closed", 1, 180, ".aashniandco.com", "/", !0);
                $("#newsletter-plot-cookies").css("display", "none");
            });
            $(".accept-all").click(function () {
                console.log("Accept button clicked. Setting cookie and hiding popup.");
                setCookie("closed", 1, 180, ".aashniandco.com", "/", !0);
                $("#newsletter-plot-cookies").css("display", "none");
            });
        });
    })(jQuery);
});
function reInitQuickview($, prodUrl) {
    if (!prodUrl.length) {
        return !1;
    }
    var url = QUICKVIEW_BASE_URL + "mgs_quickview/index/updatecart";
    $.magnificPopup.open({
        items: { src: prodUrl },
        type: "iframe",
        removalDelay: 300,
        mainClass: "mfp-fade",
        closeOnBgClick: !0,
        preloader: !0,
        tLoading: "",
        callbacks: {
            open: function () {
                $(".mfp-preloader").css("display", "block");
            },
            beforeClose: function () {
                $('[data-block="minicart"]').trigger("contentLoading");
                $.ajax({ url: url, method: "POST" });
            },
            close: function () {
                $(".mfp-preloader").css("display", "none");
            },
        },
    });
}
function setLocation(url) {
    require(["jquery"], function (jQuery) {
        (function () {
            window.location.href = url;
        })(jQuery);
    });
}
require(["Magento_Customer/js/customer-data", "jquery"], function (customerData, jQuery) {
    if (!jQuery("body").hasClass("checkout-index-index") && !jQuery("body").hasClass("checkout-onepage-success") && !jQuery("body").hasClass("checkout-cart-index")) {
        var quoteId = getCookie("buynow");
        if (quoteId != null && quoteId != undefined && quoteId != "") {
            jQuery.ajax({
                url: "/buynow/cart/replacequote",
                type: "POST",
                data: { is_ajax: 1, "quote-id": quoteId },
                beforeSend: function (e) {
                    e.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                },
                success: function (response) {
                    deleteCookie("buynow");
                    var sections = ["cart"];
                    customerData.invalidate(sections);
                    customerData.reload(sections, !0);
                },
            });
        }
    }
    if (jQuery("body").hasClass("checkout-onepage-success")) {
        var sections = ["cart"];
        customerData.invalidate(sections);
        customerData.reload(sections, !0);
    }
    function getCookie(cname) {
        console.log("Getting cookie: " + cname);
        var name = cname + "=";
        var ca = document.cookie.split(";");
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == " ") {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }
    function deleteCookie(name) {
        document.cookie = name + "=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;domain=.aashniandco.com";
    }
});
require(["Magento_Customer/js/customer-data", "jquery"], function (customerData, $) {
    $(document).ready(function () {
        $(document).on("change", ".super-attribute-select", function () {
            $("#mgs-ajax-loading").show();
            var selectedOption = $(this).find("option:selected");
            $(this).find("option").not(selectedOption).removeAttr("selected");
            $.ajax({
                type: "POST",
                url: "/pagelayout/cart/update",
                data: { sku: selectedOption.data("sku") },
                success: function (response) {
                    $("#mgs-ajax-loading").hide();
                    resp = JSON.parse(response);
                    var sections = ["cart"];
                    customerData.invalidate(sections);
                    customerData.reload(sections, !0);
                    if (resp.error == 0) {
                        window.location.reload();
                    } else {
                        console.log(resp.message);
                    }
                },
            });
        });
    });
});
require(["jquery"], function ($) {
    if ($("body").hasClass("checkout-index-index")) {
        $(document).on("click", "#co-payment-form .payment-option.discount-code", function () {
            if ($("#co-payment-form .payment-option.discount-code").hasClass("_active")) {
                $(".payment-option._collapsible.opc-payment-additional.discount-code").css("margin-bottom", "90px");
            } else {
                $(".payment-option._collapsible.opc-payment-additional.discount-code").css("margin-bottom", "");
            }
        });
    }
});
