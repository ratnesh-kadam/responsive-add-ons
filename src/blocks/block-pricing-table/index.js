/**
 * BLOCK: Responsive Blocks Pricing Table
 */

// Import block dependencies and components
import classnames from 'classnames';
import Inspector from './components/inspector';
import PricingTable from './components/pricing';
import memoize from 'memize';
import _times from 'lodash/times';

// Internationalization
const { __ } = wp.i18n;

// Extend component
const { Component } = wp.element;

// Register block
const { registerBlockType } = wp.blocks;

// Register editor components
const {
	RichText,
	AlignmentToolbar,
	BlockControls,
	BlockAlignmentToolbar,
	MediaUpload,
	InnerBlocks,
} = wp.editor;

// Register components
const {
	Button,
	SelectControl,
} = wp.components;

// Set allowed blocks and media
const ALLOWED_BLOCKS = [ 'responsive-blocks/ra-pricing-table' ];

// Get the pricing template
const getPricingTemplate = memoize( ( columns ) => {
	return _times( columns, () => [ 'responsive-blocks/ra-pricing-table' ] );
} );

class ABPricingBlock extends Component {

	render() {

		// Setup the attributes
		const {
			attributes: {
				columns,
				columnsGap,
				align,
			},
			attributes,
			isSelected,
			editable,
			className,
			setAttributes
		} = this.props;

		return [
			// Show the alignment toolbar on focus
			<BlockControls key="controls">
				<BlockAlignmentToolbar
					value={ align }
					onChange={ align => setAttributes( { align } ) }
					controls={ [ 'center', 'wide', 'full' ] }
				/>
			</BlockControls>,
			// Show the block controls on focus
			<Inspector
				{ ...{ setAttributes, ...this.props } }
			/>,
			// Show the block markup in the editor
			<PricingTable { ...this.props }>
				<div
					className={ classnames(
						'ra-pricing-table-wrap-admin',
						'ra-block-pricing-table-gap-' + columnsGap
					) }
				>
					<InnerBlocks
						template={ getPricingTemplate( columns ) }
						templateLock="all"
						allowedBlocks={ ALLOWED_BLOCKS }
					/>
				</div>
			</PricingTable>
		];
	}
}

// Register the block
registerBlockType( 'responsive-blocks/ra-pricing', {
	title: __( 'RA Pricing', 'responsive-blocks' ),
	description: __( 'Add a pricing table.', 'responsive-blocks' ),
	icon: 'cart',
	category: 'responsive-blocks',
	keywords: [
		__( 'pricing table', 'responsive-blocks' ),
		__( 'shop', 'responsive-blocks' ),
		__( 'purchase', 'responsive-blocks' ),
	],
	attributes: {
		columns: {
			type: 'number',
			default: 2,
		},
		columnsGap: {
			type: 'number',
			default: 2,
		},
		align: {
			type: 'string',
		},
	},

	// Add alignment to block wrapper
	getEditWrapperProps( { align } ) {
		if ( 'left' === align || 'right' === align || 'full' === align || 'wide' === align ) {
			return { 'data-align': align };
		}
	},

	// Render the block components
	edit: ABPricingBlock,

	// Save the attributes and markup
	save: function( props ) {

		// Setup the attributes
		const {
			columns,
			columnsGap,
			align,
		} = props.attributes;

		// Setup the classes
		const className = classnames( [
			'ra-pricing-table-wrap',
			'ra-block-pricing-table-gap-' + columnsGap
		])

		// Save the block markup for the front end
		return (
			<PricingTable { ...props }>
				<div
					className={ className ? className : undefined }
				>
					<InnerBlocks.Content />
				</div>
			</PricingTable>
		);
	},
} );
