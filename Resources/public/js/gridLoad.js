// '@LighthartGridBundle/Resources/public/js/grid.js'
// inhibits recurring callback for duration of quiet before executing
var quiet = 300; // 300 ms
var timer = 0;
var xhr = 0;

function addCommas(value) {
  return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function getOffset() {
    cookies = getCookies();
    var pageVal = Number($('input.lg-page-input').val());
    var maxPages = Number($('#lg-max-pages').val());
    if (pageVal > maxPages) {
        pageVal = maxPages;
    }
    var numPerPage = Number(cookies.pageSize);
    var offset = (pageVal - 1) * numPerPage;
    offset = (offset < 0) ? 0 : offset;
    offset = ((offset / numPerPage) > maxPages) ? maxPages - (maxPages % numPerPage) : offset;
    return offset;
}

function getMaxPages() {
    var maxPages = Number($('#lg-max-pages').val());
    return maxPages;
}

function getFlagCookies() {
    var cookies = {};
    $.map(getFlags(), function(value, flag) {
        flag = flag.replace(" ","");
        var flagCookie = "lg-" + getLgCurrentRoute() + "-flag-" + flag;
        cookies[flag] = $.cookie(flagCookie);
    });
    return cookies;
}

function getSelectorCookie(){
    return $.cookie("lg-" + getLgCurrentRoute() + "-selector");
}

function setSelectorCookie() {
    $.cookie("lg-" + getLgCurrentRoute() + "-selector", $('.lg-grid-selector').val(), { expires: 1 });
}


function getCookies() {
    var ajaxVersionCookie = "lg-" + getLgCurrentRoute() + "-version";
    var numPerPagecookie = 'lg-results-per-page';
    var filterCookie = "lg-" + getLgCurrentRoute() + "-filter";
    var offsetCookie = "lg-" + getLgCurrentRoute() + "-offset";
    var searchCookie = "lg-" + getLgCurrentRoute() + "-search";
    var sortCookie = "lg-" + getLgCurrentRoute() + "-sort";
    var cookies = {
        filter: $.cookie(filterCookie),
        offset: $.cookie(offsetCookie),
        pageSize: $.cookie(numPerPagecookie),
        search: $.cookie(searchCookie),
        sort: $.cookie(sortCookie),
        version: $.cookie(ajaxVersionCookie)
    };
    // Setting Defaults
    if ('undefined' == typeof cookies.offset || isNaN(cookies.offset)) {
        cookies.offset = 0;
    }
    if ('undefined' == typeof cookies.pageSize || isNaN(cookies.pageSize)) {
        cookies.pageSize = 10;
    }
    return cookies;
}

function setFlagCookies() {
    $.map(getFlags(), function(value, flag) {
        flag = flag.replace(" ","");
        var flagCookie = "lg-" + getLgCurrentRoute() + "-flag-" + flag;
        $.cookie(flagCookie, value, { expires: 1 });
    });
}

function setCookies(cookies) {
    var ajaxVersionCookie = "lg-" + getLgCurrentRoute() + "-version";
    var numPerPagecookie = 'lg-results-per-page';
    var filterCookie = "lg-" + getLgCurrentRoute() + "-filter";
    var offsetCookie = "lg-" + getLgCurrentRoute() + "-offset";
    var searchCookie = "lg-" + getLgCurrentRoute() + "-search";
    var sortCookie = "lg-" + getLgCurrentRoute() + "-sort";
    // var flagCookie = "lg-" + getLgCurrentRoute() + "-flags";
    $.cookie(filterCookie, cookies.filter, { expires: 1 });
    $.cookie(offsetCookie, cookies.offset, { expires: 1 });
    $.cookie(searchCookie, cookies.search, { expires: 1 });
    $.cookie(sortCookie, cookies.sort, { expires: 1 });
    $.cookie(numPerPagecookie, cookies.pageSize, { expires: 1 });
    $.cookie(ajaxVersionCookie, cookies.version, { expires: 1 });
    setFlagCookies();
}

function gridFocus() {
    var focus = null;
    $('input').each(function() {
        // o for dom 'o'bject
        if ($(this).is(':focus')) {
            focus = $(this);
        }
    });
    return focus;
}

function gridReload(options) {
    var reset = typeof options !== 'undefined' && typeof options.reset !== 'undefined' ? options.reset : false;
    var oldFocus = null;
    var oldVersion = null;
    cookies = getCookies();
    data = {
        pageSize: cookies.pageSize,
        pageOffset: cookies.offset,
        filter: (cookies.filter ? cookies.filter : "").replace("'", "''"),
        search: cookies.search,
    };
    $.map(getFlags(), function(value, flag) {
        var flagCookie = flag;
        if (value) {
            data[flagCookie] = value;
        }
    });
    data['selector'] = $('.lg-grid-selector').val();
    if (xhr) {
        xhr.abort();
    }
    if (timer !== null) {
        clearTimeout(timer);
    }
    timer = setTimeout(function() {
        xhr = $.ajax({
            url: getLgCurrentURI(),
            data: data,
            dataType: 'html',
            type: 'GET',
            cache: false,
            beforeSend: function(xhr) {
                $('.lg-table').addClass('text-muted');
                cookies = getCookies();
                oldVersion = typeof cookies.version == 'undefined' ? 0 : cookies.version;
                cookies.version = new Date().getTime();
                setCookies(cookies);
            },
            success: function(responseText, textStatus, XMLHttpRequest) {
                if (reset) {
                    $('table.lg-table').html($(responseText).find('table.lg-table').html());
                    $('div#lg-header').html($(responseText).find('div#lg-header').html());
                    $('div#lg-footer').html($(responseText).find('div#lg-footer').html());
                    $('div.lg-flags').html($(responseText).find('div.lg-flags').html());
                } else {
                    $('tbody.lg-tbody').html($(responseText).find('tbody.lg-tbody').html());
                    $('div.lg-flags').html($(responseText).find('div.lg-flags').html());
                    $('form.navbar-right').html($(responseText).find('form.navbar-right').html());
                    $('div.lg-paging-controls-header').html($(responseText).find('div.lg-paging-controls-header').html());
                    $('div.lg-middle-controls-header').html($(responseText).find('div.lg-middle-controls-header').html());
                    $('div#lg-footer').html($(responseText).find('div#lg-footer').html());
                    $('tr.lg-headers').html($(responseText).find('tr.lg-headers').html());
                    $('tr.lg-filters').html($(responseText).find('tr.lg-filters').html());

                }
            },
            complete: function() {
                highlightSearches();
                highlightFilters();
                activateControls();
                $('.lg-table').removeClass('text-muted');
                if (oldFocus) {
                    $(oldFocus).blur().focus().val($(oldFocus).val());
                }
                markFlags();
                updates();
                moveCursor();
                focusEdit();
                // makeClicks();
                // make latest timer
                clearTimeout(timer);
            }
        });
    }, quiet);
}

function gridReloadCell(td) {
    cookies = getCookies();
    data = {
        pageSize: cookies.pageSize,
        pageOffset: cookies.offset,
        filter: (cookies.filter ? cookies.filter : "").replace("'", "''"),
        search: cookies.search
    };
    $.map(getFlags(), function(value, flag) {
        var flagCookie = flag;
        if (value) {
            data[flagCookie] = value;
        }
    });

    $.ajax({
        url: getLgCurrentURI(),
        data: data,
        dataType: 'html',
        type: 'GET',
        cache: false,
        beforeSend: function(xhr) {},
        success: function(responseText, textStatus, XMLHttpRequest) {
            var tr = td.parent();
            var col = tr.children("td").index(td);
            var row = tr.parent().children("tr").index(tr);
            var tbody = tr.closest("tbody.lg-tbody");
            var table = tbody.closest("table.lg-table");
            var whichTbody = table.children("tbody.lg-tbody").index(tbody);
            var whichTable = table.parent().children("table.lg-table").index(table);
            newTable = $(responseText).find("table.lg-table").eq(whichTable);
            newTbody = newTable.children("tbody.lg-tbody").eq(whichTbody);
            newTr = newTbody.children("tr").eq(row);
            newTd = newTr.children("td").eq(col);
            $('tr.lg-filters').html($(responseText).find('tr.lg-filters').html());
            td.html(newTd.html());
        },
        complete: function() {
            highlightSearches();
            highlightFilters();
            activateControls();
            markFlags();
            td.children("input.lg-edit-field").on('change', function(event) {
                updateCell($(this), $(this).val());
            });
            moveCursor();
            // makeClicks();
        }
    });
}

function gridReloadAggregates() {
    // cookies = getCookies();
    // data = {
    //     pageSize: cookies.pageSize,
    //     pageOffset: cookies.offset,
    //     filter: (cookies.filter ? cookies.filter : "").replace("'", "''"),
    //     search: cookies.search,
    // };
    // $.map(getFlags(), function(value, flag) {
    //     var flagCookie = flag;
    //     if (value) {s
    //         data[flagCookie] = value;
    //     }
    // });

    // $.ajax({
    //     url: getLgCurrentURI(),
    //     data: data,
    //     dataType: 'html',
    //     type: 'GET',
    //     cache: false,
    //     beforeSend: function(xhr) {},
    //     success: function(responseText, textStatus, XMLHttpRequest) {
    //         var aggregateRows = $("table.lg-table tbody.lg-tbody tr.lg-aggregate-row");
    //         var newAggregateRows = $(responseText).find("table.lg-table tbody.lg-tbody tr.lg-aggregate-row");
    //         aggregateRows.each(function() {
    //             $(this).html(newAggregateRows.parent().children("tr").eq($(this).index()).html());
    //         });
    //     },
    //     complete: function() {
    //     }
    // });
}
