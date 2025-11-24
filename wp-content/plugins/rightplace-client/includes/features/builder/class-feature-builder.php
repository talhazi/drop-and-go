<?php
namespace Rightplace\Features;
class Builder
{
  public function __construct()
  {
    // TODO: move it to an adapter
    add_filter('rightplace_action_filter/builder/getBuilderAssets', array($this, 'get_builder_assets'));
    add_filter('rightplace_action_filter/builder/getBuilderAssetTypes', array($this, 'get_builder_asset_types'));
    add_filter('rightplace_action_filter/builder/syncBuilderAsset', array($this, 'sync_builder_asset'));
    add_filter('rightplace_action_filter/builder/unsyncBuilderAsset', array($this, 'unsync_builder_asset'));
    add_filter('rightplace_action_filter/builder/getBricksTemplates', array($this, 'get_bricks_templates_json'));
    add_filter('rightplace_action_filter/builder/downloadBricksTemplateJson', array($this, 'download_bricks_template_json'));
    add_filter('rightplace_action_filter/builder/uploadBricksTemplates', array($this, 'upload_bricks_templates'));
    add_filter('rightplace_action_filter/builder/updateBricksTemplates', array($this, 'update_bricks_templates'));
    add_filter('rightplace_action_filter/builder/disconnectBricksTemplates', array($this, 'disconnect_bricks_templates'));
    add_filter('rightplace_action_filter/builder/duplicateBricksTemplate', array($this, 'duplicate_bricks_template'));
    add_filter('rightplace_action_filter/builder/deleteBricksTemplate', array($this, 'delete_bricks_template'));
  }

  // we are creating menu items for the builder assets
  public function get_builder_asset_types($params)
  {
    $builder_asset_types = array();
    $asset_sub_menu_per_key = array();
    $post_types = get_post_types(array('public' => true), 'objects');

    if ($this->is_bricks_active()) {
      $asset_sub_menu_per_key['bricks'] = array(
        'bricks_page' => array('id' => 'bricks_page', 'link' => 'bricks/bricks_page', 'title' => 'Bricks Pages'),
        'bricks_template' => array('id' => 'bricks_template', 'link' => 'bricks/bricks_template', 'title' => 'Bricks Templates'),
        // 'bricks_theme' => array('id' => 'bricks_theme', 'link' => 'bricks/bricks_theme', 'title' =>'Bricks Themes'),
      );
    }


    // WIRED-794 : Remove Bricks Themes and Advanced Themer 
    // if ($this->is_advanced_themer_active()) {
    //   if (!isset($asset_sub_menu_per_key['bricks'])) {  
    //     $asset_sub_menu_per_key['bricks'] = array();
    //   }
    //   $asset_sub_menu_per_key['bricks']['advanced_themer_settings'] = array(
    //     'id' => 'advanced_themer_settings', 'link' => 'bricks/advanced_themer_settings', 'title' =>'Advanced Themer Settings',
    //   );
    // }


    // Gather builder asset types
    // this is indepdent of which builder is active. For eg, if Bricks is not active,
    // we might still have a bricks_template post type.
    foreach ($post_types as $post_type) {
      $asset_types = $this->is_builder_asset($post_type->name);
      if ($asset_types) {
        $group = $asset_types['parent'];
        $name = $post_type->name;
        unset($asset_types['parent']);
        if (!isset($asset_sub_menu_per_key[$group][$name])) {
          if (!isset($asset_sub_menu_per_key[$group])) {
            $asset_sub_menu_per_key[$group] = array();
          }
          $asset_sub_menu_per_key[$group][$name] = $asset_types;
        }
      }
    }

    if (!empty($asset_sub_menu_per_key['bricks'])) {
      $builder_asset_types[] = array(
        'title' => 'Bricks Builder',
        'icon' => 'bricks',
        'id' => 'bricks',
        'submenu' => array_values($asset_sub_menu_per_key['bricks'])
      );
    }

    return $builder_asset_types;
  }

  public function is_builder_asset($post_type_name)
  {
    if (empty($post_type_name)) {
      return null;
    }

    switch ($post_type_name) {
      case 'bricks_template':
        return array('parent' => 'bricks', 'id' => 'bricks_template', 'link' => 'bricks/bricks_template', 'title' => 'Bricks Templates');
      default:
        return null;
    }
  }

  private function is_bricks_active()
  {
    return defined('BRICKS_VERSION') && function_exists('bricks_is_builder');
  }

