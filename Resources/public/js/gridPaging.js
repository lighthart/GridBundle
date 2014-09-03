function pagingInputControl() {
    $('input.lg-grid-page-input').one('change', function(e) {
        delay(function() {
            pagingInputReload();
        }, quiet);
    });
}

function prevPageControl() {
    $('.lg-grid-prev-page').on('click', function() {
        currentPage = Number($('input#lg-grid-page-input').val());
        maxPages = Number($('input#lg-grid-max-pages').val());
        if (currentPage > 1 ) {
            currentPage--;
            $('input.lg-grid-page-input').val(currentPage);
        } else {
            $('input.lg-grid-page-input').val(1);
        }
        pagingInputReload();
    });
}

function nextPageControl() {
    $('.lg-grid-next-page').on('click', function() {
        currentPage = Number($('input#lg-grid-page-input').val());
        maxPages = Number($('input#lg-grid-max-pages').val());
        if (currentPage <= maxPages) {
            currentPage++;
            $('input.lg-grid-page-input').val(currentPage);
        } else {
            $('input.lg-grid-page-input').val(maxPages);
        }
        pagingInputReload();
    });
}

function firstPageControl() {
    $('.lg-grid-first-page').on('click', function() {
        $('input.lg-grid-page-input').val(1);
        pagingInputReload();
    });
}


function lastPageControl() {
    $('.lg-grid-last-page').on('click', function() {
        maxPages = Number($('input#lg-grid-max-pages').val());
        $('input.lg-grid-page-input').val(maxPages);
        pagingInputReload();
    });
}

function nextPageControl() {
    $('.lg-grid-next-page').on('click', function() {
        currentPage = Number($('input#lg-grid-page-input').val());
        maxPages = Number($('input#lg-grid-max-pages').val());
        if (currentPage < maxPages) {
            currentPage++;
            $('input.lg-grid-page-input').val(currentPage);
        }
        pagingInputReload();
    });
}

function pagingInputReload() {
    var cookie = "lg-grid-" + getLgCurrentRoute() + "-offset";
    offset = getOffset();
    filter = getFilter();
    var numPerPage = getNumPerPage();
    console.log('paging input reload offset: '+offset);
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
            $('input.lg-grid-page-input').val(pageVal);
            $('input#lg-grid-search-input').val(filter);
        }
    });
}