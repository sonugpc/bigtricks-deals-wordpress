<?php
/**
 * Plugin Name:       Bigtricks Deals & Store Taxonomy
 * Plugin URI:        https://bigtricks.in
 * Description:       Adds a "Deal" custom post type with product details, store taxonomy, and REST API support.
 * Version:           2.1.0
 * Author:            Bigtricks
 * Author URI:        https://bigtricks.in
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bigtricks-deals
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define constants
 */
define( 'BTDEALS_VERSION', '2.1.0' );
define( 'BTDEALS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BTDEALS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_bigtricks_deals() {
	// Flush rewrite rules on activation.
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'activate_bigtricks_deals' );

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_bigtricks_deals() {
	// Flush rewrite rules on deactivation.
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'deactivate_bigtricks_deals' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-bigtricks-deals.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_bigtricks_deals() {

	$plugin = new Bigtricks_Deals();
	$plugin->run();

}
run_bigtricks_deals();
