$(document).ready(function() {
    pagingInputControl();
});

function pagingInputControl() {
    $('input.lg-grid-page-input').on('change', function(e) {
        $(this).text("Hmm");
        delay(function() {
            pagingInputReload();
        }, quiet)
    });
}

function pagingInputReload() {
    var cookie = "lg-grid-" + getLgCurrentRoute() + "-offset"
    offset = getOffset();
    numPerPage = getNumPerPage();
    var pageVal = Number($('input.lg-grid-page-input').val());
    var maxPages = Number(getMaxPages());

    if (pageVal < 1) {
        pageVal = 1;
    }

    if (pageVal > maxPages) {
        pageVal = maxPages;
    }
    $('input.lg-grid-page-input').val(pageVal);


    $.cookie(cookie, offset);
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
            pagingInputControl();
        },
        success: function(data) {
            $('table.lg-grid-table').html($(data).find('table.lg-grid-table').html());
            $('span.lg-grid-result-counts').html($(data).find('span#lg-grid-header-result-counts').html());
            $('span.lg-grid-total-pages').html($(data).find('span#lg-grid-header-total-pages').html());
            $('input.lg-grid-page-input').val(pageVal);
        }
    });
}