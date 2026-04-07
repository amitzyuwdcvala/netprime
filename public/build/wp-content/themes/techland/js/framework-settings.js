(function ($) {

    "use strict";

    // WPBakery responsive settings
    responsiveEl();
    function responsiveEl() {

        var matches = document.querySelectorAll("[data-res-css]");
        var resdata = [];

        jQuery.each(matches, function(index, item){

            var get_style = item.getAttribute("data-res-css");
            resdata.push(get_style);
            item.removeAttribute("data-res-css");

        });

        var css = resdata.join(""),
        head = document.head || document.getElementsByTagName('head')[0],
        style = document.createElement('style');

        style.type = 'text/css';
        style.setAttribute("data-type", "nt-shortcodes-custom-css");

        if (style.styleSheet){
            // This is required for IE8 and below.
            style.styleSheet.cssText = css;

        } else {

            style.appendChild(document.createTextNode(css));
        }

        head.appendChild(style);
    }

    function setBackgrounds() {
        var background_image_objects = $( "[data-cursor-bg-img]" );
        if ( background_image_objects.length ) {
            background_image_objects.each( function () {
                var background_image_object = $( this );
                var background_image_src = background_image_object.data( "cursorBgImg" );
                if ( background_image_src ) {
                    background_image_object.css( "background-image", "url(" + background_image_src + ")" );
                }
            });
        }
    }

    function setMagnificPopup() {

        var magnific_photo = $( ".magnific-photo" );
        var magnific_gallery = $( ".magnific-gallery" );
        var magnific_iframe = $( ".magnific-iframe" );
        var magnific_inline = $( ".magnific-inline" );
        var magnific_ajax = $( ".magnific-ajax" );

        if ( magnific_photo.length ) {
            magnific_photo.magnificPopup( {
                type: "image"
            });
        }

        if ( magnific_gallery.length ) {
            magnific_gallery.each( function() {
                $( this ).magnificPopup( {
                    delegate: "a",
                    type: "image",
                    gallery: {
                        enabled: true
                    }
                });
            });
        }
        if ( magnific_iframe.length ) {
            magnific_iframe.magnificPopup( {
                type: "iframe"
            });
        }
        if ( magnific_inline.length ) {
            magnific_inline.magnificPopup( {
                type: "inline"
            });
        }
        if ( magnific_ajax.length ) {
            magnific_ajax.magnificPopup( {
                type: "ajax"
            });
        }
    }

    // Functions
    function setFitvids() {
        var fitvids_objects = $( ".fitvids" );
        if ( fitvids_objects.length ) {
            fitvids_objects.fitVids();
        }
    }

    function setOwlCarousel() {
        var rtl = $('[dir="rtl"]').length ? true : false;
        var owl_objects = $( ".owl-carousel" );
        if ( owl_objects.length ) {
            owl_objects.each( function () {
                var owl_object = $( this );
                if ( owl_object.hasClass( "-js-carousel" ) ) {
                    var owl_options = owl_object.data();
                    owl_object.owlCarousel( {
                        items: owl_options.owlItems, // Default: 3
                        loop: owl_options.owlLoop,  // Default: false
                        center: owl_options.owlCenter,  // Default: false
                        rewind: owl_options.owlRewind,  // Default: false
                        mouseDrag: owl_options.owlMousedrag,  // Default: true
                        touchDrag: owl_options.owlTouchdrag,  // Default: true
                        pullDrag: owl_options.owlPulldrag,  // Default: true
                        freeDrag: owl_options.owlFreedrag,  // Default: false
                        margin: owl_options.owlMargin,  // Default: 0
                        stagePadding: owl_options.owlStagepadding,  // Default: 0
                        merge: owl_options.owlMerge,  // Default: false
                        mergeFit: owl_options.owlMergefit,  // Default: true
                        autoWidth: owl_options.owlAutowidth,  // Default: false
                        startPosition: owl_options.owlStartposition,  // Default: 0
                        rtl: rtl,  // Default: false
                        smartSpeed: owl_options.owlSmartspeed,  // Default: 250
                        fluidSpeed: owl_options.owlFluidspeed,  // Default: false
                        dragEndSpeed: owl_options.owlDragendspeed,  // Default: false
                        responsive: owl_options.owlResponsive,  // Default: {}
                        responsiveRefreshRate: owl_options.owlResponsiverefreshrate,  // Default: 200
                        responsiveBaseElement: owl_options.owlResponsivebaseelement,  // Default: window
                        fallbackEasing: owl_options.owlFallbackeasing,  // Default: "swing"
                        info: owl_options.owlInfo,  // Default: false
                        nestedItemSelector: owl_options.owlNesteditemselector,  // Default: false
                        itemElement: owl_options.owlItemelement,  // Default: "div"
                        stageElement: owl_options.owlStageelement,  // Default: "div"
                        refreshClass: owl_options.owlRefreshclass,  // Default: "owl-refresh"
                        loadedClass: owl_options.owlLoadedclass,  // Default: "owl-loaded"
                        loadingClass: owl_options.owlLoadingclass,  // Default: "owl-loading"
                        rtlClass: owl_options.owlRtlclass,  // Default: "owl-rtl"
                        responsiveClass: owl_options.owlResponsiveclass,  // Default: "owl-responsive"
                        dragClass: owl_options.owlDragclass,  // Default: "owl-drag"
                        itemClass: owl_options.owlItemclass,  // Default: "owl-item"
                        stageClass: owl_options.owlStageclass,  // Default: "owl-stage"
                        stageOuterClass: owl_options.owlStageouterclass,  // Default: "owl-stage-outer"
                        grabClass: owl_options.owlGrabclass,  // Default: "owl-grab"
                        autoRefresh: owl_options.owlAutorefresh, // Default: true
                        autoRefreshInterval: owl_options.owlAutorefreshinterval, // Default: 500
                        lazyLoad: owl_options.owlLazyload, // Default: false
                        autoHeight: owl_options.owlAutoheight, // Default: false
                        autoHeightClass: owl_options.owlAutoheightclass, // Default: "owl-height"
                        video: owl_options.owlVideo, // Default: false
                        videoHeight: owl_options.owlVideoheight, // Default: false
                        videoWidth: owl_options.owlVideowidth, // Default: false
                        animateOut: owl_options.owlAnimateout, // Default: false
                        animateIn: owl_options.owlAnimatein, // Default: false
                        autoplay: owl_options.owlAutoplay, // Default: false
                        autoplayTimeout: owl_options.owlAutoplaytimeout, // Default: 5000
                        autoplayHoverPause: owl_options.owlAutoplayhoverpause, // Default: false
                        autoplaySpeed: owl_options.owlAutoplayspeed, // Default: false
                        nav: owl_options.owlNav, // Default: false
                        navText: owl_options.owlNavtext, // Default: ["prev","next"]
                        navSpeed: owl_options.owlNavspeed, // Default: false
                        navElement: owl_options.owlNavelement, // Default: "div"
                        navContainer: owl_options.owlNavcontainer, // Default: false
                        navContainerClass: owl_options.owlNavcontainerclass, // Default: "owl-nav"
                        navClass: owl_options.owlNavclass, // Default: [ 'owl-prev', 'owl-next' ]
                        slideBy: owl_options.owlSlideby, // Default: 1
                        dotClass: owl_options.owlDotclass, // Default: "owl-dot"
                        dotsClass: owl_options.owlDotsclass, // Default: "owl-dots"
                        dots: owl_options.owlDots, // Default: true
                        dotsEach: owl_options.owlDotseach, // Default: false
                        dotsData: owl_options.owlDotsdata, // Default: false
                        dotsSpeed: owl_options.owlDotsspeed, // Default: false
                        dotsContainer: owl_options.owlDotscontainer, // Default: false
                        URLhashListener: owl_options.owlUrlhashlistener, // Default: false
                    });
                }
            });
        }
    }

    jQuery(document).ready(function( $ ) {

        // masonry

        var masonry_check = $('#masonry-container').size();

        if(masonry_check){

            //set the container that Masonry will be inside of in a var
            var container = document.querySelector('#masonry-container');
            //create empty var msnry
            var msnry;
            // initialize Masonry after all images have loaded
            imagesLoaded( container, function() {
                msnry = new Masonry( container, {
                    itemSelector: '.masonry-item'
                });
            });

        }

        // add class for header
        if ( $( ".start-screen--style-1,.start-screen--style-2,.start-screen--style-3,.start-screen--style-7,.start-screen--style-13" ).length ) {
            $( ".top-bar" ).removeClass( "top-bar--light" ).addClass( "top-bar--dark" );
        }

        $( ".nt-theme-content table, #wp-calendar" ).addClass( "table table-striped" );

        // vc_row parllax
        var parallaxbg= $('.nt-jarallax');

        if (parallaxbg > 0){

            $('.nt-jarallax').jarallax({

            });

            jarallax(document.querySelectorAll('.nt-jarallax.mobile-parallax-off'), {
                disableParallax: /iPad|iPhone|iPod|Android/,
                disableVideo: /iPad|iPhone|iPod|Android/
            });

        }

        // CF7 remove error message
        $('.wpcf7-response-output').ajaxComplete(function(){

            window.setTimeout(function(){
                $('.wpcf7-response-output').addClass('display-none');
            }, 4000); //<-- Delay in milliseconds

            window.setTimeout(function(){
                $('.wpcf7-response-output').removeClass('wpcf7-validation-errors display-none');
            }, 4500); //<-- Delay in milliseconds

        });

        $('#nt-sidebar select, .woocommerce-ordering select').niceSelect();

    }); // end ready

    // preloader
    $(window).load(function () {

        // Animate loader off screen
        $('#nt-preloader').fadeOut('slow', function () {
            $(this).remove();
        });

    });

})(jQuery);
