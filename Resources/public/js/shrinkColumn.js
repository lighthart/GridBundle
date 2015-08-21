
$(document).ready(function() {
    $('th.lg').click(function(){
        var columnNo = $(this).index();
        $(this).closest("table")
            .find("tr td:nth-child(" + (columnNo+1) + ")")
            .toggleClass('lg-min');
        $(this).toggleClass('lg-min');
    });
});