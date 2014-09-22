function gridSearchControl() {
    $('input#lg-grid-search-input').on('keydown keyup change', function() {
        delay(function() {
            gridSearchReload();
        }, quiet);
    });
}

function gridSearchReload(control) {
    var cookies = getCookies();
    // reset offset on search
    cookies.offset = 0;
    cookies.search = getSearch();
    setCookies(cookies);
    gridReload();
}