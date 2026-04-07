(function($) {

    /*-- Strict mode enabled --*/
    'use strict';

    var _html = document.documentElement,
    isTouch = (('ontouchstart' in _html) || (navigator.msMaxTouchPoints > 0) || (navigator.maxTouchPoints));

    _html.className = _html.className.replace("no-js","js");
    _html.classList.add( isTouch ? "touch" : "no-touch");


    /*-- Global variables --*/
    var nHtmlNode = document.documentElement,
    nBodyNode = document.body || document.getElementsByTagName('body')[0],
    nAppNode  = document.getElementById('app'),
    nHeader   = document.getElementById('top-bar'),
    nHeaderTwo = $( ".nt-shortcode-header" ).closest('.nt-section'),
    nHereoTwo = nHeaderTwo.next( ".nt-section" ),
    nHereoThree = nHeaderTwo.next( ".container" ),
    nContainerNull = $( ".container-null" ).size(),
    nHero     = document.getElementById('start-screen') || document.getElementById('hero') || document.getElementById('nt-hero') || document.getElementById('nt-404') || nHereoTwo || nHereoThree,
    jWindow   = $(window),
    jBodyNode = $(nBodyNode),
    jAppNode  = $(nAppNode),
    jHeader   = $(nHeader),
    jHero     = $(nHero),
    jHeaderNotrans     = $(nHeader).hasClass('transition-off'),

    iHeaderHeight = 0,
    bNavAnchor    = jHeader.data('nav-anchor') === true ? true : false,
    bMenuOpen     = false,

    animationEnd = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend',

    rAF = window.requestAnimationFrame ||
    window.mozRequestAnimationFrame ||
    window.webkitRequestAnimationFrame ||
    window.msRequestAnimationFrame||
    function ( callback ) {
        setTimeout(callback, 1000 / 60);
    }

    var scrollBarWidth = parseInt(window.innerWidth - nHtmlNode.clientWidth);

    function _showScroll ()
    {
        nHtmlNode.style.overflow = '';
        nHeader.style.right = '';
    }

    function _hideScroll ()
    {
        nHtmlNode.style.overflow = 'hidden';
        nHeader.style.right = scrollBarWidth + 'px';
    }

    if ( nHereoTwo ) {
        nHereoTwo.addClass('after-header');
    }
    if ( nContainerNull ) {
        $( ".container-null > .nt-column" ).attr('class', 'content-wrapper');
    }


    /* smoothScroll to anchor for button
    ================================================== */
    function _smoothScroll ()
    {
        var scroll = new SmoothScroll('a[href*="#"]', {
            ignore: '[data-scroll-ignore]',
            header: null,
            topOnEmptyHash: true,
            speed: 500,
            speedAsDuration: false,
            durationMax: null,
            durationMin: null,
            clip: true,
            offset: function (anchor, toggle) {
                var header_h = $('#top-bar').outerHeight();
                return header_h;
            },
            easing: 'easeInOutCubic',
            customEasing: function (time) {
                return time < 0.5 ? 2 * time * time : -1 + (4 - 2 * time) * time;
            },
            updateURL: true,
            popstate: true,
            emitEvents: true
        });
    }


    /* svg load and animation
    ================================================== */
    function _setVivus () {

        $('.svgicon').each( function() {

            var theID = $(this).attr('id');
            var url = $(this).data('url');
            var color = $(this).data('color');
            var svgwidth = $(this).data('width');
            var svgheight = $(this).data('height');
            var duration = $(this).data('duration');
            var animation = $(this).data('animation');
            var viewbox = $(this).data('viewbox');
            var dur = parseInt(duration);
            if ( url.length ) {
                new Vivus(
                    theID, {
                        duration: dur,
                        file: url,
                        type: 'async',
                        onReady: function (obj) {
                            if( animation != 'false' && dur > 1 ){
                                obj.el.setAttribute('fill', 'none');
                                obj.el.setAttribute('stroke-width', '5px');
                            }
                            if( svgwidth && svgheight ){
                                obj.el.setAttribute('width', svgwidth);
                                obj.el.setAttribute('height', svgheight);
                            }
                            if( viewbox ){
                                obj.el.setAttribute('viewBox', viewbox);
                            }
                            if( color ){
                                obj.el.setAttribute('stroke', color);
                            }
                        }
                    },
                    function (obj) {
                        if( animation != 'false' && dur > 1 ){
                            obj.el.classList.add('finished');
                            obj.el.setAttribute('stroke', '0');
                        }
                        if( color ){
                            obj.el.setAttribute('fill', color);
                        }
                    }
                );
            }
        });
    }

    /* scroll animate
    ================================================== */
    AOS.init({
        offset: 120,
        delay: 100,
        duration: 450, // or 200, 250, 300, 350.....
        easing: 'ease-in-out-quad',
        once: true,
        disable: 'mobile'
    });

    /* header
    ================================================== */
    function _header ()
    {
        var nMenu      = document.getElementById('top-bar__navigation'),
        nMenuToggler   = document.getElementById('top-bar__navigation-toggler'),
        jMenu          = $(nMenu),
        jMenuToggler   = $(nMenuToggler),
        jMenuLink      = jMenu.find('li a'),
        jSubmenu       = jMenu.find('.submenu'),
        bHeaderSticky  = false,
        updatePosition = function ()
        {
            var iTop;
            if ( jHero.length ) {
                iTop = jHero.innerHeight() - iHeaderHeight;
            } else {
                iTop = 300;
            }

            if ( jHeaderNotrans ) {

                _stickedNotransition();

            } else {

                if ( (window.pageYOffset || document.documentElement.scrollTop) >= iTop )
                {
                    if ( !bHeaderSticky )
                    {
                        jHeader
                        .off(animationEnd)
                        .addClass('is-sticky in')
                        .one(animationEnd, function(e){
                            jHeader.removeClass('in');
                        });

                        bHeaderSticky = !bHeaderSticky;
                    }
                }
                else if ( bHeaderSticky )
                {
                    jHeader
                    .addClass('out')
                    .off(animationEnd)
                    .one(animationEnd, function(e){
                        jHeader.removeClass('is-sticky out');
                    });

                    bHeaderSticky = !bHeaderSticky;
                }
            }
        },
        hideMobileMenu = function ()
        {
            if ( window.innerWidth > 1199 && bMenuOpen )
            {

                jHeader.removeClass('is-expanded');
                jMenuToggler.removeClass('is-active');
                jSubmenu.removeAttr('style');
                nHtmlNode.style.overflow = '';
                bMenuOpen = false;
            }
        };

        iHeaderHeight = jMenuToggler.is(':visible') ? 65 : 90;

        if ( bNavAnchor )
        {
            jBodyNode.scrollspy({
                target: nHeader,
                offset: iHeaderHeight + 1
            });
        }

        if ( jSubmenu.length > 0 )
        {
            jSubmenu.parent('li').addClass('has-submenu');
        }

        jMenuToggler.on('touchend click', function (e) {
            e.preventDefault();

            var $this = $(this);

            if ( bMenuOpen )
            {
                $this.removeClass('is-active');
                jHeader.removeClass('is-expanded');
                nHtmlNode.style.overflow = '';
                bMenuOpen = !bMenuOpen;
            }
            else
            {
                $this.addClass('is-active');
                jHeader.addClass('is-expanded');
                nHtmlNode.style.overflow = 'hidden';
                bMenuOpen = !bMenuOpen;
            }

            return false;
        });

        jMenuLink.on('click', function (e) {

            var $this   = $(this),
            $parent     = $this.parent(),
            bHasSubmenu = $this.next(jSubmenu).length ? true : false;

            if ( bMenuOpen && bHasSubmenu )
            {
                if ( $this.next().is(':visible') )
                {
                    $parent.removeClass('drop_active');
                    $this.next().slideUp('fast');

                } else {

                    $this.closest('ul').find('li').removeClass('drop_active');
                    $this.closest('ul').find('.submenu').slideUp('fast');
                    $parent.addClass('drop_active');
                    $this.next().slideDown('fast');
                }

                return false;
            }
        });

        jWindow
        .on('scroll', throttle(updatePosition, 100)).scroll()
        .on('resize', debounce(hideMobileMenu, 100));
    }

    /* choose lang
    ================================================== */
    function _chooseLang ()
    {
        var chooseLang = $('.js-choose-lang');

        if ( chooseLang.length > 0 )
        {
            var currLang = chooseLang.children('.current-lang'),
            currFlag = currLang.find('img'),
            currName = currLang.find('span'),

            langList  = chooseLang.children('.list-wrap'),
            listItem  = langList.find('li');

            currLang.on('click', function (e)
            {
                var $this = $(this),
                img = $this.find('img');

                chooseLang.addClass('is-active');

                langList.slideToggle();
            });

            listItem.on('click', function (e)
            {
                var $this = $(this),
                name  = $this.attr('data-short-name'),
                flag  = $this.attr('data-img');;

                listItem.removeClass('is-active');
                $this.addClass('is-active');

                currFlag.attr('src', flag);
                currName.text(name);

                langList.delay(300).slideUp(function () {
                    chooseLang.removeClass('is-active')
                });
            });
        }
    }

    /* side menu toggle
    ================================================== */
    function _sideMenuToggle ()
    {
        var isVisible = false,
        isActive  = false,
        nSideMenu = document.getElementById('side-menu'),

        jSideMenu = $(nSideMenu),
        jBtnOpen  = $('.js-side-menu-open'),
        jBtnClose = $('.js-side-menu-close');

        jBtnOpen.on('touchend click', function () {

            if ( !isVisible )
            {
                // first click
                jSideMenu.removeClass('d-none').delay(100).queue(function () {
                    $(this).addClass('is-active').dequeue();
                });
            }
            else
            {
                jSideMenu.addClass('is-active');
            }

            isVisible = true;
            isActive  = true;

            return false;
        });

        jBtnClose.on('touchend click', function () {

            jSideMenu.removeClass('is-active');

            isActive = false;

            return false;
        });

        jWindow.on('scroll', throttle(function() {

            if ( isActive )
            {
                jSideMenu.removeClass('is-active');

                isActive = false;
            }

        }, 500));
    }

    /* scroll top
    ================================================== */
    function _sticked ()
    {
        $(window).scroll(function() {
            var top_bar = $('.top-bar.is-sticky').size();
            var scroll = $(window).scrollTop();

            if (top_bar) {
                if (scroll >= 50) {
                    $(".top-bar.is-sticky").addClass("sticked");
                } else {
                    $(".top-bar.is-sticky").removeClass("sticked");
                }
            }
        });
    }

    /* scroll top
    ================================================== */
    function _stickedNotransition ()
    {
        $(window).scroll(function() {
            var top_bar = $('.top-bar').size();
            var scroll = $(window).scrollTop();

            if (top_bar) {
                if (scroll > 0) {
                    $(".top-bar").addClass("is-sticky");
                } else {
                    $(".top-bar").removeClass("is-sticky");
                }
            }
        });
    }

    /* tilt
    ================================================== */
    function _tilt ()
    {

        var nTilt = document.querySelectorAll(".js-tilt");

        if ( device.desktop() && nTilt.length > 0 )
        {
            VanillaTilt.init(nTilt);
        }
    }

    /* parallax
    ================================================== */
    function _parallax ()
    {

        var nJarallax = document.querySelectorAll('.jarallax');

        if ( device.desktop() && nJarallax.length > 0 )
        {
            jarallax(nJarallax, {
                zIndex: -20
            });
        }
    }

    /* isotope sorting
    ================================================== */
    function _isotopeSorting ()
    {
        var jOptionSets = $('.js-isotope-sort');

        if ( jOptionSets.length > 0 )
        {
            jOptionSets.each(function ( i, optionSet ) {
                var $this         = $( optionSet ),
                jOptionLinks  = $this.find('a'),
                jIsoContainer = $this.siblings('.js-isotope');

                jOptionLinks.on('click', function(e) {
                    var currentLink   = $(this),
                    currentOption = currentLink.data('cat');

                    $this.find('.selected').removeClass('selected');
                    currentLink.addClass('selected');

                    if (currentOption !== '*') {
                        currentOption = '.' + currentOption;
                    }

                    jIsoContainer.isotope({filter : currentOption});

                    return false;
                });
            });
        }
    }

    /* slick slider
    ================================================== */
    function _slickSlider ()
    {

        var jSlider = $('.js-slick');

        var rtl = $('[dir="rtl"]').length ? true : false;
        if ( jSlider.length > 0 )
        {
            jSlider.each(function ( i, slider ) {
                var $this = $( slider );

                $this.on('init', function(event, slick){

                }).slick({
                    autoplay: true,
                    autoplaySpeed: 3000,
                    adaptiveHeight: true,
                    dots: true,
                    arrows: false,
                    speed: 800,
                    mobileFirst: true,
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    touchThreshold: 15,
                    rtl: rtl,
                    prevArrow: '<i class="fontello-angle-left slick-prev"></i>',
                    nextArrow: '<i class="fontello-angle-right slick-next"></i>'
                });
            });
        }
    }

    /* lightbox
    ================================================== */
    function _fancybox ()
    {

        var galleryElement = $("a[data-fancybox]");

        if ( galleryElement.length > 0 )
        {
            $("[data-fancybox]").fancybox({
                parentEl: nAppNode,
                buttons : [
                    'slideShow',
                    'fullScreen',
                    'thumbs',
                    'close'
                ],
                loop : true,
                protect: true,
                wheel : false,
                transitionEffect : "tube",
                onInit: function (instance, slide, e) {

                    _hideScroll();
                },
                afterClose: function (instance, slide, e) {

                    _showScroll();
                }
            });
        }
    }

    /* accordion
    ================================================== */
    function _accordion ()
    {
        var oAccordion = $('.accordion-container');

        if ( oAccordion.length > 0 ) {

            var oAccItem    = oAccordion.find('.accordion-item'),
            oAccTrigger = oAccordion.find('.accordion-toggler');

            oAccordion.each(function ( i, accordion ) {
                $( accordion ).find('.accordion-item:eq(0)').addClass('active');
            });

            oAccTrigger.on('click', function (j) {
                j.preventDefault();

                var $this = $(this),
                parent = $this.parent(),
                dropDown = $this.next('article');

                parent.toggleClass('active').siblings(oAccItem).removeClass('active').find('article').not(dropDown).slideUp();

                dropDown.stop(false, true).slideToggle();

                return false;
            });
        }
    }

    /* tabs
    ================================================== */
    function _tabs ()
    {
        var oTab = $('.tab-container');

        if ( oTab.length > 0 ) {

            var oTabTrigger = oTab.find('.tab-nav__item');

            oTab.each(function ( i , tab ) {

                $( tab )
                .find('.tab-nav__item:eq(0)').addClass('active').end()
                .find('.tab-content__item:eq(0)').addClass('is-visible');
            });

            oTabTrigger.on('click', function (g) {
                g.preventDefault();

                var $this = $(this),
                index = $this.index(),
                parent = $this.closest('.tab-container');

                $this.addClass('active').siblings(oTabTrigger).removeClass('active');

                parent
                .find('.tab-content__item.is-visible').removeClass('is-visible').end()
                .find('.tab-content__item:eq(' + index + ')').addClass('is-visible');

                return false;
            });
        }
    }

    /* counters
    ================================================== */
    function _counters ()
    {
        var jCounter = $('.js-count');

        function _countInit() {
            jCounter.each(function( i, counter ) {
                var $this = $( counter );

                if( $this.is_on_screen() && !$this.hasClass('animate') )
                {
                    $this
                    .addClass('animate')
                    .countTo({
                        from: 0,
                        speed: 2000,
                        refreshInterval: 100
                    });
                };
            });
        }

        if ( jCounter.length > 0 )
        {
            _countInit();

            jWindow.on('scroll', throttle(function(e) {

                // _countInit();

                if( rAF ) {
                    rAF(function(){
                        _countInit();
                    });
                } else {
                    _countInit();
                }

            }, 400));
        }
    }

    /* google map
    ================================================== */
    function _g_map ()
    {
        var maps = $('.g_map');

        if ( maps.length > 0 )
        {
            var apiKey = maps.attr('data-api-key'),
            apiURL;

            if (apiKey)
            {
                apiURL = 'http://maps.google.com/maps/api/js?key='+ apiKey +' &sensor=false';
            }
            else
            {
                apiURL = 'http://maps.google.com/maps/api/js?sensor=false';
            }

            $.getScript( apiURL , function( data, textStatus, jqxhr ) {

                maps.each(function() {
                    var current_map = $(this),
                    latlng = new google.maps.LatLng(current_map.attr('data-longitude'), current_map.attr('data-latitude')),
                    point = current_map.attr('data-marker'),

                    myOptions = {
                        zoom: 14,
                        center: latlng,
                        mapTypeId: google.maps.MapTypeId.ROADMAP,
                        mapTypeControl: false,
                        scrollwheel: false,
                        draggable: true,
                        panControl: false,
                        zoomControl: false,
                        disableDefaultUI: true
                    },

                    stylez = [
                        {
                            featureType: "all",
                            elementType: "all",
                            stylers: [
                                { saturation: -100 } // <-- THIS
                            ]
                        }
                    ];

                    var map = new google.maps.Map(current_map[0], myOptions);

                    var mapType = new google.maps.StyledMapType(stylez, { name:"Grayscale" });
                    map.mapTypes.set('Grayscale', mapType);
                    map.setMapTypeId('Grayscale');

                    var marker = new google.maps.Marker({
                        map: map,
                        icon: {
                            size: new google.maps.Size(59,69),
                            origin: new google.maps.Point(0,0),
                            anchor: new google.maps.Point(0,69),
                            url: point
                        },
                        position: latlng
                    });

                    google.maps.event.addDomListener(window, "resize", function() {
                        var center = map.getCenter();
                        google.maps.event.trigger(map, "resize");
                        map.setCenter(center);
                    });
                });
            });
        }
    }

    /* scrollTo
    ================================================== */
    function _scrollTo ()
    {
        var jLink = $('.top-bar a[href*="#"]').not('.top-bar [href="#"]').not('.top-bar [href="#0"]'),
        nMenuToggler = document.getElementById('top-bar__navigation-toggler'),
        jMenuToggler = $(nMenuToggler);

        jLink.on('touchend click', function (e) {

            var $this = $(this).blur();

            if ( location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname )
                {
                    var target = $(this.hash);

                    target = target.length ? target : $('[name=' + this.hash.slice(1) +']');

                    if ( target.length )
                    {
                        $('html,body').stop().animate({
                            scrollTop: target.offset().top - iHeaderHeight
                        }, 1000);
                    }

                    if ( bNavAnchor && bMenuOpen )
                    {
                        jMenuToggler.click();
                    }

                    return false;
                }
            });
        }

        /* scroll to top
        ================================================== */
        function _scrollTop ()
        {
            var	nBtnToTopWrap = document.getElementById('btn-to-top-wrap'),
            jBtnToTopWrap = $(nBtnToTopWrap);

            if ( jBtnToTopWrap.length > 0 )
            {
                var nBtnToTop = document.getElementById('btn-to-top'),
                jBtnToTop = $(nBtnToTop),
                iOffset   = jBtnToTop.data('visible-offset');

                jBtnToTop.on('click', function (e) {
                    e.preventDefault();

                    $('body,html').stop().animate({ scrollTop: 0 } , 1500);

                    return false;
                });

                jWindow.on('scroll', throttle(function(e) {

                    if ( jWindow.scrollTop() > iOffset )
                    {
                        if ( jBtnToTopWrap.is(":hidden") )
                        {
                            jBtnToTopWrap.fadeIn();
                        }

                    }
                    else
                    {
                        if ( jBtnToTopWrap.is(":visible") )
                        {
                            jBtnToTopWrap.fadeOut();
                        }
                    }

                }, 400)).scroll();
            }
        }



        /* wavify
        ================================================== */
        function _wavify ()
        {

            var nWave = document.querySelectorAll('.js-wave');
            var nWave_mob = document.querySelectorAll('.js-wave.waves-mobile-on');

            if ( nWave_mob.length > 0 )
            {
                var defaultOptions = {
                    // Height of wave
                    height: 100,
                    // Amplitude of wave
                    amplitude: 90,
                    // Animation speed
                    speed: 0.15,
                    // Total number of articulation in wave
                    bones: 3,
                    // Color
                    color: "rgba(255,255,255, 1)"
                };

                [].forEach.call(nWave, function( wave, index, arr )
                {
                    var element = wave.getElementsByTagName('path'),
                    oData   = wave.getAttribute('data-wave') || {},
                    myWave;

                    if ( oData.length )
                    {
                        var dataOptions = JSON.parse(oData);

                        wave.options = Object.assign({}, defaultOptions, dataOptions);
                    }
                    else
                    {
                        wave.options = Object.assign({}, defaultOptions);
                    };

                    myWave = wavify(element, wave.options);
                });
            }
            else
            {
                if ( device.desktop() && nWave.length > 0 )
                {
                    var defaultOptions = {
                        // Height of wave
                        height: 100,
                        // Amplitude of wave
                        amplitude: 90,
                        // Animation speed
                        speed: 0.15,
                        // Total number of articulation in wave
                        bones: 3,
                        // Color
                        color: "rgba(255,255,255, 1)"
                    };

                    [].forEach.call(nWave, function( wave, index, arr )
                    {
                        var element = wave.getElementsByTagName('path'),
                        oData   = wave.getAttribute('data-wave') || {},
                        myWave;

                        if ( oData.length )
                        {
                            var dataOptions = JSON.parse(oData);

                            wave.options = Object.assign({}, defaultOptions, dataOptions);
                        }
                        else
                        {
                            wave.options = Object.assign({}, defaultOptions);
                        };

                        myWave = wavify(element, wave.options);
                    });
                };
            }
        }

        /* scroll top
        ================================================== */
        function _headertop ()
        {
            var after_header = $('.template-after-header-menu').size();

            if ( after_header ) {
                $('body').addClass('has-template-after-header-menu');
            }
        }

        $(document).ready(function() {

            /* header
            ================================================== */
            _header();

            /* choose lang
            ================================================== */
            _chooseLang();

            /* side menu toggle
            ================================================== */
            _sideMenuToggle();

            /* Vivus
            ================================================== */
            _setVivus();

            /* tilt
            ================================================== */
            _tilt();

            /* parallax
            ================================================== */
            _parallax();

            /* isotope sorting
            ================================================== */
            _isotopeSorting();

            /* slick slider
            ================================================== */
            _slickSlider();

            /* lightbox
            ================================================== */
            _fancybox();

            /* accordion
            ================================================== */
            _accordion();

            /* tabs
            ================================================== */
            _tabs();

            /* counters
            ================================================== */
            _counters();

            /* scroll to top
            ================================================== */
            _scrollTop();

            /* scroll to anchor
            ================================================== */
            _smoothScroll();

            /* scroll to top
            ================================================== */
            _sticked ();

            /* before header topbar
            ================================================== */
            _headertop ()

        });

        jWindow.on('load', function () {

            var jMasonry = $('.js-masonry');

            if ( jMasonry.length > 0 && $.fn.isotope )
            {
                jMasonry.masonry('layout');
            };

            /* scrollTo
            ================================================== */
            _scrollTo();

            /* wavify
            ================================================== */
            _wavify();

            /* google map
            ================================================== */
            _g_map();
        });

        $.fn.is_on_screen = function () {
            var viewport = {
                top: jWindow.scrollTop(),
                left: jWindow.scrollLeft()
            };
            viewport.right = viewport.left + jWindow.width();
            viewport.bottom = viewport.top + jWindow.height();

            var bounds = this.offset();
            bounds.right = bounds.left + this.outerWidth();
            bounds.bottom = bounds.top + this.outerHeight();

            return ( !( viewport.right < bounds.left ||
                viewport.left > bounds.right ||
                viewport.bottom < bounds.top ||
                viewport.top > bounds.bottom
            ));
        }

        // Create a safe reference to the Underscore object for use below.
        function now() {
            return new Date().getTime();
        }

        function throttle(func, wait, options)
        {
            var timeout, context, args, result;
            var previous = 0;

            if (!options) options = {};

            var later = function later()
            {
                previous = options.leading === false ? 0 : now();
                timeout = null;
                result = func.apply(context, args);
                if (!timeout) context = args = null;
            }

            var throttled = function throttled()
            {
                var at = now();
                if (!previous && options.leading === false) previous = at;
                var remaining = wait - (at - previous);
                context = this;
                args = arguments;
                if (remaining <= 0 || remaining > wait)
                {
                    if (timeout)
                    {
                        clearTimeout(timeout);
                        timeout = null;
                    }
                    previous = at;
                    result = func.apply(context, args);
                    if (!timeout) context = args = null;
                }
                else if (!timeout && options.trailing !== false)
                {
                    timeout = setTimeout(later, remaining);
                }
                return result;
            }

            throttled.cancel = function ()
            {
                clearTimeout(timeout);
                previous = 0;
                timeout = context = args = null;
            }

            return throttled;
        }

        //  Pure js debounce function to optimize resize method
        function debounce(func, wait, immediate)
        {
            var timeout;

            return function()
            {
                var context = this,
                args = arguments;

                clearTimeout(timeout);

                timeout = setTimeout(function() {
                    timeout = null;

                    if (!immediate) func.apply(context, args);
                }, wait);

                if (immediate && !timeout) func.apply(context, args);
            };
        }

        var classes = ["tag-item-one", "tag-item-two", "tag-item-three", "tag-item-four", "tag-item-five"];

        $(".tagcloud a,.nt-post-category-links li a, .tags-list ul li a").each(function(){
            $(this).addClass(classes[~~(Math.random()*classes.length)]);
        });

}(jQuery));
