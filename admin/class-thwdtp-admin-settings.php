<?php
/**
 * The file that defines the core plugin class
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    order-delivery-date-and-time
 * @subpackage  order-delivery-date-and-time/admin
 */

if(!defined('WPINC')){ die; }

if(!class_exists('THWDTP_Admin_Settings')):

abstract class THWDTP_Admin_Settings {
	protected $page_id = '';	
	public static $section_id = '';
	
	protected $tabs = '';
	protected $sections = '';
	
	public function __construct($page, $section = '') {
		$this->page_id = $page;
		
		//$this->tabs = array( 'general_settings' => 'General Settings', 'configuration_settings' => 'Configuration Settings', 'advanced_settings' => 'Advanced Settings');
		$this->tabs = array( 'general_settings' => 'General Settings');
	}
	
	public function get_tabs(){
		return $this->tabs;
	}

	public function get_current_tab(){
		return $this->page_id;
	}
	
	public function render_tabs(){
		$current_tab = $this->get_current_tab();
		$tabs        = $this->get_tabs();

		if(empty($tabs)){
			return;
		}
		
		echo '<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">';
		foreach( $tabs as $id => $label ){
			$active = ( $current_tab == $id ) ? 'nav-tab-active' : '';
			$label  = esc_html__($label,'order-delivery-date-and-time');
			echo '<a class="nav-tab '.esc_attr($active).'" href="'. esc_url($this->get_admin_url($id)) .'">'.esc_html($label).'</a>';
		}
		echo '</h2>';		
	}

	public function get_admin_url($tab = false){
		$url = 'admin.php?page=th_order-delivery-date-and-time';
		if($tab && !empty($tab)){
			$url .= '&tab='. $tab;
		}
		return admin_url($url);
	}

	public function output_fields($options, $section ){
		
		foreach ( $options as $value ) {
			if ( ! isset( $value['type'] ) ) {
				continue;
			}
			if ( ! isset( $value['label'] ) ) {
				$value['label'] = isset( $value['name'] ) ? $value['name'] : '';
			}
			if ( ! isset( $value['class'] ) ) {
				$value['class'] = '';
			}
			if ( ! isset( $value['css'] ) ) {
				$value['css'] = '';
			}
			if ( ! isset( $value['value'] ) ) {
				$value['value'] = '';
			}
			if ( ! isset( $value['desc'] ) ) {
				$value['desc'] = '';
			}
			if ( ! isset( $value['desc_tip'] ) ) {
				$value['desc_tip'] = false;
			}
			if ( ! isset( $value['placeholder'] ) ) {
				$value['placeholder'] = '';
			}
			if ( ! isset( $value['suffix'] ) ) {
				$value['suffix'] = '';
			}
			
			$value['value'] = $this->get_option( $value['id'], $section, $value['value']);
			
			if ( ! isset( $value['id'] ) ) {
				$value['id'] = '';
			}else{
				$value['id'] = 'i_'.$value['id'];
			}
			
			// Custom attribute handling.
			$custom_attributes = array();

			if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
				foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
					$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
				}
			}
			// Description handling.
			$field_description = $this->get_field_description( $value );
			$description       = $field_description['description'];
			$tooltip_html      = $field_description['tooltip_html'];
			$field_event       = ( isset($value['onchange']) && !empty($value['onchange']) ) ? ' onchange="'.$value['onchange'].'"' : '';

			// Switch based on type.
			switch ( $value['type'] ) {

				// Section Titles.
				case 'title':
					if ( ! empty( $value['title'] ) ) {
						echo '<h2>' . esc_html( $value['title'] ) . '</h2>';
					}
					if ( ! empty( $value['desc'] ) ) {
						echo '<div id="' . esc_attr( sanitize_title( $value['id'] ) ) . '-description">';
						echo wp_kses_post( wpautop( wptexturize( $value['desc'] ) ) );
						echo '</div>';
					}
					echo '<table class="form-table thpladmin-form-table">' . "\n\n";
		
					break;

				// Section Ends.
				case 'sectionend':
					echo '</table>';
					break;

				// Standard text inputs and subtypes like 'number'.
				case 'text':
				case 'number':
					$option_value = $value['value'];

					?><tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['label'] ); ?> <?php echo wp_kses_post($tooltip_html); ?></label>
						</th>
						<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
							<input
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="<?php echo esc_attr( $value['type'] ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								value="<?php echo esc_attr( $option_value ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?>"
								placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
								<?php echo wp_kses_post(implode( ' ', $custom_attributes )); ?>
								<?php echo  wp_kses_post($field_event) ; ?>
								/><?php echo esc_html( $value['suffix'] ); ?> <?php echo esc_html($description); ?>
						</td>
					</tr>
					<?php
					break;
				// Select boxes.
				case 'select':
				case 'multiselect':

					$option_value = $value['value'];
					$field_event       = ( isset($value['onchange']) && !empty($value['onchange']) ) ? ' onchange="'.$value['onchange'].'"' : '';
					?>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['label'] ); ?> <?php echo wp_kses_post($tooltip_html); ?></label>
						</th>
						<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
							<select
								name="<?php echo esc_attr( $value['id'] ); ?><?php echo ( 'multiselect' === $value['type'] ) ? '[]' : ''; ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?>"
								<?php echo wp_kses_post(implode( ' ', $custom_attributes )); ?>
								<?php echo  wp_kses_post($field_event); ?>
								<?php echo 'multiselect' === $value['type'] ? 'multiple="multiple"' : ''; ?>
								>
								<?php
								foreach ( $value['options'] as $key => $val ) {
									?>
									<option value="<?php echo esc_attr( $key ); ?>"
										<?php

										if ( is_array( $option_value ) ) {
											selected( in_array( (string) $key, $option_value, true ), true );
										} else {
											selected( $option_value, (string) $key );
										}

										?>
									><?php echo esc_html( $val ); ?></option>
									<?php
								}
								?>
							</select> <?php echo wp_kses_post($description);?>
						</td>
					</tr>
					<?php
					break;
				// Checkbox input.
				case 'checkbox':
					$option_value     = $value['value'];
					$visibility_class = array();

