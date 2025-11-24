<?php
namespace RightPlace;

class Class_Media_Manager {
    const TEXTDOMAIN = 'rightplace-media-api';

    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes() {
        $salt = \Rightplace_Client::get_url_salt();
        
        register_rest_route('rightplace/v1', '/' . $salt . '/media', array(
            'methods'  => 'POST',
            'callback' => array($this, 'upload_media'),
            'permission_callback' => array($this, 'permissions_check_upload'),
            'args' => array(
                'title' => array(
                    'required' => false,
                    'type'     => 'string',
                    'description' => __('Optional title for the uploaded media.', self::TEXTDOMAIN),
                ),
                'alt' => array(
                    'required' => false,
                    'type'     => 'string',
                    'description' => __('Alternative text for the image.', self::TEXTDOMAIN),
                ),
            ),
        ));

        // Add media replacement endpoint
        register_rest_route('rightplace/v1', '/' . $salt . '/media/(?P<id>\d+)/replace', array(
            'methods'  => 'POST',
            'callback' => array($this, 'replace_media'),
            'permission_callback' => array($this, 'permissions_check_upload'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type'     => 'integer',
                    'description' => __('The ID of the media attachment to replace.', self::TEXTDOMAIN),
                ),
                'title' => array(
                    'required' => false,
                    'type'     => 'string',
                    'description' => __('Optional title for the replacement media.', self::TEXTDOMAIN),
                ),
                'alt' => array(
                    'required' => false,
                    'type'     => 'string',
                    'description' => __('Alternative text for the image.', self::TEXTDOMAIN),
                ),
            ),
        ));

