<?php
/* Protection! */
if (! function_exists('add_action'))
{
    echo 'Nothing to See here. Move along now people.';
    exit;
}

/**
 * Delta the Directory table
 */
function name_directory_install_list()
{
    global $wpdb;
    global $table_directory;
    global $name_directory_db_version;

    $installed_ver = get_option( "jal_db_version" );

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    $sql = "CREATE TABLE $table_directory (
                id INT( 11 ) NOT NULL AUTO_INCREMENT,
                name VARCHAR( 255 ) NOT NULL,
                show_title BOOLEAN NULL,
                show_description BOOLEAN NULL DEFAULT 1,
                show_submit_form BOOLEAN NULL,
                show_submitter_name BOOLEAN NULL,
                show_line_between_names BOOLEAN NULL,
                show_search_form BOOLEAN NULL,
                show_all_names_on_index BOOLEAN NULL DEFAULT 1,
                show_all_index_letters BOOLEAN NULL DEFAULT 1,
                nr_columns INT( 1 ) NULL,
                nr_most_recent INT(5) NULL DEFAULT 0,
                description TEXT NOT NULL,
                UNIQUE KEY id (id),
                PRIMARY KEY (id)
    );";

    dbDelta($sql);

    if($installed_ver != $name_directory_db_version)
    {
        $sql = "CREATE TABLE $table_directory (
                id INT( 11 ) NOT NULL AUTO_INCREMENT,
                name VARCHAR( 255 ) NOT NULL,
                show_title BOOLEAN NULL,
                show_description BOOLEAN NULL DEFAULT 1,
                show_submit_form BOOLEAN NULL,
                show_submitter_name BOOLEAN NULL,
                show_line_between_names BOOLEAN NULL,
                show_search_form BOOLEAN NULL,
                show_all_names_on_index BOOLEAN NULL DEFAULT 1,
                show_all_index_letters BOOLEAN NULL DEFAULT 1,
                nr_columns INT( 1 ) NULL,
                nr_most_recent INT(5) NULL DEFAULT 0,
                description TEXT NOT NULL,
                UNIQUE KEY id (id),
                PRIMARY KEY (id)
        );";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        $convert_dirs = "UPDATE $table_directory SET `show_all_names_on_index`=1 WHERE `show_all_names_on_index` IS NULL;";
        $wpdb->query($convert_dirs);

        update_option("name_directory_db_version", $name_directory_db_version);
    }
}

/**
 * Delta the Directory Names table
 */
function name_directory_install_names()
{
    global $table_directory_name;
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    $sqlnames = "CREATE TABLE $table_directory_name (
                id INT( 11 ) NOT NULL AUTO_INCREMENT,
                directory INT( 11 ) NOT NULL ,
                name VARCHAR( 255 ) NOT NULL ,
                letter VARCHAR( 1 ) NOT NULL ,
                description TEXT NOT NULL ,
                published BOOL NOT NULL ,
                submitted_by VARCHAR( 255 ) NOT NULL,
                UNIQUE KEY id (id),
                PRIMARY KEY (id)
    );";
    dbDelta($sqlnames);
}


/**
 * Convert tables to UTF8
 */
function name_directory_convert_to_utf8()
{
    global $wpdb;
    global $table_directory;
    global $table_directory_name;

    $convert_dirs = "ALTER TABLE $table_directory CONVERT TO CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci';";
    $wpdb->query($convert_dirs);

    $convert_names = "ALTER TABLE $table_directory_name CONVERT TO CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci';";
    $wpdb->query($convert_names);
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
            'id'                        => 1,
            'name'                      => 'Bird names',
            'show_title'                => 1,
            'show_description'          => 1,
            'show_submit_form'          => 1,
            'show_submitter_name'       => 0,
            'show_line_between_names'   => 1,
            'show_line_between_names'   => 1,
            'show_search_form'          => 1,
            'show_all_names_on_index'   => 1,
            'nr_most_recent'            => 0,
            'description'               => 'Cool budgie names'
        ));
        $wpdb->insert($table_directory_name, array(
            'directory'     => 1,
            'name'          => 'Navi',
            'letter'        => 'N',
            'description'   => 'Navi is a good aviator and navigator. A very strong and big budgie, almost English',
            'published'     => 1
        ));
        $wpdb->insert($table_directory_name, array(
            'directory'     => 1,
            'name'          => 'Mister',
            'letter'        => 'M',
            'description'   => 'Mister is a name which can only be assigned to a typical English Budgie. Big, strong and stringent.',
            'published'     => 1
        ));
    }
}
