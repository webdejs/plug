<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

class SchemaCraft_Metabox {

    /**
     * Constructor.
     * @since 0.2.0
     */
    public function __construct() {
        // Hook for registering meta fields
        add_action( 'init', array( $this, 'register_meta_fields' ), 20 );
    }

    /**
     * Registers the SchemaCraft Options meta box on eligible post types.
     * @since 0.2.0
     */
    public function register_meta_box() {
        $post_types = get_post_types( array( 'public' => true, 'show_ui' => true ), 'objects' );
        if ( empty( $post_types ) ) return;
        $excluded_post_types = array( 'attachment' );

        foreach ( $post_types as $post_type ) {
            if ( in_array( $post_type->name, $excluded_post_types, true ) || !post_type_supports( $post_type->name, 'editor' ) ) {
                continue;
            }
            add_meta_box(
                'schemacraft_options_metabox',
                __( 'SchemaCraft Options', 'schemacraft' ),
                array( $this, 'render_meta_box_content' ),
                $post_type->name, 'advanced', 'high'
            );
        }
    }

    /**
     * Renders the content of the SchemaCraft Options meta box.
     * @since 0.2.0
     * @param WP_Post $post The current post object.
     */
    public function render_meta_box_content( $post ) {
        wp_nonce_field( 'schemacraft_metabox_save_action', 'schemacraft_metabox_nonce' );
        echo '<div id="schemacraft-metabox-react-root"></div>';
    }

    /**
     * Registers post meta fields for SchemaCraft.
     * This method is hooked to 'init'.
     * @since 0.2.0
     */
    public function register_meta_fields() {
        $common_args = array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback' => function() {
                return current_user_can('edit_posts');
            }
        );

        register_post_meta( '', '_schemacraft_selected_schema_type', $common_args );
        register_post_meta( '', '_schemacraft_course_name', $common_args );
        register_post_meta( '', '_schemacraft_course_provider', $common_args );
    }

    /**
     * Enqueues scripts and styles for the SchemaCraft metabox.
     * @since 0.2.0
     * @param string $hook_suffix The current admin page hook.
     */
    public function enqueue_metabox_assets( $hook_suffix ) {
        global $post;

        if ( !in_array( $hook_suffix, array( 'post.php', 'post-new.php' ) ) ) return;

        $current_screen = get_current_screen();
        $current_post_type = $current_screen ? $current_screen->post_type : '';

        if ( empty($current_post_type) && isset($_GET['post_type']) ) {
            $current_post_type = sanitize_key($_GET['post_type']);
        }
        if ( 'post-new.php' === $hook_suffix && empty($current_post_type) ) {
            $current_post_type = 'post'; // Default to 'post' for new items if not specified
        }

        $eligible_post_types_args = array( 'public' => true, 'show_ui' => true );
        $all_eligible_post_types = get_post_types( $eligible_post_types_args, 'names' );
        $excluded_post_types = array( 'attachment' );

        $is_eligible = false;
        if (!empty($current_post_type) && in_array( $current_post_type, $all_eligible_post_types ) &&
            !in_array( $current_post_type, $excluded_post_types ) &&
            post_type_supports($current_post_type, 'editor') ) {
            $is_eligible = true;
        }
        if ( !$is_eligible ) return;

        $script_asset_path = SCHEMACRAFT_PLUGIN_DIR . 'admin/build/index.asset.php';
        if ( ! file_exists( $script_asset_path ) ) {
            if (defined('WP_DEBUG') && WP_DEBUG) error_log('SchemaCraft: React build assets not found in ' . $script_asset_path . '. Run npm run build.');
            return;
        }
        $script_asset = require( $script_asset_path );

        wp_enqueue_script(
            'schemacraft-metabox-app',
            SCHEMACRAFT_PLUGIN_URL . 'admin/build/index.js',
            $script_asset['dependencies'], $script_asset['version'], true );

        $style_path_rel = 'admin/build/index.css';
        if (file_exists(SCHEMACRAFT_PLUGIN_DIR . $style_path_rel)) {
             wp_enqueue_style(
                'schemacraft-metabox-styles',
                SCHEMACRAFT_PLUGIN_URL . $style_path_rel,
                array('wp-components'), $script_asset['version'] );
        }

        // Note: currentMeta was removed from here as per subtask instructions for React to load it.
        $localized_data = array(
            'postId'        => $post ? $post->ID : null,
            'nonce'         => wp_create_nonce( 'wp_rest' ),
            'restBase'      => esc_url_raw( rest_url() ),
            'metaNonce'     => wp_create_nonce('schemacraft_metabox_save_action'), // Matches nonce in render_meta_box_content
            'availableSchemas' => array( // This should ideally come from plugin settings
                array('value' => 'course', 'label' => __('Course', 'schemacraft')),
                array('value' => 'article', 'label' => __('Article', 'schemacraft')),
            ),
        );
        wp_localize_script( 'schemacraft-metabox-app', 'SchemaCraftData', $localized_data );
    }
}
?>
