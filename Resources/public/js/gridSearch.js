function getSearch() {
    var search = $('input#lg-search-input').val();
    return search;
}

function gridSearchControl() {
    $('input#lg-search').on('keydown keyup change', function() {
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