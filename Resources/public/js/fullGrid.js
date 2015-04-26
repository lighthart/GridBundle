// '@LighthartGridBundle/Resources/public/js/grid.js'

// inhibits recurring callback for duration of quiet before executing
var quiet = 300; // 300 ms
var timer = 0;
var xhr = 0;

function getOffset() {
    cookies = getCookies();
    var pageVal = Number($('input.lg-page-input').val());
    var maxPages = Number($('#lg-max-pages').val());
    if (pageVal > maxPages) {
        pageVal = maxPages;
    }
    var numPerPage = Number(cookies.pageSize);
    var offset = (pageVal - 1) * numPerPage;
    offset = (offset < 0) ? 0 : offset;
    offset = ((offset / numPerPage) > maxPages) ? maxPages - (maxPages % numPerPage) : offset;
    return offset;
}

function getMaxPages() {
    var maxPages = Number($('#lg-max-pages').val());
    return maxPages;
}

function highlightSearches() {
    if ($('input#lg-search-input') && undefined != $('input#lg-search-input').val()) {
        $('td.lg-searchable').highlight($('input#lg-search-input').val().split(' '), {
            className: 'lg-highlight-searches'
        });
        if ( !! $('input#lg-search-input').val().trim()) {
            $('input#lg-search-input').addClass('lg-highlight-searches');
        } else {
            $('input#lg-search-input').removeClass('lg-highlight-searches');
        }
    }
}

function highlightFilters() {
    $('input.lg-filter-input').each(function(i) {
        var col = $(this).parent().index();
        $(this).closest("table").find("tr td:nth-child(" + (col + 1) + ")").highlight($(this).val(), {
            className: 'lg-highlight-filters'
        });
        if ( !! $(this).val().trim()) {
            $(this).addClass('lg-highlight-filters');
        } else {
            $(this).removeClass('lg-highlight-filters');
        }
    });
}

function getFlagCookies() {
    var cookies = {};
    $.map(getFlags(), function(value, flag) {
        var flagCookie = "lg-" + getLgCurrentRoute() + "-flag-" + flag;
        cookies[flag] = $.cookie(flagCookie);
    });

    return cookies;
}

function getCookies() {
    var ajaxVersionCookie = "lg-" + getLgCurrentRoute() + "-version";
    var numPerPagecookie = 'lg-results-per-page';
    var filterCookie = "lg-" + getLgCurrentRoute() + "-filter";
    var offsetCookie = "lg-" + getLgCurrentRoute() + "-offset";
    var searchCookie = "lg-" + getLgCurrentRoute() + "-search";
    var sortCookie = "lg-" + getLgCurrentRoute() + "-sort";
    var cookies = {
        filter: $.cookie(filterCookie),
        offset: $.cookie(offsetCookie),
        pageSize: $.cookie(numPerPagecookie),
        search: $.cookie(searchCookie),
        sort: $.cookie(sortCookie),
        version: $.cookie(ajaxVersionCookie),
    };

    // Setting Defaults
    if ('undefined' == typeof cookies.offset || isNaN(cookies.offset)) {
        cookies.offset = 0;
    }
    if ('undefined' == typeof cookies.pageSize || isNaN(cookies.pageSize)) {
        cookies.pageSize = 10;
    }
    return cookies;
}

function setFlagCookies() {
    $.map(getFlags(), function(value, flag) {
        var flagCookie = "lg-" + getLgCurrentRoute() + "-flag-" + flag;
        $.cookie(flagCookie, value);
    });
}

function setCookies(cookies) {
    var ajaxVersionCookie = "lg-" + getLgCurrentRoute() + "-version";
    var numPerPagecookie = 'lg-results-per-page';
    var filterCookie = "lg-" + getLgCurrentRoute() + "-filter";
    var offsetCookie = "lg-" + getLgCurrentRoute() + "-offset";
    var searchCookie = "lg-" + getLgCurrentRoute() + "-search";
    var sortCookie = "lg-" + getLgCurrentRoute() + "-sort";
    // var flagCookie = "lg-" + getLgCurrentRoute() + "-flags";
    $.cookie(filterCookie, cookies.filter);
    $.cookie(offsetCookie, cookies.offset);
    $.cookie(searchCookie, cookies.search);
    $.cookie(sortCookie, cookies.sort);
    $.cookie(numPerPagecookie, cookies.pageSize);
    $.cookie(ajaxVersionCookie, cookies.version);
    setFlagCookies();
}

