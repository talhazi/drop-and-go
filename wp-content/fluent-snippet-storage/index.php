<?php
if (!defined("ABSPATH")) {return;}
/*
 * This is an auto-generated file by Fluent Snippets plugin.
 * Please do not edit manually.
 */
return array (
  'published' => 
  array (
    '1-solution-to-hide-the.php' => 
    array (
      'name' => 'Hide Accessibility on Bricks Builder',
      'description' => '',
      'type' => 'PHP',
      'status' => 'published',
      'tags' => '',
      'created_at' => '',
      'updated_at' => '2024-06-09 11:19:19',
      'run_at' => 'all',
      'priority' => 10,
      'group' => '',
      'condition' => 
      array (
        'status' => 'yes',
        'run_if' => 'assertive',
        'items' => 
        array (
          0 => 
          array (
            0 => 
            array (
              'source' => 
              array (
                0 => 'user',
                1 => 'authenticated',
              ),
              'operator' => '=',
              'value' => 'yes',
            ),
          ),
        ),
      ),
      'load_as_file' => '',
      'file_name' => '1-solution-to-hide-the.php',
    ),
    '2-sitewide-details-css.php' => 
    array (
      'name' => 'Sitewide Details CSS',
      'description' => '',
      'type' => 'css',
      'status' => 'published',
      'tags' => '',
      'created_at' => '',
      'updated_at' => '2024-07-01 08:55:53',
      'run_at' => 'admin_head',
      'priority' => 10,
      'group' => '',
      'condition' => 
      array (
        'status' => 'no',
        'run_if' => 'assertive',
        'items' => 
        array (
          0 => 
          array (
          ),
        ),
      ),
      'load_as_file' => 'yes',
      'file_name' => '2-sitewide-details-css.php',
    ),
    '3-sitewide-details-in-the.php' => 
    array (
      'name' => 'Sitewide Details in the Admin Bar',
      'description' => '',
      'type' => 'PHP',
      'status' => 'published',
      'tags' => '',
      'created_at' => '',
      'updated_at' => '2024-07-01 08:56:36',
      'run_at' => 'all',
      'priority' => 10,
      'group' => '',
      'condition' => 
      array (
        'status' => 'no',
        'run_if' => 'assertive',
        'items' => 
        array (
          0 => 
          array (
          ),
        ),
      ),
      'load_as_file' => '',
      'file_name' => '3-sitewide-details-in-the.php',
    ),
    '4-sitewide-details-page.php' => 
    array (
      'name' => 'Sitewide Details Page',
      'description' => '',
      'type' => 'PHP',
      'status' => 'published',
      'tags' => '',
      'created_at' => '',
      'updated_at' => '2024-08-13 17:27:44',
      'run_at' => 'all',
      'priority' => 10,
      'group' => '',
      'condition' => 
      array (
        'status' => 'no',
        'run_if' => 'assertive',
        'items' => 
        array (
          0 => 
          array (
          ),
        ),
      ),
      'load_as_file' => '',
      'file_name' => '4-sitewide-details-page.php',
    ),
    '5-custom-css-backend.php' => 
    array (
      'name' => 'Custom CSS Backend',
      'description' => '',
      'type' => 'css',
      'status' => 'published',
      'tags' => '',
      'created_at' => '',
      'updated_at' => '2024-07-07 08:23:26',
      'run_at' => 'admin_head',
      'priority' => 10,
      'group' => '',
      'condition' => 
      array (
        'status' => 'no',
        'run_if' => 'assertive',
        'items' => 
        array (
          0 => 
          array (
          ),
        ),
      ),
      'load_as_file' => '',
      'file_name' => '5-custom-css-backend.php',
    ),
  ),
  'draft' => 
  array (
    '6-back-to-top.php' => 
    array (
      'name' => 'Back To Top',
      'description' => '',
      'type' => 'php_content',
      'status' => 'draft',
      'tags' => '',
      'created_at' => '',
      'updated_at' => '',
      'run_at' => 'wp_head',
      'priority' => 10,
      'group' => '',
      'condition' => 
      array (
        'status' => 'no',
        'run_if' => 'assertive',
        'items' => 
        array (
          0 => 
          array (
          ),
        ),
      ),
      'load_as_file' => '',
      'file_name' => '6-back-to-top.php',
    ),
    '7-accessibility-image-change.php' => 
    array (
      'name' => 'Accessibility Image Change',
      'description' => '',
      'type' => 'js',
      'status' => 'draft',
      'tags' => '',
      'created_at' => '',
      'updated_at' => '',
      'run_at' => 'wp_head',
      'priority' => 10,
      'group' => '',
      'condition' => 
      array (
        'status' => 'no',
        'run_if' => 'assertive',
        'items' => 
        array (
          0 => 
          array (
          ),
        ),
      ),
      'load_as_file' => '',
      'file_name' => '7-accessibility-image-change.php',
    ),
    '8-flash-effect.php' => 
    array (
      'name' => 'Flash Effect',
      'description' => '',
      'type' => 'css',
      'status' => 'draft',
      'tags' => '',
      'created_at' => '',
      'updated_at' => '',
      'run_at' => 'wp_head',
      'priority' => 10,
      'group' => '',
      'condition' => 
      array (
        'status' => 'no',
        'run_if' => 'assertive',
        'items' => 
        array (
          0 => 
          array (
          ),
        ),
      ),
      'load_as_file' => '',
      'file_name' => '8-flash-effect.php',
    ),
  ),
  'hooks' => 
  array (
    'all' => 
    array (
      0 => '1-solution-to-hide-the.php',
      1 => '3-sitewide-details-in-the.php',
      2 => '4-sitewide-details-page.php',
    ),
    'admin_head' => 
    array (
      0 => '2-sitewide-details-css.php',
      1 => '5-custom-css-backend.php',
    ),
  ),
  'meta' => 
  array (
    'secret_key' => '8d22e0b7df132952553eed547cfc48db',
    'force_disabled' => 'no',
    'cached_at' => '2024-08-13 17:27:44',
    'cached_version' => '10.34',
    'cashed_domain' => 'https://bbh.nexxenstudio.com',
    'legacy_status' => 'new',
    'auto_disable' => 'yes',
    'auto_publish' => 'no',
    'remove_on_uninstall' => 'no',
  ),
  'error_files' => 
  array (
  ),
);