<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             1.0.0
 * @package           Riothere_All_In_One
 *
 * @wordpress-plugin
 * Plugin Name:       Riothere All In One
 * Plugin URI:        https://riothere.com
 * Description:       This is the Backend of our Reactjs/Nextjs Frontend eCommerce template. You can find the template and experiment with it on [Riothere](https://riothere.com/ "Customizable eCommerce Template")
 * Version:           1.0.0
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       riothere-all-in-one
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
define('RIOTHERE_ALL_IN_ONE_VERSION', '1.0.4');
define('RIOTHERE_ALL_IN_ONE_VERSION_DIR_PATH', __FILE__);

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-riothere-all-in-one-activator.php
 */
function activate_riothere_all_in_one()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-riothere-all-in-one-activator.php';
    Riothere_All_In_One_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-riothere-all-in-one-deactivator.php
 */
function deactivate_riothere_all_in_one()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-riothere-all-in-one-deactivator.php';
    Riothere_All_In_One_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_riothere_all_in_one');
register_deactivation_hook(__FILE__, 'deactivate_riothere_all_in_one');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-riothere-all-in-one.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_riothere_all_in_one()
{

    $plugin = new Riothere_All_In_One();
    $plugin->run();

}
run_riothere_all_in_one();

// Helps us sanitize arrays everywhere in the code
function riothere_sanitize_array($val)
{
    if (!$val || !is_array($val)) {
        return null;
    }

    foreach ($val as $key => $value) {
        $val[$key] = sanitize_text_field($value);
    }

    return $val;
}
