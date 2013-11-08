<?php
/* Protection! */
if (! function_exists('add_action'))
{
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

/**
 * Delta the Directory table
 */
function name_directory_install_list()
{
    global $table_directory;
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    $sql = "CREATE TABLE $table_directory (
                id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                name VARCHAR( 255 ) NOT NULL,
                show_title BOOLEAN NULL,
                show_description BOOLEAN NULL,
                show_submit_form BOOLEAN NULL,
                show_submitter_name BOOLEAN NULL,
                show_line_between_names BOOLEAN NULL,
                description TEXT NOT NULL,
                UNIQUE KEY id (id)
    );";
    dbDelta($sql);
}

/**
 * Delta the Directory Names table
 */
function name_directory_install_names()
{
    global $table_directory_name;
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    $sqlnames = "CREATE TABLE $table_directory_name (
                id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                directory INT( 11 ) NOT NULL ,
                name VARCHAR( 255 ) NOT NULL ,
                letter VARCHAR( 1 ) NOT NULL ,
                description TEXT NOT NULL ,
                published BOOL NOT NULL ,
                submitted_by VARCHAR( 255 ) NOT NULL,
                UNIQUE KEY id (id)
    );";
    dbDelta($sqlnames);
}

/**
 * Install some sample data, if applies
 */
function name_directory_install_data()
{
    global $wpdb;
    global $table_directory;
    global $table_directory_name;

    // Only insert sample data when there is no data
    $wpdb->query(sprintf("SELECT * FROM " . $table_directory));
    if($wpdb->num_rows === 0)
    {
        $wpdb->insert($table_directory, array(
            'id'                => 1,
            'name'              => 'Bird names',
            'show_title'        => 1,
            'show_description'  => 1,
            'show_submit_form'  => 1,
            'show_submitter_name' => 0,
            'description'       => 'Cool budgie names'
        ));
        $wpdb->insert($table_directory_name, array(
            'directory'         => 1,
            'name'              => 'Navi',
            'letter'            => 'N',
            'description'       => 'Navi is a good aviator and navigator. A very strong and big budgie, almost English',
            'published'         => 1
        ));
        $wpdb->insert($table_directory_name, array(
            'directory'         => 1,
            'name'              => 'Mister',
            'letter'            => 'M',
            'description'       => 'Mister is a name which can only be assigned to a typical English Budgie. Big, strong and stringent.',
            'published'         => 1
        ));
    }
}