/**
 * BLOCK: Responsive Blocks Testimonial
 */

// Import block dependencies and components
import classnames from 'classnames';
import Inspector from './components/inspector';
import Testimonial from './components/testimonial';
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
	BlockAlignmentToolbar,
	MediaUpload,
} = wp.editor;

// Register components
const {
	Button,
	SelectControl,
} = wp.components;

const ALLOWED_MEDIA_TYPES = [ 'image' ];

class ABTestimonialBlock extends Component {

	render() {

		// Setup the attributes
		const {
			attributes: {
				testimonialName,
				testimonialTitle,
				testimonialContent,
				testimonialAlignment,
				testimonialImgURL,
				testimonialImgID,
				testimonialBackgroundColor,
				testimonialTextColor,
				testimonialFontSize,
				testimonialCiteAlign
			},
			attributes,
			isSelected,
			editable,
			className,
			setAttributes
		} = this.props;

		const onSelectImage = img => {
			setAttributes( {
				testimonialImgID: img.id,
				testimonialImgURL: img.url,
			} );
		};

		return [
			// Show the alignment toolbar on focus
			<BlockControls key="controls">
				<AlignmentToolbar
					value={ testimonialAlignment }
					onChange={ ( value ) => setAttributes( { testimonialAlignment: value } ) }
				/>
			</BlockControls>,
			// Show the block controls on focus
			<Inspector
				{ ...{ setAttributes, ...this.props } }
			/>,
			// Show the block markup in the editor
			<Testimonial { ...this.props }>
				<RichText
					tagName="div"
					multiline="p"
					placeholder={ __( 'Add testimonial text...', 'responsive-blocks' ) }
					keepPlaceholderOnFocus
					value={ testimonialContent }
					formattingControls={ [ 'bold', 'italic', 'strikethrough', 'link' ] }
					className={ classnames(
						'ra-testimonial-text'
					) }
					style={ {
						textAlign: testimonialAlignment,
					} }
					onChange={ ( value ) => setAttributes( { testimonialContent: value } ) }
				/>

				<div className="ra-testimonial-info">
					<div className="ra-testimonial-avatar-wrap">
						<div className="ra-testimonial-image-wrap">
							<MediaUpload
								buttonProps={ {
									className: 'change-image'
								} }
								onSelect={ ( img ) => setAttributes(
									{
										testimonialImgID: img.id,
										testimonialImgURL: img.url,
									}
								) }
								allowed={ ALLOWED_MEDIA_TYPES }
								type="image"
								value={ testimonialImgID }
								render={ ( { open } ) => (
									<Button onClick={ open }>
										{ ! testimonialImgID ? icons.upload : <img
											className="ra-testimonial-avatar"
											src={ testimonialImgURL }
											alt="avatar"
										/>  }
									</Button>
								) }
							>
							</MediaUpload>
						</div>
					</div>

					<RichText
						tagName="h2"
						placeholder={ __( 'Add name', 'responsive-blocks' ) }
						keepPlaceholderOnFocus
						value={ testimonialName }
						className='ra-testimonial-name'
						style={ {
							color: testimonialTextColor
						} }
						onChange={ ( value ) => this.props.setAttributes( { testimonialName: value } ) }
					/>

					<RichText
						tagName="small"
						placeholder={ __( 'Add title', 'responsive-blocks' ) }
						keepPlaceholderOnFocus
						value={ testimonialTitle }
						className='ra-testimonial-title'
						style={ {
							color: testimonialTextColor
						} }
						onChange={ ( value ) => this.props.setAttributes( { testimonialTitle: value } ) }
					/>
				</div>
			</Testimonial>
		];
	}
}

// Register the block
registerBlockType( 'responsive-blocks/ra-testimonial', {
	title: __( 'AB Testimonial', 'responsive-blocks' ),
	description: __( 'Add a user testimonial with a name and title.', 'responsive-blocks' ),
	icon: 'format-quote',
	category: 'responsive-blocks',
	keywords: [
		__( 'testimonial', 'responsive-blocks' ),
		__( 'quote', 'responsive-blocks' ),
		__( 'responsive', 'responsive-blocks' ),
	],
	attributes: {
		testimonialName: {
			type: 'array',
			selector: '.ra-testimonial-name',
			source: 'children',
		},
		testimonialTitle: {
			type: 'array',
			selector: '.ra-testimonial-title',
			source: 'children',
		},
		testimonialContent: {
			type: 'array',
			selector: '.ra-testimonial-text',
			source: 'children',
		},
		testimonialAlignment: {
			type: 'string',
		},
		testimonialImgURL: {
			type: 'string',
			source: 'attribute',
			attribute: 'src',
			selector: 'img',
		},
		testimonialImgID: {
			type: 'number',
		},
		testimonialBackgroundColor: {
			type: 'string',
			default: '#f2f2f2'
		},
		testimonialTextColor: {
			type: 'string',
			default: '#32373c'
		},
		testimonialFontSize: {
			type: 'number',
			default: 18,
		},
		testimonialCiteAlign: {
            type: 'string',
            default: 'left-aligned',
        },
	},

	// Render the block components
	edit: ABTestimonialBlock,

	// Save the attributes and markup
	save: function( props ) {

		// Setup the attributes
		const {
			testimonialName,
			testimonialTitle,
			testimonialContent,
			testimonialAlignment,
			testimonialImgURL,
			testimonialImgID,
			testimonialBackgroundColor,
			testimonialTextColor,
			testimonialFontSize,
			testimonialCiteAlign
		} = props.attributes;

		// Save the block markup for the front end
		return (
			<Testimonial { ...props }>
				<RichText.Content
					tagName="div"
					className="ra-testimonial-text"
					style={ {
						textAlign: testimonialAlignment,
					} }
					value={ testimonialContent }
				/>

				<div className="ra-testimonial-info">
					{ testimonialImgURL && (
						<div className="ra-testimonial-avatar-wrap">
							<div className="ra-testimonial-image-wrap">
								<img
									className="ra-testimonial-avatar"
									src={ testimonialImgURL }
									alt="avatar"
								/>
							</div>
						</div>
					) }

					{ testimonialName && (
						<RichText.Content
							tagName="h2"
							className="ra-testimonial-name"
							style={ {
								color: testimonialTextColor
							} }
							value={ testimonialName }
						/>
					) }

					{ testimonialTitle && (
						<RichText.Content
							tagName="small"
							className="ra-testimonial-title"
							style={ {
								color: testimonialTextColor
							} }
							value={ testimonialTitle }
						/>
					) }
				</div>
			</Testimonial>
		);
	},
} );
