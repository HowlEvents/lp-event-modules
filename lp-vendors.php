<?php defined('ABSPATH') or die(); // Silence is golden

class lp_vendors {
    public function __construct(){

        add_action('init', array($this, 'create_posttype'));
        add_action('admin_init', array($this, 'add_post_meta_boxes'));
        add_action('save_post', array($this, 'save_post_meta_boxes'));
        add_filter('manage_vendor_posts_columns', array($this, 'custom_table_head'));
        add_action('manage_vendor_posts_custom_column', array($this, 'custom_table_content'), 10, 2);
        add_filter('manage_edit-vendor_sortable_columns', array($this, 'custom_table_sorting'));
        add_filter('request', array($this, 'custom_vendor_column_orderby'));

        add_shortcode('lp_vendors', array($this, 'lp_vendors_shortcode'));

        register_deactivation_hook(LP_PLUGIN_FILE, 'flush_rewrite_rules');
        register_activation_hook(LP_PLUGIN_FILE, array($this, 'flush_rewrites'));
    }

    // Our custom post type function
    public function create_posttype() {
        register_post_type( 'vendor',
            // CPT Options
            array(
                'labels' => array(
                    'name' => __( 'Vendors' ),
                    'singular_name' => __( 'Vendor' )
                ),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'vendor'),
                'show_in_rest' => true,
            )
        );
        // Set Custom Post Type options
        $args = array(
            'label'               => __('vendor', LP_SLUG),
            'description'         => __('Vendors information and table info', LP_SLUG),
            'labels'              => array(
                'name'                => _x('Vendors', 'Post Type General Name', LP_SLUG),
                'singular_name'       => _x('Vendor', 'Post Type Singular Name', LP_SLUG),
                'menu_name'           => __('Vendors', LP_SLUG),
                'parent_item_colon'   => __('Parent Vendor', LP_SLUG),
                'all_items'           => __('All Vendors', LP_SLUG),
                'view_item'           => __('View Vendor', LP_SLUG),
                'add_new_item'        => __('Add New Vendor', LP_SLUG),
                'add_new'             => __('Add New', LP_SLUG),
                'edit_item'           => __('Edit Vendor', LP_SLUG),
                'update_item'         => __('Update Vendor', LP_SLUG),
                'search_items'        => __('Search Vendor', LP_SLUG),
                'not_found'           => __('Not Found', LP_SLUG),
                'not_found_in_trash'  => __('Not found in Trash', LP_SLUG),
            ),
            // Features this CPT supports in Post Editor
            'supports'            => array('title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields'),
            // You can associate this CPT with a taxonomy or custom taxonomy.
            'taxonomies'          => array(),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'post',
            'show_in_rest'        => true,
        );

        // Registering your Custom Post Type
        register_post_type( 'vendor', $args );
    }

    public function flush_rewrites() {
        // call your CPT registration function here (it should also be hooked into 'init')
        $this::create_posttype();
        flush_rewrite_rules();
    }

    public function add_post_meta_boxes() {
        // see https://developer.wordpress.org/reference/functions/add_meta_box for a full explanation of each property
        add_meta_box(
            'post_metadata_vendor_info', // div id containing rendered fields
            'Vendor Info', // section heading displayed as text
            array($this, 'post_meta_box_vendor_info'), // callback function to render fields
            'vendor', // name of post type on which to render fields
            'normal', // location on the screen
            'high' // placement priority
        );
    }

    public function save_post_meta_boxes(){
        global $post;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (get_post_status($post->ID) === 'auto-draft') {
            return;
        }
        update_post_meta($post->ID, '_vendor_table_id', sanitize_text_field($_POST['_vendor_table_id']));
        update_post_meta($post->ID, '_vendor_table_title', sanitize_text_field($_POST['_vendor_table_title']));
    }

    public function post_meta_box_vendor_info(){
        global $post;
        $custom = get_post_custom($post->ID);
        $table_id = $custom['_vendor_table_id'][0] ?? '';
        $table_title = $custom['_vendor_table_title'][0] ?? '';
        echo <<<EOT
<label>
    Table ID<br>
    <input name="_vendor_table_id" type="text" required="required" value="$table_id">
</label>
<p>
    This corresponds to the CSS selector of the element representing the vendor's table in the map vector. For
    example if you want to highlight the table rectangle with the id <kbd>table-2</kbd> you would type
    <kbd>#table-2</kbd>
</p>
<hr />
<label>
    Table Display Name<br>
    <input name="_vendor_table_title" type="text" required="required" value="$table_title">
</label>
<p>
    This is the text you want to represent the vendor's table. Can be anything. Eg. <kbd>Table 2</kbd> or
    <kbd>Booth 5</kbd>
</p>
EOT;
    }

    public function custom_table_head($defaults) {
        $defaults['_vendor_table_id'] = 'Table ID';
        $defaults['_vendor_table_title'] = 'Table Display Name';
        $defaults['featured_image'] = 'Image';
        return $defaults;
    }

    function custom_table_content($column_name, $post_id) {
        if ($column_name == '_vendor_table_id') {
            $table_id = get_post_meta($post_id, '_vendor_table_id', true);
            echo $table_id;
        }
        if ($column_name == '_vendor_table_title') {
            $table_name = get_post_meta($post_id, '_vendor_table_title', true);
            echo $table_name;
        }
        if ($column_name == 'featured_image') {
            $featured_image = get_the_post_thumbnail($post_id, array(40, 40));
            echo $featured_image;
        }
    }

    function custom_table_sorting( $columns ) {
        $columns['_vendor_table_id'] = '_vendor_table_id';
        $columns['_vendor_table_title'] = '_vendor_table_title';
        return $columns;
    }

    function custom_vendor_column_orderby($vars) {
        if(isset($vars['orderby'])){
            if ('_vendor_table_id' == $vars['orderby']) {
                $vars = array_merge($vars, array(
                    'meta_key' => '_vendor_table_id',
                    'orderby' => 'meta_value'
                ));
            } else if ('_vendor_table_title' == $vars['orderby']) {
                $vars = array_merge($vars, array(
                    'meta_key' => '_vendor_table_title',
                    'orderby' => 'meta_value'
                ));
            }
        }

        return $vars;
    }

    public function lp_vendors_shortcode() {
        require_once 'lp-vendors-shorcode-view.php';
        return lp_vendors_shortcode_html();
    }
}