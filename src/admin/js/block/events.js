(function(wp){

  const __ = wp.i18n.__,
  	{ registerBlockType } = wp.blocks,
  	ServerSideRender = wp.serverSideRender,
  	{ useBlockProps } = wp.blockEditor,
  	{ Fragment, createElement, useEffect } = wp.element,
  	{ SelectControl, TextControl, PanelRow, PanelBody } = wp.components,
  	{ withSelect } = wp.data,
  	{ compose } = wp.compose,
  	{ InspectorControls } = wp.editor;


  const EventsConfigSelect = compose(withSelect(function(select, selectProps){
    return {posts: select('core').getEntityRecords(
      'postType',
      'linked_events_config',
      {
        orderby : 'title',
        order : 'asc',
        per_page: 100,
        status : 'publish',
      }
    )};
  }))(function(props){

    var options = [];
    if ( props.posts ) {
      options.push({
        value: 0,
        label: __( 'Select configuration', 'helsinki-linkedevents' )}
      );

      props.posts.forEach( function(post) {
        options.push({
          value:post.id,
          label:post.title.rendered
        });
      });
    } else {
      options.push({
        value: 0,
        label: __( 'Loading', 'helsinki-linkedevents' )}
      );
    }

    return createElement(SelectControl, {
      label: __( 'Events configuration', 'helsinki-linkedevents' ),
      value: props.attributes.configID,
      onChange: function(id) {
        props.setAttributes({
          configID: id,
        });
      },
      options: options,
    });
  });

  function eventCountOptions() {
    return [
      {label: 3, value: 3},
      {label: 5, value: 5},
      {label: 10, value: 10},
    ];
  }

  /**
    * InspectorControls
    */
  function inspectorControls(props) {
    return createElement(
      InspectorControls, {},
      createElement(
        PanelBody, {
          title: __( 'Settings', 'helsinki-linkedevents' ),
          initialOpen: true,
        },
        configURLControl(props),
        eventsCountControl(props)
      )
    );
  }

  function configSelectControl(props) {
    return createElement(
      PanelRow, {},
      createElement(EventsConfigSelect, props)
    );
  }

  function configURLControl(props) {
    return createElement(
      Fragment, {},
      createElement(TextControl, {
        label: __( 'Event Listing URL', 'helsinki-linkedevents' ),
        type: 'text',
        value: props.attributes.configURL,
        onChange: function(url) {
          props.setAttributes({
            configURL: url,
          });
        },
      }),
      configURLAssistiveText(props),
    );
  }

  function configURLAssistiveText(props) {
    return createElement(
      PanelRow, {},
      //Create an event listing URL at <a>https://tapahtumat.hel.fi/fi/haku</a>. Copy and paste the URL here.
      createElement('small', { style: {color: 'grey', marginBottom: '1rem', marginTop: '-1rem' } }, __('Tapahtumat.hel.fi web address, on the basis of which the listing is formed. For example,  ', 'helsinki-linkedevents'), createElement('a', {href: 'https://tapahtumat.hel.fi/fi/haku?categories=music'}, 'https://tapahtumat.hel.fi/fi/haku?categories=music'), '.')
    );
  }

  function eventsCountControl(props) {
    return createElement(
      PanelRow, {},
      createElement(SelectControl, {
        label: __( 'Number of events', 'helsinki-linkedevents' ),
        value: props.attributes.eventsCount,
        onChange: function(count) {
          props.setAttributes({
            eventsCount: count,
          });
        },
        options: eventCountOptions(),
      })
    );
  }

  function titleTextControl(props) {
    return createElement(
      PanelRow, {},
      createElement(TextControl, {
        label: __( 'Title', 'helsinki-linkedevents' ),
				type: 'text',
				value: props.attributes.title,
				onChange: function(text) {
					props.setAttributes({
            title: text,
          });
				}
      })
    );
  }

  function hdsContentTextRich(props, config) {
    return wp.element.createElement(
      wp.blockEditor.RichText, {
        tagName: 'p',
        className: config.className ? config.className : 'content__text',
        value: config.textAttribute ? props.attributes[config.textAttribute] : props.attributes.contentText,
        onChange: function (value) {
          props.setAttributes(config.textAttribute ? {[config.textAttribute]: value} : {contentText: value});
        },
        placeholder: config.placeholder ? config.placeholder : wp.i18n.__( 'Excerpt', 'helsinki-linkedevents' ),
      },
    );
  }

  function hdsContentTitleRich(props, config) {
    return wp.element.createElement(
      wp.blockEditor.RichText, {
        tagName: 'h2',
        className: config.className ? config.className : 'content__heading',
        value: config.titleAttribute ? props.attributes[config.titleAttribute] : props.attributes.contentTitle,
        onChange: function (value) {
          props.setAttributes(config.titleAttribute ? {[config.titleAttribute]: value} : {contentTitle: value});
        },
        allowedFormats: [],
        placeholder: config.placeholder ? config.placeholder : wp.i18n.__( 'Title', 'helsinki-linkedevents' ),
      },
    );
  }
  

  /**
    * Elements
    */
  function preview(props) {
    if (props.isSelected) {
      return createElement(
        'div', useBlockProps(),
        createElement(
          'div', {className: 'helsinki-events events'},
          createElement(
            'div', {className: 'hds-container'},
            hdsContentTitleRich(props, {
              placeholder: __('This is the title', 'hds-wp'),
              titleAttribute: 'title',
              className: 'events__title',
            }),
            hdsContentTextRich(props, {
              placeholder: __('This is the excerpt.', 'hds-wp'),
              className: 'events__excerpt',
            }),
            createElement(ServerSideRender, {
              block: 'helsinki-linkedevents/grid',
              attributes: {...props.attributes, isEditRender: true},
            })  
          )
        )
      );
    }
    else {
      return createElement(
        'div', useBlockProps(),
        createElement(ServerSideRender, {
          block: 'helsinki-linkedevents/grid',
          attributes: {...props.attributes },
        })
      );
    }
  }

  /**
    * Edit
    */
  function edit() {
    return function(props) {
      props.attributes.eventsCount = parseInt(props.attributes.eventsCount);
      props.attributes.blockId = props.clientId;

      useEffect(function() {
        if (props.attributes.configID != 0) {
          wp.apiFetch({path: 'helsinki-linked-events/v1/config/' + props.attributes.configID}).then(function(data) {
            //form a url using the config data
            //base url is https://tapahtumat.hel.fi/fi/haku?
            //parse the config data array as key=value pairs and add them to the url

            var url = 'https://tapahtumat.hel.fi/fi/haku?';
            var config = data;
            var configKeys = Object.keys(config);
            for (var i = 0; i < configKeys.length; i++) {
              var key = configKeys[i];
              var value = config[key];
              url += key + '=' + value + '&';
            }
            props.setAttributes({
              configURL: url,
              configID: 0,
            });
          });
        }
      }, []);


      return createElement(
        Fragment, {},
        inspectorControls(props),
        preview(props)
      );
    }
  }

  function save() {
    return function(props) {
		return null;
	  };
	}

  /**
    * Register
    */
  registerBlockType('helsinki-linkedevents/grid', {
		apiVersion: 2,
		title: __( 'Helsinki - Events', 'helsinki-linkedevents' ),
		category: 'helsinki-linkedevents',
		icon: 'calendar-alt',
		keywords: [ __( 'events', 'helsinki-linkedevents' ), 'linked events', 'Helsinki - Events Grid'],
		supports: {
			html: false,
			anchor: true,
		},
		attributes: {
			configID: {
				type: 'string',
				default: 0,
			},
      configURL: {
        type: 'string',
        default: '',
      },
      eventsCount: {
        type: 'number',
        default: 3,
      },
			title: {
				type: 'string',
				default: '',
			},
      contentText: {
        type: 'string',
        default: '',
      },
			anchor: {
				type: 'string',
				default: '',
			},
      blockId: {
        type: 'string',
      },
      isEditRender: {
        type: 'boolean',
        default: false,
      },


		},
		edit: edit(),
	});

})(window.wp);
