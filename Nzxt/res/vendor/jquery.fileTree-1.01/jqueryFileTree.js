// jQuery File Tree Plugin
//
// Version 1.01
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
// 24 March 2008
//
// Visit http://abeautifulsite.net/notebook.php?article=58 for more information
//
// Usage: $('.fileTreeDemo').fileTree( options, callback )
//
// Options:  root           - root folder to display; default = /
//           script         - location of the serverside AJAX file to use; default = jquery.filetree.php
//           folderEvent    - event to trigger expand/collapse; default = click
//           expandSpeed    - default = 500 (ms); use -1 for no animation
//           collapseSpeed  - default = 500 (ms); use -1 for no animation
//           expandEasing   - easing function to use on expand (optional)
//           collapseEasing - easing function to use on collapse (optional)
//           loadMessage    - Message to display while initial tree loads (can be HTML)
//
// History:
//
// 1.01 - updated to work with foreign characters in directory/file names (12 April 2008)
// 1.00 - released (24 March 2008)
//
// TERMS OF USE
//
// jQuery File Tree is licensed under a Creative Commons License and is copyrighted (C)2008 by Cory S.N. LaViska.
// For details, visit http://creativecommons.org/licenses/by/3.0/us/
//
if (jQuery) (function($){
    $.extend($.fn, {
        fileTree: function(o) {
            // Defaults
            o = o || {};

            if (o.root == undefined) o.root = '/';
            if (o.script == undefined) o.script = 'jquery.filetree.php';
            if (o.folderEvent == undefined) o.folderEvent = 'click';
            if (o.expandSpeed == undefined) o.expandSpeed = 0;
            if (o.collapseSpeed == undefined) o.collapseSpeed = 0;
            if (o.expandEasing == undefined) o.expandEasing = null;
            if (o.collapseEasing == undefined) o.collapseEasing = null;

            // Modifications
            if (o.scriptParamName == undefined) o.scriptParamName = 'dir';
            if (o.onDataLoaded == undefined) o.onDataLoaded = function (data) { return data };
            if (o.onBeforeLoad == undefined) o.onBeforeLoad = $.noop;
            if (o.onAfterLoad == undefined) o.onAfterLoad = $.noop;
            if (o.onSelect == undefined) o.onSelect = $.noop;
            if (o.triggerSelector == undefined) o.triggerSelector = 'li a';
            if (o.returnType == undefined) o.returnType = 'json';
            if (o.waitClass == undefined) o.waitClass = 'wait';
            if (o.persist == undefined) o.persist = false;
            if (o.cookieName == undefined) o.cookieName = 'fileTree';

            $(this).each(function() {
                var $container = $(this);

                /**
                 * Loads data via Ajax, modifies it using a callack and inserts the resulting markup into container $c.
                 * @param $c
                 * @param root
                 */
                function loadTree($c, root) {
                    o.onBeforeLoad.call();

                    $c.addClass(o.waitClass);
                    $('.jqueryFileTree.start').remove();

                    $.post(o.script, o.scriptParamName + '=' + root, function(data) {
                        data = o.onDataLoaded(data); // Pass data to modify-Callback

                        $c.find('.start').html('');
                        $c.removeClass(o.waitClass).append(data);

                        (o.root == root)
                            ? $c.find('UL:hidden').show()
                            : $c.find('UL:hidden').slideDown({ duration: o.expandSpeed, easing: o.expandEasing });

                        bindTree($c);
                    }, o.returnType);
                }

                /**
                 * Binds click events on new inserted markup.
                 * @param $c
                 */
                function bindTree($c) {
                    $c.find(o.triggerSelector).bind(o.folderEvent, function(e) {
                        e.preventDefault();

                        if ($(this).parent().hasClass('collapsed')) {
                            // Expand
                            $(this).parent().find('UL').remove(); // Cleanup
                            loadTree($(this).parent(), $(this).attr('rel'));
                            $(this).parent().removeClass('collapsed').addClass('expanded');
                        } else {
                            // Collapse
                            $(this).parent().find('UL').slideUp({ duration: o.collapseSpeed, easing: o.collapseEasing });
                            $(this).parent().removeClass('expanded').addClass('collapsed');
                        }
                    });

                    // Apply caption click callback
                    $c.find('a.caption').click(function(e) {
                        o.onSelect($(this), e);
                    });

                    // Apply callback when data has been inserted into the DOM
                    o.onAfterLoad($c);

                    // Persis current state
                    if (o.persist) {
                        persist($container);
                    }
                }

                /**
                 * Reads the value of a cookie.
                 * @param name
                 * @returns String
                 */
                function readCookie(name) {
                    return (name = new RegExp('(?:^|;\\s*)' + ('' + name).replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&') + '=([^;]*)').exec(document.cookie)) && name[1];
                }

                /**
                 * Persists the current state by collecting all expanded nodes and storing there ids into a cookie.
                 * @param $c
                 */
                function persist($c) {
                    var idList = [];

                    $('li.expanded', $c).each(function() {
                        var $idHolder = $(this).children(o.triggerSelector).eq(0);

                        if ($idHolder) {
                            idList.push($idHolder.attr('rel'));
                        }
                    });

                    document.cookie = o.cookieName + '=' + idList.join('::');
                }

                /**
                 * Restores the last state of the file tree by getting all expanded node ids from a cookie.
                 * @param $c
                 */
                function restore($c) {
                    var lastState = readCookie(o.cookieName);

                    if (!lastState) {
                        return;
                    }

                    var ids = lastState.split('::');

                    /*
                    var createCallback = function() {
                        if (ids.length) {
                            return loadTree($c, ids.shift(), createCallback())
                        }
                    }

                    createCallback();
                    */
                }

                // Loading message
                $container.html('<ul class="jqueryFileTree start"><li class="' + o.waitClass + '"></li></ul>');

                // Get the initial file list
                loadTree($container, o.root);

                // Get last state, if available
                if (o.persist) {
                    restore($container);
                }
            });
        }
    });
})(jQuery);
