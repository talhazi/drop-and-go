<?php

/**
 * Custom Table Manager for RightPlace Plugin
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
 * Custom Table Manager Class
 *
 * Generic database table manager with ORM-like functionality
 * Follows WordPress conventions for security and best practices
 *
 * @since      1.0.0
 * @package    Rightplace_Client
 * @subpackage Rightplace_Client/includes/db
 * @author     WiredWP <ryan@wiredwp.com>
 */
abstract class Custom_Table_Manager
{
    /**
     * Database version - increment when table structure changes
     */
    const DB_VERSION = '1.0.0';

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    protected function init_hooks(): void
    {
        add_action('plugins_loaded', [$this, 'maybe_upgrade_schema']);
    }

    /**
     * Get table name with prefix
     */
    public static function get_table_name(string $table): string
    {
        global $wpdb;
        return $wpdb->prefix . $table;
    }

    /**
     * Install or upgrade database schema
     */
    public function install_or_upgrade_schema(): void
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Create tables defined by child classes
        $this->create_tables($charset_collate);

        // Update database version
        update_option($this->get_db_version_option_name(), static::DB_VERSION);
    }

    /**
     * Check if schema needs upgrading
     */
    public function maybe_upgrade_schema(): void
    {
        $installed_version = get_option($this->get_db_version_option_name(), '0.0.0');
        
        if (version_compare($installed_version, static::DB_VERSION, '<')) {
            $this->install_or_upgrade_schema();
        }
    }

    /**
     * Generic insert method
     */
    public function insert(string $table, array $data, array $format = []): int|false
    {
        global $wpdb;

        $table_name = self::get_table_name($table);
        
        $result = $wpdb->insert($table_name, $data, $format);
        
        if ($result === false) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Generic update method
     */
    public function update(string $table, array $data, array $where, array $format = [], array $where_format = []): int|false
    {
        global $wpdb;

        $table_name = self::get_table_name($table);
        
        return $wpdb->update($table_name, $data, $where, $format, $where_format);
    }

    /**
     * Generic delete method
     */
    public function delete(string $table, array $where, array $where_format = []): int|false
    {
        global $wpdb;

        $table_name = self::get_table_name($table);
        
        return $wpdb->delete($table_name, $where, $where_format);
    }

    /**
     * Generic get method
     */
    public function get(string $table, array $where = [], string $output_type = ARRAY_A, int $limit = 0): array|object|null
    {
        global $wpdb;

        $table_name = self::get_table_name($table);
        
        $sql = "SELECT * FROM {$table_name}";
        $values = [];

        if (!empty($where)) {
            $where_clauses = [];
            foreach ($where as $key => $value) {
                $where_clauses[] = "{$key} = %s";
                $values[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where_clauses);
        }

        if ($limit > 0) {
            $sql .= " LIMIT " . intval($limit);
        }

        if (!empty($values)) {
            $sql = $wpdb->prepare($sql, $values);
        }

        if ($limit === 1) {
            return $wpdb->get_row($sql, $output_type);
        }

        return $wpdb->get_results($sql, $output_type);
    }

    /**
     * Generic get all method
     */
    public function get_all(string $table, array $where = [], string $order_by = '', string $order = 'ASC', int $limit = 0, int $offset = 0): array
    {
        global $wpdb;

        $table_name = self::get_table_name($table);
        
        $sql = "SELECT * FROM {$table_name}";
        $values = [];

        if (!empty($where)) {
            $where_clauses = [];
            foreach ($where as $key => $value) {
                $where_clauses[] = "{$key} = %s";
                $values[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where_clauses);
        }

        if (!empty($order_by)) {
            $sql .= " ORDER BY {$order_by} {$order}";
        }

        if ($limit > 0) {
            $sql .= " LIMIT " . intval($limit);
            if ($offset > 0) {
                $sql .= " OFFSET " . intval($offset);
            }
        }

        if (!empty($values)) {
            $sql = $wpdb->prepare($sql, $values);
        }

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Generic count method
     */
    public function count(string $table, array $where = []): int
    {
        global $wpdb;

        $table_name = self::get_table_name($table);
        
        $sql = "SELECT COUNT(*) FROM {$table_name}";
        $values = [];

        if (!empty($where)) {
            $where_clauses = [];
            foreach ($where as $key => $value) {
                $where_clauses[] = "{$key} = %s";
                $values[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where_clauses);
        }

        if (!empty($values)) {
            $sql = $wpdb->prepare($sql, $values);
        }

        return (int) $wpdb->get_var($sql);
    }

    /**
     * Execute raw SQL query safely
     */
    public function query(string $sql, array $values = []): mixed
    {
        global $wpdb;

        if (!empty($values)) {
            $sql = $wpdb->prepare($sql, $values);
        }

        return $wpdb->query($sql);
    }

    /**
     * Get results from raw SQL query safely
     */
    public function get_results(string $sql, array $values = [], string $output_type = ARRAY_A): array
    {
        global $wpdb;

        if (!empty($values)) {
            $sql = $wpdb->prepare($sql, $values);
        }

        return $wpdb->get_results($sql, $output_type);
    }

    /**
     * Get single row from raw SQL query safely
     */
    public function get_row(string $sql, array $values = [], string $output_type = ARRAY_A): array|object|null
    {
        global $wpdb;

        if (!empty($values)) {
            $sql = $wpdb->prepare($sql, $values);
        }

        return $wpdb->get_row($sql, $output_type);
    }

    /**
     * Get single value from raw SQL query safely
     */
    public function get_var(string $sql, array $values = []): string|null
    {
        global $wpdb;

        if (!empty($values)) {
            $sql = $wpdb->prepare($sql, $values);
        }

        return $wpdb->get_var($sql);
    }

    /**
     * Abstract methods that must be implemented by child classes
     */

    /**
     * Create tables - must be implemented by child classes
     */
    abstract protected function create_tables(string $charset_collate): void;

    /**
     * Get database version option name - must be implemented by child classes
     */
    abstract protected function get_db_version_option_name(): string;

    /**
     * Get table names for cleanup - must be implemented by child classes
     */
    abstract protected function get_table_names(): array;

    /**
     * Cleanup on plugin uninstall
     */
    public static function uninstall(): void
    {
        global $wpdb;

        $instance = static::get_instance();
        $tables = $instance->get_table_names();

        foreach ($tables as $table) {
            $table_name = self::get_table_name($table);
            $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
        }

        // Remove options
        delete_option($instance->get_db_version_option_name());
    }
} 