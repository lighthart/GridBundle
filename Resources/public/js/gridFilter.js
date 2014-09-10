function gridFilterControl() {
    $('input.lg-grid-filter-input').on('keyup', function() {
        delay(function() {
            gridFilterReload();
        }, quiet);
    });
}

function gridFilterReload(control) {
    var cookies = getCookies();
    // reset offset on filter
    cookies.offset = 0;
    cookies.filter= getAllFilters();
    setCookies(cookies);
    gridReload();
}