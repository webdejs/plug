<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Main class for the SchemaCraft plugin.
 *
 * @since 0.1.0
 */
final class SchemaCraft_Main {

    /**
     * The single instance of the class.
     *
     * @since 0.1.0
     * @var   SchemaCraft_Main
     * @static
     */
    private static $instance = null;

    /**
     * Plugin version.
     *
     * @since 0.1.0
     * @var   string
     */
    public $version = '0.1.0';

    /**
     * The plugin path.
     *
     * @since 0.1.0
     * @var   string
     */
    public $plugin_path;

    /**
     * The plugin url.
     *
     * @since 0.1.0
     * @var   string
     */
    public $plugin_url;

    /**
     * The settings manager instance.
     *
     * @since 0.1.0
     * @var   SchemaCraft_Settings
     */
    public $settings_manager;


    /**
     * Ensures only one instance of the class is loaded or can be loaded.
     *
     * @since  0.1.0
     * @static
     * @see    SchemaCraft_Main::init()
     * @return SchemaCraft_Main - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
            self::$instance->setup_constants();
            self::$instance->init_hooks(); // Call a new method to setup hooks
        }
        return self::$instance;
    }

    /**
     * Constructor.
     *
     * @since 0.1.0
     * @access private
     */
    private function __construct() {
        // We don't want to do anything here.
    }

    /**
     * Initialize WordPress hooks.
     *
     * @since 0.1.0
     * @access private
     */
    private function init_hooks() {
        if ( is_admin() ) {
            require_once SCHEMACRAFT_PLUGIN_DIR . 'admin/class-schemacraft-settings.php';
            $this->settings_manager = new SchemaCraft_Settings();
            add_action( 'admin_menu', array( $this, 'admin_menu_setup' ) );
            add_action( 'admin_init', array( $this->settings_manager, 'register_settings' ) );
            add_action( 'admin_enqueue_scripts', array( $this->settings_manager, 'enqueue_admin_assets' ) );
        }
        // Add other hooks (public, etc.) here
    }

    /**
     * Setup the admin menu.
     *
     * @since 0.1.0
     */
    public function admin_menu_setup() {
        add_menu_page(
            __( 'SchemaCraft Settings', 'schemacraft' ), // Page title
            __( 'SchemaCraft', 'schemacraft' ),          // Menu title
            'manage_options',                            // Capability
            'schemacraft',                               // Menu slug
            array( $this->settings_manager, 'display_settings_page' ),      // Callback function
            'dashicons-admin-generic'                    // Icon URL
        );
    }

    /**
	 * Setup plugin constants.
	 *
	 * @access private
	 * @since  0.1.0
	 * @return void
	 */
	private function setup_constants() {
		// Plugin version.
		if ( ! defined( 'SCHEMACRAFT_VERSION' ) ) {
			define( 'SCHEMACRAFT_VERSION', $this->version );
		}

		// Plugin Folder Path.
        $this->plugin_path = plugin_dir_path( dirname(__FILE__, 2) . '/schemacraft.php' );
		if ( ! defined( 'SCHEMACRAFT_PLUGIN_DIR' ) ) {
			define( 'SCHEMACRAFT_PLUGIN_DIR', $this->plugin_path );
		}

		// Plugin Folder URL.
        $this->plugin_url = plugin_dir_url( dirname(__FILE__, 2) . '/schemacraft.php' );
		if ( ! defined( 'SCHEMACRAFT_PLUGIN_URL' ) ) {
			define( 'SCHEMACRAFT_PLUGIN_URL', $this->plugin_url );
		}

		// Plugin Root File.
		// Note: SCHEMACRAFT_PLUGIN_FILE was defined as __FILE__ which is class-schemacraft-main.php
		// It should ideally be the main plugin file for plugin_dir_path and plugin_dir_url consistency
		// For now, the above dirname usage for path and URL should make them root-based.
		if ( ! defined( 'SCHEMACRAFT_PLUGIN_FILE' ) ) {
			define( 'SCHEMACRAFT_PLUGIN_FILE', dirname(__FILE__, 2) . '/schemacraft.php' );
		}
	}

    /**
     * Cloning is forbidden.
     *
     * @since 0.1.0
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'schemacraft' ), '0.1.0' );
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 0.1.0
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'schemacraft' ), '0.1.0' );
    }

    /**
     * Plugin activation hook.
     *
     * @since 0.1.0
     * @static
     */
    public static function activate() {
        // You can add activation code here.
        // For example, set a transient or option.
        set_transient( 'schemacraft_activated_time', time(), 3600 );
    }

    /**
     * Plugin deactivation hook.
     *
     * @since 0.1.0
     * @static
     */
    public static function deactivate() {
        // You can add deactivation code here.
        // For example, delete the transient or option.
        delete_transient( 'schemacraft_activated_time' );
    }
}
?>
