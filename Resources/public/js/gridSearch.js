function gridSearchControl() {
    $('input#lg-grid-search-input').on('keyup', function() {
        delay(function() {
            gridSearchReload();
        }, quiet);
    });
}

function gridSearchReload(control) {
    var numPerPagecookie = 'lg-grid-results-per-page';
    var offsetCookie = "lg-grid-" + getLgCurrentRoute() + "-offset";
    var cookie = "lg-grid-" + getLgCurrentRoute() + "-search";
    offset = 0;
    $.cookie(offsetCookie, offset);
    searchString = getSearch().trim();
    $.cookie(cookie, searchString);
    if (!!searchString) {
        $('input#lg-grid-search-input').addClass('lg-grid-searched');
    } else {
        $('input#lg-grid-search-input').removeClass('lg-grid-searched');
    }
    numPerPage = getNumPerPage();
    $.ajax({
        url: getLgCurrentURI(),
        data: {
            pageSize: numPerPage,
            pageOffset: offset,
            search: searchString
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
            // Get the control, replace the value, move cursor to the end
            $('input#lg-grid-search-input').blur().focus().val(searchString);
            $('td').highlight($('input#lg-grid-search-input').val().split(' '));
        }
    });
}