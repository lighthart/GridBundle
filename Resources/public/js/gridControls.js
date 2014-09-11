$(document).ready(function() {
    activateControls();
    cookies = getCookies();
});

function activateControls() {
    var cookies = getCookies();
    $('input#lg-grid-search-input').val(cookies.search);
    filter = cookies.filter;
    if (filter) {
        filters = filter.split(';');
        var index;
        for (index = 0; index < filters.length; ++index) {
            var filterVal = filters[index].split(':')[1];
            var filterId = filters[index].split(':')[0];
            if (filterVal) {
                $('#lg-grid-filter-'+filterId).val(filterVal);
            }
        }
    }
    pageSizeControl();
    pagingInputControl();
    prevPageControl();
    nextPageControl();
    firstPageControl();
    lastPageControl();
    gridSearchControl();
    gridFilterControl();
    gridFilterToggleControl();
    highlightFilters();
    highlightSearches();
}