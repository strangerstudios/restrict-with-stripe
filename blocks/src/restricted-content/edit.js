/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-block-editor/#useBlockProps
 */
import { useBlockProps } from '@wordpress/block-editor';

const { Component } = wp.element;

const { __ } = wp.i18n;

const { InspectorControls, InnerBlocks } = wp.blockEditor;

const { PanelBody, CheckboxControl, Spinner } = wp.components;

class RWStripeRestrictionSelect extends Component {
	constructor( props ) {
		super( props );
		this.state = {
			productList: [],
			loadingProducts: true,
		};
		this.props = props;
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
		var product_checkboxes = <Spinner />;
		if ( ! this.state.loadingProducts ) {
			if ( ! Array.isArray( this.state.productList ) ) {
				product_checkboxes = <p>{ __('Could not connect to Stripe. Please check your Stripe connection on the Restrict With Stripe settings page.', 'restrict-with-stripe') }</p>;
			} else if ( this.state.productList.length === 0 ) {
				product_checkboxes = <p>{ __('No products found. Please create a product in Stripe.', 'restrict-with-stripe') }</p>;
			} else {
				product_checkboxes = this.state.productList.map(
					( product ) => {
						return (
							<CheckboxControl
								key={ product.id }
								label={ product.name }
								checked={ this.props.rwstripe_restricted_products.includes( product.id ) }
								onChange={ () => {
									let newValue = [...this.props.rwstripe_restricted_products];
									if ( newValue.includes( product.id ) ) {
										newValue = newValue.filter(
											( item ) => item !== product.id
										);
									} else {
										newValue.push( product.id )
									}
									this.props.setAttributes( {
										rwstripe_restricted_products: newValue,
									} )
								} }
							/>
						)
					}
				);
			}
		}
		return (
			<div>
				{ product_checkboxes }
			</div>
		);
	}
}

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @param {Object}   props               Properties passed to the function.
 * @param {Object}   props.attributes    Available block attributes.
 * @param {Function} props.setAttributes Function that updates individual attributes.
 *
 * @return {WPElement} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();

	// Check if blockProps.class contains the class 'is-selected'
	const isSelected = blockProps.className.includes( 'is-selected' );

	return [
		isSelected && (
			<InspectorControls>
				<PanelBody>
					<h4>{ __('Select products to restrict by:', 'restrict-with-stripe') }</h4>
					<RWStripeRestrictionSelect
						rwstripe_restricted_products={
							attributes.rwstripe_restricted_products
						}
						setAttributes={ setAttributes }
					/>
					<hr/>
					<h4>{ __('Show purchase link:', 'restrict-with-stripe') }</h4>
					<CheckboxControl
						label={ __('Allow users without access to purhcase this content', 'restrict-with-stripe') }
						checked={ attributes.rwstripe_show_checkout_form }
						onChange={ ( value ) => {
							setAttributes( {
								rwstripe_show_checkout_form: value,
							} );
						}
						}
					/>
				</PanelBody>
			</InspectorControls>
		),
		isSelected && (
			<div { ...blockProps }>
				<span className="rwstripe-block-title">{ __( 'Restricted Content', 'restrict-with-stripe' ) }</span>
				<PanelBody>
					<label>{ __('Select products to restrict by:', 'restrict-with-stripe') }</label>
					<RWStripeRestrictionSelect
						rwstripe_restricted_products={
							attributes.rwstripe_restricted_products
						}
						setAttributes={ setAttributes }
					/>
				</PanelBody>
				<InnerBlocks
					renderAppender={ () => <InnerBlocks.ButtonBlockAppender /> }
					templateLock={ false }
				/>
			</div>
		),
		! isSelected && (
			<div { ...blockProps }>
				<span className="rwstripe-block-title">{ __( 'Restricted Content', 'restrict-with-stripe' ) }</span>
				<InnerBlocks
					renderAppender={ () => <InnerBlocks.ButtonBlockAppender /> }
					templateLock={ false }
				/>
			</div>
		),
	];
}
