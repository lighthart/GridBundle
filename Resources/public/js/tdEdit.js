$(document).ready(function() {
    $('td').each(function() {
        makeClickable($(this));
    });
});

function makeClickable(object) {
    object.on('click', function() {
        if ($('input#cell.cell').length > 0) {
        } else {
            var th = object.closest('table').find('th').eq(object.index());
            var td = object;
            if (typeof td.attr('data-role-lg-editable') != 'undefined' && td.attr('data-role-lg-editable')) {
                makeEditable(object);
            }
        }
    });
}

function makeEditable(object) {
    // this function loads the input field and associated controls
    var original = object.text().trim();
    object.off('click');
    input=object.children('input');
    $('.lg-editing').each(function() {
        var val = $(this).children('input').val();
    });
    object.addClass('lg-editing');
    var th = object.closest('table').find('th').eq(object.index());
    if (object.attr('data-role-lg-entity-id')) {
        url = makeURLfromTD(object, 'edit');
    } else {
        url = makeURLfromTD(object, 'new');
    }
    object.load(url, null, function(responseText, textStatus, XMLHttpRequest) {
        if ('GRID_CONFIG_ERROR' == responseText) {
            object.text(original);
        } else {
            $('input#cell').on('keydown', function(event) {
                // this tells us which key is pressed
                // keep in comments if more functionality
                // becomes required
                var tab = 9;
                var enter = 13;
                var escape = 27;
                // var left   = 37
                // var up     = 38;
                // var right  = 39;
                // var down   = 40;
                if (event.which == escape) {
                    // rewrite original value of ajax loaded input
                    update(object, original, original);
                    makeClickable(object);
                }
                if (event.which == tab || event.which == enter) {
                    var val = object.children('input').val();
                    update(object, original, val);
                    if (event.which == enter) {
                        // This needs to be rethought
                        // Automatic traversal down on enter ?

                        // if (object.closest('tr').is(':last-child')) {
                        //     // We are at bottom of column
                        //     makeEditable(
                        //         // get tr
                        //         object.closest('tbody').find('tr')
                        //         // find the first row
                        //         // because we were at the end
                        //         // and need to get to the beginning
                        //         .first()
                        //         // find the index that matches our td,
                        //         // and then get the next one
                        //         .find('td').eq(object.index()).next());
                        // } else {
                        //     // We are NOT at bottom of column
                        //     makeEditable(
                        //         // get tr
                        //         object.closest('tr')
                        //         // get next row
                        //         .next()
                        //         // find td that has same index
                        //         // to keep in same column
                        //         .find('td').eq(object.index()));
                        // }
                    }
                    if (event.which == tab) {
                        if (object.is(':last-child')) {
                            // we are at end of row
                            thdr = object.closest('table').find('th');
                            var nextth = object.closest('table').find('th').first();
                            while (typeof nextth.attr('data-role-lg-editable') != 'undefined' && th.attr('data-role-lg-editable')) {
                                // keep going until one can be editted
                                nextth = nextth.next();
                            }
                            makeEditable(object.closest('tr')
                                // get next row
                                .next()
                                // find td that has same index
                                // to keep in same column
                                .find('td').eq(nextth.index()));
                        } else {
                            var nextth = object.closest('table').find('th').eq(object.index()).next();
                            while (typeof nextth.attr('data-role-lg-editable') != 'undefined' && th.attr('data-role-lg-editable')) {
                                // keep going until one can be editted
                                nextth = nextth.next();
                            }
                            // we are NOT at end of row
                            makeEditable(object.closest('tr')
                                // find td that has same index
                                // to keep in same column
                                .find('td').eq(nextth.index()));
                        }
                    }
                }
            });
            object.children('input').focus();
        }
    });
}

function update(object, original, val) {
    object.removeClass('lg-editing');
    var th = object.closest('table').find('th').eq(object.index());
    if ( val == '' || val ==null ) {
        // not string safe...
        val = 0;
    }
    if (val == original) {
        object.text(val);
    } else {
        // This over writes the input field
        object.text(val);
        if (object.attr('data-role-lg-new') && !object.attr('data-role-lg-entity-id')) {
            url = makeURLfromTD(object, 'create');
            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    data: val
                },
                success: function(responseText, textStatus, XMLHttpRequest) {

                    $('.lg-table').addClass('text-muted');
                    location.reload(true);
                }
                // dataType : dataType
            });

        } else {
            url = makeURLfromTD(object, 'update');
            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    // possible as form fields?
                    // eg data: {object.attr('data-role-lg-field'):val}
                    data: val
                },
                success: function(responseText, textStatus, XMLHttpRequest) {
                    // console.log(val);
                    // console.log(url);
                    $('.lg-table').addClass('text-muted');
                    location.reload(true);
                    // object.load(url, null);
                }
                // dataType : dataType
            });
        }
    }
    // reenable clickyness
    makeClickable(object);
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

    if (url.indexOf('___') != -1){
        url = url.split('___');
        url[0]=url[0].substr(0,url[0].lastIndexOf('/')+1);
        url=url.join('');
    }
    return url;
}