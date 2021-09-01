<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.smartsites.com
 * @since             1.0.0
 * @package           Smartsites_Autocorner_Scrape
 *
 * @wordpress-plugin
 * Plugin Name:       SmartSites Autocorner Scrape
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            SmartSites
 * Author URI:        https://www.smartsites.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       smartsites-autocorner-scrape
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('SMARTSITES_AUTOCORNER_SCRAPE_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-smartsites-autocorner-scrape-activator.php
 */
function activate_smartsites_autocorner_scrape()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-smartsites-autocorner-scrape-activator.php';
	Smartsites_Autocorner_Scrape_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-smartsites-autocorner-scrape-deactivator.php
 */
function deactivate_smartsites_autocorner_scrape()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-smartsites-autocorner-scrape-deactivator.php';
	Smartsites_Autocorner_Scrape_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_smartsites_autocorner_scrape');
register_deactivation_hook(__FILE__, 'deactivate_smartsites_autocorner_scrape');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-smartsites-autocorner-scrape.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_smartsites_autocorner_scrape()
{

	$plugin = new Smartsites_Autocorner_Scrape();
	$plugin->run();
}
run_smartsites_autocorner_scrape();
