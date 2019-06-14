/**
 * Inspector Controls.
 */

 /**
 * External dependencies.
 */
import map from 'lodash/map';
import columnLayouts from './column-layouts';
import Margin from './../../../utils/components/margin';
import Padding from './../../../utils/components/padding';

/**
 * WordPress dependencies.
 */
const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const {
	InspectorControls,
	PanelColorSettings,
	ContrastChecker,
} = wp.editor;
const {
	PanelBody,
	RangeControl,
	ButtonGroup,
	Button,
	Tooltip,
	ToggleControl,
	SelectControl,
} = wp.components;

/**
 * Create an Inspector Controls wrapper Component
 */
export default class Inspector extends Component {

	constructor( props ) {
		super( ...arguments );
	}

	render() {

		const {
			attributes,
			setAttributes,
			backgroundColor,
			setBackgroundColor,
			textColor,
			setTextColor,
		} = this.props;

		let selectedRows = 1;

		if ( attributes.columns ) {
			selectedRows = parseInt( attributes.columns.toString().split('-') );
		}

		/* CSS Units */
		const cssUnits = [
			{ value: 'px', label: __( 'Pixel (px)', 'responsive-blocks' ) },
			{ value: '%', label: __( 'Percent (%)', 'responsive-blocks' ) },
			{ value: 'em', label: __( 'Em (em)', 'responsive-blocks' ) },
        ];

		return (
			<InspectorControls key="inspector">
				{ attributes.layout &&
					/* Show the column settings once a layout is selected. */
					<PanelBody
						title={ __( 'General', 'responsive-blocks' ) }
						initialOpen={ true }
						className="ra-column-select-panel"
					>
						<RangeControl
							label={ __( 'Column Count', 'responsive-blocks' ) }
							help={ __( "Note: Changing the column count after you've added content to the column can cause loss of content.", 'responsive-blocks' ) }
							value={ attributes.columns }
							onChange={ ( value ) => this.props.setAttributes( {
								columns: value,
								layout: 'ra-' + value + '-col-equal',
							} ) }
							min={ 1 }
							max={ 6 }
							step={ 1 }
						/>

						<hr />

						{ ( attributes.columns == 2 || attributes.columns == 3 || attributes.columns == 4 ) &&
							<Fragment>
								<p>{ __( 'Column Layout', 'responsive-blocks' ) }</p>
								<ButtonGroup aria-label={ __( 'Column Layout', 'responsive-blocks' ) }>
									{ map( columnLayouts[ selectedRows ], ( { name, key, icon, col } ) => (
										<Tooltip text={ name } key={ key }>
											<Button
												key={ key }
												className="ra-column-selector-button"
												isSmall
												onClick={ () => {
													setAttributes( {
														layout: key,
													} );
													this.setState( { 'selectLayout' : false } );
												} }
											>
												{ icon }
											</Button>
										</Tooltip>
									) ) }
								</ButtonGroup>
								<p><i>{ __( 'Change the layout of your columns.', 'responsive-blocks' ) }</i></p>
								<hr />
							</Fragment>
						}

						<RangeControl
							label={ __( 'Column Gap', 'responsive-blocks' ) }
							help={ __( 'Adjust the spacing between columns.', 'responsive-blocks' ) }
							value={ attributes.columnsGap }
							onChange={ ( value ) => this.props.setAttributes( { columnsGap: value } ) }
							min={ 0 }
							max={ 10 }
							step={ 1 }
						/>

						<hr />

						<RangeControl
							label={ __( 'Column Inner Max Width (px)' ) }
							help={ __( 'Adjust the width of the content inside the container wrapper.', 'responsive-blocks' ) }
							value={ attributes.columnMaxWidth }
							onChange={ ( value ) => this.props.setAttributes( { columnMaxWidth: value } ) }
							min={ 0 }
							max={ 2000 }
							step={ 1 }
						/>

						{ attributes.columnMaxWidth > 0 &&
							<ToggleControl
								label={ __( 'Center Columns In Container', 'responsive-blocks' ) }
								help={ __( 'Center the columns in the container when max-width is used.', 'responsive-blocks' ) }
								checked={ attributes.centerColumns }
								onChange={ () => this.props.setAttributes( { centerColumns: ! attributes.centerColumns } ) }
							/>
						}

						<hr />

						<ToggleControl
							label={ __( 'Responsive Columns', 'responsive-blocks' ) }
							help={ __( 'Columns will be adjusted to fit on tablets and mobile devices.', 'responsive-blocks' ) }
							checked={ attributes.responsiveToggle }
							onChange={ () => this.props.setAttributes( { responsiveToggle: ! attributes.responsiveToggle } ) }
						/>
					</PanelBody>
				}
				<PanelBody
					title={ __( 'Margin and Padding', 'responsive-blocks' ) }
					initialOpen={ false }
				>
					<SelectControl
						label={ __( 'Margin Unit', 'responsive-blocks' ) }
						help={ __( 'Choose between pixel, percent, or em units.', 'responsive-blocks' ) }
						options={ cssUnits }
						value={ attributes.marginUnit }
						onChange={ ( value ) => this.props.setAttributes( { marginUnit: value } ) }
					/>
					<ToggleControl
						label={ __( 'Sync Margin', 'responsive-blocks' ) }
						help={ __( 'Top and bottom margins will have the same value.', 'responsive-blocks' ) }
						checked={ attributes.marginSync }
						onChange={ () => this.props.setAttributes( { marginSync: ! attributes.marginSync } ) }
					/>
					{ ! attributes.marginSync ?
						<Margin
							/* Margin top. */
							marginEnableTop={ true }
							marginTop={ attributes.marginTop }
							marginTopMin="0"
							marginTopMax="200"
							onChangeMarginTop={ marginTop => setAttributes( { marginTop } ) }
							/* Margin bottom. */
							marginEnableBottom={ true }
							marginBottom={ attributes.marginBottom }
							marginBottomMin="0"
							marginBottomMax="200"
							onChangeMarginBottom={ marginBottom => setAttributes( { marginBottom } ) }
						/>
					:
						<Margin
							/* Margin top/bottom. */
							marginEnableVertical={ true }
							marginVerticalLabel={ __( 'Margin Top/Bottom', 'responsive-blocks' ) }
							marginVertical={ attributes.margin }
							marginVerticalMin="0"
							marginVerticalMax="200"
							onChangeMarginVertical={ margin => setAttributes( { margin } ) }
						/>
					}

					<hr />

					<SelectControl
						label={ __( 'Padding Unit', 'responsive-blocks' ) }
						help={ __( 'Choose between pixel, percent, or em units.', 'responsive-blocks' ) }
						options={ cssUnits }
						value={ attributes.paddingUnit }
						onChange={ ( value ) => this.props.setAttributes( { paddingUnit: value } ) }
					/>

					<ToggleControl
						label={ __( 'Sync Padding', 'responsive-blocks' ) }
						help={ __( 'Padding on all sides will have the same value.', 'responsive-blocks' ) }
						checked={ attributes.paddingSync }
						onChange={ () => this.props.setAttributes( { paddingSync: ! attributes.paddingSync } ) }
					/>

					{ ! attributes.paddingSync ?
						<Padding
							/* Padding top. */
							paddingEnableTop={ true }
							paddingTop={ attributes.paddingTop }
							paddingTopMin="0"
							paddingTopMax="200"
							onChangePaddingTop={ paddingTop => setAttributes( { paddingTop } ) }
							/* Padding right. */
							paddingEnableRight={ true }
							paddingRight={ attributes.paddingRight }
							paddingRightMin="0"
							paddingRightMax="200"
							onChangePaddingRight={ paddingRight => setAttributes( { paddingRight } ) }
							/* Padding bottom. */
							paddingEnableBottom={ true }
							paddingBottom={ attributes.paddingBottom }
							paddingBottomMin="0"
							paddingBottomMax="200"
							onChangePaddingBottom={ paddingBottom => setAttributes( { paddingBottom } ) }
							/* Padding left. */
							paddingEnableLeft={ true }
							paddingLeft={ attributes.paddingLeft }
							paddingLeftMin="0"
							paddingLeftMax="200"
							onChangePaddingLeft={ paddingLeft => setAttributes( { paddingLeft } ) }
						/>
					:
						<Padding
							/* Padding. */
							paddingEnable={ true }
							padding={ attributes.padding }
							paddingMin="0"
							paddingMax="200"
							onChangePadding={ padding => setAttributes( { padding } ) }
						/>
					}
				</PanelBody>

				<PanelColorSettings
					title={ __( 'Color', 'responsive-blocks' ) }
					initialOpen={ false }
					colorSettings={ [
						{
							value: backgroundColor.color,
							onChange: setBackgroundColor,
							label: __( 'Background Color', 'responsive-blocks' ),
						},
						{
							value: textColor.color,
							onChange: setTextColor,
							label: __( 'Text Color', 'responsive-blocks' ),
						}
					] }
				>
					<ContrastChecker
						{ ...{
							textColor: textColor.color,
							backgroundColor: backgroundColor.color,
						} }
					/>
				</PanelColorSettings>
			</InspectorControls>
		);
	}
}
