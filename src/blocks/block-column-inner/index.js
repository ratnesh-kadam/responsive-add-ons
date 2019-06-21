/**
 * BLOCK: Responsive Blocks Advanced Columns InnerBlocks.
 */

/**
 * Internal dependencies.
 */
import Edit from './components/edit';
import Save from './components/save';
import deprecated from './deprecated/deprecated';
import './styles/style.scss';
import './styles/editor.scss';

/**
 * WordPress dependencies.
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

/**
 * Register advanced columns block.
 */
registerBlockType( 'responsive-blocks/ra-column', {
	title: __( 'RA Advanced Column', 'responsive-blocks' ),
	description: __( 'Add a pre-defined column layout.', 'responsive-blocks' ),
	icon: 'editor-table',
	category: 'responsive-blocks',
	parent: [ 'responsive-blocks/ra-columns' ],
	keywords: [
		__( 'column', 'responsive-blocks' ),
		__( 'layout', 'responsive-blocks' ),
		__( 'row', 'responsive-blocks' ),
	],
	attributes: {
		backgroundColor: {
			type: 'string',
		},
		customBackgroundColor: {
			type: 'string',
		},
		textColor: {
			type: 'string',
		},
		customTextColor: {
			type: 'string',
		},
		textAlign: {
			type: 'string',
		},
		marginSync: {
			type: 'boolean',
			default: false,
		},
		marginUnit: {
			type: 'string',
			default: 'px',
		},
		margin: {
			type: 'number',
			default: 0,
		},
		marginTop: {
			type: 'number',
			default: 0,
		},
		marginBottom: {
			type: 'number',
			default: 0,
		},
		paddingSync: {
			type: 'boolean',
			default: false,
		},
		paddingUnit: {
			type: 'string',
			default: 'px',
		},
		padding: {
			type: 'number',
			default: 0,
		},
		paddingTop: {
			type: 'number',
			default: 0,
		},
		paddingRight: {
			type: 'number',
			default: 0,
		},
		paddingBottom: {
			type: 'number',
			default: 0,
		},
		paddingLeft: {
			type: 'number',
			default: 0,
		},
		columnVerticalAlignment: {
			type: 'string',
		},
	},

	/* Render the block in the editor. */
	edit: props => {
		return <Edit { ...props } />;
	},

	/* Save the block markup. */
	save: props => {
		return <Save { ...props } />;
	},

	deprecated: deprecated,
} );

/* Add the vertical column alignment class to the block wrapper. */
const withClientIdClassName = wp.compose.createHigherOrderComponent( ( BlockListBlock ) => {
    return ( props ) => {
		const blockName = props.block.name;

		if( props.attributes.columnVerticalAlignment && blockName === 'responsive-blocks/ra-column' ) {
            return <BlockListBlock { ...props } className={ "ra-is-vertically-aligned-" + props.attributes.columnVerticalAlignment } />;
        } else {
            return <BlockListBlock { ...props } />
        }
	};
}, 'withClientIdClassName' );

wp.hooks.addFilter(
	'editor.BlockListBlock',
	'responsive-blocks/add-vertical-align-class',
	withClientIdClassName
);