  private function is_advanced_themer_active()
  {
    return class_exists('Advanced_Themer_Bricks\AT__Builder');
  }

  /**
   * Get builder_assets
   *
   * @since    1.0.0
   */
  public function get_builder_assets($params)
  {
    $post_type = $params['post_type'];
    $builder_assets = array();

    switch ($post_type) {
      case 'bricks_page':

        if (class_exists('\Bricks\Helpers')) {
          $result = Posts::get_posts(array('post_type' => 'page'));
          if (!empty($result['posts'])) {
            $test = [];
            $result['posts'] = array_filter($result['posts'], function ($post) use (&$test) {
              $test[] = [$post->ID, \Bricks\Helpers::get_editor_mode($post->ID)];
              return \Bricks\Helpers::get_editor_mode($post->ID) === 'bricks';
            });

            $result['total_posts'] = count($result['posts']);
          }

          $builder_assets = $this->get_bricks_templates($result);
        }

        break;
      case 'bricks_template':
        $result = Posts::get_posts($params);
        $builder_assets = $this->get_bricks_templates($result);
        break;
    }

    foreach ($builder_assets as $key => $builder_asset) {
      $builder_assets[$key]['uuid'] = get_post_meta($builder_asset['id'], RIGHTPLACE_ASSET_UUID, true);
      $builder_assets[$key]['rp_template_uuid'] = get_post_meta($builder_asset['id'], RIGHTPLACE_TEMPLATE_UUID, true);
    }

    return array(
      'assets' => $builder_assets,
      'total_posts' => $result['total_posts'],
      'last_batch' => $result['last_batch'],
    );
  }

  private function get_bricks_templates($result)
  {
    $builder_assets = array();

    $posts = $result['posts'];

    foreach ($posts as $post) {
      $wp_admin_edit_url = admin_url('post.php?post=' . $post->ID . '&action=edit');
      $preview_url = $this->get_preview_url($post);

      if (class_exists('\Bricks\Helpers')) {
        $editor_mode = \Bricks\Helpers::get_editor_mode($post->ID);
      } else {
        $editor_mode = '';
      }

      // Get template thumbnail
      $template_thumbnail = null;
      
      if (has_post_thumbnail($post->ID)) {
        $template_thumbnail = get_the_post_thumbnail_url($post->ID, 'bricks_medium');
      } elseif (class_exists('\Bricks\Database') && \Bricks\Database::get_setting('generateTemplateScreenshots')) {
        // Define the base directory and URL for the custom screenshots
        $wp_upload_dir = wp_upload_dir();
        $custom_dir    = $wp_upload_dir['basedir'] . '/' . BRICKS_TEMPLATE_SCREENSHOTS_DIR . '/';
        $custom_url    = $wp_upload_dir['baseurl'] . '/' . BRICKS_TEMPLATE_SCREENSHOTS_DIR . '/';

        // Get all files for this template ID
        $all_files = glob($custom_dir . "template-screenshot-{$post->ID}-*");

        $latest_file = null;
        $latest_time = 0;

        foreach ($all_files as $file) {
          // Check if the file is of a valid type
          $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
          if (in_array($extension, ['webp', 'png'])) {
            // Extract the timestamp from the filename
            if (preg_match('/-(\d+)\.' . $extension . '$/', $file, $matches)) {
              $file_time = intval($matches[1]);
              if ($file_time > $latest_time) {
                $latest_time = $file_time;
                $latest_file = $file;
              }
            }
          }
        }


        
        if ($latest_file) {
          $filename           = basename($latest_file);
          $template_thumbnail = $custom_url . $filename;
        } else {
          // Check if my template thumbnail exists locally in WP root 'template-screenshots' folder
          $template_thumbnail_path = ABSPATH . trailingslashit('template-screenshots') . $post->post_name . '.jpg';
          if (file_exists($template_thumbnail_path)) {
            $template_thumbnail = get_site_url(null, '/') . trailingslashit('template-screenshots') . $post->post_name . '.jpg';
          }
        }
      }

      $builder_assets[] = array(
        'id' => $post->ID,
        'title' => $post->post_title,
        'updatedAt' => $post->post_modified,
        'postEditUrl' => $wp_admin_edit_url,
        'builderEditUrl' => $preview_url . '?bricks=run',
        'previewUrl' => $preview_url,
        'editorMode' => $editor_mode,
        'type' => 'bricks-templates',
        'thumbnail' => $template_thumbnail,
      );
    }

    return $builder_assets;
  }

