<?php

// TODO: to return inside get_media response simmilar to this structure:
/**
 *  {
 *    hash: '',
 *    base_url: '', example: 'rightplace.com' or 'localhost:10010' or even 'localhost' i think get_site_url(); will solve this
 *    meta_data: {
 *     created_at: '',
 *     modified_at: '',
 *     size: '',
 *     mime_type: '',
 *     extension: '',
 *     filename: '',
 *     basename: '',
 *     dirname: '',
 *     path: '',
 *     url: '',
 *     id: '',
 *     alt: '',
 *     title: '',
 *     caption: '',
 *     description: '',
 *     ... and another important information
 *  }
 * }
 * 
 * 
 */



namespace Rightplace\Features;


// params for media
class Media_Sync {
    private $rightplace_folder_taxonomy = 'rightplace_folder';
    private $happyfiles_folder_taxonomy = 'happyfiles_category';

    public function __construct() {
        // Register the RightPlace Folder taxonomy
        add_action('init', array($this, 'register_folder_taxonomy'));

        // Hook into attachment upload to generate hash for all file types
        add_action('add_attachment', array($this, 'generate_media_hash'));

        // Add the hash field to the media details screen
        add_filter('attachment_fields_to_edit', array($this, 'add_hash_to_media_details'), 10, 2);

        // Media filters
        add_filter('rightplace_action_filter/media/getMediaList', array($this, 'get_media_list'));
        add_filter('rightplace_action_filter/media/getMediaFilterOptions', array($this, 'get_media_filter_options'));
        add_filter('rightplace_action_filter/media/updateMedia', array($this, 'update_media'));
        add_filter('rightplace_action_filter/media/getMediaDetailById', array($this, 'get_media_detail_by_id'));
        add_filter('rightplace_action_filter/media/deleteMedia', array($this, 'delete_media'));
        add_filter('rightplace_action_filter/media/deleteMediaBulk', array($this, 'delete_media_bulk'));
        add_filter('rightplace_action_filter/media/getMediaCount', array($this, 'get_media_count'));
        add_filter('rightplace_action_filter/media/replaceMedia', array($this, 'replace_media'));

        // Folder filters
        add_filter('rightplace_action_filter/media/createFolder', array($this, 'create_folder'));
        add_filter('rightplace_action_filter/media/updateFolder', array($this, 'update_folder'));
        add_filter('rightplace_action_filter/media/deleteFolder', array($this, 'delete_folder'));
        add_filter('rightplace_action_filter/media/getFolderTree', array($this, 'get_folder_tree'));
        add_filter('rightplace_action_filter/media/getFolder', array($this, 'get_folder'));
        add_filter('rightplace_action_filter/media/searchFolders', array($this, 'search_folders'));
        add_filter('rightplace_action_filter/media/getMediaInFolder', array($this, 'get_media_in_folder'));
        add_filter('rightplace_action_filter/media/addMediaToFolder', array($this, 'add_media_to_folder'));
        add_filter('rightplace_action_filter/media/removeMediaFromFolder', array($this, 'remove_media_from_folder'));
        add_filter('rightplace_action_filter/media/moveMedia', array($this, 'move_media'));
        add_filter('rightplace_action_filter/media/copyMediaToFolder', array($this, 'copy_media_to_folder'));
        add_filter('rightplace_action_filter/media/getFoldersForMedia', array($this, 'get_folders_for_media'));
    }

    /**
     * Register the RightPlace Folder taxonomy
     */
    public function register_folder_taxonomy() {
        $labels = array(
            'name'              => _x('RightPlace Folders', 'taxonomy general name', 'rightplace-client'),
            'singular_name'     => _x('RightPlace Folder', 'taxonomy singular name', 'rightplace-client'),
            'search_items'      => __('Search RightPlace Folders', 'rightplace-client'),
            'all_items'         => __('All RightPlace Folders', 'rightplace-client'),
            'parent_item'       => __('Parent RightPlace Folder', 'rightplace-client'),
            'parent_item_colon' => __('Parent RightPlace Folder:', 'rightplace-client'),
            'edit_item'         => __('Edit RightPlace Folder', 'rightplace-client'),
            'update_item'       => __('Update RightPlace Folder', 'rightplace-client'),
            'add_new_item'      => __('Add New RightPlace Folder', 'rightplace-client'),
            'new_item_name'     => __('New RightPlace Folder Name', 'rightplace-client'),
            'menu_name'         => __('RightPlace Folders', 'rightplace-client'),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'rightplace-folder'),
            'show_in_rest'      => true,
            'rest_base'         => 'rightplace-folders',
        );

