"use strict";

(function (elements) {
  elements.forEach(element => {
    element.style.cursor = 'pointer';

    var down, up, link;

    element.onmousedown = function (event) {
      if (event.target.nodeName === 'A') {
        link = null;
        return;
      }

      var card = event.target.closest('.react-search__list-container .card');
      link = card.querySelector('.card__link');

      return down = +new Date();
    };

    element.onmouseup = function () {
      if (link) {
        up = +new Date();
        if (up - down < 200) {
          link.click();
        }
      }
    };
  });
})(document.querySelectorAll('.helsinki-events .events__container'));