  public function sync_builder_asset($params)
  {
    $rightplace_asset_uuid = $params[RIGHTPLACE_ASSET_UUID];
    $post_id = $params['post_id'];
    $type = $params['type'];

    $post = get_post($post_id);

    if (!$post) {
      return new \WP_Error('post_not_found', 'Post not found');
    }

    $existing_uuid = get_post_meta($post_id, RIGHTPLACE_ASSET_UUID, true);

    if (empty($existing_uuid)) {
      update_post_meta($post_id, RIGHTPLACE_ASSET_UUID, $rightplace_asset_uuid);
    }

    $builder_assets = null;

    switch ($type) {
      case 'bricks-templates':
        $builder_assets = array(
          'bricks_template_content' => get_post_meta($post_id, '_bricks_page_content_2', true),
          'bricks_template_settings' => get_post_meta($post_id, '_bricks_template_settings', true),
        );
        break;
    }

    return array(
      'type' => $type,
      'post_id' => $post_id,
      'existing_uuid' => $existing_uuid,
      'builder_assets' => $builder_assets,
    );

  }

  public function unsync_builder_asset($params)
  {
    $post_id = $params['post_id'];

    delete_post_meta($post_id, RIGHTPLACE_ASSET_UUID);

    return array(
      'success' => true,
      'post_id' => $post_id,
    );
  }

  private function get_preview_url($post)
  {
    return get_permalink($post->ID);
  }


  public function get_bricks_templates_json($templates)
  {
    $result = [
      'success' => [],
      'failed' => [],
    ];

    foreach ($templates as $template) {
      $template_id = $template['id'];

      $bricks_template = \Bricks\Templates::get_template_by_id($template_id);

      if (!isset($bricks_template)) {

        $result['failed'][] = [
          'id' => $template_id,
          'reason' => 'Template not found',
        ];

        rp_dev_log("❌ template not found for template ID: $template_id");

        continue;
      }

      // $template_settings = \Bricks\Helpers::get_template_settings( $template_id );

      // // Add template conditions
      // if ( isset( $template_settings['templateConditions'] ) ) {
      //   $bricks_template['templateConditions'] = $template_settings['templateConditions'];
      // }

      // Create directory if it is not exist

      $result['success'][] = $bricks_template;
    }

    return $result;
  }

  public function download_bricks_template_json($template_id)
  {
    $bricks_template = \Bricks\Templates::get_template_by_id($template_id);

    if (!isset($bricks_template)) {
      rp_dev_log("❌ Template not found for ID: $template_id");
      return new \WP_Error('template_not_found', "Template not found for ID: $template_id", ['status' => 404]);
    }

    $json_data = json_encode($bricks_template);

    return $json_data;
  }


