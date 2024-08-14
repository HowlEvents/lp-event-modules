<?php defined('ABSPATH') or die(); // Silence is golden

function lp_events_shortcode_html() {
    global $lp_options;

    if (!isset($lp_options[LP_SLUG . '_field_event_module_enabled']) ||
        $lp_options[LP_SLUG . '_field_event_module_enabled'] == 0)
        return '';

    return <<<EOT
<div class="events-wrapper">
    <div class="tabs">
        <div id="tab-fri" class="tab-btn btn-success tab selected">
            <small>4</small>
            Friday
        </div>
        <div id="tab-sat" class="tab-btn btn-success tab">
            <small>5</small>
            Saturday
        </div>
        <div id="tab-sun" class="tab-btn btn-success tab">
            <small>6</small>
            Sunday
        </div>
    </div>
    <div class="loading">
        <div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
        <div>Loading...</div>
    </div>
    <div style="overflow:auto;">
        <div id="cal-fri" class="calendar"></div>
        <div id="cal-sat" class="calendar" style="display:none"></div>
        <div id="cal-sun" class="calendar" style="display:none"></div>
    </div>
    <div style="clear:both"></div>
    <ul id="track-key"></ul>
    <div class="pdf-button-container"><a href class="button" onclick="renderSchedulePdf(EVENTS); return false;">Download as PDF</a></div>
</div>
EOT;
}

function lp_my_events_shortcode_html() {
    global $lp_options;

    if(!isset($lp_options[LP_SLUG . '_field_event_module_enabled']) ||
        $lp_options[LP_SLUG . '_field_event_module_enabled'] == 0)
        return '';

    return <<<EOT
<div class="events-wrapper">
    <div id="mysched" class="calendar" style="overflow:auto;"></div>
    <div class="loading">
        <div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
        <div>Loading...</div>
    </div>
    <div id="track-key-placeholder">Click on events on the left to add them to your custom schedule! Your schedule is saved per device and will be lost if you clear you cache/cookies.</div>
    <div class="pdf-button-container"><a href class="button" onclick="renderMySchedulePdf(myEvents); return false;">Download as PDF</a></div>
</div>
EOT;
}