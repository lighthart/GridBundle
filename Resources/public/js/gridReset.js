
function gridResetControl() {
    $('a.lg-reset').on('click', function(e) {
        control = $(this);
        e.preventDefault();
        $('input.lg-filter').each(function(){
            $(this).val('');
        });
        $('input.lg-search').val('');
        $('.lg-grid-flag').each(function(){this.checked = false;});

        delay(function() {
            gridResetReload(control);
        }, quiet);
    });
}


function gridResetReload(control) {
    var ajaxVersionCookie = "lg-" + getLgCurrentRoute() + "-version";
    var numPerPagecookie = 'lg-results-per-page';
    var filterCookie = "lg-" + getLgCurrentRoute() + "-filter";
    var offsetCookie = "lg-" + getLgCurrentRoute() + "-offset";
    var searchCookie = "lg-" + getLgCurrentRoute() + "-search";
    var sortCookie = "lg-" + getLgCurrentRoute() + "-sort";
    var filterToggleCookie = 'lg-filter-toggle';
    $.cookie(filterToggleCookie, 1);
    $.removeCookie(searchCookie);
    $.removeCookie(filterCookie);
    $.removeCookie(offsetCookie);

    $.map(getFlags(), function(value, flag) {
        var flagCookie = "lg-" + getLgCurrentRoute() + "-flag-" + flag;
        $.removeCookie(flagCookie);
    });

    gridReload();
}