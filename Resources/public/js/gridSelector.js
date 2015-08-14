// '@LighthartGridBundle/Resources/public/js/gridSelector.js'

// this stuff might need to be looped through if more than one selector
function getSelected() {
    return $('.lg-grid-selector').val();
}

function getSelectorCookie(){
    return $.cookie("lg-" + getLgCurrentRoute() + "-selector");
}

function setSelectorCookie() {
    $.cookie("lg-" + getLgCurrentRoute() + "-selector", $('.lg-grid-selector').val(), { expires: 1 });
}


function gridSelectorControl() {
    $('.lg-grid-selector').change(function(e) {
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
    $('.lg-grid-selector').val(selected);
}