  public function upload_bricks_templates($params)
  {
    $json_contents = $params['jsonContents'];
    $imported_uuids = [];

    // Builer only: Import images (@since 1.10.2)
    $import_images = isset($_POST['importImages']) && $_POST['importImages'] == 'true';
    $global_classes = !empty($_POST['globalClasses']) ? json_decode($_POST['globalClasses']) : false;

    if (!is_array($json_contents)) {
      $json_contents = array($json_contents);
    }

    foreach ($json_contents as $json_content) {

      if (is_string($json_content)) {
        $template_data = json_decode($json_content, true);
      } else {
        $template_data = $json_content;
      }

      // Import template using Bricks API
      if (class_exists('\Bricks\Templates')) {

        // Capture current time to be used as post_date (@since 1.10.2)
        $time_now = current_time('mysql');
        $time_offset = 0;

        try {
          // Reset template instance data for new template insert
          \Bricks\Templates::$template_images = [];

          //     // Ensure each post has at least 1 second difference (@since 1.10.2)
          $post_date = date('Y-m-d H:i:s', strtotime($time_now) - $time_offset);

          $insert_post_data = [
            'post_status' => current_user_can('publish_posts') ? 'publish' : 'pending',
            'post_title' => !empty($template_data['title']) ? $template_data['title'] : esc_html__('(no title)', 'bricks'),
            'post_type' => BRICKS_DB_TEMPLATE_SLUG,
            'post_date' => $post_date, // (@since 1.10.2)
          ];

          // Template tags (terms)
          if (is_array($template_data['tags'])) {
            if (count($template_data['tags'])) {
              $insert_post_data['tax_input'] = [BRICKS_DB_TEMPLATE_TAX_TAG => $template_data['tags']];
            }
          }

          // Template bundles (terms)
          if (is_array($template_data['bundles'])) {
            if (count($template_data['bundles'])) {
              $insert_post_data['tax_input'] = [BRICKS_DB_TEMPLATE_TAX_BUNDLE => $template_data['bundles']];
            }
          }
          $new_template_id = wp_insert_post($insert_post_data);
          $area = 'content';
          $meta_key = BRICKS_DB_PAGE_CONTENT;
          $elements = false;
          $time_offset++; // Increase time offset for next post (@since 1.10.2)


          // Set rightplace_template_uuid (post meta)
          $rp_template_uuid = wp_generate_uuid4();
          $rp_template_uuid_with_prefix = 'rp_template:' . $rp_template_uuid;
          update_post_meta($new_template_id, RIGHTPLACE_TEMPLATE_UUID, $rp_template_uuid_with_prefix);
          $imported_uuids[] = $rp_template_uuid_with_prefix;

          if (!empty($template_data['type'])) {
            update_post_meta($new_template_id, BRICKS_DB_TEMPLATE_TYPE, $template_data['type']);
          }

          if (!empty($template_data['header'])) {
            $area = 'header';
            $meta_key = BRICKS_DB_PAGE_HEADER;
          } elseif (!empty($template_data['footer'])) {
            $area = 'footer';
            $meta_key = BRICKS_DB_PAGE_FOOTER;
          }

          if (!empty($template_data[$area])) {
            $elements = $template_data[$area];
          }

          if (isset($template_data['pageSettings'])) {
            update_post_meta($new_template_id, BRICKS_DB_PAGE_SETTINGS, $template_data['pageSettings']);
          }

          // Add template settings (@since 1.8.1)
          $template_settings = $template_data['templateSettings'] ?? false;
          // $template_settings = $template_data['templateSettings'] ?? [];


          // Add template conditions if they exist
          // if ( !empty( $template_data['templateConditions'] ) ) {
          //   $template_settings['templateConditions'] = $template_data['templateConditions'];
          // }

          if (is_array($template_settings)) {
            // Remove template preview post ID if it's not a single post (@since 1.10.2)
            $template_preview_post_id = $template_settings['templatePreviewPostId'] ?? 0;
            if ($template_preview_post_id && !is_single($template_preview_post_id)) {
              unset($template_settings['templatePreviewPostId']);
            }

            // Remove templatePreviewTerm if term ID doesn't exist (@since 1.10.2)
            $template_preview_term = $template_settings['templatePreviewTerm'] ?? 0;
            if ($template_preview_term) {
              $preview_term = $template_preview_term ? explode('::', $template_preview_term) : [];
              $preview_taxonomy = isset($preview_term[0]) ? $preview_term[0] : '';
              $preview_term_id = isset($preview_term[1]) ? intval($preview_term[1]) : '';
              if (!term_exists($preview_term_id, $preview_taxonomy)) {
                unset($template_settings['templatePreviewTerm']);
              }
            }

            // Remove templatePreviewAuthor if author ID doesn't exist (@since 1.10.2)
            $template_preview_author = $template_settings['templatePreviewAuthor'] ?? 0;
            if ($template_preview_author && !get_user_by('id', $template_preview_author)) {
              unset($template_settings['templatePreviewAuthor']);
            }

            // Remove templatePreviewPostType if post type doesn't exist (@since 1.10.2)
            $template_preview_post_type = $template_settings['templatePreviewPostType'] ?? '';
            if ($template_preview_post_type && !post_type_exists($template_preview_post_type)) {
              unset($template_settings['templatePreviewPostType']);
            }

            \Bricks\Helpers::set_template_settings($new_template_id, $template_settings);
          }

          // STEP: Add global classes used in template to global classes in this database
          $template_global_classes = !empty($template_data['global_classes']) ? $template_data['global_classes'] : [];
          $map_classes = []; // @see PopupTemplates.vue (@since 1.5.1)
          $maybe_pseudo_class_setting_keys = [];

          foreach ($template_global_classes as $template_class) {
            // STEP: Add template setting keys to create missing pseudo class from (@since 1.7.1)
            if (!empty($template_class['settings'])) {
              $maybe_pseudo_class_setting_keys = array_merge($maybe_pseudo_class_setting_keys, array_keys($template_class['settings']));
            }

            // Skip: Class with same unique 'id' exists locally
            $class_index = array_search($template_class['id'], array_column($global_classes, 'id'));

            if ($class_index !== false) {
              continue;
            }

            // Add to map_classes, then skip (global class with this 'name' already exists in this installation)
            $class_index = array_search($template_class['name'], array_column($global_classes, 'name'));

            if ($class_index !== false) {
              $map_classes[$template_class['id']] = $global_classes[$class_index]['id'];

              continue;
            }

            // Update global classes in database
            $global_classes[] = $template_class;
          }

          // Loop over all mapped classes to replace template element class id's with local class id's
          foreach ($map_classes as $template_class_id => $local_class_id) {
            foreach ($elements as $index => $element) {
              $element_classes = !empty($element['settings']['_cssGlobalClasses']) ? $element['settings']['_cssGlobalClasses'] : [];

              if (count($element_classes)) {
                foreach ($element_classes as $class_index => $element_class_id) {
                  if ($element_class_id === $template_class_id) {
                    $element_classes[$class_index] = $local_class_id;
                  }
                }

                $elements[$index]['settings']['_cssGlobalClasses'] = $element_classes;
              }
            }
          }

          // STEP: Update global classes in db
          $global_classes_response = \Bricks\Helpers::save_global_classes_in_db($global_classes);

          // STEP: Save final template elements
          $elements = \Bricks\Helpers::sanitize_bricks_data($elements);

          // Add back slashes to element settings (needed for '_content' HTML entities, and Custom CSS)
          foreach ($elements as $index => $element) {
            $element_settings = !empty($element['settings']) ? $element['settings'] : [];

            // STEP: Import element images & update template data with local image data (@since 1.10.2)
            if (count($element_settings) && $import_images) {
              \Bricks\Templates::import_images($element_settings, $import_images);
            }

            foreach ($element_settings as $setting_key => $setting_value) {
              if (is_string($setting_value)) {
                $elements[$index]['settings'][$setting_key] = addslashes($setting_value);
              }
            }
          }

          // STEP: Generate element IDs (@since 1.9.8)
          $elements = \Bricks\Helpers::generate_new_element_ids($elements);

          // STEP: Replace remote image data with imported/existing image data (@since 1.10.2)
          if (count(\Bricks\Templates::$template_images)) {
            $elements_encoded = wp_json_encode($elements);

            foreach (\Bricks\Templates::$template_images as $template_image) {
              $elements_encoded = str_replace(
                wp_json_encode($template_image['old']),
                wp_json_encode($template_image['new']),
                $elements_encoded
              );
            }

            $elements = json_decode($elements_encoded, true);
          }

          update_post_meta($new_template_id, $meta_key, $elements);

          // STEP: Generate CSS file for imported template
          if (\Bricks\Database::get_setting('cssLoading') === 'file' && $elements) {
            $template_css_file_name = \Bricks\Assets_Files::generate_post_css_file($new_template_id, $area, $elements);
          }

          // STEP: Add pseudo elements & classes used in the template to the database (@since 1.7.1)

          // Get latest pseudo classes from builder
          $pseudo_classes = !empty($_POST['pseudoClasses']) ? \Bricks\Ajax::decode($_POST['pseudoClasses'], false) : [];

          // Add element setting keys to create missing pseudo class from
          foreach ($elements as $element) {
            if (!empty($element['settings'])) {
              $maybe_pseudo_class_setting_keys = array_merge($maybe_pseudo_class_setting_keys, array_keys($element['settings']));
            }
          }

          $all_pseudo_classes = \Bricks\Templates::template_import_create_missing_pseudo_classes($pseudo_classes, $maybe_pseudo_class_setting_keys);
          $all_pseudo_classes = array_unique($all_pseudo_classes);

          // Update pseudo classes db entry (if we got more items than before)
          if (count($all_pseudo_classes) > count($pseudo_classes)) {
            update_option(BRICKS_DB_PSEUDO_CLASSES, $all_pseudo_classes);
          }

        } catch (\Exception $e) {
          rp_dev_log("❌ Error importing template: " . $e->getMessage());
        }
      } else {
        rp_dev_log("❌ Bricks\Templates class not found");
        break;
      }
    }

    return array(
      'success' => !empty($imported_uuids),
      'imported_uuids' => $imported_uuids,
      'total_imported' => count($imported_uuids)
    );
  }


