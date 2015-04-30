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
                        $('div.lg-flags').html($(data).find('div.lg-flags').html());
                    } else {
                        $('tbody.lg-tbody').html($(data).find('tbody.lg-tbody').html());
                        $('form.navbar-right').html($(data).find('form.navbar-right').html());
                        $('div#lg-footer').html($(data).find('div#lg-footer').html());
                        $('tr.lg-headers').html($(data).find('tr.lg-headers').html());
                        $('div.lg-flags').html($(data).find('div.lg-flags').html());
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
                    // make latest timer
                    clearTimeout(timer);
                }
            });
        },
        quiet
    );

}