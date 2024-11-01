<?php
/**
 *
 * @link              http://www.benjamindejong.com/your-tables
 * @since             1.0.4
 * @package           Your_Tables
 *
 * @wordpress-plugin
 * Plugin Name:       Your Tables
 * Plugin URI:        http://www.benjamindejong.com/your-tables
 * Description:       This plugins lets you define forms in the admin area for existing tables in your WordPress database.
 * Version:           1.0.4
 * Author:            Benjamin de Jong
 * Author URI:        http://www.benjamindejong.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       your-tables
 * Domain Path:       /languages
 */
 
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-your-tables-activator.php
 */
if (!function_exists('activate_your_tables')) {
	function activate_your_tables() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-your-tables-activator.php';
		Your_Tables_Activator::activate();
	}
 }
/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-your-tables-deactivator.php
 */
if (!function_exists('deactivate_your_tables')) {
	function deactivate_your_tables() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-your-tables-deactivator.php';
		Your_Tables_Deactivator::deactivate();
	}
}
register_activation_hook( __FILE__, 'activate_your_tables' );
register_deactivation_hook( __FILE__, 'deactivate_your_tables' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-your-tables.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 */
if (!function_exists('run_your_tables')) {
	function run_your_tables() {

		$plugin = new Your_Tables();
		$plugin->run();

	}
}

run_your_tables();
