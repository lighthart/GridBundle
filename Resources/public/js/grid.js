// inhibits recurring callback for duration of quiet before executing
var quiet = 300; // 300 ms
var delay = (function() {
    var timer = 0;
    return function(callback, ms) {
        clearTimeout(timer);
        timer = setTimeout(callback, ms);
    };
})();
// eg:
// var quiet = 100; // 100 ms
// delay(function(){ sidebar.finishMove();}, quiet)
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
    $('td.lg-searchable').highlight($('input#lg-search-input').val().split(' '), {
        className: 'lg-highlight-searches'
    });
    if ( !! $('input#lg-search-input').val().trim()) {
        $('input#lg-search-input').addClass('lg-highlight-searches');
    } else {
        $('input#lg-search-input').removeClass('lg-highlight-searches');
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

function getCookies() {
    var ajaxVersionCookie = "lg-" + getLgCurrentRoute() + "-version";
    var numPerPagecookie = 'lg-results-per-page';
    var filterCookie = "lg-" + getLgCurrentRoute() + "-filter";
    var offsetCookie = "lg-" + getLgCurrentRoute() + "-offset";
    var searchCookie = "lg-" + getLgCurrentRoute() + "-search";
    var sortCookie = "lg-" + getLgCurrentRoute() + "-sort";
    //reset pagination upon filtering
    var cookies = {
        filter: $.cookie(filterCookie),
        offset: $.cookie(offsetCookie),
        pageSize: $.cookie(numPerPagecookie),
        search: $.cookie(searchCookie),
        sort: $.cookie(sortCookie),
        version: $.cookie(ajaxVersionCookie)
    };

    // Setting Defaults

    if ( 'undefined' == typeof cookies.offset || isNaN(cookies.offset) ) { cookies.offset = 0;}
    if ( 'undefined' == typeof cookies.pageSize || isNaN(cookies.pageSize) ) { cookies.pageSize = 10;}

    return cookies;
}

function setCookies(cookies) {
    var ajaxVersionCookie = "lg-" + getLgCurrentRoute() + "-version";
    var numPerPagecookie = 'lg-results-per-page';
    var filterCookie = "lg-" + getLgCurrentRoute() + "-filter";
    var offsetCookie = "lg-" + getLgCurrentRoute() + "-offset";
    var searchCookie = "lg-" + getLgCurrentRoute() + "-search";
    var sortCookie = "lg-" + getLgCurrentRoute() + "-sort";
    $.cookie(filterCookie, cookies.filter);
    $.cookie(offsetCookie, cookies.offset);
    $.cookie(searchCookie, cookies.search);
    $.cookie(sortCookie, cookies.sort);
    $.cookie(numPerPagecookie, cookies.pageSize);
    $.cookie(ajaxVersionCookie, cookies.version);
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

function gridReload() {
    var oldFocus = null;
    var oldVersion = null;
    cookies = getCookies();
    $.ajax({
        url: getLgCurrentURI(),
        data: {
            pageSize: cookies.pageSize,
            pageOffset: cookies.offset,
            filter: cookies.filter,
            search: cookies.search,
        },
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
            $('table.lg-table').html($(data).find('table.lg-table').html());
            $('div#lg-header').html($(data).find('div#lg-header').html());
            $('div#lg-footer').html($(data).find('div#lg-footer').html());
        },
        complete: function() {
            highlightSearches();
            highlightFilters();
            activateControls();
            $('.lg-table').removeClass('text-muted');
            if (oldFocus) {
                $(oldFocus).blur().focus().val($(oldFocus).val());
            }
        }
    });
}