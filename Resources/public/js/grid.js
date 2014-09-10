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
    var pageVal = Number($('input.lg-grid-page-input').val());
    var maxPages = Number($('#lg-grid-max-pages').val());
    if (pageVal > maxPages) {
        pageVal = maxPages;
    }
    var numPerPage = Number(cookies.pageSize);
    var offset = (pageVal - 1) * numPerPage;
    console.log(offset);
    offset = (offset < 0) ? 0 : offset;
    offset = ((offset / numPerPage) > maxPages) ? maxPages - (maxPages % numPerPage) : offset;
    return offset;
}

function getMaxPages() {
    var maxPages = Number($('#lg-grid-max-pages').val());
    return maxPages;
}

function getSearch() {
    var search = $('input#lg-grid-search-input').val();
    return search;
}

function getAllFilters() {
    var filter = "";
    $('.lg-grid-filter-input').each(function(i, e) {
        filter += $(this).parent().attr('data-role-lg-class') + '__' + $(this).parent().attr('data-role-lg-field') + ':' + $(this).val() + ';';
    });
    return filter;
}

function highlightSearches() {
    $('td.lg-grid-searchable').highlight($('input#lg-grid-search-input').val().split(' '), {
        className: 'lg-grid-highlight-searches'
    });
    if ( !! $('input#lg-grid-search-input').val().trim()) {
        $('input#lg-grid-search-input').addClass('lg-grid-highlight-searches');
    } else {
        $('input#lg-grid-search-input').removeClass('lg-grid-highlight-searches');
    }
}

function highlightFilters() {
    $('input.lg-grid-filter-input').each(function(i) {
        var col = $(this).parent().index();
        $(this).closest("table").find("tr td:nth-child(" + (col + 1) + ")").highlight($(this).val(), {
            className: 'lg-grid-highlight-filters'
        });
        if ( !! $(this).val().trim()) {
            $(this).addClass('lg-grid-highlight-filters');
        } else {
            $(this).removeClass('lg-grid-highlight-filters');
        }
    });
}

function getCookies() {
    var debugCookie = 'lg-grid-debug';
    var numPerPagecookie = 'lg-grid-results-per-page';
    var offsetCookie = "lg-grid-" + getLgCurrentRoute() + "-offset";
    var searchCookie = "lg-grid-" + getLgCurrentRoute() + "-search";
    var filterCookie = "lg-grid-" + getLgCurrentRoute() + "-filter";
    //reset pagination upon filtering
    var cookies = {
        debug: $.cookie(debugCookie),
        offset: $.cookie(offsetCookie),
        filter: $.cookie(filterCookie),
        search: $.cookie(searchCookie),
        pageSize: $.cookie(numPerPagecookie)
    };
    return cookies;
}

function setCookies(cookies) {
    var debugCookie = 'lg-grid-debug';
    var numPerPagecookie = 'lg-grid-results-per-page';
    var offsetCookie = "lg-grid-" + getLgCurrentRoute() + "-offset";
    var searchCookie = "lg-grid-" + getLgCurrentRoute() + "-search";
    var filterCookie = "lg-grid-" + getLgCurrentRoute() + "-filter";
    $.cookie(debugCookie, 1);
    $.cookie(offsetCookie, cookies.offset);
    $.cookie(filterCookie, cookies.filter);
    $.cookie(searchCookie, cookies.search);
    $.cookie(numPerPagecookie, cookies.pageSize);
}

function gridReload() {
    $('.lg-grid-table').addClass('text-muted');
    cookies = getCookies();
    $.ajax({
        url: getLgCurrentURI(),
        data: {
            debug: cookies.debug,
            pageSize: cookies.pageSize,
            pageOffset: cookies.offset,
            filter: cookies.filter,
            search: cookies.search
        },
        dataType: 'html',
        type: 'GET',
        complete: function() {
            activateControls();
        },
        success: function(data) {
            $('table.lg-grid-table').html($(data).find('table.lg-grid-table').html());
            $('div#lg-grid-header').html($(data).find('div#lg-grid-header').html());
            $('div#lg-grid-footer').html($(data).find('div#lg-grid-footer').html());
            $('.lg-grid-table').removeClass('text-muted');
            highlightSearches();
            highlightFilters();
            $('#lg-grid-search-input').blur().focus().val($('#lg-grid-search-input').val());
        }
    });
}