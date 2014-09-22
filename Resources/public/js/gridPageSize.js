function pageSizeControl() {
    // The control
    $('.lg-grid-pagesize').unbind('click');
    $('.lg-grid-pagesize').one('click', function(e) {
        e.preventDefault();
        pageSizeReload($(this));
    });
}

function pageSizeReload(control) {
   // The stuff being done
    var cookies = getCookies();
    var pageVal = Number($('input.lg-grid-page-input').val());
    var maxPages = Number(getMaxPages());
    offset = getOffset(cookies);
    if (offset < cookies.pageSize) {
        cookies.offset = 0;
    }
    // map to the bottom control
    $('.lg-grid-pagesize-button').html(control.html());
    // put the data into our javascript, for next time this is called
    // currentPageSize = Number($('#lg-grid-results-per-page').val());
    $('#lg-grid-results-per-page').val(control.attr('data-role-lg-pagesize'));
    cookies.pageSize = control.attr('data-role-lg-pagesize');
    setCookies(cookies);
    gridReload();
}