function gridFocus() {
    var focus = null;
    $('input').each(function() {
        // o for dom 'o'bject
        if ($(this).is(':focus')) {
            focus = $(this);
        }
    });
    return focus;
}

function gridReload(reset) {

    reset = typeof reset !== 'undefined' ? reset : false;

    var oldFocus = null;
    var oldVersion = null;
    cookies = getCookies();
    data = {
        pageSize: cookies.pageSize,
        pageOffset: cookies.offset,
        filter: (cookies.filter ? cookies.filter : "").replace("'","''"),
        search: cookies.search,
    };


    $.map(getFlags(), function(value, flag) {
        var flagCookie = flag;
        if (value) {
            data[flagCookie] = value;
        }
    });

    if (xhr) {
        xhr.abort();
    }

    if (timer != null) {
        clearTimeout(timer);
    }

    timer = setTimeout(function() {
            xhr = $.ajax({
                url: getLgCurrentURI(),
                data: data,
                dataType: 'html',
                type: 'GET',
                cache: false,
                beforeSend: function(xhr) {
                    $('.lg-table').addClass('text-muted');
                    cookies = getCookies();
                    oldVersion = typeof cookies.version == 'undefined' ? 0 : cookies.version;
                    cookies.version = new Date().getTime();
                    setCookies(cookies);
                    oldFocus = gridFocus() ? '#' + gridFocus().attr('id') : 0;
                },
                success: function(data) {
                    if (reset) {
                        $('table.lg-table').html($(data).find('table.lg-table').html());
                        $('div#lg-header').html($(data).find('div#lg-header').html());
                        $('div#lg-footer').html($(data).find('div#lg-footer').html());
                    } else {
                        $('tbody.lg-tbody').html($(data).find('tbody.lg-tbody').html());
                        $('form.navbar-right').html($(data).find('form.navbar-right').html());
                        $('div#lg-footer').html($(data).find('div#lg-footer').html());
                        $('tr.lg-headers').html($(data).find('tr.lg-headers').html());
                    }
                },
                complete: function() {
                    highlightSearches();
                    highlightFilters();
                    activateControls();
                    $('.lg-table').removeClass('text-muted');
                    if (oldFocus) {
                        $(oldFocus).blur().focus().val($(oldFocus).val());
                    }
                    markFlags();
                    makeClicks();
                    // make latest timer
                    clearTimeout(timer);
                }
            });
        },
        quiet
    );

}

// '@LighthartGridBundle/Resources/public/js/gridHighlight.js'

/*
 * jQuery Highlight plugin
 *
 * Based on highlight v3 by Johann Burkard
 * http://johannburkard.de/blog/programming/javascript/highlight-javascript-text-higlighting-jquery-plugin.html
 *
 * Code a little bit refactored and cleaned (in my humble opinion).
 * Most important changes:
 *  - has an option to highlight only entire words (wordsOnly - false by default),
 *  - has an option to be case sensitive (caseSensitive - false by default)
 *  - highlight element tag and class names can be specified in options
 *
 * Usage:
 *   // wrap every occurrance of text 'lorem' in content
 *   // with <span class='highlight'> (default options)
 *   $('#content').highlight('lorem');
 *
 *   // search for and highlight more terms at once
 *   // so you can save some time on traversing DOM
 *   $('#content').highlight(['lorem', 'ipsum']);
 *   $('#content').highlight('lorem ipsum');
 *
 *   // search only for entire word 'lorem'
 *   $('#content').highlight('lorem', { wordsOnly: true });
 *
 *   // don't ignore case during search of term 'lorem'
 *   $('#content').highlight('lorem', { caseSensitive: true });
 *
 *   // wrap every occurrance of term 'ipsum' in content
 *   // with <em class='important'>
 *   $('#content').highlight('ipsum', { element: 'em', className: 'important' });
 *
 *   // remove default highlight
 *   $('#content').unhighlight();
 *
 *   // remove custom highlight
 *   $('#content').unhighlight({ element: 'em', className: 'important' });
 *
 *
 * Copyright (c) 2009 Bartek Szopka
 *
 * Licensed under MIT license.
 *
 */

