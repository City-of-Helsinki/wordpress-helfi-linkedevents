import { mountLinkedEvents } from 'linkedevents-filter-list';

((elements) => {

  function initLinkedEvents(element) {
    try {
      let {config, translations} = JSON.parse(element.getAttribute('data-config'));

      element.removeAttribute('data-config');

      mountLinkedEvents({
        element,
        config,
        translations
      });
    } catch (e) {
      console.error(e);
    }
  }

  elements.forEach(initLinkedEvents);

})(document.querySelectorAll('.wp-block-helsinki-linkedevents-grid [data-config]'));
