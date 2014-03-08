$(document).ready(function() {
    $('td').each(function() {
        makeClickable($(this));
    });
});

function makeClickable($object) {
    $object.on('click', function() {
        var $th = $object.closest('table').find('th').eq($object.index());
        if ($th.hasClass('editable')) {
            makeEditable($object);
        }
    });
}

function makeEditable($object) {
    $object.off('click');
    var $original = $object.text();
    var $th = $object.closest('table').find('th').eq($object.index());
    // tr does not appear to be needed, because each td must
    // contain an ide to accomodate multiple entities on a grid
    var $tr = $object.closest('tr');
    $url =
        '../../cell/edit/' +
        $th.attr('data-role-class') + '/' +
        $th.attr('data-role-field') + '/' +
        $object.attr('data-role-entity-id');
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
                        event.which == tab ||
                        event.which == enter
                    ) {
                        var $val = $(this).val();
                        if ( $val == $original ) {
                            $object.text($val);
                        } else {
                            // This over writes the input field
                            $object.text($val);
                            $url =
                            '../../cell/update/' +
                                    $th.attr('data-role-class') + '/' +
                                    $th.attr('data-role-field') + '/' +
                                    $object.attr('data-role-entity-id');
                                    // console.log('post: '+$url);
                            $.ajax({
                                type: 'POST',
                                url: $url,
                                data: {
                                    data: $val
                                },
                                success: function(responseText, textStatus, XMLHttpRequest) {
                                    $object.load(
                                        '../../cell/value/' +
                                        $th.attr('data-role-class') + '/' +
                                        $th.attr('data-role-field') + '/' +
                                        $object.attr('data-role-entity-id'),
                                        null
                                    )
                                }
                                // dataType : dataType
                            });
                        }

                        // reenable clickyness
                        makeClickable($object);

                    }

                    if (
                        event.which == escape
                    ) {
                        // rewrite original value of ajax loaded input
                        $object.text($original);
                        makeClickable($object);
                    }
                });
                $object.children('input').focus();
            }

        }
    );
}