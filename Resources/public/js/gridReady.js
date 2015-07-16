// '@LighthartGridBundle/Resources/public/js/gridReady.js'

// Document.readys removed and recombined:
$(document).ready(function() {
    activateControls();
    cookies = getCookies();
    updates();
    moveCursor();
    focusEdit();
});