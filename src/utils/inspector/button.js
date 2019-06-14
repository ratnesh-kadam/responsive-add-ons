const { __ } = wp.i18n;
const { Fragment } = wp.element;
const {
	SelectControl,
	ToggleControl,
} = wp.components;
const {
	PanelColorSettings,
} = wp.editor;

export default function ButtonSettings( props ) {
	const {
		enableButtonBackgroundColor,
		buttonBackgroundColor,
		onChangeButtonColor = () => {},
		enableButtonTextColor,
		buttonTextColor,
		onChangeButtonTextColor = () => {},
		enableButtonSize,
		buttonSize,
		onChangeButtonSize = () => {},
		enableButtonShape,
		buttonShape,
		onChangeButtonShape = () => {},
		enableButtonTarget,
		buttonTarget,
		onChangeButtonTarget = () => {},
	} = props;

	// Button size values
	const buttonSizeOptions = [
		{ value: 'ra-button-size-small', label: __( 'Small', 'responsive-blocks' ) },
		{ value: 'ra-button-size-medium', label: __( 'Medium', 'responsive-blocks' ) },
		{ value: 'ra-button-size-large', label: __( 'Large', 'responsive-blocks' ) },
		{ value: 'ra-button-size-extralarge', label: __( 'Extra Large', 'responsive-blocks' ) },
	];

	// Button shape
	const buttonShapeOptions = [
		{ value: 'ra-button-shape-square', label: __( 'Square', 'responsive-blocks' ) },
		{ value: 'ra-button-shape-rounded', label: __( 'Rounded Square', 'responsive-blocks' ) },
		{ value: 'ra-button-shape-circular', label: __( 'Circular', 'responsive-blocks' ) },
	];

	return (
		<Fragment>
			{ enableButtonTarget != false && (
				<ToggleControl
					label={ __( 'Open link in new window', 'responsive-blocks' ) }
					checked={ buttonTarget }
					onChange={ onChangeButtonTarget }
				/>
			) }
			{ enableButtonSize != false && (
				<SelectControl
					selected={ buttonSize }
					label={ __( 'Button Size', 'responsive-blocks' ) }
					value={ buttonSize }
					options={ buttonSizeOptions.map( ({ value, label }) => ( {
						value: value,
						label: label,
					} ) ) }
					onChange={ onChangeButtonSize }
				/>
			) }
			{ enableButtonShape != false && (
				<SelectControl
					label={ __( 'Button Shape', 'responsive-blocks' ) }
					value={ buttonShape }
					options={ buttonShapeOptions.map( ({ value, label }) => ( {
						value: value,
						label: label,
					} ) ) }
					onChange={ onChangeButtonShape }
				/>
			) }
			{ enableButtonBackgroundColor != false && (
				<PanelColorSettings
					title={ __( 'Button Color', 'responsive-blocks' ) }
					initialOpen={ false }
					colorSettings={ [ {
						value: buttonBackgroundColor,
						onChange: onChangeButtonColor,
						label: __( 'Button Color', 'responsive-blocks' ),
					} ] }
				>
				</PanelColorSettings>
			) }
			{ enableButtonTextColor != false && (
				<PanelColorSettings
					title={ __( 'Button Text Color', 'responsive-blocks' ) }
					initialOpen={ false }
					colorSettings={ [ {
						value: buttonTextColor,
						onChange: onChangeButtonTextColor,
						label: __( 'Button Text Color', 'responsive-blocks' ),
					} ] }
				>
				</PanelColorSettings>
			) }
		</Fragment>
	);
}