  public function update_bricks_templates($params)
  {
    $json_contents = $params['jsonContents'];
    $rp_template_uuid = $params['rp_template_uuid'];
    $updated_uuids = [];


    // Builer only: Import images (@since 1.10.2)
    $import_images = isset($_POST['importImages']) && $_POST['importImages'] == 'true';
    $global_classes = !empty($_POST['globalClasses']) ? json_decode($_POST['globalClasses']) : false;

    foreach ($json_contents as $json_content) {
      if (is_string($json_content)) {
        $template_data = json_decode($json_content, true);
      } else {
        $template_data = $json_content;
      }

      if (class_exists('\Bricks\Templates')) {
        // 🔍 Search exist templae by UUID
        $existing_post = new \WP_Query([
          'post_type' => BRICKS_DB_TEMPLATE_SLUG,
          'post_status' => 'any',
          'meta_query' => [
            [
              'key' => RIGHTPLACE_TEMPLATE_UUID,
              'value' => $rp_template_uuid,
            ]
          ],
          'fields' => 'ids',
          'posts_per_page' => 1,
        ]);

        if (empty($existing_post->posts)) {
          rp_dev_log("❌ No existing template found with UUID: $rp_template_uuid");
          continue;
        }

        $template_id = $existing_post->posts[0];
        rp_dev_log("✅ Found template ID: $template_id");

        // Update Process
        $area = 'content';
        $meta_key = BRICKS_DB_PAGE_CONTENT;
        $elements = false;

        if (!empty($template_data['type'])) {
          update_post_meta($template_id, BRICKS_DB_TEMPLATE_TYPE, $template_data['type']);
        }

        if (!empty($template_data['header'])) {
          $area = 'header';
          $meta_key = BRICKS_DB_PAGE_HEADER;
        } elseif (!empty($template_data['footer'])) {
          $area = 'footer';
          $meta_key = BRICKS_DB_PAGE_FOOTER;
        }

        if (!empty($template_data[$area])) {
          $elements = $template_data[$area];
        }

        if (isset($template_data['pageSettings'])) {
          update_post_meta($template_id, BRICKS_DB_PAGE_SETTINGS, $template_data['pageSettings']);
        }

        // Add template settings (@since 1.8.1)
        $template_settings = $template_data['templateSettings'] ?? false;
        // $template_settings = $template_data['templateSettings'] ?? [];

        // Add template conditions if they exist
        // if ( !empty( $template_data['templateConditions'] ) ) {
        //   $template_settings['templateConditions'] = $template_data['templateConditions'];
        // }


        if (is_array($template_settings)) {
          // Remove template preview post ID if it's not a single post (@since 1.10.2)
          $template_preview_post_id = $template_settings['templatePreviewPostId'] ?? 0;
          if ($template_preview_post_id && !is_single($template_preview_post_id)) {
            unset($template_settings['templatePreviewPostId']);
          }

          // Remove templatePreviewTerm if term ID doesn't exist (@since 1.10.2)
          $template_preview_term = $template_settings['templatePreviewTerm'] ?? 0;
          if ($template_preview_term) {
            $preview_term = $template_preview_term ? explode('::', $template_preview_term) : [];
            $preview_taxonomy = isset($preview_term[0]) ? $preview_term[0] : '';
            $preview_term_id = isset($preview_term[1]) ? intval($preview_term[1]) : '';
            if (!term_exists($preview_term_id, $preview_taxonomy)) {
              unset($template_settings['templatePreviewTerm']);
            }
          }

          // Remove templatePreviewAuthor if author ID doesn't exist (@since 1.10.2)
          $template_preview_author = $template_settings['templatePreviewAuthor'] ?? 0;
          if ($template_preview_author && !get_user_by('id', $template_preview_author)) {
            unset($template_settings['templatePreviewAuthor']);
          }

          // Remove templatePreviewPostType if post type doesn't exist (@since 1.10.2)
          $template_preview_post_type = $template_settings['templatePreviewPostType'] ?? '';
          if ($template_preview_post_type && !post_type_exists($template_preview_post_type)) {
            unset($template_settings['templatePreviewPostType']);
          }

          \Bricks\Helpers::set_template_settings($template_id, $template_settings);
        }

        // STEP: Add global classes used in template to global classes in this database
        $template_global_classes = !empty($template_data['global_classes']) ? $template_data['global_classes'] : [];
        $map_classes = []; // @see PopupTemplates.vue (@since 1.5.1)
        $maybe_pseudo_class_setting_keys = [];

        foreach ($template_global_classes as $template_class) {
          // STEP: Add template setting keys to create missing pseudo class from (@since 1.7.1)
          if (!empty($template_class['settings'])) {
            $maybe_pseudo_class_setting_keys = array_merge($maybe_pseudo_class_setting_keys, array_keys($template_class['settings']));
          }

          // Skip: Class with same unique 'id' exists locally
          $class_index = array_search($template_class['id'], array_column($global_classes, 'id'));

          if ($class_index !== false) {
            continue;
          }

          // Add to map_classes, then skip (global class with this 'name' already exists in this installation)
          $class_index = array_search($template_class['name'], array_column($global_classes, 'name'));

          if ($class_index !== false) {
            $map_classes[$template_class['id']] = $global_classes[$class_index]['id'];

            continue;
          }

          // Update global classes in database
          $global_classes[] = $template_class;
        }

        // Loop over all mapped classes to replace template element class id's with local class id's
        foreach ($map_classes as $template_class_id => $local_class_id) {
          foreach ($elements as $index => $element) {
            $element_classes = !empty($element['settings']['_cssGlobalClasses']) ? $element['settings']['_cssGlobalClasses'] : [];

            if (count($element_classes)) {
              foreach ($element_classes as $class_index => $element_class_id) {
                if ($element_class_id === $template_class_id) {
                  $element_classes[$class_index] = $local_class_id;
                }
              }

              $elements[$index]['settings']['_cssGlobalClasses'] = $element_classes;
            }
          }
        }

        // STEP: Update global classes in db
        $global_classes_response = \Bricks\Helpers::save_global_classes_in_db($global_classes);

        // STEP: Save final template elements
        $elements = \Bricks\Helpers::sanitize_bricks_data($elements);

        // Add back slashes to element settings (needed for '_content' HTML entities, and Custom CSS)
        foreach ($elements as $index => $element) {
          $element_settings = !empty($element['settings']) ? $element['settings'] : [];

          // STEP: Import element images & update template data with local image data (@since 1.10.2)
          if (count($element_settings) && $import_images) {
            \Bricks\Templates::import_images($element_settings, $import_images);
          }

          foreach ($element_settings as $setting_key => $setting_value) {
            if (is_string($setting_value)) {
              $elements[$index]['settings'][$setting_key] = addslashes($setting_value);
            }
          }
        }

        // STEP: Generate element IDs (@since 1.9.8)
        $elements = \Bricks\Helpers::generate_new_element_ids($elements);

        // STEP: Replace remote image data with imported/existing image data (@since 1.10.2)
        if (count(\Bricks\Templates::$template_images)) {
          $elements_encoded = wp_json_encode($elements);

          foreach (\Bricks\Templates::$template_images as $template_image) {
            $elements_encoded = str_replace(
              wp_json_encode($template_image['old']),
              wp_json_encode($template_image['new']),
              $elements_encoded
            );
          }

          $elements = json_decode($elements_encoded, true);
        }

        update_post_meta($template_id, $meta_key, $elements);

        // STEP: Generate CSS file for imported template
        if (\Bricks\Database::get_setting('cssLoading') === 'file' && $elements) {
          $template_css_file_name = \Bricks\Assets_Files::generate_post_css_file($template_id, $area, $elements);
        }

        // STEP: Add pseudo elements & classes used in the template to the database (@since 1.7.1)

        // Get latest pseudo classes from builder
        $pseudo_classes = !empty($_POST['pseudoClasses']) ? \Bricks\Ajax::decode($_POST['pseudoClasses'], false) : [];

        // Add element setting keys to create missing pseudo class from
        foreach ($elements as $element) {
          if (!empty($element['settings'])) {
            $maybe_pseudo_class_setting_keys = array_merge($maybe_pseudo_class_setting_keys, array_keys($element['settings']));
          }
        }

        $all_pseudo_classes = \Bricks\Templates::template_import_create_missing_pseudo_classes($pseudo_classes, $maybe_pseudo_class_setting_keys);
        $all_pseudo_classes = array_unique($all_pseudo_classes);

        // Update pseudo classes db entry (if we got more items than before)
        if (count($all_pseudo_classes) > count($pseudo_classes)) {
          update_option(BRICKS_DB_PSEUDO_CLASSES, $all_pseudo_classes);
        }

        // Template Title update
        $new_title = $template_data['title'] ?? null;
        if ($new_title) {
          wp_update_post([
            'ID' => $template_id,
            'post_title' => $new_title,
          ]);
        }

        $updated_uuids[] = $rp_template_uuid;
        rp_dev_log("✅✅✅✅ Template updated successfully for UUID: $rp_template_uuid");

      } else {
        rp_dev_log("❌ Bricks\Templates class not found");
        break;
      }

    }

    return array(
      'success' => !empty($updated_uuids),
      'updated_uuids' => $updated_uuids,
    );
  }