jQuery.extend({
    highlight: function (node, re, nodeName, className) {
        if (node.nodeType === 3) {
            var match = node.data.match(re);
            if (match) {
                var highlight = document.createElement(nodeName || 'span');
                highlight.className = className || 'highlight';
                var wordNode = node.splitText(match.index);
                wordNode.splitText(match[0].length);
                var wordClone = wordNode.cloneNode(true);
                highlight.appendChild(wordClone);
                wordNode.parentNode.replaceChild(highlight, wordNode);
                return 1; //skip added node in parent
            }
        } else if ((node.nodeType === 1 && node.childNodes) && // only element nodes that have children
                !/(script|style)/i.test(node.tagName) && // ignore script and style nodes
                !(node.tagName === nodeName.toUpperCase() && node.className === className)) { // skip if already highlighted
            for (var i = 0; i < node.childNodes.length; i++) {
                i += jQuery.highlight(node.childNodes[i], re, nodeName, className);
            }
        }
        return 0;
    }
});

jQuery.fn.unhighlight = function (options) {
    var settings = { className: 'highlight', element: 'span' };
    jQuery.extend(settings, options);

    return this.find(settings.element + "." + settings.className).each(function () {
        var parent = this.parentNode;
        parent.replaceChild(this.firstChild, this);
        parent.normalize();
    }).end();
};

jQuery.fn.highlight = function (words, options) {
    var settings = { className: 'highlight', element: 'span', caseSensitive: false, wordsOnly: false };
    jQuery.extend(settings, options);

    if (words.constructor === String) {
        words = [words];
    }
    words = jQuery.grep(words, function(word, i){
      return word !== '';
    });
    words = jQuery.map(words, function(word, i) {
      return word.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&");
    });
    if (words.length == 0) { return this; };

    var flag = settings.caseSensitive ? "" : "i";
    var pattern = "(" + words.join("|") + ")";
    if (settings.wordsOnly) {
        pattern = "\\b" + pattern + "\\b";
    }
    var re = new RegExp(pattern, flag);

    return this.each(function () {
        jQuery.highlight(this, re, settings.element, settings.className);
    });
};

// '@LighthartGridBundle/Resources/public/js/gridPaging.js'

function pagingInputControl() {
    $('.lg-last-page').unbind('change');
    $('input.lg-page-input').on('change keyup', function(e) {
        e.preventDefault();
        cookies = getCookies();
        cookies.offset = cookies.offset ? cookies.offset : 0;
        var maxPages = Number($('#lg-max-pages').val());
        var newPage = Number($('#lg-page-input').val());
        if (newPage < 0) {
            cookies.offset = 0;
        } else if (newPage >= maxPages) {
            var maxResults = Number($('#lg-max-results').val());
            cookies.offset = maxResults - maxResults % Number(cookies.pageSize);
        } else {
            cookies.offset = (newPage - 1) * cookies.pageSize;
        }
        setCookies(cookies);
        delay(function() {
            pagingInputReload();
        }, quiet);
    });
}

function prevPageControl() {
    $('.lg-prev-page').unbind('click');
    $('.lg-prev-page').on('click', function(e) {
        e.preventDefault();
        cookies = getCookies();
        cookies.offset = cookies.offset ? cookies.offset : 0;
        cookies.offset = Number(cookies.offset) - Number(cookies.pageSize);
        if (cookies.offset < 0) {
            cookies.offset = 0;
        }
        setCookies(cookies);
        delay(function() {
            pagingInputReload();
        }, quiet);
    });
}

function nextPageControl() {
    $('.lg-next-page').unbind('click');
    $('.lg-next-page').on('click', function(e) {
        e.preventDefault();
        cookies = getCookies();
        cookies.offset = cookies.offset ? cookies.offset : 0;
        var maxResults = $('#lg-max-results').val();
        cookies.offset = Number(cookies.pageSize) + Number(cookies.offset);
        if (cookies.offset > maxResults) {
            cookies.offset -= maxResults % cookies.pageSize;
        }
        setCookies(cookies);
        delay(function() {
            pagingInputReload();
        }, quiet);
    });
}

function firstPageControl() {
    $('.lg-first-page').unbind('click');
    $('.lg-first-page').on('click', function(e) {
        e.preventDefault();
        cookies = getCookies();
        cookies.offset = 0;
        setCookies(cookies);
        pagingInputReload();
    });
}

