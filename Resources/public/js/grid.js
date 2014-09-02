// inhibits recurring callback for duration of quiet before executing
var quiet = 200; // 100 ms
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
    var numPerPage = $.cookie('lg-grid-results-per-page');
    if (numPerPage === undefined || numPerPage === null) {
        numPerPage = 10;
    }
    return numPerPage;
}

function getOffset() {
    var pageVal = Number($('input.lg-grid-page-input').val());
    var maxPages = Number($('#lg-grid-max-pages').val());
    if (pageVal > maxPages) {
        pageVal = maxPages;
    }
    var numPerPage = Number(getNumPerPage());
    var offset = (pageVal - 1) * numPerPage;
    offset = (offset < 0) ? 0 : offset;
    offset = ((offset / numPerPage) > maxPages) ? maxPages - (maxPages % numPerPage) : offset
    return offset;
}

function getMaxPages() {
    var maxPages = $('#lg-grid-max-pages').val();;
    return maxPages;
}
