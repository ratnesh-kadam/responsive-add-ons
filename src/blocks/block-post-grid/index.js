/**
 * BLOCK: Responsive Blocks Post and Page Grid
 */

// Import block dependencies and components
import edit from './components/edit';

// Import CSS
import './styles/style.scss';
import './styles/editor.scss';

// Components
const { __ } = wp.i18n;

// Register block controls
const {
	registerBlockType,
} = wp.blocks;

// Register alignments
const validAlignments = [ 'center', 'wide', 'full' ];

// Register the block
registerBlockType( 'responsive-blocks/ra-post-grid', {
	title: __( 'RA Post and Page Grid', 'responsive-blocks' ),
	description: __( 'Add a grid or list of customizable posts or pages.', 'responsive-blocks' ),
	icon: 'grid-view',
	category: 'responsive-blocks',
	keywords: [
		__( 'post', 'responsive-blocks' ),
		__( 'page', 'responsive-blocks' ),
		__( 'grid', 'responsive-blocks' ),
	],

	getEditWrapperProps( attributes ) {
		const { align } = attributes;
		if ( -1 !== validAlignments.indexOf( align ) ) {
			return { 'data-align': align };
		}
	},

	edit,

	// Render via PHP
	save() {
		return null;
	},
} );
