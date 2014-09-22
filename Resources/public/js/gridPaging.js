function pagingInputControl() {
    $('.lg-grid-last-page').unbind('change');
    $('input.lg-grid-page-input').on('change', function(e) {
        e.preventDefault();
        cookies = getCookies();
        cookies.offset = cookies.offset ? cookies.offset : 0;
        var maxPages = Number($('#lg-grid-max-pages').val());
        var newPage = Number($('#lg-grid-page-input').val());
        console.log('NewPage:' + newPage);
        if (newPage < 0) {
            cookies.offset = 0;
        } else if (newPage >= maxPages) {
            var maxResults = Number($('#lg-grid-max-results').val());
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
    $('.lg-grid-prev-page').unbind('click');
    $('.lg-grid-prev-page').on('click', function(e) {
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
    $('.lg-grid-next-page').unbind('click');
    $('.lg-grid-next-page').on('click', function(e) {
        e.preventDefault();
        cookies = getCookies();
        cookies.offset = cookies.offset ? cookies.offset : 0;
        var maxResults = $('#lg-grid-max-results').val();
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
    $('.lg-grid-first-page').unbind('click');
    $('.lg-grid-first-page').on('click', function(e) {
        e.preventDefault();
        cookies = getCookies();
        cookies.offset = 0;
        setCookies(cookies);
        pagingInputReload();
    });
}

function lastPageControl() {
    $('.lg-grid-last-page').unbind('click');
    $('.lg-grid-last-page').on('click', function(e) {
        e.preventDefault();
        cookies = getCookies();
        var maxResults = Number($('#lg-grid-max-results').val());
        cookies.offset = maxResults - maxResults % Number(cookies.pageSize);
        setCookies(cookies);
        pagingInputReload();
    });
}

function pagingInputReload() {
    var cookies = getCookies();
    var pageVal = Number($('input.lg-grid-page-input').val());
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
        $('input.lg-grid-page-input').val(pageVal);
        setCookies(cookies);
        gridReload();
    }
}