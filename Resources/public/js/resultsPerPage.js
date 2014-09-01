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
    offset = getOffset();
    // map to the bottom control
    $('.lg-grid-pagesize-button').html(control.html());
    // put the data into our javascript, for next time this is called
    $('#lg-grid-results-per-page').val(control.attr('data-role-lg-pagesize'));
    $.cookie(cookie, control.attr('data-role-lg-pagesize'));
    var pageVal = $('input.lg-grid-page-input').val();
    var maxPages = getMaxPages();
    if (pageVal > maxPages) {
        $('input.lg-grid-page-input').val(maxPages);
    }

    $.ajax({
        url: getLgCurrentURI(),
        data: {
            pageSize: numPerPage,
            pageOffset: offset
            // tableOnly: true
        },
        dataType: 'html',
        type: 'GET',
        complete: function() {
            pageSizeControl();
        },
        success: function(data) {
            $('table.lg-grid-table').html($(data).find('table.lg-grid-table').html());
            $('span.lg-grid-result-counts').html($(data).find('span#lg-grid-header-result-counts').html());
            $('span.lg-grid-total-pages').html($(data).find('span#lg-grid-header-total-pages').html());
        }
    });
}