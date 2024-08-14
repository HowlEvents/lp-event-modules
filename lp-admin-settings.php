<?php defined('ABSPATH') or die(); // Silence is golden
/**
 * Interface Lp_Input_Validator
 *
 * Interface for implementing validation for each option input field
 */
interface Lp_Input_Validator {

    /**
     * Constructor
     *
     * @param string $setting the settings slug title
     * @param array $args settings for validation
     */
    public function __construct($setting, $args = array());

    /**
     * Returns true if the setting inputted is a valid number
     *
     * @param mixed $input the input
     * @return bool true if the input is valid; otherwise false
     */
    public function is_valid($input);
}

/**
 * This class is responsible for validating numbers
 *
 * @implements Lp_Input_Validator
 */
class Lp_Number_Validator implements Lp_Input_Validator{

    /**
     * Slug title of relevant setting
     *
     * @access private
     */
    private $setting;
    private $min;
    private $max;

    /**
     * Constructor
     *
     * @param string $setting the settings slug title
     * @param array $args settings for validation
     */
    public function __construct($setting, $args = array()){
        $this->setting = $setting;
        $this->min = (isset($args['min'])) ? $args['min'] : 0;
        $this->max = (isset($args['max'])) ? $args['max'] : PHP_INT_MAX;
    }

    /**
     * Returns true if the setting inputted is a valid number
     *
     * @param string $input the input number
     * @return bool true if the input is valid; otherwise false
     */
    public function is_valid($input){
        if (!is_numeric($input)) {
            $this->add_error('invalid-number', 'You must provide a valid number.');
            return false;
        }

        $input = intval(round(doubleval($input)));

        if ($input < $this->min) {
            $this->add_error('number-out-of-bounds-positive', 'You must provide a number greater then ' . ($this->min - 1) . '.');
            return false;
        }

        if ($input > $this->max) {
            $this->add_error('number-out-of-bounds-negative', 'You must provide a number less or equal to ' .
                number_format($this->max) . '.');
            return false;
        }

        return true;

    }

    /**
     * Adds an error if the validation fails
     *
     * @access private
     * @param string $key a unique idetifier for the specific message
     * @param string $message the actual message
     */
    private function add_error($key, $message){
        add_settings_error(
            $this->setting,
            $key,
            __($message, LP_SLUG),
            'error'
        );

    }

}

class lp_settings {
    public function __construct() {
        add_action('admin_init', array($this, 'settings_init'));
        add_action('admin_menu', array($this, 'options_page'));
        add_filter('upload_mimes', array($this, 'add_svg_mime_type'));
        add_action('wp_enqueue_scripts', array($this, 'add_scripts_styles'));
        add_action('admin_enqueue_scripts', array($this, 'add_scripts_styles'));
    }

