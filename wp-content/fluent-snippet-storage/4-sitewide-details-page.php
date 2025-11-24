<?php
// <Internal Doc Start>
/*
*
* @description: 
* @tags: 
* @group: 
* @name: Sitewide Details Page
* @type: PHP
* @status: published
* @created_by: 
* @created_at: 
* @updated_at: 2024-08-13 17:27:44
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
// Hook to add a custom menu to the admin panel
add_action('admin_menu', 'my_custom_settings_page');

// Function to define the custom menu page
function my_custom_settings_page() {
    add_menu_page(
        'Sitewide Details', // Page Title
        'Sitewide Details', // Menu Title
        'manage_options', // Capability
        'sitewid-details', // Menu Slug
        'my_custom_settings_page_html' // Callback function for the page content
    );
}

// Callback function for rendering the settings page content
function my_custom_settings_page_html() {
    // Security check: Verify that the current user has the required capability
    if (!current_user_can('manage_options')) {
        return;
    }

    // Check if a delete request was submitted
    if (isset($_POST['action']) && $_POST['action'] == 'delete_custom_setting' && !empty($_POST['delete_field_name'])) {
        // Verify nonce for security
        if (check_admin_referer('custom_settings_action', 'custom_settings_nonce')) {
            $field_name = sanitize_text_field($_POST['delete_field_name']);
            delete_option($field_name);

            // Also remove from dynamic fields list if it exists there
            $dynamic_fields = get_option('dynamic_swd_fields', []);
            if (isset($dynamic_fields[$field_name])) {
                unset($dynamic_fields[$field_name]);
                update_option('dynamic_swd_fields', $dynamic_fields);
            }

            echo '<div id="message" class="updated fade"><p>Field deleted.</p></div>';
        }
    }

    // Check if the form was submitted to update or add fields
    if (isset($_POST['action']) && $_POST['action'] == 'update_custom_settings') {
        // Verify nonce for security
        if (check_admin_referer('custom_settings_action', 'custom_settings_nonce')) {
            // Process settings updates
            if (isset($_POST['settings']) && is_array($_POST['settings'])) {
                foreach ($_POST['settings'] as $key => $value) {
                    // Sanitize and update each setting
                    update_option($key, sanitize_text_field($value));
                }
                echo '<div id="message" class="updated fade"><p>Settings saved.</p></div>';
            }

            // Process new field addition
            if (!empty($_POST['new_field_name']) && isset($_POST['new_field_value'])) {
                // Convert the field name to lowercase
                $new_field_name = strtolower(sanitize_text_field($_POST['new_field_name']));
                $new_field_value = sanitize_text_field($_POST['new_field_value']);
                // Validate new field name to ensure it is a valid option name
                if (preg_match('/^[a-z0-9_\-]+$/', $new_field_name)) {
                    update_option($new_field_name, $new_field_value);
                    
                    // Optionally save the new field name in a list of dynamic fields
                    $dynamic_fields = get_option('dynamic_swd_fields', []);
                    if (!isset($dynamic_fields[$new_field_name])) {
                        $dynamic_fields[$new_field_name] = $new_field_name;
                        update_option('dynamic_swd_fields', $dynamic_fields);
                    }
                    echo '<div id="message" class="updated fade"><p>New field added.</p></div>';
                } else {
                    echo '<div id="message" class="error fade"><p>Invalid field name. Only lowercase letters, numbers, underscores, and hyphens are allowed.</p></div>';
                }
            }
        }
    }

    // Retrieve current settings
    $settings = swd();

    // Form HTML for settings and adding new fields, with the new intro text added
    echo '<div id="sitewide-details" class="wrap sitewide-details-wrap">
            <h1 class="sitewide-details-title">Sitewide Details</h1>
            <p class="sitewide-details-intro">To add new field data into the website, add the following <code>{echo:swd(\'field_name\')}</code> and the data will be shown.</p>
            <form method="post" action="" class="sitewide-details-form">';
    wp_nonce_field('custom_settings_action', 'custom_settings_nonce');
    echo '<input type="hidden" name="action" value="update_custom_settings">';

    echo '<div class="sitewide-details-grid">';
    foreach ($settings as $key => $value) {
        echo "<div class='sitewide-details-field'>
                <label for='settings[{$key}]' class='sitewide-details-label'>" . esc_html(ucfirst($key)) . "</label>
                <input type='text' name='settings[{$key}]' value='" . esc_attr($value) . "' class='sitewide-details-input'>
                <button type='submit' name='action' value='delete_custom_setting' class='button-secondary' onclick=\"if(!confirm('Are you sure you want to delete this field?')){return false;}\">Delete</button>
                <input type='hidden' name='delete_field_name' value='{$key}'>
              </div>";
    }
    echo '</div>'; // close grid

    // Section for adding a new field
    echo '<hr><h2 class="sitewide-details-subtitle">Add New Field</h2>
          <div class="add-new-field">
              <div class="sitewide-details-field">
                  <label for="new_field_name" class="sitewide-details-label">Field Name (lowercase):</label>
                  <input type="text" name="new_field_name" id="new_field_name" class="sitewide-details-input">
              </div>
              <div class="sitewide-details-field">
                  <label for="new_field_value" class="sitewide-details-label">Default Value:</label>
                  <input type="text" name="new_field_value" id="new_field_value" class="sitewide-details-input">
              </div>
              <input type="submit" value="Save Changes" class="button-primary sitewide-details-button">
          </div>';

    echo '</form></div>';
}

// Function to dynamically fetch settings from the WordPress database
function swd($field = '') {
    $default_fields = [
        'phone' => '+000-000-0000',
        'toll_free_phone' => '+000-000-0000',
        'email' => 'contact@bb.wpdesigns.fr',
        'address' => '1234 Street Name, City, Country',
        'facebook' => 'https://www.facebook.com/YourPage',
        'instagram' => 'https://www.instagram.com/YourProfile',
        'linkedin' => 'https://www.linkedin.com/in/YourProfile',
        'youtube' => 'https://www.youtube.com/channel/YourChannel',
        'google_maps' => 'https://maps.google.com/?q=YourLocation',
        'tiktok' => 'https://www.tiktok.com/@YourProfile',
        'twitter' => 'https://www.x.com/@YourProfile',
        'contact_form' => 'Submit',
        'submission_email' => 'test@example.com',
        'main_cta' => 'Contact Us',
        'main_cta_link' => 'https://48.wpdesigns.fr/contact/'
    ];

    $static_fields = [];
    
    // Initialize and fetch each option, or use the default value if it doesn't exist
    foreach ($default_fields as $key => $default_value) {
        $static_fields[$key] = get_option($key, $default_value);
    }

    $dynamic_fields = get_option('dynamic_swd_fields', []);
    foreach ($dynamic_fields as $field_name) {
        $static_fields[$field_name] = get_option($field_name, '');
    }

    // If a specific field is requested and exists, return that field's value
    if (!empty($field)) {
        return isset($static_fields[$field]) ? $static_fields[$field] : 'No data available!';
    }

    // If no specific field is requested, return all fields for admin display
    return $static_fields;
}



// Filter to allow specific functions to be called using the Bricks Builder echo tag
add_filter('bricks/code/echo_function_names', function($function_name) {
    return in_array($function_name, ['swd', 'get_site_domain'], true);
});
