<?php
/**
 * @package lp-event-modules
 * @version 1.2.3
 */
/*
Plugin Name: LinuxPony's Event Modules
Description: A collection of components used to display various event information such as vendors, events schedules and more.
Version: 1.2.3
Text Domain: lp-event-modules
Author: LinuxPony
Author URI: https://sailextech.me/
License: GPLv2.1
*/

/*
Copyright (C) 2022 Elias Turner (email : sonic.ert@gmail.com)
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

defined('ABSPATH') or die(); // Silence is golden

define('LP_VER', '1.2.3');
define('LP_SLUG', 'lp-event-modules');
define('LP_PLUGIN_FILE', __FILE__);

$lp_options = get_option(LP_SLUG . '_options');

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

/* Load the vendors module */
require_once 'lp-vendors.php';
new lp_vendors();

/* Load the vendors module */
require_once 'lp-events.php';
new lp_events();

/* Load the setting module */
require_once 'lp-admin-settings.php';
new lp_settings();