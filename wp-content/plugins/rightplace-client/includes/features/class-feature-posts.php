<?php
namespace Rightplace\Features;
class Posts
{
  public function __construct()
  {
    add_filter('rightplace_action_filter/getPosts', array($this, 'get_posts_for_hooks'));
    add_filter('rightplace_action_filter/getPostTypes', array($this, 'get_post_types'));
    add_filter('rightplace_action_filter/updatePost', array($this, 'update_post'));
    add_filter('rightplace_action_filter/getPostCategories', array($this, 'get_post_categories'));
    add_filter('rightplace_action_filter/getPostTags', array($this, 'get_post_tags'));
    add_filter('rightplace_action_filter/searchPostsByTitle', array($this, 'search_posts_by_title'));
    add_filter('rightplace_action_filter/getPostStatistics', array($this, 'get_post_statistics'));
    add_filter('rightplace_action_filter/deletePostMetaByKey', array($this, 'delete_post_meta_by_key'));
    add_filter('rightplace_action_filter/getAllPostMetaPaths', array($this, 'get_all_post_meta_paths'));
    add_filter('rightplace_action_filter/getPost', array($this, 'get_post'));
    add_filter('rightplace_action_filter/getMedia', array($this, 'get_media'));
    add_filter('rightplace_action_filter/getPostsRaw', array($this, 'get_posts_raw'));
    add_filter('rightplace_action_filter/bulkActions', array($this, 'bulk_actions'));
    add_filter('rightplace_action_filter/createPost', array($this, 'create_post'));
    add_filter('rightplace_action_filter/getCategories', array($this, 'get_categories'));
    add_filter('rightplace_action_filter/getPostById', array($this, 'get_post_by_id'));   
    add_filter('rightplace_action_filter/importPostsFromCsv', array($this, 'import_posts_from_csv'));   
    add_filter('rightplace_action_filter/exportPostsByTypeToCsv', array($this, 'export_posts_by_type_to_csv'));   
    add_filter('rightplace_action_filter/exportPostsByTypeToJson', array($this, 'export_posts_by_type_to_json'));   
    add_filter('rightplace_action_filter/getPostsByTypeToCsv', array($this, 'get_post_fields_by_type'));   
    add_filter('rightplace_action_filter/importPostsByPostType', array($this, 'import_posts_by_post_type'));   
    add_filter('rightplace_action_filter/getAcfFieldGroups', array($this, 'get_acf_field_groups'));   
    add_filter('rightplace_action_filter/getMetaBoxFieldGroups', array($this, 'get_meta_box_fields'));   
    add_filter('rightplace_action_filter/getBricksEnabledPostTypes', array($this, 'get_bricks_enabled_post_types'));   

    

    
    // add_filter('rightplace_action_filter/exportPostsByTypeToCsv', array($this, 'export_posts_by_type_to_csv'));   
  }


  




  public function get_post_types($params)
  {
    $post_types = get_post_types(array('public' => true), 'objects');

    foreach ($post_types as $post_type) {
      if (apply_filters('rightplace_action_filter/builder/isComponent', false, $post_type->name) === true) {
        unset($post_types[$post_type->name]);
      }
    }

    return array('success' => true, 'post_types' => $post_types);
  }

  public function get_posts_for_hooks($params)
  {
    return self::get_posts($params);
  }

  public function get_post($params)
  {
    $post = get_post($params['post_id']);
    if (!$post) {
      return array('success' => false, 'message' => 'Post not found');
    }

    return array(
      'success' => true,
      'post' => array(
        'ID' => $post->ID,
        'post_content' => $post->post_content,
        'post_title' => $post->post_title,
        'post_date' => $post->post_date,
        'post_modified' => $post->post_modified,
        'post_type' => $post->post_type,
      )
    );
  }

  /**
   * Get posts raw: Normally for LLM 
   */
  public function get_posts_raw($params)
  {
    try {
      $posts = get_posts($params);
      return array('success' => true, 'posts' => $posts);
    } catch (\Exception $e) {
      return array('success' => false, 'error' => $e->getMessage());
    }
  }

