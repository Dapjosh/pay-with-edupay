<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       cloudpitcher.com
 * @since      1.0.0
 *
 * @package    Pay_With_Edupay
 * @subpackage Pay_With_Edupay/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Pay_With_Edupay
 * @subpackage Pay_With_Edupay/includes
 * @author     CloudPitcher <support@cloudpitcher.com>
 */
class Pay_With_Edupay_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'pay-with-edupay',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
