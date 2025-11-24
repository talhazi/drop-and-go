<?php
namespace Rightplace\Features;

class CustomField
{
  public function __construct()
  {
    add_filter('rightplace_action_filter/custom_field/get_data', array($this, 'get_data'));
    add_filter('rightplace_action_filter/custom_field/set_data', array($this, 'set_data'));
  }

  public function get_data($params)
  {
    $config = $params['config'];

    if (empty($config)) {
      return [
        'success' => false,
        'message' => 'No config provided',
      ];
    }

    $result = [];

    // Process filters
    if (!empty($config['filters']) && is_array($config['filters'])) {
      foreach ($config['filters'] as $key => $filter_name) {
        if (!empty($filter_name)) {
          $filter_result = apply_filters($filter_name, null);
          $result[$key] = $filter_result;
        }
      }
    }

    // Process options
    if (!empty($config['options']) && is_array($config['options'])) {
      foreach ($config['options'] as $key => $option_name) {
        if (!empty($option_name)) {
          $option_value = get_option($option_name);
          $result[$key] = $option_value;
        }
      }
    }

    return [
      'success' => true,
      'data' => $result,
    ];
  }

  public function set_data($params)
  {
    $saving_method = $params['savingMethod'];
    $key = $params['key'];
    $value = $params['value'];

    try {
      switch ($saving_method) {
        case 'wordpress_filter_action':
          if (has_action($key)) {
            do_action($key, $value);
          } else {
            return [
              'success' => false,
              'message' => 'Action "' . $key . '" not found',
            ];
          }
          break;
        case 'wordpress_option':
          update_option($key, $value);
          break;
      }
    } catch (\Exception $e) {
      return [
        'success' => false,
        'message' => $e->getMessage(),
      ];
    }

    return [
      'success' => true,
      'key' => $key,
      'savingMethod' => $saving_method,
    ];
  }
}

new CustomField();