function lastPageControl() {
    $('.lg-last-page').unbind('click');
    $('.lg-last-page').on('click', function(e) {
        e.preventDefault();
        cookies = getCookies();
        var maxResults = Number($('#lg-max-results').val());
        cookies.offset = maxResults - maxResults % Number(cookies.pageSize);
        setCookies(cookies);
        pagingInputReload();
    });
}

function pagingInputReload() {
    var cookies = getCookies();
    var pageVal = Number($('input.lg-page-input').val());
    var maxPages = Number(getMaxPages());
    var numPerPage = cookies.pageSize;
    offset = cookies.offset;
    offset = (offset < 0) ? 0 : offset;
    offset = ((offset / numPerPage) > maxPages) ? maxPages - (maxPages % numPerPage) : offset;
    if (pageVal < 1 || pageVal > maxPages) {
        if (pageVal < 1) {
            pageVal = 1;
        }
        if (pageVal > maxPages) {
            pageVal = maxPages;
        }
    } else {
        offset = numPerPage * (pageVal - 1);
        $('input.lg-page-input').val(pageVal);
        setCookies(cookies);
        gridReload();
    }
}

// '@LighthartGridBundle/Resources/public/js/gridPageSize.js'

function pageSizeControl() {
    // The control
    $('.lg-pagesize').unbind('click');
    $('.lg-pagesize').one('click', function(e) {
        e.preventDefault();
        pageSizeReload($(this));
    });
}

function pageSizeReload(control) {
   // The stuff being done
    var cookies = getCookies();
    var pageVal = Number($('input.lg-page-input').val());
    var maxPages = Number(getMaxPages());
    offset = getOffset(cookies);
    if (offset < cookies.pageSize) {
        cookies.offset = 0;
    }
    // map to the bottom control
    $('.lg-pagesize-button').html(control.html());
    // put the data into our javascript, for next time this is called
    // currentPageSize = Number($('#lg-results-per-page').val());
    $('#lg-results-per-page').val(control.attr('data-role-lg-pagesize'));
    cookies.pageSize = control.attr('data-role-lg-pagesize');
    setCookies(cookies);
    gridReload();
}

// '@LighthartGridBundle/Resources/public/js/gridSearch.js'

function getSearch() {
    var search = $('input#lg-search-input').val();
    return search;
}

function gridSearchControl() {
    $('input#lg-search-input').on('keyup change', function() {
        var cookies = getCookies();
        // reset offset on search
        cookies.offset = 0;
        cookies.search = getSearch();
        setCookies(cookies);
        gridReload();
    });
}

// '@LighthartGridBundle/Resources/public/js/gridFilter.js'

function getAllFilters() {
    var filter = '';
    $('.lg-filter-input').each(function(i, e) {
        var value = $(this).val();
        parent= $(this).parent();
        filter += parent.attr('data-role-lg-class') + '__' + parent.attr('data-role-lg-field') + ':' + value;
        if (parent.attr('data-role-lg-hidden')) {
            parent.attr('data-role-lg-hidden').split(';')
                .forEach(function(f) {
                    filter +=  '|'+parent.attr('data-role-lg-class') + '__' + f + ':' + value;

            });
        }
        filter +=";"
    });
    return filter;
}

function gridFilterControl() {
    $('input.lg-filter-input').on('keyup change', function() {
        var cookies = getCookies();
        // reset offset on filter
        cookies.offset = 0;
        cookies.filter = getAllFilters();
        setCookies(cookies);
        gridReload();
    });
}

function gridFilterToggleControl() {
    $('#lg-toggle-filter').unbind('click');
    $('#lg-toggle-filter').one('click', function(e) {
        e.preventDefault();
        var filterToggleCookie = 'lg-filter-toggle';
        if ($('.lg-filter').parent().hasClass('hide') || $('.lg-filter').hasClass('hide')) {
            // Open
            $.cookie(filterToggleCookie, 1);
            $('#lg-toggle-filter > span').removeClass('fa-chevron-down');
            $('#lg-toggle-filter > span').addClass('fa-chevron-up');
            $('.lg-filter').parent().removeClass('hide');
            $('.lg-filter').removeClass('hide');
        } else {
            // Closed
            $.cookie(filterToggleCookie, 0);
            $('#lg-toggle-filter > span').addClass('fa-chevron-down');
            $('#lg-toggle-filter > span').removeClass('fa-chevron-up');
            $('.lg-filter').parent().addClass('hide');
            $('.lg-filter').addClass('hide');
        }
        activateControls();
    });
}

