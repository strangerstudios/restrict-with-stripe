( function( wp ) {

	const { registerPlugin } = wp.plugins;
	const { PluginDocumentSettingPanel } = wp.editPost;

	const { 
		SelectControl,
	} = wp.components;

	const { withSelect, withDispatch } = wp.data;
	const { compose } = wp.compose;

	const RestrictionSelectControl = compose(
		withDispatch( function( dispatch, props ) {
			return {
				setMetaValue: function( value ) {
					dispatch( 'core/editor' ).editPost( { meta: { [props.metaKey]: value } } );
				}
			}
		} ),
		withSelect( function( select, props ) {
			return {
				metaValue: select( 'core/editor' ).getEditedPostAttribute( 'meta' )[ props.metaKey ],
			}
		} )
	)( function( props ) {
		return (
			<SelectControl
				type="text"
				label={ props.label }
				value={ props.metaValue }
				onChange={ ( content ) => { props.setMetaValue( content ) } }
				options={[
					{
					  label: '-- Not Restricted --',
					  value: ''
					},
					{
					  label: 'Product A',
					  value: 'a'
					},
					{
					  label: 'Product B',
					  value: 'b'
					},
					{
					  label: 'Product C',
					  value: 'c'
					}
				  ]}
			/>
		);
	});

	registerPlugin( 'rwstripe-sidebar', {
		render: function(){

			return (
				<PluginDocumentSettingPanel
					name="rwstripe-sidebar-panel"
					title="Restrict With Stripe"
				>
					<RestrictionSelectControl label="Stripe Product" metaKey="rwstripe_test_restricted_product" />

				</PluginDocumentSettingPanel>
			)
			
		},
		icon: 'lock'
	} );


} )( window.wp );