        register_taxonomy($this->rightplace_folder_taxonomy, 'attachment', $args);
    }

    /**
     * Get the appropriate taxonomy based on folder type
     */
    private function get_folder_taxonomy($type = 'rightplace') {
        switch ($type) {
            case 'happyfile':
            case 'happyfiles':
                return $this->happyfiles_folder_taxonomy;
            case 'rightplace':
            default:
                return $this->rightplace_folder_taxonomy;
        }
    }

    // Generate and save the SHA-256 hash for the uploaded media
    public function generate_media_hash($attachment_id) {
        // Get the file path of the uploaded media
        $file_path = get_attached_file($attachment_id);

        // Check if the file exists
        if (file_exists($file_path)) {
            // Generate the SHA-256 hash of the file
            $hash = hash_file('sha256', $file_path);

            // Save the hash as post meta
            if ($hash) {
                update_post_meta($attachment_id, '_sha256_hash', $hash);
            }
        }
    }

    // Add the hash field to the media details screen
    public function add_hash_to_media_details($form_fields, $post) {
        // Get the stored hash
        $hash = get_post_meta($post->ID, '_sha256_hash', true);
    
        // If no hash exists, generate it on-the-fly and save it
        if (!$hash) {
            $file_path = get_attached_file($post->ID);
            if (file_exists($file_path)) {
                $hash = hash_file('sha256', $file_path);
                update_post_meta($post->ID, '_sha256_hash', $hash);
            }
        }
    
        // Add the hash field (display as plain text, not input)
        $form_fields['sha256_hash'] = array(
            'label' => 'SHA-256 Hash',
            'input' => 'html', // Use 'html' instead of 'text' to render plain HTML
            'html'  => '<strong>' . esc_html($hash) . '</strong>', // Display the hash as plain text
            'helps' => 'This is the unique SHA-256 hash of the file.',
        );
    
        return $form_fields;
    }

    public function get_media($params) {
        

    }

    public function get_media_filter_options($params) {
        // Get available media types with counts
        $type_options = $this->get_media_type_options();
        
        // Get available date ranges
        $date_options = $this->get_date_range_options();
        
        return [
            'success' => true,
            'data' => [
                'media_types' => $type_options,
                'date_ranges' => $date_options
            ]
        ];
    }

    private function get_media_type_options() {
        $types = [
            'all' => 'All Media',
            'images' => 'Images',
            'videos' => 'Videos', 
            'documents' => 'Documents',
            'archives' => 'Archives'
        ];
        
        $type_options = [];
        
        foreach ($types as $type_key => $type_label) {
            $count_params = ['type' => $type_key];
            $count_result = $this->get_media_count($count_params);
            
            $type_options[] = [
                'value' => $type_key,
                'label' => $type_label,
                'count' => $count_result['count'] ?? 0
            ];
        }
        
        return $type_options;
    }

    private function get_date_range_options() {
        // Get the earliest and latest media upload dates
        $earliest_query = new \WP_Query([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'orderby' => 'date',
            'order' => 'ASC',
            'fields' => 'ids'
        ]);
        
        $latest_query = new \WP_Query([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'orderby' => 'date',
            'order' => 'DESC',
            'fields' => 'ids'
        ]);
        
        $earliest_date = null;
        $latest_date = null;
        
        if ($earliest_query->have_posts()) {
            $earliest_post = get_post($earliest_query->posts[0]);
            $earliest_date = $earliest_post->post_date;
        }
        
        if ($latest_query->have_posts()) {
            $latest_post = get_post($latest_query->posts[0]);
            $latest_date = $latest_post->post_date;
        }
        
        $current_time = current_time('timestamp');
        
        return [
            [
                'value' => 'all',
                'label' => 'All Time',
                'count' => $this->get_media_count(['type' => 'all'])['count'] ?? 0
            ],
            [
                'value' => 'today',
                'label' => 'Today',
                'count' => $this->get_media_count_by_date_range('today')['count'] ?? 0
            ],
            [
                'value' => 'yesterday',
                'label' => 'Yesterday',
                'count' => $this->get_media_count_by_date_range('yesterday')['count'] ?? 0
            ],
            [
                'value' => 'last_7_days',
                'label' => 'Last 7 Days',
                'count' => $this->get_media_count_by_date_range('last_7_days')['count'] ?? 0
            ],
            [
                'value' => 'last_30_days',
                'label' => 'Last 30 Days',
                'count' => $this->get_media_count_by_date_range('last_30_days')['count'] ?? 0
            ],
            [
                'value' => 'last_90_days',
                'label' => 'Last 90 Days',
                'count' => $this->get_media_count_by_date_range('last_90_days')['count'] ?? 0
            ],
            [
                'value' => 'this_month',
                'label' => 'This Month',
                'count' => $this->get_media_count_by_date_range('this_month')['count'] ?? 0
            ],
            [
                'value' => 'last_month',
                'label' => 'Last Month',
                'count' => $this->get_media_count_by_date_range('last_month')['count'] ?? 0
            ],
            [
                'value' => 'this_year',
                'label' => 'This Year',
                'count' => $this->get_media_count_by_date_range('this_year')['count'] ?? 0
            ],
            [
                'value' => 'last_year',
                'label' => 'Last Year',
                'count' => $this->get_media_count_by_date_range('last_year')['count'] ?? 0
            ]
        ];
    }

    private function get_media_count_by_date_range($range) {
        $date_query = $this->build_date_query($range);
        
        if (!$date_query) {
            return ['count' => 0];
        }
        
        $query_args = [
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'date_query' => $date_query
        ];
        
        $query = new \WP_Query($query_args);
        
        return ['count' => $query->found_posts];
    }

    private function build_date_query($range) {
        $current_time = current_time('timestamp');
        
        switch ($range) {
            case 'today':
                return [
                    [
                        'year' => date('Y', $current_time),
                        'month' => date('n', $current_time),
                        'day' => date('j', $current_time)
                    ]
                ];
                
            case 'yesterday':
                $yesterday = $current_time - (24 * 60 * 60);
                return [
                    [
                        'year' => date('Y', $yesterday),
                        'month' => date('n', $yesterday),
                        'day' => date('j', $yesterday)
                    ]
                ];
                
            case 'last_7_days':
                return [
                    [
                        'after' => '7 days ago'
                    ]
                ];
                
            case 'last_30_days':
                return [
                    [
                        'after' => '30 days ago'
                    ]
                ];
                
            case 'last_90_days':
                return [
                    [
                        'after' => '90 days ago'
                    ]
                ];
                
            case 'this_month':
                return [
                    [
                        'year' => date('Y', $current_time),
                        'month' => date('n', $current_time)
                    ]
                ];
                
            case 'last_month':
                $last_month = strtotime('-1 month', $current_time);
                return [
                    [
                        'year' => date('Y', $last_month),
                        'month' => date('n', $last_month)
                    ]
                ];
                
            case 'this_year':
                return [
                    [
                        'year' => date('Y', $current_time)
                    ]
                ];
                
            case 'last_year':
                $last_year = date('Y', $current_time) - 1;
                return [
                    [
                        'year' => $last_year
                    ]
                ];
                
            default:
                return null;
        }
    }

    public function get_media_list($params) {
        // Set default parameters
        $page = $params['page'] ?? 1;
        $per_page = $params['per_page'] ?? $params['posts_per_page'] ?? 100;
        $type = $params['type'] ?? 'all';
        $date_range = $params['date_range'] ?? 'all';
        $start_date = $params['start_date'] ?? null;
        $end_date = $params['end_date'] ?? null;

        // Handle special case for getting all media without pagination
        if ($per_page === -1 || $per_page === 'all' || $per_page === 'ALL') {
            $per_page = -1; // WordPress uses -1 to get all posts
            $page = 1; // Reset page to 1 when getting all
        }

        // Initialize response structure
        $response = [
            'success' => false,
            'data' => [],
            'total' => 0,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => 0,
        ];

        // Build query args
        $query_args = [
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        // Filter by media type if specified
        if ($type !== 'all') {
            $query_args['post_mime_type'] = $this->get_mime_type_from_type($type);
        }

        // Filter by date range
        $date_query = [];
        
        // Handle predefined date ranges
        if ($date_range !== 'all') {
            $date_query = $this->build_date_query($date_range);
        }
        
        // Handle custom date range
        if ($start_date || $end_date) {
            $custom_date_query = [];
            
            if ($start_date) {
                $custom_date_query['after'] = $start_date;
            }
            
            if ($end_date) {
                $custom_date_query['before'] = $end_date;
            }
            
            if (!empty($custom_date_query)) {
                $date_query = [$custom_date_query];
            }
        }
        
        if (!empty($date_query)) {
            $query_args['date_query'] = $date_query;
        }

        // Add debug logging
        $query = new \WP_Query($query_args);

        if (!$query->have_posts()) {
            $response['message'] = 'No media found';
            return $response;
        }

        // Process each attachment
        foreach ($query->posts as $attachment) {
            $attachment_id = $attachment->ID;
            
            // Get the mime type to determine the type of file
            $mime_type = get_post_mime_type($attachment_id);
            
            // Try to get thumbnail or full URL
            $thumbnail_url = wp_get_attachment_image_url($attachment_id, 'medium');
            if (!$thumbnail_url) {
                $thumbnail_url = wp_get_attachment_url($attachment_id);
            }
            
            // If still no URL, provide a mime-type based fallback
            if (!$thumbnail_url) {
                // For now, just provide an empty string instead of false
                // This will allow the frontend to handle the fallback display
                $thumbnail_url = '';
            }
            
            // Get file path and size
            $file_path = get_attached_file($attachment_id);
            $file_size = file_exists($file_path) ? filesize($file_path) : 0;
            
            $data = [
                'id' => $attachment_id,
                'title' => $attachment->post_title,
                'thumbnail' => $thumbnail_url,
                'mime_type' => $mime_type,
                'type' => $this->determine_media_type($mime_type),
                'file_size' => $file_size,
                'upload_date' => $attachment->post_date,
                'alt_text' => get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
                'description' => $attachment->post_content,
                'caption' => $attachment->post_excerpt,
            ];

            // Add url field when per_page is -1 (getting all media)
            if ($per_page === -1) {
                $data['url'] = wp_get_attachment_url($attachment_id);
            }

            $response['data'][] = $data;
        }

        // Update response metadata
        $response['success'] = true;
        $response['total'] = $query->found_posts;
        $response['total_pages'] = $per_page === -1 ? 1 : $query->max_num_pages;

        return $response;
    }

    private function get_mime_type_from_type($type) {
        $mime_types = [
            'images' => 'image',
            'videos' => 'video',
            'documents' => 'application',
            'archives' => 'application/zip',
        ];

        return $mime_types[$type] ?? '';
    }

    private function determine_media_type($mime_type) {
        if (strpos($mime_type, 'image') === 0) {
            return 'image';
        } elseif (strpos($mime_type, 'video') === 0) {
            return 'video';
        } elseif (strpos($mime_type, 'application/pdf') === 0 || 
                 strpos($mime_type, 'text') === 0 || 
                 strpos($mime_type, 'application/msword') === 0 || 
                 strpos($mime_type, 'application/vnd.openxmlformats-officedocument') === 0) {
            return 'file';
        } elseif (strpos($mime_type, 'application/zip') === 0 || 
                 strpos($mime_type, 'application/x-tar') === 0 || 
                 strpos($mime_type, 'application/x-gzip') === 0) {
            return 'file';
        } else {
            return 'file';
        }
    }

    private function get_category_from_mime_type($mime_type) {
        if (strpos($mime_type, 'image') !== false) {
            return 'Image';
        } elseif (strpos($mime_type, 'video') !== false) {
            return 'Video';
        } elseif (strpos($mime_type, 'application/pdf') !== false || strpos($mime_type, 'text') !== false) {
            return 'Document';
        } elseif (strpos($mime_type, 'application/zip') !== false || strpos($mime_type, 'application/x-tar') !== false) {
            return 'Archive';
        } else {
            return 'Other';
        }
    }

    public function get_media_detail_by_id($params) {
        $id = $params['id'] ?? null;

        if (!$id) {
            return [
                'success' => false,
                'message' => 'Media ID is required',
            ];
        }

        $attachment = get_post($id);
        if (!$attachment) {
            return [
                'success' => false,
                'message' => 'Media not found'
            ];
        }

        $file_path = get_attached_file($id);
        $file_info = pathinfo($file_path);
        $mime_type = get_post_mime_type($id);
        $metadata = wp_get_attachment_metadata($id);

        $data = [
            'id' => $id,
            'title' => $attachment->post_title,
            'filename' => $file_info['basename'],
            'mime_type' => $mime_type,
            'url' => wp_get_attachment_url($id),
            'sizes' => [
                'full' => wp_get_attachment_url($id),
                'thumbnail' => wp_get_attachment_thumb_url($id),
                'medium' => wp_get_attachment_image_url($id, 'medium'),
                'large' => wp_get_attachment_image_url($id, 'large'),
            ],
            'dimensions' => [
                'width' => $metadata['width'] ?? null,
                'height' => $metadata['height'] ?? null,
            ],
            'file_size' => filesize($file_path),
            'uploaded_on' => $attachment->post_date,
            'modified_on' => $attachment->post_modified,
            'alt_text' => get_post_meta($id, '_wp_attachment_image_alt', true),
            'description' => $attachment->post_content,
            'caption' => $attachment->post_excerpt,
        ];

        return $data;
    }

    public function delete_media($params) {
        // Add debug logging
        rp_dev_log('=== WordPress Media Delete Debug ===');
        rp_dev_log('Received params: ' . print_r($params, true));

        if (empty($params['id'])) {
            rp_dev_log('Delete failed: Missing media ID');
            return [
                'success' => false,
                'message' => 'Media ID is required'
            ];
        }

        $attachment_id = intval($params['id']);
        
        // Check if attachment exists
        $attachment = get_post($attachment_id);
        if (!$attachment || $attachment->post_type !== 'attachment') {
            rp_dev_log('Delete failed: Media not found');
            return [
                'success' => false,
                'message' => 'Media not found'
            ];
        }

        // Delete the attachment and its metadata
        $delete_result = wp_delete_attachment($attachment_id, true);
        
        if ($delete_result === false) {
            rp_dev_log('Delete failed: WordPress deletion failed');
            return [
                'success' => false,
                'message' => 'Failed to delete media'
            ];
        }

        rp_dev_log('Successfully deleted media with ID: ' . $attachment_id);
        return [
            'success' => true,
            'message' => 'Media deleted successfully'
        ];
    }

    public function delete_media_bulk($params) {
        $mediaIds = $params['mediaIds'] ?? [];

        $all_success = true;
        $failed_media = [];
        $success_media = [];
        foreach ($mediaIds as $mediaId) {
            $result = $this->delete_media(['id' => $mediaId]);
            if (!$result['success']) {
                $all_success = false;
                $failed_media[] = $mediaId;
            } else {
                $success_media[] = $mediaId;
            }
        }

        return [
            'success' => $all_success,
            'message' => $all_success ? 'Media deleted successfully' : 'Some media failed to delete',
            'failed_media' => $failed_media,
            'success_media' => $success_media,
        ];
    }

    public function get_media_count($params) {
        // Initialize response structure
        $response = [
            'success' => false,
            'count' => 0
        ];

        $type = $params['type'] ?? 'all';

        // Build query args
        $query_args = [
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => -1, // Set to -1 to get all posts
            'fields' => 'ids', // Only get post IDs to make the query lighter
        ];

        // Filter by media type if specified
        if ($type !== 'all') {
            $query_args['post_mime_type'] = $this->get_mime_type_from_type($type);
        }

        $query = new \WP_Query($query_args);
        
        $response['success'] = true;
        $response['count'] = $query->found_posts;

        return $response;
    }

    public function update_media($params) {
        // Add debug logging
        rp_dev_log('=== WordPress Media Update Debug ===');
        rp_dev_log('Received params: ' . print_r($params, true));

        // Check if params is an array of media objects (bulk update)
        if (isset($params[0]) && is_array($params[0])) {
            return $this->update_media_bulk($params);
        }

        // Single media update (existing logic)
        $attachment_id  = $params['id'];
        
        // Validate required parameters
        if (empty($attachment_id )) {
            rp_dev_log('Update failed: Missing media ID');
            return [
                'success' => false,
                'message' => 'Media ID is required'
            ];
        }

        // Check if attachment exists
        $attachment = get_post($attachment_id);
        if (!$attachment || $attachment->post_type !== 'attachment') {
            rp_dev_log('Update failed: Media not found');
            return [
                'success' => false,
                'message' => 'Media not found'
            ];
        }

        // Prepare update data
        $update_data = array();
        
        // Update title if provided
        if (isset($params['title'])) {
            $update_data['post_title'] = sanitize_text_field($params['title']);
        }
        
        // Update description if provided
        if (isset($params['description'])) {
            $update_data['post_content'] = sanitize_textarea_field($params['description']);
        }
        
        // Update caption if provided
        if (isset($params['caption'])) {
            $update_data['post_excerpt'] = sanitize_textarea_field($params['caption']);
        }

        // Update alt text if provided
        if (isset($params['alt_text'])) {
            update_post_meta($attachment_id, '_wp_attachment_image_alt', sanitize_text_field($params['alt_text']));
        }

        // Update the post if we have data to update
        if (!empty($update_data)) {
            $update_data['ID'] = $attachment_id;
            $update_result = wp_update_post($update_data);
            
            if (is_wp_error($update_result)) {
                rp_dev_log('Update failed: ' . $update_result->get_error_message());
                return [
                    'success' => false,
                    'message' => 'Failed to update media: ' . $update_result->get_error_message()
                ];
            }
        }

        rp_dev_log('Successfully updated media with ID: ' . $attachment_id);
        
        // Return updated media data
        return [
            'success' => true,
            'message' => 'Media updated successfully',
            'data' => $this->get_media_detail_by_id(['id' => $attachment_id])
        ];
    }

    private function update_media_bulk($media_array) {
        rp_dev_log('=== WordPress Bulk Media Update Debug ===');
        rp_dev_log('Processing bulk update for ' . count($media_array) . ' media items');

        $all_success = true;
        $failed_media = [];
        $success_media = [];
        $updated_data = [];

        foreach ($media_array as $media_item) {
            $result = $this->update_media($media_item);
            
            if (!$result['success']) {
                $all_success = false;
                $failed_media[] = [
                    'id' => $media_item['id'],
                    'error' => $result['message']
                ];
            } else {
                $success_media[] = $media_item['id'];
                if (isset($result['data'])) {
                    $updated_data[] = $result['data'];
                }
            }
        }

        return [
            'success' => $all_success,
            'message' => $all_success ? 'All media updated successfully' : 'Some media failed to update',
            'updated_count' => count($success_media),
            'failed_count' => count($failed_media),
            'success_media' => $success_media,
            'failed_media' => $failed_media,
            'data' => $updated_data
        ];
    }

    /**
     * Replace media attachment with new file while keeping same ID
     * This is used by the media conversion feature
     */
    public function replace_media($params) {
        rp_dev_log('=== WordPress Media Replace Debug ===');
        rp_dev_log('Received params: ' . print_r($params, true));

        $attachment_id = $params['id'] ?? null;
        $file_path = $params['file_path'] ?? null;
        $mime_type = $params['mime_type'] ?? null;

        if (!$attachment_id || !$file_path) {
            rp_dev_log('Replace failed: Missing required parameters');
            return [
                'success' => false,
                'message' => 'Media ID and file path are required'
            ];
        }

        // Check if attachment exists
        $attachment = get_post($attachment_id);
        if (!$attachment || $attachment->post_type !== 'attachment') {
            rp_dev_log('Replace failed: Media not found');
            return [
                'success' => false,
                'message' => 'Media not found'
            ];
        }

        if (!file_exists($file_path)) {
            rp_dev_log('Replace failed: Replacement file not found');
            return [
                'success' => false,
                'message' => 'Replacement file not found'
            ];
        }

        try {
            // Get original file info
            $original_file = get_attached_file($attachment_id);
            $upload_dir = wp_upload_dir();
            $file_info = pathinfo($original_file);
            
            // Generate new filename keeping the base name but with new extension
            $new_extension = pathinfo($file_path, PATHINFO_EXTENSION);
            $new_filename = $file_info['filename'] . '.' . $new_extension;
            $new_file_path = $file_info['dirname'] . '/' . $new_filename;

            // Create backup of original
            $backup_file = $original_file . '.backup.' . time();
            if (file_exists($original_file)) {
                copy($original_file, $backup_file);
            }

            // Copy new file to WordPress uploads directory
            if (!copy($file_path, $new_file_path)) {
                throw new Exception('Failed to copy replacement file');
            }

            // Delete old file
            if (file_exists($original_file) && $original_file !== $new_file_path) {
                unlink($original_file);
            }

            // Update attachment file path
            update_attached_file($attachment_id, $new_file_path);

            // Update post mime type if provided
            if ($mime_type) {
                wp_update_post([
                    'ID' => $attachment_id,
                    'post_mime_type' => $mime_type
                ]);
            }

            // Regenerate thumbnails and metadata
            require_once ABSPATH . 'wp-admin/includes/image.php';
            $metadata = wp_generate_attachment_metadata($attachment_id, $new_file_path);
            wp_update_attachment_metadata($attachment_id, $metadata);

            // Update SHA-256 hash
            $hash = hash_file('sha256', $new_file_path);
            if ($hash) {
                update_post_meta($attachment_id, '_sha256_hash', $hash);
            }

            // Clean up backup
            if (file_exists($backup_file)) {
                unlink($backup_file);
            }

            // Clear caches
            clean_attachment_cache($attachment_id);
            wp_cache_flush();

            rp_dev_log('Successfully replaced media with ID: ' . $attachment_id);

            return [
                'success' => true,
                'message' => 'Media replaced successfully',
                'data' => [
                    'id' => $attachment_id,
                    'url' => wp_get_attachment_url($attachment_id),
                    'mime_type' => get_post_mime_type($attachment_id),
                    'file_path' => $new_file_path
                ]
            ];

        } catch (Exception $e) {
            // Restore backup on error
            if (isset($backup_file) && file_exists($backup_file)) {
                if (file_exists($new_file_path)) {
                    unlink($new_file_path);
                }
                rename($backup_file, $original_file);
            }

            rp_dev_log('Replace failed with error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to replace media: ' . $e->getMessage()
            ];
        }
    }

    /*
     * Folder Management Methods
     */

    /**
     * Create a new folder
     */
    public function create_folder($params) {
        try {
            $name = sanitize_text_field($params['name'] ?? '');
            $description = sanitize_textarea_field($params['description'] ?? '');
            $parent_id = !empty($params['parent_id']) ? intval($params['parent_id']) : 0;
            
            if (empty($name)) {
                return [
                    'success' => false,
                    'message' => 'Folder name is required'
                ];
            }

            $term_data = array(
                'description' => $description,
                'parent' => $parent_id,
            );

            $taxonomy = $this->get_folder_taxonomy($params['type'] ?? 'rightplace');
            $result = wp_insert_term($name, $taxonomy, $term_data);
            
            if (is_wp_error($result)) {
                return [
                    'success' => false,
                    'message' => 'Failed to create folder: ' . $result->get_error_message()
                ];
            }

            $folder = get_term($result['term_id'], $this->folder_taxonomy);
            $folder_data = $this->format_folder_data($folder);
            
            return [
                'success' => true,
                'message' => 'Folder created successfully',
                'data' => $folder_data
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error creating folder: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update folder
     */
    public function update_folder($params) {
        try {
            $folder_id = $params['id'] ?? null;
            
            if (!$folder_id) {
                return [
                    'success' => false,
                    'message' => 'Folder ID is required'
                ];
            }

            $term_data = array();
            
            if (isset($params['name'])) {
                $term_data['name'] = sanitize_text_field($params['name']);
            }
            
            if (isset($params['description'])) {
                $term_data['description'] = sanitize_textarea_field($params['description']);
            }
            
            if (isset($params['parent_id'])) {
                $term_data['parent'] = intval($params['parent_id']);
            }

            $taxonomy = $this->get_folder_taxonomy($params['type'] ?? 'rightplace');
            $result = wp_update_term($folder_id, $taxonomy, $term_data);
            
            if (is_wp_error($result)) {
                return [
                    'success' => false,
                    'message' => 'Failed to update folder: ' . $result->get_error_message()
                ];
            }

            $folder = get_term($result['term_id'], $taxonomy);
            $folder_data = $this->format_folder_data($folder, $taxonomy);
            
            return [
                'success' => true,
                'message' => 'Folder updated successfully',
                'data' => $folder_data
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error updating folder: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete folder
     */
    public function delete_folder($params) {
        try {
            $folder_id = $params['id'] ?? null;
            
            if (!$folder_id) {
                return [
                    'success' => false,
                    'message' => 'Folder ID is required'
                ];
            }

            $taxonomy = $this->get_folder_taxonomy($params['type'] ?? 'rightplace');
            $result = wp_delete_term($folder_id, $taxonomy);
            
            if (is_wp_error($result)) {
                return [
                    'success' => false,
                    'message' => 'Failed to delete folder: ' . $result->get_error_message()
                ];
            }

            return [
                'success' => true,
                'message' => 'Folder deleted successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error deleting folder: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get folder tree
     */
    public function get_folder_tree($params) {
        try {
            $max_depth = $params['max_depth'] ?? 10;
            $type = $params['type'] ?? 'rightplace';
            $taxonomy = $this->get_folder_taxonomy($type);
            
            $terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
                'parent' => 0,
                'orderby' => 'name',
                'order' => 'ASC',
                'fields' => 'all'
            ));
            
            if (is_wp_error($terms)) {
                return [
                    'success' => false,
                    'message' => 'Error getting folder tree: ' . $terms->get_error_message(),
                    'data' => []
                ];
            }
            
            $tree = array();
            foreach ($terms as $term) {
                $tree[] = $this->build_folder_node($term, 0, $max_depth, $taxonomy);
            }
            
            return [
                'success' => true,
                'data' => $tree
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error getting folder tree: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Get folder by ID
     */
    public function get_folder($params) {
        try {
            $folder_id = $params['id'] ?? null;
            
            if (!$folder_id) {
                return [
                    'success' => false,
                    'message' => 'Folder ID is required'
                ];
            }

            $taxonomy = $this->get_folder_taxonomy($params['type'] ?? 'rightplace');
            $folder = get_term($folder_id, $taxonomy);
            
            if (is_wp_error($folder) || !$folder) {
                return [
                    'success' => false,
                    'message' => 'Folder not found'
                ];
            }

            $folder_data = $this->format_folder_data($folder, $taxonomy);
            
            return [
                'success' => true,
                'data' => $folder_data
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error getting folder: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Search folders
     */
    public function search_folders($params) {
        try {
            $search_term = $params['search_term'] ?? '';
            
            if (empty($search_term)) {
                return [
                    'success' => false,
                    'message' => 'Search term is required'
                ];
            }

            $taxonomy = $this->get_folder_taxonomy($params['type'] ?? 'rightplace');
            $terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
                'search' => $search_term,
                'orderby' => 'name',
                'order' => 'ASC'
            ));
            
            if (is_wp_error($terms)) {
                return [
                    'success' => false,
                    'message' => 'Error searching folders: ' . $terms->get_error_message(),
                    'data' => []
                ];
            }
            
            $folders = array();
            foreach ($terms as $term) {
                $folders[] = $this->format_folder_data($term, $taxonomy);
            }
            
            return [
                'success' => true,
                'data' => $folders
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error searching folders: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Get media in folder
     */
    public function get_media_in_folder($params) {
        try {
            $folder_id = $params['folder_id'] ?? null;
            $limit = $params['limit'] ?? 0;
            $offset = $params['offset'] ?? 0;
            $type = $params['media_type'] ?? $params['type'] ?? 'all';
            $date_range = $params['date_range'] ?? 'all';
            $start_date = $params['start_date'] ?? null;
            $end_date = $params['end_date'] ?? null;
            
            if (!$folder_id) {
                return [
                    'success' => false,
                    'message' => 'Folder ID is required'
                ];
            }

            $taxonomy = $this->get_folder_taxonomy($params['type'] ?? 'rightplace');
            $query_args = array(
                'post_type' => 'attachment',
                'post_status' => 'inherit',
                'tax_query' => array(
                    array(
                        'taxonomy' => $taxonomy,
                        'field' => 'term_id',
                        'terms' => $folder_id,
                        'include_children' => false, 
                    ),
                ),
                'posts_per_page' => $limit > 0 ? $limit : -1,
                'offset' => $offset,
                'orderby' => 'date',
                'order' => 'DESC',
            );

            // Filter by media type if specified
            if ($type !== 'all') {
                $query_args['post_mime_type'] = $this->get_mime_type_from_type($type);
            }

            // Filter by date range
            $date_query = [];
            
            // Handle predefined date ranges
            if ($date_range !== 'all') {
                $date_query = $this->build_date_query($date_range);
            }
            
            // Handle custom date range
            if ($start_date || $end_date) {
                $custom_date_query = [];
                
                if ($start_date) {
                    $custom_date_query['after'] = $start_date;
                }
                
                if ($end_date) {
                    $custom_date_query['before'] = $end_date;
                }
                
                if (!empty($custom_date_query)) {
                    $date_query = [$custom_date_query];
                }
            }
            
            if (!empty($date_query)) {
                $query_args['date_query'] = $date_query;
            }

            $query = new \WP_Query($query_args);
            $media = array();
            
            if ($query->have_posts()) {
                foreach ($query->posts as $attachment) {
                    $media[] = $this->format_media_data($attachment);
                }
            }
            
            return [
                'success' => true,
                'data' => $media,
                'total' => $query->found_posts,
                'folder_id' => $folder_id
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error getting media in folder: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Add media to folder
     */
    public function add_media_to_folder($params) {
        try {
            $folder_id = $params['folder_id'] ?? null;
            $media_id = $params['media_id'] ?? null;
            
            if (!$folder_id || !$media_id) {
                return [
                    'success' => false,
                    'message' => 'Folder ID and Media ID are required'
                ];
            }

            // Ensure folder_id is always an array
            if (!is_array($folder_id)) {
                $folder_id = [$folder_id];
            }

            $taxonomy = $this->get_folder_taxonomy($params['type'] ?? 'rightplace');
            $result = wp_set_object_terms($media_id, $folder_id, $taxonomy, true);
            
            if (is_wp_error($result)) {
                return [
                    'success' => false,
                    'message' => 'Failed to add media to folder: ' . $result->get_error_message()
                ];
            }

            $folder_count = count($folder_id);
            $message = $folder_count === 1 
                ? 'Media added to folder successfully' 
                : "Media added to {$folder_count} folders successfully";

            return [
                'success' => true,
                'message' => $message,
                'folder_count' => $folder_count
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error adding media to folder: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Remove media from folder
     */
    public function remove_media_from_folder($params) {
        try {
            $folder_id = $params['folder_id'] ?? null;
            $media_id = $params['media_id'] ?? null;
            
            if (!$folder_id || !$media_id) {
                return [
                    'success' => false,
                    'message' => 'Folder ID and Media ID are required'
                ];
            }

            // Ensure folder_id is always an array
            if (!is_array($folder_id)) {
                $folder_id = [$folder_id];
            }

            $taxonomy = $this->get_folder_taxonomy($params['type'] ?? 'rightplace');
            $current_terms = wp_get_object_terms($media_id, $taxonomy, array('fields' => 'ids'));
            $updated_terms = array_diff($current_terms, $folder_id);
            
            $result = wp_set_object_terms($media_id, $updated_terms, $taxonomy);
            
            if (is_wp_error($result)) {
                return [
                    'success' => false,
                    'message' => 'Failed to remove media from folder: ' . $result->get_error_message()
                ];
            }

            $folder_count = count($folder_id);
            $message = $folder_count === 1 
                ? 'Media removed from folder successfully' 
                : "Media removed from {$folder_count} folders successfully";

            return [
                'success' => true,
                'message' => $message,
                'folder_count' => $folder_count
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error removing media from folder: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Move media between folders
     */
    public function move_media($params) {
        try {
            $media_id = $params['media_id'] ?? null;
            $from_folder_id = $params['from_folder_id'] ?? null;
            $to_folder_id = $params['to_folder_id'] ?? null;
            
            if (!$media_id || !$from_folder_id || !$to_folder_id) {
                return [
                    'success' => false,
                    'message' => 'Media ID, From Folder ID, and To Folder ID are required'
                ];
            }

            $taxonomy = $this->get_folder_taxonomy($params['type'] ?? 'rightplace');
            // Get current terms and remove the source folder
            $current_terms = wp_get_object_terms($media_id, $taxonomy, array('fields' => 'ids'));
            $updated_terms = array_diff($current_terms, array($from_folder_id));
            
            // Add the destination folder
            $updated_terms[] = $to_folder_id;
            
            $result = wp_set_object_terms($media_id, $updated_terms, $taxonomy);
            
            if (is_wp_error($result)) {
                return [
                    'success' => false,
                    'message' => 'Failed to move media: ' . $result->get_error_message()
                ];
            }

            return [
                'success' => true,
                'message' => 'Media moved successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error moving media: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Copy media to folder
     */
    public function copy_media_to_folder($params) {
        try {
            $media_id = $params['media_id'] ?? null;
            $folder_id = $params['folder_id'] ?? null;
            
            if (!$media_id || !$folder_id) {
                return [
                    'success' => false,
                    'message' => 'Folder ID and Media ID are required'
                ];
            }

            $taxonomy = $this->get_folder_taxonomy($params['type'] ?? 'rightplace');
            $result = wp_set_object_terms($media_id, $folder_id, $taxonomy, true);
            
            if (is_wp_error($result)) {
                return [
                    'success' => false,
                    'message' => 'Failed to copy media to folder: ' . $result->get_error_message()
                ];
            }

            return [
                'success' => true,
                'message' => 'Media copied to folder successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error copying media to folder: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get folders for media
     */
    public function get_folders_for_media($params) {
        try {
            $media_id = $params['media_id'] ?? null;
            
            if (!$media_id) {
                return [
                    'success' => false,
                    'message' => 'Media ID is required'
                ];
            }

            $taxonomy = $this->get_folder_taxonomy($params['type'] ?? 'rightplace');
            $terms = wp_get_object_terms($media_id, $taxonomy);
            
            if (is_wp_error($terms)) {
                return [
                    'success' => false,
                    'message' => 'Error getting folders for media: ' . $terms->get_error_message(),
                    'data' => []
                ];
            }
            
            $folders = array();
            foreach ($terms as $term) {
                $folders[] = $this->format_folder_data($term, $taxonomy);
            }
            
            return [
                'success' => true,
                'data' => $folders
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error getting folders for media: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Format folder data for API response
     */
    private function format_folder_data($term, $taxonomy = null) {
        if (!$taxonomy) {
            $taxonomy = $this->rightplace_folder_taxonomy;
        }
        
        $children = get_terms(array(
            'taxonomy' => $taxonomy,
            'parent' => $term->term_id,
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ));

        // Get actual media count by querying attachments with this taxonomy term
        $media_count_query = new \WP_Query(array(
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'tax_query' => array(
                array(
                    'taxonomy' => $taxonomy,
                    'field' => 'term_id',
                    'terms' => $term->term_id,
                    'include_children' => false,
                ),
            ),
        ));
        
        $media_count = $media_count_query->found_posts;
        
        return array(
            'id' => $term->term_id,
            'name' => $term->name,
            'slug' => $term->slug,
            'description' => $term->description,
            'parent_id' => $term->parent,
            'media_count' => $media_count,
            'children_count' => count($children),
            'created_at' => null, // WordPress terms don't have creation dates
            'updated_at' => null, // WordPress terms don't have update dates
            'path' => $this->get_folder_path($term->term_id, $taxonomy)
        );
    }

    /**
     * Build folder node for tree structure
     */
    private function build_folder_node($term, $depth, $max_depth, $taxonomy = null) {
        if (!$taxonomy) {
            $taxonomy = $this->rightplace_folder_taxonomy;
        }
        
        $node = $this->format_folder_data($term, $taxonomy);
        $node['depth'] = $depth;
        $node['children'] = array();

        if ($depth < $max_depth) {
            $children = get_terms(array(
                'taxonomy' => $taxonomy,
                'parent' => $term->term_id,
                'hide_empty' => false,
                'orderby' => 'name',
                'order' => 'ASC',
                'fields' => 'all'
            ));

            foreach ($children as $child) {
                $node['children'][] = $this->build_folder_node($child, $depth + 1, $max_depth, $taxonomy);
            }
        }

        return $node;
    }

    /**
     * Get folder path as string
     */
    private function get_folder_path($term_id, $taxonomy = null) {
        if (!$taxonomy) {
            $taxonomy = $this->rightplace_folder_taxonomy;
        }
        
        $path = array();
        $current_term = get_term($term_id, $taxonomy);
        
        while ($current_term && !is_wp_error($current_term)) {
            array_unshift($path, $current_term->name);
            if ($current_term->parent == 0) {
                break;
            }
            $current_term = get_term($current_term->parent, $taxonomy);
        }
        
        return implode(' / ', $path);
    }

    /**
     * Format media data for API response
     */
    private function format_media_data($attachment) {
        $file_path = get_attached_file($attachment->ID);
        $file_size = file_exists($file_path) ? filesize($file_path) : 0;
        $file_type = wp_check_filetype($file_path);
        
        return array(
            'id' => $attachment->ID,
            'title' => $attachment->post_title,
            'filename' => basename($file_path),
            'url' => wp_get_attachment_url($attachment->ID),
            'thumbnail_url' => wp_get_attachment_image_url($attachment->ID, 'thumbnail'),
            'file_size' => $file_size,
            'file_type' => $file_type['type'] ?? '',
            'mime_type' => $attachment->post_mime_type,
            'upload_date' => $attachment->post_date,
            'modified_date' => $attachment->post_modified,
            'alt_text' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
            'description' => $attachment->post_content,
            'caption' => $attachment->post_excerpt
        );
    }
}

// Initialize the Media_Sync class
new Media_Sync();


/**
 * Media Filter Options API
 * 
 * Get available filter options for media:
 * rightplace_action_filter/media/getMediaFilterOptions
 * 
 * Response will look like:
 * {
 *   success: true,
 *   data: {
 *     media_types: [
 *       { value: 'all', label: 'All Media', count: 711 },
 *       { value: 'images', label: 'Images', count: 500 },
 *       { value: 'videos', label: 'Videos', count: 100 },
 *       { value: 'documents', label: 'Documents', count: 100 },
 *       { value: 'archives', label: 'Archives', count: 11 }
 *     ],
 *     date_ranges: [
 *       { value: 'all', label: 'All Time', count: 711 },
 *       { value: 'today', label: 'Today', count: 5 },
 *       { value: 'yesterday', label: 'Yesterday', count: 3 },
 *       { value: 'last_7_days', label: 'Last 7 Days', count: 25 },
 *       { value: 'last_30_days', label: 'Last 30 Days', count: 89 },
 *       { value: 'last_90_days', label: 'Last 90 Days', count: 234 },
 *       { value: 'this_month', label: 'This Month', count: 67 },
 *       { value: 'last_month', label: 'Last Month', count: 45 },
 *       { value: 'this_year', label: 'This Year', count: 456 },
 *       { value: 'last_year', label: 'Last Year', count: 255 }
 *     ]
 *   }
 * }
 * 
 * 
 * Media List API with Filters
 * 
 * Get media list with filtering options:
 * rightplace_action_filter/media/getMediaList
 * 
 * If params is empty than by default will be page: 1 per_page: 100 type: all
 * 
 * Params will look like:
 * {
 *   type: "all" | "images" | "videos" | "documents" | "archives",
 *   date_range: "all" | "today" | "yesterday" | "last_7_days" | "last_30_days" | "last_90_days" | "this_month" | "last_month" | "this_year" | "last_year",
 *   start_date: "2024-01-01", // Custom start date (YYYY-MM-DD format)
 *   end_date: "2024-12-31",   // Custom end date (YYYY-MM-DD format)
 *   page: 2,
 *   per_page: 100,
 * }
 * 
 * Response will look like:
 * {
 *   success: true,
 *   data: [
 *     {
 *       id: 123,
 *       title: 'Image Title',
 *       thumbnail: 'https://example.com/image-thumbnail.jpg',
 *       mime_type: 'image/jpeg',
 *       type: 'image',
 *       file_size: 1024000,
 *       upload_date: '2024-01-15 10:30:00',
 *       alt_text: 'Alt text for image',
 *       description: 'Image description',
 *       caption: 'Image caption',
 *       url: 'https://example.com/image.jpg' // Only included when per_page is -1
 *     }
 *   ],
 *   total: 711,
 *   page: 2,
 *   per_page: 100,
 *   total_pages: 8
 * }
 * 
 * Filter Examples:
 * 
 * 1. Get all images uploaded in the last 30 days:
 * {
 *   type: "images",
 *   date_range: "last_30_days",
 *   page: 1,
 *   per_page: 50
 * }
 * 
 * 2. Get all PDF files uploaded this year:
 * {
 *   type: "documents",
 *   extension: "pdf",
 *   date_range: "this_year",
 *   page: 1,
 *   per_page: 100
 * }
 * 
 * 3. Get all media between specific dates:
 * {
 *   start_date: "2024-01-01",
 *   end_date: "2024-03-31",
 *   page: 1,
 *   per_page: 100
 * }
 * 
 * 4. Get all media without pagination:
 * {
 *   per_page: -1
 * }
 * 
 */