  /**
   * Get posts
   *
   * @since    1.0.0
   */
  public static function get_posts($params)
  {
    $limit = $params['limit'] ?? $params['posts_per_page'] ?? -1;
    $max_mb = $params['max_mb'] ?? null;
    $offset = $params['offset'] ?? 0;
    $post_type = $params['post_type'] ?? 'post';
    $last_modified = $params['last_modified'] ?? null;
    $fields = $params['fields'] ?? array();

    // Check if post_type is 'trash' or 'trash-{post_type}'
    if ($post_type === 'trash') {
      $post_status = 'trash'; // Set post status to trash
      $post_type = 'any'; // Set post type to any to retrieve all trashed posts
    } elseif (strpos($post_type, 'trash-') === 0) {
      $post_type = substr($post_type, 6); // Remove 'trash-' prefix to get the actual post type
      $post_status = 'trash'; // Set post status to trash for the specific post type
    } else {
      $post_status = 'any'; // Default to any status for other post types
    }


    // Get PHP post size limit (in bytes)
    $post_max_size = min(
      self::return_bytes(ini_get('post_max_size')),
      self::return_bytes(ini_get('upload_max_filesize'))
    );

    // Use the smaller of max_mb or PHP's limit
    $max_size = $max_mb
      ? min($max_mb * 1024 * 1024, $post_max_size)
      : $post_max_size;

    // Add date query if last_modified is provided
    $args = array(
      'posts_per_page' => $limit,
      'offset' => $offset,
      'post_type' => $post_type,
      'post_status' => $post_status,
    );

    if ($last_modified) {
      $args['date_query'] = array(
        array(
          'column' => 'post_modified',
          'after' => $last_modified,
        ),
      );
    }

    // Get total posts count using WP_Query
    $count_query = new \WP_Query(array_merge(
      $args,
      array('posts_per_page' => -1, 'fields' => 'ids')
    ));
    $total_posts = $count_query->found_posts;

    // Get posts using WP_Query
    $posts = get_posts($args);

    // Initialize result array
    $result_posts = array();
    $next_offset = $offset;

    $post_fields = array();

    if (!empty($fields)) {
      // Extract post meta fields
      $post_meta_to_extract = array();
      $acf_to_extract = array();
      $meta_box_to_extract = array();

      foreach ($fields as $key => $field) {
        if ($field['custom'] === true && $field['data_type'] === 'post_meta') {
          $post_meta_to_extract[$key] = $field['path'];
        } else if ($field['custom'] === true && $field['data_type'] === 'acf') {
          $acf_to_extract[$key] = $field['path']; 
        } else if ($field['custom'] === true && $field['data_type'] === 'meta_box') {
          $meta_box_to_extract[$key] = $field['path']; // <-- 추가
        } else if ($field['custom'] === false) {
          if ($field['path'] === 'author_data') {
            $post_fields[] = 'author'; // QUICK HACK
          } else {
            $post_fields[] = $field['path'];
          }
        }
      }
    } else {
      // for post fields we will load them like default for now
      $post_fields = array(
        'featured_image',
        'author',
        'categories',
        'tags',
        'permalink',
        'comment_count',
        'post_format',
        'slug',
      );
    }

    // Process posts one by one to check size
    foreach ($posts as $post) {
      unset($post->post_content);

      // Initialize post_meta and acf objects
      $post->post_meta = array();
      $post->acf = array(); 
      $post->meta_box = array();

      // Add extra fields including post meta
      $post = self::add_extra_fields($post, $post_fields);

      // Add the post name based on post type
      // $post->name = isset($post_type_names[$post->post_type]) ? $post_type_names[$post->post_type] : ucfirst($post->post_type);


      // Extract post meta if any were specified
      if (!empty($post_meta_to_extract)) {
        foreach ($post_meta_to_extract as $key => $meta_key) {
          $post->post_meta[$key] = get_post_meta($post->ID, $meta_key, true);
        }
      }


      // Extract ACF if any were specified
      // if (!empty($acf_to_extract) && function_exists('get_field')) {
      //   foreach ($acf_to_extract as $key => $acf_key) {
      //     $post->acf[$key] = get_field($acf_key, $post->ID);
      //   }
      // }

      // Extract ACF if any were specified
if (!empty($acf_to_extract) && function_exists('get_field')) {
  foreach ($acf_to_extract as $key => $acf_key) {
      $acf_value = get_field($acf_key, $post->ID);
      $field_obj = get_field_object($acf_key, $post->ID);

     // default is origin value
      $formatted_value = $acf_value;


     // date_picker, date_time_picker
      if (in_array($field_obj['type'], ['date_picker', 'date_time_picker'])) {
        $formatted_value = [
          'value' => $acf_value,
          'format' => $field_obj['return_format'] ?? '',
        ];
      }

    // select, taxonomy
    else if (in_array($field_obj['type'], ['select', 'taxonomy'])) {
      $options = [];

      // select has options in the choices array
      if ($field_obj['type'] === 'select' && isset($field_obj['choices'])) {
        foreach ($field_obj['choices'] as $option_value => $option_label) {
          $options[] = [
            'label' => $option_label,
            'value' => $option_value,
          ];
        }
      }

      // taxonomy retrieves terms directly
      if ($field_obj['type'] === 'taxonomy' && isset($field_obj['taxonomy'])) {
        $terms = get_terms([
          'taxonomy' => $field_obj['taxonomy'],
          'hide_empty' => false,
        ]);

        if (!is_wp_error($terms)) {
          foreach ($terms as $term) {
            $options[] = [
              'label' => $term->name,
              'value' => (string)$term->term_id,
            ];
          }
        }
      }

      $formatted_value = [
        'value' => $acf_value,
        'options' => $options,
      ];
    }

      // Store the ACF value in the post object
      $post->acf[$key] = $formatted_value;
  }
}

if (!empty($meta_box_to_extract) && function_exists('rwmb_meta')) {
  foreach ($meta_box_to_extract as $key => $mb_key) {
    $mb_value = rwmb_meta($mb_key, '', $post->ID);
    $field_obj = rwmb_get_field_settings($mb_key, [], $post->ID);

    $formatted_value = $mb_value;

    // select, taxonomy
    if (in_array($field_obj['type'], ['select', 'taxonomy'])) {
      $options = [];

      // select
      if ($field_obj['type'] === 'select' && isset($field_obj['options'])) {
        foreach ($field_obj['options'] as $option_value => $option_label) {
          $options[] = [
            'label' => $option_label,
            'value' => $option_value,
          ];
        }

        $formatted_value = [
          'value' => $mb_value,
          'options' => $options,
        ];
      }

      // taxonomy
      if ($field_obj['type'] === 'taxonomy' && isset($field_obj['taxonomy'])) {
        $terms = get_terms([
          'taxonomy' => $field_obj['taxonomy'],
          'hide_empty' => false,
        ]);

        if (!is_wp_error($terms)) {
          foreach ($terms as $term) {
            $options[] = [
              'label' => $term->name,
              'value' => (string)$term->term_id,
            ];
          }
        }

        $is_multiple = !empty($field_obj['multiple']);

        // extract only term_id when it is not multiple taxonomy
        if ($is_multiple) {
          $value = [];
          if (is_array($mb_value)) {
            foreach ($mb_value as $term_obj) {
              if (is_object($term_obj) && isset($term_obj->term_id)) {
                $value[] = (string)$term_obj->term_id;
              }
            }
          }
        } else {
          $value = '';
          if (is_object($mb_value) && isset($mb_value->term_id)) {
            $value = (string)$mb_value->term_id;
          }
        }

        $formatted_value = [
          'value' => $value,
          'options' => $options,
        ];
      }
    }

    $post->meta_box[$key] = $formatted_value;
  }
}

      

      // Calculate size after adding this post
      $temp_array = $result_posts;
      $temp_array[] = $post;
      $serialized_size = strlen(serialize($temp_array));

      // If adding this post would exceed size limit, break
      if ($serialized_size > $max_size) {
        break;
      }

      // Add post to result array
      $result_posts[] = $post;
      $next_offset++;
    }

    // Only include next_offset if there are more posts to fetch
    $response = array(
      'success' => true,
      'posts' => $result_posts,
      'total_posts' => $total_posts,
      'last_batch' => ($next_offset >= $total_posts),
      'post_fields' => $post_fields,
      'params' => $params, // <-- FOR DEV
    );

    if (count($posts) > count($result_posts)) {
      $response['next_offset'] = $next_offset;
    }

    return $response;
  }

  /**
   * Convert PHP ini size values to bytes
   * 
   * @param string $val Size value from php.ini (e.g., '8M', '1G')
   * @return int Size in bytes
   */
  private static function return_bytes($val)
  {
    $val = trim($val);
    $last = strtolower($val[strlen($val) - 1]);
    $val = (int) $val;

    switch ($last) {
      case 'g':
        $val *= 1024;
      case 'm':
        $val *= 1024;
      case 'k':
        $val *= 1024;
    }

    return $val;
  }

  /**
   * Add additional fields to post object
   *
   * @param WP_Post $post Post object
   * @param array $fields Array of fields to include
   * @return WP_Post Modified post object
   */
  private static function add_extra_fields($post, $fields)
  {
    if (in_array('featured_image', $fields)) {
      $thumbnail_id = get_post_thumbnail_id($post->ID);
      if ($thumbnail_id) {
        $post->featured_image = array(
          'id' => $thumbnail_id,
          'url' => get_the_post_thumbnail_url($post->ID, 'thumbnail'),
        );
      }
    }

    if (in_array('author', $fields)) {
      $author = get_userdata($post->post_author);
      $post->author_data = array(
        'ID' => $author->ID,
        'display_name' => $author->display_name,
        'avatar' => get_avatar_url($author->ID),
      );
    }

    if (in_array('categories', $fields)) {
      $post->categories = get_the_category($post->ID);
    }

    if (in_array('tags', $fields)) {
      $post->tags = get_the_tags($post->ID);
    }

    if (in_array('post_format', $fields)) {
      $post->post_format = get_post_format($post->ID);
    }

    if (in_array('comment_count', $fields)) {
      $post->comment_count = get_comment_count($post->ID);
    }

    if (in_array('permalink', $fields)) {
      $post->permalink = get_permalink($post->ID);
    }

    if (in_array('slug', $fields)) {
      $post->slug = $post->post_name;
    }

    // Add custom post meta fields
    //   foreach ($fields as $key => $field) {
    //     if ($field['custom'] === true && $field['data_type'] === 'post_meta') {
    //         $meta_value = get_post_meta($post->ID, $field['path'], true);
    //         // Split the path by '.' to create nested structure
    //         $path_parts = explode('.', $field['path']);
    //         $current = &$post->post_meta;

    //         foreach ($path_parts as $part) {
    //             if (!isset($current[$part])) {
    //                 $current[$part] = [];
    //             }
    //             $current = &$current[$part];
    //         }
    //         $current = $meta_value; // Set the value at the final part
    //     } else if ($field['data_type'] === 'post') {
    //         // Handle standard post fields
    //         $post->{$field['path']} = $post->{$field['path']};
    //     }
    // }


    return $post;
  }

  public static function get_media($params)
  {
    $media_ids = $params['media_ids'];
    $media = array();

    foreach ($media_ids as $media_id) {
      $attachment = get_post($media_id);
      if (!$attachment)
        continue;

      $metadata = wp_get_attachment_metadata($media_id);
      $sizes = array();

      // Get all available image sizes
      if (isset($metadata['sizes'])) {
        foreach ($metadata['sizes'] as $size => $size_info) {
          $image_src = wp_get_attachment_image_src($media_id, $size);
          if ($image_src) {
            $sizes[$size] = array(
              'url' => $image_src[0],
              'width' => $image_src[1],
              'height' => $image_src[2]
            );
          }
        }
      }

      // Get full size image
      $full_src = wp_get_attachment_image_src($media_id, 'full');

      $media[] = array(
        'id' => $media_id,
        'title' => $attachment->post_title,
        'caption' => $attachment->post_excerpt,
        'description' => $attachment->post_content,
        'alt' => get_post_meta($media_id, '_wp_attachment_image_alt', true),
        'mime_type' => $attachment->post_mime_type,
        'date_created' => $attachment->post_date,
        'date_modified' => $attachment->post_modified,
        'author' => $attachment->post_author,
        'full' => array(
          'url' => $full_src[0],
          'width' => $full_src[1],
          'height' => $full_src[2]
        ),
        'sizes' => $sizes,
        'metadata' => $metadata
      );
    }

    return array('success' => true, 'media' => $media);
  }




