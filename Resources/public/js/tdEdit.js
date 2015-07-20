// '@LighthartGridBundle/Resources/public/js/tdEdit.js'

cursor = {
    down: function(object) {
        var td = object.parent();
        var tr = td.parent();
        var col = tr.children().index(td);
        var cols = tr.children();
        var row = tr.index();
        var rows = tr.parent().children("tr");
        row += 1;
        next = rows.eq(row).children().eq(col).children("input");
        while (0 === next.length) {
            if (row < rows.length) {
                row += 1;
            } else {
                row = 0;
                if (col < cols.length) {
                    col += 1;
                } else {
                    col = 0;
                }
            }
            next = rows.eq(row).children().eq(col).children("input");
            if (next.length) {
                break;
            }
        }
        next.focus();
    },

    up: function(object) {
        var td = object.parent();
        var tr = td.parent();
        var col = tr.children().index(td);
        var cols = tr.children();
        var row = tr.index();
        var rows = tr.parent().children("tr");
        row -= 1;
        next = rows.eq(row).children().eq(col).children("input");
        // have to check row because .eq() takes negative index
        while (0 === next.length || row < 0) {
            if (row > 0) {
                row -= 1;
            } else {
                row = rows.length;
                if (col > 0) {
                    col -= 1;
                } else {
                    col = cols.length;
                }
            }
            next = rows.eq(row).children().eq(col).children("input");
            if (next.length) {
                break;
            }
        }
        next.focus();
    },

    left: function(object) {
        var td = object.parent();
        var tr = td.parent();
        var col = tr.children().index(td);
        var cols = tr.children();
        var row = tr.index();
        var rows = tr.parent().children("tr");
        next = tr.prev('td').children("input");

        // this bit for wrapping
        // while (0 === next.length || col < 0) {
        //     if (col > 0) {
        //         col -= 1;
        //     } else {
        //         col = cols.length;
        //         if (row > 0) {
        //             row -= 1;
        //         } else {
        //             row = rows.length;
        //         }
        //     }



        //     next = tr.parent().children("tr").eq(row).children().eq(col).children("input");

        //     if (next.length) {
        //         break;
        //     }
        // }
        next.focus();
    },

    right: function(object) {
        var td = object.parent();
        var tr = td.parent();
        var col = tr.children().index(td);
        var cols = tr.children();
        var row = tr.index();
        var rows = tr.parent().children("tr");
        next = tr.next('td').children("input");

        // this bit for wrapping
        // while (next.length === 0) {
        //     if (col < cols.length) {
        //         col += 1;
        //     } else {
        //         col = 0;
        //         if (row < rows.length) {
        //             row += 1;
        //         } else {
        //             row = 0;
        //         }
        //     }

        //     next = tr.parent().children("tr").eq(row).children().eq(col).children("input");

        //     if (next.length) {
        //         break;
        //     }
        // }
        next.focus();
    }
};


function moveCursor() {
    $('input.lg-edit-field').off('keydown');
    $('input.lg-edit-field').on('keydown', function(event) {
        // this tells us which key is pressed
        // keep in comments if more functionality
        // becomes required
        var tab = 9;
        var enter = 13;
        var escape = 27;
        var left = 37;
        var up = 38;
        var right = 39;
        var down = 40;
        dir = event.which;

        if (event.which == escape) { /* unknown at moment  */ }

        if (event.which == down)   { cursor.down($(this));  }
        if (event.which == up)     { cursor.up($(this));    }
        // if (event.which == left)   { cursor.left($(this));  }
        // if (event.which == right)  { cursor.right($(this)); }
        if (event.which == tab) {
            if (event.shiftKey === true) {
                cursor.left($(this));
            } else {
                cursor.right($(this));
            }
        }
        if (event.which == enter) {
            if (event.shiftKey === true) {
                cursor.up($(this));
            } else {
                cursor.down($(this));
            }
        }
    });
}

function moveCursor() {
    $('input.lg-edit-field').off('keydown');
    $('input.lg-edit-field').on('keydown', function(event) {
        // this tells us which key is pressed
        // keep in comments if more functionality
        // becomes required
        var tab = 9;
        var enter = 13;
        var escape = 27;
        var left = 37;
        var up = 38;
        var right = 39;
        var down = 40;
        dir = event.which;

        if (event.which == escape) { /* unknown at moment  */ }

        // if (event.which == down)   { cursor.down($(this));  }
        // if (event.which == up)     { cursor.up($(this));    }
        // if (event.which == left)   { cursor.left($(this));  }
        // if (event.which == right)  { cursor.right($(this)); }
        if (event.which == tab) {
            if (event.shiftKey === true) {
                cursor.left($(this));
            } else {
                cursor.right($(this));
            }
        }
        if (event.which == enter) {
            if (event.shiftKey === true) {
                cursor.up($(this));
            } else {
                cursor.down($(this));
            }
        }
    });
}



function updates() {
    $('input.lg-edit-field').on('change', function(event) {
        updateCell($(this), $(this).val());
    });
}

function focusEdit() {
    $('input.lg-edit-field').on('focus click mouseup mousedown', function(event) {
        event.preventDefault();
        $(this).select();
    });
}


