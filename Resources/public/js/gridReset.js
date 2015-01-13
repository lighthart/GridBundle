
function gridResetControl() {
    $('a.lg-reset').on('click', function(e) {
        control = $(this);
        e.preventDefault();

        delay(function() {
            gridResetReload(control);
        }, quiet);
    });
}


function gridResetReload(control) {

        var cookies = getCookies();
        cookies.offset = 0;
        cookies.filter = null;
        cookies.sort = null;

    gridReload();
}