  public static function update_post($params)
  {
    // Check if 'posts' key exists in params
    if (!isset($params['posts'])) {
        return array('success' => false, 'message' => 'No posts data provided.');
    }

    $post_data = $params['posts']; // Access the posts array
    $updated_posts = array();

    foreach ($post_data as $data) {
        $post_id = isset($data['ID']) ? $data['ID'] : 0;

        // Check if the post ID is valid
        if ($post_id <= 0) {
            continue; // Skip invalid post IDs
        }

        // Update post title (if provided)
        if (isset($data['post_title'])) {
            $post_title = sanitize_text_field($data['post_title']);
            // Update the post
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => $post_title,
            ));
        }

        // Update post content (if provided)
        if (isset($data['post_content'])) {
          $post_content = wp_kses_post($data['post_content']); // Sanitize content
          // Update the post content
          wp_update_post(array(
              'ID' => $post_id,
              'post_content' => $post_content,
          ));
      }

        // Update post excerpt (if provided)
        if (isset($data['post_excerpt'])) {
          $post_excerpt = wp_kses_post($data['post_excerpt']); // Sanitize excerpt
          // Update the post excerpt
          wp_update_post(array(
              'ID' => $post_id,
              'post_excerpt' => $post_excerpt,
          ));
      }

        // Update post date (if provided)
        if (isset($data['post_date'])) {
            $post_date = sanitize_text_field($data['post_date']);
            // Update the post date
            wp_update_post(array(
                'ID' => $post_id,
                'post_date' => $post_date, // Assuming the date is in UTC
                'post_date_gmt' => get_gmt_from_date($post_date) // Set GMT date
            ));
        }

        // Update slug (if provided)
        if (isset($data['slug'])) {
          $slug = sanitize_title($data['slug']);
          // Update the post slug
          wp_update_post(array(
              'ID' => $post_id,
              'post_name' => $slug,
          ));
      }

      // Update permalink (if provided)
      if (isset($data['permalink'])) {
          $permalink = esc_url($data['permalink']);
          // Update the post's permalink by setting the post_name
          $slug = basename($permalink); // Extract slug from permalink
          wp_update_post(array(
              'ID' => $post_id,
              'post_name' => $slug,
          ));
      }

        // Update featured image (if provided)
        if (isset($data['featured_image']) && isset($data['featured_image']['id'])) {
            $thumbnail_id = intval($data['featured_image']['id']);
            // Set the featured image for the post
            set_post_thumbnail($post_id, $thumbnail_id);
        }

