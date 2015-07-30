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

                $admin_email = get_option('admin_email');
                wp_mail($admin_email,
                    __('New submission for Name Directory', 'name-directory'),
                    __('Howdy,', 'name-directory') . "\n\n" .
                    sprintf(__('There was a new submission to the Name Directory on %s at %s', 'name-directory'), get_option('blogname'), get_option('home')) . "\n\n" .
                    sprintf("%s: %s", __('Name', 'name-directory'), $_POST['name_directory_name']) . "\n" .
                    sprintf("%s: %s", __('Description', 'name-directory'), $_POST['name_directory_description']) . "\n" .
                    sprintf("%s: %s", __('Submitted by', 'name-directory'), $_POST['name_directory_submitter']) . "\n\n" .
                    __('This new submission does not have the published status.', 'name-directory') . ' ' .
                    __('Please login to your WordPress admin to review and accept the submission.', 'name-directory') . "\n\n" .
                    sprintf("Link: %s/wp-admin/options-general.php?page=name-directory&sub=manage-directory&dir=%d&status=unpublished", get_option('home'), $directory) . "\n\n" .
                    sprintf("Your %s WordPress site", get_option('blogname')));
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
 * Function that takes care of displaying.. stuff
 * @param $attributes
 * @return mixed
 */
function show_directory($attributes)
{
    $dir = null;
    $show_all_link = '';
    $show_latest_link = '';
    extract(shortcode_atts(
        array('dir' => '1'),
        $attributes
    ));

    $name_filter = array();
    if(isset($_GET['name_directory_startswith']))
    {
        $name_filter['character'] = $_GET['name_directory_startswith'];
    }
    else if(! empty($attributes['start_with']) && empty($_GET['name-directory-search-value']))
    {
        $name_filter['character'] = $attributes['start_with'];
    }

    $str_all = __('All', 'name-directory');
    $str_latest = __('Latest', 'name-directory');
    $search_value = '';
    if(! empty($_GET['name-directory-search-value']))
    {
        $search_value = htmlspecialchars($_GET['name-directory-search-value']);
        $name_filter['containing'] = $search_value;
    }

    $letter_url = name_directory_make_plugin_url('name_directory_startswith', 'name-directory-search-value');
    $directory = get_directory_properties($dir);
    $names = get_directory_names($directory, $name_filter);
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

    if(! empty($directory['show_all_names_on_index']))
    {
        $show_all_link = '<a class="name_directory_startswith" href="' . $letter_url . '">' . $str_all . '</a> |';
    }

    if(! empty($directory['nr_most_recent']))
    {
        $show_latest_link = ' <a class="name_directory_startswith" href="' . $letter_url . 'latest">' . $str_latest . '</a> |';
    }

    /* Prepare and print the index-letters */
    echo '<div class="name_directory_index">';
    echo $show_all_link;
    echo $show_latest_link;

    $index_letters = range('A', 'Z');
    array_unshift($index_letters, '#');

    if(empty($directory['show_all_index_letters']))
    {
        $starting_letters = get_directory_start_characters($dir);
        $index_letters = array_intersect($index_letters, $starting_letters);
    }

    foreach($index_letters as $index_letter)
    {
        echo ' <a class="name_directory_startswith" href="' . $letter_url . urlencode($index_letter) . '">' . strtoupper($index_letter) . '</a> ';
    }

    if(! empty($directory['show_submit_form']))
    {
        echo " | <a href='" . $letter_url . "&show_submitform=true'>" . __('Submit a name', 'name-directory') . "</a>";
    }

    if(! empty($directory['show_search_form']))
    {
        $parsed_url = parse_url($_SERVER['REQUEST_URI']);
        parse_str($parsed_url['query'], $search_get_url);
        unset($search_get_url['name-directory-search-value']);

        echo "<br />";
        echo "<form method='get'>";
        foreach($search_get_url as $key_name=>$value)
        {
            if($key_name == 'name_directory_startswith')
            {
                continue;
            }
            echo "<input type='hidden' name='" . htmlspecialchars($key_name) . "' value='" . htmlspecialchars($value) . "' />";
        }
        echo "<input type='text' name='name-directory-search-value' id='name-directory-search-input-box' placeholder='" . __('Search for...', 'name-directory') . "' />";
        echo "<input type='submit' id='name-directory-search-input-button' value='" . __('Search', 'name-directory') . "' />";
        echo "</form>";
    }
    echo '</div>';

    echo '<div class="name_directory_total">';
    if(empty($name_filter['character']) && empty($search_value))
    {
        echo sprintf(__('There are currently %d names in this directory', 'name-directory'), $num_names);
    }
    else if(empty($name_filter['character']) && ! empty($search_value))
    {
        echo sprintf(__('There are %d names in this directory containing the search term %s.', 'name-directory'), $num_names, "<i>" . $search_value . "</i>");
        echo " <a href='" . get_permalink() . "'><small>" . __('Clear results', 'name-directory') . "</small></a>.<br />";
    }
    else if($name_filter['character'] == 'latest')
    {
        echo sprintf(__('Showing %d most recent names in this directory', 'name-directory'), $num_names);
    }
    else
    {
        echo sprintf(__('There are %d names in this directory beginning with the letter %s.', 'name-directory'), $num_names, $name_filter['character']);
    }
    echo  '</div>';

    echo '<div class="name_directory_names">';
    if($num_names === 0 && empty($search_value))
    {
        echo '<p>' . __('There are no names in this directory at the moment', 'name-directory') . '</p>';
    }
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
            echo '<a name="namedirectory_' . sanitize_html_class($entry['name']) . '"></a>';
            echo '<strong>' . htmlspecialchars($entry['name']) . '</strong>';
            if(! empty($directory['show_description']) && ! empty($entry['description']))
            {
                echo '<br /><div>' . html_entity_decode(stripslashes($entry['description'])) . '</div>';
            }
			if(! empty($directory['show_submitter_name']) && ! empty($entry['submitted_by']))
			{
				echo "<small>" . __('Submitted by:', 'name-directory') . " " . $entry['submitted_by'] . "</small>";
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
