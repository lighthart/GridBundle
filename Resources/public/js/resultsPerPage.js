$(document).ready(function() {
    pageSizeControl();
});

function pageSizeControl() {
    console.log('pagesizecontrol');
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
            // tableOnly: true
        },
        dataType: 'html',
        type: 'GET',
        complete: function() {
            pageSizeControl();
        },
        success: function(data) {
            console.log('success');
            $('table.lg-grid-table').html($(data).find('table.lg-grid-table').html());
            $('span.lg-grid-result-counts').html($(data).find('span#lg-grid-header-result-counts').html());
        }
    });
}