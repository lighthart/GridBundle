function getFlags(){
    flags = $('input.lg-grid-flag');
    console.log(flags);
    flags = $.map(flags, function( val, i ) {
        console.log(i);
        return $(val).is(':checked');
    });
    console.log(flags)
}

function gridFlagControl() {
    $('input.lg-grid-flag').on('click', function(e) {
        flags = getFlags();
        control = $(this);

        console.log(this.id);
        // e.preventDefault();
        delay(function() {
            gridFlagReload(control);
        }, quiet);
    });
}


function gridFlagReload(control) {
    gridReload();
}