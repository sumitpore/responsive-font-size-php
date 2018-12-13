<?php
/**
 * This function returns responsive font size array.
 *
 * @see https://github.com/MartijnCuppens/rfs
 *
 * @param  integer $font_size         Default font size.
 * @param  integer $factor            This value determines the strength of font size resizing. The higher the factor,
 *                                    the less difference there is between font sizes on small screens. Lower value of
 *                                    factor results in bigger font sizes for small screens. The factor must me greater
 *                                    than 1, setting it to 1 will disable dynamic rescaling.
 * @param  boolean $important         Mark this css as important.
 * @param  string  $minimum_font_size The option will prevent the font size from becoming too small on smaller screens.
 *                                    If the font size passed in first parameter is smaller than this minimum font size,
 *                                    no fluid font rescaling will take place.
 * @param  string  $breakpoint        Breakpoint device size. Make sure you use same breakpoint in your css too.
 * @param  boolean $two_dimensional   Enabling the two dimensional media queries will determine the font size based on
 *                                    the smallest side of the screen with vmin. This prevents the font size from
 *                                    changing if the device toggles between portrait and landscape mode.
 * @return array                      array with default font size and responsive font size.
 */
function css_get_responsive_font_size( $font_size, $factor = 5, $important = false, $minimum_font_size = '12px', $breakpoint = '768px', $two_dimensional = true ) {

	$final_array = array();
	$rfs_suffix  = '';
	if ( $important ) {
		$rfs_suffix = ' !important';
	}

	/*
	 * If unit is given in input then code to remove unit needs to be added here.
	 * Remove unit from breakpoint
	 * Remove unit from minimum-font-size
	 * Remove unit from font-size
	 */
	$breakpoint        = str_replace( 'px', '', $breakpoint );
	$minimum_font_size = str_replace( 'px', '', $minimum_font_size );
	$font_size         = str_replace( 'px', '', $font_size );

	/**
	 * If $font_size isn't a number (like inherit) or $font_size has a unit (not px or rem, like 1.5em) or $font_size is 0, just print the value.
	 */
	if ( ! is_numeric( $font_size ) || strpos( $font_size, 'px' ) || strpos( $font_size, 'rem' ) || 0 == $font_size ) {
		$final_array['default']    = $font_size . $rfs_suffix;
		$final_array['responsive'] = $font_size . $rfs_suffix;
	} else {

		// If minimum font size and passed font size are same, value of default and responsive font sizes will be same.
		if ( ( $font_size == $minimum_font_size ) || ( $font_size <= $minimum_font_size ) ) {
			$final_array['default']    = $font_size . $rfs_suffix;
			$final_array['responsive'] = $font_size . $rfs_suffix;
			return $final_array;
		}

		// Variables for storing static and fluid rescaling.
		$rfs_static = null;
		$rfs_fluid  = null;

		// Set default font-size.
		$rfs_static = $font_size . 'px' . $rfs_suffix;

		if ( ! is_numeric( $factor ) || $factor < 1 ) {
			return array(
				'error' => $factor . ' is not a valid it must be greater or equal to 1.',
			);
		}

		/**
		 * Only add media query if font-size is bigger as the minimum font-size
		 * If $factor == 1, no rescaling will take place.
		 */
		if ( $font_size > $minimum_font_size && 1 != $factor ) {
			/*
			 * Calculate minimum font-size for given font-size
			 */
			$fs_min = $minimum_font_size + ( $font_size - $minimum_font_size ) / $factor;

			/*
			 * Calculate difference between given font-size and minimum font-size for given font-size
			 */
			$fs_diff = $font_size - $fs_min;

			/*
			 * Minimum font-size formatting
			 * No need to check if the unit is valid, because we did that before
			 */
			$min_width = $fs_min . 'px';

			/*
			 * If two-dimensional, use smallest of screen width and height
			 */
			if ( $two_dimensional ) {
				$variable_unit = 'vmin';
			} else {
				$variable_unit = 'vw';
			}

			/*
			 * Calculate the variable width between 0 and $rfs-breakpoint
			 */
			$variable_width = round( ( $fs_diff * 100 / $breakpoint ), 5 ) . $variable_unit;

			/*
			 * Set the calculated font-size.
			 */
			$rfs_fluid = "calc($min_width + $variable_width)" . $rfs_suffix;
		}

		/*
		 * Rendering
		 */
		if ( null == $rfs_fluid ) {
			$final_array['default']    = $rfs_static;
			$final_array['responsive'] = $rfs_static;
		} else {
			$final_array['default']    = $rfs_static;
			$final_array['responsive'] = $rfs_fluid;
		}
	}

	return $final_array;
}
