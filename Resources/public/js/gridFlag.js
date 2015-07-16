// '@LighthartGridBundle/Resources/public/js/gridFlag.js'
function getFlags() {
    checks = $('input.lg-grid-flag');
    flags = {};
    $.map(checks, function(val, i) {
        flag = $(val).attr('id').replace('lg-grid-flag-', '');
        flags[flag] = $(val).is(':checked') ? 1 : 0;
    });
    return flags;
}

function gridFlagControl() {
    $('input.lg-grid-flag').on('click', function(e) {
        control = $(this);
        // e.preventDefault();
        gridFlagReload(control);
    });
}

function gridFlagReload(control) {
    var cookies = getCookies();
    // reset offset on search
    cookies.offset = 0;
    setFlagCookies();
    gridReload();
}

function markFlags() {
    var flags = getFlagCookies();
    $.map(flags, function(value, flag) {
        if (value == 1) {
            $('input#lg-grid-flag-' + flag).each(function() {
                this.checked = true;
            });
        }
        // Conflicts with pre-set flags
        // else {
        //     $('input#lg-grid-flag-' + flag).each(function() {
        //         this.checked = false;
        //     });
        // }
    });
}