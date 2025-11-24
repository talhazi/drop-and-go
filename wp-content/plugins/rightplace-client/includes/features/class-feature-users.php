<?php
namespace Rightplace\Features;
class Users
{
  public function __construct()
  {
    add_filter('rightplace_action_filter/getUsers', array($this, 'get_users_for_hooks'));
    add_filter('rightplace_action_filter/getEditableUsers', array($this, 'get_editable_users'));
    add_filter('rightplace_action_filter/getAllRoles', array($this, 'get_all_roles'));
    add_filter('rightplace_action_filter/updateUser', array($this, 'update_user'));
  }

  public function update_user($params)
  {
    return self::update_user_data($params);
  }

  public function get_users_for_hooks($params)
  {
    return self::get_users($params);
  }

  public static function get_users($params)
  {
    $limit = $params['limit'] ?? -1;
    $offset = $params['offset'] ?? 0;
    $role = $params['role'] ?? ''; // Get role from params

    // Get users using WP_User_Query
    $args = array(
        'number' => $limit,
        'offset' => $offset,
        // Add role filter if provided
        'role' => !empty($role) ? $role : '', // If role is empty, it will fetch all users
    );

    $user_query = new \WP_User_Query($args);
    $users = $user_query->get_results();

    // Initialize result array
    $result_users = array();

    foreach ($users as $user) {
        // Add user data to result array
        $post_count = count_user_posts($user->ID); // Get the published post count for the user
        $capabilities = $user->allcaps; // Get all capabilities for the user

        // Prepare capabilities information
        $capabilities_info = array();
        foreach ($capabilities as $cap => $has_cap) {
            $capabilities_info[] = $cap . ': ' . ($has_cap ? 'Full access' : 'No access');
        }

        // Get available display name options for this user
        $display_name_options = self::get_display_name_options($user);
        
        $result_users[] = array(
            'ID' => $user->ID,
            'display_name' => $user->display_name,
            'user_email' => $user->user_email,
            'user_login' => $user->user_login,
            'user_url' => $user->user_url,
            'user_registered' => $user->user_registered,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'nickname' => $user->nickname,
            'roles' => $user->roles,
            'post_count' => $post_count, 
            'capabilities' => $capabilities_info,
            'display_name_options' => $display_name_options,
        );
    }

    return array(
        'success' => true,
        'users' => $result_users,
        'total_users' => $user_query->get_total(),
    );
  }

