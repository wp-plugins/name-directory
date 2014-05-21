<?php
add_action('wp_enqueue_scripts', 'name_directory_add_my_stylesheet');

/**
 * Add the CSS file to output
 */
function name_directory_add_my_stylesheet()
{
    wp_register_style('prefix-style', plugins_url('name_directory.css', __FILE__));
    wp_enqueue_style('prefix-style');
}

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
 * @param array $name_filter
 * @return mixed
 */
function get_directory_names($dir, $name_filter = array())
{
    global $wpdb;
    global $table_directory_name;
    $sql_filter = "";

    if(! empty($name_filter['character']))
    {
        $sql_filter = " AND `letter`='" . $name_filter['character'] . "' ";
    }
    else if(! empty($name_filter['containing']))
    {
        $sql_filter = " AND `name` LIKE '%" . $name_filter['containing'] . "%' ";
    }

    $names = $wpdb->get_results(sprintf("
		SELECT * 
		FROM %s 
		WHERE `directory` = %d AND `published` = 1
		%s 
		ORDER BY `letter`, `name` ASC",
        esc_sql($table_directory_name),
        esc_sql($dir),
        $sql_filter),
        ARRAY_A
    );

    return $names;
}


/**
 * Show and handle the submission form
 * @param $directory
 * @param $overview_url
 * @return string
 */
function name_directory_show_submit_form($directory, $overview_url)
{
    global $wpdb;
    global $table_directory_name;

    $name = __('Name', 'name-directory');
    $required = __('Required', 'name-directory');
    $description = __('Description', 'name-directory');
    $your_name = __('Your name', 'name-directory');
    $submit = __('Submit', 'name-directory');
    $back_txt = __('Back to name directory', 'name-directory');

    $result_class = '';
    $form_result = null;

    if(! empty($_POST['name_directory_submitted']))
    {
        $wpdb->get_results(
            sprintf("SELECT `id` FROM `%s` WHERE `name` = '%s'",
            $table_directory_name,
            esc_sql($_POST['name_directory_name']))
        );

        if($wpdb->num_rows == 1)
        {
            $result_class = 'form-result-error';
            $form_result = sprintf(__('Sorry, %s was already on the list so your submission was not sent.', 'name-directory'),
                '<i>' . esc_sql($_POST['name_directory_name']) . '</i>');
        }
        else
        {
            $db_success = $wpdb->insert(
                $table_directory_name,
                array(
                    'directory'     => intval($directory),
                    'name'          => esc_sql($_POST['name_directory_name']),
                    'letter'        => name_directory_get_first_char($_POST['name_directory_name']),
                    'description'   => esc_sql($_POST['name_directory_description']),
                    'published'     => 0,
                    'submitted_by'  => esc_sql($_POST['name_directory_submitter']),
                ),
                array('%d', '%s', '%s', '%s', '%d', '%s')
            );

            if(! empty($db_success))
            {
                $result_class = 'form-result-success';
                $form_result = __('Thank you for your submission! It will be reviewed shortly.', 'name-directory');
            }
            else
            {
                $result_class = 'form-result-error';
                $form_result = __('Something must have gone terribly wrong. Would you please try it again?', 'name-directory');
            }
        }
    }
    else if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $result_class = 'form-result-error';
        $form_result = __('Please fill in at least a name', 'name-directory');;
    }

    $form = <<<HTML
        <form method='post' name='name_directory_submit'>

            <div class='name-directory-form-result {$result_class}'>{$form_result}</div>

            <p><a href="{$overview_url}">{$back_txt}</a></p>

            <div class='name_directory_forminput'>
                <label for='name_directory_name'>{$name} <small>{$required}</small></label>
                <br />
                <input id='name_directory_name' type='text' name='name_directory_name' />
            </div>

            <div class='name_directory_forminput'>
                <label for='name_directory_description'>{$description}</label>
                <br />
                <textarea id='name_directory_description' name='name_directory_description'></textarea>
            </div>

            <div class='name_directory_forminput'>
                <label for='name_directory_submitter'>{$your_name}</label>
                <br />
                <input id='name_directory_submitter' type='text' name='name_directory_submitter' />
            </div>

            <div class='name_directory_forminput'>
                <input type='hidden' name='name_directory_submitted' value='1' />
                <br />
                <button type='submit'>{$submit}</button>
            </div>

        </form>
HTML;

    return $form;
}


/**
 * Construct a plugin URL
 * @param string $index
 * @param null $exclude
 * @return string
 */
function name_directory_make_plugin_url($index = 'name_directory_startswith', $exclude = null)
{
    $parsed = parse_url($_SERVER['REQUEST_URI']);
    parse_str($parsed['query'], $url);

    if(! empty($exclude))
    {
        unset($url[$exclude]);
    }

    unset($url[$index]);
    $url[$index] = '';

    return get_permalink() . '?' . http_build_query($url);
}


/**
 * Function that takes care of displaying.. stuff
 * @param $attributes
 * @return mixed
 */
