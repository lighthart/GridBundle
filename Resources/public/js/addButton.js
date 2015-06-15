function addButtonToggleControl() {
    $('#lg-toggle-add-form').unbind('click');
    $('#lg-toggle-add-form').one('click', function(e) {
        e.preventDefault();
        var addFormToggleCookie = 'lg-add-form-toggle';
        if ($('#lg-add-form').hasClass('hide')) {
            // Open
            $.cookie(addFormToggleCookie, 1);
            $('#lg-add-icon').removeClass('fa-chevron-right');
            $('#lg-add-icon').addClass('fa-chevron-left');
            $('#lg-add-form').removeClass('hide');
            $('#lg-add-form-toggle').removeClass('add-form-closed');
            $('#lg-add-form-toggle').addClass('add-form-open');
        } else {
            // Closed
            $.cookie(addFormToggleCookie, 0);
            $('#lg-add-icon').addClass('fa-chevron-right');
            $('#lg-add-icon').removeClass('fa-chevron-left');
            $('#lg-add-form').addClass('hide');
            $('#lg-add-form-toggle').addClass('add-form-closed');
            $('#lg-add-form-toggle').removeClass('add-form-open');
        }
        activateControls();
    });
}