// Import block dependencies and components
import classnames from 'classnames';
import Inspector from './inspector';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { compose } = wp.compose;
const { Component, Fragment } = wp.element;

const {
	RichText,
	withFontSizes,
	withColors,
} = wp.editor;

class Edit extends Component {

	constructor() {
		super( ...arguments );
	}

	render() {

		// Setup the attributes
		const {
			attributes: {
				price,
				currency,
				term,
				showTerm,
				showCurrency,
				paddingTop,
				paddingRight,
				paddingBottom,
				paddingLeft,
			},
			isSelected,
			className,
			setAttributes,
			fallbackFontSize,
			fontSize,
			backgroundColor,
			textColor,
		} = this.props;

		// Setup wrapper class names
		const editClassWrapperName = classnames( {
			'ra-pricing-table-price-wrap': true,
			'has-text-color': textColor.color,
			'has-background': backgroundColor.color,
			[ backgroundColor.class ]: backgroundColor.class,
			[ textColor.class ]: textColor.class,
			'ra-pricing-has-currency': showCurrency,
		} );

		// Setup price class names
		const editClassName = classnames( {
			'ra-pricing-table-price': true,
			[ fontSize.class ]: fontSize.class,
		} );

		// Setup wrapper styles
		const editWrapStyles = {
			backgroundColor: backgroundColor.color,
			color: textColor.color,
			paddingTop: paddingTop ? paddingTop + 'px' : undefined,
			paddingRight: paddingRight ? paddingRight + 'px' : undefined,
			paddingBottom: paddingBottom ? paddingBottom + 'px' : undefined,
			paddingLeft: paddingLeft ? paddingLeft + 'px' : undefined,
		};

		// Setup price styles
		const editStyles = {
			fontSize: fontSize.size ? fontSize.size + 'px' : undefined,
		};

		// Setup currency styles
		var currencySize = Math.floor(fontSize.size / 2.5);
		const currencyStyles = {
			fontSize: fontSize.size ? currencySize + 'px' : undefined,
		};

		// Setup term styles
		var termSize = Math.floor(fontSize.size / 2.5);
		const termStyles = {
			fontSize: fontSize.size ? termSize + 'px' : undefined,
		};

		return [
			<Fragment>
				<Inspector
					{ ...this.props }
				/>
				<div
					className={ editClassWrapperName ? editClassWrapperName : undefined }
					style={ editWrapStyles }
				>
					<div itemProp="offers" itemScope itemType="http://schema.org/Offer">
						{ showCurrency && (
							<RichText
								tagName="span"
								itemProp="priceCurrency"
								placeholder={ __( '$', 'responsive-blocks' ) }
								keepPlaceholderOnFocus
								value={ currency }
								onChange={ ( value ) => setAttributes( { currency: value } ) }
								className="ra-pricing-table-currency"
								style={ currencyStyles }
							/>
						) }
						<RichText
							tagName="div"
							itemProp="price"
							placeholder={ __( '49', 'responsive-blocks' ) }
							keepPlaceholderOnFocus
							value={ price }
							onChange={ ( value ) => setAttributes( { price: value } ) }
							style={ editStyles }
							className={ editClassName ? editClassName : undefined }
						/>
						{ showTerm && (
							<RichText
								tagName="span"
								value={ term }
								placeholder={ __( '/mo', 'responsive-blocks' ) }
								keepPlaceholderOnFocus
								onChange={ ( value ) => setAttributes( { term: value } ) }
								className="ra-pricing-table-term"
								style={ termStyles }
							/>
						) }
					</div>
				</div>
			</Fragment>
		];
	}
}

export default compose( [
	withFontSizes( 'fontSize' ),
	withColors( 'backgroundColor', { textColor: 'color' } ),
] )( Edit );