  public function disconnect_bricks_templates($params)
  {
    $rp_template_uuid = $params['rp_template_uuid'];
    $disconnected_uuids = [];

    // Search for template by UUID
    $existing_post = new \WP_Query([
      'post_type' => BRICKS_DB_TEMPLATE_SLUG,
      'post_status' => 'any',
      'meta_query' => [
        [
          'key' => RIGHTPLACE_TEMPLATE_UUID,
          'value' => $rp_template_uuid,
        ]
      ],
      'fields' => 'ids',
      'posts_per_page' => 1,
    ]);

    if (!empty($existing_post->posts)) {
      $template_id = $existing_post->posts[0];
      rp_dev_log("✅ Found template ID: $template_id");

      // Delete the rp_template_uuid meta
      $result = delete_post_meta($template_id, RIGHTPLACE_TEMPLATE_UUID);

      if ($result) {
        $disconnected_uuids[] = $rp_template_uuid;
        rp_dev_log("✅ Successfully disconnected template UUID: $rp_template_uuid");
      } else {
        rp_dev_log("❌ Failed to disconnect template UUID: $rp_template_uuid");
      }
    } else {
      rp_dev_log("❌ No template found with UUID: $rp_template_uuid");
    }

    return array(
      'success' => !empty($disconnected_uuids),
      'disconnected_uuids' => $disconnected_uuids,
    );
  }