        // Add media replacement endpoint for RightPlace core API
        register_rest_route('rightplace/v1', '/' . $salt . '/media/replace', array(
            'methods'  => 'POST',
            'callback' => array($this, 'replace_media_core'),
            'permission_callback' => array($this, 'permissions_check_upload'),
        ));
    }

    public function permissions_check_upload($request) {
        return current_user_can('upload_files');
    }

    public function upload_media($request) {
        $files = $request->get_file_params();
        $file = $files['file'] ?? null;
        
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return new WP_Error('no_file', __('No valid file uploaded.', self::TEXTDOMAIN), ['status' => 400]);
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        // Upload the file
        $filename = $file['name'];
        $title = pathinfo($filename, PATHINFO_FILENAME); // without extension
        // Convert title to be more human readable
        $title = str_replace('-', ' ', $title);
        $title = str_replace(['-', '_'], ' ',  $title);
        $title = ucwords($title);
        
        $attachment_id = media_handle_sideload($file, 0, $request->get_param('title') ?? '',[
            'post_title' => $title,
        ]);

        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }

        if ($alt = $request->get_param('alt')) {
            update_post_meta($attachment_id, '_wp_attachment_image_alt', sanitize_text_field($alt));
        }

        $attachment_url = wp_get_attachment_url($attachment_id);
        $attachment = get_post($attachment_id);

        return new \WP_REST_Response([
            'success' => true,
            'id'      => $attachment_id,
            'title'   => $attachment->post_title,
            'url'     => $attachment_url,
            'mime'    => get_post_mime_type($attachment_id),
        ], 200);
    }

    /**
     * Replace an existing media attachment with a new file while keeping the same ID
     * This maintains all post relationships and references
     */
    public function replace_media($request) {
        $attachment_id = intval($request->get_param('id'));
        $files = $request->get_file_params();
        $file = $files['file'] ?? null;
        
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return new \WP_Error('no_file', __('No valid file uploaded.', self::TEXTDOMAIN), ['status' => 400]);
        }

        // Check if attachment exists
        $attachment = get_post($attachment_id);
        if (!$attachment || $attachment->post_type !== 'attachment') {
            return new \WP_Error('attachment_not_found', __('Media attachment not found.', self::TEXTDOMAIN), ['status' => 404]);
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        // Get original file info
        $original_file = get_attached_file($attachment_id);
        $original_file_info = pathinfo($original_file);
        $upload_dir = wp_upload_dir();

        try {
            // Create backup of original file
            $backup_file = $original_file . '.backup.' . time();
            if (file_exists($original_file)) {
                copy($original_file, $backup_file);
            }

            // Prepare new filename with same name but potentially different extension
            $new_filename = $original_file_info['filename'] . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_file_path = $original_file_info['dirname'] . '/' . $new_filename;

            // Handle file upload
            $uploaded_file = wp_handle_upload($file, array('test_form' => false));
            
            if (isset($uploaded_file['error'])) {
                // Restore backup if upload failed
                if (file_exists($backup_file)) {
                    rename($backup_file, $original_file);
                }
                return new \WP_Error('upload_error', $uploaded_file['error'], ['status' => 500]);
            }

            // Delete old file (but keep backup for now)
            if (file_exists($original_file)) {
                unlink($original_file);
            }

            // Move new file to replace old one
            if (!rename($uploaded_file['file'], $new_file_path)) {
                // If rename fails, restore backup
                if (file_exists($backup_file)) {
                    rename($backup_file, $original_file);
                }
                return new \WP_Error('file_replace_error', __('Failed to replace media file.', self::TEXTDOMAIN), ['status' => 500]);
            }

            // Update attachment metadata
            update_attached_file($attachment_id, $new_file_path);
            
            // Update MIME type
            $new_mime_type = wp_check_filetype($new_file_path)['type'];
            wp_update_post(array(
                'ID' => $attachment_id,
                'post_mime_type' => $new_mime_type,
                'post_title' => $request->get_param('title') ?: $attachment->post_title,
            ));

            // Generate new thumbnails and metadata
            $metadata = wp_generate_attachment_metadata($attachment_id, $new_file_path);
            wp_update_attachment_metadata($attachment_id, $metadata);

            // Update alt text if provided
            if ($alt = $request->get_param('alt')) {
                update_post_meta($attachment_id, '_wp_attachment_image_alt', sanitize_text_field($alt));
            }

            // Update the SHA-256 hash for the new file
            if (file_exists($new_file_path)) {
                $hash = hash_file('sha256', $new_file_path);
                if ($hash) {
                    update_post_meta($attachment_id, '_sha256_hash', $hash);
                }
            }

            // Clean up backup after successful replacement
            if (file_exists($backup_file)) {
                unlink($backup_file);
            }

            // Clear any caches
            clean_attachment_cache($attachment_id);
            wp_cache_flush();

            $updated_attachment = get_post($attachment_id);
            $new_url = wp_get_attachment_url($attachment_id);

            return new \WP_REST_Response([
                'success' => true,
                'id' => $attachment_id,
                'title' => $updated_attachment->post_title,
                'url' => $new_url,
                'mime' => get_post_mime_type($attachment_id),
                'message' => __('Media file replaced successfully.', self::TEXTDOMAIN),
                'replaced' => true,
            ], 200);

        } catch (Exception $e) {
            // Restore backup on any error
            if (file_exists($backup_file)) {
                if (file_exists($original_file)) {
                    unlink($original_file);
                }
                rename($backup_file, $original_file);
            }
            
            return new \WP_Error('replacement_failed', $e->getMessage(), ['status' => 500]);
        }
    }

    /**
     * Replace media via RightPlace core API call (handles file from desktop app)
     */
    public function replace_media_core($request) {
        $attachment_id = intval($request->get_param('id'));
        $files = $request->get_file_params();
        $file = $files['file'] ?? null;
        
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return new \WP_Error('no_file', __('No valid file uploaded.', self::TEXTDOMAIN), ['status' => 400]);
        }

        // Check if attachment exists
        $attachment = get_post($attachment_id);
        if (!$attachment || $attachment->post_type !== 'attachment') {
            return new \WP_Error('attachment_not_found', __('Media attachment not found.', self::TEXTDOMAIN), ['status' => 404]);
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        // Get original file info
        $original_file = get_attached_file($attachment_id);
        $original_file_info = pathinfo($original_file);

        try {
            // Create backup of original file
            $backup_file = $original_file . '.backup.' . time();
            if (file_exists($original_file)) {
                copy($original_file, $backup_file);
            }

            // Generate new filename with same base name but new extension
            $new_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = $original_file_info['filename'] . '.' . $new_extension;
            $new_file_path = $original_file_info['dirname'] . '/' . $new_filename;

            // Move uploaded file to replace original
            if (!move_uploaded_file($file['tmp_name'], $new_file_path)) {
                // Restore backup if move failed
                if (file_exists($backup_file)) {
                    rename($backup_file, $original_file);
                }
                return new \WP_Error('file_move_error', __('Failed to move uploaded file.', self::TEXTDOMAIN), ['status' => 500]);
            }

            // Delete old file if it's different from new file
            if (file_exists($original_file) && $original_file !== $new_file_path) {
                unlink($original_file);
            }

            // Update attachment metadata
            update_attached_file($attachment_id, $new_file_path);
            
            // Update MIME type
            $new_mime_type = wp_check_filetype($new_file_path)['type'];
            wp_update_post(array(
                'ID' => $attachment_id,
                'post_mime_type' => $new_mime_type,
                'post_title' => $request->get_param('title') ?: $attachment->post_title,
            ));

            // Generate new thumbnails and metadata
            $metadata = wp_generate_attachment_metadata($attachment_id, $new_file_path);
            wp_update_attachment_metadata($attachment_id, $metadata);

            // Update alt text if provided
            if ($alt = $request->get_param('alt')) {
                update_post_meta($attachment_id, '_wp_attachment_image_alt', sanitize_text_field($alt));
            }

            // Update the SHA-256 hash for the new file
            if (file_exists($new_file_path)) {
                $hash = hash_file('sha256', $new_file_path);
                if ($hash) {
                    update_post_meta($attachment_id, '_sha256_hash', $hash);
                }
            }

            // Clean up backup after successful replacement
            if (file_exists($backup_file)) {
                unlink($backup_file);
            }

            // Clear any caches
            clean_attachment_cache($attachment_id);
            wp_cache_flush();

            $updated_attachment = get_post($attachment_id);
            $new_url = wp_get_attachment_url($attachment_id);

            return new \WP_REST_Response([
                'success' => true,
                'id' => $attachment_id,
                'title' => $updated_attachment->post_title,
                'url' => $new_url,
                'mime' => get_post_mime_type($attachment_id),
                'message' => __('Media file replaced successfully.', self::TEXTDOMAIN),
                'replaced' => true,
            ], 200);

        } catch (Exception $e) {
            // Restore backup on any error
            if (isset($backup_file) && file_exists($backup_file)) {
                if (file_exists($new_file_path)) {
                    unlink($new_file_path);
                }
                rename($backup_file, $original_file);
            }
            
            return new \WP_Error('replacement_failed', $e->getMessage(), ['status' => 500]);
        }
    }
}

new Class_Media_Manager();
