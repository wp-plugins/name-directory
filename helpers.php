<?php
/**
 * This file is part of the NameDirectory plugin for WordPress
 */



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
 * Prepare an associative array to be used for the csv importer
 * @param array $row (csv-row)
 * @param int $published (optional)
 * @return array|bool
 */
function name_directory_prepared_import_row($row, $published=1)
{
    // Don't continue when there is no name to add (first column in csv-row)
    if(empty($row[0]))
    {
        return false;
    }

    $row_props = array('name', 'description', 'submitted_by');
    $prepared_row = array('published' => $published);
    foreach($row_props as $index=>$prop)
    {
        if(! empty($row[$index]))
        {
            $prepared_row[$prop] = $row[$index];
        }
    }

    return $prepared_row;
}


/**
 * Return localized yes or no based on a variable
 * @param $var
 * @return string|void
 */
function name_directory_yesno($var)
{
    if(! empty($var))
    {
        return __('Yes', 'name-directory');
    }

    return __('No', 'name-directory');
}


/**
 * Switches the published state of a name and returns the human readable value
 * @param (numeric) $name_id
 * @return string|void
 */
function name_directory_switch_name_published_status($name_id)
{
    global $wpdb;
    global $table_directory_name;

    $wpdb->query($wpdb->prepare("UPDATE `$table_directory_name` SET `published`=1 XOR `published` WHERE id=%d",
        intval($name_id)));
    sleep(0.1);

    return name_directory_yesno($wpdb->get_var(sprintf("SELECT `published` FROM `%s` WHERE id=%d",
        $table_directory_name, intval($name_id))));
}


/**
 * Check if a given name already exists in a Name Directory
 * @param $name
 * @param $dir
 * @return bool
 */
function name_directory_name_exists_in_directory($name, $dir)
{
    global $wpdb;
    global $table_directory_name;

    $wpdb->get_results(sprintf("SELECT 1 FROM `%s` WHERE `name` = '%s' AND `directory` = %d",
        $table_directory_name, esc_sql($name), intval($dir)));

    return (bool)$wpdb->num_rows;
}