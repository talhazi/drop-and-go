<?php

namespace Rightplace\Features;

class Collaboration {
    public function __construct() {
        // Register the filter hooks for collaboration features
        add_filter('rightplace_action_filter/collaboration/registerAsPeer', array($this, 'register_as_peer'));
        add_filter('rightplace_action_filter/collaboration/getAvailablePeers', array($this, 'get_available_peers'));
        add_filter('rightplace_action_filter/collaboration/getPeerId', array($this, 'get_peer_id'));
    }

    /**
     * Register the current user as a peer and generate a peer ID
     * 
     * @param array $params Parameters from the request
     * @return array Response with peer ID and available peers
     */
    public function register_as_peer($params) {
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            return [
                'success' => false,
                'message' => 'User not authenticated'
            ];
        }
        
        // always generate a new peer id
        $peer_id = 'rp_' . wp_generate_uuid4();
        update_user_meta($user_id, 'rightplace_peer_id', $peer_id);
        
        // Save the rightplace_user_id if not already set
        $rightplace_user_id = get_user_meta($user_id, 'rightplace_user_id', true);
        if (empty($rightplace_user_id)) {
            $rightplace_user_id = wp_generate_uuid4();
            update_user_meta($user_id, 'rightplace_user_id', $rightplace_user_id);
        }
        
        // Get available peers
        $available_peers = $this->get_available_peers_data();
        
        return [
            'success' => true,
            'peer_id' => $peer_id,
            'rightplace_user_id' => $rightplace_user_id,
            'available_peers' => $available_peers
        ];
    }

    /**
     * Get all available peers
     * 
     * @param array $params Parameters from the request
     * @return array List of available peers
     */
    public function get_available_peers($params) {
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            return [
                'success' => false,
                'message' => 'User not authenticated'
            ];
        }
        
        $my_peer_id = get_user_meta($user_id, 'rightplace_peer_id', true);
        
        $available_peers = $this->get_available_peers_data();
        
        return [
            'success' => true,
            'available_peers' => $available_peers,
            'my_peer_id' => $my_peer_id
        ];
    }

    /**
     * Get the peer ID for the current user
     * 
     * @param array $params Parameters from the request
     * @return array Response with the peer ID
     */
    public function get_peer_id($params) {
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            return [
                'success' => false,
                'message' => 'User not authenticated'
            ];
        }
        
        $peer_id = get_user_meta($user_id, 'rightplace_peer_id', true);
        
        if (empty($peer_id)) {
            return [
                'success' => false,
                'message' => 'No peer ID found for this user'
            ];
        }
        
        return [
            'success' => true,
            'peer_id' => $peer_id,
        ];
    }

    /**
     * Helper function to get all available peers data
     * 
     * @return array List of available peers with their IDs and user IDs
     */
    private function get_available_peers_data() {
        $available_peers = [];
        $current_user_id = get_current_user_id();
        
        // Get all users with a rightplace_peer_id
        $users = get_users([
            'meta_key' => 'rightplace_peer_id',
            'meta_compare' => 'EXISTS'
        ]);


        foreach ($users as $user) {
            // Skip the current user
            if ($user->ID === $current_user_id) {
                continue;
            }
            
            $peer_id = get_user_meta($user->ID, 'rightplace_peer_id', true);
            $rightplace_user_id = get_user_meta($user->ID, 'rightplace_user_id', true);
            
            if (!empty($peer_id)) {
                $available_peers[] = [
                    'peer_id' => $peer_id,
                    'rightplace_user_id' => $rightplace_user_id,
                    'user_id' => $user->ID,
                    'display_name' => $user->display_name
                ];
            }
        }
        
        return $available_peers;
    }
}

// Initialize the Collaboration class
new Collaboration(); 