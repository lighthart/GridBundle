$(document).ready(function() {
    activateControls();
    cookies = getCookies();
    console.log(cookies);
    console.log(cookies.offset);
});

function activateControls() {
    var cookies = getCookies();
    $('input#lg-grid-search-input').val(cookies.search);
    filter = cookies.filter;
    console.log(filter);
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
    highlightFilters();
    highlightSearches();
}