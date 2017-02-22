// '@LighthartGridBundle/Resources/public/js/gridSelector.js'

// this stuff might need to be looped through if more than one selector

function gridCheckboxControl() {
    $('.lg-grid-checkbox')
        .on('click', function(e) {
            control = $(this);
            // e.preventDefault();
            gridCheckboxes(control.prop('checked'));
        });
}

function gridCheckboxes(status) {
    $('.lg-row-checkbox')
        .prop('checked', status);
};