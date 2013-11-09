<?php

/**
 * Get the directory with the supplied ID
 * @param $id
 * @return mixed
 */
function get_directory_properties($id)
{
    global $wpdb;
    global $table_directory;

    $directory = $wpdb->get_row(sprintf("SELECT * FROM %s WHERE `id` = %d",
        esc_sql($table_directory),
        esc_sql($id)), ARRAY_A);

    return $directory;
}

/**
 * Get the names of given directory, maybe only with the char?
 * @param $dir
 * @param $char
 * @return mixed
 */
function get_directory_names($dir, $char)
{
    global $wpdb;
    global $table_directory_name;
    $limit = "";

    if(! empty($char))
    {
        $limit = " AND `letter`='" . $char . "' ";
    }

    $names = $wpdb->get_results(sprintf("SELECT * FROM %s WHERE `directory` = %d %s ORDER BY `letter` ASC",
        esc_sql($table_directory_name),
        esc_sql($dir),
        $limit),
        ARRAY_A
    );

    return $names;
}

function name_directory_show_submit_form($directory)
{
    $name = __('Name', 'name-directory');
    $required = __('Required', 'name-directory');
    $description = __('Description', 'name-directory');
    $your_name = __('Your name', 'name-directory');
    $submit = __('Submit', 'name-directory');

    $form = <<<HTML
        <form method='post' name='name_directory_submit'>

            <div class='name_directory_forminput'>
                <label for='name_directory_name'>{$name} <small>{$required}</small></label>
                <br />
                <input id='name_directory_name' type='text' name='name_directory_name' />
            </div>

            <div class='name_directory_forminput'>
                <label for='name_directory_description'>{$description}</label>
                <br />
                <input id='name_directory_description' type='text' name='name_directory_description' />
            </div>

            <div class='name_directory_forminput'>
                <label for='name_directory_submitter'>{$your_name}</label>
                <br />
                <input id='name_directory_submitter' type='text' name='name_directory_submitter' />
            </div>

            <div class='name_directory_forminput'>
                <button type='submit'>{$submit}</button>
            </div>

        </form>
HTML;

    return $form;
}

/**
 * Construct a plugin URL
 * @param string $index
 * @return string
 */
function name_directory_make_plugin_url($index = 'name_directory_startswith')
{
    $parsed = parse_url($_SERVER['REQUEST_URI']);
    parse_str($parsed['query'], $url);
    unset($url[$index]);
    $url[$index] = '';

    return get_site_url() . '?' . http_build_query($url);
}

/**
 * Function that takes care of displaying.. stuff
 * @param $attributes
 * @return mixed
 */
function show_directory($attributes)
{
    extract(shortcode_atts(
        array('dir' => '1'),
        $attributes
    ));

    $character = null;
    if(! empty($_GET['name_directory_startswith']))
    {
        $character = $_GET['name_directory_startswith'];
    }

    $str_all = __('All', 'name-directory');

    $letter_url = name_directory_make_plugin_url('name_directory_startswith');
    $directory = get_directory_properties($dir);
    $names = get_directory_names($dir, $character);

    if($_GET['show_submitform'])
    {
        return name_directory_show_submit_form($dir);
    }

    ob_start();

    if(! empty($directory['show_title']))
    {
        echo "<h3>" . $directory['name'] . "</h3>";
    }

    echo <<<HTML
        <div class="name_directory_index">
            <a class="name_directory_startswith" href="{$letter_url}">{$str_all}</a> |
            <a class="name_directory_startswith" href="{$letter_url}#">#</a>
            <a class="name_directory_startswith" href="{$letter_url}A">A</a>
            <a class="name_directory_startswith" href="{$letter_url}B">B</a>
            <a class="name_directory_startswith" href="{$letter_url}C">C</a>
            <a class="name_directory_startswith" href="{$letter_url}D">D</a>
            <a class="name_directory_startswith" href="{$letter_url}E">E</a>
            <a class="name_directory_startswith" href="{$letter_url}F">F</a>
            <a class="name_directory_startswith" href="{$letter_url}G">G</a>
            <a class="name_directory_startswith" href="{$letter_url}H">H</a>
            <a class="name_directory_startswith" href="{$letter_url}I">I</a>
            <a class="name_directory_startswith" href="{$letter_url}J">J</a>
            <a class="name_directory_startswith" href="{$letter_url}K">K</a>
            <a class="name_directory_startswith" href="{$letter_url}L">L</a>
            <a class="name_directory_startswith" href="{$letter_url}M">M</a>
            <a class="name_directory_startswith" href="{$letter_url}N">N</a>
            <a class="name_directory_startswith" href="{$letter_url}O">O</a>
            <a class="name_directory_startswith" href="{$letter_url}P">P</a>
            <a class="name_directory_startswith" href="{$letter_url}Q">Q</a>
            <a class="name_directory_startswith" href="{$letter_url}R">R</a>
            <a class="name_directory_startswith" href="{$letter_url}S">S</a>
            <a class="name_directory_startswith" href="{$letter_url}T">T</a>
            <a class="name_directory_startswith" href="{$letter_url}U">U</a>
            <a class="name_directory_startswith" href="{$letter_url}V">V</a>
            <a class="name_directory_startswith" href="{$letter_url}W">W</a>
            <a class="name_directory_startswith" href="{$letter_url}X">X</a>
            <a class="name_directory_startswith" href="{$letter_url}Y">Y</a>
            <a class="name_directory_startswith" href="{$letter_url}Z">Z</a>
        </div>
HTML;

    echo '<div class="name_directory_total">';
    if(empty($character))
    {
        echo sprintf(__('There are currently %d names in this directory', 'name-directory'), count($names));
    }
    else
    {
        echo sprintf(__('There are %d names in this directory beginning with the letter %s.', 'name-directory'), count($names), $character);
    }
    echo  '</div>';

    $num_names = count($names);

    echo '<div class="name_directory_names">';
    if($num_names === 0)
    {
        echo '<p>' . __('There are no names in this directory at the moment', 'name-directory') . '</p>';
    }
    else
    {
        $i = 1;
        foreach($names as $entry)
        {
            echo '<div class="name_directory_name_box">';
            echo '<strong>' . htmlentities($entry['name']) . '</strong>';
            if(! empty($entry['description']))
            {
                echo '<br /><div>' . htmlentities($entry['description']) . '</div>';
            }
            echo '</div>';

            if(! empty($directory['show_line_between_names']) && $num_names != $i)
            {
                echo '<hr />';
            }
            $i++;
        }
    }
    echo '</div>';

    # TODO
    //if($submit_permitted){
    //echo "<a href='" . $letter_url . "&show_submitform=true'>" . __('Submit a name', 'name-directory') . "</a>";
    //}

	return ob_get_clean();
}

add_shortcode('namedirectory', 'show_directory');