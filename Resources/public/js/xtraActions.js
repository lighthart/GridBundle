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
            } else {
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
            $(this)
                .children('button.lg-xtra-action')
                .each(
                    function() {
                        $(this)
                            .removeClass('hide');
                    }
                )
        });
    control.siblings('.lg-xtra-action')
        .each(function() {
            $(this)
                .removeClass('hide');
        });
    var size = parseInt(control.parent()
        .children()
        .length);
    var forms = parseInt(control.parent()
        .children("form")
        .length);
    size = size * 23 - (size - 2) + forms + 2;
    // (size - 2) is to deal with button group
    // 23 is default button width
    // control.parent()
    //     .attr('style', 'width:' + size + 'px;');
    // control.parent()
    //     .parent()
    //     .attr('style', 'width:' + size + 'px;');
}

function hideVisibleActions(control) {
    // Close
    control.children('span')
        .addClass('fa-caret-right');
    control.children('span')
        .removeClass('fa-caret-left');
    control.siblings('.lg-form-action')
        .each(function() {
            $(this)
                .children('button.lg-xtra-action')
                .each(
                    function() {
                        $(this)
                            .removeClass('hide');
                    }
                )
        });
    control.siblings('.lg-xtra-action')
        .each(function() {
            $(this)
                .addClass('hide');
        });

    var size = parseInt(control.parent()
        .attr(
            "data-button-num"));
    var forms = parseInt(control.parent()
        .children("form")
        .length -
        control.parent()
        .children("form.lg-xtra-action")
        .length
    );
    // (size - 2) is to deal with button group
    // 23 is default button width
    // size = size * 23 - (size - 2) + forms + 2;

    // control.parent()
    //     .attr('style', 'width:' + size + 'px;');

    // control.parent()
    //     .parent()
    //     .attr('style', 'width:' + size + 'px;');
}