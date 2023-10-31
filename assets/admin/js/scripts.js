"use strict";

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

(function (wp) {
  var __ = wp.i18n.__,
      registerBlockType = wp.blocks.registerBlockType,
      ServerSideRender = wp.serverSideRender,
      useBlockProps = wp.blockEditor.useBlockProps,
      _wp$element = wp.element,
      Fragment = _wp$element.Fragment,
      createElement = _wp$element.createElement,
      _wp$components = wp.components,
      SelectControl = _wp$components.SelectControl,
      TextControl = _wp$components.TextControl,
      PanelRow = _wp$components.PanelRow,
      PanelBody = _wp$components.PanelBody,
      withSelect = wp.data.withSelect,
      compose = wp.compose.compose,
      InspectorControls = wp.editor.InspectorControls;
  var EventsConfigSelect = compose(withSelect(function (select, selectProps) {
    return {
      posts: select('core').getEntityRecords('postType', 'linked_events_config', {
        orderby: 'title',
        order: 'asc',
        per_page: 100,
        status: 'publish'
      })
    };
  }))(function (props) {
    var options = [];

    if (props.posts) {
      options.push({
        value: 0,
        label: __('Select configuration', 'helsinki-linkedevents')
      });
      props.posts.forEach(function (post) {
        options.push({
          value: post.id,
          label: post.title.rendered
        });
      });
    } else {
      options.push({
        value: 0,
        label: __('Loading', 'helsinki-linkedevents')
      });
    }

    return createElement(SelectControl, {
      label: __('Events configuration', 'helsinki-linkedevents'),
      value: props.attributes.configID,
      onChange: function onChange(id) {
        props.setAttributes({
          configID: id
        });
      },
      options: options
    });
  });

  function eventCountOptions() {
    return [{
      label: 3,
      value: 3
    }, {
      label: 5,
      value: 5
    }];
  }
  /**
    * InspectorControls
    */


  function inspectorControls(props) {
    return createElement(InspectorControls, {}, createElement(PanelBody, {
      title: __('Settings', 'helsinki-linkedevents'),
      initialOpen: true
    }, configSelectControl(props), eventsCountControl(props)));
  }

  function configSelectControl(props) {
    return createElement(PanelRow, {}, createElement(EventsConfigSelect, props));
  }

  function eventsCountControl(props) {
    return createElement(PanelRow, {}, createElement(SelectControl, {
      label: __('Number of events', 'helsinki-linkedevents'),
      value: props.attributes.eventsCount,
      onChange: function onChange(count) {
        props.setAttributes({
          eventsCount: count
        });
      },
      options: eventCountOptions()
    }));
  }

  function titleTextControl(props) {
    return createElement(PanelRow, {}, createElement(TextControl, {
      label: __('Title', 'helsinki-linkedevents'),
      type: 'text',
      value: props.attributes.title,
      onChange: function onChange(text) {
        props.setAttributes({
          title: text
        });
      }
    }));
  }

  function hdsContentTextRich(props, config) {
    return wp.element.createElement(wp.blockEditor.RichText, {
      tagName: 'p',
      className: config.className ? config.className : 'content__text',
      value: config.textAttribute ? props.attributes[config.textAttribute] : props.attributes.contentText,
      onChange: function onChange(value) {
        props.setAttributes(config.textAttribute ? _defineProperty({}, config.textAttribute, value) : {
          contentText: value
        });
      },
      placeholder: config.placeholder ? config.placeholder : wp.i18n.__('Excerpt', 'helsinki-linkedevents')
    });
  }

  function hdsContentTitleRich(props, config) {
    return wp.element.createElement(wp.blockEditor.RichText, {
      tagName: 'h2',
      className: config.className ? config.className : 'content__heading',
      value: config.titleAttribute ? props.attributes[config.titleAttribute] : props.attributes.contentTitle,
      onChange: function onChange(value) {
        props.setAttributes(config.titleAttribute ? _defineProperty({}, config.titleAttribute, value) : {
          contentTitle: value
        });
      },
      allowedFormats: [],
      placeholder: config.placeholder ? config.placeholder : wp.i18n.__('Title', 'helsinki-linkedevents')
    });
  }
  /**
    * Elements
    */


  function preview(props) {
    if (props.isSelected) {
      return createElement('div', useBlockProps(), createElement('div', {
        className: 'helsinki-events events'
      }, createElement('div', {
        className: 'hds-container'
      }, hdsContentTitleRich(props, {
        placeholder: __('This is the title', 'hds-wp'),
        titleAttribute: 'title',
        className: 'events__title'
      }), hdsContentTextRich(props, {
        placeholder: __('This is the excerpt.', 'hds-wp'),
        className: 'events__excerpt'
      }), createElement(ServerSideRender, {
        block: 'helsinki-linkedevents/grid',
        attributes: _objectSpread(_objectSpread({}, props.attributes), {}, {
          isEditRender: true
        })
      }))));
    } else {
      return createElement('div', useBlockProps(), createElement(ServerSideRender, {
        block: 'helsinki-linkedevents/grid',
        attributes: _objectSpread({}, props.attributes)
      }));
    }
  }
  /**
    * Edit
    */


  function edit() {
    return function (props) {
      props.attributes.eventsCount = parseInt(props.attributes.eventsCount);
      props.attributes.blockId = props.clientId;
      return createElement(Fragment, {}, inspectorControls(props), preview(props));
    };
  }

  function save() {
    return function (props) {
      return null;
    };
  }
  /**
    * Register
    */


  registerBlockType('helsinki-linkedevents/grid', {
    apiVersion: 2,
    title: __('Helsinki - Events', 'helsinki-linkedevents'),
    category: 'helsinki-linkedevents',
    icon: 'calendar-alt',
    keywords: [__('events', 'helsinki-linkedevents'), 'linked events', 'Helsinki - Events Grid'],
    supports: {
      html: false,
      anchor: true
    },
    attributes: {
      configID: {
        type: 'string',
        default: 0
      },
      eventsCount: {
        type: 'number',
        default: 3
      },
      title: {
        type: 'string',
        default: ''
      },
      contentText: {
        type: 'string',
        default: ''
      },
      anchor: {
        type: 'string',
        default: ''
      },
      blockId: {
        type: 'string'
      },
      isEditRender: {
        type: 'boolean',
        default: false
      }
    },
    edit: edit()
  });
})(window.wp);