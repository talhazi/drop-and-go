<?php

/**
 * Example Table Manager for RightPlace Plugin
 *
 * This is an example of how to extend the base Custom_Table_Manager
 * for a different business entity (e.g., bookmarks, notes, etc.)
 *
 * @link       https://rightplace.app
 * @since      1.0.0
 *
 * @package    Rightplace_Client
 * @subpackage Rightplace_Client/includes/db
 */

namespace RightPlace\DB;

defined('ABSPATH') || exit;

/**
 * Example Table Manager Class
 *
 * Example implementation of a table manager for a different business entity
 * Extends the base Custom_Table_Manager
 *
 * @since      1.0.0
 * @package    Rightplace_Client
 * @subpackage Rightplace_Client/includes/db
 * @author     WiredWP <ryan@wiredwp.com>
 */
class Example_Table_Manager extends Custom_Table_Manager
{
    /**
     * Database version for example table
     */
    const DB_VERSION = '1.0.0';

    /**
     * Table name
     */
    const TABLE_EXAMPLE = 'rp_example';

    /**
     * Create tables
     */
    protected function create_tables(string $charset_collate): void
    {
        $this->create_example_table($charset_collate);
    }

    /**
     * Create example table
     */
    private function create_example_table(string $charset_collate): void
    {
        global $wpdb;

        $table = self::get_table_name(self::TABLE_EXAMPLE);

        $sql = "
        CREATE TABLE {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT NULL,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            status ENUM('active', 'inactive', 'pending') DEFAULT 'active',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_user_id (user_id),
            KEY idx_status (status),
            KEY idx_created_at (created_at)
        ) {$charset_collate};
        ";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Get database version option name
     */
    protected function get_db_version_option_name(): string
    {
        return 'rightplace_example_db_version';
    }

    /**
     * Get table names for cleanup
     */
    protected function get_table_names(): array
    {
        return [self::TABLE_EXAMPLE];
    }

    /**
     * Business Logic Methods
     */

    /**
     * Add an example item
     */
    public function add_example_item(array $data): int|false
    {
        $defaults = [
            'name' => '',
            'description' => '',
            'user_id' => 0,
            'status' => 'active'
        ];

        $data = wp_parse_args($data, $defaults);
        
        // Validate required fields
        if (empty($data['name']) || empty($data['user_id'])) {
            return false;
        }

        // Sanitize data
        $data['name'] = sanitize_text_field($data['name']);
        $data['description'] = sanitize_textarea_field($data['description']);
        $data['user_id'] = intval($data['user_id']);
        $data['status'] = sanitize_text_field($data['status']);

        // Validate status
        if (!in_array($data['status'], ['active', 'inactive', 'pending'])) {
            $data['status'] = 'active';
        }

        return $this->insert(self::TABLE_EXAMPLE, $data);
    }

    /**
     * Get example item by ID
     */
    public function get_example_item(int $id): array|null
    {
        return $this->get(self::TABLE_EXAMPLE, ['id' => $id], ARRAY_A, 1);
    }

    /**
     * Update example item
     */
    public function update_example_item(int $id, array $data): int|false
    {
        // Sanitize data
        if (isset($data['name'])) {
            $data['name'] = sanitize_text_field($data['name']);
        }
        if (isset($data['description'])) {
            $data['description'] = sanitize_textarea_field($data['description']);
        }
        if (isset($data['user_id'])) {
            $data['user_id'] = intval($data['user_id']);
        }
        if (isset($data['status'])) {
            $data['status'] = sanitize_text_field($data['status']);
            if (!in_array($data['status'], ['active', 'inactive', 'pending'])) {
                $data['status'] = 'active';
            }
        }

        return $this->update(self::TABLE_EXAMPLE, $data, ['id' => $id]);
    }

    /**
     * Delete example item
     */
    public function delete_example_item(int $id): int|false
    {
        return $this->delete(self::TABLE_EXAMPLE, ['id' => $id]);
    }

    /**
     * Get example items by user ID
     */
    public function get_example_items_by_user(int $user_id, string $status = null): array
    {
        $where = ['user_id' => $user_id];
        
        if ($status !== null) {
            $where['status'] = $status;
        }

        return $this->get_all(self::TABLE_EXAMPLE, $where, 'created_at', 'DESC');
    }

    /**
     * Get active example items
     */
    public function get_active_example_items(): array
    {
        return $this->get_all(self::TABLE_EXAMPLE, ['status' => 'active'], 'created_at', 'DESC');
    }

    /**
     * Search example items by name
     */
    public function search_example_items_by_name(string $search_term, int $user_id = null): array
    {
        global $wpdb;
        
        $sql = "SELECT * FROM " . self::get_table_name(self::TABLE_EXAMPLE) . 
               " WHERE name LIKE %s";
        $values = ['%' . $wpdb->esc_like($search_term) . '%'];

        if ($user_id !== null) {
            $sql .= " AND user_id = %d";
            $values[] = $user_id;
        }

        $sql .= " ORDER BY created_at DESC";

        return $this->get_results($sql, $values);
    }

    /**
     * Get example items count by status
     */
    public function get_example_items_count_by_status(string $status): int
    {
        return $this->count(self::TABLE_EXAMPLE, ['status' => $status]);
    }

    /**
     * Bulk update example items by user
     */
    public function update_example_items_by_user(int $user_id, array $data): int|false
    {
        // Sanitize data
        if (isset($data['status'])) {
            $data['status'] = sanitize_text_field($data['status']);
            if (!in_array($data['status'], ['active', 'inactive', 'pending'])) {
                $data['status'] = 'active';
            }
        }

        return $this->update(self::TABLE_EXAMPLE, $data, ['user_id' => $user_id]);
    }

    /**
     * Get table model for ORM functionality
     */
    public function get_table_model(): array
    {
        return [
            'table' => self::get_table_name(self::TABLE_EXAMPLE),
            'primary_key' => 'id',
            'fields' => [
                'id' => ['type' => 'int', 'auto_increment' => true, 'primary' => true],
                'name' => ['type' => 'varchar', 'length' => 255, 'required' => true],
                'description' => ['type' => 'text', 'required' => false],
                'user_id' => ['type' => 'int', 'required' => true, 'foreign_key' => 'users'],
                'status' => ['type' => 'enum', 'values' => ['active', 'inactive', 'pending'], 'default' => 'active'],
                'created_at' => ['type' => 'datetime', 'default' => 'CURRENT_TIMESTAMP'],
                'updated_at' => ['type' => 'datetime', 'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
            ],
            'indexes' => [
                'idx_user_id' => ['columns' => ['user_id']],
                'idx_status' => ['columns' => ['status']],
                'idx_created_at' => ['columns' => ['created_at']]
            ]
        ];
    }
}

// Note: This example table manager is not automatically initialized
// Uncomment the line below to enable it:
// Example_Table_Manager::get_instance(); 