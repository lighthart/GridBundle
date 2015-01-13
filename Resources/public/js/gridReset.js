
function gridResetControl() {
    $('a.lg-reset').on('click', function(e) {
        control = $(this);
        e.preventDefault();
        $('input.lg-filter').each(function(){
            $(this).val('');
            console.log($(this));
        });
        $('input.lg-search').val('');

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
    $.removeCookie(searchCookie);
    $.removeCookie(filterCookie);
    $.removeCookie(offsetCookie);

    var cookies = getCookies();
    console.table(cookies);

    gridReload();
}