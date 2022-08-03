( function ( wp ) {
	const { __ } = wp.i18n;
	const { registerPlugin } = wp.plugins;
	const { PluginDocumentSettingPanel } = wp.editPost;
	const { Component } = wp.element;
	const { Spinner, CheckboxControl } = wp.components;

	const { withSelect, withDispatch } = wp.data;
	const { compose } = wp.compose;

	const RestrictionSelectControl = compose(
		withDispatch( function ( dispatch, props ) {
			return {
				setMetaValue: function ( value ) {
					dispatch( 'core/editor' ).editPost( {
						meta: { [ props.metaKey ]: value },
					} );
				},
			};
		} ),
		withSelect( function ( select, props ) {
			return {
				metaValue:
					select( 'core/editor' ).getEditedPostAttribute( 'meta' )[
						props.metaKey
					],
			};
		} )
	)( function ( props ) {
		const product_checkboxes = props.products.map(
			( product ) => {
				return (
					<CheckboxControl
						key={ product.id }
						label={ product.name }
						checked={ props.metaValue.includes( product.id ) }
						onChange={ () => {
							let newValue = [...props.metaValue];
							if ( newValue.includes( product.id ) ) {
								newValue = newValue.filter(
									( item ) => item !== product.id
								);
							} else {
								newValue.push( product.id )
							}
							props.setMetaValue( newValue );
						} }
					/>
				)
			}
		);
		return (
			<fragment>
				{product_checkboxes}
			</fragment>
		);
	} );

	class RWStripeSidebar extends Component {
		constructor( props ) {
			super( props );
			this.state = {
				productList: [],
				loadingProducts: true,
			};
		}

		componentDidMount() {
			this.fetchProducts();
		}

		fetchProducts() {
			wp.apiFetch( {
				path: 'rwstripe/v1/products',
			} ).then( ( data ) => {
				this.setState( {
					productList: data,
					loadingProducts: false,
				} );
			} );
		}

		render() {
			return (
				<PluginDocumentSettingPanel
					name="rwstripe-sidebar-panel"
					title={ __( 'Restrict With Stripe', 'restrict-with-stripe' ) }
				>
					{ this.state.loadingProducts ? (
						<Spinner />
					) : (
						<RestrictionSelectControl
							label={ __( 'Stripe Product', 'restrict-with-stripe' ) }
							metaKey="rwstripe_stripe_product_ids"
							products={ this.state.productList }
						/>
					) }
				</PluginDocumentSettingPanel>
			);
		}
	}

	registerPlugin( 'rwstripe-sidebar', {
		icon: 'lock',
		render: RWStripeSidebar,
	} );
} )( window.wp );
