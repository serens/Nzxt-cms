var DialogController = DialogController || {
    isDialog: false,
    initHeader: function() {
        var $header = $('.dialog-header');
        var $dialogCloseToggle = $('a.dialog-close', $header);
        var $helpToggle = $('a.toggle-help', $header);
        var $sizeToggle = $('a.toggle-panelsize', $header);
        var $helpItems = $('.help-item');
        var $window = $(window);

        // Assign handler to cancel icon
        if (this.isDialog) {
            $dialogCloseToggle.click(function(e) {
                e.preventDefault();

                parent.Nzxt.Dialog.close();
            });
        } else {
            $dialogCloseToggle.hide();
        }

        // Assign handler to show help toggle icon
        if (0 == $helpItems.length) {
            $helpToggle.hide();
        } else {
            $helpToggle.click(function(e) {
                e.preventDefault();
                $(this).toggleClass('active').blur();
                $helpItems.stop().fadeToggle();
            });
        }

        // Assign handler to resize dialog panel
        $sizeToggle.click(function(e) {
            e.preventDefault();
            $(this).toggleClass('active').blur();
            parent.Nzxt.Dialog.maximize();
        });

        // Add eventHandler for scroll event
        $window.on('scroll', function() {
            ($window.scrollTop() > 25)
                ? $header.addClass('small')
                : $header.removeClass('small');
        });
    },
    initDialogButtons: function() {
        // Assign handler to submit and cancel buttons
        $('button[data-dialog-button-type="submit"]').click(function() {
            // Send form data via ajax request and handle the response
            var $form = $(this).parents('form');

            if (!$form.length) {
                return;
            }

            parent.Nzxt.showLoading(true);

            $.ajax($form.attr('action'), {
                method: $form.attr('method').toUpperCase(),
                data: new FormData($form[0]),
                processData: false,
                cache: false,
                dataType: 'json',
                contentType: false,
                success: function(data, textStatus, jqXHR) {
                    if ('ok' == data.status) {
                        if ('undefined' != typeof data.action && 'reload' == data.action) {
                            parent.Nzxt.Dialog.closeAndReload();
                        } else {
                            parent.Nzxt.Dialog.close();
                        }
                    }
                },
                always: function() {
                    parent.Nzxt.showLoading(false);
                }
            });
        });

        $('button[data-dialog-button-type="close"]').click(function() {
            parent.Nzxt.Dialog.closeAndReload();
        });

        $('button[data-dialog-button-type="cancel"]').click(function() {
            parent.Nzxt.Dialog.close();
        });
    },
    initVendorPlugins: function() {
        // Init tooltip plugin
        $('.cms [title]').tooltip({
            showURL: false,
            fade: 250,
            delay: 350,
            id: 'cms-tooltip'
        });
    },
    init: function() {
        this.isDialog = (parent.Nzxt);

        this.initHeader();
        this.initDialogButtons();
        this.initVendorPlugins();
    },
    setEnableBodyScroll: function(enable) {
        // User inputs which have scrollable content can make use of this function
        $('body').css('overflowY', enable ? 'auto' : 'hidden');
    }
};

$(function() {
    DialogController.init();
});
