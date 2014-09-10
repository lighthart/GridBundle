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

function highlightSearches(){
    $('td.lg-grid-searchable').highlight($('input#lg-grid-search-input').val().split(' '));
    if (!!$('input#lg-grid-search-input').val().trim()) {
        $('input#lg-grid-search-input').addClass('lg-grid-highlight');
    } else {
        $('input#lg-grid-search-input').removeClass('lg-grid-highlight');
    }
}