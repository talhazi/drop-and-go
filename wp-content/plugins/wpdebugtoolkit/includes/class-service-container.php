<?php
/**
 *
 * Dependency injection container 
 *
 * @package DebugToolkit
 */

namespace DebugToolkit;

/**
 * Service Container 
 */
class Service_Container {
    /**
     * Registered services
     *
     * @var array
     */
    private $services = [];
    
    /**
     * Service instances
     *
     * @var array
     */
    private $instances = [];
    
    /**
     * Register a service
     * 
     * @param string $id Service identifier
     * @param callable $factory 
     * @param array $dependencies 
     * @return void
     */
    public function register($id, $factory, $dependencies = []) {
        $this->services[$id] = [
            'factory' => $factory,
            'dependencies' => $dependencies
        ];
        
        if (isset($this->instances[$id])) {
            unset($this->instances[$id]);
        }
    }
    
    /**
     * Get a service
     * 
     * @param string $id Service identifier
     * @return mixed 
     * @throws \Exception 
     */
    public function get($id) {
        if (!isset($this->services[$id])) {
            error_log('Debug Toolkit - Service Container: Service "' . $id . '" not found in container');
            throw new \Exception(sprintf('Service "%s" not found in container', $id));
        }
        
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }
        
        try {
            if ($id === 'health_check') {
                try {

                    if (!class_exists('WP_Site_Health') && !class_exists('\\WP_Site_Health')) {
                        error_log('Debug Toolkit - Service Container: Required WordPress class WP_Site_Health not found when creating health_check service');
                    }
                    
                    if (!function_exists('wp_convert_hr_to_bytes')) {
                        error_log('Debug Toolkit - Service Container: Required WordPress function wp_convert_hr_to_bytes not found when creating health_check service');
                    }
                } catch (\Throwable $t) {
                    error_log('Debug Toolkit - Service Container: Error during health_check pre-check: ' . $t->getMessage());
                }
            }
            
            $this->instances[$id] = $this->create_service($id);
            return $this->instances[$id];
        } catch (\Throwable $e) {
            
            if (class_exists('\\DebugToolkit\\Error_Handler')) {
                try {
                    Error_Handler::log_exception($e, sprintf('Failed to create service "%s"', $id));
                } catch (\Throwable $log_error) {
                    error_log('Debug Toolkit - Service Container: Error during error logging: ' . $log_error->getMessage());
                }
            }
            
            if ($id === 'health_check') {
                require_once __DIR__ . '/services/class-health-check.php';
                return new Services\Health_Check();
            }
            
            throw $e;
        }
    }
    
    /**
     * Check if a service exists
     * 
     * @param string $id Service identifier
     * @return bool 
     */
    public function has($id) {
        return isset($this->services[$id]);
    }
    
    /**
     * Create a service
     * 
     * @param string $id Service identifier
     * @return mixed 
     * @throws \Exception 
     */
    private function create_service($id) {
        static $creating = [];
        
        if (isset($creating[$id])) {
            throw new \Exception(sprintf('Circular dependency detected for service "%s"', $id));
        }
        
        $creating[$id] = true;
        
        try {
            $service = $this->services[$id];
            $dependencies = [];
            
            foreach ($service['dependencies'] as $dependency_id) {
                $dependencies[] = $this->get($dependency_id);
            }
            
            $instance = call_user_func_array($service['factory'], $dependencies);
            
            unset($creating[$id]);
            
            return $instance;
        } catch (\Exception $e) {
            unset($creating[$id]);
            throw $e;
        }
    }
} 