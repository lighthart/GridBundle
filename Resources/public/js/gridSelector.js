// '@LighthartGridBundle/Resources/public/js/gridSelector.js'

// this stuff might need to be looped through if more than one selector
function getSelected() {
    return $('select.lg-grid-selector').val();
}

function getSelectorCookie(){
    return $.cookie("lg-" + getLgCurrentRoute() + "-selector");
}

function setSelectorCookie() {
    $.cookie("lg-" + getLgCurrentRoute() + "-selector", $('select.lg-grid-selector').val());
}


function gridSelectorControl() {
    $('select.lg-grid-selector').change(function(e) {
        control = $(this);
        // e.preventDefault();
        gridSelectorReload(control);
    });
}

function gridSelectorReload(control) {
    var cookies = getCookies();
    // reset offset on search
    cookies.offset = 0;
    setSelectorCookie();
    gridReload();
}

function setSelector() {
    var selected = getSelectorCookie();
    $('select.lg-grid-selector').val(selected);
}