					if ( ! isset( $value['hide_if_checked'] ) ) {
						$value['hide_if_checked'] = false;
					}
					if ( ! isset( $value['show_if_checked'] ) ) {
						$value['show_if_checked'] = false;
					}
					if ( 'yes' === $value['hide_if_checked'] || 'yes' === $value['show_if_checked'] ) {
						$visibility_class[] = 'hidden_option';
					}
					if ( 'option' === $value['hide_if_checked'] ) {
						$visibility_class[] = 'hide_options_if_checked';
					}
					if ( 'option' === $value['show_if_checked'] ) {
						$visibility_class[] = 'show_options_if_checked';
					}

					if ( ! isset( $value['checkboxgroup'] ) || 'start' === $value['checkboxgroup'] ) {
						?>
							<tr valign="top" class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
								<th scope="row" class="titledesc th-checkbox"><?php echo esc_html( $value['label'] ); ?></th>
								<td class="forminp forminp-checkbox">
									<fieldset>
						<?php
					} else {
						?>
							<fieldset class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
						<?php
					}

					if ( ! empty( $value['label'] ) ) {
						?>
							<legend class="screen-reader-text"><span><?php echo esc_html( $value['label'] ); ?></span></legend>
						<?php
					}

					?>
						<label for="<?php echo esc_attr( $value['id'] ); ?>">
							<input
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="checkbox"
								class="<?php echo esc_attr( isset( $value['class'] ) ? $value['class'] : '' ); ?>"
								value="1"
								<?php checked( $option_value, 'yes' ); ?>
								<?php checked( $option_value, 1 ); ?>
								<?php echo wp_kses_post(implode( ' ', $custom_attributes )); ?>
							/> <?php echo wp_kses_post($description);  ?>
						</label> <?php echo wp_kses_post($tooltip_html);?>
					<?php

					if ( ! isset( $value['checkboxgroup'] ) || 'end' === $value['checkboxgroup'] ) {
						?>
									</fieldset>
								</td>
							</tr>
						<?php
					} else {
						?>
							</fieldset>
						<?php
					}
					break;
				case 'description':
					?>
					<tr>
					<td colspan="2">
					<?php
					if ( ! empty( $value['description'] ) ) {
						echo '<h4>' . esc_html( $value['description'] ) . '</h4>';
					}
					?>
					</td>
					</tr>

					<?php
				// Default: run an action.
				default:
					do_action( 'thwdtp_admin_field_' . $value['type'], $value );
					break;
			}
		}
	}

	public function get_option( $name, $section, $default = '' ) {
		if ( ! $name ) {
			return $default;
		}
		$all_settings = get_option(THWDTP_Utils::OPTION_KEY_DELIVERY_SETTINGS);

		$option_value = null;
		if(is_array($all_settings) && !empty($all_settings)){

			$settings     = (!empty($all_settings[$section])) ? $all_settings[$section] : null ;
			if($section === 'delivery_time' || $section === 'pickup_time'){
				$settings = isset($settings['time_settings']) ? $settings['time_settings'] : $settings ; 
			}
			$option_value =  (is_array($settings) && isset($settings[$name])) ? $settings[$name] : null;
		}

		if ( is_array( $option_value ) ) {
			$option_value = wp_unslash( $option_value );
		} elseif ( ! is_null( $option_value ) ) {
			$option_value = stripslashes( $option_value );
		}

		return ( null === $option_value ) ? $default : $option_value;
	}

	public function get_field_description( $value ) {

		$description  = '';
		$tooltip_html = '';

		if ( true === $value['desc_tip'] ) {
			$tooltip_html = $value['desc'];
		} elseif ( ! empty( $value['desc_tip'] ) ) {
			$description  = $value['desc'];
			$tooltip_html = $value['desc_tip'];
		} elseif ( ! empty( $value['desc'] ) ) {
			$description = $value['desc'];
		}

		if ( $description && in_array( $value['type'], array( 'textarea', 'radio' ), true ) ) {
			$description = '<p style="margin-top:0">' . wp_kses_post( $description ) . '</p>';
		} elseif ( $description && in_array( $value['type'], array( 'checkbox' ), true ) ) {
			$description = wp_kses_post( $description );
		} elseif ( $description ) {
			$description = '<p class="description">' . wp_kses_post( $description ) . '</p>';
		}

		if ( $tooltip_html && in_array( $value['type'], array( 'checkbox' ), true ) ) {
			$tooltip_html = '<p class="description">' . wp_kses_post($tooltip_html) . '</p>';
		} elseif ( $tooltip_html ) {
			$tooltip_html = wc_help_tip( $tooltip_html );
		}

		return array(
			'description'  => $description,
			'tooltip_html' => $tooltip_html,
		);
	}
}

endif;