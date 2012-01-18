// jQuery
$(document).ready(function () {
  if (!Modernizr['cssanimations']) {
    var $errorMessage = $('#error-message');
    var $message = $('#success-message');
    var $container, top;
    var totalTime = 4000 / 2; /* IE is not as smooth, so CSS time / 2 */
    var inTime = totalTime * 0.8;
    var outTime = totalTime * 0.2;

    if ($errorMessage.size() !== 0) {
      $container = $errorMessage;
    }
    else if ($message.size() !== 0) {
      $container = $message;
    }
    else {
      return;
    }

    top = parseInt($container.css('top'), 10);
    if (isNaN(top)) {
      top = -48;
    }

    $container.animate({
      'top': 0
    }, inTime, function () {
      setTimeout(function () {
        $container.animate({
          'top': top
        }, outTime);
      }, inTime);
    });
  }
});

// Normal, no jQuery dependency, for IE only
// Needs testing
// if (window.attachEvent) {
//   window.attachEvent('onload', function () {
//     var hasAnimationSupport = true;
//
//     if (Modernizr && !Modernizr['cssanimations']) {
//       hasAnimationSupport = false;
//     }
//     else if (document.getElementsByTagName('html')[0].className.match(/no\-cssanimations/g)) {
//       hasAnimationSupport = false;
//     }
//
//     if (!hasAnimationSupport) {
//       var errorMessage = document.getElementById('error-message');
//       var message = document.getElementById('success-message');
//       var container, top;
//       var totalTime = 4000 / 2; /* IE is not as smooth, so CSS time / 2 */
//       var inTime = totalTime * 0.8;
//       var outTime = totalTime * 0.2;
//
//       if (errorMessage) {
//         container = errorMessage;
//       }
//       else if (message) {
//         container = message;
//       }
//       else {
//         return;
//       }
//
//       top = parseInt(container.style.top, 10);
//       if (isNaN(top)) {
//         top = -48;
//       }
//
//       // 30 frames per second
//       // Move around 2 pixels per frame
//       var plusOrMinus = -(top / 30);
//       (function animate(frame, maxFrame, pixelsPerFrame, done) {
//         if (frame < maxFrame) {
//           container.style.top += pixelsPerFrame + 'px';
//
//           animate(frame + 1, maxFrame, pixelsPerFrame, direction);
//           return;
//         }
//
//         if (frame == maxFrame && typeof done == 'function') {
//           done();
//         }
//       })(0, 30, plusOrMinus, 1, function () {
//         setTimeout(function () {
//           animate(0, 30, -plusOrMinus);
//         }, outTime);
//       });
//     }
//   });
// }
