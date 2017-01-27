// '@LighthartGridBundle/Resources/public/js/gridControls.js'
// $(document).ready moved to below
function activateControls() {
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
    gridCheckboxControl();
}