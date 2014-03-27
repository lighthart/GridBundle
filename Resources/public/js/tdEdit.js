$(document).ready(function() {
    $('td').each(function() {
        makeClickable($(this));
    });
});

function makeClickable($object) {
    $object.on('click', function() {
        var $th = $object.closest('table').find('th').eq($object.index());
        if ($th.hasClass('lg-editable')) {
            makeEditable($object);
        }
    });
}

function makeEditable($object) {
    var $original = $object.text().trim();

    $('.lg-editing').each(
        function() {
            var $val = $(this).children('input').val();
            update($(this), $original, $val);
        }
    );
    $object.addClass('lg-editing');

    $object.off('click');

    var $th = $object.closest('table').find('th').eq($object.index());
    $url = makeURLfromTD($object, 'edit');

    $object.load(
        $url,
        null,
        function(responseText, textStatus, XMLHttpRequest) {
            if ('GRID_CONFIG_ERROR' == responseText) {
                $object.text($original);
            } else {
                $('input#cell').on('keydown', function(event) {
                    // this tells us which key is pressed
                    // keep in comments if more functionality
                    // becomes required
                    // console.log(event.which);
                    var tab = 9;
                    var enter = 13;
                    var escape = 27;
                    // var left   = 37
                    // var up     = 38;
                    // var right  = 39;
                    // var down   = 40;
                    if (
                        event.which == escape
                    ) {
                        // rewrite original value of ajax loaded input
                        $object.text($original);
                        makeClickable($object);
                    }

                    if (
                        event.which == tab ||
                        event.which == enter
                    ) {
                        var $val = $object.children('input').val();
                        update($object, $original, $val);
                        if (event.which == enter) {
                            if ($object.closest('tr').is(':last-child')) {
                                // We are at bottom of column
                                makeEditable(
                                    // get tr
                                    $object.closest('tbody')
                                    .find('tr')
                                    // find the first row
                                    // because we were at the end
                                    // and need to get to the beginning
                                    .first()
                                    // find the index that matches our td,
                                    // and then get the next one
                                    .find('td').eq($object.index()).next()
                                );
                            } else {
                                // We are NOT at bottom of column
                                makeEditable(
                                    // get tr
                                    $object.closest('tr')
                                    // get next row
                                    .next()
                                    // find td that has same index
                                    // to keep in same column
                                    .find('td').eq($object.index())
                                );
                            }
                        }

                        if (event.which == tab) {
                            if ($object.is(':last-child')) {
                                // we are at end of row

                                $thdr = $object.closest('table').find('th');
                                var $nextth = $object.closest('table').find('th').first();
                                while (!$nextth.hasClass('lg-editable')){
                                    // keep going until one can be editted
                                    $nextth = $nextth.next();
                                }

                                makeEditable(
                                     $object.closest('tr')
                                    // get next row
                                    .next()
                                    // find td that has same index
                                    // to keep in same column
                                    .find('td').eq($nextth.index())
                                );
                            } else {

                                var $nextth = $object.closest('table').find('th').eq($object.index()).next();
                                while (!$nextth.hasClass('lg-editable')){
                                    // keep going until one can be editted
                                    $nextth = $nextth.next();
                                }

                                // we are NOT at end of row
                                makeEditable(
                                     $object.closest('tr')
                                    // find td that has same index
                                    // to keep in same column
                                    .find('td').eq($nextth.index())
                                );

                            }

                        }
                    }
                });

                $object.children('input').focus();
            }

        }
    );
}

function update($object, $original, $val) {
    $object.removeClass('lg-editing')
    var $th = $object.closest('table').find('th').eq($object.index());
    if ($val == $original) {
        $object.text($val);
    } else {
        // This over writes the input field
        $object.text($val);
        $url = makeURLfromTD($object, 'update');
        $.ajax({
            type: 'POST',
            url: $url,
            data: {
                data: $val
            },
            success: function(responseText, textStatus, XMLHttpRequest) {
                $url = makeURLfromTD($object, 'value'),
                $object.load($url, null);
            }
            // dataType : dataType
        });
    }

    // reenable clickyness
    makeClickable($object);
}

function makeURLfromTD($object, $action) {
    var $th = $object.closest('table').find('th').eq($object.index());
    // td must have data-role-entity-id
    // or
    // tr must have data-role-parent-entity-id
    // the second case should be used for grid that expose
    // a single entity only

    // the ../../../ is based on bundle config

    var $tr = $object.closest('tr');
    if ($object.attr('data-role-lg-entity-id')) {
        $url =
            '../../../cell/' + $action + '/' +
            $th.attr('data-role-lg-class') + '/' +
            $th.attr('data-role-lg-field') + '/' +
            $object.attr('data-role-lg-entity-id');
    } else {
        $url =
            '../../../cell/' + $action + '/' +
            $th.attr('data-role-lg-class') + '/' +
            $th.attr('data-role-lg-field') + '/' +
            $tr.attr('data-role-lg-parent-entity-id');
    }

    return $url;
}