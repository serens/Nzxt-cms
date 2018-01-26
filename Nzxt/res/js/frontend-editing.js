var Nzxt = Nzxt || {
    animation: {
        speed: 500,
        easing: 'easeOutExpo'
    },
    init: function() {
        // Create soms divs
        $('body')
            .append('<div id="cms-mask" class="cms" />')
            .append('<div id="cms-loading" class="cms"><div class="spinner"></div></div>')
            .append('<div id="cms-dialog" class="cms accent-background" />');

        // Assign handling for dialogs on node panel action links
        $('.cms.editable a.dialog').click(function (e) {
            e.preventDefault();

            var size = $(this).data('cms-dialog-size') ? parseFloat($(this).data('cms-dialog-size')) : 0.0;
            var side = $(this).data('cms-dialog-side') ? $(this).data('cms-dialog-side') : 'right';

            Nzxt.Dialog.open($(this).attr('href'), side, size);
        });

        // Init tooltip plugin
        $('body.accent-dark .cms.editable [title]').tooltip({
            showURL: false,
            fade: 250,
            delay: 350,
            id: 'cms-tooltip'
        });
    },
    showLoading: function(loading) {
        var $loading = $('#cms-loading')
        var size = $loading.height();

        if (loading) {
            $loading
                .css({top: -size})
                .show()
                .animate({top: 0}, Nzxt.animation.speed, Nzxt.animation.easing);
        } else {
            $loading
                .css({top: 0})
                .animate({top: -size}, Nzxt.animation.speed, Nzxt.animation.easing, function() {
                    $(this).hide();
                });
        }
    },
    showMask: function(visibility) {
        $('body').css('overflow', visibility ? 'hidden' : 'auto'); // Disable scrollbars on body

        visibility
            ? $('#cms-mask').show()
            : $('#cms-mask').fadeOut(this.animation.speed, this.animation.easing);
    }
};

/**
 * Dialog handler which can open a dialog.
 */
Nzxt.Dialog = {
    init: function() {
        this.container = $('#cms-dialog');
    },
    open: function(src, side, size) {
        if (size <= 0) {
            size = 0.3;
        }

        if (size > 1) {
            size = 1;
        }

        var width = Math.round(window.innerWidth * size);
        var $newIframe = $('<iframe />');

        Nzxt.showMask(true);
        Nzxt.showLoading(true);

        this.container
            .html('')
            .data('original-size', size)
            .data('maximized', false)
            .css({width: 0, opacity: 1})
            .show()
            .removeClass('left right')
            .addClass(side)
            .animate({width: width}, Nzxt.animation.speed, Nzxt.animation.easing);

        $newIframe
            .appendTo(this.container)
            .css({marginLeft: (side == 'left' ? -width : width) + 'px', width: '100%'})
            .on('load', function() {
                Nzxt.showLoading(false);
                $newIframe.animate({marginLeft: 0}, Nzxt.animation.speed, Nzxt.animation.easing);
            })
            .attr('src', src);
    },
    close: function(afterAnimation) {
        Nzxt.showMask(false);

        this.container.animate({width: 0}, Nzxt.animation.speed / 2, Nzxt.animation.easing, function() {
            $(this).html('').hide();

            if ('function' == typeof afterAnimation) {
                afterAnimation.call();
            }
        });
    },
    closeAndReload: function() {
        this.closeAndGoto(window.location.href);
    },
    closeAndGoto: function(location) {
        this.close(function() {
            window.location.assign(location);
        })
    },
    maximize: function() {
        var isMaximized = this.container.data('maximized') == true;
        var width = isMaximized ? parseFloat(this.container.data('original-size')) : 0.5;

        this.container
            .animate({width: Math.round(window.innerWidth * width)}, Nzxt.animation.speed, Nzxt.animation.easing)
            .data('maximized', !isMaximized);
    }
};

$(function() {
    Nzxt.init();
    Nzxt.Dialog.init();
});
