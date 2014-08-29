$(document).ready(function() {
    $('.lg-grid-pagesize').on("click",
        function(){
            // alert($(this).attr('data-role-lg-pagesize'));
            var cookie = 'lg-grid-'+getLgCurrentRoute()+'-results-per-page';
            console.log(cookie);
            $('.lg-grid-pagesize-button').html($(this).html());
            $('#lg-grid-results-per-page').val($(this).attr('data-role-lg-pagesize'));
            $.cookie(cookie, $(this).attr('data-role-lg-pagesize'));

            // load ajax stuff here
            // $.ajax();
        }
        );
    // $(".lg-grid-results-per-page").on("click",
    //     function(){
    //         var value=$("#lg-grid-results-per-page").val();
    //         var cookie = 'lgGrid-'+getLgCurrentRoute()+'-results-per-page';
    //         console.log('cookie: '+cookie);
    //         console.log( getLgAppRoot() );
    //         console.log( getLgCurrentURI() );
    //         console.log( getLgCurrentRoute() );
    //         console.log( value );
    //         // console.log($.cookie('MesdPresentationSidebarSize'));

    //     });
});

