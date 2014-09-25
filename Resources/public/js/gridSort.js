function getAllSorts() {
    var sorts = "";
    $('.lg-sort').each(function(i, e) {
        sorts += $(this).parent().attr('data-role-lg-class') + '__' + $(this).parent().attr('data-role-lg-field') + ':' + $(this).children('input').val() + ';';
    });
    return sorts;
}


function gridSortControl() {
    $('span.lg-sort').on('click', function(e) {
        control = $(this);
        e.preventDefault();
        console.log(getAllSorts());
        delay(function() {
            gridSortReload(control);
        }, quiet);
    });
}


function gridSortReload(control) {

    var order = control.children('input').val();
    console.log(order);
    if (!order) {
        order = 'ASC';
    } else if (order == 'ASC') {
        order = 'DESC';
    } else {
        order = '';
    }
    control.children('input').val(order);
    console.log(order);

    var cookies = getCookies();
    console.log(cookies.sort);
    cookies.offset = 0;
    cookies.sort = getAllSorts();
    console.log(cookies.sort);
    setCookies(cookies);


    console.log('Sort');
    console.log(control);

    var th = control.closest('th');
    var thClass = th.attr('data-role-lg-class');
    var thField = th.attr('data-role-lg-field');
    console.log(th);
    console.log(thField);
    console.log(thClass);

    gridReload();
}