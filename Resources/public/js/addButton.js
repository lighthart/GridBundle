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
        } else {
            // Closed
            $.cookie(addFormToggleCookie, 0);
            $('#lg-add-icon').addClass('fa-chevron-right');
            $('#lg-add-icon').removeClass('fa-chevron-left');
            $('#lg-add-form').addClass('hide');
        }
        activateControls();
    });
}