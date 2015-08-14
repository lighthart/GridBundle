// '@LighthartGridBundle/Resources/public/js/gridSearch.js'
function getSearch() {
    var search = $('input#lg-search-input').val();
    return search;
}

function gridSearchControl() {
    $('input#lg-search-input').on('keyup change', function(e) {
        if ([37, 38, 39, 40].indexOf(e.keyCode) > -1) {
        } else {

            var cookies = getCookies();
            // reset offset on search
            cookies.offset = 0;
            cookies.search = getSearch();
            setCookies(cookies);
            gridReload();
        }
    });
}