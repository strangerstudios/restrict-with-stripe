/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-block-editor/#useBlockProps
 */
import { useBlockProps } from '@wordpress/block-editor';

const { Component } = wp.element;

const { InspectorControls, InnerBlocks } = wp.blockEditor;

const { PanelBody, SelectControl, Spinner } = wp.components;

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
		} );
	}

	render() {
		return (
			<div>
				{ this.state.loadingProducts ? (
					<Spinner />
				) : (
					<SelectControl
						type="text"
						label="Stripe Product"
						value={ this.props.rwstripe_restricted_products }
						onChange={ ( val ) =>
							this.props.setAttributes( {
								rwstripe_restricted_products: [ val ],
							} )
						}
						options={ [
							{ label: '-- Not Restricted --', value: '' },
						].concat(
							this.state.productList.map( ( product ) => {
								return {
									label: product.name,
									value: product.id,
								};
							} )
						) }
					/>
				) }
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
					<RWStripeRestrictionSelect
						rwstripe_restricted_products={
							attributes.rwstripe_restricted_products
						}
						setAttributes={ setAttributes }
					/>
				</PanelBody>
			</InspectorControls>
		),
		isSelected && (
			<div { ...blockProps }>
				<span className="rwstripe-block-title">Restricted Content</span>
				<PanelBody>
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
				<span className="rwstripe-block-title">Restricted Content</span>
				<InnerBlocks
					renderAppender={ () => <InnerBlocks.ButtonBlockAppender /> }
					templateLock={ false }
				/>
			</div>
		),
	];
}
