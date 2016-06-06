/// '@LighthartGridBundle/Resources/public/js/gridPaging.js'
function pagingInputControl() {
    $('input.lg-page-input').unbind('change keyup');
    $('input.lg-page-input').on('change keyup', function(e) {
        if ([37, 38, 39, 40].indexOf(e.keyCode) > -1) {}
        else {
            e.preventDefault();
            pagingInputReload($(this).val(), $('#lg-grid-div').findScrollBar().scrollTop());
        }
    });
}

function prevPageControl() {
    $('.lg-prev-page').unbind('click');
    $('.lg-prev-page').on('click', function(e) {
        e.preventDefault();
        var nextPage = Number($('input.lg-page-input').val());
        if (1 == nextPage) {}
        else {
            nextPage -= 1;
        }
        pagingInputReload(nextPage, $('#lg-grid-div').findScrollBar().scrollTop());
    });
}

function nextPageControl() {
    $('.lg-next-page').unbind('click');
    $('.lg-next-page').on('click', function(e) {
        e.preventDefault();
        cookies = getCookies();
        cookies.offset = cookies.offset ? cookies.offset : 0;
        var maxPages = Number($('#lg-max-pages').val());
        var nextPage = Number($('input.lg-page-input').val());
        if (nextPage < maxPages) {
            nextPage += 1;
        }
        else {}
        pagingInputReload(nextPage, 0);
    });
}

function firstPageControl() {
    $('.lg-first-page').unbind('click');
    $('.lg-first-page').on('click', function(e) {
        e.preventDefault();
        cookies = getCookies();
        cookies.offset = 0;
        setCookies(cookies);
        pagingInputReload(1, 0);
    });
}

function lastPageControl() {
    $('.lg-last-page').unbind('click');
    $('.lg-last-page').on('click', function(e) {
        e.preventDefault();
        pagingInputReload($('#lg-max-pages').val(), 0);
    });
}

function pagingInputReload(page, top) {
    top = typeof top !== 'undefined' ? top : false;
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
    }
    else {}
    cookies.offset = numPerPage * (pageVal - 1);
    $('input.lg-page-input').val(pageVal);
    setCookies(cookies);
    gridReload({
        cscroll: top
    });
}