    /**
     * Callback that registers the settings and rendering callbacks used to drawing the settings.
     */
    public function settings_init() {
        register_setting(
            LP_SLUG,
            LP_SLUG . '_options',
            array(
                'sanitize_callback' => array($this, 'validate_config')
            )
        );

        add_settings_section(
            LP_SLUG . '_main_section',
            '',
            array($this, 'main_section_cb'),
            LP_SLUG
        );

        add_settings_field(
            LP_SLUG . '_field_vendor_module_enabled',
            __('Enable Vendor Module', LP_SLUG),
            array($this, 'field_checkbox'),
            LP_SLUG,
            LP_SLUG . '_main_section',
            [
                'label_for' => LP_SLUG . '_field_vendor_module_enabled',
                'class' => LP_SLUG . '_row',
                'description' => 'Enables the input and display of vendor information'
            ]
        );

        add_settings_field(
            LP_SLUG . '_field_vendor_map_svg_exception',
            __('Enable Vendor Map SVG Exception', LP_SLUG),
            array($this, 'field_checkbox'),
            LP_SLUG,
            LP_SLUG . '_main_section',
            [
                'label_for' => LP_SLUG . '_field_vendor_map_svg_exception',
                'class' => LP_SLUG . '_row',
                'description' => 'In order to get the interactive nature of this module, an SVG must be used for the ' .
                    'vendors map. SVGs are not allowed to be uploaded to WordPress for security reason. By checking ' .
                    'this box, SVGs will be added to the allowed mime types for anyone with the `manage_options` ' .
                    'capability.'
            ]
        );

        add_settings_field(
            LP_SLUG . '_field_vendor_map_id',
            __('Vendor Map ID', LP_SLUG),
            array($this, 'field_input'),
            LP_SLUG,
            LP_SLUG . '_main_section',
            [
                'type' => 'text',
                'label_for' => LP_SLUG . '_field_vendor_map_id',
                'class' => LP_SLUG . '_row',
                'placeholder' => '12345',
                'description' => 'Enter the attachment ID of the vendor map vector from the WordPress media library'
            ]
        );

        add_settings_field(
            LP_SLUG . '_field_event_module_enabled',
            __('Enable Event Module', LP_SLUG),
            array($this, 'field_checkbox'),
            LP_SLUG,
            LP_SLUG . '_main_section',
            [
                'label_for' => LP_SLUG . '_field_event_module_enabled',
                'class' => LP_SLUG . '_row',
                'description' => 'Enables the input and display of events'
            ]
        );

        add_settings_field(
            LP_SLUG . '_field_event_days',
            __('Event Days', LP_SLUG),
            array($this, 'field_textarea'),
            LP_SLUG,
            LP_SLUG . '_main_section',
            [
                'label_for' => LP_SLUG . '_field_event_days',
                'class' => LP_SLUG . '_row',
                'placeholder' => '2020-01-01
2020-01-02
2020-01-03...',
                'rows' => 5,
                'description' => 'Enter the days of the event, one per line. Use the format YYYY-MM-DD.'
            ]
        );

        add_settings_field(
            LP_SLUG . '_field_event_rooms',
            __('Event Rooms', LP_SLUG),
            array($this, 'field_textarea'),
            LP_SLUG,
            LP_SLUG . '_main_section',
            [
                'label_for' => LP_SLUG . '_field_event_rooms',
                'class' => LP_SLUG . '_row',
                'placeholder' => 'Main Hall
Boardroom A
Boardroom B...',
                'rows' => 5,
                'description' => 'Enter the rooms of the event, one per line, exactly as you want it to appear.'
            ]
        );
    }

    public function validate_config($input) {
        global $lp_options;
        $output = array();
        $valid = true;

//        if ((new Lp_Number_Validator(
//                LP_SLUG . '_field_sync_interval',
//                array(
//                    'min' => 1,
//                    'max' => 9999
//                )
//            ))->is_valid($input[LP_SLUG . '_field_number']) == false) $valid = false;

        $input[LP_SLUG . '_field_vendor_module_enabled'] = (isset($input[LP_SLUG . '_field_vendor_module_enabled']) &&
            $input[LP_SLUG . '_field_vendor_module_enabled'] == 1) ? '1' : '0';

        $input[LP_SLUG . '_field_vendor_map_svg_exception'] = (isset($input[LP_SLUG . '_field_vendor_map_svg_exception']) &&
            $input[LP_SLUG . '_field_vendor_map_svg_exception'] == 1) ? '1' : '0';

        // Make the inputted data tag safe
        foreach($input as $key => $value){
            if(isset($input[$key])){
                if(is_array($input[$key])){
                    $array_out = array();
                    foreach ($input[$key] as $item){
                        array_push($array_out, strip_tags(stripslashes($item)));
                    }
                    $output[$key] = $array_out;
                } else {
                    $output[$key] = strip_tags(stripslashes($input[$key]));
                }
            }
        }

        if(!$valid){
            add_settings_error(
                LP_SLUG . '_messages',
                LP_SLUG . '_messages_1',
                __('An error occurred. Please see below for details'),
                'error'
            );
            $output = $lp_options;
        } else {
            $output[LP_SLUG . '_field_vendor_map_id'] = trim($output[LP_SLUG . '_field_vendor_map_id']);
        }

        return apply_filters('lp_validate_config', $output, $input);
    }

    public function field_checkbox($args){
        global $lp_options;
        $errors = get_settings_errors($args['label_for']);
        if(!empty($errors)) {
            foreach($errors as $index => $error){
                ?>
                <p style="color:#ff0000;"><?php print_r($error['message'])?></p>
            <?php }
        } ?>
        <input id="<?php echo esc_attr($args['label_for']); ?>" type="checkbox"
               name="<?php echo LP_SLUG ?>_options[<?php echo esc_attr($args['label_for']); ?>]"
            <?php echo (isset($lp_options[$args['label_for']]) && $lp_options[$args['label_for']] == true) ? 'checked="checked"' : ''; ?> value="1" />
        <?php if(isset($args['description'])){?>
            <p class="description">
                <?php esc_html_e($args['description'], LP_SLUG); ?>
            </p>
            <?php
        }
    }

