// '@LighthartGridBundle/Resources/public/js/gridFilter.js'
function getAllFilters() {
    var filter = '';
    $('.lg-filter-input').each(function(i, e) {
        var value = $(this).val();
        parent = $(this).parent();
        if ('otherGroup' != parent.attr('data-role-lg-field')) {
        }
        filter += parent.attr('data-role-lg-class') + '__' + parent.attr('data-role-lg-field') + ':' + value;
        if (parent.attr('data-role-lg-hidden')) {
            parent.attr('data-role-lg-hidden').split(';').forEach(function(f) {
                filter += '|'+ f + ':' + value;
                // Older below.  A little concerned about no reference to column's original field
                // filter += '|' + parent.attr('data-role-lg-class') + '__' + f + ':' + value;
            });
        }
        filter += ";";
    });
    return filter;
}

function gridFilterControl() {
    $('input.lg-filter-input').on('keyup change', function() {
        var cookies = getCookies();
        // reset offset on filter
        cookies.offset = 0;
        cookies.filter = getAllFilters();
        setCookies(cookies);
        gridReload();
    });
}

function gridFilterToggleControl() {
    $('#lg-toggle-filter').unbind('click');
    $('#lg-toggle-filter').one('click', function(e) {
        e.preventDefault();
        var filterToggleCookie = 'lg-filter-toggle';
        if ($('.lg-filter').parent().hasClass('hide') || $('.lg-filter').hasClass('hide')) {
            // Open
            $.cookie(filterToggleCookie, 1);
            $('#lg-toggle-filter > span').removeClass('fa-chevron-down');
            $('#lg-toggle-filter > span').addClass('fa-chevron-up');
            $('#lg-toggle-filter').removeClass('lg-filter-closed');
            $('#lg-toggle-filter').addClass('lg-filter-open');
            $('.lg-filter').parent().removeClass('hide');
            $('.lg-filter').removeClass('hide');
        } else {
            // Closed
            $.cookie(filterToggleCookie, 0);
            $('#lg-toggle-filter > span').addClass('fa-chevron-down');
            $('#lg-toggle-filter > span').removeClass('fa-chevron-up');
            $('#lg-toggle-filter').addClass('lg-filter-closed');
            $('#lg-toggle-filter').removeClass('lg-filter-open');
            $('.lg-filter').parent().addClass('hide');
            $('.lg-filter').addClass('hide');
        }
        activateControls();
    });
}