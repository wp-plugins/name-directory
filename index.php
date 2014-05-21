<?php
/**
 * Plugin Name: Name Directory
 * Plugin URI: http://www.jeroen.in
 * Description: A Name Directory, i.e. for animal names. Visitors can add, search or just browse all names.
 * Version: 1.6.2
 * Author: Jeroen Peters
 * Author URI: http://www.jeroen.in
 * License: GPL2
 */
/*  Copyright 2013      Jeroen Peters (email : jeroenpeters1986@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

global $wpdb;

global $name_directory_db_version;
$name_directory_db_version = '1.7';

global $table_directory;
$table_directory = $wpdb->prefix . "name_directory";

global $table_directory_name;
$table_directory_name = $wpdb->prefix . "name_directory_name";


// Make sure we don't expose any info if called directly
if (! function_exists('add_action'))
{
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}


/**
 * Return the first character of a word,
 * or hashtag, may the word begin with a number
 * @param $name
 * @return string
 */
function name_directory_get_first_char($name)
{
    $first_char = strtoupper(substr($name, 0, 1));
    if(is_numeric($first_char))
    {
        $first_char = '#';
    }

    return $first_char;
}


/**
 * Only run the DB provisioning if an admin accesses this page
 */
if (is_admin())
{
    require_once dirname( __FILE__ ) . '/admin.php';

    register_activation_hook( __FILE__, 'run_db_provisioning');
}


/**
 * This takes care of setting up the database and
 * installing some sample data
 */
function run_db_provisioning()
{
    global $name_directory_db_version;
    require_once dirname( __FILE__ ) . '/install.php';

    name_directory_install_list();
    name_directory_install_names();
    name_directory_install_data();
    name_directory_convert_to_utf8();

    add_option("name_directory_db_version", $name_directory_db_version);
}


require_once dirname( __FILE__ ) . '/shortcode.php';


/**
 * Initialize the plugin
 * Ready.. set.. go!
 */
function name_directory_init()
{
    $plugin_dir = basename(dirname(__FILE__));
    load_plugin_textdomain('name-directory', false, $plugin_dir . '/lang/');
}
add_action('plugins_loaded', 'name_directory_init');


function myplugin_update_db_check()
{
    global $name_directory_db_version;
    if (get_site_option('name_directory_db_version') != $name_directory_db_version)
    {
        run_db_provisioning();
    }
}
add_action( 'plugins_loaded', 'myplugin_update_db_check' );
