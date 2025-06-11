<?php
/**
 * Plugin Name: SchemaCraft
 * Plugin URI: https://example.com/schemacraft
 * Description: An advanced WordPress plugin for managing Schema Markup.
 * Version: 0.1.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: schemacraft
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Include the main SchemaCraft class.
if ( ! class_exists( 'SchemaCraft_Main' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-schemacraft-main.php';
}

// Register activation and deactivation hooks.
register_activation_hook( __FILE__, array( 'SchemaCraft_Main', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'SchemaCraft_Main', 'deactivate' ) );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.1.0
 */
function run_schemacraft() {

    $plugin = SchemaCraft_Main::instance();
    // Further plugin setup can go here if needed in the future.

}
run_schemacraft();
?>
