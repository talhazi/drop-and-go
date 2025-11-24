<?php
// <Internal Doc Start>
/*
*
* @description: 
* @tags: 
* @group: 
* @name: Sitewide Details in the Admin Bar
* @type: PHP
* @status: published
* @created_by: 
* @created_at: 
* @updated_at: 2024-07-01 08:56:36
* @is_valid: 
* @updated_by: 
* @priority: 10
* @run_at: all
* @load_as_file: 
* @condition: {"status":"no","run_if":"assertive","items":[[]]}
*/
?>
<?php if (!defined("ABSPATH")) { return;} // <Internal Doc End> ?>
<?php
// Hook into the 'admin_bar_menu' action to add a custom link to the Admin Bar
add_action('admin_bar_menu', 'add_link_to_admin_bar', 100);

function add_link_to_admin_bar($wp_admin_bar) {
    // Check if the current user has the 'manage_options' capability before adding the menu item
    if (!current_user_can('manage_options')) {
        return;
    }

    // Add a new link to the Admin Bar
    $args = array(
        'id'    => 'custom_sitewide_details', // Unique ID for the menu item
        'title' => 'Sitewide Details', // Title of the menu item
        'href'  => admin_url('admin.php?page=sitewid-details'), // URL the menu item links to
        'meta'  => array(
            'class' => 'sitewide-details', // Optional, additional classes for the menu item
            'title' => 'Manage Sitewide Details' // Optional, on-hover title attribute
        )
    );

    // Add the menu item to the Admin Bar
    $wp_admin_bar->add_node($args);
}
