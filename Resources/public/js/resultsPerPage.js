$(document).ready(function() {
    pageSizeControl();
});

function pageSizeControl() {
    $('.lg-grid-pagesize').on('click', function() {
        pageSizeReload($(this));
    });
}

function pageSizeReload(control) {
    var cookie = 'lg-grid-results-per-page';
    var numPerPage = control.attr('data-role-lg-pagesize');
    $('.lg-grid-pagesize-button').html(control.html());
    $('#lg-grid-results-per-page').val(control.attr('data-role-lg-pagesize'));
    $.cookie(cookie, control.attr('data-role-lg-pagesize'));

    $.ajax({
        url: getLgCurrentURI(),
        data: {
            pageSize: numPerPage,
            tableOnly: true
        },
        dataType: 'html',
        type: 'GET',
        complete: function() {
            pageSizeControl();
        },
        success: function(data) {
            $('.lg-grid-table').html(data);
        }
    });
}