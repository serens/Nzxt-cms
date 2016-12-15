var $ = jQuery.noConflict();

$(document).foundation();

var WEPRO = WEPRO || {};

(function($){
    "use strict";

    WEPRO.initialize = {
        init: function(){
            WEPRO.initialize.responsiveClasses();
            WEPRO.initialize.scrollToTop();
            WEPRO.initialize.showTopScroll();
        },

        responsiveClasses: function(){
            var jRes = jRespond([
                {
                    label: 'small',
                    enter: 0,
                    exit: 479
                },{
                    label: 'handheld',
                    enter: 480,
                    exit: 767
                },{
                    label: 'tablet',
                    enter: 768,
                    exit: 991
                },{
                    label: 'laptop',
                    enter: 992,
                    exit: 1199
                },{
                    label: 'desktop',
                    enter: 1200,
                    exit: 10000
                }
            ]);

            jRes.addFunc([
                {
                    breakpoint: 'desktop',
                    enter: function() { $body.addClass('screen-lg'); },
                    exit: function() { $body.removeClass('screen-lg'); }
                },{
                    breakpoint: 'laptop',
                    enter: function() { $body.addClass('screen-md'); },
                    exit: function() { $body.removeClass('screen-md'); }
                },{
                    breakpoint: 'tablet',
                    enter: function() { $body.addClass('screen-sm'); },
                    exit: function() { $body.removeClass('screen-sm'); }
                },{
                    breakpoint: 'handheld',
                    enter: function() { $body.addClass('screen-xs'); },
                    exit: function() { $body.removeClass('screen-xs'); }
                },{
                    breakpoint: 'small',
                    enter: function() { $body.addClass('screen-xxs'); },
                    exit: function() { $body.removeClass('screen-xxs'); }
                }
            ]);
        },

        scrollToTop: function() {
            $scrollToTopEl.click(function() {
                $('body,html').stop(true).animate({scrollTop: 0}, 400);
                return false;
            });
        },

        showTopScroll: function() {
            if ($body.hasClass('screen-lg') || $body.hasClass('screen-md') || $body.hasClass('screen-sm') ) {
                ($window.scrollTop() > 450)
                    ? $scrollToTopEl.fadeIn()
                    : $scrollToTopEl.fadeOut();
            }
        }
    };

    WEPRO.head = {
        init: function(){
            WEPRO.head.showHeaderNav();
            WEPRO.head.misc();
        },

        showHeaderNav: function(){
            ($(window).scrollTop() > 100)
                ? $header.addClass('show-header-nav')
                : $header.removeClass('show-header-nav');
        },

        misc: function() {
            $('#primary-menu-trigger').click(function() {
                $primaryMenue.toggleClass('show');
                return false;
            });

            $primaryMenue.find('a').click(function(){
                $primaryMenue.removeClass('show');
            });

            if (WEPRO.isMobile.any()) {
                $body.addClass('touch-device');
            }
        }
    };

    WEPRO.isMobile = {
        Android: function() {
            return navigator.userAgent.match(/Android/i);
        },
        BlackBerry: function() {
            return navigator.userAgent.match(/BlackBerry/i);
        },
        iOS: function() {
            return navigator.userAgent.match(/iPhone|iPad|iPod/i);
        },
        Opera: function() {
            return navigator.userAgent.match(/Opera Mini/i);
        },
        Windows: function() {
            return navigator.userAgent.match(/IEMobile/i);
        },
        any: function() {
            return (WEPRO.isMobile.Android() || WEPRO.isMobile.BlackBerry() || WEPRO.isMobile.iOS() || WEPRO.isMobile.Opera() || WEPRO.isMobile.Windows());
        }
    };

    WEPRO.documentOnResize = {
        init: function() {
        }
    };

    WEPRO.documentOnReady = {
        init: function() {
            WEPRO.initialize.init();
            WEPRO.head.init();
            WEPRO.documentOnReady.windowscroll();
        },

        windowscroll: function() {
            $window.on('scroll', function(){
                WEPRO.initialize.showTopScroll();
                WEPRO.head.showHeaderNav();
            });
        }
    };

    WEPRO.documentOnLoad = {
        init: function(){
        }
    };

    var $window = $(window);
    var $singlePageNav = $('.single-page-nav');
    var $body = $('body');
    var $header = $('#header');
    var $primaryMenue = $('#primary-menu > ul');
    var $scrollToTopEl = $('#scrollToTop');

    $(document).ready(WEPRO.documentOnReady.init);
    $window.load(WEPRO.documentOnLoad.init);
    $window.on('resize', WEPRO.documentOnResize.init);
})(jQuery);

new scrollReveal({
    mobile: false,
    reset: false
});
