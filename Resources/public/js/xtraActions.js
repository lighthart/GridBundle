function gridExtraActionsControl(){
    console.log('Xtra Actions!');
    $('.lg-xtra-actions').on('click', function(e) {
        e.preventDefault();
        console.log($(this));
        console.log($(this).siblings());
        console.log($(this).siblings('.lg-xtra-action'));
        if ($('lg-xtra-action hide')) {
            // Open
            console.log('open');
            $(this).children('span').removeClass('fa-caret-right');
            $(this).children('span').addClass('fa-caret-down');
            console.log('siblings:');
            $(this).siblings('.lg-xtra-action').each(function() {console.log($(this));$(this).removeClass('hide');});
        } else {
            // Close
            console.log('close');
            $(this).children('span').addClass('fa-caret-right');
            $(this).children('span').removeClass('fa-caret-down');
            $(this).siblings('.lg-xtra-action').each(function() {$(this).addClass('hide');});
        }
    });
}
