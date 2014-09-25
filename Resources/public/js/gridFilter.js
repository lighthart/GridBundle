function getAllFilters() {
    var filter = "";
    $('.lg-filter-input').each(function(i, e) {
        filter += $(this).parent().attr('data-role-lg-class') + '__' + $(this).parent().attr('data-role-lg-field') + ':' + $(this).val() + ';';
    });
    return filter;
}

function gridFilterControl() {
    $('input.lg-filter-input').on('keydown keyup change', function() {
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
    $('#lg-toggle-filter').unbind('click');
    $('#lg-toggle-filter').one('click', function(e) {
        e.preventDefault();
        var filterToggleCookie = 'lg-filter-toggle';
        if ($('.lg-filter:first').hasClass('hide')) {
            // Open
            $.cookie(filterToggleCookie, 1);
            $('#lg-toggle-filter > span').removeClass('fa-chevron-right');
            $('#lg-toggle-filter > span').addClass('fa-chevron-down');
        } else {
            // Closed
            $.cookie(filterToggleCookie, 0);
            $('#lg-toggle-filter > span').addClass('fa-chevron-right');
            $('#lg-toggle-filter > span').removeClass('fa-chevron-down');
        }
        $('.lg-filter').toggleClass('hide');
        activateControls();
    });
}