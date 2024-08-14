<?php defined('ABSPATH') or die(); // Silence is golden

?>
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha256-KM512VNnjElC30ehFwehXjx1YCHPiQkOPmqnrWtpccM=" crossorigin="anonymous"></script>-->
<!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" integrity="sha256-rByPlHULObEjJ6XQxW/flG2r+22R5dKiAoef+aXWfik=" crossorigin="anonymous" />-->
<!--<style>-->
<!--</style>-->
<?php settings_errors(LP_SLUG . '_messages'); ?>
<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
<div class="wrap">
    <h1>Settings</h1>
    <form action="options.php" method="post">
        <?php
        settings_fields(LP_SLUG);
        do_settings_sections(LP_SLUG);
        submit_button('Save Settings');
//        global $lp_options;
//        echo '<h2>Debug</h2>';
//        echo '<pre style="border: 1px solid #000; padding: 1em">' .
//            print_r($lp_options, true) . '</pre>';
        ?>
    </form>
</div>