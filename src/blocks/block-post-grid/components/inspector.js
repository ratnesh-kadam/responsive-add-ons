/**
 * Inspector Controls
 */

// Setup the block
const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;

import compact from 'lodash/compact';
import map from 'lodash/map';

// Import block components
const {
  InspectorControls,
} = wp.editor;

// Import Inspector components
const {
	PanelBody,
	QueryControls,
	RangeControl,
	SelectControl,
	TextControl,
	ToggleControl,
} = wp.components;

const { addQueryArgs } = wp.url;

const { apiFetch } = wp;

const MAX_POSTS_COLUMNS = 4;

/**
 * Create an Inspector Controls wrapper Component
 */
export default class Inspector extends Component {

	constructor() {
		super( ...arguments );
		this.state = { categoriesList: [] }
	}

	componentDidMount() {
		this.stillMounted = true;
		this.fetchRequest = apiFetch( {
			path: addQueryArgs( '/wp/v2/categories', { per_page: -1 } )
		} ).then(
			( categoriesList ) => {
				if ( this.stillMounted ) {
					this.setState( { categoriesList } );
				}
			}
		).catch(
			() => {
				if ( this.stillMounted ) {
					this.setState( { categoriesList: [] } );
				}
			}
		);
	}

	componentWillUnmount() {
		this.stillMounted = false;
	}

	/* Get the available image sizes */
	imageSizeSelect() {
		const getSettings = wp.data.select( 'core/editor' ).getEditorSettings();

		return compact( map( getSettings.imageSizes, ( { name, slug } ) => {
			return {
				value: slug,
				label: name,
			};
		} ) );
	}

