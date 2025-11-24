<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class X_Panorama_Scene extends \Bricks\Element {

  // Element properties
  public $category     = 'extras';
	public $name         = 'xpanoramascene';
	public $icon         = 'ti-image';
	public $css_selector = '';
	public $scripts      = ['xPanoramaViewer'];
    public $loop_index   = 0;
    public $nestable = true;

  
  public function get_label() {
	return esc_html__( 'Panorama Scene', 'extras' );
  }

  public function set_control_groups() {

    $this->control_groups['hotspots'] = [
      'title' => esc_html__( 'Scene Hotspots', 'bricks' ),
      'tab' => 'content',
    ];

    $this->control_groups['viewer'] = [
      'title' => esc_html__( 'Viewer Settings', 'bricks' ),
      'tab' => 'content',
    ];

    


  }

  public function set_controls() {

   // $this->controls = array_replace_recursive( $this->controls, $this->get_loop_builder_controls() );

    // Scene ID (for tours)
    $this->controls['sceneId'] = [
      'tab' => 'content',
      'label' => esc_html__( 'Scene ID', 'bricks' ),
      'type' => 'text',
      'inline' => true,
      'description' => esc_html__( 'Unique identifier for this scene', 'bricks' ),
    ];

    // Panorama Image
    $this->controls['panoramaImage'] = [
      'tab' => 'content',
      'label' => esc_html__( 'Scene Image', 'bricks' ),
      'type' => 'image',
      'description' => esc_html__( 'Upload a panorama image', 'bricks' ),
    ];

    // Query Loop
    $this->controls['hasLoop'] = [
      'tab' => 'content',
      'group' => 'hotspots',
      'label' => esc_html__( 'Use query loop', 'bricks' ),
      'type' => 'checkbox',
      'inline' => true,
      'description' => esc_html__( 'Populate hotspots from query results or add manually below', 'bricks' ),
    ];

    $this->controls['query'] = [
      'tab' => 'content',
      'group' => 'hotspots',
      'label' => esc_html__( 'Query', 'bricks' ),
      'type' => 'query',
      'popup' => true,
      'inline' => true,
      'required' => [ 'hasLoop', '!=', '' ],
    ];

    // Hot Spots
    $this->controls['hotSpots'] = [
      'tab' => 'content',
      'group' => 'hotspots',
      'placeholder' => esc_html__( 'Hot Spots', 'bricks' ),
      'title' => esc_html__( 'Add Hot Spots', 'bricks' ),
      'type' => 'repeater',
      'titleProperty' => 'buttonText',
      'required' => [ 'hasLoop', '=', '' ],
      'fields' => [
        'pitch' => [
          'label' => esc_html__( 'Pitch', 'bricks' ),
          'type' => 'number',
          'placeholder' => '0',
          'description' => esc_html__( 'Vertical position in degrees', 'bricks' ),
        ],
        'yaw' => [
          'label' => esc_html__( 'Yaw', 'bricks' ),
          'type' => 'number',
          'placeholder' => '0',
          'description' => esc_html__( 'Horizontal position in degrees', 'bricks' ),
        ],
        'icon' => [
          'label' => esc_html__( 'Icon', 'bricks' ),
          'type' => 'icon',
        ],
        'text' => [
          'label' => esc_html__( 'Tooltip Content', 'bricks' ),
          'type' => 'editor',
          'description' => esc_html__( 'Content displayed in tooltip', 'bricks' ),
        ],
        'buttonText' => [
          'label' => esc_html__( 'Button Text', 'bricks' ),
          'inline' => true,
          'type' => 'text',
        ],
        'type' => [
          'label' => esc_html__( 'Type', 'bricks' ),
          'type' => 'select',
          'inline' => true,
          'options' => [
            'info' => esc_html__( 'Info (standard)', 'bricks' ),
            'scene' => esc_html__( 'Scene Link', 'bricks' ),
            'link' => esc_html__( 'Link', 'bricks' ),
          ],
          'placeholder' => esc_html__( 'Info (standard)', 'bricks' ),
        ],
        'link' => [
          'label' => esc_html__( 'Link', 'bricks' ),
          'type' => 'link',
          'description' => esc_html__( 'Link URL', 'bricks' ),
          'required' => [ 'type', '=', 'link' ],
        ],
        'sceneId' => [
          'label' => esc_html__( 'Scene ID', 'bricks' ),
          'type' => 'text',
          'placeholder' => 'scene-1',
          'description' => esc_html__( 'ID of the scene to link to', 'bricks' ),
          'required' => [ 'type', '=', 'scene' ],
        ],
      ],
      'default' => [],
    ];

    // Query Loop Template Fields (shown when hasLoop is enabled)
    $this->controls['queryLoopSeparator'] = [
      'tab' => 'content',
      'group' => 'hotspots',
      'type' => 'separator',
      'label' => esc_html__( 'Hotspots', 'bricks' ),
      'required' => [ 'hasLoop', '!=', '' ],
    ];

     $this->controls['hotspotPitch'] = [
      'tab' => 'content',
      'group' => 'hotspots',
      'label' => esc_html__( 'Pitch', 'bricks' ),
      'inline' => true,
      'type' => 'text',
      'placeholder' => '0',
      'description' => esc_html__( 'Vertical position in degrees', 'bricks' ),
      'required' => [ 'hasLoop', '!=', '' ],
    ];

    $this->controls['hotspotYaw'] = [
      'tab' => 'content',
      'group' => 'hotspots',
      'inline' => true,
      'label' => esc_html__( 'Yaw', 'bricks' ),
      'type' => 'text',
      'placeholder' => '0',
      'description' => esc_html__( 'Horizontal position in degrees', 'bricks' ),
      'required' => [ 'hasLoop', '!=', '' ],
    ];

    $this->controls['icon'] = [
      'tab' => 'content',
      'group' => 'hotspots',
      'label' => esc_html__( 'Icon', 'bricks' ),
      'type' => 'icon',
      'required' => [ 'hasLoop', '!=', '' ],
    ];

    $this->controls['hotspotText'] = [
      'tab' => 'content',
      'group' => 'hotspots',
      'label' => esc_html__( 'Tooltip Content', 'bricks' ),
      'type' => 'editor',
      'description' => esc_html__( 'Content displayed in tooltip', 'bricks' ),
      'required' => [ 'hasLoop', '!=', '' ],
    ];

    $this->controls['buttonText'] = [
      'tab' => 'content',
      'group' => 'hotspots',
      'label' => esc_html__( 'Button Text', 'bricks' ),
      'type' => 'text',
      'inline' => true,
      'required' => [ 'hasLoop', '!=', '' ],
    ];

    $this->controls['hotspotType'] = [
      'tab' => 'content',
      'group' => 'hotspots',
      'label' => esc_html__( 'Type', 'bricks' ),
      'type' => 'text',
      'inline' => true,
      'placeholder' => esc_html__( 'info', 'bricks' ),
      'info' => esc_html__( 'info, scene_link or link', 'bricks' ),
      'required' => [ 'hasLoop', '!=', '' ],
    ];

    $this->controls['hotspotLink'] = [
      'tab' => 'content',
      'group' => 'hotspots',
      'label' => esc_html__( 'Link URL  (used when type is "link")', 'bricks' ),
      'type' => 'text',
      'inline' => true,
      'required' => [ 'hasLoop', '!=', '' ],
    ];

    $this->controls['hotspotSceneId'] = [
      'tab' => 'content',
      'group' => 'hotspots',
      'label' => esc_html__( 'Scene ID (used when type is "scene")', 'bricks' ),
      'type' => 'text',
      'inline' => true,
      'required' => [ 'hasLoop', '!=', '' ],
    ];

   

    // Viewer Settings Group - autoLoad should only be on viewer, not scene

    $this->controls['viewerSceneSettingsSeparator'] = [
      'tab' => 'content',
      'group' => 'viewer',
      'type' => 'separator',
      'label' => esc_html__( 'Override global settings for this scene', 'bricks' ),
    ];

    $this->controls['pitch'] = [
      'tab' => 'content',
      'group' => 'viewer',
      'label' => esc_html__( 'Initial Pitch', 'bricks' ),
      'type' => 'text',
      'units' => false,
      'description' => esc_html__( 'Initial vertical angle in degrees', 'bricks' ),
    ];

    $this->controls['yaw'] = [
      'tab' => 'content',
      'group' => 'viewer',
      'label' => esc_html__( 'Initial Yaw', 'bricks' ),
      'type' => 'text',
      'units' => false,
      'description' => esc_html__( 'Initial horizontal angle in degrees', 'bricks' ),
    ];

    $this->controls['hfov'] = [
      'tab' => 'content',
      'group' => 'viewer',
      'label' => esc_html__( 'Initial Field of View', 'bricks' ),
      'type' => 'text',
      'units' => false,
      'description' => esc_html__( 'Initial horizontal field of view in degrees', 'bricks' ),
    ];

    $this->controls['autoRotate'] = [
      'tab' => 'content',
      'group' => 'viewer',
      'label' => esc_html__( 'Auto Rotate Speed', 'bricks' ),
      'type' => 'text',
      'units' => false,
      'description' => esc_html__( 'Rotation speed in degrees per second (0 = disabled)', 'bricks' ),
    ];


  }

  // Methods: Frontend-specific
  public function enqueue_scripts() {

    if ( bricks_is_builder_main() ) {
      return;
    }

    // Enqueue our custom script
    wp_enqueue_script( 'x-panoramaviewer', BRICKSEXTRAS_URL . 'components/assets/js/' . \BricksExtras\Helpers::maybeMinifyScripts('panoramaviewer') . '.js', '', \BricksExtras\Plugin::VERSION, true );

    if (! \BricksExtras\Helpers::elementCSSAdded($this->name) ) {
      wp_enqueue_style( 'x-panoramaviewer', BRICKSEXTRAS_URL . 'components/assets/css/panoramaviewer.css', [], \BricksExtras\Plugin::VERSION );
    }
  }
  
  public function render() {

    $settings = $this->settings;

    // Get panorama image using helper function (force original image for panoramas)
    $panorama_image = \BricksExtras\Helpers::get_normalized_image_settings( $settings, 'panoramaImage', true );
    
    // On frontend, return early if no image
    if ( ( empty( $panorama_image ) || empty( $panorama_image['url'] ) ) && ! \BricksExtras\Helpers::maybePreview() ) {
      return $this->render_element_placeholder( [
        'title' => esc_html__( 'No panorama image selected', 'bricks' ),
      ] ); 
    }

    $panorama_url = ! empty( $panorama_image['url'] ) ? esc_url( bricks_render_dynamic_data( $panorama_image['url'] ) ) : '';

    // Build configuration
    $config = [
      'type' => 'equirectangular',
      'panorama' => $panorama_url,
      'autoLoad' => true,
      'crossOrigin' => 'anonymous',
    ];

    // Add scene ID if set (for tours)
    if ( ! empty( $settings['sceneId'] ) ) {
      $config['sceneId'] = sanitize_key( bricks_render_dynamic_data( $settings['sceneId'] ) );
    }

    // Optional settings - inherit from viewer if not set on scene
    if ( isset( $settings['autoRotate'] ) && $settings['autoRotate'] != '' ) {
      $config['autoRotate'] = floatval( $settings['autoRotate'] );
    }

    // Yaw - use scene setting or inherit from viewer
    if ( isset( $settings['yaw'] ) && $settings['yaw'] != '' ) {
      $config['yaw'] = floatval( $settings['yaw'] );
    }

    // Pitch - use scene setting or inherit from viewer
    if ( isset( $settings['pitch'] ) && $settings['pitch'] != '' ) {
      $config['pitch'] = floatval( $settings['pitch'] );
    } 

    if ( isset( $settings['hfov'] ) && $settings['hfov'] != '' ) {
      $config['hfov'] = floatval( $settings['hfov'] );
    }

    // Generate and set a unique identifier for this instance
		$indentifier = \BricksExtras\Helpers::set_identifier_attribute( $this ); 

    // Set attributes
    $this->set_attribute( '_root', 'data-panorama-config', htmlspecialchars( wp_json_encode( $config, JSON_HEX_APOS | JSON_HEX_QUOT ), ENT_QUOTES, 'UTF-8' ) );

    echo "<div {$this->render_attributes( '_root' )}>";

    if ( method_exists('\Bricks\Frontend','render_children') ) {
        echo \Bricks\Frontend::render_children( $this );
    }
    
    // Render hotspots as DOM elements
    $this->render_hotspots_dom( $settings );
    
    echo "</div>";
    
  }

  /**
   * Render hotspots as DOM elements
   */
  private function render_hotspots_dom( $settings ) {
    $hotSpots_setting = empty( $settings['hotSpots'] ) ? false : $settings['hotSpots'];
    $has_query_loop = isset( $settings['hasLoop'] );
    
    // Only render container if we have hotspots
    if ( !$hotSpots_setting && !$has_query_loop ) {
      return;
    }
    
    echo '<div class="x-hotspot-data" style="display: none;">';
    
    // Render query loop hotspots (takes priority over manual hotspots)
    if ( $has_query_loop ) {
      $query = new \Bricks\Query(
        [
          'id' => $this->id,
          'settings' => $settings,
        ]
      );
      
      // Use Bricks query render method with callback
      $query->render( [ $this, 'render_hotspot_item' ], [] );
      
      $query->destroy();
      unset( $query );
    } elseif ( $hotSpots_setting ) {
      // Render manual hotspots from repeater (only when query loop is NOT enabled)
      foreach ( $hotSpots_setting as $hotspot_data ) {
        $this->render_manual_hotspot( $hotspot_data );
      }
    }
    
    echo '</div>';
  }
  
  /**
   * Render a single manual hotspot as DOM element
   */
  private function render_manual_hotspot( $hotspot_data ) {
    // Default yaw and pitch to 0 if not provided
    $pitch = isset( $hotspot_data['pitch'] ) && $hotspot_data['pitch'] !== '' ? floatval( bricks_render_dynamic_data( $hotspot_data['pitch'] ) ) : 0;
    $yaw = isset( $hotspot_data['yaw'] ) && $hotspot_data['yaw'] !== '' ? floatval( bricks_render_dynamic_data( $hotspot_data['yaw'] ) ) : 0;
    $type = isset( $hotspot_data['type'] ) ? sanitize_key( $hotspot_data['type'] ) : 'info';
    
    $attributes = [
      'class' => 'x-hotspot-item',
      'data-pitch' => $pitch,
      'data-yaw' => $yaw,
      'data-type' => $type,
    ];
    
    // Add type-specific attributes
    if ( $type === 'link' && !empty( $hotspot_data['link'] ) ) {
      $link_data = $hotspot_data['link'];
      $url = '';
      if ( is_array( $link_data ) && isset( $link_data['url'] ) ) {
        $url = bricks_render_dynamic_data( $link_data['url'] );
      } elseif ( is_string( $link_data ) ) {
        $url = bricks_render_dynamic_data( $link_data );
      }
      if ( $url ) {
        $attributes['data-link'] = esc_url( $url );
      }
    } elseif ( $type === 'scene' && !empty( $hotspot_data['sceneId'] ) ) {
      $attributes['data-scene-id'] = sanitize_key( bricks_render_dynamic_data( $hotspot_data['sceneId'] ) );
    }
    
    // Build attributes string
    $attr_string = '';
    foreach ( $attributes as $key => $value ) {
      $attr_string .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
    }
    
    echo '<div' . $attr_string . '>';
    
    // Render icon
    if ( !empty( $hotspot_data['icon'] ) ) {
      // Render dynamic data first to check if it has actual content
      $icon_html = self::render_icon( $hotspot_data['icon'] );
      // Check if icon has meaningful content (not just base classes like "dashicons ")
      if ( !empty( $icon_html ) && !preg_match('/class="[^"]*dashicons\s+"/', $icon_html) ) {
        echo '<div class="x-hotspot-icon">';
        echo $icon_html;
        echo '</div>';
      }
    }
    
    // Render button text (if set)
    if ( !empty( $hotspot_data['buttonText'] ) ) {
      echo '<div class="x-hotspot-button-text">';
      echo esc_html( bricks_render_dynamic_data( $hotspot_data['buttonText'] ) );
      echo '</div>';
    }
    
    // Render text content
    if ( !empty( $hotspot_data['text'] ) ) {
      echo '<div class="x-hotspot-text">';
      echo bricks_render_dynamic_data( $hotspot_data['text'] );
      echo '</div>';
    }
    
    echo '</div>';
  }

  /**
   * Render callback for query loop hotspots
   */
  public function render_hotspot_item() {
    $settings = $this->settings;
    
    // Get dynamic data for coordinates
    $pitch = isset( $settings['hotspotPitch'] ) ? intval(bricks_render_dynamic_data( $settings['hotspotPitch'] )) : 0;
    $yaw = isset( $settings['hotspotYaw'] ) ? intval(bricks_render_dynamic_data( $settings['hotspotYaw'] )) : 0;
    $type = isset( $settings['hotspotType'] ) ? bricks_render_dynamic_data( $settings['hotspotType'] ) : 'info';
    
    // Validate coordinates
    if ( $pitch === '' && $yaw === '' ) {
      return '';
    }
    
    $attributes = [
      'class' => 'x-hotspot-item',
      'data-pitch' => $pitch,
      'data-yaw' => $yaw,
      'data-type' => sanitize_key( $type ),
    ];
    
    // Add type-specific attributes
    if ( $type === 'link' && isset( $settings['hotspotLink'] ) ) {
      $link = bricks_render_dynamic_data( $settings['hotspotLink'] );
      if ( $link ) {
        $attributes['data-link'] = esc_url( $link );
      }
    } elseif ( $type === 'scene' && isset( $settings['hotspotSceneId'] ) ) {
      $scene_id = bricks_render_dynamic_data( $settings['hotspotSceneId'] );
      if ( $scene_id ) {
        $attributes['data-scene-id'] = sanitize_key( $scene_id );
      }
    }
    
    // Build attributes string
    $attr_string = '';
    foreach ( $attributes as $key => $value ) {
      $attr_string .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
    }
    
    echo '<div' . $attr_string . '>';
    
    // Render icon
    if ( isset( $settings['icon'] ) && !empty( $settings['icon'] ) ) {
      // Render dynamic data first to check if it has actual content
      $icon_html = self::render_icon( $settings['icon'] );

      // Check if icon has meaningful content (not just base classes like "dashicons ")
      if ( !empty( $icon_html ) && !preg_match('/class="[^"]*dashicons\s+"/', $icon_html) ) {
        // Icon has specific icon class, not just base class
        echo '<div class="x-hotspot-icon">';
        echo $icon_html;
        echo '</div>';
      }
    }
    
    // Render button text (if set)
    if ( isset( $settings['buttonText'] ) ) {
      $button_text = bricks_render_dynamic_data( $settings['buttonText'] );
      if ( $button_text ) {
        echo '<div class="x-hotspot-button-text">' . wp_kses_post( $button_text ) . '</div>';
      }
    }
    
    // Render text content
    if ( isset( $settings['hotspotText'] ) ) {
      $content = $this->settings['hotspotText'];
      $content = $this->render_dynamic_data( $content );
      $content = \Bricks\Helpers::parse_editor_content( $content );
      if ( $content ) {
        echo '<div class="x-hotspot-text">' . wp_kses_post( $content ) . '</div>';
      }
    }
    
    echo '</div>';
    
    // Return empty string since we're outputting directly
    return '';
  }

}
