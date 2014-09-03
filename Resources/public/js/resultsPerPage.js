function pageSizeControl() {
    // The unbind is necessary here because the ajax call in
    // the reload does not replace the buttons which the
    // events are attached to

    $('.lg-grid-pagesize').unbind('click');
    $('.lg-grid-pagesize').one('click', function() {
        pageSizeReload($(this));
    });
}

function pageSizeReload(control) {
    var cookie = 'lg-grid-results-per-page';
    var numPerPage = control.attr('data-role-lg-pagesize');
    var pageVal = $('input.lg-grid-page-input').val();
    var maxPages = getMaxPages();
    offset = getOffset();
    filter = getFilter();
    console.log(offset);
    console.log(maxPages);
    getNumPerPage();
    // map to the bottom control
    $('.lg-grid-pagesize-button').html(control.html());
    // put the data into our javascript, for next time this is called
    // currentPageSize = Number($('#lg-grid-results-per-page').val());
    $('#lg-grid-results-per-page').val(control.attr('data-role-lg-pagesize'));
    $.cookie(cookie, control.attr('data-role-lg-pagesize'));
    $.ajax({
        url: getLgCurrentURI(),
        data: {
            pageSize: numPerPage,
            pageOffset: offset
        },
        dataType: 'html',
        type: 'GET',
        complete: function() {
            activateControls();
        },
        success: function(data) {
            $('table.lg-grid-table').html($(data).find('table.lg-grid-table').html());
            $('span.lg-grid-result-counts').html($(data).find('span#lg-grid-header-result-counts').html());
            $('span.lg-grid-total-pages').html($(data).find('span#lg-grid-header-total-pages').html());
            $('span.lg-grid-paging-controls').html($(data).find('span.lg-grid-paging-controls').html());
            $('input#lg-grid-search-input').val(filter);
        }
    });
}