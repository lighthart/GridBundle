function gridExtraActionsControl(){
    console.log('Xtra Actions!');
    $('.lg-xtra-actions').on('click', function(e) {
        e.preventDefault();
        console.log($(this));
        console.log($(this).siblings());
        console.log('siblings:');
        console.log($(this).siblings('.lg-xtra-action'));
        console.log('hidden siblings');
        console.log($(this).siblings('lg-xtra-action hide'));
        console.log($(this).siblings('lg-xtra-action hide').length);
        // this needs to be changed to find the relevant children
        if ($(this).siblings('lg-xtra-action hide')) {
            // Open
            console.log('open');
            $(this).children('span').removeClass('fa-caret-right');
            $(this).children('span').addClass('fa-caret-left');
            $(this).siblings('.lg-xtra-action').each(function() {console.log($(this));$(this).removeClass('hide');});
        } else {
            // Close
            $(this).children('span').addClass('fa-caret-right');
            $(this).children('span').removeClass('fa-caret-left');
            console.log('close');
            $(this).siblings('.lg-xtra-action').each(function() {$(this).addClass('hide');});
        }
    });
}