// '@LighthartGridBundle/Resources/public/js/xtraActions.js'

function gridExtraActionsControl() {
    $('.lg-xtra-actions').on('click', function(e) {
        e.preventDefault();
        // this needs to be changed to find the relevant children
        if ($(this).siblings('.lg-xtra-action.hide').length) {
            showHiddenActions($(this));
        } else {
            hideVisibleActions($(this));
        }
    });
}

function showHiddenActions(control) {
    // Open
    control.children('span').removeClass('fa-caret-right');
    control.children('span').addClass('fa-caret-left');
    control.siblings('.lg-xtra-action').each(function() {
        $(this).removeClass('hide');
    });
    control.parent().attr('style','width:'+control.parent().children().length*24+'px;');
}

function hideVisibleActions(control) {
    // Close
    control.children('span').addClass('fa-caret-right');
    control.children('span').removeClass('fa-caret-left');
    control.siblings('.lg-xtra-action').each(function() {
        $(this).addClass('hide');
    });
    control.parent().attr('style','width:'+4*24+'px;');
}

// '@LighthartGridBundle/Resources/public/js/gridSort.js'

function getAllSorts() {
    var sorts = "";
    $('span.lg-sort').each(function(i, e) {
            sorts += $(this).parent().attr('data-role-lg-class') + '__' + $(this).parent().attr('data-role-lg-field') + ':' + $(this).children('input').val() + ';';
    });
    return sorts;
}

function gridSortControl() {
    $('span.lg-sort').on('click', function(e) {
        control = $(this);
        e.preventDefault();
        delay(function() {
            gridSortReload(control);
        }, quiet);
    });
}

function gridSortReload(control) {

    var order = control.children('input').val();
    $('.lg-sort').each(function() { $(this).val(''); });
    if (!order) {
        order = 'ASC';
    } else if (order == 'ASC') {
        order = 'DESC';
    } else {
        order = '';
    }
    control.children('input').val(order);

    var cookies = getCookies();
    cookies.offset = 0;
    cookies.sort = getAllSorts();
    setCookies(cookies);

    var th = control.closest('th');
    var thClass = th.attr('data-role-lg-class');
    var thField = th.attr('data-role-lg-field');

    gridReload();
}

// '@LighthartGridBundle/Resources/public/js/gridFlag.js'

function getFlags(){
    checks = $('input.lg-grid-flag');
    flags = {};
    $.map(checks, function( val, i ) {
        flag = $(val).attr('id').replace('lg-grid-flag-','');
        flags[flag] = $(val).is(':checked') ? 1 : 0;
    });

    return flags;
}

function gridFlagControl() {
    $('input.lg-grid-flag').on('click', function(e) {
        control = $(this);
        // e.preventDefault();
        delay(function() {
            gridFlagReload(control);
        }, quiet);
    });
}

function gridFlagReload(control) {
    var cookies = getCookies();
    // reset offset on search
    cookies.offset = 0;
    setFlagCookies();

    gridReload();
}

function markFlags(){
    var flags = getFlagCookies();
    $.map(flags, function( value, flag) {
        if (value == 1) {
            $('input#lg-grid-flag-'+flag).each(function(){this.checked = true;});
        } else {
            $('input#lg-grid-flag-'+flag).each(function(){this.checked = false;});
        }
    });
}

// '@LighthartGridBundle/Resources/public/js/gridReset.js'

function gridResetControl() {
    $('a.lg-reset').on('click', function(e) {
        control = $(this);
        e.preventDefault();
        $('input.lg-filter').each(function(){
            $(this).val('');
        });
        $('input.lg-search').val('');
        $('.lg-grid-flag').each(function(){this.checked = false;});

        delay(function() {
            gridResetReload(control);
        }, quiet);
    });
}

