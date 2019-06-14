/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

/**
 * Internal dependencies
 */
import Edit from './components/edit';
import './styles/style.scss';
import './styles/editor.scss';

registerBlockType(
	'responsive-blocks/newsletter',
	{
		title: __( 'Email newsletter', 'responsive-blocks' ),
		description: __( 'Add an email newsletter sign-up form.', 'responsive-blocks' ),
		category: 'responsive-blocks',
		icon: 'email-alt',
		keywords: [
			__( 'Mailchimp', 'responsive-blocks' ),
			__( 'Subscribe', 'responsive-blocks' ),
			__( 'Newsletter', 'responsive-blocks' ),
		],
		edit: Edit,
		save: () => {
			return null;
		}
	},
);
