function gridExtraActionsControl() {
    $('.lg-xtra-actions').on('click', function(e) {
        e.preventDefault();
        // this needs to be changed to find the relevant children
        if ($(this).siblings('.lg-xtra-action.hide').length) {
            showHiddenActions($(this));
        } else {
            hideVisibleActions($(this));
        }
    });
}

function showHiddenActions(control) {
    // Open
    control.children('span').removeClass('fa-caret-right');
    control.children('span').addClass('fa-caret-left');
    control.siblings('.lg-xtra-action').each(function() {
        $(this).removeClass('hide');
    });
    control.parent().attr('style','width:'+control.parent().children().length*24+'px;');
}

function hideVisibleActions(control) {
    // Close
    control.children('span').addClass('fa-caret-right');
    control.children('span').removeClass('fa-caret-left');
    control.siblings('.lg-xtra-action').each(function() {
        $(this).addClass('hide');
    });
    control.parent().attr('style','width:'+4*24+'px;');
}