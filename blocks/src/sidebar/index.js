( function ( wp ) {
	const { registerPlugin } = wp.plugins;
	const { PluginDocumentSettingPanel } = wp.editPost;
	const { Component } = wp.element;
	const { SelectControl, Spinner } = wp.components;

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
		return (
			<SelectControl
				type="text"
				label={ props.label }
				value={ props.metaValue }
				onChange={ ( content ) => {
					props.setMetaValue( content );
				} }
				options={ [
					{ label: '-- Not Restricted --', value: '' },
				].concat(
					props.products.map( ( product ) => {
						return { label: product.name, value: product.id };
					} )
				) }
			/>
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
					title="Restrict With Stripe"
				>
					{ this.state.loadingProducts ? (
						<Spinner />
					) : (
						<RestrictionSelectControl
							label="Stripe Product"
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