function gridResetReload(control) {
    var ajaxVersionCookie = "lg-" + getLgCurrentRoute() + "-version";
    var numPerPagecookie = 'lg-results-per-page';
    var filterCookie = "lg-" + getLgCurrentRoute() + "-filter";
    var offsetCookie = "lg-" + getLgCurrentRoute() + "-offset";
    var searchCookie = "lg-" + getLgCurrentRoute() + "-search";
    var sortCookie = "lg-" + getLgCurrentRoute() + "-sort";
    var filterToggleCookie = 'lg-filter-toggle';
    $.cookie(filterToggleCookie, 1);
    $.removeCookie(searchCookie);
    $.removeCookie(filterCookie);
    $.removeCookie(offsetCookie);

    $.map(getFlags(), function(value, flag) {
        var flagCookie = "lg-" + getLgCurrentRoute() + "-flag-" + flag;
        $.removeCookie(flagCookie);
    });

    gridReload(new Date().getTime(), true);
}

// '@LighthartGridBundle/Resources/public/js/gridControls.js'
// $(document).ready moved to below

function activateControls() {
    var cookies = getCookies();
    $('input#lg-search-input').val(cookies.search);
    filter = cookies.filter;
    if (filter) {
        filters = filter.split(';');
        var index;
        for (index = 0; index < filters.length; ++index) {
            f = filters[index].split('|')[0];
            var filterId = f.split(':')[0];
            var filterVal = f.split(':')[1];
            if (filterVal) {
                $('#lg-filter-'+filterId).val(filterVal);
            }
        }
    }
    pageSizeControl();
    pagingInputControl();
    prevPageControl();
    nextPageControl();
    firstPageControl();
    lastPageControl();
    gridSearchControl();
    gridFilterControl();
    gridFilterToggleControl();
    highlightFilters();
    highlightSearches();
    gridExtraActionsControl();
    gridSortControl();
    gridFlagControl();
    gridResetControl();
    markFlags();
}

// '@LighthartGridBundle/Resources/public/js/tdEdit.js'
// $(document).ready moved to below
function makeClickable(object) {
    object.on('click', function() {
        if ($('input#cell.cell').length > 0) {
        } else {
            var th = object.closest('table').find('th').eq(object.index());
            var td = object;
            if (typeof td.attr('data-role-lg-editable') != 'undefined' && td.attr('data-role-lg-editable')) {
                makeEditable(object);
            }
        }
    });
}

function makeEditable(object) {
    // this function loads the input field and associated controls
    var original = object.text().trim();
    object.off('click');
    input=object.children('input');
    $('.lg-editing').each(function() {
        var val = $(this).children('input').val();
    });
    object.addClass('lg-editing');
    var th = object.closest('table').find('th').eq(object.index());
    if (object.attr('data-role-lg-entity-id')) {
        url = makeURLfromTD(object, 'edit');
    } else {
        url = makeURLfromTD(object, 'new');
    }
    object.load(url, null, function(responseText, textStatus, XMLHttpRequest) {
        if ('GRID_CONFIG_ERROR' == responseText) {
            object.text(original);
        } else {
            $('input#cell').on('keydown', function(event) {
                // this tells us which key is pressed
                // keep in comments if more functionality
                // becomes required
                var tab = 9;
                var enter = 13;
                var escape = 27;
                // var left   = 37
                // var up     = 38;
                // var right  = 39;
                // var down   = 40;
                if (event.which == escape) {
                    // rewrite original value of ajax loaded input
                    update(object, original, original);
                    makeClickable(object);
                }
                if (event.which == tab || event.which == enter) {
                    var val = object.children('input').val();
                    update(object, original, val);
                    if (event.which == enter) {
                        // This needs to be rethought
                        // Automatic traversal down on enter ?

                        // if (object.closest('tr').is(':last-child')) {
                        //     // We are at bottom of column
                        //     makeEditable(
                        //         // get tr
                        //         object.closest('tbody').find('tr')
                        //         // find the first row
                        //         // because we were at the end
                        //         // and need to get to the beginning
                        //         .first()
                        //         // find the index that matches our td,
                        //         // and then get the next one
                        //         .find('td').eq(object.index()).next());
                        // } else {
                        //     // We are NOT at bottom of column
                        //     makeEditable(
                        //         // get tr
                        //         object.closest('tr')
                        //         // get next row
                        //         .next()
                        //         // find td that has same index
                        //         // to keep in same column
                        //         .find('td').eq(object.index()));
                        // }
                    }
                    // if (event.which == tab) {
                    //     if (object.is(':last-child')) {
                    //         // we are at end of row
                    //         thdr = object.closest('table').find('th');
                    //         var nextth = object.closest('table').find('th').first();
                    //         while (typeof nextth.attr('data-role-lg-editable') != 'undefined' && th.attr('data-role-lg-editable')) {
                    //             // keep going until one can be editted
                    //             nextth = nextth.next();
                    //         }
                    //         makeEditable(object.closest('tr')
                    //             // get next row
                    //             .next()
                    //             // find td that has same index
                    //             // to keep in same column
                    //             .find('td').eq(nextth.index()));
                    //     } else {
                    //         var nextth = object.closest('table').find('th').eq(object.index()).next();
                    //         while (typeof nextth.attr('data-role-lg-editable') != 'undefined' && th.attr('data-role-lg-editable')) {
                    //             // keep going until one can be editted
                    //             nextth = nextth.next();
                    //         }
                    //         // we are NOT at end of row
                    //         makeEditable(object.closest('tr')
                    //             // find td that has same index
                    //             // to keep in same column
                    //             .find('td').eq(nextth.index()));
                    //     }
                    // }
                }
            });
            object.children('input').focus();
        }
    });
}