        // Update post status (if provided)
        if (isset($data['post_status'])) {
            $post_status = sanitize_text_field($data['post_status']);
            // Validate post status
            $valid_statuses = array('draft', 'pending', 'private', 'publish', 'inherit');
            if (in_array($post_status, $valid_statuses)) {
                // Update the post status
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_status' => $post_status,
                ));
            }
        }

        // Update author data (if provided)
        if (isset($data['author_data'])) {
          $author_data = $data['author_data'];
          if (isset($author_data['ID'])) {
              $post_author = intval($author_data['ID']);
              // Validate if the user has 'edit_posts' capability
              if (user_can($post_author, 'edit_posts')) {
                  // Update the post author
                  wp_update_post(array(
                      'ID' => $post_id,
                      'post_author' => $post_author,
                  ));
              }
          }
      }

        // Tag update
        if (isset($data['tags']) && is_array($data['tags'])) {
            // Initialize tag array
            $tag_names = array();
    
            foreach ($data['tags'] as $tag) {
                 // Tag name is required
                 if (empty($tag['name'])) {
                        continue; // Skip if tag name is missing
                 }
    
                // Search for existing tag
                $existing_tag = term_exists($tag['name'], 'post_tag');
    
                if ($existing_tag) {
                    // If the tag exists, add its name
                    $tag_names[] = $tag['name']; // Add existing tag name
                } else {
                    // Add a new tag. The slug is generated based on the tag name
                    $slug = sanitize_title($tag['name']); // Generate slug
                    $new_tag = wp_insert_term($tag['name'], 'post_tag', array(
                        'slug' => $slug,
                    ));
  
                    if (!is_wp_error($new_tag)) {
                        $tag_names[] = $new_tag['term_id']; // Add new tag ID
                    }
                 }
            }
    
               // Update the post with tags
                wp_set_post_terms($post_id, $tag_names, 'post_tag');
            }
        

        // Category update (if provided)
        if (isset($data['categories'])) {
            $category_ids = array();
            foreach ($data['categories'] as $category) {
                if (!empty($category['cat_name'])) {
                    $existing_category = term_exists($category['cat_name'], 'category');
                    if ($existing_category) {
                        $category_ids[] = $existing_category['term_id'];
                    } else {
                        // Add new category
                        $new_category = wp_insert_term($category['cat_name'], 'category');
                        if (!is_wp_error($new_category)) {
                            $category_ids[] = $new_category['term_id'];
                        }
                    }
                }
            }
            // Update the post with categories
            wp_set_post_terms($post_id, $category_ids, 'category');
        }
        
                // Custom field update (if provided)
                if (isset($data['custom_fields']) && is_array($data['custom_fields'])) {
                  foreach ($data['custom_fields'] as $custom_field) {
                      if (isset($custom_field['path'])) {
                          // If value is provided, update the custom field
                          if (array_key_exists('value', $custom_field)) {
                              update_post_meta($post_id, $custom_field['path'], $custom_field['value']);
                          } else {
                              // If value is undefined, delete the custom field
                              delete_post_meta($post_id, $custom_field['path']);
                          }
                      }
                  }
              }

        // ACF field update (if provided)
        if (isset($data['acf_fields']) && is_array($data['acf_fields']) && function_exists('update_field')) {
          foreach ($data['acf_fields'] as $acf_field) {
             if (isset($acf_field['path'])) {
                  if (array_key_exists('value', $acf_field)) {
                    $value = $acf_field['value'];

                    // Check if value is an array with 'value' key inside (e.g., date object format)
                    if (is_array($value) && isset($value['value'])) {
                      $value = $value['value'];
                    }

                   update_field($acf_field['path'], $value, $post_id);
                 } else {
                 // No ACF equivalent for "delete", so you can optionally clear the field like this:
                   update_field($acf_field['path'], null, $post_id);
                 }
              }
          }
        }

        // Meta box field update (if provided)
        if (isset($data['meta_box_fields']) && is_array($data['meta_box_fields'])) {
            foreach ($data['meta_box_fields'] as $field) {
                if (isset($field['path'])) {
                    $field_key = $field['path'];
                    $value = null;

                    if (array_key_exists('value', $field)) {
                        $value = $field['value'];

                        // Check for nested 'value'
                        if (is_array($value) && isset($value['value'])) {
                            $value = $value['value'];
                        }
                    }

                    // Get the field settings from Meta Box
                    $meta_box_fields = rwmb_get_field_settings($field_key, [], $post_id);
                    $field_type = isset($meta_box_fields['type']) ? $meta_box_fields['type'] : '';

                    // Handle taxonomy field types
                    if ($field_type === 'taxonomy') {
                        $taxonomy = isset($meta_box_fields['taxonomy']) ? $meta_box_fields['taxonomy'] : '';
                        $is_multiple = !empty($meta_box_fields['multiple']);

                        // rp_dev_log("=== Meta Box Field Update ===");
                        // rp_dev_log("1️⃣ Field Key: {$field_key}");
                        // rp_dev_log("2️⃣ Value: " . print_r($value, true));
                        // rp_dev_log("3️⃣ Field Type: {$field_type}");          
                        // rp_dev_log('4️⃣ Taxonomy: ' . json_encode($taxonomy));
                        // rp_dev_log('5️⃣ meta_box_fields: ' . json_encode($meta_box_fields));
                        // rp_dev_log("=== DONE ===");

                        if (!empty($term_ids)) {
                            rp_dev_log("Term IDs to set: " . implode(', ', $term_ids));
                        }

                        if ($taxonomy) {
                            $term_value = null;

                            // Extract value from the object if it exists
                            if (is_array($value) && isset($value['value'])) {
                                $term_value = $value['value'];
                            } else {
                                $term_value = $value;
                            }

                            if ($term_value === null || $term_value === '') {
                                // Remove all terms if no value is provided
                                wp_set_object_terms($post_id, array(), $taxonomy);
                            } else {
                                // Handle both single and multiple values
                                if ($is_multiple) {
                                    // For multiple selection, ensure we have an array
                                    $term_ids = is_array($term_value) ? $term_value : array($term_value);
                                } else {
                                    // For single selection, take the first value if it's an array
                                    $term_ids = is_array($term_value) ? array($term_value[0]) : array($term_value);
                                }
                                
                                // Convert to integers
                                $term_ids = array_map('intval', $term_ids);

                                // Set the terms
                                wp_set_object_terms($post_id, $term_ids, $taxonomy[0]);
                            }
                        }
                    } else {
                        // Handle non-taxonomy fields
                        if ($value !== null) {
                            update_post_meta($post_id, $field_key, $value);
                        } else {
                            delete_post_meta($post_id, $field_key);
                        }
                    }
                }
            }
        }

        // Save updated post information
        $updated_posts[] = array('ID' => $post_id);
    }

    return array(
        'success' => true,
        'updated_posts' => $updated_posts // Return list of updated post IDs
    );
  }


  /**
   * Add custom fields to post object
   *
   * @param WP_Post $post Post object
   * @param array $fields Array of fields to include
   * @return WP_Post Modified post object
   */
  private static function add_custom_fields($post, $fields)
  {
    return $post;
  }

  /**
   * Get all categories
   *
   * @param array $params Parameters for filtering categories
   * @return array Categories data
   */
  public function get_post_categories($params)
  {
    $args = array(
      'type' => 'post',
      'orderby' => 'name',
      'order' => 'ASC',
      'hide_empty' => false, // false means include categories with no posts
      'hierarchical' => true,  // true means maintain hierarchy
    );

    // If taxonomy is specified, get the terms for that taxonomy
    $taxonomy = isset($params['taxonomy']) ? $params['taxonomy'] : 'category';

    $terms = get_terms(array(
      'taxonomy' => $taxonomy,
      'hide_empty' => $args['hide_empty']
    ));

    $categories = array();

    if (!is_wp_error($terms)) {
      foreach ($terms as $term) {
        $categories[] = array(
          'id' => $term->term_id,
          'name' => $term->name,
          'slug' => $term->slug,
          'parent' => $term->parent,
          'description' => $term->description,
          'count' => $term->count, // Number of posts associated with this category
          'link' => get_term_link($term)
        );
      }
    }

    return array(
      'success' => true,
      'categories' => $categories
    );
  }

  /**
   * Get all tags
   *
   * @param array $params Parameters for filtering tags
   * @return array Tags data
   */
  public function get_post_tags($params)
  {
    $args = array(
      'orderby' => 'name',
      'order' => 'ASC',
      'hide_empty' => false, // false means include tags with no posts
    );

    // If taxonomy is specified, get the terms for that taxonomy
    $taxonomy = isset($params['taxonomy']) ? $params['taxonomy'] : 'post_tag';

    $terms = get_terms(array(
      'taxonomy' => $taxonomy,
      'hide_empty' => $args['hide_empty']
    ));

    $tags = array();

    if (!is_wp_error($terms)) {
      foreach ($terms as $term) {
        $tags[] = array(
          'id' => $term->term_id,
          'name' => $term->name,
          'slug' => $term->slug,
          'description' => $term->description,
          'count' => $term->count, // Number of posts associated with this tag
          'link' => get_term_link($term)
        );
      }
    }

    return array(
      'success' => true,
      'tags' => $tags
    );
  }

  public static function search_posts_by_title($params)
  {
    $post_data = $params['keyword'];
    // Sanitize the keyword to prevent SQL injection
    $keyword = sanitize_text_field($post_data);

    // Check if the length of the keyword is 0
    if (strlen($keyword) === 0) {
      return array(
        'success' => true,
        'keyword' => $keyword, // Include the keyword in the response
        'posts' => array(), // Return an empty array for posts
      );
    }

    // Set up the query arguments
    $args = array(
      'post_type' => 'any', // Change this if you want to search other post types
      'posts_per_page' => 100, // Retrieve all matching posts
      's' => $keyword, // Search keyword
      'post_status' => 'any', // Include all post statuses
    );

    // Fetch posts based on the search criteria
    $posts = get_posts($args);

    // Filter posts to only include those where the title contains the keyword
    // The 's' parameter does not search exclusively in the title, so we add this filtering logic.
    $filtered_posts = array_filter($posts, function ($post) use ($keyword) {
      return stripos($post->post_title, $keyword) !== false; // Case-insensitive search
    });

    // Prepare the response
    if (!empty($filtered_posts)) {
      $result = array();
      foreach ($filtered_posts as $post) {
        $post_type_object = get_post_type_object($post->post_type);
        $post_type_label = $post_type_object ? $post_type_object->labels->singular_name : 'Unknown';

        $result[] = array(
          'ID' => $post->ID,
          'title' => $post->post_title,
          'status' => $post->post_status,
          'date' => $post->post_date,
          'excerpt' => wp_trim_words($post->post_content, 20), // Optional: Get an excerpt
          'permalink' => get_permalink($post->ID),
          'post_type' => $post->post_type,
          'post_type_label' => $post_type_label,
        );
      }

      return array(
        'success' => true,
        'posts' => $result,
        'keyword' => $keyword,
      );
    } else {
      return array(
        'success' => false,
        'message' => 'No posts found matching the keyword.',
      );
    }
  }

  public static function get_post_statistics()
  {
    // Initialize an array to hold the counts for each post status
    $post_status_counts = array(
      'publish' => 0,
      'draft' => 0,
      'pending' => 0,
      'private' => 0,
      'inherit' => 0,
    );

    // Get all posts
    $all_posts = get_posts(array(
      'post_type' => 'any',
      'posts_per_page' => -1, // Retrieve all posts
      'post_status' => 'any', // Include all post statuses
    ));

    // Count posts by status
    foreach ($all_posts as $post) {
      if (isset($post_status_counts[$post->post_status])) {
        $post_status_counts[$post->post_status]++;
      }
    }

    // Get monthly published post counts for all post types
    global $wpdb;
    $monthly_results = $wpdb->get_results("
        SELECT 
            DATE_FORMAT(post_date, '%Y-%m') AS month,
            COUNT(ID) AS post_count
        FROM 
            {$wpdb->posts}
        WHERE 
            post_type IN ('post', 'page', 'custom_post_type')  -- Include all relevant post types
            AND post_status = 'publish'
        GROUP BY 
            month
        ORDER BY 
            month DESC
    ");

    // Prepare the monthly published post count array
    $monthly_published_counts = array();
    foreach ($monthly_results as $row) {
      $monthly_published_counts[$row->month] = (int) $row->post_count; // Convert to integer
    }


    $weekly_results = $wpdb->get_results("
SELECT 
    YEAR(post_date) AS year,
    MONTH(post_date) AS month,
    DAY(post_date) AS day,
    COUNT(ID) AS post_count
FROM 
    {$wpdb->posts}
WHERE 
    post_type IN ('post', 'page', 'custom_post_type')  -- Include all relevant post types
    AND post_status = 'publish'
GROUP BY 
    year, month, FLOOR((DAY(post_date) - 1) / 7) + 1  -- Calculate week of the month
ORDER BY 
    year DESC, month DESC
LIMIT 5
");

    // Prepare the weekly published post count array
    $weekly_published_counts = array();
    $current_date = new \DateTime();

    // Loop through the last 5 weeks
    for ($i = 0; $i < 5; $i++) {
      $week_start = clone $current_date;
      $week_start->modify('-' . $i . ' week');

      // Format the week label as "Year-Month-Week of Month"
      $week_label = sprintf(
        "%d-%d-%d",
        $week_start->format('Y'),
        $week_start->format('n'),
        ceil($week_start->format('j') / 7) // Calculate week of the month based on the day of the month
      );

      // Check if the week exists in the results
      $found = false;
      foreach ($weekly_results as $row) {
        if (
          $row->year == $week_start->format('Y') &&
          $row->month == $week_start->format('n') &&
          floor(($row->day - 1) / 7) + 1 == ceil($week_start->format('j') / 7)
        ) {
          $weekly_published_counts[$week_label] = (int) $row->post_count; // Convert to integer
          $found = true;
          break;
        }
      }

      // If not found, set count to 0
      if (!$found) {
        $weekly_published_counts[$week_label] = 0;
      }
    }



    // Prepare the statistics array
    $statistics = array(
      'total_posts' => count($all_posts),
      'post_status_counts' => $post_status_counts,
      'monthly_published_counts' => $monthly_published_counts, // Add monthly published counts to statistics
      'weekly_published_counts' => $weekly_published_counts, // Add weekly published counts to statistics
    );

    return array(
      'success' => true,
      'statistics' => $statistics,
    );
  }

  public static function delete_post_meta($params)
  {
    // Check if 'post_id' and 'meta_key' are provided
    if (!isset($params['post_id']) || !isset($params['meta_key'])) {
      return array('success' => false, 'message' => 'Post ID and meta key are required.');
    }

    $post_id = intval($params['post_id']);
    $meta_key = sanitize_text_field($params['meta_key']);

    // Delete the post meta
    $deleted = delete_post_meta($post_id, $meta_key);

    if ($deleted) {
      return array('success' => true, 'message' => 'Post meta deleted successfully.');
    } else {
      return array('success' => false, 'message' => 'No post meta found for the given key.');
    }
  }

  public static function delete_post_meta_by_key($params)
  {
    // Check if 'meta_key' and 'post_type' are provided
    if (!isset($params['meta_key'])) {
      return array('success' => false, 'message' => 'Meta key is required.');
    }

    $meta_key = sanitize_text_field($params['meta_key']);
    $post_type = isset($params['post_type']) ? sanitize_text_field($params['post_type']) : 'any'; // 기본값은 'any'

    // Get all posts of the specified post type
    $all_posts = get_posts(array(
      'post_type' => $post_type,
      'posts_per_page' => -1, // Retrieve all posts
      'post_status' => 'any', // Include all post statuses
    ));

    $deleted_count = 0;

    // Loop through all posts and delete the specified meta key
    foreach ($all_posts as $post) {
      $deleted = delete_post_meta($post->ID, $meta_key);
      if ($deleted) {
        $deleted_count++;
      }
    }

    return array(
      'success' => true,
      'message' => "{$deleted_count} posts updated. Meta key '{$meta_key}' deleted from post type '{$post_type}'.",
    );
  }


  public function get_all_post_meta_paths($params)
  {
    // Check if 'post_type' is provided
    if (!isset($params['post_type'])) {
      return array('success' => false, 'message' => 'Post type is required.');
    }

    $post_type = sanitize_text_field($params['post_type']);

    // Get all posts of the specified post type
    $all_posts = get_posts(array(
      'post_type' => $post_type,
      'posts_per_page' => -1, // Retrieve all posts
      'post_status' => 'any', // Include all post statuses
    ));

    $meta_paths = array();
    $unique_paths = array();  // Array to store unique paths

    // Loop through all posts and get their meta data
    foreach ($all_posts as $post) {
      $post_meta = get_post_meta($post->ID);

      foreach ($post_meta as $key => $value) {
        // Exclude paths that start with '_'
        if (strpos($key, '_') === 0) {
          continue; // Skip if the key starts with '_'
        }

        // Only add unique paths
        if (!in_array($key, $unique_paths)) {
          $unique_paths[] = $key;  // Add path to the array
        }
        $meta_paths[] = array(
          'post_id' => $post->ID,
          'path' => $key,
          'value' => $value[0] // Assuming you want the first value for each key
        );
      }
    }

    return array(
      'success' => true,
      'post_type' => $post_type,
      'meta_data' => $meta_paths,
      'meta_paths' => $unique_paths  // Add unique path array
    );
  }

  public static function bulk_actions($params)
  {
    // Check if 'post_ids' and 'action' are provided
    if (!isset($params['post_ids']) || !is_array($params['post_ids']) || empty($params['post_ids'])) {
      return array('success' => false, 'message' => 'Post IDs are required.');
    }

    if (!isset($params['action'])) {
      return array('success' => false, 'message' => 'Action type is required.');
    }

    $post_ids = $params['post_ids'];
    $action = $params['action'];
    $results = array();

    foreach ($post_ids as $post_id) {
      switch ($action) {
        case 'trash':
          // Move the post to trash
          if (wp_trash_post($post_id)) {
            $results[] = array('ID' => $post_id, 'status' => 'trashed');
          } else {
            $results[] = array('ID' => $post_id, 'status' => 'failed', 'message' => 'Failed to trash post.');
          }
          break;

        case 'restore':
          // Restore the post from trash
          $post = get_post($post_id);
          if ($post && $post->post_status === 'trash') {
            if (
              wp_update_post(array(
                'ID' => $post_id,
                'post_status' => 'publish', // or any other status you want to restore to
              ))
            ) {
              $results[] = array('ID' => $post_id, 'status' => 'restored');
            } else {
              $results[] = array('ID' => $post_id, 'status' => 'failed', 'message' => 'Failed to restore post.');
            }
          } else {
            $results[] = array('ID' => $post_id, 'status' => 'failed', 'message' => 'Post is not in trash or does not exist.');
          }
          break;

        case 'delete':
          // Permanently delete the post
          if (wp_delete_post($post_id, true)) {
            $results[] = array('ID' => $post_id, 'status' => 'deleted');
          } else {
            $results[] = array('ID' => $post_id, 'status' => 'failed', 'message' => 'Failed to delete post.');
          }
          break;

        default:
          return array('success' => false, 'message' => 'Invalid action type.');
      }
    }

    return array(
      'success' => true,
      'results' => $results // Return the results of the actions performed
    );
  }

  public static function create_post($params)
  {
    // Extract basic post data
    $post_data = array(
      'post_title' => $params['post_title'],
      'post_content' => isset($params['post_content']) ? $params['post_content'] : '',
      'post_status' => $params['post_status'],
      'post_type' => $params['post_type'], // Use post_type from params
      'post_excerpt' => isset($params['post_excerpt']) ? $params['post_excerpt'] : (isset($params['excerpt']) ? $params['excerpt'] : ''), // Optional excerpt
      'guid' => isset($params['link']) ? $params['link'] : '', // Optional link
    );

    // Insert the post into the database
    $post_id = wp_insert_post($post_data);

    // Check for errors
    if (is_wp_error($post_id)) {
      return $post_id; // Return the error
    }

    // Process categories
    if (!empty($params['categories'])) {
      $category_ids = [];
      foreach ($params['categories'] as $category) {
        $term = term_exists($category['value'], 'category');
        if ($term) {
          $category_ids[] = $term['term_id'];
        } else {
          $new_term = wp_insert_term($category['value'], 'category');
          if (!is_wp_error($new_term)) {
            $category_ids[] = $new_term['term_id'];
          }
        }
      }
      wp_set_post_categories($post_id, $category_ids);
    }

    // Process tags
    if (!empty($params['tags'])) {
      $tag_ids = [];
      foreach ($params['tags'] as $tag) {
        $term = term_exists($tag['name'], 'post_tag');
        if ($term) {
          // Use the existing tag's name instead of its ID
          $tag_ids[] = $tag['name'];
        } else {
          $new_term = wp_insert_term($tag['name'], 'post_tag');
          if (!is_wp_error($new_term)) {
            $tag_ids[] = $new_term['term_id'];
          }
        }
      }
      wp_set_post_tags($post_id, $tag_ids);
    }


      // Helper: apply fields (custom, ACF, meta box)
  function apply_fields($post_id, $fields, $type = 'meta') {
    foreach ($fields as $field) {
      $key = $field['path'];
      $value = $field['value'];
      $field_type = $field['type'] ?? '';

      if ($value === null || $value === '' || $value === 'Invalid Date') {
       continue;
       }

      if ($type === 'meta' && str_starts_with($field_type, 'taxonomy-')) {
        $meta_box_fields = rwmb_get_field_settings($key, [], $post_id);
        $field_type = isset($meta_box_fields['type']) ? $meta_box_fields['type'] : '';

        if ($field_type === 'taxonomy') {
          $taxonomy = isset($meta_box_fields['taxonomy']) ? $meta_box_fields['taxonomy'] : '';
          $is_multiple = !empty($meta_box_fields['multiple']);

          if ($taxonomy) {
            $term_value = null;

            // Extract value from the object if it exists
            if (is_array($value) && isset($value['value'])) {
                $term_value = $value['value'];
            } else {
                $term_value = $value;
            }

            if ($term_value === null || $term_value === '') {
                // Remove all terms if no value is provided
                wp_set_object_terms($post_id, array(), $taxonomy);
            } else {
                // Handle both single and multiple values
                if ($is_multiple) {
                    // For multiple selection, ensure we have an array
                    $term_ids = is_array($term_value) ? $term_value : array($term_value);
                } else {
                    // For single selection, take the first value if it's an array
                    $term_ids = is_array($term_value) ? array($term_value[0]) : array($term_value);
                }
                
                // Convert to integers
                $term_ids = array_map('intval', $term_ids);

                // Set the terms
                wp_set_object_terms($post_id, $term_ids, $taxonomy[0]);
            }
          }
        }

          } else if ($type === 'acf' && function_exists('update_field')) {
            update_field($key, $value, $post_id);
          } else {
            update_post_meta($post_id, $key, $value);
          }
    }
  }

  // Process custom fields
  if (!empty($params['custom_fields'])) {
    apply_fields($post_id, $params['custom_fields'], 'meta');
  }

  // Process ACF fields
  if (!empty($params['acf_fields'])) {
    apply_fields($post_id, $params['acf_fields'], 'acf');
  }

  // Process Meta Box fields
  if (!empty($params['meta_box_fields'])) {
    apply_fields($post_id, $params['meta_box_fields'], 'meta');
  }

    return array(
      'success' => true,
      'post_id' => $post_id,
  );
  }

  public static function get_categories()
  {
    $categories = get_terms(array(
      'taxonomy' => 'category',
      'hide_empty' => false,
    ));

    return $categories;
  }



public static function get_post_fields_by_type($params)
{
    if (!function_exists('get_posts')) {
        return [];
    }

    $post_type = sanitize_text_field($params);
    $posts = get_posts([
        'post_type'   => $post_type,
        'post_status' => 'any',
        'numberposts' => -1,
        'fields'      => 'ids', 
    ]);

    

    if (empty($posts)) {
        return [];
    }

    $exclude_keys = ['_wp_old_date', '_pingme', '_encloseme', '_edit_lock', '_edit_last', '_', '_wp_old_slug'];
    $all_meta_keys = [];


    foreach ($posts as $post_id) {
        $meta = get_post_meta($post_id);
        foreach ($meta as $key => $value) {
            if (!in_array($key, $exclude_keys) && !in_array($key, $all_meta_keys)) {
              $all_meta_keys[] = $key;
            }
        }
    }

  
    
    // Basic Field + Post Meta Key + Category/Tag
    $fields = ['ID', 'post_title', 'post_content', 'post_status', 'post_date', 'category', 'tag', 'post_author', 'post_excerpt', 'permalink'];
    $fields = array_merge($fields, $all_meta_keys);
    
    return $fields;
  }

  private static function encrypt_json_string($json, $password)
{
    $algorithm = 'aes-256-cbc';
    $salt = 'salt'; // Must match client-side decryption
    $key = openssl_pbkdf2($password, $salt, 32, 10000);
    $iv = openssl_random_pseudo_bytes(16);

    $encrypted = openssl_encrypt($json, $algorithm, $key, OPENSSL_RAW_DATA, $iv);

    return json_encode([
        'iv' => bin2hex($iv),
        'data' => bin2hex($encrypted),
        'encrypted' => true
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}


  public static function export_posts_by_type_to_json($params)
  {
      $post_type = $params['post_type'];
      $selected_fields = $params['selected_fields'];
      $field_labels = $params['field_labels'];
      $post_ids        = $params['post_ids'] ?? [];
      $password = $params['password'] ?? null;
      $return_type     = $params['return_type'] ?? 'link';

      if (!function_exists('get_posts')) {
          return;
      }
  
      $post_type = sanitize_text_field($post_type);
  
      $posts = get_posts([
          'post_type'   => $post_type,
          'post_status' => 'any',
          'numberposts' => -1,
          'post__in'    => !empty($post_ids) ? $post_ids : null,
      ]);
  
      if (empty($posts)) {
          return;
      }
  
      $exclude_keys = ['_wp_old_date', '_pingme', '_encloseme', '_edit_lock', '_edit_last', '_', '_wp_old_slug'];
  
      $export_data = [];
  
      foreach ($posts as $post) {
        $row = [];
        $used_labels = [];
        
        foreach ($selected_fields as $field) {
          $value = '';
  
          switch ($field) {
              case 'ID':
              case 'post_title':
              case 'post_content':
              case 'post_status':
              case 'post_date':
              case 'post_excerpt':
              case 'post_name':
                  $value = $post->$field;
                  break;
              case 'post_author':
                  $value = get_the_author_meta('display_name', $post->post_author);
                  break;
              case 'permalink':
                  $value = get_permalink($post->ID);
                  break;
              case 'category':
                  $value = implode(', ', wp_get_post_terms($post->ID, 'category', ['fields' => 'names']));
                  break;
              case 'tag':
                  $value = implode(', ', wp_get_post_terms($post->ID, 'post_tag', ['fields' => 'names']));
                  break;
              default:
                  if (!in_array($field, $exclude_keys)) {
                    $value = get_post_meta($post->ID, $field, true);
                  }                
                  break;
          }
  
          // 라벨 처리 (중복 시 숫자 붙이기)
            $base_label = $field_labels[$field] ?? $field;
            $label = $base_label;
            $i = 1;
            while (isset($used_labels[$label])) {
                $label = "{$base_label} ({$i})";
                $i++;
            }
            $used_labels[$label] = true;
            $row[$label] = $value;
        }
  
        $export_data[] = $row;
      }
  
      $json_data = json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);


       // 암호화 처리
       if (!empty($password)) {
         $json_data = self::encrypt_json_string($json_data, $password);
       }

      if ($return_type === 'json') {
         // JSON 바로 반환
         return $json_data;
       } else {
         // 파일로 저장 후 링크 반환
         $upload_dir = wp_upload_dir();
         $filename = "{$post_type}-export.json";
         $file_path = $upload_dir['basedir'] . '/' . $filename;
         $file_url  = $upload_dir['baseurl'] . '/' . $filename;
      
         file_put_contents($file_path, $json_data);
         return $file_url;
       }
  }
  


// SELECTED FIELD
public static function export_posts_by_type_to_csv($params)
{
    $post_type = $params['post_type'];
    $selected_fields = $params['selected_fileds'];
    $field_labels = $params['field_labels'];
    $post_ids = $params['post_ids'] ?? [];



    if (!function_exists('get_posts')) {
        return;
    }

    $post_type = sanitize_text_field($post_type);
    $posts = get_posts([
        'post_type'   => $post_type,
        'post_status' => 'any',
        'numberposts' => -1,
        'post__in'    => !empty($post_ids) ? $post_ids : null,
    ]);

    if (empty($posts)) {
        return;
    }

    $exclude_keys = ['_wp_old_date', '_pingme', '_encloseme', '_edit_lock', '_edit_last', '_', '_wp_old_slug'];

    $upload_dir = wp_upload_dir();
    $file_path = $upload_dir['basedir'] . "/{$post_type}-export.csv";
    $file = fopen($file_path, 'w');

    
    // Set Headers /Get Header labels: retrieve labels for header from Users (field_labels)
    $headers = [];
    foreach ($selected_fields as $field) {
        // if 'field_labels' has the field, use them as labels Or use it's field.
        $headers[] = isset($field_labels[$field]) ? $field_labels[$field] : $field;
    };

    fputcsv($file, $headers);

    foreach ($posts as $post) {
        $row = [];

        $meta = get_post_meta($post->ID);

        foreach ($selected_fields as $field) {
            switch ($field) {
                case 'ID':
                    $row[] = $post->ID;
                    break;
                case 'post_title':
                case 'post_content':
                case 'post_status':
                case 'post_date':
                case 'post_excerpt':
                    $row[] = $post->{$field};
                    break;
                case 'post_author':
                    $author = get_the_author_meta('display_name', $post->post_author);
                    $row[] = $author;
                    break;
                case 'post_name':
                    $row[] = $post->post_name;
                    break;
                case 'permalink':
                    $row[] = get_permalink($post->ID);
                    break;
                case 'category':
                    $category_terms = wp_get_post_terms($post->ID, 'category', ['fields' => 'names']);
                    $row[] = implode(', ', $category_terms);
                    break;
                case 'tag':
                    $tag_terms = wp_get_post_terms($post->ID, 'post_tag', ['fields' => 'names']);
                    $row[] = implode(', ', $tag_terms);
                    break;
                default:
                    if (!in_array($field, $exclude_keys)) {
                        $row[] = isset($meta[$field]) ? maybe_serialize($meta[$field][0]) : '';
                    } else {
                        $row[] = '';
                    }
                    break;
            }
        }

        fputcsv($file, $row);
    }

    fclose($file);

    return $upload_dir['baseurl'] . "/{$post_type}-export.csv";
}

private static function apply_field($post_id, $type, $key, $value, $field_type)
{
  if ($value === null || $value === '' || $value === 'Invalid Date') {
    return;
  }


  if ($type === 'meta' && str_starts_with($field_type, 'taxonomy-')) {
    $meta_box_fields = rwmb_get_field_settings($key, [], $post_id);
    $field_type = isset($meta_box_fields['type']) ? $meta_box_fields['type'] : '';

    if ($field_type === 'taxonomy') {
      $taxonomy = isset($meta_box_fields['taxonomy']) ? $meta_box_fields['taxonomy'] : '';
      $is_multiple = !empty($meta_box_fields['multiple']);

      if ($taxonomy) {
        $term_value = null;

        // Extract value from the object if it exists
        if (is_array($value) && isset($value['value'])) {
            $term_value = $value['value'];
        } else {
            $term_value = $value;
        }

        if ($term_value === null || $term_value === '') {
            // Remove all terms if no value is provided
            wp_set_object_terms($post_id, array(), $taxonomy);
        } else {
            // Handle both single and multiple values
            if ($is_multiple) {
                // For multiple selection, ensure we have an array
                $term_ids = is_array($term_value) ? $term_value : array($term_value);
            } else {
                // For single selection, take the first value if it's an array
                $term_ids = is_array($term_value) ? array($term_value[0]) : array($term_value);
            }
            
            // Convert to integers
            $term_ids = array_map('intval', $term_ids);

            // Set the terms
            wp_set_object_terms($post_id, $term_ids, $taxonomy[0]);
        }
      }
    }

      } else if ($type === 'acf' && function_exists('update_field')) {
        update_field($key, $value, $post_id);
      } else {
        update_post_meta($post_id, $key, $value);
      }
}


    public static function import_posts_by_post_type($params)
{
    $post_type = $params['post_type']; // The post type to create (e.g., posts, pages)
    $posts = $params['posts']; // Array of post data
    $post_fields = $params['post_fields']; // Mapped key-value (object key-actual wp key) pairs of post fields (core fields and metadata)
    
    $createdPosts = []; // Array to store created post IDs

    // Loop through each post data
    foreach ($posts as $post) {
        // Basic WordPress post data array (dynamically populated)
        $new_post = array(
            'post_status'  => 'draft', // 상태는 필요에 따라 'publish', 'draft' 등으로 변경 가능
            'post_type'    => $post_type, // "page" 또는 "post"
        );

        // Variables for category and tag
        $categories = [];
        $tags = [];
        
        // Array to store meta data
        $meta = [];

        // Array to store ACF data
        $acf = [];

        // Array to store Meta Box data
        $meta_box = [];

        // Process each post field
        foreach ($post_fields as $field) {
            $field_key = $field['fieldKey'];
            $field_type = $field['type']; // wp | meta(=post meta) | acf | metaBox
            $data_key = $field['dataKey'];
            $data_type = isset($field['fieldType']) ? $field['fieldType'] : null; // textarea | taxonomy | select ....

            // Skip if the field is 'ID'
            if ($field_key == 'ID') {
              continue;
            }

            if ($field_type == 'wp') {
                // Handle WordPress core fields
                if (isset($post[$data_key])) {
                    $new_post[$field_key] = $post[$data_key];
                }
            } elseif ($field_type == 'meta') {
                // Handle custom meta fields
                if (isset($post[$data_key])) {
                    $meta[$field_key] = $post[$data_key];
                }
            } elseif ($field_type == 'acf') {
              // Handle custom meta fields
              if (isset($post[$data_key])) {
                    $acf[$field_key] = [
                      'value' => $post[$data_key],
                      'type' => $data_type,
                    ];
              }
            } elseif ($field_type == 'metaBox') {
              // Handle custom meta fields
              if (isset($post[$data_key])) {
                $meta_box[$field_key] = [
                    'value' => $post[$data_key],
                    'type' => $data_type,
                ];
              }
            }
        }


        // rp_dev_log("=== Import Data ===");
        // rp_dev_log('1️⃣ post meta: ' . json_encode($meta));
        // rp_dev_log('2️⃣ ACF: ' . json_encode($acf));
        // rp_dev_log('3️⃣ meta_box_fields: ' . json_encode($meta_box));
        // rp_dev_log("=== DONE ===");


        // Insert the post into the database
        $post_id = wp_insert_post($new_post);


        // Handle category
        if (isset($post['category'])) {
          $categories = explode(',', $post['category']);
        }

        if (!empty($categories)) {
            $category_ids = [];
            foreach ($categories as $category) {
                $term = term_exists($category, 'category');
                if ($term) {
                    // Use existing category ID
                    $category_ids[] = $term['term_id'];
                } else {
                    // Create a new category
                    $new_term = wp_insert_term($category, 'category');
                    if (!is_wp_error($new_term)) {
                        $category_ids[] = $new_term['term_id'];
                    }
                }
            }
            wp_set_post_categories($post_id, $category_ids);
        }


        // Handle tag
        if (isset($post['tag'])) {
          $tags = explode(',', $post['tag']);
        }

        if (!empty($tags)) {
          $tag_ids = [];
          foreach ($tags as $tag) {
              $term = term_exists($tag, 'post_tag');
              if ($term) {
                  // Use the existing tag name
                  $tag_ids[] = $tag;  // use $tag instead of term['name']
              } else {
                  // Create a new tag
                  $new_term = wp_insert_term($tag, 'post_tag');
                  if (!is_wp_error($new_term)) {
                      $tag_ids[] = $tag;  // use $tag instead of term['name']
                  }
              }
          }
          wp_set_post_tags($post_id, $tag_ids);
        }

        // Handle post meta data
        if (!empty($meta)) {
            foreach ($meta as $key => $value) {
                update_post_meta($post_id, $key, $value);
            }
        }

        // Handle ACF data
        if (!empty($acf) && function_exists('update_field')) {
            foreach ($acf as $key => $value) {
              $input_value = $value['value'];
              $input_type = $value['type'];

            
              // rp_dev_log('1️⃣ input value: ' . json_encode($input_value));
              // rp_dev_log("2️⃣ input type: {$input_type}");
              // rp_dev_log("3️⃣ key: {$key}");
              

              self::apply_field($post_id, 'acf', $key, $input_value, $input_type);
            }
        }

        // Handle MetaBox data
        if (!empty($meta_box) && function_exists('rwmb_get_registry')) {
          foreach ($meta_box as $key => $value) {
            $input_value = $value['value'];
            $input_type = $value['type'];
  
            self::apply_field($post_id, 'acf', $key, $input_value, $input_type);
          }
        }
            
        // Add the created post ID to the result array
        if ($post_id) {
            $createdPosts[] = $post_id;
        }
    }

    return $createdPosts; // Return array of created post IDs
}


public function get_acf_field_groups($params)
{
  if (!function_exists('acf_get_field_groups')) {
    return array('success' => false, 'message' => 'ACF plugin is not active');
  }

  if (empty($params['post_type'])) {
    return array('success' => false, 'message' => 'post_type is required');
  }

  $post_type = $params['post_type'];

  // Get Field Groups related to post_type
  $field_groups = acf_get_field_groups([
    'post_type' => $post_type
  ]);

  $results = [];

  foreach ($field_groups as $group) {
    $fields = acf_get_fields($group['key']);
    $field_list = [];

    if ($fields) {
      foreach ($fields as $field) {
        $field_data = [
          'label' => $field['label'],
          'name' => $field['name'],
          'type' => $field['type'],
          'key' => $field['key']
        ];

        // date picker, date time picker
        if (in_array($field['type'], ['date_picker', 'date_time_picker'])) {
          $field_data['format'] = $field['return_format'] ?? null;
        }

        // select
        if ($field['type'] === 'select' && !empty($field['choices'])) {
          $options = [];
          foreach ($field['choices'] as $option_value => $option_label) {
            $options[] = [
              'label' => $option_label,
              'value' => $option_value
            ];
          }
          $field_data['options'] = $options;
        }

        // taxonomy
        if ($field['type'] === 'taxonomy' && !empty($field['taxonomy'])) {
          $options = [];

          // 기본적으로 모든 term을 불러옵니다
          $terms = get_terms([
            'taxonomy' => $field['taxonomy'],
            'hide_empty' => false,
          ]);

          if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
              $options[] = [
                'label' => $term->name,
                'value' => $term->term_id
              ];
            }
          }

          $field_data['options'] = $options;
        }

        $field_list[] = $field_data;
        
      }
    }

    $results[] = [
      'group_title' => $group['title'],
      'group_key' => $group['key'],
      'fields' => $field_list
    ];
  }

  return array('success' => true, 'field_groups' => $results);
}


public function get_meta_box_fields($params)
{
  if (!function_exists('rwmb_get_registry')) {
    return ['success' => false, 'message' => 'Meta Box plugin is not active'];
  }

  if (empty($params['post_type'])) {
    return array('success' => false, 'message' => 'post_type is required');
  }

  $post_type = $params['post_type'];

  $meta_boxes = rwmb_get_registry('meta_box')->all();
  $results = [];

  foreach ($meta_boxes as $box) {
    $box_post_types = $box->post_types ?? [];

    if (!in_array($post_type, (array) $box_post_types)) {
      continue;
    }

    $fields = $box->fields ?? [];

    $field_list = [];

    foreach ($fields as $field) {
      $type = $field['type'] ?? '';

      // taxonomy
      $is_taxonomy = false;
      $taxonomy_options = [];
      if ($type === 'taxonomy') {
        $is_taxonomy = true;
        $is_multiple = !empty($field['multiple']) ? 'multiple' : 'single';
        $type = "taxonomy-$is_multiple";

        $taxonomy_name = $field['taxonomy'] ?? null;
        if ($taxonomy_name) {
          $terms = get_terms([
            'taxonomy' => $taxonomy_name,
            'hide_empty' => false,
          ]);

          if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
              $taxonomy_options[] = [
                'label' => $term->name,
                'value' => $term->term_id,
              ];
            }
          }
        }
      }

      // select
      $select_options = [];
      if ($field['type'] === 'select' && !empty($field['options'])) {
        foreach ($field['options'] as $option_value => $option_label) {
          $select_options[] = [
            'label' => $option_label,
            'value' => $option_value,
          ];
        }
      }

      $field_data = [
        'label' => $field['name'] ?? '',
        'id'    => $field['id'] ?? '',
        'type'  => $type,
      ];

      if (!empty($select_options)) {
        $field_data['options'] = $select_options;
      }

      if (!empty($taxonomy_options)) {
        $field_data['options'] = $taxonomy_options;
      }

      $field_list[] = $field_data;
    }

    $results[] = [
      'group_id' => $box->id ?? '',
      'group_title' => $box->title ?? '',
      'fields' => $field_list,
    ];
  }

  return ['success' => true, 'field_groups' => $results];
}


