    // (function($) {
    //     $.fn.hasVerticalScrollBar = function() {
    //         return this.get(0) ? this.get(0).scrollHeight > this.innerheight() : false;
    //     }
    // })(jQuery);
    (function($) {
        $.fn.findScrollBar = function() {
            element = this;
            while (!(element.get(0) ? element.get(0).scrollHeight > element.innerHeight() : false) && !element.is("body")) {
                console.log(element);
                element = element.parent();
            }
            console.log(element);
            return element;
        }
    })(jQuery);