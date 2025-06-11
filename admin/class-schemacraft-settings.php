<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

class SchemaCraft_Settings {

    private $active_tab;
    private $supported_schema_types;

    public function __construct() {
        // Determine the active tab
        $this->active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';

        // Define supported schema types
        $this->supported_schema_types = array(
            'article'       => __( 'Article', 'schemacraft' ),
            'product'       => __( 'Product', 'schemacraft' ),
            'course'        => __( 'Course', 'schemacraft' ),
            'event'         => __( 'Event', 'schemacraft' ),
            'recipe'        => __( 'Recipe', 'schemacraft' ),
            'faq'           => __( 'FAQ Page', 'schemacraft' ),
            'local_business'=> __( 'Local Business', 'schemacraft' ),
            // Add more as needed
        );
    }

    public function register_settings() {
        // Register General Settings
        register_setting(
            'schemacraft_general_settings',
            'schemacraft_options_general',
            array( $this, 'sanitize_general_settings' )
        );

        // Add General Settings Section
        add_settings_section(
            'schemacraft_general_settings_section_main',
            __( 'Main Settings', 'schemacraft' ),
            array( $this, 'render_general_section_main_text' ),
            'schemacraft_general_settings_section' // Page slug for this section's fields
        );

        // Add Fields to General Settings Section
        add_settings_field(
            'schemacraft_enabled',
            __( 'Enable Plugin', 'schemacraft' ),
            array( $this, 'render_field_enabled_switch' ),
            'schemacraft_general_settings_section', // Page slug (for do_settings_sections)
            'schemacraft_general_settings_section_main' // Section ID
        );

        add_settings_field(
            'schemacraft_org_name',
            __( 'Organization Name', 'schemacraft' ),
            array( $this, 'render_field_org_name' ),
            'schemacraft_general_settings_section',
            'schemacraft_general_settings_section_main'
        );

        add_settings_field(
            'schemacraft_org_logo',
            __( 'Organization Logo', 'schemacraft' ),
            array( $this, 'render_field_org_logo' ),
            'schemacraft_general_settings_section',
            'schemacraft_general_settings_section_main'
        );

        // Register Schema Types Settings
        register_setting(
            'schemacraft_schema_types_settings',
            'schemacraft_options_schema_types',
            array( $this, 'sanitize_schema_types_settings' )
        );

        // Add Schema Types Settings Section
        add_settings_section(
            'schemacraft_schema_types_section_main',
            __( 'Available Schema Types', 'schemacraft' ),
            array( $this, 'render_schema_types_section_main_text' ),
            'schemacraft_schema_types_settings_section' // Page slug for this section's fields
        );

        // Dynamically add fields for each schema type
        foreach ( $this->supported_schema_types as $slug => $name ) {
            add_settings_field(
                'schemacraft_enable_schema_' . $slug,
                $name, // Use the translated name as the label
                array( $this, 'render_field_schema_type_switch' ),
                'schemacraft_schema_types_settings_section', // Page slug for this section's fields
                'schemacraft_schema_types_section_main',   // Section ID
                array( 'slug' => $slug, 'name' => $name )    // Arguments for the callback
            );
        }
    }

    public function sanitize_general_settings( $input ) {
        $sanitized_input = array();
        if ( isset( $input['enabled'] ) ) {
            $sanitized_input['enabled'] = absint( $input['enabled'] );
        } else {
            $sanitized_input['enabled'] = 0;
        }
        if ( isset( $input['org_name'] ) ) {
            $sanitized_input['org_name'] = sanitize_text_field( $input['org_name'] );
        }
        if ( isset( $input['org_logo'] ) ) {
            $sanitized_input['org_logo'] = esc_url_raw( $input['org_logo'] );
        }
        return $sanitized_input;
    }

    public function render_general_section_main_text() {
        echo '<p>' . esc_html__( 'Configure the main settings for SchemaCraft.', 'schemacraft' ) . '</p>';
    }

    public function render_field_enabled_switch() {
        $options = get_option( 'schemacraft_options_general' );
        $checked = isset( $options['enabled'] ) ? $options['enabled'] : 0; // Default to 0 (unchecked) if not set
        echo '<input type="checkbox" id="schemacraft_enabled" name="schemacraft_options_general[enabled]" value="1" ' . checked( 1, $checked, false ) . ' />';
        echo '<label for="schemacraft_enabled"> ' . esc_html__( 'Globally enable SchemaCraft functionality', 'schemacraft' ) . '</label>';
    }

    public function render_field_org_name() {
        $options = get_option( 'schemacraft_options_general' );
        $value = isset( $options['org_name'] ) ? $options['org_name'] : '';
        echo '<input type="text" id="schemacraft_org_name" name="schemacraft_options_general[org_name]" value="' . esc_attr( $value ) . '" class="regular-text" />';
    }

