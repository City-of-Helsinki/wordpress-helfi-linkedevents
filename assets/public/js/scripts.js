"use strict";

(function (config) {
  'use strict';

  var events = config.events,
      ajax = config.ajax;

  if (events.length > 0) {
    for (var i = 0; i < events.length; i++) {
      _initEventsElement(events[i]);
    }
  }

  function _initEventsElement(element) {
    var _loadMore = element.querySelector('.events__more .button');

    if (_loadMore) {
      _loadMore.addEventListener('click', _clickLoadMore);
    }
  }

  function _currentEventsContainer(element) {
    return element.closest('.helsinki-events').querySelector('.events__container');
  }

  function _clickLoadMore(event) {
    event.preventDefault();

    _loadMoreEvents(event.currentTarget);
  }

  function _loadMoreEvents(button) {
    button.disabled = true;

    var _container = _currentEventsContainer(button),
        _formData = new FormData();

    _formData.append('action', button.getAttribute('data-action'));

    _formData.append('config', parseInt(button.getAttribute('data-config'), 10));

    var _currentPage = parseInt(button.getAttribute('data-paged'), 10);

    _formData.append('paged', _currentPage);

    _ajaxRequest(ajax.ajaxUrl, _formData).then(function (response) {
      if (response) {
        response = JSON.parse(response);
      }

      if (!response.data.html) {
        button.remove();
        return;
      }

      _container.insertAdjacentHTML('beforeend', response.data.html);

      if (!response.data.more) {
        button.remove();
      } else {
        _currentPage++;
        button.setAttribute('data-paged', _currentPage);
        button.disabled = false;
      }
    }).catch(function (error) {
      button.remove();
    });
  }

  function _ajaxRequest(endpoint, formData) {
    return new Promise(function (resolve, reject) {
      var xhr = new XMLHttpRequest();
      xhr.open('POST', endpoint);

      xhr.onload = function () {
        if (this.status >= 200 && this.status < 300) {
          resolve(xhr.response);
        } else {
          reject(xhr.response);
        }
      };

      xhr.onerror = function () {
        reject({
          status: this.status,
          message: xhr.statusText
        });
      };

      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      xhr.send(formData);
    });
  }
})({
  events: document.querySelectorAll('.helsinki-events'),
  ajax: helsinkiLinkedEvents
});

(function () {
  document.querySelectorAll('.helsinki-events .event').forEach(initEvent);

  function initEvent(element) {
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
  }
})();