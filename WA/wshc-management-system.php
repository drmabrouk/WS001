<?php
/**
 * Plugin Name: Management System
 * Plugin URI: https://gshcouncil.org
 * Description: The official professional management extension developed by the Global Council of Sport Health. This enterprise-level system provides advanced user control, real-time analytics, and secure administrative tools for the Global Council of Sport Health's digital ecosystem.
 * Version: 1.1.0
 * Author: Global Council of Sport Health
 * Author URI: https://gshcouncil.org
 * Text Domain: wshc-ms
 * License: GPL-2.0-or-later
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WSHC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WSHC_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once __DIR__ . '/vendor/autoload.php';

use WSHC\Core\Plugin;

function wshc_ms_run() {
    Plugin::get_instance();
}

register_activation_hook(__FILE__, [Plugin::class, 'activate']);
register_deactivation_hook(__FILE__, [Plugin::class, 'deactivate']);

add_action('plugins_loaded', 'wshc_ms_run');
