/**
 * BLOCK: Responsive Blocks Notice
 */

// Import block dependencies and components
import classnames from 'classnames';
import Inspector from './components/inspector';
import NoticeBox from './components/notice';
import DismissButton from './components/button';
import icons from './components/icons';

// Import CSS
import './styles/style.scss';
import './styles/editor.scss';

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
} = wp.editor;

class ABNoticeBlock extends Component {

	render() {

		// Setup the attributes
		const {
			attributes: {
				noticeTitle,
				noticeContent,
				noticeAlignment,
				noticeBackgroundColor,
				noticeTitleColor,
				noticeDismiss
			},
			setAttributes
		} = this.props;

		return [
			// Show the alignment toolbar on focus
			<BlockControls key="controls">
				<AlignmentToolbar
					value={ noticeAlignment }
					onChange={ ( value ) => setAttributes( { noticeAlignment: value } ) }
				/>
			</BlockControls>,
			// Show the block controls on focus
			<Inspector
				{ ...{ setAttributes, ...this.props } }
			/>,
			// Show the block markup in the editor
			<NoticeBox { ...this.props }>
				{	// Check if the notice is dismissible and output the button
					( noticeDismiss && noticeDismiss === 'ra-dismissable' ) && (
					<DismissButton { ...this.props }>
						{ icons.dismiss }
					</DismissButton>
				) }

				<RichText
					tagName="p"
					placeholder={ __( 'Notice Title', 'responsive-blocks' ) }
					keepPlaceholderOnFocus
					value={ noticeTitle }
					className={ classnames(
						'ra-notice-title'
					) }
					style={ {
						color: noticeTitleColor,
					} }
					onChange={ ( value ) => setAttributes( { noticeTitle: value } ) }
				/>

				<RichText
					tagName="div"
					multiline="p"
					placeholder={ __( 'Add notice text...', 'responsive-blocks' ) }
					value={ noticeContent }
					className={ classnames(
						'ra-notice-text'
					) }
					style={ {
						borderColor: noticeBackgroundColor,
					} }
					onChange={ ( value ) => setAttributes( { noticeContent: value } ) }
				/>
			</NoticeBox>
		];
	}
}

// Register the block
registerBlockType( 'responsive-blocks/ra-notice', {
	title: __( 'AB Notice', 'responsive-blocks' ),
	description: __( 'Add a stylized text notice.', 'responsive-blocks' ),
	icon: 'format-aside',
	category: 'responsive-blocks',
	keywords: [
		__( 'notice', 'responsive-blocks' ),
		__( 'message', 'responsive-blocks' ),
		__( 'responsive', 'responsive-blocks' ),
	],
	attributes: {
		noticeTitle: {
			type: 'string',
			selector: '.ra-notice-title',
		},
		noticeContent: {
			type: 'array',
			selector: '.ra-notice-text',
			source: 'children',
		},
		noticeAlignment: {
			type: 'string',
		},
		noticeBackgroundColor: {
			type: 'string',
			default: '#00d1b2'
		},
		noticeTextColor: {
			type: 'string',
			default: '#32373c'
		},
		noticeTitleColor: {
			type: 'string',
			default: '#fff'
		},
		noticeFontSize: {
			type: 'number',
			default: 18
		},
		noticeDismiss: {
            type: 'string',
            default: '',
        },
	},

	// Render the block components
	edit: ABNoticeBlock,

	// Save the attributes and markup
	save: function( props ) {

		// Setup the attributes
		const {
			noticeTitle,
			noticeContent,
			noticeBackgroundColor,
			noticeTitleColor,
			noticeDismiss
		} = props.attributes;

		// Save the block markup for the front end
		return (
			<NoticeBox { ...props }>
				{ ( noticeDismiss && noticeDismiss === 'ra-dismissable' ) && (
					<DismissButton { ...props }>
						{ icons.dismiss }
					</DismissButton>
				) }

				{ noticeTitle && (
					<div
						className="ra-notice-title"
						style={ {
							color: noticeTitleColor
						} }
					>
						<RichText.Content
							tagName="p"
							value={ noticeTitle }
						/>
					</div>
				) }

				{ noticeContent && (
					<RichText.Content
						tagName="div"
						className="ra-notice-text"
						style={ {
							borderColor: noticeBackgroundColor
						} }
						value={ noticeContent }
					/>
				) }
			</NoticeBox>
		);
	},
} );
