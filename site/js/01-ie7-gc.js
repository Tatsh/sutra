if (isIE('lte 7')) {
  if (jQuery && jQuery.fn && jQuery.fn.remove) {
    (function ($) {
      $.fn.originalRemove = $.fn.remove;
      $.fn.remove = function (selector) {
        this.originalRemove(selector);
        for (var k in this) {
          delete this[k];
        }
      };
    })(jQuery);
  }
}