    public function field_input($args){
        global $lp_options;
        $errors = get_settings_errors($args['label_for']);
        if(!empty($errors)) {
            foreach($errors as $index => $error){
                ?>
                <p style="color:#ff0000;"><?php print_r($error['message'])?></p>
            <?php }
        } ?>
        <input id="<?php echo esc_attr($args['label_for']); ?>" type="<?php echo esc_attr($args['type']); ?>"
               placeholder="<?php echo esc_attr($args['placeholder']); ?>"
               name="<?php echo LP_SLUG ?>_options[<?php echo esc_attr($args['label_for']); ?>]"
               value="<?php echo (isset($lp_options[$args['label_for']])) ? esc_attr($lp_options[$args['label_for']]) : ''; ?>"
            <?php echo (isset($args['step'])) ? 'step="' . $args['step'] . '"' : '' ?>
            <?php echo (isset($args['min'])) ? 'min="' . $args['min'] . '"' : '' ?>
            <?php echo (isset($args['max'])) ? 'max="' . $args['max'] . '"' : '' ?>
            <?php echo (isset($args['required'])) ? 'required="' . $args['required'] . '"' : '' ?>  />
        <?php if(isset($args['description'])){?>
            <p class="description">
                <?php esc_html_e($args['description'], LP_SLUG); ?>
            </p>
            <?php
        }
    }

    public function field_textarea($args){
        global $lp_options;
        $errors = get_settings_errors($args['label_for']);
        if(!empty($errors)) {
            foreach($errors as $index => $error){
                ?>
                <p style="color:#ff0000;"><?php print_r($error['message'])?></p>
            <?php }
        } ?>
        <textarea id="<?php echo esc_attr($args['label_for']); ?>" rows="<?php echo esc_attr($args['rows']); ?>"
               placeholder="<?php echo esc_attr($args['placeholder']); ?>"
               name="<?php echo LP_SLUG ?>_options[<?php echo esc_attr($args['label_for']); ?>]"
            <?php echo (isset($args['required'])) ? 'required="' . $args['required'] . '"' : '' ?>><?php
            echo (isset($lp_options[$args['label_for']])) ? esc_attr($lp_options[$args['label_for']]) : '';
            ?></textarea>
        <?php if(isset($args['description'])){?>
            <p class="description">
                <?php esc_html_e($args['description'], LP_SLUG); ?>
            </p>
            <?php
        }
    }

    public function main_section_cb($args){
        ?>
        <h2><?php esc_html_e('General', LP_SLUG) ?></h2>
        <p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e('General Settings for the plugin.', LP_SLUG); ?></p>
        <?php
    }

    /**
     * WordPress Menu renderer callback that will add our section to the side bar.
     */
    public function options_page() {
        // add top level menu page
        add_menu_page(
            'LinuxPony\'s Event Modules',
            'LP Event Modules',
            'manage_options',
            LP_SLUG . '_settings',
            array($this, 'options_page_html')
        );
    }

    /**
     * Renders the settings page. Will render a warning instead if user does not have the 'manage_options' permission.
     */
    public function options_page_html() {
        // check user capabilities
        if (!current_user_can('manage_options')){
            echo "<h1>Current user cannot manage options.</h1>";
            return;
        }

        // add error/update messages
        // check if the user have submitted the settings
        // wordpress will add the "settings-updated" $_GET parameter to the url
        if (isset($_GET['settings-updated'])) {
            $has_error = false;
            foreach(get_settings_errors() as $index => $message) {
                if($message['type'] == "error" && strpos($message['setting'], LP_SLUG) !== false) {
                    $has_error = true;
                }
            }
            // add settings saved message with the class of "updated"
            if (!$has_error) {
                add_settings_error(
                    LP_SLUG . '_messages',
                    LP_SLUG . '_message',
                    __('Settings Saved', LP_SLUG),
                    'updated'
                );
            }
        }

        // show error/update messages
        require_once 'lp-admin-settings-view.php';
    }

    public function add_svg_mime_type($mimes) {
        global $lp_options;
        if(current_user_can('manage_options') &&
            $lp_options[LP_SLUG . '_field_vendor_map_svg_exception'] == '1'){
            $mimes['svg'] = 'image/svg+xml';
        }
        return $mimes;
    }

    public function add_scripts_styles() {
        wp_register_script('lp_scripts', plugins_url('js/lp_scripts.js', LP_PLUGIN_FILE), array('jquery'),LP_VER, true);
        wp_enqueue_script('lp_scripts');
        wp_register_style('lp_styles', plugins_url('lp_style.css', LP_PLUGIN_FILE), null, LP_VER);
        wp_enqueue_style('lp_styles');
    }
}