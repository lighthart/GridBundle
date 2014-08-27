function getLgAppRoot() { return "{{app.request.getBasePath}}/"; }
function getLgCurrentURI() { return "{{app.request.requesturi}}"; }


// results per page
$(document).ready(function() {
    $(".lg-grid-results-per-page-less").on("click",
        function(){
            var value =$("#lg-grid-header-results-per-page").val();
            console.log('cookie: ');
            console.log( getLgAppRoot() );
            console.log( getLgCurrentURI() );
            // console.log($.cookie('MesdPresentationSidebarSize'));

        });
    $(".lg-grid-results-per-page-more").on("click",
        function(){
            alert("more");
        });
});
