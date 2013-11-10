<?php

add_action('admin_menu', 'name_directory_menu');
add_action('wp_ajax_name_directory_ajax_names', 'name_directory_names');


/**
 * Add a menu entry on options
 */
function name_directory_menu()
{
    add_options_page(__('Name Directory Options', 'name-directory'),
        __('Name Directory Plugin', 'name-directory'),
        'manage_options', 'name-directory', 'name_directory_options');
}


/**
 * Return yes or no based on a variable
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
 * This is a little router for the
 * name-directory plugin
 */
function name_directory_options()
{
    if (!current_user_can('manage_options'))
    {
        wp_die( __('You do not have sufficient permissions to access this page.', 'name-directory') );
    }

    global $wpdb;
    global $table_directory;

    $sub_page = $_GET['sub'];

    switch($sub_page)
    {
        case 'manage-directory':
            name_directory_names();
            break;
        case 'edit-directory':
            name_directory_edit();
            break;
        case 'new-directory':
            name_directory_edit('new');
            break;
        default:
            show_list();
            break;
    }

}


/**
 * Show the list of directories and all of the
 * links to manage the directories
 */
function show_list()
{
    global $wpdb;
    global $table_directory;
    global $table_directory_name;

    if(! empty($_GET['delete_dir']) && is_numeric($_GET['delete_dir']))
    {
        $name = $wpdb->get_var(sprintf("SELECT `name` FROM %s WHERE id=%d", $table_directory, $_GET['delete_dir']));
        $wpdb->delete($table_directory, array('id' => $_GET['delete_dir']), array('%d'));
        $wpdb->delete($table_directory_name, array('directory' => $_GET['delete_dir']), array('%d'));
        echo "<div class='updated'><p><strong>"
            . sprintf(__('Name directory %s and all entries deleted', 'name-directory'), "<i>" . $name . "</i>")
            . "</strong></p></div>";
    }

    $wp_file = $_SERVER['SCRIPT_NAME'];
    $wp_page = $_GET['page'];
    $wp_url_path = sprintf("%s?page=%s", $wp_file, $wp_page);
    $wp_new_url = sprintf("%s&sub=%s", $wp_url_path, 'new-directory');


    echo '<div class="wrap">';
    echo '<div id="icon-page" class="icon32 icon32-posts-post"><br></div>';
    echo "<h2>"
        . __('Name Directory management', 'name-directory')
        . " <a href='" . $wp_new_url . "' class='add-new-h2'>" . __('Add directory', 'name-directory') . "</a>"
        . "</h2>";

    if(! empty($_POST['mode']) && ! empty($_POST['dir_id']))
    {
        $wpdb->update(
            $table_directory,
            array(
                'name'                  => $_POST['name'],
                'description'           => $_POST['description'],
                'show_title'            => $_POST['show_title'],
                'show_description'      => $_POST['show_description'],
                'show_submit_form'      => $_POST['show_submit_form'],
                'show_submitter_name'   => $_POST['show_submitter_name'],
                'show_line_between_names' => $_POST['show_line_between_names'],
            ),
            array('id' => intval($_POST['dir_id']))
        );

        echo "<div class='updated'><p>"
            . sprintf(__('Directory %s updated.', 'name-directory'), "<i>" . esc_sql($_POST['name']) . "</i>")
            . "</p></div>";

        unset($_GET['dir_id']);
    }
    elseif($_POST['mode'] == "new")
    {
        $wpdb->insert(
            $table_directory,
            array(
                'name'                  => $_POST['name'],
                'description'           => $_POST['description'],
                'show_title'            => $_POST['show_title'],
                'show_description'      => $_POST['show_description'],
                'show_submit_form'      => $_POST['show_submit_form'],
                'show_submitter_name'   => $_POST['show_submitter_name'],
                'show_line_between_names' => $_POST['show_line_between_names'],
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        echo "<div class='updated'><p>"
            . sprintf(__('Directory %s created.', 'name-directory'), "<i>" . esc_sql($_POST['name']) . "</i>")
            . "</p></div>";
    }

    $directories = $wpdb->get_results("SELECT * FROM $table_directory");
    $num_directories = $wpdb->num_rows;
    $plural = ($num_directories==1)?__('name directory', 'name-directory'):__('name directories', 'name-directory');

    echo "<p>"
        . sprintf(__('You currently have %d %s.', 'name-directory'), $num_directories, $plural)
        . "</p>";
    ?>

    <table class="wp-list-table widefat fixed name-directory" cellspacing="0">
        <thead>
            <tr>
                <th width="1%" scope="col" class="manage-column column-cb check-column">&nbsp;</th>
                <th width="52%" scope="col" id="title" class="manage-column column-title sortable desc">
                    <span><?php echo __('Title', 'name-directory'); ?></span>
                </th>
                <th width="13%" scope="col"><?php echo __('Entries', 'name-directory'); ?></th>
                <th width="13%" scope="col"><?php echo __('Published', 'name-directory'); ?></th>
                <th width="13%" scope="col"><?php echo __('Unpublished', 'name-directory'); ?></th>
            </tr>
        </thead>

        <tfoot>
        <tr>
            <th width="1%" scope="col" class="manage-column column-cb check-column">&nbsp;</th>
            <th width="52%" scope="col" id="title" class="manage-column column-title sortable desc">
                <span><?php echo __('Title', 'name-directory'); ?></span>
            </th>
            <th width="13%" scope="col"><?php echo __('Entries', 'name-directory'); ?></th>
            <th width="13%" scope="col"><?php echo __('Published', 'name-directory'); ?></th>
            <th width="13%" scope="col"><?php echo __('Unpublished', 'name-directory'); ?></th>
        </tr>
        </tfoot>

        <tbody>
            <?php
            foreach ( $directories as $directory )
            {
                $entries = $wpdb->get_var(sprintf("SELECT COUNT(`id`) FROM %s WHERE directory=%d", $table_directory_name, $directory->id));
                $unpublished = $wpdb->get_var(sprintf("SELECT COUNT(`id`) FROM %s WHERE directory=%d AND `published` = 0", $table_directory_name, $directory->id));
                echo sprintf("
                <tr class='type-page status-publish hentry alternate iedit author-self' valign='top'>
                    <th scope='col'>&nbsp;</th>
                    <td class='post-title page-title column-title' style='padding-left: 0;'>
                        <strong><a class='row-title' href='" . $wp_url_path . "&sub=manage-directory&dir=%d' title='%s'>%s</a>
                        <span style='font-weight: normal;'>&nbsp;%s</span></strong>
                        <div class='locked-info'>&nbsp;</div>
                        <div class='row-actions'>
                               <span class='manage'><a href='" . $wp_url_path . "&sub=manage-directory&dir=%d' title='%s'>%s</a>
                             | </span><span class='view'><a href='" . $wp_url_path . "&sub=manage-directory&dir=%d#anchor_add_name' title='%s'>%s</a>
                             | </span><span class='inline hide-if-no-js'><a href='" . $wp_url_path . "&sub=edit-directory&dir=%d' class='editinline' title='%s'>%s</a>
                             | </span><span class='view'><a class='toggle-info' data-id='%s' href='" . $wp_url_path . "&sub=manage-directory&dir=%d#shortcode' title='%s'>%s</a></span>
                             | </span><span class='trash'><a class='submitdelete' href='" . $wp_url_path . "&delete_dir=%d' title=%s'>%s</a>
                        </div>
                    </td>
                    <td>
                        &nbsp; <strong title='%s'>%d</strong>
                        <br /><br />&nbsp;
                    </td>
                    <td>%d</td>
                    <td>%d</td>
                    </tr>",

                    $directory->id, $directory->name, $directory->name,
                    substr($directory->description, 0, 70),
                    $directory->id, __('Add, edit and remove names', 'name-directory'), __('Manage names', 'name-directory'),
                    $directory->id, __('Go to the add-name-form on the Manage page', 'name-directory'), __('Add name', 'name-directory'),
                    $directory->id, __('Edit name, description and appearance settings', 'name-directory'), __('Settings', 'name-directory'),
                    $directory->id, $directory->id, __('Show the copy-paste shortcode for this directory', 'name-directory'), __('Shortcode', 'name-directory'),
                    $directory->id, __('Permanently remove this name directory', 'name-directory'), __('Delete', 'name-directory'),

                    __('Number of names in this directory', 'name-directory'),
                    $entries,
                    ($entries - $unpublished),
                    $unpublished
                    );
                    echo sprintf("
                    <tr id='embed_code_%s' style='display: none;'>
                        <td>&nbsp;</td>
                        <td align='right'>%s</td>
                        <td colspan='5'>
                            <input value='[namedirectory dir=\"%s\"]' type='text' size='25' id='title'
                                style='text-align: center; padding: 8px 5px;' />
                        </td>
                    </tr>
                    <tr style='display: none;'><td colspan='7'>&nbsp;</td></tr>",
                    $directory->id,
                    __('To show your directory on your website, use the shortcode on the right.', 'name-directory') . '<br />' .
                    __('Copy the code and paste it in a post or in a page.', 'name-directory'),
                    $directory->id);
            }
            ?>
        </tbody>
    </table>

    <script type='text/javascript'>
        jQuery(document).ready(function()
        {
            jQuery('.toggle-info').on('click', function(event)
            {
                event.preventDefault();
                var toggle_id = jQuery(this).attr('data-id');
                jQuery('#embed_code_' + toggle_id).toggle();
                return false;
            });
        });
    </script>
<?php
}


/**
 * A double purpose function for editing a name-directory and
 * creating a new directory.
 * @param string $mode
 */
function name_directory_edit($mode = 'edit')
{
    if (!current_user_can('manage_options'))
    {
        wp_die( __('You do not have sufficient permissions to access this page.', 'name-directory') );
    }

    global $wpdb;
    global $table_directory;

    $wp_file = $_SERVER['SCRIPT_NAME'];
    $wp_page = $_GET['page'];
    $wp_sub  = $_GET['sub'];
    $overview_url = sprintf("%s?page=%s", $wp_file, $wp_page, $wp_sub);
    $directory_id = intval($_GET['dir']);
    $wp_url_path = sprintf("%s?page=%s", $wp_file, $wp_page);

    $directory = $wpdb->get_row("SELECT * FROM " . $table_directory . " WHERE `id` = " . $directory_id, ARRAY_A);

    echo '<div class="wrap">';
    echo '<div id="icon-page" class="icon32 icon32-posts-post"><br></div>';
    if($mode == "new")
    {
        $table_heading  = __('Create new name directory', 'name-directory');
        $button_text    = __('Create', 'name-directory');
        echo "<h2>" . __('Create new name directory', 'name-directory') . "</h2>";
        echo "<p>" . __('Complete the form below to create a new name directory.', 'name-directory');
    }
    else
    {
        $table_heading  = __('Edit this directory', 'name-directory');
        $button_text    = __('Save Changes', 'name-directory');
        echo "<h2>" . __('Edit name directory', 'name-directory') . "</h2>";
        echo "<p>"
            . sprintf(__('You are editing the name, description and settings of directory %s', 'name-directory'),
                $directory['name']);
    }
    echo " <a style='float: right;' href='" . $overview_url . "'>" . __('Back to the directory overview', 'name-directory') . "</a></p>";
    ?>

    <form name="add_name" method="post" action="<?php echo $wp_url_path; ?>">
        <table class="wp-list-table widefat" cellpadding="0">
            <thead>
            <tr>
                <th colspan="2">
                    <?php echo $table_heading; ?>
                    <input type="hidden" name="dir_id" value="<?php echo $directory_id; ?>">
                    <input type="hidden" name="mode" value="<?php echo $mode; ?>">
                </th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td width="18%"><?php echo __('Title', 'name-directory'); ?></td>
                <td width="82%"><input type="text" name="name" value="<?php echo $directory['name']; ?>" size="20" style="width: 100%;"></td>
            </tr>
            <tr>
                <td><?php echo __('Description', 'name-directory'); ?></td>
                <td><textarea name="description" rows="5" style="width: 100%;"><?php echo $directory['description']; ?></textarea></td>
            </tr>
            <tr>
                <td><?php echo __('Show title', 'name-directory'); ?></td>
                <td>
                    <label for="show_title_yes">
                        <input type="radio" name="show_title" id="show_title_yes" value="1" checked="checked" />
                        &nbsp;<?php echo __('Yes', 'name-directory') ?>
                    </label>

                    &nbsp; &nbsp;

                    <label for="show_title_no">
                        <input type="radio" name="show_title" id="show_title_no" value="0"
                        <?php
                        if(empty($directory['show_title']))
                        {
                            echo 'checked="checked"';
                        }?> />
                        &nbsp;<?php echo __('No', 'name-directory') ?>
                    </label>
                </td>
            </tr>
            <tr>
                <td><?php echo __('Show description', 'name-directory'); ?></td>
                <td>
                    <label for="show_description_yes">
                        <input type="radio" name="show_description" id="show_description_yes" value="1" checked="checked">
                        &nbsp;<?php echo __('Yes', 'name-directory') ?>
                    </label>

                    &nbsp; &nbsp;

                    <label for="show_description_no">
                        <input type="radio" name="show_description" id="show_description_no" value="0"
                            <?php
                            if(empty($directory['show_description']))
                            {
                                echo 'checked="checked"';
                            }?>>
                        &nbsp;<?php echo __('No', 'name-directory') ?>
                    </label>
                </td>
            </tr>
            <tr>
                <td><?php echo __('Submit form', 'name-directory'); ?></td>
                <td>
                    <label for="show_submit_form_yes">
                        <input type="radio" name="show_submit_form" id="show_submit_form_yes" value="1" checked="checked" />
                        &nbsp;<?php echo __('Yes', 'name-directory') ?>
                    </label>

                    &nbsp; &nbsp;

                    <label for="show_submit_form_no">
                        <input type="radio" name="show_submit_form" id="show_submit_form_no" value="0"
                        <?php
                        if(empty($directory['show_submit_form']))
                        {
                            echo 'checked="checked"';
                        }?> />
                        &nbsp;<?php echo __('No', 'name-directory') ?>
                    </label>
                </td>
            </tr>
            <tr>
                <td><?php echo __('Submitter name', 'name-directory'); ?></td>
                <td>
                    <label for="show_submitter_name_yes">
                        <input type="radio" name="show_submitter_name" id="show_submitter_name_yes" value="1" checked="checked" />
                        &nbsp;<?php echo __('Yes', 'name-directory') ?>
                    </label>

                    &nbsp; &nbsp;

                    <label for="show_submitter_name_no">
                        <input type="radio" name="show_submitter_name" id="show_submitter_name_no" value="0"
                        <?php
                        if(empty($directory['show_submitter_name']))
                        {
                            echo 'checked="checked"';
                        }?> />
                        &nbsp;<?php echo __('No', 'name-directory') ?>
                    </label>
                </td>
            </tr>
            <tr>
                <td><?php echo __('Show line between names', 'name-directory'); ?></td>
                <td>
                    <label for="show_line_between_names_yes">
                        <input type="radio" name="show_line_between_names" id="show_line_between_names_yes" value="1" checked="checked" />
                        &nbsp;<?php echo __('Yes', 'name-directory') ?>
                    </label>

                    &nbsp; &nbsp;

                    <label for="show_line_between_names_no">
                        <input type="radio" name="show_line_between_names" id="show_line_between_names_no" value="0"
                        <?php
                        if(empty($directory['show_line_between_names']))
                        {
                            echo 'checked="checked"';
                        }?> />
                        &nbsp;<?php echo __('No', 'name-directory') ?>
                    </label>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <input type="submit" name="submit" class="button button-primary button-large"
                           value="<?php echo $button_text; ?>" />

                    <a class='button button-large' href='<?php echo $overview_url; ?>'>
                        <?php echo __('Cancel', 'name-directory'); ?>
                    </a>
                </td>
            </tr>
            </tbody>
        </table>
    </form>

<?php

}


/**
 * Handle the names in the name directory
 *  - Display all names
 *  - Edit names (ajax and 'oldskool' view
 *  - Create new names
 */
function name_directory_names()
{
    if (!current_user_can('manage_options'))
    {
        wp_die( __('You do not have sufficient permissions to access this page.', 'name-directory') );
    }

    global $wpdb;
    global $table_directory;
    global $table_directory_name;

    if(! empty($_GET['delete_name']) && is_numeric($_GET['delete_name']))
    {
        $name = $wpdb->get_var(sprintf("SELECT `name` FROM %s WHERE id=%d", $table_directory_name, $_GET['delete_name']));
        $wpdb->delete($table_directory_name, array('id' => $_GET['delete_name']), array('%d'));
        echo "<div class='updated'><p>"
            . sprintf(__('Name %s deleted', 'name-directory'), "<i>" . $name . "</i>")
            . "</p></div>";
    }
    else if(! empty($_POST['name_id']))
    {
        $wpdb->update(
            $table_directory_name,
            array(
                'name'          => $_POST['name'],
                'letter'        => name_directory_get_first_char($_POST['name']),
                'description'   => $_POST['description'],
                'published'     => $_POST['published'],
                'submitted_by'  => $_POST['submitted_by'],
            ),
            array('id' => intval($_POST['name_id']))
        );

        if($_POST['action'] == "name_directory_ajax_names")
        {
            echo '<p>';
            echo sprintf(__('Name %s updated', 'name-directory'), "<i>" . esc_sql($_POST['name']) . "</i>");
            echo '</p>';
            exit;
        }

        echo "<div class='updated'><p>"
            . sprintf(__('Name %s updated', 'name-directory'), "<i>" . esc_sql($_POST['name']) . "</i>")
            . "</p></div>";

        unset($_GET['edit_name']);
    }
    else if(! empty($_POST['name']))
    {
        $wpdb->get_results(sprintf("SELECT `id` FROM `%s` WHERE `name` = '%s'",
                           $table_directory_name, esc_sql($_POST['name'])));
        if($wpdb->num_rows == 1 && $_POST['action'] == "name_directory_ajax_names")
        {
            echo '<p>';
            echo sprintf(__('Name %s was already on the list, so it was not added', 'name-directory'),
                                '<i>' . esc_sql($_POST['name']) . '</i>');
            echo '</p>';
            exit;
        }

        $wpdb->insert(
            $table_directory_name,
            array(
                'directory'     => $_POST['directory'],
                'name'          => $_POST['name'],
                'letter'        => name_directory_get_first_char($_POST['name']),
                'description'   => $_POST['description'],
                'published'     => $_POST['published'],
                'submitted_by'  => $_POST['submitted_by'],
            ),
            array('%d', '%s', '%s', '%s', '%d', '%s')
        );

        if($_POST['action'] == "name_directory_ajax_names")
        {
            echo '<p>';
            echo sprintf(__('New name %s added', 'name-directory'), '<i>' . esc_sql($_POST['name']) . '.</i> ');
            echo ' <small><i>' . __('Will be visible when the page is refreshed.', 'name-directory') . '</i></small>';
            echo '</p>';
            exit;
        }

        echo "<div class='updated'><p><strong>"
            . sprintf(__('New name %s added', 'name-directory'), "<i>" . esc_sql($_POST['name']) . "</i> ")
            . "</strong></p></div>";
    }
    else if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        if($_POST['action'] == "name_directory_ajax_names")
        {
            echo '<p>' . __('Please fill in at least a name', 'name-directory') . '</p>';
            exit;
        }

        echo "<div class='updated'><p><strong>"
            . __('Please fill in at least a name', 'name-directory')
            . "</strong></p></div>";
    }

    $directory_id = intval($_GET['dir']);

    $wp_file = $_SERVER['SCRIPT_NAME'];
    $wp_page = $_GET['page'];
    $wp_sub  = $_GET['sub'];
    $overview_url = sprintf("%s?page=%s", $wp_file, $wp_page);
    $wp_url_path = sprintf("%s?page=%s&sub=%s&dir=%d", $wp_file, $wp_page, $wp_sub, $directory_id);

    $published_status = '0,1';
    $empasis_class = 's_all';
    if($_GET['status'] == 'published')
    {
        $published_status = '1';
        $empasis_class = 's_published';
    }
    else if($_GET['status'] == 'unpublished')
    {
        $published_status = '0';
        $empasis_class = 's_unpublished';
    }

    $directory = $wpdb->get_row("SELECT * FROM " . $table_directory . " WHERE `id` = " . $directory_id, ARRAY_A);
    $names = $wpdb->get_results(sprintf("SELECT * FROM %s WHERE `directory` = %d AND `published` IN (%s) ORDER BY `name` ASC",
        $table_directory_name, $directory_id, $published_status));

    echo '<div class="wrap">';
    echo '<div id="icon-page" class="icon32 icon32-posts-post"><br></div>';
    echo "<h2>" . sprintf(__('Manage names for %s', 'name-directory'), $directory['name']) . "</h2>";
    ?>

    <p>
        View:
        <a class='s_all' href='<?php echo $wp_url_path; ?>&status=all'><?php _e('all', 'name-directory'); ?></a> |
        <a class='s_published' href='<?php echo $wp_url_path; ?>&status=published'><?php _e('published', 'name-directory'); ?></a> |
        <a class='s_unpublished' href='<?php echo $wp_url_path; ?>&status=unpublished'><?php _e('unpublished', 'name-directory'); ?></a>

        <a style='float: right'; href='<?php echo $overview_url; ?>'>
            <?php _e('Back to the directory overview', 'name-directory'); ?>
        </a>
    </p>

    <table class="wp-list-table widefat fixed" cellpadding="0">
        <thead>
        <tr>
            <th width="16%"><?php echo __('Name', 'name-directory'); ?></th>
            <th width="46%"><?php echo __('Description', 'name-directory'); ?></th>
            <th width="13%"><?php echo __('Submitted by', 'name-directory'); ?></th>
            <th width="10%"><?php echo __('Published', 'name-directory'); ?></th>
            <th width="15%"><?php echo __('Manage', 'name-directory'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        if(empty($names))
        {
            echo sprintf("<tr class='empty-directory'><td colspan='5'>%s</td></tr>",
                __('Currently, there are no names in this directory..', 'name-directory'));
        }
        foreach($names as $name)
        {
            $published = __('No', 'name-directory');
            if($name->published == 1)
            {
                $published = __('Yes', 'name-directory');
            }

            echo sprintf("
                <tr>
                    <td>%s</td><td>%s</td><td>%s</td><td>%s</td>
                    <td><a class='button button-primary button-small' href='" . $wp_url_path . "&edit_name=%d#anchor_add_form'>%s</a>
                        <a class='button button-small' href='" . $wp_url_path . "&delete_name=%d'>%s</a>
                    </td>
                </tr>",
                $name->name, $name->description, $name->submitted_by, $published,
                $name->id, __('Edit', 'name-directory'),
                $name->id, __('Delete', 'name-directory'));
        }
        ?>
        </tbody>
    </table>

    <p>&nbsp;</p>

    <?php
    if(! empty($_GET['edit_name']))
    {
        $name = $wpdb->get_row(sprintf("SELECT * FROM `%s` WHERE `id` = %d",
            $table_directory_name, $_GET['edit_name']), ARRAY_A);
        $table_heading = __('Edit a name', 'name-directory');
        $save_button_txt = __('Save name', 'name-directory');
    }
    else
    {
        $table_heading = __('Add a new name', 'name-directory');
        $save_button_txt = __('Add name', 'name-directory');
        $name = array();
    }

    echo "<p><a style='float: right;' href='" . $overview_url . "'>"
        . __('Back to the directory overview', 'name-directory') . "</a><br /></p>";
    ?>

    <div class="updated hidden" id="add_result"></div>

    <a name="anchor_add_form"></a>
    <form name="add_name" id="add_name_ajax" method="post" action="<?php echo $wp_url_path; ?>">
    <table class="wp-list-table widefat" cellpadding="0">
        <thead>
            <tr>
                <th width="16%"><?php echo $table_heading; ?>
                    <input type="hidden" name="directory" value="<?php echo $directory_id; ?>">
                    <?php
                    if($_GET['edit_name'])
                    {
                        echo '<input type="hidden" name="name_id" id="edit_name_id" value="' . intval($_GET['edit_name']) . '">';
                    }
                    ?>
                    <input type="hidden" name="action" value="0" id="add_form_ajax_submit" />
                </th>
                <th align="right">

                    <label id="input_compact" title="<?php echo __('Show the compact form, showing only the name, always published)', 'name-directory'); ?>">
                        <input type="radio" name="input_mode" />
                        <?php echo __('Quick add view', 'name-directory'); ?>
                    </label>
                    <label id="input_extensive" title="<?php echo __('Show the full form, which allows you to enter a description and submitter', 'name-directory'); ?>">
                        <input type="radio" name="input_mode" />
                        <?php echo __('Full add view', 'name-directory'); ?>
                    </label>

                </th>
            </tr>
        </thead>
        <tbody>
            <tr id="add_name">
                <td width="16%"><?php echo __('Name', 'name-directory'); ?></td>
                <td width="84%"><input type="text" name="name" value="<?php echo $name['name']; ?>" size="20" style="width: 100%;"></td>
            </tr>
            <tr id="add_description">
                <td><?php echo __('Description', 'name-directory'); ?></td>
                <td><textarea name="description" rows="5" style="width: 100%;"><?php echo $name['description']; ?></textarea></td>
            </tr>
            <tr id="add_published">
                <td><?php echo __('Published', 'name-directory'); ?></td>
                <td>
                    <input type="radio" name="published" id="published_yes" value="1" checked="checked">
                    <label for="published_yes"><?php echo __('Yes', 'name-directory') ?></label>

                    <input type="radio" name="published" id="published_no" value="0"
                        <?php
                        if(isset($name['published']) && empty($name['published']))
                        {
                            echo 'checked="checked"';
                        }?>>
                    <label for="published_no"><?php echo __('No', 'name-directory') ?></label>
                </td>
            </tr>
            <tr id="add_submitter">
                <td><?php echo __('Submitted by', 'name-directory'); ?></td>
                <td><input type="text" name="submitted_by" value="<?php echo $name['submitted_by']; ?>" size="20" style="width: 100%;"></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <input type="submit" id="add_button" name="Submit" class="button button-primary button-large"
                           value="<?php echo $save_button_txt; ?>" />
                </td>
            </tr>
        </tbody>
    </table>
    </form>

    <?php
    echo print_javascript($empasis_class);
}

/**
 * Return the Javascripts needed by this plugin
 * @return string
 */
function print_javascript($empasis_class = '')
{
    $js = '

    <script type="text/javascript">
        /**
         * Save a named preference to a cookie
         */
        function savePreference(name, value)
        {
            var expires = "";
            document.cookie = name+"="+value+expires+"; path=/";
        }

        /**
         * Read the named preference from cookie
         */
        function readPreference(name)
        {
            var nameEQ = name + "=";
            var ca = document.cookie.split(";");
            for(var i=0;i < ca.length;i++) {
                var c = ca[i];
                while (c.charAt(0)==" ") c = c.substring(1,c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
            }
            return null;
        }

        jQuery(document).ready(function()
        {
            jQuery("#input_compact").on("click", function(e)
            {
                jQuery("#published_yes").attr("checked", "checked");
                jQuery("#add_description, #add_published, #add_submitter").hide();
                savePreference("wp-plugin-nd-add_form", "compact");
            });

            jQuery("#input_extensive").on("click", function(e)
            {
                jQuery("#add_description, #add_published, #add_submitter").show();
                savePreference("wp-plugin-nd-add_form", "extensive");
            });

            var pref = readPreference("wp-plugin-nd-add_form");
            if(pref != null)
            {
                jQuery("#input_" + pref).trigger("click");
                if(! window.location.hash)
                {
                    jQuery("html, body").animate({scrollTop:0}, 1);
                }
            }

            jQuery("#add_form_ajax_submit").val("name_directory_ajax_names");

            jQuery("#add_name_ajax").on("submit", function(e)
            {
                var form_data = jQuery(this).serialize();

                e.preventDefault();

                jQuery("#add_button").attr("disabled", "disabled");

                jQuery.ajax({
                    url: "admin-ajax.php",
                    type: "POST",
                    data: form_data,
                    success: function(data)
                    {
                        jQuery("#add_result").slideDown().html(data);
                        jQuery("#add_name_ajax input[type=text], #add_name_ajax textarea, #edit_name_id").val("");
                    },
                    error: function(data)
                    {
                        window.location.reload();
                    },
                    complete: function(data)
                    {
                        jQuery("#add_button").removeAttr("disabled");
                    }
                });

                return false;
            });
        });
    </script>';

    if(! empty($empasis_class))
    {
        $js .= "<script>jQuery('." . $empasis_class . "').css('font-weight', 'bold');</script>";
    }

    if(! empty($_GET['edit_name']))
    {
        $js .= "<script>jQuery(document).ready(function(){
                    jQuery('#input_extensive').trigger('click');
                });</script>";
    }

    return $js;
}