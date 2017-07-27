// '@LighthartGridBundle/Resources/public/js/xtraActions.js'
function gridExtraActionsControl() {
    $('.lg-xtra-actions')
        .on('click', function(e) {
            e.preventDefault();
            // this needs to be changed to find the relevant children
            if ($(this)
                .siblings('.lg-xtra-action.hide')
                .length) {
                showHiddenActions($(this));
            }
            else {
                hideVisibleActions($(this));
            }
        });
}

function showHiddenActions(control) {
    // Open
    control.children('span')
        .removeClass('fa-caret-right');
    control.children('span')
        .addClass('fa-caret-left');
    control.siblings('.lg-form-action')
        .each(function() {
            $(this).children('button.lg-xtra-action').each(
                function() {
                $(this).removeClass('hide');                    
                }
        )});
    control.siblings('.lg-xtra-action')
        .each(function() {
            $(this)
                .removeClass('hide');
        });
        var size=parseInt(control.parent().children().length * 23) - 5;
    control.parent()
        .attr('style', 'width:' + size + 'px;');
}

function hideVisibleActions(control) {
    // Close
    control.children('span')
        .addClass('fa-caret-right');
    control.children('span')
        .removeClass('fa-caret-left');
    control.siblings('.lg-form-action')
        .each(function() {
            $(this).children('button.lg-xtra-action').each(
                function() {
                $(this).addClass('hide');                    
                }
        )});
    control.siblings('.lg-xtra-action')
        .each(function() {
            $(this)
                .addClass('hide');
        });
    // This is related to padding set in cell.html.twig. 
    // Bad coupling here
    // Why 3? Why 5 above.  It works and I'm sick of chasing a pixel
    var size=parseInt(control.parent().attr('data-button-num')) * 23 - 3;
    control.parent()
        .attr('style', 'width:' + size + 'px;');
}