function show_directory($attributes)
{
    $dir = null;
    $show_all_link = '';
    extract(shortcode_atts(
        array('dir' => '1'),
        $attributes
    ));

    $name_filter = array();
    if(! empty($_GET['name_directory_startswith']))
    {
        $name_filter['character'] = $_GET['name_directory_startswith'];
    }

    $str_all = __('All', 'name-directory');
    $search_value = '';
    if(! empty($_GET['name-directory-search-value']))
    {
        $search_value = htmlspecialchars($_GET['name-directory-search-value']);
        $name_filter['containing'] = $search_value;
    }

    $letter_url = name_directory_make_plugin_url('name_directory_startswith', 'name-directory-search-value');
    $directory = get_directory_properties($dir);
    $names = get_directory_names($dir, $name_filter);
    $num_names = count($names);

    if(isset($_GET['show_submitform']))
    {
        return name_directory_show_submit_form($dir, name_directory_make_plugin_url('name_directory_startswith','show_submitform'));
    }

    ob_start();

    if(! empty($directory['show_title']))
    {
        echo "<h3 class='name_directory_title'>" . $directory['name'] . "</h3>";
    }

    // At this moment we can't suffice with an empty() check because of empty values
    if(isset($directory['show_all_names_on_index']) && $directory['show_all_names_on_index'] != 1)
    {
        $show_all_link = '<a class="name_directory_startswith" href="' . $letter_url . '">' . $str_all . '</a> |';
    }

    echo <<<HTML
	<div class="name_directory_index">
            {$show_all_link}
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
HTML;

    if(! empty($directory['show_submit_form']))
    {
        echo " | <a href='" . $letter_url . "&show_submitform=true'>" . __('Submit a name', 'name-directory') . "</a>";
    }

    if(! empty($directory['show_search_form']))
    {
        echo "<br />";
        echo "<form method='get'>";
        echo "<input type='text' name='name-directory-search-value' id='name-directory-search-input-box' placeholder='" . __('Search for...', 'name-directory') . "' />";
        echo "<input type='submit' id='name-directory-search-input-button' value='" . __('Search', 'name-directory') . "' />";
        echo "</form>";
    }
    echo '</div>';

    echo '<div class="name_directory_total">';
    if(empty($character) && empty($search_value))
    {
        echo sprintf(__('There are currently %d names in this directory', 'name-directory'), $num_names);
    }
    else if(empty($character) && ! empty($search_value))
    {
        echo sprintf(__('There are %d names in this directory containing the searchterm %s.', 'name-directory'), $num_names, "<i>" . $search_value . "</i>");
        echo " <a href='" . get_permalink() . "'><small>" . __('Clear results', 'name-directory') . "</small></a>.<br />";
    }
    else
    {
        echo sprintf(__('There are %d names in this directory beginning with the letter %s.', 'name-directory'), $num_names, $character);
    }
    echo  '</div>';

    echo '<div class="name_directory_names">';
    if($num_names === 0 && empty($search_value))
    {
        echo '<p>' . __('There are no names in this directory at the moment', 'name-directory') . '</p>';
    }
    // TODO: Enable this if db's are migrated correctly: else if(empty($directory['show_all_names_on_index']) && empty($name_filter))
    else if(isset($directory['show_all_names_on_index']) && $directory['show_all_names_on_index'] != 1 && empty($name_filter))
    {
        echo '<p>' . __('Please select a letter from the index (above) to see entries', 'name-directory') . '</p>';
    }
    else
    {
        $split_at = null;
        if(! empty($directory['nr_columns']) && $directory['nr_columns'] > 1)
        {
            $split_at = round($num_names/$directory['nr_columns'])+1;
        }

        echo '<div class="name_directory_column name_directory_nr' . (int)$directory['nr_columns'] . '">';

        $i = 1;
        $split_i = 1;
        foreach($names as $entry)
        {
            echo '<div class="name_directory_name_box">';
            echo '<strong>' . htmlspecialchars($entry['name']) . '</strong>';
            if(! empty($entry['description']))
            {
                echo '<br /><div>' . html_entity_decode(stripslashes($entry['description'])) . '</div>';
            }
            echo '</div>';

            if(! empty($directory['show_line_between_names']) && $num_names != $i)
            {
                echo '<hr />';
            }

            $split_i++;
            $i++;

            if($split_at == $split_i)
            {
                echo '</div><div class="name_directory_column name_directory_nr' . (int)$directory['nr_columns'] . '">';
                $split_i = 0;
            }
        }
        echo '</div>';
    }
    echo '</div>';

    if(! empty($directory['nr_columns']) && $directory['nr_columns'] > 1)
    {
        echo '<div class="name_directory_column_clear"></div>';
    }

    if(! empty($directory['show_submit_form']))
    {
        echo "<br /><br />
              <a href='" . $letter_url . "&show_submitform=true' class='name_directory_submit_bottom_link'>" . __('Submit a name', 'name-directory') . "</a>";
    }

	return ob_get_clean();
}

add_shortcode('namedirectory', 'show_directory');
