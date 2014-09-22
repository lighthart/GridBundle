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
    cookies.filter = getAllFilters();
    setCookies(cookies);
    gridReload();
}

function gridFilterToggleControl() {
    $('#lg-grid-toggle-filter').unbind('click');
    $('#lg-grid-toggle-filter').one('click', function(e) {
        e.preventDefault();
        var filterToggleCookie = 'lg-grid-filter-toggle';
        if ($('.lg-grid-filter:first').hasClass('hide')) {
            // Open
            $.cookie(filterToggleCookie, 1);
            $('#lg-grid-toggle-filter > span').removeClass('fa-chevron-right');
            $('#lg-grid-toggle-filter > span').addClass('fa-chevron-down');
        } else {
            // Closed
            $.cookie(filterToggleCookie, 0);
            $('#lg-grid-toggle-filter > span').addClass('fa-chevron-right');
            $('#lg-grid-toggle-filter > span').removeClass('fa-chevron-down');
        }
        $('.lg-grid-filter').toggleClass('hide');
        activateControls();
    });
}