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
						label={ product.name + ( product.default_price ? '' : ' (' + __('no default price set', 'restrict-with-stripe') + ')' ) }
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
				<h4>{ __('Select products to restrict by:', 'restrict-with-stripe') }</h4>
				{
					product_checkboxes.length > 6 ? (
						<div className="rwstripe-scrollable-div">
							{ product_checkboxes }
						</div>
					) : (
						product_checkboxes
					)
				}
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
			} ).catch( ( error ) => {
				this.setState( {
					productList: error.message,
					loadingProducts: false,
				} );
			} );
		}

		render() {
			var sidebar_content = <Spinner />;
			if ( ! this.state.loadingProducts ) {
				if ( ! Array.isArray( this.state.productList ) || 'undefined' === typeof rwstripeSidebar.restricted_product_ids_meta_key ) {
					sidebar_content = <p>{ __('Could not connect to Stripe. Please check your Stripe connection on the Restrict with Stripe settings page.', 'restrict-with-stripe') }</p>;
				} else if ( this.state.productList.length === 0 ) {
					sidebar_content = <div>
						<p>{ __('No products found. Please create a product in Stripe.', 'restrict-with-stripe') }</p>
						<a href={rwstripeSidebar.stripe_products_url} target="_blank">{ __('Manage Products', 'restrict-with-stripe') }</a>
					</div>;
				} else {
					sidebar_content = <div>
						<RestrictionSelectControl
							label={ __( 'Stripe Product', 'restrict-with-stripe' ) }
							metaKey={ rwstripeSidebar.restricted_product_ids_meta_key }
							products={ this.state.productList }
						/>
						<a href={rwstripeSidebar.stripe_products_url} target="_blank">{ __('Manage Products', 'restrict-with-stripe') }</a>
					</div>;
				}
			}

			return (
				<PluginDocumentSettingPanel name="rwstripe-sidebar-panel" title={ __( 'Restrict with Stripe', 'restrict-with-stripe' ) } >
					{sidebar_content}
				</PluginDocumentSettingPanel>
			);
		}
	}

	registerPlugin( 'rwstripe-sidebar', {
		icon: 'lock',
		render: RWStripeSidebar,
	} );
} )( window.wp );
