$(document).ready(function() {
    $('td').each(function() {
        makeClickable($(this));
    });
});

function makeClickable ($object) {
    $object.on('click', function() {
        var $th = $object.closest('table').find('th').eq($object.index());
        if ($th.hasClass('editable')){
            makeEditable($object);
         }
    });
}

function makeEditable( $object ){
    $object.off('click');
    var $original = $object.text();
    var $th = $object.closest('table').find('th').eq($object.index());
    // tr does not appear to be needed, because each td must
    // contain an ide to accomodate multiple entities on a grid
    var $tr = $object.closest('tr');
    $object.load(
        'celledit/'+
        $th.attr('data-role-class')+'/'+
        $th.attr('data-role-field')+'/'+
        $object.attr('data-role-entity-id'),
        null,
        function(responseText, textStatus, XMLHttpRequest) {
            if ( 'GRID_CONFIG_ERROR' == responseText ) {
                $object.text($original);
            } else {
                $('input#gridcell').on('keydown', function( event ) {
                    //console.log(event.which)
                    // this tells us which key is pressed
                    // keep in commentsif more functionality
                    // becomes required
                    // console.log(event.which);
                    var tab    = 9;
                    var enter  = 13;
                    var escape = 27;
                    // var left   = 37
                    // var up     = 38;
                    // var right  = 39;
                    // var down   = 40;
                    if (
                        event.which == tab   ||
                        event.which == enter
                        ){
                        var $val = $(this).val();
                        if ($object.val() == $original) {
                            $object.text($original);
                        } else {
                            // This over writes the input field
                            $object.text($val);
                            $.ajax({
                                type     :    'POST',
                                url      :    'cellupdate/'+
                                            $th.attr('data-role-class')+'/'+
                                            $th.attr('data-role-field')+'/'+
                                            $object.attr('data-role-entity-id'),
                                data     : { data : $val },
                                success  : function(responseText, textStatus, XMLHttpRequest) {
                                            $object.load(
                                                'cellvalue/'+
                                                $th.attr('data-role-class')+'/'+
                                                $th.attr('data-role-field')+'/'+
                                                $object.attr('data-role-entity-id'),
                                                null
                                                )
                                           }
                                // dataType : dataType
                            });
                            makeClickable($object);
                        }
                        // also do the post here

                    }

                    if (
                        event.which == escape
                        ){
                        // also do the post here
                        var $parent = $object.parent();
                        // $(this).remove();
                        $parent.text($original);
                    }
                });
                $object.children('input').focus();
            }

        }
    );
}

