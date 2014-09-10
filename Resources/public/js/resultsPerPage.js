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
    $('.lg-grid-table').addClass('text-muted');
    var cookie = 'lg-grid-results-per-page';
    var numPerPage = control.attr('data-role-lg-pagesize');
    var pageVal = $('input.lg-grid-page-input').val();
    var maxPages = getMaxPages();
    offset = getOffset();
    if (offset < numPerPage) {
        offset = 0;
        var offsetCookie = "lg-grid-" + getLgCurrentRoute() + "-offset";
        $.cookie(offsetCookie, offset);
    }
    searchString = getSearch();
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
            $('div#lg-grid-header').html($(data).find('div#lg-grid-header').html());
            $('div#lg-grid-footer').html($(data).find('div#lg-grid-footer').html());
            $('.lg-grid-table').removeClass('text-muted');
            $('input#lg-grid-search-input').blur().focus().val(searchString);
            highlightSearches();
        }
    });
}