private function is_bricks_active()
{
  return defined('BRICKS_VERSION') && function_exists('bricks_is_builder');
}


public function get_bricks_enabled_post_types($params)
{
    // Check if post_type parameter is provided
    if (empty($params['post_type'])) {
        return array('success' => false, 'message' => 'post_type is required');
    }

    $post_type = $params['post_type'];

    // Check if Bricks builder is active
    if (!$this->is_bricks_active()) {
        return array('success' => false, 'message' => 'Bricks builder is not active');
    }

    // Get the list of post types enabled for Bricks
    $enabled_post_types = get_option('bricks_post_types', array('page', 'post'));

    // Check if the given post_type is enabled in Bricks
    $is_enabled = in_array($post_type, $enabled_post_types);

    $results = array(
        'post_type' => $post_type,
        'enabled' => $is_enabled,
        'reason' => $is_enabled
            ? 'Bricks builder is enabled for this post type'
            : 'Bricks builder is not enabled for this post type'
    );

    return array('success' => true, 'data' => $results);
}

/**
 * Get a post by its ID.
 *
 * @param array $params An associative array containing:
 *   - post_id (int): The ID of the post to retrieve.
 * @return array An associative array containing:
 *   - success (bool): Indicates whether the retrieval was successful.
 *   - post (array|null): The post data if found, or null if not found.
 *   - message (string, optional): A message providing additional information.
 */
