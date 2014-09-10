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
function getNumPerPage() {
    var cookie = 'lg-grid-results-per-page';
    var numPerPage = $.cookie(cookie);
    if (numPerPage === undefined || numPerPage === null) {
        numPerPage = 10;
    }
    return numPerPage;
}

function getOffset() {
    var cookie = "lg-grid-" + getLgCurrentRoute() + "-offset";
    var pageVal = Number($('input.lg-grid-page-input').val());
    var maxPages = Number($('#lg-grid-max-pages').val());
    if (pageVal > maxPages) {
        pageVal = maxPages;
    }
    var numPerPage = Number(getNumPerPage());
    var offset = (pageVal - 1) * numPerPage;
    offset = (offset < 0) ? 0 : offset;
    offset = ((offset / numPerPage) > maxPages) ? maxPages - (maxPages % numPerPage) : offset;
    $.cookie(cookie, offset);
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

function getFilter(field) {
    var filter = $('input#lg-grid-filter-' + field).val();
    return field + ':' + filter;
}

function getAllFilters() {
    var filter = "";
    $('.lg-grid-filter-input').each(function(i, e) {
        console.log($(this));
        filter += $(this).parent().attr('data-role-lg-class') + '_' + $(this).parent().attr('data-role-lg-field') + ':' + $(this).val() + ';';
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
        console.log($(this).val());
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
    var numPerPagecookie = 'lg-grid-results-per-page';
    var offsetCookie = "lg-grid-" + getLgCurrentRoute() + "-offset";
    var searchCookie = "lg-grid-" + getLgCurrentRoute() + "-search";
    var filterCookie = "lg-grid-" + getLgCurrentRoute() + "-filter";
    //reset pagination upon filtering
    var cookies = {
        offset: $.cookie(offsetCookie),
        filter: $.cookie(filterCookie),
        search: $.cookie(searchCookie),
        pageSize: $.cookie(numPerPagecookie)
    };
    return cookies;
}

function setCookies(cookies) {
    var numPerPagecookie = 'lg-grid-results-per-page';
    var offsetCookie = "lg-grid-" + getLgCurrentRoute() + "-offset";
    var searchCookie = "lg-grid-" + getLgCurrentRoute() + "-search";
    var filterCookie = "lg-grid-" + getLgCurrentRoute() + "-filter";
    //reset pagination upon filtering
    $.cookie(offsetCookie, cookies.offset),
    $.cookie(filterCookie, cookies.filter),
    $.cookie(searchCookie, cookies.search),
    $.cookie(numPerPagecookie, cookies.pageSize)
}

function gridReload(cookies, element) {
    $('.lg-grid-table').addClass('text-muted');
    $.ajax({
        url: getLgCurrentURI(),
        data: {
            pageSize: cookies.pagesize,
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
            $('input#lg-grid-search-input').val(cookies.search);
            highlightSearches();
            highlightFilters();
            if (element) {
                $('element').blur().focus().val(element.val());
            }
        }
    });
}