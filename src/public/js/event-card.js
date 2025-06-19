(() => {
  document.querySelectorAll('.helsinki-events .event').forEach(initEvent);

  function initEvent(element) {
    element.style.cursor = 'pointer';

    let down, up, link = element.querySelector('.event__link');
    element.onmousedown = () => down = +new Date();
    element.onmouseup = () => {
      up = +new Date();
      if ((up - down) < 200) {
        link.click();
      }
    }
  }
})();
