/// '@LighthartGridBundle/Resources/public/js/gridPaging.js'
function pagingInputControl() {
    $('input.lg-page-input').unbind('change keyup');
    $('input.lg-page-input').on('change keyup', function(e) {
        if ([37, 38, 39, 40].indexOf(e.keyCode) > -1) {
        } else {
            e.preventDefault();
            cookies = getCookies();
            cookies.offset = cookies.offset ? cookies.offset : 0;
            var maxPages = Number($('#lg-max-pages').val());
            var newPage = Number($(this).val());
            var maxResults = Number($('#lg-max-results').val());
            if (newPage < 0) {
                cookies.offset = 0;
                newPage = 1;
            } else if (newPage >= maxPages) {
                cookies.offset = maxResults - maxResults % Number(cookies.pageSize);
                newPage = maxPages;
            } else {
                cookies.offset = (newPage - 1) * cookies.pageSize;
            }
            if (cookies.offset >= maxResults) {
                cookies.offset -= maxResults % cookies.pageSize;
            }
            setCookies(cookies);
            pagingInputReload($(this).val());
        }
    });
}

function prevPageControl() {
    $('.lg-prev-page').unbind('click');
    $('.lg-prev-page').on('click', function(e) {
        e.preventDefault();
        var nextPage = $('input.lg-page-input').val();
        if (1 == nextPage) {
        } else {
            nextPage -= 1;
        }
        pagingInputReload(nextPage);
    });
}

function nextPageControl() {
    $('.lg-next-page').unbind('click');
    $('.lg-next-page').on('click', function(e) {
        e.preventDefault();
        cookies = getCookies();
        cookies.offset = cookies.offset ? cookies.offset : 0;
        var maxPages = Number($('#lg-max-pages').val());
        var nextPage = $('input.lg-page-input').val();
        if (nextPage < maxPages) {
            nextPage += 1;
        } else {
        }
        pagingInputReload(nextPage);
    });
}

function firstPageControl() {
    $('.lg-first-page').unbind('click');
    $('.lg-first-page').on('click', function(e) {
        e.preventDefault();
        cookies = getCookies();
        cookies.offset = 0;
        setCookies(cookies);
        pagingInputReload(1);
    });
}

function lastPageControl() {
    $('.lg-last-page').unbind('click');
    $('.lg-last-page').on('click', function(e) {
        e.preventDefault();
        pagingInputReload($('#lg-max-pages').val());
    });
}

function pagingInputReload(page) {
    var cookies = getCookies();
    var pageVal = Number(page);
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
    }
    cookies.offset = numPerPage * (pageVal - 1);
    $('input.lg-page-input').val(pageVal);
    setCookies(cookies);
    gridReload();
}