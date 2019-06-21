/**
 * BLOCK: Responsive Blocks Accordion Block
 */

// Import block dependencies and components
import classnames from 'classnames';
import Inspector from './components/inspector';
import Accordion from './components/accordion';
import icons from './components/icons';
import omit from 'lodash/omit';

// Import CSS
import './styles/style.scss';
import './styles/editor.scss';

// Components
const { __ } = wp.i18n;

// Extend component
const { Component } = wp.element;

// Register block
const {
	registerBlockType,
	createBlock,
} = wp.blocks;

// Register editor components
const {
	RichText,
	AlignmentToolbar,
	BlockControls,
	BlockAlignmentToolbar,
	InnerBlocks,
} = wp.editor;

// Register components
const {
	Button,
	withFallbackStyles,
	IconButton,
	Dashicon,
} = wp.components;

const blockAttributes = {
	accordionTitle: {
		type: 'array',
		selector: '.ra-accordion-title',
		source: 'children',
	},
	accordionText: {
		type: 'array',
		selector: '.ra-accordion-text',
		source: 'children',
	},
	accordionAlignment: {
		type: 'string',
	},
	accordionFontSize: {
		type: 'number',
		default: 18
	},
	accordionOpen: {
		type: 'boolean',
		default: false
	},
};

class ABAccordionBlock extends Component {

	render() {

		// Setup the attributes
		const { attributes: { accordionTitle, accordionText, accordionAlignment, accordionFontSize, accordionOpen }, isSelected, className, setAttributes } = this.props;

		return [
			// Show the block alignment controls on focus
			<BlockControls key="controls">
				<AlignmentToolbar
					value={ accordionAlignment }
					onChange={ ( value ) => this.props.setAttributes( { accordionAlignment: value } ) }
				/>
			</BlockControls>,
			// Show the block controls on focus
			<Inspector
				{ ...this.props }
			/>,
			// Show the button markup in the editor
			<Accordion { ...this.props }>
				<RichText
					tagName="p"
					placeholder={ __( 'Accordion Title', 'responsive-blocks' ) }
					value={ accordionTitle }
					className="ra-accordion-title"
					onChange={ ( value ) => this.props.setAttributes( { accordionTitle: value } ) }
				/>

				<div className="ra-accordion-text">
					<InnerBlocks />
				</div>
			</Accordion>
		];
	}
}

// Register the block
registerBlockType( 'responsive-blocks/ra-accordion', {
	title: __( 'RA Accordion', 'responsive-blocks' ),
	description: __( 'Add accordion block with a title and text.', 'responsive-blocks' ),
	icon: 'editor-ul',
	category: 'responsive-blocks',
	keywords: [
		__( 'accordion', 'responsive-blocks' ),
		__( 'list', 'responsive-blocks' ),
		__( 'responsive', 'responsive-blocks' ),
	],
	attributes: blockAttributes,

	// Render the block components
	edit: ABAccordionBlock,

	// Save the attributes and markup
	save: function( props ) {

		// Setup the attributes
		const { accordionTitle, accordionText, accordionAlignment, accordionFontSize, accordionOpen } = props.attributes;

		// Save the block markup for the front end
		return (
			<Accordion { ...props }>
				<details open={accordionOpen}>
					<summary className="ra-accordion-title">
						<RichText.Content
							value={ accordionTitle }
						/>
					</summary>
					<div className="ra-accordion-text">
						<InnerBlocks.Content />
					</div>
				</details>
			</Accordion>
		);
	},

	deprecated: [ {
		attributes: {
			accordionText: {
				type: 'array',
				selector: '.ra-accordion-text',
				source: 'children',
			},
			...blockAttributes
		},

		migrate( attributes, innerBlocks  ) {
			return [
				omit( attributes, 'accordionText' ),
				[
					createBlock( 'core/paragraph', {
						content: attributes.accordionText,
					} ),
					...innerBlocks,
				],
			];
		},

		save( props ) {
			return (
				<Accordion { ...props }>
					<details open={ props.attributes.accordionOpen }>
						<summary className="ra-accordion-title">
							<RichText.Content
								value={ props.attributes.accordionTitle }
							/>
						</summary>
						<RichText.Content
							className="ra-accordion-text"
							tagName="p"
							value={ props.attributes.accordionText }
						/>
					</details>
				</Accordion>
			);
		},
	} ],
} );
