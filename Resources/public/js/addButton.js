function addButtonToggleControl() {
    $('#lg-add-form-toggle').unbind('click');
    $('#lg-add-form-toggle').one('click', function(e) {
        e.preventDefault();
        var addFormToggleCookie = 'lg-add-form-toggle';
        if ($('#lg-add-form').hasClass('hide')) {
            // Open
            $.cookie(addFormToggleCookie, 1);
            $('#lg-add-icon').removeClass('fa-chevron-down');
            $('#lg-add-icon').addClass('fa-chevron-up');
            $('#lg-add-form').removeClass('hide');
            $('#lg-add-form-toggle').removeClass('add-form-closed');
            $('#lg-add-form-toggle').addClass('add-form-open');
        } else {
            // Closed
            $.cookie(addFormToggleCookie, 0);
            $('#lg-add-icon').addClass('fa-chevron-down');
            $('#lg-add-icon').removeClass('fa-chevron-up');
            $('#lg-add-form').addClass('hide');
            $('#lg-add-form-toggle').addClass('add-form-closed');
            $('#lg-add-form-toggle').removeClass('add-form-open');
        }
        activateControls();
    });
}