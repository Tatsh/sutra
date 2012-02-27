(function (doc, win) {
  if (win.Modernizr && !win.Modernizr['cssanimations']) {
    // display it for 5 seconds then have it disappear
    // no special effects
    /**
    * Loads messages on page load for browsers that do not support CSS
    *   animations.
    * @private
    */
    var load = function () {
      var errorMessage = doc.getElementById('error-message');
      var message = doc.getElementById('success-message');
      var container;

      if (errorMessage) {
        container = errorMessage;
      }
      else if (message) {
        container = message;
      }
      else {
        return;
      }

      container.style.top = 0;
      setTimeout(function () {
        container.style.top = '-48px';
      }, 2500);
    };

    if (doc.addEventListener) {
      doc.addEventListener('DOMContentLoaded', load, false);
    }
    else if (win.attachEvent) {
      win.attachEvent('onload', load);
    }
  }
})(document, window)
