<?php defined('ABSPATH') or die(); // Silence is golden

function lp_vendors_shortcode_html() {
    global $lp_options;

    if(!isset($lp_options[LP_SLUG . '_field_vendor_module_enabled']) ||
        $lp_options[LP_SLUG . '_field_vendor_module_enabled'] == 0)
        return '';

    if(!isset($lp_options[LP_SLUG . '_field_vendor_map_id']) ||
        get_attached_file($lp_options[LP_SLUG . '_field_vendor_map_id']) == false)
        return '<p style="color:#f00">Attachment ID for Vendors Map is not a valid attachment. Please check the settings and try again.</p>';

    if(get_post_mime_type($lp_options[LP_SLUG . '_field_vendor_map_id']) != 'image/svg+xml')
        return '<p style="color:#f00">The vendor map MUST be an SVG. Please check the settings and try again.</p>';

    $args = array(
        'post_type' => 'vendor',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    );
    $loop = new WP_Query( $args );

    $to_return = '';

    if($loop->have_posts()){
        $to_return .= '
    <div class="lp-vendor-block">
        <div class="lp-vendor-left-column">
            <div class="lp-vendor-list">';
        while ($loop->have_posts()) { $loop->the_post();
            $id = get_the_ID();
            $to_return .= '
                <div class="lp-vendor" role="button" data-map-element="' .
                (get_post_meta($id, '_vendor_table_id', true) ?? '') . '">';
            $img = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large', false);
            if($img != false) {
                $to_return .= '
                    <picture class="lp-vendor-image">
                        <img src="' . $img[0] . '" alt="' . get_the_title() . '">
                    </picture>';
            }
            $to_return .= '
                    <div class="lp-title"><h2 class="lp-title-flex">' . get_the_title() . '</h2></div>
                    <h3 class="lp-table-name" style="display: none;">' .
                get_post_meta($id, '_vendor_table_title', true) . '</h3>
                    <div class="lp-description" style="display: none;">' . get_the_content() . '</div>
                </div>';

        }
        $to_return .= '
            </div>
            <div class="lp-vendor-pick" style="display: none;">
                <p>There are more then one person at this table. Please select one.</p>
                <div class="lp-vendor-content"></div>
                <button class="lp-btn-back">
                    &leftarrow; Vendors List
                </button>
            </div>
            <div class="lp-vendor-item" style="display: none;">
                <div class="lp-vendor-content"></div>
                <button class="lp-btn-back">
                    &leftarrow; Vendors List
                </button>
            </div>
        </div>

        <div class="lp-vendor-right-column">';

        $svg_map = get_attached_file($lp_options[LP_SLUG . '_field_vendor_map_id']);
        if(file_exists($svg_map)) {
            $svg_content = file_get_contents($svg_map);
            $to_return .= implode("\n", array_slice(explode("\n", $svg_content),1));
        }
        $to_return .= '
        </div>
    </div>';
    }

//    $to_return .= '
//    <div class="lp-modal" id="lp-choice-modal" tabindex="-1" role="dialog" aria-labelledby="lp-choice-modal-label" aria-hidden="true" style="display: none">
//        <div class="lp-modal-dialog" role="document">
//            <div class="lp-modal-content">
//                <div class="lp-modal-header">
//                    <h5 class="lp-modal-title" id="lp-choice-modal-label">Select A Vendor</h5>
//                    <button type="button" class="lp-close" data-dismiss="modal" aria-label="Close">
//                        <span aria-hidden="true">&times;</span>
//<!--                            <span class="far fa-time fa-2x" aria-hidden="true"></span>-->
//                    </button>
//                </div>
//                <div class="modal-body text-center">
//                    There are <span class="count">2</span> vendors at this table:
//                    <div class="choices"></div>
//                </div>
//                <div class="modal-footer">
//                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
//                </div>
//            </div>
//        </div>
//    </div>';
    return $to_return;
}