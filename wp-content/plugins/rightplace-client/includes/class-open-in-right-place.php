<?php
namespace RightPlace;
/**
 * Open in Right Place Desktop App
 *
 * @link       https://rightplace.app
 * @since      1.0.0
 *
 * @package    Rightplace_Client
 * @subpackage Rightplace_Client/includes
 */

/**
 * @author     WiredWP <ryan@wiredwp.com>
 */
class Class_Open_In_Right_Place {

    /**
     * Custom protocol to be used in the button (e.g., rightplace://open).
     */
    private $custom_protocol = 'rightplace://open';

    /**
     * Initialize the plugin.
     */
    public function __construct() {
        if (!rightplace_is_called_by_electron_webview()) {
            add_action('admin_bar_menu', [$this, 'add_admin_bar_button'], 100);
            add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        } else {
            add_action('init', [$this, 'redirect_to_download']);
        }
    }
 
    /**
     * Redirect to the download page if not ElectronWebView.
     */
    public function redirect_to_download() {
        if (!is_admin()) { // Ensure this only runs on the front-end
            wp_redirect('https://rightplace.com/download');
            exit;
        }
    }

    /**
     * Add a button to the admin bar.
     *
     * @param WP_Admin_Bar $wp_admin_bar
     */
    public function add_admin_bar_button($wp_admin_bar) {
        // Only show to admin users who have the right capabilities
        if (!is_admin_bar_showing() || !current_user_can('manage_options')) {
            return;
        }

        if (rightplace_is_called_by_electron()) {
            return;
        }
    
        $protocol_url = esc_url($this->custom_protocol);
    
        $wp_admin_bar->add_node([
            'id'    => 'open-in-rightplace',
            'title' => __('Open in RightPlace', 'open-in-rightplace'),
            'href'  => '#',
            'meta'  => [
                'class' => 'open-in-rightplace-button',
                'title' => __('Open in RightPlace App'),
            ]
        ]);
    }
    

    /**
     * Enqueue necessary scripts and styles.
     */
    public function enqueue_scripts() {
        // Load the script and style only if the user is logged in
        if (is_user_logged_in()) {
            // Inline script to replace div with a proper anchor tag
            add_action('admin_footer', function() {
                ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var button = document.querySelector('#wp-admin-bar-open-in-rightplace .ab-item');
                        if (button) {
                            var link = document.createElement('a');
                            link.href = 'rightplace://open';
                            link.textContent = button.textContent;
                            link.className = button.className;
                            link.target = '_blank';
                            button.replaceWith(link);
                        }
                    });
                </script>
                <?php
            });
        }
    }

    /**
     * Add a custom link to the WordPress footer for logged-in users.
     */
    public function add_custom_protocol_link() {
        if (is_user_logged_in()) {
            echo '<div class="rightplace-link-wrapper" style="text-align:center; margin-top:20px;">';
            echo '<a href="' . esc_url($this->custom_protocol) . '" class="rightplace-link" style="padding:10px 20px; background-color:#0073aa; color:#fff; text-decoration:none; border-radius:4px;">' . esc_html__('Open in RightPlace App', 'open-in-rightplace') . '</a>';
            echo '</div>';
        }
    }
}