  // Function to get users with 'edit_posts' capability
public static function get_editable_users() {
  $args = array(
    'orderby' => 'display_name',
    'order'   => 'ASC',
);

// Get all users without filtering by role
$users = get_users($args);
$editable_users = array();

foreach ($users as $user) {
    // Check if the user has 'edit_posts' capability
    if (user_can($user->ID, 'edit_posts')) {
        $editable_users[] = array(
            'ID' => $user->ID,
            'display_name' => $user->display_name,
            'avatar' => get_avatar_url($user->ID),
        );
    }
}

return array(
    'success' => true,
    'users' => $editable_users, // Return only editable users
    'total_users' => count($editable_users), // Count of editable users
);
}

// Function to get all roles in WordPress
public static function get_all_roles() {
  global $wp_roles;
  $roles = $wp_roles->roles; // Get all roles
  $result_roles = array();

  foreach ($roles as $role => $details) {
      $result_roles[] = array(
          'role' => $role,
          'name' => $details['name'], // Display name of the role
      );
  }

  return array(
      'success' => true,
      'roles' => $result_roles, // Return all roles
      'total_roles' => count($result_roles), // Count of roles
  );
}

// Function to get available display name options for a user
public static function get_display_name_options($user) {
    $options = array();
    
    // Username
    if (!empty($user->user_login)) {
        $options[] = array(
            'value' => $user->user_login,
            'label' => $user->user_login
        );
    }
    
    // First name
    if (!empty($user->first_name)) {
        $options[] = array(
            'value' => $user->first_name,
            'label' => $user->first_name
        );
    }
    
    // Last name
    if (!empty($user->last_name)) {
        $options[] = array(
            'value' => $user->last_name,
            'label' => $user->last_name
        );
    }
    
    // First name + Last name
    if (!empty($user->first_name) && !empty($user->last_name)) {
        $options[] = array(
            'value' => $user->first_name . ' ' . $user->last_name,
            'label' => $user->first_name . ' ' . $user->last_name
        );
    }
    
    // Last name + First name
    if (!empty($user->first_name) && !empty($user->last_name)) {
        $options[] = array(
            'value' => $user->last_name . ' ' . $user->first_name,
            'label' => $user->last_name . ' ' . $user->first_name
        );
    }
    
    // Nickname
    if (!empty($user->nickname)) {
        $options[] = array(
            'value' => $user->nickname,
            'label' => $user->nickname
        );
    }
    
    // Display name (current)
    if (!empty($user->display_name)) {
        $options[] = array(
            'value' => $user->display_name,
            'label' => $user->display_name
        );
    }
    
    // Remove duplicates while preserving order
    $unique_options = array();
    $seen_values = array();
    
    foreach ($options as $option) {
        if (!in_array($option['value'], $seen_values)) {
            $unique_options[] = $option;
            $seen_values[] = $option['value'];
        }
    }
    
    return $unique_options;
}

// Function to update user data
public static function update_user_data($params) {
    // Validate required parameters
    if (!isset($params['id']) || empty($params['id'])) {
        return array(
            'success' => false,
            'message' => 'User ID is required'
        );
    }

    $user_id = intval($params['id']);
    
    // Check if user exists
    $user = get_user_by('ID', $user_id);
    if (!$user) {
        return array(
            'success' => false,
            'message' => 'User not found'
        );
    }

    // Check if current user has permission to edit this user
    if (!current_user_can('edit_user', $user_id)) {
        return array(
            'success' => false,
            'message' => 'You do not have permission to edit this user'
        );
    }

    // Prepare user data for update
    $user_data = array(
        'ID' => $user_id
    );

    // Update display_name if provided
    if (isset($params['display_name']) && !empty($params['display_name'])) {
        $user_data['display_name'] = sanitize_text_field($params['display_name']);
    }

    // Update first_name if provided
    if (isset($params['first_name'])) {
        $user_data['first_name'] = sanitize_text_field($params['first_name']);
    }

    // Update last_name if provided
    if (isset($params['last_name'])) {
        $user_data['last_name'] = sanitize_text_field($params['last_name']);
    }

    // Update nickname if provided
    if (isset($params['nickname'])) {
        $user_data['nickname'] = sanitize_text_field($params['nickname']);
    }

    // Update user_email if provided
    if (isset($params['user_email']) && !empty($params['user_email'])) {
        // Validate email format
        if (!is_email($params['user_email'])) {
            return array(
                'success' => false,
                'message' => 'Invalid email format'
            );
        }
        
        // Check if email is already in use by another user
        $existing_user = get_user_by('email', $params['user_email']);
        if ($existing_user && $existing_user->ID !== $user_id) {
            return array(
                'success' => false,
                'message' => 'Email is already in use by another user'
            );
        }
        
        $user_data['user_email'] = sanitize_email($params['user_email']);
    }

    // Update user roles if provided
    if (isset($params['roles']) && is_array($params['roles'])) {
        // Check if current user has permission to edit roles
        if (!current_user_can('promote_users')) {
            return array(
                'success' => false,
                'message' => 'You do not have permission to change user roles'
            );
        }

        // Validate roles
        $valid_roles = array_keys(wp_roles()->roles);
        $new_roles = array();
        
        foreach ($params['roles'] as $role) {
            if (in_array($role, $valid_roles)) {
                $new_roles[] = $role;
            }
        }

        // Ensure user has at least one role
        if (empty($new_roles)) {
            return array(
                'success' => false,
                'message' => 'User must have at least one role'
            );
        }

        // Update user roles
        $user_obj = new \WP_User($user_id);
        $user_obj->set_role(''); // Remove all roles first
        foreach ($new_roles as $role) {
            $user_obj->add_role($role);
        }
    }

    // Update user data
    $result = wp_update_user($user_data);

    if (is_wp_error($result)) {
        return array(
            'success' => false,
            'message' => $result->get_error_message()
        );
    }

    // Get updated user data
    $updated_user = get_user_by('ID', $user_id);
    $post_count = count_user_posts($user_id);
    $capabilities = $updated_user->allcaps;
    
    // Prepare capabilities information
    $capabilities_info = array();
    foreach ($capabilities as $cap => $has_cap) {
        $capabilities_info[] = $cap . ': ' . ($has_cap ? 'Full access' : 'No access');
    }

    // Get display name options for updated user
    $display_name_options = self::get_display_name_options($updated_user);

    return array(
        'success' => true,
        'message' => 'User updated successfully',
        'user' => array(
            'ID' => $updated_user->ID,
            'display_name' => $updated_user->display_name,
            'user_email' => $updated_user->user_email,
            'user_login' => $updated_user->user_login,
            'user_url' => $updated_user->user_url,
            'user_registered' => $updated_user->user_registered,
            'first_name' => $updated_user->first_name,
            'last_name' => $updated_user->last_name,
            'nickname' => $updated_user->nickname,
            'roles' => $updated_user->roles,
            'post_count' => $post_count,
            'capabilities' => $capabilities_info,
            'display_name_options' => $display_name_options,
        )
    );
}
}

new Users();