  public function duplicate_bricks_template($params)
  {
    $post_id = $params['post_id'];
    
    if (!class_exists('\Bricks\Templates')) {
      rp_dev_log("❌ Bricks\Templates class not found");
      return new \WP_Error('bricks_not_found', 'Bricks Templates class not found');
    }

    try {
        // Call Bricks function - duplicate_content 
      $duplicated_template_id = \Bricks\Admin::duplicate_content($post_id);
      
      if ($duplicated_template_id) {
        rp_dev_log("✅ Successfully duplicated template from post ID: $post_id to new template ID: $duplicated_template_id");
        
        // Remove Rightplace-related meta fields from the duplicated template
        delete_post_meta($duplicated_template_id, RIGHTPLACE_ASSET_UUID);
        delete_post_meta($duplicated_template_id, RIGHTPLACE_TEMPLATE_UUID);
        rp_dev_log("✅ Removed Rightplace meta fields from duplicated template ID: $duplicated_template_id");
        
        return array(
          'success' => true,
          'original_post_id' => $post_id,
          'duplicated_template_id' => $duplicated_template_id,
          'message' => 'Template duplicated successfully'
        );
      } else {
        rp_dev_log("❌ Failed to duplicate template from post ID: $post_id");
        return new \WP_Error('duplication_failed', 'Failed to duplicate template');
      }
      
    } catch (\Exception $e) {
      rp_dev_log("❌ Error duplicating template from post ID: $post_id - " . $e->getMessage());
      return new \WP_Error('duplication_error', 'Error duplicating template: ' . $e->getMessage());
    }
  }

  public function delete_bricks_template($params)
  {
    $post_id = $params['post_id'];
    
    if (!class_exists('\Bricks\Templates')) {
      rp_dev_log("❌ Bricks\Templates class not found");
      return new \WP_Error('bricks_not_found', 'Bricks Templates class not found');
    }

    try {
      $result = wp_delete_post($post_id, true);
      
      if ($result) {
        rp_dev_log("✅ Successfully deleted template from post ID: $post_id");
        
        return array(
          'success' => true,
          'post_id' => $post_id,
          'message' => 'Template deleted successfully'
        );
      } else {
        rp_dev_log("❌ Failed to delete template from post ID: $post_id");
        return new \WP_Error('deletion_failed', 'Failed to delete template');
      }
      
    } catch (\Exception $e) {
      rp_dev_log("❌ Error deleting template from post ID: $post_id - " . $e->getMessage());
      return new \WP_Error('deletion_error', 'Error deleting template: ' . $e->getMessage());
    }
  }

}

new Builder();
