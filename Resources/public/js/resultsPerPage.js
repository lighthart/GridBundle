$(document).ready(function() {
    $('.lg-grid-pagesize').on("click", function() {
        // alert($(this).attr('data-role-lg-pagesize'));
        var cookie = 'lg-grid-' + getLgCurrentRoute() + '-results-per-page';
        console.log(cookie);
        $('.lg-grid-pagesize-button').html($(this).html());
        $('#lg-grid-results-per-page').val($(this).attr('data-role-lg-pagesize'));
        $.cookie(cookie, $(this).attr('data-role-lg-pagesize'));
        // load ajax stuff here
        $('.lg-grid').load(getLgCurrentURI().'?pageSize=10');
    });
});