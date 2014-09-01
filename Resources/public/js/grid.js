// inhibits recurring callback for duration of quiet before executing
var quiet = 200; // 100 ms

var delay = (function(){
    var timer = 0;
    return function(callback, ms){
        clearTimeout (timer);
        timer = setTimeout(callback, ms);
    };
})();

// eg:

// var quiet = 100; // 100 ms
// delay(function(){ sidebar.finishMove();}, quiet)

var getNumPerPage = (function(){
    var numPerPage = $.cookie('lg-grid-results-per-page');
    if (numPerPage === undefined || numPerPage === null) {
        numPerPage = 10;
    }
    return numPerPage;
})();

var getOffsetVal = (function(){
    var offsetVal = $('input.lg-grid-page-input').val();
    console.log('getOffsetVal');
    console.log(offsetVal);
    if (offsetVal < 1) {
        offsetVal = 1;
    }

    console.log(offsetVal);

    maxPages = 3;
    if (offsetVal > maxPages) {
        offsetVal = maxPages;
    }
    console.log(offsetVal);
    return offsetVal;
})();

var getOffset = (function(){
    var offsetVal = getOffsetVal;
    var numPerPage = getNumPerPage;

    offset = (offsetVal - 1) * numPerPage;
    offset = offset < 0 ? 0 : offset;
    return offset;
})();