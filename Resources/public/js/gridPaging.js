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
            if (newPage < 0) {
                cookies.offset = 0;
            } else if (newPage >= maxPages) {
                var maxResults = Number($('#lg-max-results').val());
                cookies.offset = maxResults - maxResults % Number(cookies.pageSize);
            } else {
                cookies.offset = (newPage - 1) * cookies.pageSize;
            }
            setCookies(cookies);
            pagingInputReload($(this));
        }
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
        pagingInputReload();
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
        pagingInputReload();
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

function pagingInputReload(control) {
    var cookies = getCookies();
    var pageVal = Number(control.val());
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