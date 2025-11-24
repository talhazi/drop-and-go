<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class X_Panorama_Viewer extends \Bricks\Element {

  // Element properties
  public $category     = 'extras';
	public $name         = 'xpanoramaviewer';
	public $icon         = 'ti-world';
	public $css_selector = '';
	public $scripts      = ['xPanoramaViewer'];
    public $loop_index   = 0;
    public $nestable = true;
    private static $script_localized = false;

  
  public function get_label() {
	return esc_html__( 'Panorama Viewer', 'extras' );
  }

  public function get_keywords() {
    return [ '360', 'panorama', 'virtual tour', 'equirectangular', 'immersive', 'vr' ];
  }

  public function set_control_groups() {

    $this->control_groups['viewer'] = [
      'title' => esc_html__( 'Viewer Settings', 'bricks' ),
      'tab' => 'content',
    ];

    $this->control_groups['loading'] = [
      'title' => esc_html__( 'Loading', 'bricks' ),
      'tab' => 'content',
    ];

    $this->control_groups['interaction'] = [
      'title' => esc_html__( 'User Interaction', 'bricks' ),
      'tab' => 'content',
    ];

    $this->control_groups['scrollInstructions'] = [
      'title' => esc_html__( 'Scroll Instructions', 'bricks' ),
      'tab' => 'content',
    ];

    $this->control_groups['hotspotIcon'] = [
      'title' => esc_html__( 'Hotspot Styling', 'bricks' ),
      'tab' => 'content',
    ];

    $this->control_groups['hotspotTooltip'] = [
      'title' => esc_html__( 'Tooltip Styling', 'bricks' ),
      'tab' => 'content',
    ];

    

  }

  public function set_controls() {

    $this->controls['_justifyContent']['css'][0]['selector'] = '.x-panorama-viewer_inner';
    $this->controls['_alignItems']['css'][0]['selector'] = '.x-panorama-viewer_inner';
    $this->controls['_gap']['css'][0]['selector'] = '.x-panorama-viewer_inner';
    $this->controls['_flexDirection']['css'][0]['selector'] = '.x-panorama-viewer_inner';

    // Dimensions
    $this->controls['aspectRatio'] = [
      'tab' => 'content',
      'label' => esc_html__( 'Aspect Ratio', 'bricks' ),
      'type' => 'number',
      'units' => false,
      'info' => esc_html__( 'Refresh canvas after change if image stretched', 'bricks' ),
      'placeholder' => '2/1',
      'css' => [
        [
          'selector' => '',
          'property' => 'aspect-ratio',
        ],
      ],
    ];


    // Viewer Settings Group

    $this->controls['pitch'] = [
      'tab' => 'content',
      'group' => 'viewer',
      'label' => esc_html__( 'Initial Pitch', 'bricks' ),
      'type' => 'number',
      'placeholder' => '0',
      'description' => esc_html__( 'Initial vertical angle in degrees', 'bricks' ),
    ];

    $this->controls['yaw'] = [
      'tab' => 'content',
      'group' => 'viewer',
      'label' => esc_html__( 'Initial Yaw', 'bricks' ),
      'type' => 'number',
      'placeholder' => '0',
      'description' => esc_html__( 'Initial horizontal angle in degrees', 'bricks' ),
    ];

    $this->controls['hfov'] = [
      'tab' => 'content',
      'group' => 'viewer',
      'label' => esc_html__( 'Initial Field of View', 'bricks' ),
      'type' => 'number',
      'placeholder' => '110',
      'min' => 10,
      'max' => 120,
      'description' => esc_html__( 'Initial horizontal field of view in degrees', 'bricks' ),
    ];

    $this->controls['autoRotate'] = [
      'tab' => 'content',
      'group' => 'viewer',
      'label' => esc_html__( 'Auto Rotate Speed', 'bricks' ),
      'type' => 'number',
      'placeholder' => '0',
      'description' => esc_html__( 'Rotation speed in degrees per second (0 = disabled)', 'bricks' ),
    ];

    // Controls Group
    $this->controls['showZoomCtrl'] = [
      'tab' => 'content',
      'group' => 'interaction',
      'label' => esc_html__( 'Zoom Controls', 'bricks' ),
      'type' => 'select',
      'options' => [
        'enable' => esc_html__( 'Enable', 'bricks' ),
        'disable' => esc_html__( 'Disable', 'bricks' ),
      ],
      'inline' => true,
      'placeholder' => esc_html__( 'Enable', 'bricks' ),
    ];

    $this->controls['showFullscreenCtrl'] = [
      'tab' => 'content',
      'group' => 'interaction',
      'label' => esc_html__( 'Fullscreen Control', 'bricks' ),
      'type' => 'select',
      'options' => [
        'enable' => esc_html__( 'Enable', 'bricks' ),
        'disable' => esc_html__( 'Disable', 'bricks' ),
      ],
      'inline' => true,
      'placeholder' => esc_html__( 'Enable', 'bricks' ),
    ];

    $this->controls['fullscreenFallback'] = [
      'tab' => 'content',
      'label' => esc_html__( 'Fullscreen Fallback', 'bricks' ),
      'group' => 'interaction',
      'type' => 'select',
      'options' => [
        'enable' => esc_html__( 'Enable', 'bricks' ),
        'disable' => esc_html__( 'Disable', 'bricks' ),
      ],
      'inline' => true,
      'placeholder' => esc_html__( 'Enable', 'bricks' ),
      'info' => esc_html__( 'Use full-window fallback for devices with no FullScreen API support (iOS)', 'bricks' ),
      'required' => [ 'showFullscreenCtrl', '!=', 'disable' ],
    ];

    $this->controls['mouseZoom'] = [
      'tab' => 'content',
      'group' => 'interaction',
      'label' => esc_html__( 'Mouse Wheel Zoom', 'bricks' ),
      'type' => 'select',
      'options' => [
        'enable' => esc_html__( 'Enable', 'bricks' ),
        'disable' => esc_html__( 'Disable', 'bricks' ),
      ],
      'inline' => true,
      'placeholder' => esc_html__( 'Enable', 'bricks' ),
    ];

    $this->controls['mouseZoomSensitivity'] = [
      'tab' => 'content',
      'label' => esc_html__( 'Scroll Sensitivity', 'bricks' ),
      'group' => 'interaction',
      'type' => 'number',
      'min' => 0.1,
      'max' => 5,
      'step' => 0.1,
      'placeholder' => '1',
      'info' => esc_html__( 'Lower values = less sensitive scrolling', 'bricks' ),
    ];

   

    $this->controls['friction'] = [
      'tab' => 'content',
      'label' => esc_html__( 'Movement Friction', 'bricks' ),
      'group' => 'interaction',
      'type' => 'number',
      'min' => 0.01,
      'max' => 1,
      'step' => 0.01,
      'placeholder' => '0.5',
      'info' => esc_html__( 'Higher values = stops movement faster', 'bricks' ),
    ];

    $this->controls['draggable'] = [
      'tab' => 'content',
      'group' => 'interaction',
      'label' => esc_html__( 'Draggable', 'bricks' ),
      'type' => 'select',
      'options' => [
        'enable' => esc_html__( 'Enable', 'bricks' ),
        'disable' => esc_html__( 'Disable', 'bricks' ),
      ],
      'inline' => true,
      'placeholder' => esc_html__( 'Enable', 'bricks' ),
    ];

    $this->controls['disableKeyboardCtrl'] = [
      'tab' => 'content',
      'group' => 'interaction',
      'label' => esc_html__( 'Keyboard Controls', 'bricks' ),
      'type' => 'select',
      'options' => [
        'enable' => esc_html__( 'Enable', 'bricks' ),
        'disable' => esc_html__( 'Disable', 'bricks' ),
      ],
      'inline' => true,
      'placeholder' => esc_html__( 'Enable', 'bricks' ),
    ];


     $this->controls['showScrollInstructions'] = [
      'tab' => 'content',
      'group' => 'scrollInstructions',
      'label' => esc_html__( 'Scroll Instructions', 'bricks' ),
      'type' => 'select',
      'options' => [
        'enable' => esc_html__( 'Enable', 'bricks' ),
        'disable' => esc_html__( 'Disable', 'bricks' ),
      ],
      'inline' => true,
      'placeholder' => esc_html__( 'Enable', 'bricks' ),
      'description' => esc_html__( 'Show overlay with scroll instructions', 'bricks' ),
    ];

    $this->controls['previewScrollInstructions'] = [
      'type' => 'checkbox',
      'label' => esc_html__( 'Preview scroll instructions in Builder', 'bricks' ),
      'group' => 'scrollInstructions',
      'required' => [
        'showScrollInstructions',
        '!=',
        'disable'
      ],
    ];

    $this->controls['scrollInstructionsDesktopText'] = [
      'tab' => 'content',
      'group' => 'scrollInstructions',
      'label' => esc_html__( 'Desktop Instructions Text', 'bricks' ),
      'type' => 'text',
      'placeholder' => esc_html__( 'Use {x_key} + scroll to zoom', 'bricks' ),
      'description' => esc_html__( 'Text for desktop overlay. Use {x_key} placeholder for modifier key (⌘/Ctrl)', 'bricks' ),
      'required' => [
        'showScrollInstructions',
        '!=',
        'disable'
      ],
    ];
 
    $this->controls['scrollInstructionsMobileText'] = [
      'tab' => 'content',
      'group' => 'scrollInstructions',
      'label' => esc_html__( 'Touch Device Instructions Text', 'bricks' ),
      'type' => 'text',
      'placeholder' => esc_html__( 'Use two fingers to navigate', 'bricks' ),
      'required' => [
        'showScrollInstructions',
        '!=',
        'disable'
      ],
    ];

    $this->controls['scrollInstructionsStyleSep'] = [
      'type' => 'separator',
      'label' => esc_html__( 'Overlay Styling', 'bricks' ),
      'group' => 'scrollInstructions',
      'required' => [
        'showScrollInstructions',
        '!=',
        'disable'
      ],
    ];

    $this->controls['scrollInstructionsTypography'] = [
      'tab' => 'content',
      'group' => 'scrollInstructions',
      'label' => esc_html__( 'Typography', 'bricks' ),
      'type' => 'typography',
      'css' => [
        [
          'property' => 'font',
          'selector' => '.pnlm-scroll-instructions',
        ],
      ],
      'required' => [
        'showScrollInstructions',
        '!=',
        'disable'
      ],
    ];

    $this->controls['scrollInstructionsBackground'] = [
      'tab' => 'content',
      'group' => 'scrollInstructions',
      'label' => esc_html__( 'Background', 'bricks' ),
      'type' => 'background',
      'css' => [
        [
          'property' => 'background',
          'selector' => '.pnlm-scroll-instructions',
        ],
      ],
      'required' => [
        'showScrollInstructions',
        '!=',
        'disable'
      ],
    ];

    $this->controls['scrollInstructionsPadding'] = [
      'tab' => 'content',
      'group' => 'scrollInstructions',
      'label' => esc_html__( 'Padding', 'bricks' ),
      'type' => 'spacing',
      'css' => [ 
        [
          'property' => 'padding',
          'selector' => '.pnlm-scroll-instructions',
        ],
      ],
      'required' => [
        'showScrollInstructions',
        '!=',
        'disable'
      ],
    ];

    $this->controls['scrollInstructionsBorder'] = [
      'tab' => 'content',
      'group' => 'scrollInstructions',
      'label' => esc_html__( 'Border', 'bricks' ),
      'type' => 'border',
      'css' => [
        [
          'property' => 'border',
          'selector' => '.pnlm-scroll-instructions',
        ],
      ],
      'required' => [
        'showScrollInstructions',
        '!=',
        'disable'
      ],
    ];

    $this->controls['scrollInstructionsBoxShadow'] = [
      'tab' => 'content',
      'group' => 'scrollInstructions',
      'label' => esc_html__( 'Box Shadow', 'bricks' ),
      'type' => 'box-shadow',
      'css' => [
        [
          'property' => 'box-shadow',
          'selector' => '.pnlm-scroll-instructions',
        ],
      ],
      'required' => [
        'showScrollInstructions',
        '!=',
        'disable'
      ],
    ];

    $this->controls['scrollInstructionsWidth'] = [
      'tab' => 'content',
      'group' => 'scrollInstructions',
      'label' => esc_html__( 'Width', 'bricks' ),
      'type' => 'number',
      'units' => true,
      'css' => [
        [
          'property' => 'width',
          'selector' => '.pnlm-scroll-instructions',
        ],
      ],
      'placeholder' => '100%',
      'required' => [
        'showScrollInstructions',
        '!=',
        'disable'
      ],
    ];

    $this->controls['scrollInstructionsHeight'] = [
      'tab' => 'content',
      'group' => 'scrollInstructions',
      'label' => esc_html__( 'Height', 'bricks' ),
      'type' => 'number',
      'units' => true,
      'css' => [
        [
          'property' => 'height',
          'selector' => '.pnlm-scroll-instructions',
        ],
      ],
      'placeholder' => '100%',
      'required' => [
        'showScrollInstructions',
        '!=',
        'disable'
      ],
    ];

    $this->controls['scrollInstructionsMaxWidth'] = [
      'tab' => 'content',
      'group' => 'scrollInstructions',
      'label' => esc_html__( 'Max Width', 'bricks' ),
      'type' => 'number',
      'units' => true,
      'css' => [
        [
          'property' => 'max-width',
          'selector' => '.pnlm-scroll-instructions',
        ],
      ],
      'required' => [
        'showScrollInstructions',
        '!=',
        'disable'
      ],
    ];

    $this->controls['loadingStrategySep'] = [
      'type' => 'separator',
      'label' => esc_html__( 'Loading Strategy', 'bricks' ),
      'group' => 'loading',
    ];

    $this->controls['loadingStrategy'] = [
      'tab' => 'content',
      'label' => esc_html__( 'Loading Strategy', 'bricks' ),
      'type' => 'select',
      'group' => 'loading',
      'options' => [
        'eager' => esc_html__( 'Eager', 'bricks' ),
        'lazy' => esc_html__( 'Lazy', 'bricks' ),
        'click' => esc_html__( 'Click to load', 'bricks' ),
        'interactions' => esc_html__( 'Via Interactions', 'bricks' ),
      ],
      'inline' => true,
      'placeholder' => esc_html__( 'Lazy', 'bricks' ),
    ];

    $this->controls['hideNestedContentInfo'] = [
      'tab' => 'content',
      'group' => 'loading',
      'content' => esc_html__( 'Nest content inside viewer to inform the user to click to load', 'bricks' ),
      'type' => 'info',
      'required' => ['loadingStrategy','=', 'click'],
    ];

    $this->controls['hideNestedContent'] = [
      'tab' => 'content',
      'group' => 'loading',
      'label' => esc_html__( 'Hide Nested Content in Builder', 'bricks' ),
      'type' => 'checkbox',
      'inline' => true,
      'required' => ['loadingStrategy','=', 'click'],
    ];

    $this->controls['hideNestedContentOverlayColor'] = [
      'tab' => 'content',
      'group' => 'loading',
      'label' => esc_html__( 'Nested Content Overlay Color', 'bricks' ),
      'type' => 'color',
      'inline' => true,
      'css' => [
        [
          'property' => 'background-color',
          'selector' => '.x-panorama-viewer_inner',
        ],
      ],
      'required' => ['loadingStrategy','=', 'click'],
    ];

    $this->controls['placeholderImage'] = [
      'group' => 'loading',
      'tab' => 'content',
      'label' => esc_html__( 'Placeholder Image', 'bricks' ),
      'type' => 'image',
      'info' => esc_html__( 'Smaller image displayed before real panorama loads', 'bricks' ),
    ];

    $this->controls['spinnerStyleSep'] = [
      'type' => 'separator',
      'label' => esc_html__( 'Loading Spinner', 'bricks' ),
      'group' => 'loading',
    ];

    $this->controls['previewSpinner'] = [
      'type' => 'checkbox',
      'label' => esc_html__( 'Preview spinner in Builder', 'bricks' ),
      'group' => 'loading',
    ];

    $this->controls['spinnerColor'] = [
      'tab' => 'content',
      'group' => 'loading',
      'type' => 'color',
      'label' => esc_html__( 'Spinner color', 'bricks' ),
      'css' => [
        [
          'property' => 'color',
          'selector' => '.x-panorama-viewer::before',
        ],
      ],
    ];

    $this->controls['spinnerTrackColor'] = [
      'tab' => 'content',
      'group' => 'loading',
      'type' => 'color',
      'label' => esc_html__( 'Track color', 'bricks' ),
      'css' => [
        [
          'property' => '--x-panorama-spinner-track-color',
          'selector' => '',
        ],
      ],
    ];

    $this->controls['spinnerStrokeWidth'] = [
      'tab' => 'content',
      'group' => 'loading',
      'type' => 'number',
      'placeholder' => '2px',
      'small' => true,
      'units' => true,
      'label' => esc_html__( 'Spinner width', 'bricks' ),
      'css' => [
        [
          'property' => '--x-panorama-spinner-stroke-width',
          'selector' => '',
        ],
      ],
    ];

    $this->controls['spinnerSize'] = [ 
      'tab' => 'content',
      'group' => 'loading',
      'type' => 'number',
      'units' => true,
      'small' => true,
      'placeholder' => '3em',
      'label' => esc_html__( 'Spinner size', 'bricks' ),
      'css' => [
        [
          'property' => '--x-panorama-spinner-size',
          'selector' => '',
        ],
      ],
    ];

    $this->controls['hotSpotDebugSep'] = [
      'type' => 'separator',
    ];

    $this->controls['hotSpotDebug'] = [
      'tab' => 'content',
     //  'group' => 'controls',
      'label' => esc_html__( 'Hot Spot Debug Mode', 'bricks' ),
      'type' => 'checkbox',
      'inline' => true,
      'description' => esc_html__( 'Show pitch/yaw values in builder', 'bricks' ),
    ];

    $hotspotSelector = '.pnlm-hotspot-icon';
    $hotspotSelectorInner = '.pnlm-hotspot-icon-inner';

    // HOTSPOT ICON STYLING
    $this->controls['hotspotIconBackground'] = [
      'tab' => 'style',
      'group' => 'hotspotIcon',
      'label' => esc_html__( 'Background', 'bricks' ),
      'type' => 'background',
      'css' => [
        [
          'property' => 'background',
          'selector' => $hotspotSelectorInner,
        ],
      ],
    ];

    $this->controls['hotspotIconBorder'] = [
      'tab' => 'style',
      'group' => 'hotspotIcon',
      'label' => esc_html__( 'Border', 'bricks' ),
      'type' => 'border',
      'css' => [
        [
          'property' => 'border',
          'selector' => $hotspotSelectorInner,
        ],
      ],
    ];

    $this->controls['hotspotIconBoxShadow'] = [
      'tab' => 'style',
      'group' => 'hotspotIcon',
      'label' => esc_html__( 'Box Shadow', 'bricks' ),
      'type' => 'box-shadow',
      'css' => [
        [
          'property' => 'box-shadow',
          'selector' => '.pnlm-hotspot-icon',
        ],
      ],
    ];

    $this->controls['hotspotIconPadding'] = [
      'tab' => 'style',
      'group' => 'hotspotIcon',
      'label' => esc_html__( 'Padding', 'bricks' ),
      'type' => 'spacing',
      'css' => [
        [
          'property' => 'padding',
          'selector' => $hotspotSelectorInner,
        ],
      ],
    ];

    $this->controls['hotspotIconWidth'] = [
      'tab' => 'style',
      'group' => 'hotspotIcon',
      'label' => esc_html__( 'Hotspot Width', 'bricks' ),
      'type' => 'number',
      'units' => true,
      'css' => [
        [
          'property' => 'width',
          'selector' => $hotspotSelectorInner,
        ],
      ],
    ];

    $this->controls['hotspotIconHeight'] = [
      'tab' => 'style',
      'group' => 'hotspotIcon',
      'label' => esc_html__( 'Hotspot Height', 'bricks' ),
      'type' => 'number',
      'units' => true,
      'css' => [
        [
          'property' => 'height',
          'selector' => $hotspotSelectorInner,
        ],
      ],
    ];

    $this->controls['hotspotIconTypography'] = [
      'tab' => 'style',
      'group' => 'hotspotIcon',
      'label' => esc_html__( 'Typography', 'bricks' ),
      'type' => 'typography',
      'css' => [
        [
          'property' => 'font',
          'selector' => $hotspotSelectorInner,
        ],
      ],
    ];

    $this->controls['hotspotPulseSep'] = [
      'type' => 'separator',
      'label' => esc_html__( 'Pulse Effect', 'bricks' ),
      'group' => 'hotspotIcon',
    ];

    // PULSE EFFECT CONTROLS
    $this->controls['hotspotPulseColor'] = [
      'tab' => 'style',
      'group' => 'hotspotIcon',
      'label' => esc_html__( 'Pulse Color', 'bricks' ),
      'type' => 'color',
      'css' => [
        [
          'property' => '--x-panorama-pulse-color',
          'selector' => '',
        ],
      ],
    ];

    $this->controls['hotspotPulseDuration'] = [
      'tab' => 'style',
      'group' => 'hotspotIcon',
      'label' => esc_html__( 'Pulse Duration', 'bricks' ),
      'type' => 'number',
      'units' => true,
      'placeholder' => '2s',
      'css' => [
        [
          'property' => '--x-panorama-pulse-duration',
          'selector' => '',
        ],
      ],
    ];

    $this->controls['hotspotPulseSize'] = [
      'tab' => 'style',
      'group' => 'hotspotIcon',
      'label' => esc_html__( 'Pulse Size', 'bricks' ),
      'type' => 'number',
      'units' => false,
      'placeholder' => '2',
      'css' => [
        [
          'property' => '--x-panorama-pulse-size',
          'selector' => '',
        ],
      ],
    ];

    $this->controls['hotspotPulseDisable'] = [
      'tab' => 'style',
      'group' => 'hotspotIcon',
      'label' => esc_html__( 'Disable Pulse on Hover', 'bricks' ),
      'type' => 'checkbox',
    ];

    // HOTSPOT TOOLTIP STYLING todo

     $this->controls['hotspotTooltipWidth'] = [
      'tab' => 'style',
      'group' => 'hotspotTooltip',
      'label' => esc_html__( 'Width', 'bricks' ),
      'type' => 'number',
      'placeholder' => '100px',
      'units' => true,
      'css' => [
        [
          'property' => 'width',
          'selector' => '.pnlm-hotspot-tooltip',
        ],
      ],
    ];

    $this->controls['hotspotTooltipMaxWidth'] = [
      'tab' => 'style',
      'group' => 'hotspotTooltip',
      'label' => esc_html__( 'Max Width', 'bricks' ),
      'type' => 'number',
      'units' => true,
      'css' => [
        [
          'property' => 'max-width',
          'selector' => '.pnlm-hotspot-tooltip',
        ],
      ],
    ];

    $this->controls['hotspotTooltipBackground'] = [
      'tab' => 'style',
      'group' => 'hotspotTooltip',
      'label' => esc_html__( 'Background', 'bricks' ),
      'type' => 'color',
      'css' => [
        [
          'property' => 'background-color',
          'selector' => '.pnlm-hotspot-tooltip',
        ],
        [
          'property' => 'border-top-color',
          'selector' => '.pnlm-hotspot-tooltip:after',
        ],
      ],
    ];

    $this->controls['hotspotTooltipBackground'] = [
      'tab' => 'style',
      'group' => 'hotspotTooltip',
      'label' => esc_html__( 'Background', 'bricks' ),
      'type' => 'color',
      'css' => [
        [
          'property' => 'background-color',
          'selector' => '.pnlm-hotspot-tooltip',
        ],
        [
          'property' => 'border-top-color',
          'selector' => '.pnlm-hotspot-tooltip:after',
        ],
      ],
    ];

    $this->controls['hotspotTooltipTypography'] = [
      'tab' => 'style',
      'group' => 'hotspotTooltip',
      'label' => esc_html__( 'Typography', 'bricks' ),
      'type' => 'typography',
      'css' => [
        [
          'property' => 'font',
          'selector' => '.pnlm-hotspot-tooltip',
        ],
      ],
    ];

    $this->controls['hotspotTooltipPadding'] = [
      'tab' => 'style',
      'group' => 'hotspotTooltip',
      'label' => esc_html__( 'Padding', 'bricks' ),
      'type' => 'spacing',
      'css' => [
        [
          'property' => 'padding',
          'selector' => '.pnlm-hotspot-tooltip',
        ],
      ],
    ];

    $this->controls['hotspotTooltipBorder'] = [
      'tab' => 'style',
      'group' => 'hotspotTooltip',
      'label' => esc_html__( 'Border', 'bricks' ),
      'type' => 'border',
      'css' => [
        [
          'property' => 'border',
          'selector' => '.pnlm-hotspot-tooltip',
        ],
      ],
    ];

    $this->controls['hotspotTooltipBoxShadow'] = [
      'tab' => 'style',
      'group' => 'hotspotTooltip',
      'label' => esc_html__( 'Box Shadow', 'bricks' ),
      'type' => 'box-shadow',
      'css' => [
        [
          'property' => 'box-shadow',
          'selector' => '.pnlm-hotspot-tooltip',
        ],
      ],
    ];

    

  }

  // Methods: Frontend-specific
  public function enqueue_scripts() {

    if ( bricks_is_builder_main() ) {
      return;
    }

    // Enqueue our custom script

    wp_enqueue_script( 'x-pannellum', BRICKSEXTRAS_URL . 'components/assets/js/pannellum.min.js', '', '2.5.6', true );

    wp_enqueue_script( 'x-panoramaviewer', BRICKSEXTRAS_URL . 'components/assets/js/' . \BricksExtras\Helpers::maybeMinifyScripts('panoramaviewer') . '.js', ['x-pannellum'], \BricksExtras\Plugin::VERSION, true );

    if (!self::$script_localized) {

      wp_localize_script(
        'x-panoramaviewer',
        'xPanoramaViewer',
        [
          'Instances' => [],
        ]
      );
    
      self::$script_localized = true;
    
    }

    if (! \BricksExtras\Helpers::elementCSSAdded($this->name) ) {
      wp_enqueue_style( 'x-panoramaviewer', BRICKSEXTRAS_URL . 'components/assets/css/panoramaviewer.css', [], \BricksExtras\Plugin::VERSION ); 
    }
  }


  public function get_nestable_item() {

    return [
        'name'     => 'xpanoramascene',
        'label'    => esc_html__( 'Panorama Scene', 'bricks' ),
        'settings' => [
           'panoramaImage' => [
                  'url'  => 'https://images.unsplash.com/photo-1707406045095-c333306c92f5?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=4120',
            ],
        ],
    ];
  }

  public function get_nestable_children() {
    $children = [];

    for ( $i = 0; $i < 1; $i++ ) {
        $item = $this->get_nestable_item();

        // Replace {item_index} with $index
        $item       = json_encode( $item );
        $item       = str_replace( '{item_index}', $i + 1, $item );
        $item       = json_decode( $item, true );
        $children[] = $item;
    }

    return $children;
  }

  
  public function render() {

    $settings = $this->settings;

    // Build configuration
    $config = [
      'type' => 'equirectangular',
      'crossOrigin' => 'anonymous',
    ];

    // Add scene ID if set (for tours)
    if ( ! empty( $settings['sceneId'] ) ) {
      $config['sceneId'] = sanitize_key( bricks_render_dynamic_data( $settings['sceneId'] ) );
    }

    // Optional settings
    if ( isset( $settings['autoRotate'] ) && $settings['autoRotate'] != '' ) {
      $config['autoRotate'] = floatval( $settings['autoRotate'] );
    }

    if ( isset( $settings['yaw'] ) && $settings['yaw'] != '' ) {
      $config['yaw'] = floatval( $settings['yaw'] );
    }

    if ( isset( $settings['pitch'] ) && $settings['pitch'] != '' ) {
      $config['pitch'] = floatval( $settings['pitch'] );
    }

    $config['hfov'] = isset( $settings['hfov'] ) ? floatval( $settings['hfov'] ) : 110;

    if ( isset( $settings['showZoomCtrl'] ) ) {
      $config['showZoomCtrl'] = $settings['showZoomCtrl'] === 'enable' ? true : false; 
    }

    if ( isset( $settings['showFullscreenCtrl'] ) ) {
      $config['showFullscreenCtrl'] = $settings['showFullscreenCtrl'] === 'enable' ? true : false;
    }

    if ( isset( $settings['fullscreenFallback'] ) ) {
      $config['fullscreenFallback'] = $settings['fullscreenFallback'] === 'enable' ? true : false;
    }

    if ( isset( $settings['mouseZoom'] ) ) {
      $config['mouseZoom'] = $settings['mouseZoom'] === 'enable' ? true : false;
    }

    if ( isset( $settings['mouseZoomSensitivity'] ) && $settings['mouseZoomSensitivity'] !== '' ) {
      $config['mouseZoomSensitivity'] = floatval( $settings['mouseZoomSensitivity'] );
    }

    // Default to enabled unless explicitly disabled
    $config['showScrollInstructions'] = !isset( $settings['showScrollInstructions'] ) || $settings['showScrollInstructions'] !== 'disable'; 

    if ( isset( $settings['scrollInstructionsDesktopText'] ) && !empty( $settings['scrollInstructionsDesktopText'] ) ) {
      $config['scrollInstructionsDesktopText'] = esc_html__( wp_kses_post($settings['scrollInstructionsDesktopText']), 'bricks' );
    }
 
    if ( isset( $settings['scrollInstructionsMobileText'] ) && !empty( $settings['scrollInstructionsMobileText'] ) ) {
      $config['scrollInstructionsMobileText'] = esc_html__( wp_kses_post($settings['scrollInstructionsMobileText']), 'bricks' );
    }

    if ( isset( $settings['friction'] ) && $settings['friction'] !== '' ) {
      $config['friction'] = floatval( $settings['friction'] );
    }

    if ( isset( $settings['draggable'] ) ) {
      $config['draggable'] = $settings['draggable'] === 'enable' ? true : false;
    }

    if ( isset( $settings['disableKeyboardCtrl'] ) ) {
      $config['disableKeyboardCtrl'] = $settings['disableKeyboardCtrl'] === 'enable' ? true : false;
    }

    if (\BricksExtras\Helpers::maybePreview()) {
      $config['hotSpotDebug'] = isset( $settings['hotSpotDebug'] );
    } 

    // Loading strategy
    $loading_strategy = isset( $settings['loadingStrategy'] ) ? esc_attr($settings['loadingStrategy']) : 'lazy';
    $config['loadingStrategy'] = $loading_strategy;

    // Placeholder image for click to load
    if ( ! empty( $settings['placeholderImage'] ) ) {
      $placeholder_image = \BricksExtras\Helpers::get_normalized_image_settings( $settings, 'placeholderImage' );
      if ( ! empty( $placeholder_image['url'] ) ) {
        $config['placeholderImage'] = esc_url( bricks_render_dynamic_data( $placeholder_image['url'] ) );
      }
    }

    // Generate and set a unique identifier for this instance
		$indentifier = \BricksExtras\Helpers::set_identifier_attribute( $this ); 

    // Set attributes
    $this->set_attribute( '_root', 'data-panorama-config', htmlspecialchars( wp_json_encode( $config, JSON_HEX_APOS | JSON_HEX_QUOT ), ENT_QUOTES, 'UTF-8' ) );
    $this->set_attribute( '_root', 'data-loading-strategy', $loading_strategy );

    if ( \BricksExtras\Helpers::maybePreview() && isset($settings['hideNestedContent'] ) ) {
      $this->set_attribute( '_root', 'data-hide-nested-content', 'true' );
    } 

    if ( \BricksExtras\Helpers::maybePreview() ) {
      $this->set_attribute( '_root', 'data-preview-scroll-instructions', isset($settings['previewScrollInstructions'] ) ? 'true' : 'false' );
      $this->set_attribute( '_root', 'data-preview-spinner', isset($settings['previewSpinner'] ) ? 'true' : 'false' );
    }
     
    
    // Add data attribute for pulse disable setting
    if ( ! empty( $settings['hotspotPulseDisable'] ) ) {
      $this->set_attribute( '_root', 'data-pulse-disable-hover', 'true' );
    }

    echo "<div {$this->render_attributes( '_root' )}>";
    
    echo "<div class='x-panorama-viewer'></div>";

    echo "<div class='x-panorama-viewer_inner'>";
    

    if ( method_exists('\Bricks\Frontend','render_children') ) {
        echo \Bricks\Frontend::render_children( $this );
    }

    echo "</div>";
    
    
    echo "</div>";
    
  }

}
