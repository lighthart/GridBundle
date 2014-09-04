function pagingInputControl() {
    $('.lg-grid-last-page').unbind('change');
    $('input.lg-grid-page-input').on('change', function() {
        delay(function() {
            pagingInputReload();
        }, quiet);
    });
}

function prevPageControl() {
    $('.lg-grid-prev-page').unbind('click');
    $('.lg-grid-prev-page').on('click', function() {
        var currentPage = Number($('input#lg-grid-page-input').val());
        var maxPages = Number($('input#lg-grid-max-pages').val());
        if (currentPage > 1) {
            currentPage--;
            $('input.lg-grid-page-input').val(currentPage);
        } else {
            $('input.lg-grid-page-input').val(1);
        }
        delay(function() {
            pagingInputReload();
        }, quiet);
    });
}

function nextPageControl() {
    $('.lg-grid-next-page').unbind('click');
    $('.lg-grid-next-page').on('click', function() {
        currentPage = Number($('input#lg-grid-page-input').val());
        maxPages = Number($('input#lg-grid-max-pages').val());
        if (currentPage <= maxPages) {
            currentPage++;
            $('input.lg-grid-page-input').val(currentPage);
        } else {
            $('input.lg-grid-page-input').val(maxPages);
        }
        delay(function() {
            pagingInputReload();
        }, quiet);
    });
}

function firstPageControl() {
    $('.lg-grid-first-page').unbind('click');
    $('.lg-grid-first-page').on('click', function() {
        $('input.lg-grid-page-input').val(1);
        pagingInputReload();
    });
}

function lastPageControl() {
    $('.lg-grid-last-page').unbind('click');
    $('.lg-grid-last-page').on('click', function() {
        maxPages = Number($('input#lg-grid-max-pages').val());
        $('input.lg-grid-page-input').val(maxPages);
        pagingInputReload();
    });
}

function pagingInputReload() {
    var cookie = "lg-grid-" + getLgCurrentRoute() + "-offset";
    var numPerPage = getNumPerPage();
    var pageVal = Number($('input.lg-grid-page-input').val());
    var maxPages = Number(getMaxPages());
    offset = getOffset();
    search = getSearch();
    getNumPerPage();
    if (pageVal < 1 || pageval > maxPages) {
        if (pageVal < 1) {
            pageVal = 1;
        }
        if (pageVal > maxPages) {
            pageVal = maxPages;
        }
    } else {
        console.log(pageVal);
        $('input.lg-grid-page-input').val(pageVal);
        $.cookie(cookie, offset);
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
            }
        });
    }
}