// '@LighthartGridBundle/Resources/public/js/gridControls.js'
// $(document).ready moved to below
function activateControls() {
    var cookies = getCookies();
    $('input#lg-search-input').val(cookies.search);
    filter = cookies.filter;
    if (filter) {
        filters = filter.split(';');
        var index;
        for (index = 0; index < filters.length; ++index) {
            f = filters[index].split('|')[0];
            var filterId = f.split(':')[0];
            var filterVal = f.split(':')[1];
            if (!!filterVal) {
                $('#lg-filter-' + filterId).val(filterVal);
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
    gridExtraActionsControl();
    gridSortControl();
    gridFlagControl();
    gridSelectorControl();
    gridResetControl();
    addButtonToggleControl();
    markFlags();
    setSelector();
}