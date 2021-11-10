<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              cloudpitcher.com
 * @since             1.0.0
 * @package           Pay_With_Edupay
 *
 * @wordpress-plugin
 * Plugin Name:       Pay With EduPay
 * Plugin URI:        edupay.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            CloudPitcher
 * Author URI:        cloudpitcher.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pay-with-edupay
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
define( 'FLW_WC_PLUGIN_FILE', __FILE__ );
define( 'FLW_WC_DIR_PATH', plugin_dir_path( FLW_WC_PLUGIN_FILE ) );
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PAY_WITH_EDUPAY_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pay-with-edupay-activator.php
 */
function activate_pay_with_edupay() {
	if ( !class_exists( 'WC_Payment_Gateway' ) ) return;
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pay-with-edupay-activator.php';
	Pay_With_Edupay_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pay-with-edupay-deactivator.php
 */
function deactivate_pay_with_edupay() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pay-with-edupay-deactivator.php';
	Pay_With_Edupay_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_pay_with_edupay' );
register_deactivation_hook( __FILE__, 'deactivate_pay_with_edupay' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-pay-with-edupay.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function edp_pay_plugin_action_links( $links ) {

    $edp_settings_url = esc_url( get_admin_url( null, 'admin.php?page=wc-settings&tab=checkout&section=edupay' ) );
    array_unshift( $links, "<a title='EduPay Settings Page' href='$edp_settings_url'>Settings</a>" );

    return $links;

  }
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'edp_pay_plugin_action_links' );
add_action('plugins_loaded', 'init_edupay_gateway_class');
function init_edupay_gateway_class(){
	require_once( plugin_dir_path( __FILE__ ). 'includes/class-pay-with-edupay-woocommerce-gateway.php');
}
function add_edupay_gateway_class($methods){
	$methods[] = 'Pay_With_Edupay_WooCommerce_Gateway'; 
    return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'add_edupay_gateway_class' );
// function run_pay_with_edupay() {
// 	$plugin = new Pay_With_Edupay();
// 	$plugin->run();

// }
// run_pay_with_edupay();