	render() {

		// Setup the attributes
		const {
			attributes,
			setAttributes,
			latestPosts
		} = this.props;

		const {
			order,
			orderBy,
		} = attributes;

		const { categoriesList } = this.state;

		// Thumbnail options
		const imageCropOptions = [
			{ value: 'landscape', label: __( 'Landscape', 'responsive-blocks' ) },
			{ value: 'square', label: __( 'Square', 'responsive-blocks' ) },
		];

		// Post type options
		const postTypeOptions = [
			{ value: 'post', label: __( 'Post', 'responsive-blocks' ) },
			{ value: 'page', label: __( 'Page', 'responsive-blocks' ) },
		];

		// Section title tags
		const sectionTags = [
			{ value: 'div', label: __( 'div', 'responsive-blocks' ) },
			{ value: 'header', label: __( 'header', 'responsive-blocks' ) },
			{ value: 'section', label: __( 'section', 'responsive-blocks' ) },
			{ value: 'article', label: __( 'article', 'responsive-blocks' ) },
			{ value: 'main', label: __( 'main', 'responsive-blocks' ) },
			{ value: 'aside', label: __( 'aside', 'responsive-blocks' ) },
			{ value: 'footer', label: __( 'footer', 'responsive-blocks' ) },
		];

		// Section title tags
		const sectionTitleTags = [
			{ value: 'h2', label: __( 'H2', 'responsive-blocks' ) },
			{ value: 'h3', label: __( 'H3', 'responsive-blocks' ) },
			{ value: 'h4', label: __( 'H4', 'responsive-blocks' ) },
			{ value: 'h5', label: __( 'H5', 'responsive-blocks' ) },
			{ value: 'h6', label: __( 'H6', 'responsive-blocks' ) },
		];

		// Check for posts
		const hasPosts = Array.isArray( latestPosts ) && latestPosts.length;

		// Check the post type
		const isPost = attributes.postType === 'post';

		// Add instruction text to the select
		const abImageSizeSelect = {
			value: 'selectimage',
			label: __( 'Select image size' ),
		};

		// Add the landscape image size to the select
		const abImageSizeLandscape = {
			value: 'ra-post-grid-image-landscape',
			label: __( 'AB Grid Landscape' ),
		};

		// Add the square image size to the select
		const abImageSizeSquare = {
			value: 'ra-post-grid-image-square',
			label: __( 'AB Grid Square' ),
		};

		// Get the image size options
		const imageSizeOptions = this.imageSizeSelect();

		// Combine the objects
		imageSizeOptions.push( abImageSizeSquare, abImageSizeLandscape );
		imageSizeOptions.unshift( abImageSizeSelect );

		const imageSizeValue = () => {
			for ( var i = 0; i < imageSizeOptions.length; i++ ) {
				if ( imageSizeOptions[i].value === attributes.imageSize ) {
					return attributes.imageSize;
				}
			}
			return 'full';
		};

		return (
			<InspectorControls>
				<PanelBody
					title={ __( 'Post and Page Grid Settings', 'responsive-blocks' ) }
					className={ isPost ? null : 'responsive-blocks-hide-query' }
				>
					<SelectControl
						label={ __( 'Content Type', 'responsive-blocks' ) }
						options={ postTypeOptions }
						value={ attributes.postType }
						onChange={ ( value ) => this.props.setAttributes( { postType: value } ) }
					/>
					<QueryControls
						{ ...{ order, orderBy } }
						numberOfItems={ attributes.postsToShow }
						categoriesList={ categoriesList }
						selectedCategoryId={ attributes.categories }
						onOrderChange={ ( value ) => setAttributes( { order: value } ) }
						onOrderByChange={ ( value ) => setAttributes( { orderBy: value } ) }
						onCategoryChange={ ( value ) => setAttributes( { categories: '' !== value ? value : undefined } ) }
						onNumberOfItemsChange={ ( value ) => setAttributes( { postsToShow: value } ) }
					/>
					<RangeControl
						label={ __( 'Number of items to offset', 'responsive-blocks' ) }
						value={ attributes.offset }
						onChange={ ( value ) => setAttributes( { offset: value } ) }
						min={ 0 }
						max={ 20 }
					/>
					{ attributes.postLayout === 'grid' &&
						<RangeControl
							label={ __( 'Columns', 'responsive-blocks' ) }
							value={ attributes.columns }
							onChange={ ( value ) => setAttributes( { columns: value } ) }
							min={ 2 }
							max={ ! hasPosts ? MAX_POSTS_COLUMNS : Math.min( MAX_POSTS_COLUMNS, latestPosts.length ) }
						/>
					}
				</PanelBody>
				<PanelBody
					title={ __( 'Post and Page Grid Content', 'responsive-blocks' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Display Section Title', 'responsive-blocks' ) }
						checked={ attributes.displaySectionTitle }
						onChange={ () => this.props.setAttributes( { displaySectionTitle: ! attributes.displaySectionTitle } ) }
					/>
					{ attributes.displaySectionTitle &&
						<TextControl
							label={ __( 'Section Title', 'responsive-blocks' ) }
							type="text"
							value={ attributes.sectionTitle }
							onChange={ ( value ) => this.props.setAttributes( { sectionTitle: value } ) }
						/>
					}
					<ToggleControl
						label={ __( 'Display Featured Image', 'responsive-blocks' ) }
						checked={ attributes.displayPostImage }
						onChange={ () => this.props.setAttributes( { displayPostImage: ! attributes.displayPostImage } ) }
					/>
					{ attributes.displayPostImage &&
						<SelectControl
							label={ __( 'Image Size', 'responsive-blocks' ) }
							value={ imageSizeValue() }
							options={ imageSizeOptions }
							onChange={ ( value ) => this.props.setAttributes( { imageSize: value } ) }
						/>
					}
					{ attributes.displayPostImage &&
						<Fragment>
							<SelectControl
								label={ __( 'Featured Image Style', 'responsive-blocks' ) }
								options={ imageCropOptions }
								value={ attributes.imageCrop }
								onChange={ ( value ) => this.props.setAttributes( { imageCrop: value } ) }
							/>
						</Fragment>
					}
					<ToggleControl
						label={ __( 'Display Title', 'responsive-blocks' ) }
						checked={ attributes.displayPostTitle }
						onChange={ () => this.props.setAttributes( { displayPostTitle: ! attributes.displayPostTitle } ) }
					/>
					{ isPost &&
						<ToggleControl
							label={ __( 'Display Author', 'responsive-blocks' ) }
							checked={ attributes.displayPostAuthor }
							onChange={ () => this.props.setAttributes( { displayPostAuthor: ! attributes.displayPostAuthor } ) }
						/>
					}
					{ isPost &&
						<ToggleControl
							label={ __( 'Display Date', 'responsive-blocks' ) }
							checked={ attributes.displayPostDate }
							onChange={ () => this.props.setAttributes( { displayPostDate: ! attributes.displayPostDate } ) }
						/>
					}
					<ToggleControl
						label={ __( 'Display Excerpt', 'responsive-blocks' ) }
						checked={ attributes.displayPostExcerpt }
						onChange={ () => this.props.setAttributes( { displayPostExcerpt: ! attributes.displayPostExcerpt } ) }
					/>
					{ attributes.displayPostExcerpt &&
						<RangeControl
							label={ __( 'Excerpt Length', 'responsive-blocks' ) }
							value={ attributes.excerptLength }
							onChange={ ( value ) => setAttributes( { excerptLength: value } ) }
							min={ 0 }
							max={ 150 }
						/>
					}
					<ToggleControl
						label={ __( 'Display Continue Reading Link', 'responsive-blocks' ) }
						checked={ attributes.displayPostLink }
						onChange={ () => this.props.setAttributes( { displayPostLink: ! attributes.displayPostLink } ) }
					/>
					{ attributes.displayPostLink &&
						<TextControl
							label={ __( 'Customize Continue Reading Text', 'responsive-blocks' ) }
							type="text"
							value={ attributes.readMoreText }
							onChange={ ( value ) => this.props.setAttributes( { readMoreText: value } ) }
						/>
					}
				</PanelBody>
				<PanelBody
					title={ __( 'Post and Page Grid Markup', 'responsive-blocks' ) }
					initialOpen={ false }
					className="ra-block-post-grid-markup-settings"
				>
					<SelectControl
						label={ __( 'Post Grid Section Tag', 'responsive-blocks' ) }
						options={ sectionTags }
						value={ attributes.sectionTag }
						onChange={ ( value ) => this.props.setAttributes( { sectionTag: value } ) }
						help={ __( 'Change the post grid section tag to match your content hierarchy.', 'responsive-blocks' ) }
					/>
					{ attributes.sectionTitle &&
						<SelectControl
							label={ __( 'Section Title Heading Tag', 'responsive-blocks' ) }
							options={ sectionTitleTags }
							value={ attributes.sectionTitleTag }
							onChange={ ( value ) => this.props.setAttributes( { sectionTitleTag: value } ) }
							help={ __( 'Change the post/page section title tag to match your content hierarchy.', 'responsive-blocks' ) }
						/>
					}
					{ attributes.displayPostTitle &&
						<SelectControl
							label={ __( 'Post Title Heading Tag', 'responsive-blocks' ) }
							options={ sectionTitleTags }
							value={ attributes.postTitleTag }
							onChange={ ( value ) => this.props.setAttributes( { postTitleTag: value } ) }
							help={ __( 'Change the post/page title tag to match your content hierarchy.', 'responsive-blocks' ) }
						/>
					}
				</PanelBody>
			</InspectorControls>
		);
	}
}
