(function(wp){

  const __ = wp.i18n.__,
  	{ registerBlockType } = wp.blocks,
  	ServerSideRender = wp.serverSideRender,
  	{ useBlockProps } = wp.blockEditor,
  	{ Fragment, createElement } = wp.element,
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
        titleTextControl(props),
        configSelectControl(props)
      )
    );
  }

  function configSelectControl(props) {
    return createElement(
      PanelRow, {},
      createElement(EventsConfigSelect, props)
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

  /**
    * Elements
    */
  function preview(props) {
    return createElement(
      'div', useBlockProps(),
      createElement(ServerSideRender, {
        block: 'helsinki-linkedevents/grid',
        attributes: props.attributes,
      })
    );
  }

  /**
    * Edit
    */
  function edit() {
    return function(props) {
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
			title: {
				type: 'string',
				default: '',
			},
			anchor: {
				type: 'string',
				default: '',
			},

		},
		edit: edit(),
	});

})(window.wp);