function update(object, original, val) {
    object.removeClass('lg-editing');
    var th = object.closest('table').find('th').eq(object.index());
    if ( val == '' || val ==null ) {
        // not string safe...
        val = 0;
    }
    if (val == original) {
        object.text(val);
    } else {
        // This over writes the input field
        object.text(val);
        if (object.attr('data-role-lg-new') && !object.attr('data-role-lg-entity-id')) {
            url = makeURLfromTD(object, 'create');
            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    data: val
                },
                success: function(responseText, textStatus, XMLHttpRequest) {

                    $('.lg-table').addClass('text-muted');
                    location.reload(true);
                }
                // dataType : dataType
            });

        } else {
            url = makeURLfromTD(object, 'update');
            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    // possible as form fields?
                    // eg data: {object.attr('data-role-lg-field'):val}
                    data: val
                },
                success: function(responseText, textStatus, XMLHttpRequest) {
                    // console.log(val);
                    // console.log(url);
                    $('.lg-table').addClass('text-muted');
                    location.reload(true);
                    // object.load(url, null);
                }
                // dataType : dataType
            });
        }
    }
    // reenable clickyness
    makeClickable(object);
}

function makeURLfromTD(td, action) {
    var th = td.closest('table').find('th').eq(td.index());
    var thid = th.attr('data-role-lg-parent-entity-id');
    // td must have data-role-entity-id
    // or
    // tr must have data-role-parent-entity-id
    // the second case should be used for grid that expose
    // a single entity only
    // the ../../../ is based on bundle config
    // try to make that more extensible
    var tr = td.closest('tr');
    var trid = tr.attr('data-role-lg-parent-entity-id');
    var tdid = td.attr('data-role-lg-entity-id');
    if (action == 'update' && td.attr('data-role-lg-update')) {
        url = td.attr('data-role-lg-update').replace('~entity_id~', td.attr('data-role-lg-entity-id')).replace('~col_id~', thid).replace('~row_id~', trid);
    } else if (action == 'create' && td.attr('data-role-lg-new')) {
        url = td.attr('data-role-lg-new').replace('~entity_id~', td.attr('data-role-lg-entity-id')).replace('~col_id~', thid).replace('~row_id~', trid);
    } else if (action == 'new') {
        url = getLgAppRoot() + 'cell/' + action + '/' + th.attr('data-role-lg-class') + '/' + th.attr('data-role-lg-field');
    } else if (typeof tdid != 'undefined' && tdid) {
     // otherwise try to figure it out yourself

        url = getLgAppRoot() + 'cell/' + action + '/' + th.attr('data-role-lg-class') + '/' + th.attr('data-role-lg-field') + '/' + tdid;
    } else {
        url = getLgAppRoot() + 'cell/' + action + '/' + th.attr('data-role-lg-class') + '/' + th.attr('data-role-lg-field') + '/' + trid;
    }

    if (url.indexOf('___') != -1){
        url = url.split('___');
        url[0]=url[0].substr(0,url[0].lastIndexOf('/')+1);
        url=url.join('');
    }
    return url;
}

function makeClicks(){
    $('td').each(function() {
        makeClickable($(this));
    });
}
// Document.readys removed and recombined:

$(document).ready(function() {
    activateControls();
    cookies = getCookies();
    makeClicks();
});