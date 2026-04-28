"use strict";

(function (elements) {
  elements.forEach(element => {
    element.style.cursor = 'pointer';
    var down,
      up,
      link = element.querySelector('.event__link');
    element.onmousedown = function () {
      return down = +new Date();
    };
    element.onmouseup = function () {
      up = +new Date();
      if (up - down < 200) {
        link.click();
      }
    };
  });
})(document.querySelectorAll('.helsinki-events .event'));
