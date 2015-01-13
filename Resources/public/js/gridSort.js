function getAllSorts() {
    var sorts = "";
    $('span.lg-sort').each(function(i, e) {
            sorts += $(this).parent().attr('data-role-lg-class') + '__' + $(this).parent().attr('data-role-lg-field') + ':' + $(this).children('input').val() + ';';
    });
    return sorts;
}


function gridSortControl() {
    $('span.lg-sort').on('click', function(e) {
        control = $(this);
        e.preventDefault();
        delay(function() {
            gridSortReload(control);
        }, quiet);
    });
}


function gridSortReload(control) {

    var order = control.children('input').val();
    $('.lg-sort').each(function() { $(this).val(''); });
    if (!order) {
        order = 'ASC';
    } else if (order == 'ASC') {
        order = 'DESC';
    } else {
        order = '';
    }
    control.children('input').val(order);

    var cookies = getCookies();
    cookies.offset = 0;
    cookies.sort = getAllSorts();
    setCookies(cookies);

    var th = control.closest('th');
    var thClass = th.attr('data-role-lg-class');
    var thField = th.attr('data-role-lg-field');

    gridReload();
}