    public function render_field_org_logo() {
        $options = get_option( 'schemacraft_options_general' );
        $value = isset( $options['org_logo'] ) ? $options['org_logo'] : '';
        echo '<input type="text" id="schemacraft_org_logo" name="schemacraft_options_general[org_logo]" value="' . esc_attr( $value ) . '" class="regular-text" />';
        echo ' <input type="button" id="schemacraft_upload_logo_button" class="button" value="' . esc_attr__( 'Upload Logo', 'schemacraft' ) . '" />';
        echo '<p class="description">' . esc_html__( 'Enter URL or upload logo. The logo should represent the organization, not a specific product or service.', 'schemacraft' ) . '</p>';
    }

    public function render_schema_types_section_main_text() {
        echo '<p>' . esc_html__( 'Enable or disable specific schema types across your site.', 'schemacraft' ) . '</p>';
    }

    public function render_field_schema_type_switch( $args ) {
        $options = get_option( 'schemacraft_options_schema_types' );
        $slug = $args['slug'];
        $name = $args['name']; // Not strictly needed for the input, but good for context

        $checked = isset( $options[$slug] ) ? $options[$slug] : 0; // Default to disabled

        echo '<input type="checkbox" id="schemacraft_schema_type_' . esc_attr( $slug ) . '" name="schemacraft_options_schema_types[' . esc_attr( $slug ) . ']" value="1" ' . checked( 1, $checked, false ) . ' />';
        echo '<label for="schemacraft_schema_type_' . esc_attr( $slug ) . '"> ' . sprintf( esc_html__( 'Enable %s schema', 'schemacraft' ), esc_html( $name ) ) . '</label>';
    }

    public function sanitize_schema_types_settings( $input ) {
        $sanitized_input = array();
        if ( ! empty( $input ) && is_array( $input ) ) {
            foreach ( $this->supported_schema_types as $slug => $name ) {
                if ( isset( $input[$slug] ) ) {
                    $sanitized_input[$slug] = absint( $input[$slug] );
                } else {
                    $sanitized_input[$slug] = 0; // Ensure all known types have a value
                }
            }
        }
        // Ensure all supported types have an entry, even if not in $input (e.g., form submitted from another tab)
        foreach ( $this->supported_schema_types as $slug => $name ) {
            if ( ! isset( $sanitized_input[$slug] ) ) {
                $sanitized_input[$slug] = 0;
            }
        }
        return $sanitized_input;
    }

    public function enqueue_admin_assets( $hook_suffix ) {
        // Check if we are on the SchemaCraft settings page.
        // The hook_suffix for a top-level page is 'toplevel_page_MENU_SLUG'.
        if ( 'toplevel_page_schemacraft' !== $hook_suffix ) {
            return;
        }

        // SCHEMACRAFT_PLUGIN_URL and SCHEMACRAFT_VERSION are defined in SchemaCraft_Main
        // and should be available here if this method is called correctly through the instance.

        wp_enqueue_style(
            'schemacraft-admin-styles',
            SCHEMACRAFT_PLUGIN_URL . 'admin/css/schemacraft-admin.css',
            array(),
            SCHEMACRAFT_VERSION
        );

        wp_enqueue_script(
            'schemacraft-admin-scripts',
            SCHEMACRAFT_PLUGIN_URL . 'admin/js/schemacraft-admin.js',
            array( 'jquery', 'wp-media' ),
            SCHEMACRAFT_VERSION,
            true // Load in footer
        );
    }

    public function display_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'SchemaCraft Settings', 'schemacraft' ); ?></h1>
            <?php settings_errors(); // Display settings errors/update messages ?>

            <h2 class="nav-tab-wrapper">
                <a href="?page=schemacraft&tab=general" class="nav-tab <?php echo $this->active_tab == 'general' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'General Settings', 'schemacraft' ); ?>
                </a>
                <a href="?page=schemacraft&tab=schema_types" class="nav-tab <?php echo $this->active_tab == 'schema_types' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Schema Types', 'schemacraft' ); ?>
                </a>
            </h2>

            <form action="options.php" method="post">
                <?php
                // Output nonce, action, and option_page fields for the active tab.
                // Settings API will handle the rest.
                if ( $this->active_tab == 'general' ) {
                    settings_fields( 'schemacraft_general_settings' ); // Group name for general settings
                    do_settings_sections( 'schemacraft_general_settings_section' ); // Page slug for general settings section
                } elseif ( $this->active_tab == 'schema_types' ) {
                    settings_fields( 'schemacraft_schema_types_settings' ); // Group name for schema types settings
                    do_settings_sections( 'schemacraft_schema_types_settings_section' ); // Page slug for schema types section
                }

                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
?>
