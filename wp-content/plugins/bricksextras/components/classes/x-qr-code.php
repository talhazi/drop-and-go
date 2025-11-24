<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class X_Qr_Code extends \Bricks\Element {

  // Element properties
  public $category     = 'extras';
	public $name         = 'xqrcode';
	public $icon         = 'ion-ios-keypad';
	public $css_selector = '';
	public $scripts      = ['xQrCode'];
  private static $script_localized = false;

  
  public function get_label() {
	return esc_html__( 'QR Code', 'bricks' );
  }
  
  public function set_control_groups() {

    $this->control_groups['dots'] = [
      'title' => esc_html__( 'Dots', 'bricks' ),
      'tab' => 'content',
    ];

    $this->control_groups['corners'] = [
      'title' => esc_html__( 'Corners', 'bricks' ),
      'tab' => 'content',
    ];

    /*
    $this->control_groups['image'] = [
      'title' => esc_html__( 'Image / Logo', 'bricks' ),
      'tab' => 'content',
    ];
    */

    
    $this->control_groups['config'] = [
      'title' => esc_html__( 'Config', 'bricks' ),
      'tab' => 'content',
    ];
  }

  public function set_controls() {

    
    $this->controls['data'] = [
      'tab' => 'content',
      'label' => esc_html__( 'QR Code Data', 'bricks' ),
      'type' => 'textarea',
      'placeholder' => esc_url( home_url( '/' ) ),
    ];

    $this->controls['shape'] = [
      'tab' => 'content',
      'inline' => true,
      'label' => esc_html__( 'Shape', 'bricks' ),
      'type' => 'select',
      'options' => [
        'square' => esc_html__( 'Square', 'bricks' ),
        'circle' => esc_html__( 'Circle', 'bricks' ),
      ],
      'placeholder' => esc_html__( 'Square', 'bricks' ),
    ];
    
    $this->controls['outputType'] = [
      'tab' => 'content',
      'inline' => true,
      'label' => esc_html__( 'Render type', 'bricks' ),
      'type' => 'select',
      'group' => 'config',
      'options' => [
        'canvas' => esc_html__( 'Canvas', 'bricks' ),
        'svg' => esc_html__( 'SVG', 'bricks' ),
      ],
      'placeholder' => esc_html__( 'Canvas', 'bricks' ),
    ];

    // QR Options
    $this->controls['errorCorrectionLevel'] = [
      'tab' => 'content',
      'group' => 'config',
      'label' => esc_html__( 'Error Correction', 'bricks' ),
      'inline' => true,
      'type' => 'select',
      'options' => [
        'L' => esc_html__( 'Low (~7%)', 'bricks' ),
        'M' => esc_html__( 'Medium (~15%)', 'bricks' ),
        'Q' => esc_html__( 'Quartile (~25%)', 'bricks' ),
        'H' => esc_html__( 'High (~30%)', 'bricks' ),
      ],
      'placeholder' => esc_html__( 'Medium (~15%)', 'bricks' ),
    ];
    
    $this->controls['mode'] = [
      'tab' => 'content',
      'label' => esc_html__( 'QR Mode', 'bricks' ),
      'inline' => true,
      'type' => 'select',
      'group' => 'config',
      'options' => [
        'Numeric' => esc_html__( 'Numeric', 'bricks' ),
        'Alphanumeric' => esc_html__( 'Alphanumeric', 'bricks' ),
        'Byte' => esc_html__( 'Byte', 'bricks' ),
        'Kanji' => esc_html__( 'Kanji', 'bricks' ),
      ],
      'placeholder' => esc_html__( 'Byte', 'bricks' ),
      'info' => esc_html__( 'Alphanumeric only supports 0-9, A-Z, and some symbols', 'bricks' ),
    ];

    // Dots Group
    $this->controls['dotsColor'] = [
      'tab' => 'content',
      'group' => 'dots',
      'label' => esc_html__( 'Dots Color', 'bricks' ),
      'type' => 'color',
      'placeholder' => '#000000',
    ];

    $this->controls['dotsType'] = [
      'tab' => 'content',
      'group' => 'dots',
      'label' => esc_html__( 'Dots Type', 'bricks' ),
      'inline' => true,
      'type' => 'select',
      'options' => [
        'square' => esc_html__( 'Square', 'bricks' ),
        'rounded' => esc_html__( 'Rounded', 'bricks' ),
        'dots' => esc_html__( 'Dots', 'bricks' ),
        'classy' => esc_html__( 'Classy', 'bricks' ),
        'classy-rounded' => esc_html__( 'Classy Rounded', 'bricks' ),
        'extra-rounded' => esc_html__( 'Extra Rounded', 'bricks' ),
      ],
      'placeholder' => esc_html__( 'Square', 'bricks' ),
    ];

    // Corners Group
    $this->controls['cornersSquareColor'] = [
      'tab' => 'content',
      'group' => 'corners',
      'label' => esc_html__( 'Corner Square Color', 'bricks' ),
      'type' => 'color',
    ];

    $this->controls['cornersSquareType'] = [
      'tab' => 'content',
      'group' => 'corners',
      'label' => esc_html__( 'Corner Square Type', 'bricks' ),
      'type' => 'select',
      'inline' => true,
      'options' => [
        'square' => esc_html__( 'Square', 'bricks' ),
        'dot' => esc_html__( 'Dot', 'bricks' ),
        'extra-rounded' => esc_html__( 'Extra Rounded', 'bricks' ),
        'rounded' => esc_html__( 'Rounded', 'bricks' ),
        'dots' => esc_html__( 'Dots', 'bricks' ),
        'classy' => esc_html__( 'Classy', 'bricks' ),
        'classy-rounded' => esc_html__( 'Classy Rounded', 'bricks' ),
      ],
    ];

    $this->controls['cornersDotColor'] = [
      'tab' => 'content',
      'group' => 'corners',
      'label' => esc_html__( 'Corner Dot Color', 'bricks' ),
      'type' => 'color',
    ];

    $this->controls['cornersDotType'] = [
      'tab' => 'content',
      'group' => 'corners',
      'label' => esc_html__( 'Corner Dot Type', 'bricks' ),
      'type' => 'select',
      'inline' => true,
      'options' => [
        'square' => esc_html__( 'Square', 'bricks' ),
        'dot' => esc_html__( 'Dot', 'bricks' ),
        'rounded' => esc_html__( 'Rounded', 'bricks' ),
        'dots' => esc_html__( 'Dots', 'bricks' ),
        'classy' => esc_html__( 'Classy', 'bricks' ),
        'classy-rounded' => esc_html__( 'Classy Rounded', 'bricks' ),
        'extra-rounded' => esc_html__( 'Extra Rounded', 'bricks' ),
      ],
    ];

    /* image 

    $this->controls['imageSeparator'] = [
      'tab' => 'content',
      'type' => 'separator',
      'group' => 'image',
      'description' => esc_html__( 'Image to be placed in center of QR Code', 'bricks' ),
    ];
    
    $this->controls['image'] = [
      'tab' => 'content',
      'group' => 'image',
      'type' => 'image',
    ];

    $this->controls['imageMargin'] = [
      'tab' => 'content',
      'group' => 'image',
      'label' => esc_html__( 'Image margin', 'bricks' ),
      'type' => 'number',
      'placeholder' => '20',
    ];

    */

  }

  
   public function enqueue_scripts() {
   
    if (! \BricksExtras\Helpers::elementCSSAdded($this->name) ) {
			wp_enqueue_style( 'x-qr-code', BRICKSEXTRAS_URL . 'components/assets/css/qr-code.css', [], '' );
		}

    if ( bricks_is_builder_main() ) {
      return;
    }

    wp_enqueue_script( 'qr-code-styling', BRICKSEXTRAS_URL . 'components/assets/js/qr-code-styling.js', '', '1.9.2', true );

    wp_enqueue_script( 'x-qr-code', BRICKSEXTRAS_URL . 'components/assets/js/' . \BricksExtras\Helpers::maybeMinifyScripts('qr-code') . '.js', ['qr-code-styling'], \BricksExtras\Plugin::VERSION, true );

    if (!self::$script_localized) {

      wp_localize_script(
        'x-qr-code',
        'xQrCode',
        [
          'Instances' => [],
        ]
      );

      self::$script_localized = true;

    }
    
  }
  
  public function render() {

    $data = isset( $this->settings['data'] ) ? $this->render_dynamic_data( $this->settings['data'] ) : esc_url( home_url( '/' ) );

    
    
    $qrConfig = [
      'data' => $data,
    ];
    
    if (isset($this->settings['margin'])) {
      $qrConfig['margin'] = intval($this->settings['margin']);
    }
    
    if (isset($this->settings['shape'])) {
      $qrConfig['shape'] = $this->settings['shape'];
    }
    
    if (isset($this->settings['outputType'])) {
      $qrConfig['type'] = $this->settings['outputType'];
    }
    
    if (isset($this->settings['errorCorrectionLevel'])) {
      $qrConfig['qrOptions']['errorCorrectionLevel'] = $this->settings['errorCorrectionLevel'];
    }
    
    if (isset($this->settings['mode'])) {
      $qrConfig['qrOptions']['mode'] = $this->settings['mode'];
    }

    $dotsOptions = [];
    
    if (isset($this->settings['dotsType'])) {
      $dotsOptions['type'] = $this->settings['dotsType'];
    }
    
    if (isset($this->settings['shape']) && $this->settings['shape'] === 'circle') {
      $dotsOptions['roundSize'] = true;
    }

    if (isset($this->settings['dotsColor'])) {
      $dotsOptions['color'] = $this->settings['dotsColor'];
    }

    if (null !==$dotsOptions) {
      $qrConfig['dotsOptions'] = $dotsOptions;
    }

    $cornersSquareOptions = [];
    
    if (isset($this->settings['cornersSquareColor'])) {
      $cornersSquareOptions['color'] = $this->settings['cornersSquareColor'];
    }
    
    if (isset($this->settings['cornersSquareType'])) {
      $cornersSquareOptions['type'] = $this->settings['cornersSquareType'];
    }
    
    if (!empty($cornersSquareOptions)) {
      $qrConfig['cornersSquareOptions'] = $cornersSquareOptions;
    }


    if (isset($this->settings['cornersDotColor'])) {
      $cornersDotOptions['color'] = $this->settings['cornersDotColor'];
    }
    
    if (isset($this->settings['cornersDotType'])) {
      $cornersDotOptions['type'] = $this->settings['cornersDotType'];
    }
    
    if (!empty($cornersDotOptions)) {
      $qrConfig['cornersDotOptions'] = $cornersDotOptions;
    }

    $image = false;
    
    if ($image) {

      $image_url  = esc_url( bricks_render_dynamic_data( $image['url'] ) );

      $imageMargin = isset($this->settings['imageMargin']) ? intval($this->settings['imageMargin']) : 20;

      if (isset($this->settings['outputType']) && 'svg' === $this->settings['outputType']) {
        $imageMargin = ($imageMargin / 2);
      }

      $qrConfig['image'] = $image_url;
      $qrConfig['imageOptions']['margin'] = $imageMargin;
    }

    $this->set_attribute( '_root', 'data-x-qr-code', wp_json_encode( $qrConfig ) );
    $this->set_attribute( '_root', 'data-x-qr-init', false );

    // Generate and set a unique identifier for this instance
		$identifier = \BricksExtras\Helpers::set_identifier_attribute( $this );

    echo "<div {$this->render_attributes( '_root' )}></div>";

  }

}