function updateCell(object, val) {

    if (val !== '') {
        var isMoney = object.hasClass('money');
        var td = object.parent();
        var tr = td.parent();
        var col = tr.children().index(td);
        var row = tr.index();

        if (parseFloat(val) < 0) {
            var negative = true;
        } else {
            var negative = false;
        }

        val = addCommas(val);
        // if (negative) {
        //     val = "("+val+")";
        //     object.addClass("negative");
        //     object.removeClass("positive");
        // } else {
        //     object.removeClass("negative");
        //     object.addClass("positive");
        // }

        //
        if (isMoney) {
            newValue = parseFloat(object.val().replace(/[^0-9\.\-]/g,''));
            oldValue = parseFloat(object.attr("value").replace(/[^0-9\.\-]/g,''));
        } else {
        }

        object.val(val);
        object.attr("value",val);
        object.text(val);

        if (isMoney) {
            val = val.replace(/[^0-9\.\-]/g,'');
            difference = newValue - oldValue;
        } else {
            difference = null;
        }

        if (object.parent().attr('data-role-lg-new') && !object.parent().attr('data-role-lg-entity-id')) {
            console.log('create');
            url = makeURLfromTD(object.parent(), 'create');
            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    data: val
                },
                success: function(responseText, textStatus, XMLHttpRequest) {
                    updateAggregate(td, difference);
                    // gridReloadCell(td);
                },
            });
        } else {
            console.log('update');
            url = makeURLfromTD(object.parent(), 'update');
            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    data: val
                },
                success: function(responseText, textStatus, XMLHttpRequest) {
                    updateAggregate(td, difference);
                    // updateSuper(td, difference);
                    // gridReloadCell(td);
                },
            });
        }
    }
}

function updateAggregate(td, difference) {

        if (typeof difference === 'undefined'  || difference === null) {
        } else {
            var tr = td.parent();
            var col = tr.children().index(td);
            var row = tr.index();
            var aggregateRow = tr.parent().children("tr.lg-aggregate-row");
            var aggregateCell = aggregateRow.children("td").eq(col);
            var oldAggregateHtml = aggregateCell.html();
            var oldAggregateValue = aggregateCell.text();
            var newAggregateValue = parseFloat(oldAggregateValue.replace(/[^0-9\.\-]/g,'')) + parseFloat(difference);
            if (isNaN(newAggregateValue)) {
            } else {
                var newAggregateHtml = oldAggregateHtml.replace(oldAggregateValue,addCommas(newAggregateValue));
                aggregateCell.html(newAggregateHtml);
                aggregateCell.children("input").val(newAggregateValue);
            }
            updateSuper(td, difference);
            gridReloadAggregates();
        }
}

function updateSuper(td, difference) {
        td.children("input").off('change');
        var tr = td.parent();
        var col = tr.children().index(td);
        var row = tr.index();
        var superCells = tr.children("td.lg-editable");
        var superCell = superCells.first();
        var superInput = superCell.children("input");
        if (superInput.val()) {
            var oldSuperHtml = superCell.html();
            var oldSuperValue = superInput.val().replace(/[^0-9\.\-]/g,'');
            var newSuperValue = parseFloat(oldSuperValue) + parseFloat(difference);
            if (isNaN(newSuperValue)) {
            } else {
                console.log('here');
                superInput.val(addCommas(newSuperValue));
            }
            td.children("input").on('change', function(event) {
                updateCell($(this), $(this).val());
            });
        }
        // updateAggregate(td, difference);
}

function makeURLfromTD(td, action) {
    var th = td.closest('table').find('th').eq(td.index());
    var thid = th.attr('data-role-lg-parent-entity-id');
    // td must have data-role-entity-id
    // or
    // tr must have data-role-parent-entity-id
    // the second case should be used for grid that expose
    // a single entity only
    // the ../../../ is based on bundle config
    // try to make that more extensible
    var tr = td.closest('tr');
    var trid = tr.attr('data-role-lg-parent-entity-id');
    var tdid = td.attr('data-role-lg-entity-id');
    if (action == 'update' && td.attr('data-role-lg-update')) {
        url = td.attr('data-role-lg-update').replace('~entity_id~', td.attr('data-role-lg-entity-id')).replace('~col_id~', thid).replace('~row_id~', trid);
    } else if (action == 'create' && td.attr('data-role-lg-new')) {
        url = td.attr('data-role-lg-new').replace('~entity_id~', td.attr('data-role-lg-entity-id')).replace('~col_id~', thid).replace('~row_id~', trid);
    } else if (action == 'new') {
        url = getLgAppRoot() + 'cell/' + action + '/' + th.attr('data-role-lg-class') + '/' + th.attr('data-role-lg-field');
    } else if (typeof tdid != 'undefined' && tdid) {
        // otherwise try to figure it out yourself
        url = getLgAppRoot() + 'cell/' + action + '/' + th.attr('data-role-lg-class') + '/' + th.attr('data-role-lg-field') + '/' + tdid;
    } else {
        url = getLgAppRoot() + 'cell/' + action + '/' + th.attr('data-role-lg-class') + '/' + th.attr('data-role-lg-field') + '/' + trid;
    }
    if (url.indexOf('___') != -1) {
        url = url.split('___');
        url[0] = url[0].substr(0, url[0].lastIndexOf('/') + 1);
        url = url.join('');
    }
    return url;
}