// public function get_post_by_id($params)
// {
//     // Validate the input
//     if (!isset($params['post_id']) || !is_int($params['post_id']) || $params['post_id'] <= 0) {
//         return array('success' => false, 'message' => 'Invalid post ID provided.');
//     }

//     $post_id = $params['post_id']; // Extract post ID from params

//     // Retrieve the post
//     $post = get_post($post_id);
//     if (!$post) {
//         return array('success' => false, 'message' => 'Post not found.');
//     }

//     // Prepare the post data for the response
//     return array(
//         'success' => true,
//         'post' => array(
//             'ID' => $post->ID,
//             'post_title' => $post->post_title,
//             'post_content' => $post->post_content,
//             'post_date' => $post->post_date,
//             'post_modified' => $post->post_modified,
//             'post_type' => $post->post_type,
//         )
//     );
// }


/**
 * Get posts by their IDs.
 *
 * This method retrieves multiple posts based on an array of post IDs.
 *
 * @param array $params An associative array containing:
 *   - post_id (int[]): An array of post IDs to retrieve.
 * @return array An associative array containing:
 *   - success (bool): Indicates whether the retrieval was successful.
 *   - posts (array): An array of post data if found, or an empty array if no posts were found.
 *   - message (string, optional): A message providing additional information, especially in case of failure.
 */
public function get_post_by_id($params)
{
    // Validate the input
    if (!isset($params['post_id']) || !is_array($params['post_id'])) {
        return array('success' => false, 'message' => 'Invalid post ID(s) provided.');
    }

    $post_ids = array_filter($params['post_id'], 'is_int'); // Filter to ensure all IDs are integers
    if (empty($post_ids)) {
        return array('success' => false, 'message' => 'No valid post IDs provided.');
    }

    $posts = array();
    foreach ($post_ids as $post_id) {
        $post = get_post($post_id);
        if ($post) {
            $posts[] = array(
                'ID' => $post->ID,
                'post_title' => $post->post_title,
                'post_content' => $post->post_content,
                'post_date' => $post->post_date,
                'post_modified' => $post->post_modified,
                'post_type' => $post->post_type,
            );
        }
    }

    return array(
        'success' => true,
        'posts' => $posts,
    );
}

}

new Posts();




