function gridFilterControl() {
    $('input#lg-grid-search-input').on('change', function(e) {
        delay(function() {
            gridFilter();
        }, quiet);
    });
}

function gridFilter(control) {
    var numPerPagecookie = 'lg-grid-results-per-page';
    var offsetCookie = "lg-grid-" + getLgCurrentRoute() + "-offset";
    offset = 0;
    $.cookie(offsetCookie, offset);

    if (currentPageSize != Number(control.attr('data-role-lg-pagesize'))) {
        $.ajax({
            url: getLgCurrentURI(),
            data: {
                pageSize: getNumPerPage(),
                pageOffset: offset,
                filter: getFilter()
            },
            dataType: 'html',
            type: 'GET',
            complete: function() {
                activateControls();
            },
            success: function(data) {
                $('table.lg-grid-table').html($(data).find('table.lg-grid-table').html());
                $('div#lg-grid-header').html($(data).find('div#lg-grid-header').html());
                $('div#lg-grid-footer').html($(data).find('div#lg-grid-footer').html());
            }
        });
    }
}