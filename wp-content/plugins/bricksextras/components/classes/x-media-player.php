<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once BRICKSEXTRAS_PATH . 'components/traits/media-player-trait.php';

use BricksExtras\Components\Traits\Media_Player_Trait;

class X_Media_Player extends \Bricks\Element {

  use Media_Player_Trait;

  // Element properties
  public $category     = 'extras';
	public $name         = 'xmediaplayer';
	public $icon         = 'ti-video-camera';
	public $scripts      = [
    'xMediaPlayerTooltips',
    'xDoMediaPlayer'
  ];
  public $loop_index  = 0;
  public $nestable    = true;
  public $controlFields = [];

  
  public function get_label() {
	  return esc_html__( 'Media Player', 'extras' );
  }

  public function get_keywords() {
    return [ 'video', 'audio', 'media', 'player', 'controls' ];
    }
  

  public function set_control_groups() {
    
    // Main UI Type selection - should be at the top
    $this->control_groups['controls'] = [
      'title' => esc_html__( 'Player UI Type', 'bricks' ),
    ];
    
    // Customize UI options - placed immediately after UI Type selection
    $this->control_groups['controlsSmall'] = [
      'title' => esc_html__( 'Customize UI', 'bricks' ),
      'clearable' => false,
      'required' => [
        ['layoutType','!=',['custom','type_two']],
      ]
    ];
    
    $this->control_groups['controlsLarge'] = [
      'title' => esc_html__( 'Customize UI', 'bricks' ),
      'clearable' => false,
      'required' => [
        ['layoutType','=',['type_two']],
      ]
    ];

    $this->control_groups['poster'] = [
      'title' => esc_html__( 'Poster Image', 'bricks' ),
      'required' => ['playlistMode', '!=', true]
    ];
    
   

    $this->control_groups['styleGeneral'] = [
      'title' => esc_html__( 'General Styling', 'bricks' ),
    ];

    $this->control_groups['styleControls'] = [
      'title' => esc_html__( 'Individual control styling', 'bricks' ),
    ];

    $this->control_groups['behaviour'] = [
        'title' => esc_html__( 'Config / Behaviour', 'bricks' ),
        ['layoutType','!=','custom']
    ];
    
    // Tooltips group
    $this->control_groups['tooltips'] = [
      'title' => esc_html__( 'Tooltips', 'bricks' ),
    ];

     // Notice Overlay - related to UI but less frequently used
     $this->control_groups['noticeOverlay'] = [
      'title' => esc_html__( 'Notice Overlay', 'bricks' ),
      'required' => [
        ['layoutType','!=','custom']
      ]
    ];
    
    // Text tracks group
    $this->control_groups['textTracks'] = [
      'title' => esc_html__( 'Text Tracks', 'bricks' ),
      'required' => ['playlistMode', '!=', true]
    ];
    
    // Chapters group
    $this->control_groups['chapters'] = [
      'title' => esc_html__( 'Chapters', 'bricks' ),
      'required' => ['playlistMode', '!=', true]
    ];

    // Keyboard Shortcuts group
    $this->control_groups['keyboardShortcuts'] = [
      'title' => esc_html__( 'Keyboard Shortcuts', 'bricks' ),
    ];
    
    // Playlists group
    $this->control_groups['playLists'] = [
      'title' => esc_html__( 'Playlists', 'bricks' ),
    ];
  }

  public function set_controls() {
    
    $this->set_common_controls(); 
    
    // Set video-specific background and border selectors
    if (isset($this->controls['_background'])) {
      $this->controls['_background']['css'][1]['selector'] = '&[data-x-view-type=video]';
    }
    if (isset($this->controls['_border'])) {
      $this->controls['_border']['css'][1]['selector'] = '&[data-x-view-type=video]';
    }

    $this->controls['_background']['css'][0]['selector'] = '&[data-x-view-type=video] media-provider';
    $this->controls['_border']['css'][0]['selector'] = '&[data-x-view-type=video] media-provider';

    $this->controls['_justifyContent']['css'][0]['selector'] = '.xmp-controls';
    $this->controls['_alignItems']['css'][0]['selector'] = '.xmp-controls';
    $this->controls['_gap']['css'][0]['selector'] = '.xmp-controls';
    $this->controls['_flexDirection']['css'][0]['selector'] = '.xmp-controls';

    /* shared media player controls */
    $shared_vars = $this->get_media_player_shared_variables();
    $activeControls = $shared_vars['activeControls'];
    $inactiveControls = $shared_vars['inactiveControls'];
    $placementOptions = $shared_vars['placementOptions'];
    $optionControls = $shared_vars['optionControls'];
    $controlFields = $shared_vars['controlFields'];
    
    // Make sure $controlFields is defined as a class property so it's available throughout the class
    $this->controlFields = $controlFields;

    $this->controls = array_merge($this->controls, $this->get_media_player_controls('video'));

    
    $smallRequirement = [
      ['layoutType','!=','custom'],
      ['layoutType','!=','type_two']
    ];

    $largeRequirement = [
        ['layoutType','!=','custom'],
        ['layoutType','!=','type_one']
    ];

    $this->controls['aspectRatio'] = [
      'label'    => esc_html__( 'Aspect-ratio', 'bricks' ),
      'type'        => 'text',
      'placeholder' => esc_html__( '16/9', 'bricks' ),
      'inline'      => true,
      'info' => esc_html__( 'Format w/h', 'bricks' ),
      'hasDynamicData' => false,
      'small' => true,
      'css'    => [
        [
          'property' => 'aspect-ratio',
          'selector' => '&[data-x-view-type=video]',
        ],
        [
          'property' => 'aspect-ratio',
          'selector' => '&[data-x-view-type=video] video',
        ],
      ],
    ];




    $this->controls['controlsTopSep'] = [
      'tab' => 'content',
      'label'    => esc_html__( 'Top UI controls', 'bricks' ),
      'description' => esc_html__( 'Add/Remove/Re-order the internal controls', 'bricks' ),
      'type' => 'separator',
      'group' => 'controlsLarge',
      'required' => $largeRequirement,
    ];

    $this->controls['controlsTop'] = [
        'group' => 'controlsLarge',
        //'label'    => esc_html__( 'Controls', 'bricks' ),
        'placeholder'   => esc_html__( 'Control', 'bricks' ),
        'titleProperty' => 'control',
        'type'  => 'repeater',
        'required' => $largeRequirement,
        'fields'        => $this->controlFields,
        'default'       => [
            [ 'control' => 'title' ],
            [ 'control' => 'spacer' ],
        ],
    ];

    $this->controls['controlsTopVisibility'] = [
      'tab'    => 'content',
      'type'   => 'select',
      'options' => [
        'loaded' => esc_html__( 'Video loaded', 'bricks' ),
        'play' => esc_html__( 'Video played', 'bricks' ),
      ],
      'placeholder' => esc_html__( '', 'bricks' ),
      'group' => 'controlsLarge',
      'label'  => esc_html__( 'Hide until..', 'extras' ),
      'inline' => true,
      'required' => $largeRequirement,
    ];

    $this->controls['controlGapTop'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'group' => 'controlsLarge',
      'label'  => esc_html__( 'Gap', 'extras' ),
      'css'    => [
        [
          'property' => '--media-button-gap',
          'selector' => '.xmp-controls_group_top',
        ],
      ],
      'required' => $largeRequirement,
    ];


    $this->controls['controlsCenterSep'] = [
      'tab' => 'content',
      'label'    => esc_html__( 'Center UI controls', 'bricks' ),
      'description' => esc_html__( 'Add/Remove/Re-order the internal controls', 'bricks' ),
      'type' => 'separator',
      'group' => 'controlsLarge',
      'required' => $largeRequirement,
    ];

    

    $this->controls['controlsCenter'] = [
        'group' => 'controlsLarge',
        //'label'    => esc_html__( 'Controls', 'bricks' ),
        'placeholder'   => esc_html__( 'Control', 'bricks' ),
        'titleProperty' => 'control',
        'type'  => 'repeater',
        'required' => $largeRequirement,
        'fields'        => $this->controlFields,
        'default'       => [],
    ];

    $this->controls['controlsCenterVisibility'] = [
      'tab'    => 'content',
      'type'   => 'select',
      'options' => [
        'loaded' => esc_html__( 'Video loaded', 'bricks' ),
        'play' => esc_html__( 'Video played', 'bricks' ),
      ],
      'placeholder' => esc_html__( '', 'bricks' ),
      'group' => 'controlsLarge',
      'label'  => esc_html__( 'Hide until..', 'extras' ),
      'inline' => true,
      'required' => $largeRequirement,
    ];

    $this->controls['controlGapCenter'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'group' => 'controlsLarge',
      'label'  => esc_html__( 'Gap', 'extras' ),
      'css'    => [
        [
          'property' => '--media-button-gap',
          'selector' => '.xmp-controls_group_center',
        ],
      ],
      'required' => $largeRequirement,
    ];


    $this->controls['controlsSep'] = [
      'tab' => 'content',
      'label'    => esc_html__( 'Bottom UI Controls', 'bricks' ),
      'description' => esc_html__( 'Add/Remove/Re-order the internal controls', 'bricks' ),
      'type' => 'separator',
      'group' => 'controlsLarge',
      'required' => $largeRequirement,
    ];


    $this->controls['controls'] = [
        'group' => 'controlsLarge',
        //'label'    => esc_html__( 'Controls', 'bricks' ),
        'placeholder'   => esc_html__( 'Control', 'bricks' ),
        'titleProperty' => 'control',
        'type'  => 'repeater',
        'required' => $largeRequirement,
        'fields'        => $this->controlFields,
        'default'       => [
            [ 'control' => 'play' ],
            [ 'control' => 'mute' ],
            [ 'control' => 'time' ],
            [ 'control' => 'spacer' ],
            [ 'control' => 'captions' ],
            [ 'control' => 'chapters' ],
            [ 'control' => 'fullscreen' ],
            [ 'control' => 'settings' ],
        ],
    ];

    $this->controls['controlsBottomVisibility'] = [
      'tab'    => 'content',
      'type'   => 'select',
        'options' => [
          'loaded' => esc_html__( 'Video loaded', 'bricks' ),
          'play' => esc_html__( 'Video played', 'bricks' ),
        ],
        'placeholder' => esc_html__( '', 'bricks' ),
      'group' => 'controlsLarge',
      'label'  => esc_html__( 'Hide until..', 'extras' ),
      'inline' => true,
      'required' => $largeRequirement,
    ];

    $this->controls['controlGapBottom'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'group' => 'controlsLarge',
      'label'  => esc_html__( 'Gap', 'extras' ),
      'css'    => [
        [
          'property' => '--media-button-gap',
          'selector' => '.xmp-controls_group_bottom',
        ],
      ],
      'required' => $largeRequirement,
    ];


    $this->controls['maybeTimeSlider'] = [
      'group' => 'controlsLarge',
      'label'    => esc_html__( 'Full width time slider', 'bricks' ),
      'type'        => 'select',
      'options'     => [
        'enable' => esc_html__( 'Enable', 'bricks' ),
        'disable'  => esc_html__( 'Disable', 'bricks' ),
      ],
      'placeholder' => esc_html__( 'Enable', 'bricks' ),
      'inline'      => true,
      'required' => $largeRequirement,
      'info' => esc_html__( 'Above bottom UI controls', 'bricks' ),
    ];



      /* small ui */  

      $this->controls['smallControlsSep'] = [
        'group' => 'controls',
        'label'    => esc_html__( 'UI type', 'bricks' ),
        'description' => esc_html__( 'Choose the starting layout for the player', 'bricks' ),
        'type'        => 'separator',
      ];

      $this->controls['layoutType'] = [
        'group' => 'controls',
        'label'    => esc_html__( 'UI type', 'bricks' ),
        'inline' => true,
        'type'        => 'select',
        'options'     => [
          'type_one' => esc_html__( 'Type 1', 'bricks' ),
          'type_two' => esc_html__( 'Type 2', 'bricks' ),
          'custom' => esc_html__( 'Custom (advanced)', 'bricks' ),
        ],
        'placeholder' => esc_html__( 'Type 1', 'bricks' ),
      ];

      $this->controls['controlsAdvancedSep'] = [
        'tab' => 'content',
        'label'    => esc_html__( 'Custom UI', 'bricks' ),
        'description' => esc_html__( 'All default UI is removed, add a block element inside the media player for the layout. Add "media controls" element inside block to build custom UI. The style settings below can still be used to style the controls globally for the player', 'bricks' ),
        'type' => 'separator',
        'group' => 'controls',
        'required' => ['layoutType','=','custom']
    ];

    /*
      $this->controls['smallControlsBreakpoint'] = [
        'group' => 'controls',
        'label'    => esc_html__( 'Width of player (px)', 'bricks' ),
        'type'        => 'number',
        'placeholder' => '478',
        'inline' => true,
        'required' => ['layoutType','=',['container']],
      ];

      $this->controls['smallControlsMediaBreakpoint'] = [
        'group' => 'controls',
        'label'    => esc_html__( 'Use small UI from current breakpoint', 'bricks' ),
        'type'        => 'select',
        'placeholder' => 'false',
        'options'     => [
          'true' => esc_html__( 'True', 'bricks' ),
          'false'  => esc_html__( 'False', 'bricks' ),
        ],
        'inline' => true,
        'required' => ['layoutType','=',['media']],
        'css'    => [
          [
            'selector' => '&[data-x-media-player*=media] media-layout',
            'property' => '--x-media-layout',
            'value'    => 'var(--x-media-layout-small)',
            'required' => 'true',
          ],
          [
            'selector' => '&[data-x-media-player*=media] media-layout',
            'property' => '--x-media-layout',
            'value'    => 'var(--x-media-layout-large)',
            'required' => 'false',
          ],
          
        ],
      ];

      */


     /* controls small */


     $this->controls['smallControlsTopSep'] = [
      'tab' => 'content',
      'label'    => esc_html__( 'Top UI Controls', 'bricks' ),
      'description' => esc_html__( 'Add/Remove/Re-order the internal controls', 'bricks' ),
      'type' => 'separator',
      'group' => 'controlsSmall',
      'required' => [
        ['layoutType','!=','custom'],
        ['layoutType','!=','type_two']
      ]
    ];

    $this->controls['smallControlsTop'] = [
      'group' => 'controlsSmall',
      'label'    => esc_html__( 'Controls', 'bricks' ),
      'placeholder'   => esc_html__( 'Controls', 'bricks' ),
      'titleProperty' => 'control',
      'type'  => 'repeater',
      'required' => [
        ['layoutType','!=','custom'],
        ['layoutType','!=','type_two']
      ],
      'fields'        => $this->controlFields,
      'default'       => [
        [ 'control' => 'mute' ],
        [ 'control' => 'spacer' ],
        [ 'control' => 'captions' ],
        [ 'control' => 'chapters' ],
        [ 'control' => 'fullscreen' ],
        [ 'control' => 'settings' ],
      ],
    ];

    $this->controls['smallcontrolsTopVisibility'] = [
      'tab'    => 'content',
      'type'   => 'select',
        'options' => [
          'loaded' => esc_html__( 'Video loaded', 'bricks' ),
          'play' => esc_html__( 'Video played', 'bricks' ),
        ],
        'placeholder' => esc_html__( '', 'bricks' ),
      'group' => 'controlsSmall',
      'label'  => esc_html__( 'Hide until..', 'extras' ),
      'inline' => true,
      'required' => [
        ['layoutType','!=','custom'],
        ['layoutType','!=','type_two']
      ],
    ];

    $this->controls['smallcontrolGapTop'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'group' => 'controlsSmall',
      'label'  => esc_html__( 'Gap', 'extras' ),
      'css'    => [
        [
          'property' => '--media-button-gap',
          'selector' => '.xmp-layout_type-one .xmp-controls_group_top',
        ],
      ],
      'required' => [
        ['layoutType','!=','custom'],
      ],
    ];
    
     $this->controls['smallcontrolsCenterSep'] = [
      'tab' => 'content',
      'label'    => esc_html__( 'Center UI controls', 'bricks' ),
      'description' => esc_html__( 'Add/Remove/Re-order the internal controls', 'bricks' ),
      'type' => 'separator',
      'group' => 'controlsSmall',
      'required' => [
        ['layoutType','!=','custom'],
        ['layoutType','!=','type_two']
      ]
    ];

    

    $this->controls['smallcontrolsCenter'] = [
        'group' => 'controlsSmall',
        //'label'    => esc_html__( 'Controls', 'bricks' ),
        'placeholder'   => esc_html__( 'Control', 'bricks' ),
        'titleProperty' => 'control',
        'type'  => 'repeater',
        'required' => $smallRequirement,
        'fields'        => $this->controlFields,
        'default'       => [
          [ 'control' => 'spacer' ],
          [ 'control' => 'play-large' ],
          [ 'control' => 'spacer' ],
        ],
    ];

    $this->controls['smallcontrolsCenterVisibility'] = [
      'tab'    => 'content',
      'type'   => 'select',
        'options' => [
          'loaded' => esc_html__( 'Video loaded', 'bricks' ),
          'play' => esc_html__( 'Video played', 'bricks' ),
        ],
        'placeholder' => esc_html__( '', 'bricks' ),
      'group' => 'controlsSmall',
      'label'  => esc_html__( 'Hide until..', 'extras' ),
      'inline' => true,
      'required' => $smallRequirement,
    ];


    $this->controls['smallcontrolGapCenter'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'group' => 'controlsSmall',
      'label'  => esc_html__( 'Gap', 'extras' ),
      'css'    => [
        [
          'property' => '--media-button-gap',
          'selector' => '.xmp-layout_type-one .xmp-controls_group_center',
        ],
      ],
      'required' => [
        ['layoutType','!=','custom'],
      ],
    ];
    

    $this->controls['smallControlsBottomSep'] = [
      'tab' => 'content',
      'label'    => esc_html__( 'Bottom UI Controls', 'bricks' ),
      'description' => esc_html__( 'Add/Remove/Re-order the internal controls', 'bricks' ),
      'type' => 'separator',
      'group' => 'controlsSmall',
      'required' => [
        ['layoutType','!=','custom'],
        ['layoutType','!=','type_two']
      ]
    ];

    

    $this->controls['smallControls'] = [
      'group' => 'controlsSmall',
      'label'    => esc_html__( 'Controls', 'bricks' ),
      'placeholder'   => esc_html__( 'Control', 'bricks' ),
      'titleProperty' => 'control',
      'type'  => 'repeater',
      'required' => [
        ['layoutType','!=','custom'],
        ['layoutType','!=','type_two']
      ],
      'fields'        => $this->controlFields,
      'default'       => [
          [ 'control' => 'title' ],
      ],
    ];

    $this->controls['smallcontrolsBottomVisibility'] = [
      'tab'    => 'content',
      'type'   => 'select',
        'options' => [
          'loaded' => esc_html__( 'Video loaded', 'bricks' ),
          'play' => esc_html__( 'Video played', 'bricks' ),
        ],
        'placeholder' => esc_html__( '', 'bricks' ),
      'group' => 'controlsSmall',
      'label'  => esc_html__( 'Hide until..', 'extras' ),
      'inline' => true,
      'required' => [
        ['layoutType','!=','custom'],
        ['layoutType','!=','type_two']
      ],
    ];
    
    $this->controls['smallcontrolGapBottom'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'group' => 'controlsSmall',
      'label'  => esc_html__( 'Gap', 'extras' ),
      'css'    => [
        [
          'property' => '--media-button-gap',
          'selector' => '.xmp-layout_type-one .xmp-controls_group_bottom',
        ],
      ],
      'required' => [
        ['layoutType','!=','custom'],
      ],
    ];

      $this->controls['maybeTimeSliderSmall'] = [
        'group' => 'controlsSmall',
        'label'    => esc_html__( 'Bottom time slider', 'bricks' ),
        'info'    => esc_html__( 'Below bottom UI', 'bricks' ),
        'type'        => 'select',
        'options'     => [
          'enable' => esc_html__( 'Enable', 'bricks' ),
          'disable'  => esc_html__( 'Disable', 'bricks' ),
        ],
        'placeholder' => esc_html__( 'Enable', 'bricks' ),
        'inline'      => true,
        'required' => $smallRequirement
      ];


      $this->controls['controlsVideoOpacity'] = [
        'tab'    => 'content',
        'type'   => 'number',
        'step'   => 0.05,
        'group' => 'styleGeneral',
        'label'  => esc_html__( 'Video opacity (when controls show)', 'extras' ),
        'css'    => [
          [
            'property' => 'opacity',
            'selector' => '&[data-controls] media-provider',
          ],
          [
            'property' => 'opacity',
            'selector' => '&[data-buffering] media-provider',
          ],
          [
            'property' => 'opacity',
            'selector' => '&[data-paused] media-provider',
          ],
        ],
        'placeholder' => '.8',
      ];


    /* Buffering Indicator */

    $bufferringIndicator = '.xmp-buffering-indicator';

    $this->controls['bufferingIndicatorSep'] = [
      'tab' => 'content',
      'type' => 'separator',
      'group' => 'styleGeneral',
      'label' => esc_html__( 'Buffering indicator', 'bricks' ),
    ];

    $this->controls['bufferingIndicatorDisplay'] = [
      'tab' => 'content',
      'label' => esc_html__( 'Buffering indicator', 'bricks' ),
      'type' => 'select',
      'group' => 'styleGeneral',
      'options' => [
        'true' => esc_html__( 'Enable', 'bricks' ),
        'false' => esc_html__( 'Disable', 'bricks' ),
      ],
      'inline'      => true,
      'clearable' => false,
      'placeholder' => esc_html__( 'Enable', 'bricks' ),
    ];

    $this->controls['bufferingIndicatorDisplay'] = [
      'tab' => 'content',
      'label' => esc_html__( 'Buffering indicator', 'bricks' ),
      'type' => 'select',
      'group' => 'styleGeneral',
      'options' => [
        'true' => esc_html__( 'Enable', 'bricks' ),
        'false' => esc_html__( 'Disable', 'bricks' ),
      ],
      'inline'      => true,
      'clearable' => false,
      'placeholder' => esc_html__( 'Enable', 'bricks' ),
    ];

    $this->controls['bufferingIndicatorPreview'] = [
      'tab' => 'content',
      'label' => esc_html__( 'Preview in builder', 'bricks' ),
      'type' => 'checkbox',
      'group' => 'styleGeneral',
      'inline'      => true,
      'clearable' => false,
      'required' => ['bufferingIndicatorDisplay', '!=', 'false'],
    ];

    $this->controls['bufferingIndicatorFillColor'] = [
     'tab'    => 'content',
     'type'   => 'color',
     'group' => 'styleGeneral',
     'label'  => esc_html__( 'Fill color', 'extras' ),
     'css'    => [
       [
         'property' => '--media-buffering-track-fill-color',
         'selector' => $bufferringIndicator,
       ],
     ],
     'required' => ['bufferingIndicatorDisplay', '!=', 'false'],
   ];

    $this->controls['bufferingIndicatorTrackColor'] = [
      'tab'    => 'content',
      'type'   => 'color',
      'group' => 'styleGeneral',
      'label'  => esc_html__( 'Track color', 'extras' ),
      'css'    => [
        [
          'property' => '--media-buffering-track-color',
          'selector' => $bufferringIndicator,
        ],
      ],
      'required' => ['bufferingIndicatorDisplay', '!=', 'false'],
    ];

    

    $this->controls['bufferingIndicatorTrackWidth'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'small' => true,
      'group' => 'styleGeneral',
      'label'  => esc_html__( 'Track width', 'extras' ),
      'css'    => [
        [
          'property' => '--media-buffering-track-width',
          'selector' => $bufferringIndicator,
        ],
        [
         'property' => '--media-buffering-track-fill-width',
         'selector' => $bufferringIndicator,
       ],
      ],
      'required' => ['bufferingIndicatorDisplay', '!=', 'false'],
      'placeholder' => '2'
    ];

    

    $this->controls['bufferingIndicatorSize'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'small' => true,
      'group' => 'styleGeneral',
      'label'  => esc_html__( 'Size', 'extras' ),
      'css'    => [
        [
          'property' => '--media-buffering-size',
          'selector' => $bufferringIndicator,
        ],
      ],
    'required' => ['bufferingIndicatorDisplay', '!=', 'false'],
      'placeholder' => '96px'
    ];


    $this->controls['bufferingIndicatorTrackOpacity'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'group' => 'styleGeneral',
      'small' => true,
      'label'  => esc_html__( 'Track opacity', 'extras' ),
      'css'    => [
        [
          'property' => '--media-buffering-track-opacity',
          'selector' => $bufferringIndicator,
        ],
      ],
      'required' => ['bufferingIndicatorDisplay', '!=', 'false'],
    ];


    $this->controls['bufferingIndicatorTrackColor'] = [
      'tab'    => 'content',
      'type'   => 'color',
      'group' => 'styleGeneral',
      'label'  => esc_html__( 'Track color', 'extras' ),
      'css'    => [
        [
          'property' => '--media-buffering-track-color',
          'selector' => $bufferringIndicator,
        ],
      ],
      'required' => ['bufferingIndicatorDisplay', '!=', 'false'],
    ];


    $this->controls['bufferingIndicatorTrackFillOpacity'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'small' => true,
      'group' => 'styleGeneral',
      'label'  => esc_html__( 'Track fill opacity', 'extras' ),
      'css'    => [
        [
          'property' => '--media-buffering-track-fill-opacity',
          'selector' => $bufferringIndicator,
        ],
      ],
      'required' => ['bufferingIndicatorDisplay', '!=', 'false'],
    ];


    $this->controls['autoLocalPoster'] = [
      'label'    => esc_html__( 'Auto local poster image', 'bricks' ),
      'type'        => 'checkbox',
      'inline'      => true,
      'group' => 'behaviour',
      'info' => esc_html__( 'Save poster images locally', 'bricks' ),
      //'required' => ['loadingType', '=', ['custom','play']]
    ];
  
  
    

    $this->controls['playsinline'] = [
      'group' => 'behaviour',
      'label'    => esc_html__( 'Playsinline', 'bricks' ),
      'type'        => 'select',
      'options'     => [
        'enable' => esc_html__( 'Enable', 'bricks' ),
        'disable'  => esc_html__( 'Disable', 'bricks' ),
      ],
      'placeholder' => esc_html__( 'Enable', 'bricks' ),
      'inline'      => true,
      'small' => true,
      'rerender' => false,
    ];


    $this->controls['posterReshow'] = [
      'group' => 'behaviour',
      'label'    => esc_html__( 'Reshow poster image on pause', 'bricks' ),
      'type'        => 'checkbox',
      'inline'      => true,
      'small' => true,
      'rerender' => false,
      'required' => ['providerControls', '!=', 'enable'],
    ];

    

    $this->controls['controlsDelay'] = [
      'group' => 'behaviour',
      'label'    => esc_html__( 'Controls hiding delay (ms)', 'bricks' ),
      'type'        => 'number',
      'placeholder' => '2000',
      'inline'      => true,
      'small' => true,
    ];

    $this->controls['fullscreenFallback'] = [
      'group' => 'behaviour',
      'label'    => esc_html__( 'Fullscreen API fallback', 'bricks' ),
      'info' => esc_html__( 'For browsers not supporting the fullscreen API', 'bricks' ),
      'type'        => 'select', 
      'options'     => [
          'native' => esc_html__( "Use native fullscreen where available", 'bricks' ),
          'full_window' => esc_html__( 'Full window (Plyr style)', 'bricks' ),
          'none' => esc_html__( 'None', 'bricks' ),
      ],
      'placeholder' => esc_html__( 'Full window (Plyr style)', 'bricks' ), 
    ];

      /* loading */
      
          
      $this->controls['loadingSep'] = [
        'group' => 'behaviour',
        'label'    => esc_html__( 'Loading media', 'bricks' ),
        'type'        => 'separator',
        'required' => ['providerControls', '!=', 'enable']
    ];
    
        $this->controls['loadingType'] = [
        'group' => 'behaviour',
        'label'    => esc_html__( 'Media Loading', 'bricks' ),
        'type'        => 'select',
        'options'     => [
            //'eager' => esc_html__( 'Eager', 'bricks' ),
            //'idle'  => esc_html__( 'Idle', 'bricks' ),
            'visible'  => esc_html__( 'When visible (lazy)', 'bricks' ),
            'click' => esc_html__( 'On click (user first clicks player to load in media)', 'bricks' ),
            'play' => esc_html__( "On play (Fallbacks to 'on click' for iFrame providers)", 'bricks' ),
            'custom' => esc_html__( 'Custom (using interactions)', 'bricks' ),
        ],
        'placeholder' => esc_html__( 'When visible (lazy)', 'bricks' ),
        'required' => ['providerControls', '!=', 'enable']
        
    ];


    $this->controls['loadingUISep'] = [
      'group' => 'behaviour',
      'label'    => esc_html__( 'Loading UI', 'bricks' ),
      'type'        => 'separator',
      'required' => [
      ['layoutType', '!=', ['custom']],
      ['providerControls', '!=', 'enable']
    ]
    ];
  
    $this->controls['loadingUI'] = [
    'group' => 'behaviour',
    'label'    => esc_html__( 'UI', 'bricks' ),
    'type'        => 'select',
    'options'     => [
        'eager' => esc_html__( 'Eager', 'bricks' ),
        'visible'  => esc_html__( 'When visible (lazy)', 'bricks' ),
        //'click' => esc_html__( 'On click', 'bricks' ),
    ],
    'inline' => true,
    'placeholder' => esc_html__( 'When visible (lazy)', 'bricks' ),
    'required' => [
      ['layoutType', '!=', ['custom']],
      ['providerControls', '!=', 'enable']
    ]
    ];
  
    

    $this->controls['securitySep'] = [
      'tab' => 'content',
      'label' => esc_html__('Bunny Stream', 'bricks'),
      'group' => 'behaviour',
      
      'type' => 'separator',
   ];
   
    
    // Add security controls
    $this->controls['enableSecurity'] = [
        'tab' => 'content',
        'group' => 'behaviour',
        'label' => esc_html__('Generate Authentication Tokens', 'bricksextras'),
        'description' => esc_html__('Auto-generate authentication tokens for Bunny Stream videos with CDN Token Authentication enabled (Enable to create expiring video URLs that prevent permanent access and unauthorized sharing)', 'bricks'),
        'type' => 'checkbox',
    ];
  
    $this->controls['securityExpiry'] = [
        'tab' => 'content',
        'group' => 'behaviour',
        'label' => esc_html__('Token Expiry Time (seconds)', 'bricksextras'),
        'info' => esc_html__('How long the authentication token will be valid', 'bricksextras'),
        'type' => 'number',
        'placeholder' => '3600 (1 hour)',
        'required' => [
            ['enableSecurity', '=', true],
        ],
    ];

    $this->controls['autoCaptionsBunny'] = [
      'tab' => 'content',
      'group' => 'behaviour',
      'label' => esc_html__('Auto populate captions', 'bricksextras'),
      'description' => esc_html__('Auto populate captions from Bunny Stream videos', 'bricks'),
      'type' => 'checkbox',
  ];

    
    $this->controls['detectLanguage'] = [
      'tab' => 'content',
      'group' => 'behaviour',
      'label' => esc_html__('Auto Detect site language', 'bricksextras'),
      'description' => esc_html__('If captions are detected matching current language, it will be selected automatically', 'bricks'),
      'type' => 'checkbox',
    ];



    /* gestures */

    $this->controls['gestures'] = [
      'tab' => 'content',
      'label' => esc_html__( 'Gestures', 'bricks' ),
      'type' => 'separator',
      'group' => 'behaviour',
    ];

    $this->controls['clickToPlay'] = [
      'label'    => esc_html__( 'Click to play', 'bricks' ),
      'type'        => 'select',
      'options'     => [
        'enable' => esc_html__( 'Enable', 'bricks' ),
        'disable'  => esc_html__( 'Disable', 'bricks' ),
      ],
      'placeholder' => esc_html__( 'Enable', 'bricks' ),
      'inline'      => true,
      'group' => 'behaviour',
      'info' => esc_html__( 'Disabled in builder', 'bricks' ),
    ];



   /* notice */

    $this->controls['maybeNotice'] = [
      'group' => 'noticeOverlay',
      'label'    => esc_html__( 'Add text notice', 'bricks' ),
      'type'        => 'select',
      'options'     => [
        'enable' => esc_html__( 'Enable', 'bricks' ),
        'disable'  => esc_html__( 'Disable', 'bricks' ),
      ],
      'placeholder' => esc_html__( 'Disable', 'bricks' ),
      'inline'      => true,
      'required' => ['layoutType','!=','custom'],
    ];

    $this->controls['noticeText'] = [
      'group' => 'noticeOverlay',
      'label'    => esc_html__( 'Notice text', 'bricks' ),
      'type'        => 'text',
      'placeholder' => esc_html__( 'Notice: Your message here', 'bricks' ),
    // 'inline'      => true,
      'required' => [
        ['layoutType','!=','custom'],
        ['maybeNotice','=','enable']
      ]
    ];

    $this->controls['noticeVisibility'] = [
      'group' => 'noticeOverlay',
      'label'    => esc_html__( 'Notice visibility', 'bricks' ),
      'type'        => 'select',
      'options'     => [
          'first'  => esc_html__( 'Only visible before play', 'bricks' ),
          'play'  => esc_html__( 'Only visible after play', 'bricks' ),
          'loaded' => esc_html__( 'Only visible when player ready to play', 'bricks' ),
          'before_loaded' => esc_html__( 'Only visible before player ready to play', 'bricks' ),
      ],
      'placeholder' => esc_html__( 'Only visible before play', 'bricks' ),
      //'inline'      => true,
      'required' => [
        ['layoutType','!=','custom'],
        ['maybeNotice','=','enable']
      ]
    ];


    /*Notice text */

    $noticeText = '.x-notice-text';

    $this->controls['noticeTextBg'] = [
      'tab'    => 'content',
      'type'   => 'color',
      'group' => 'noticeOverlay',
      'label'  => esc_html__( 'Background', 'extras' ),
      'css'    => [
        [
          'property' => 'background-color',
          'selector' => $noticeText,
        ],
      ],
      'required' => [
        ['layoutType','!=','custom'],
        ['maybeNotice','=','enable']
      ]
    ];

    $this->controls['noticeTextBorder'] = [
      'tab'    => 'content',
      'type'   => 'border',
      'group' => 'noticeOverlay',
      'label'  => esc_html__( 'Border', 'extras' ),
      'css'    => [
        [
          'property' => 'border',
          'selector' => $noticeText
        ],
      ],
      'required' => [
        ['layoutType','!=','custom'],
        ['maybeNotice','=','enable']
      ]
    ];
    

    $this->controls['noticeTextTypography'] = [
      'tab'    => 'content',
      'type'   => 'typography',
      'group' => 'noticeOverlay',
      'label'  => esc_html__( 'Typography', 'extras' ),
      'css'    => [
        [
          'property' => 'font',
          'selector' => $noticeText
        ],
      ],
      'required' => [
        ['layoutType','!=','custom'],
        ['maybeNotice','=','enable']
      ]
    ];

    $this->controls['noticeTextPadding'] = [
      'tab'    => 'content',
      'type'   => 'dimensions',
      'group' => 'noticeOverlay',
      'label'  => esc_html__( 'Padding', 'extras' ),
      'css'    => [
        [
          'property' => 'padding',
          'selector' => $noticeText
        ],
      ],
      'required' => [
        ['layoutType','!=','custom'],
        ['maybeNotice','=','enable']
      ]
    ];

    $this->controls['noticeTextWidth'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'group' => 'noticeOverlay',
      'label'  => esc_html__( 'Width', 'extras' ),
      'css'    => [
        [
          'property' => 'width',
          'selector' => $noticeText
        ],
      ],
      'required' => [
        ['layoutType','!=','custom'],
        ['maybeNotice','=','enable']
      ]
    ];

    $this->controls['noticeTextHeight'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'group' => 'noticeOverlay',
      'label'  => esc_html__( 'Height', 'extras' ),
      'css'    => [
        [
          'property' => 'height',
          'selector' => $noticeText
        ],
      ],
      'required' => [
        ['layoutType','!=','custom'],
        ['maybeNotice','=','enable']
      ]
    ];


    $this->controls['noticeJustifyContent'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Align main axis', 'bricks' ),
      'group'		  => 'noticeOverlay',
      'tooltip'  => [
        'content'  => 'justify-content',
        'position' => 'top-left',
      ],
      'type'     => 'justify-content',
      'css'      => [
        [
          'property' => 'justify-content',
          'selector' => '.xmp-custom-overlay',
        ],
        [
          'property' => 'justify-content',
          'selector' => $noticeText
        ],
      ],
      'required' => [
        ['layoutType','!=','custom'],
        ['maybeNotice','=','enable']
      ]
    ];

    $this->controls['noticeAlignItems'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Align cross axis', 'bricks' ),
      'group'		  => 'noticeOverlay',
      'tooltip'  => [
        'content'  => 'align-items',
        'position' => 'top-left',
      ],
      'type'     => 'align-items',
      'css'      => [
        [
          'property' => 'align-items',
          'selector' => '.xmp-custom-overlay',
        ],
      ],
      'required' => [
        ['layoutType','!=','custom'],
        ['maybeNotice','=','enable']
      ]
    ];


  } 


  public function render() {

    $settings = $this->settings;

    $provider = 'video';

    $is_gutenberg = $this->is_gutenberg_editor();
    
    $src = $this->get_media_source($settings, $provider);

    $sourceType = isset($settings['sourceType']) ? $settings['sourceType'] : 'url';

    if (isset($settings['multipleSources']) && $sourceType === 'url') {
      
      $additionalSources = [];

      if (isset($settings['srcRepeater']) && is_array($settings['srcRepeater'])) {
          foreach ($settings['srcRepeater'] as $item) {
              $source = [];
              
              if (isset($item['source'])) $source['src'] = $item['source'];
              if (isset($item['type'])) $source['type'] = $item['type'];
              if (isset($item['width'])) {
                $source['width'] = $item['width'];
              } else { 
                $source['width'] = 1000;
              }
              
              if (isset($item['height'])) $source['height'] = $item['height'];
              
              $additionalSources[] = $source;
          }
      }

      $additional_sources_json = htmlspecialchars(
        wp_json_encode($additionalSources)
      );

    
      $this->set_attribute('_root', 'data-x-srcs', $additional_sources_json);
    }

    $colorScheme = isset( $settings['colorScheme'] ) ? esc_attr( $settings['colorScheme'] ) : 'dark';

    $positioning = isset( $settings['positioning'] ) ? $settings['positioning'] : 'default';
    $altText = isset( $settings['altText'] ) ? esc_attr( $settings['altText'] ) : 'video cover image';
    $layout = isset( $settings['layout'] ) ? $settings['layout'] : 'default';
    $playsinline = isset( $settings['playsinline'] ) ? 'enable' === $settings['playsinline'] : true;
    $providerControls = isset( $settings['providerControls'] ) ? 'enable' === $settings['providerControls'] : false;
    $controlsDelay = isset( $settings['controlsDelay'] ) ? intval($settings['controlsDelay']) : 2000;
    $loop = isset( $settings['loop'] );
    $muted = isset( $settings['muted'] );
    $autoplay = isset( $settings['autoplay'] );
    $fullscreenOrientation = isset( $settings['fullscreenOrientation'] ) ? $settings['fullscreenOrientation'] : 'landscape';
    $hideNested = isset( $settings['hideNested'] ) ? $settings['hideNested'] : 'firstplay';
    $loadingType = isset( $settings['loadingType'] ) ? $settings['loadingType'] : 'visible';
    
    if ($providerControls)   {
      $loadingType = 'visible';
    }

    $title = isset( $settings['title'] ) ? esc_attr__( $settings['title'] ) : '';
    $maybeToolTips = isset( $settings['maybeToolTips'] ) ? 'enable' === $settings['maybeToolTips'] : false;
    $defaultTooltipPlacement = isset( $settings['defaultTooltipPlacement'] ) ? esc_attr( $settings['defaultTooltipPlacement'] ) : 'top';
    $bufferingIndicatorDisplay = isset( $settings['bufferingIndicatorDisplay'] ) ? 'true' === $settings['bufferingIndicatorDisplay'] : true;
    $portalSettings = isset( $settings['portalSettings'] );
    $clipStart = isset( $settings['clipStartTime'] ) ? $settings['clipStartTime'] : false;
    $clipEnd = isset( $settings['clipEndTime'] ) ? $settings['clipEndTime'] : false;
    $clipStartTime =  $clipStart ? \BricksExtras\Helpers::timeToSeconds( $this->render_dynamic_data( $clipStart ) ) : 0;
	  $clipEndTime =  $clipEnd ? \BricksExtras\Helpers::timeToSeconds( $this->render_dynamic_data( $clipEnd ) ) : 0;
    $textTracks = ! empty( $settings['textTracks'] ) ? $settings['textTracks'] : false;
    $image  = \BricksExtras\Helpers::get_normalized_image_settings( $settings );
    $image_id   = $image['id'];
    $image_url  = bricks_render_dynamic_data( $image['url'] );
    $image_size = $image['size'];
    $imgLoading = isset( $settings['imgLoading'] ) ? esc_attr( $settings['imgLoading'] ) : 'lazy';
    $posterURL = $image_url ? esc_url( $image_url ) : '';
    $controls = ! empty( $settings['controls'] ) ? $settings['controls'] : false;
    $controlsTop = ! empty( $settings['controlsTop'] ) ? $settings['controlsTop'] : false;
    $controlsCenter = ! empty( $settings['controlsCenter'] ) ? $settings['controlsCenter'] : false;
    $smallcontrolsCenter = ! empty( $settings['smallcontrolsCenter'] ) ? $settings['smallcontrolsCenter'] : false;
    $smallControls = ! empty( $settings['smallControls'] ) ? $settings['smallControls'] : false;
    $smallControlsTop = ! empty( $settings['smallControlsTop'] ) ? $settings['smallControlsTop'] : false;
    $initControls = ! empty( $settings['initControls'] ) ? $settings['initControls'] : false;
    $chapters = ! empty( $settings['chapters'] ) ? $settings['chapters'] : false;
    $chapterText = isset( $settings['chapterText'] ) ? esc_attr( $settings['chapterText'] ) : '';
    $chapterStart = isset( $settings['chapterStart'] ) ? esc_attr( $settings['chapterStart'] ) : '';
    $chapterEnd = isset( $settings['chapterEnd'] ) ? esc_attr( $settings['chapterEnd'] ) : '';
    $thumbnailAttr = isset( $settings['thumbnailFile'] ) ? 'src="' . esc_url( $settings['thumbnailFile'] ) . '"' : '';
    $thumbnails = isset( $settings['thumbnailFile'] ) ? esc_url( $settings['thumbnailFile'] ) : false;
    $layoutType = isset( $settings['layoutType'] ) ? esc_attr( $settings['layoutType'] ) : 'type_one';
    $smallControlsBreakpoint = isset( $settings['smallControlsBreakpoint'] ) ? intval( $settings['smallControlsBreakpoint'] ) : 478;
    $maybeTimeSlider = isset( $settings['maybeTimeSlider'] ) ? 'enable' === $settings['maybeTimeSlider'] : true;
    $maybeTimeSliderSmall = isset( $settings['maybeTimeSliderSmall'] ) ? 'enable' === $settings['maybeTimeSliderSmall'] : true;
    $maybeNotice = isset( $settings['maybeNotice'] ) ? 'enable' === $settings['maybeNotice'] : false;
    $noticeText = ! empty( $settings['noticeText'] ) ? $settings['noticeText'] : 'Notice: Your message here';
    $noticeVisibility = isset( $settings['noticeVisibility'] ) ? $settings['noticeVisibility'] : 'first';
    $posterReshow = isset( $settings['posterReshow'] );
    $rememberPlayerSettings = isset( $settings['rememberPlayerSettings'] );
    $autoLocalPoster = isset( $settings['autoLocalPoster'] );
    $clickToPlay = isset( $settings['clickToPlay'] ) ? 'enable' === $settings['clickToPlay'] : true;
    $doubleClickToFullScreen = isset( $settings['doubleClickToFullScreen'] ) ? 'enable' === $settings['doubleClickToFullScreen'] : false;
    $fullscreenFallback = isset( $settings['fullscreenFallback'] ) ? $settings['fullscreenFallback'] : 'full_window';
    $loadingUI = isset( $settings['loadingUI'] ) ? $settings['loadingUI'] : 'visible';
    $componentScope = isset( $this->settings['componentScope'] ) ? $this->settings['componentScope'] : 'false';
    $whichPlaylist = isset( $settings['whichPlaylist'] ) ? $settings['whichPlaylist'] : 'section';
    $playlistSelector = isset( $settings['playlistSelector'] ) ? $settings['playlistSelector'] : '';
    $streamType = isset( $settings['streamType'] ) ? $settings['streamType'] : 'on-demand';

    $playReset = isset( $settings['playReset'] );


    if ('custom' === $layoutType) {
      $loadingUI = 'eager'; 
    }

    // Generate and set a unique identifier for this instance
    $playerIndentifier = \BricksExtras\Helpers::set_identifier_attribute( $this );
    
    // Use the common config setup method from the trait
    $config = $this->setup_common_config(
      $settings,
      $layoutType,
      $loadingType,
      $clickToPlay,
      $autoLocalPoster,
      $whichPlaylist,
      $playlistSelector,
      $posterURL,
      $thumbnails,
      $rememberPlayerSettings
    );
    
    // Add video-specific config settings
    $config['fullscreenFallBack'] = $fullscreenFallback;
    
    // Add lazy UI config if needed
    if ('visible' === $loadingUI && 'custom' !== $layoutType) {
      $config += [
        'lazyUI' => $loadingUI,
      ];
    }
    
    // Add poster reshow config if needed
    if ($posterReshow && !$providerControls) {
      $config += [
        'reshowPoster' => 'true',
      ];
    }
    
    // Add container layout breakpoint if needed
    if ('container' === $layoutType) {
      $config += [
        'breakpoint' => $smallControlsBreakpoint,
      ];
    }

    if ( isset( $settings['autoCaptionsBunny'] ) ) {
      $config += [
        'autoCaptionsBunny' => true
      ];
    }

    if ( isset( $settings['detectLanguage'] ) ) {
      $config += [
        'detectLanguage' => true
      ];
    }
    
    // Process chapters using the trait method
    $chaptersArray = $this->process_chapters($settings);
    
    // Set chapters attribute if valid chapters exist
    $this->set_chapters_attribute($chaptersArray);
    
    // Set common attributes using the trait method
    $this->set_common_attributes(
      $settings,
      'video',
      $config,
      $src,
      $layoutType,
      $positioning,
      $rememberPlayerSettings ? $playerIndentifier : '',
      $is_gutenberg
    );

    // play reset
    if ( $muted && $autoplay && $playReset && !\BricksExtras\Helpers::maybePreview() ) {
      $this->set_attribute('_root', 'data-x-play-reset', 'true');
    }
    
    // Set video-specific attributes
    $this->set_attribute('_root', 'data-lg', '');
    $this->set_attribute('_root', 'controls-delay', esc_attr($controlsDelay));
    
    if ($playsinline) {
      $this->set_attribute('_root', 'playsInline', true);
    }
    
    /* find if poster image has been saved, load from server if so */
    if (!BricksExtras\Helpers::maybePreview() && !$image_url) {
      $this->set_attribute('_root', 'poster-load', 'visible');
      
      if ($autoLocalPoster && get_option('bricksextras_media_poster_url')) {
        $savedPosterImages = json_decode(sanitize_text_field(get_option('bricksextras_media_poster_url')), TRUE);
        $videoID = \BricksExtras\Helpers::get_video_id_from_url(esc_url($src));
        
        if ($savedPosterImages && is_array($savedPosterImages)) {
          if (array_key_exists($videoID, $savedPosterImages)) {
            $thumbnail_dir_info = wp_get_upload_dir();
            $newVideoURL = esc_url($thumbnail_dir_info['baseurl'] . '/bricksextras/posters/' . $savedPosterImages[$videoID]);
            $this->set_attribute('_root', 'data-x-local-poster', esc_attr($newVideoURL));
            $posterURL = esc_attr($newVideoURL);
          }
        }
      }
    }
    
    $firstOutput = '';
    $output = '';
    $lastOutput = '';

    $firstOutput .= "<media-player {$this->render_attributes( '_root' )}>";
    $firstOutput .=  "<media-provider>";

        if ( $posterURL ) {

          if ( 'custom' === $layoutType && BricksExtras\Helpers::maybePreview() ) {
              /* in builder */
              $firstOutput .=  "<media-poster {$this->render_attributes( 'media-poster' )}>";
              $firstOutput .=  "<img class='xmp-poster_initial' src='" . $posterURL . "' alt='" . esc_attr( $altText ) . "' >";
              $firstOutput .=  "</media-poster>";
          } else {
            $firstOutput .=  "<media-poster {$this->render_attributes( 'media-poster' )}>";

              if ( $image_id && $image_size ) {
                $firstOutput .=  wp_get_attachment_image($image_id, $image_size, false, ['class' => 'xmp-poster_initial', 'loading' => $imgLoading, 'alt' => esc_attr( $altText )]);
                
              }
              else {
                $firstOutput .=  "<img class='xmp-poster_initial' src='" . $posterURL . "' alt='" . esc_attr( $altText ) . "' loading='" . $imgLoading . "'>";
              }

              
              $firstOutput .=  "</media-poster>";
          }
  
          
        } else {
          $firstOutput .= "<media-poster alt='" . esc_attr( $altText ) . "' class='xmp-poster'></media-poster>";
        }

			if ( $textTracks ) {
        $firstOutput .= $this->render_text_tracks($textTracks);
			}

      $firstOutput .=  "</media-provider>";

    if ( $maybeNotice && 'custom' !== $layoutType) {
      $firstOutput .= '<div data-x-control-visibility="' . esc_attr( $noticeVisibility ) . '" class="xmp-custom-overlay"><div class="x-notice-text"><span>' .  wp_kses_post( $noticeText ) . '</span></div></div>';
    }

    if (!$providerControls) {

      if ('custom' !== $layoutType) {

        $output = $this->render_video_player(
          $output,
          $provider,
          $layoutType,
          $controlsTop,
          $controlsCenter,
          $controls,
          $smallControlsTop,
          $smallcontrolsCenter,
          $smallControls,
          $maybeTimeSlider,
          $maybeTimeSliderSmall,
          $maybeToolTips,
          $defaultTooltipPlacement,
          $title,
          $thumbnailAttr,
          $colorScheme,
          $portalSettings,
          $playerIndentifier,
          $settings
        );
      }

    if ( 'custom' === $layoutType ) {
      $output .= "<media-layout {$this->render_attributes( 'x-media-player_inner' )}>";
    } else {
      $output .= BricksExtras\Helpers::maybePreview() ? "<media-layout {$this->render_attributes( 'x-media-player_hidden-inner' )}>" : "";
    }

    /* nesting elements */
    if ( 'custom' === $layoutType || BricksExtras\Helpers::maybePreview() ) {

      if ( method_exists('\Bricks\Frontend','render_children') ) {
        $output .= "<media-controls  {$this->render_attributes( 'media-controls' )}>";
        $output .=  \Bricks\Frontend::render_children( $this );
        $output .= "</media-controls>";
      }

    }

    if ( 'custom' === $layoutType ) {
      $output .= "</media-layout>";
    } else {
      $output .= BricksExtras\Helpers::maybePreview() ? "</media-layout>" : "";
    }

    $output .= '<div class="xmp-media-features" data-x-color="' . $colorScheme . '">';

    $bufferingClass = BricksExtras\Helpers::maybePreview() && isset( $settings['bufferingIndicatorPreview'] ) ? 'xmp-buffering-indicator xmp-buffering-indicator_preview' : 'xmp-buffering-indicator';

    $output .= $bufferingIndicatorDisplay ? '<div class="' . $bufferingClass . '">
        <media-spinner class="xmp-buffering-spinner"></media-spinner>
      </div>': '';

    /* gestures */

    $output .= '<media-gesture class="xmp-gesture" event="pointerup" action="toggle:controls"></media-gesture>';

    if ( $clickToPlay && !BricksExtras\Helpers::maybePreview() ) {
      $output .= '<media-gesture event="click" action="toggle:paused"></media-gesture><media-gesture event="keyup" action="play"></media-gesture>';
    }


    } /* end of !providerControls */

    $output .= '<media-captions class="xmp-captions"></media-captions>';

    $output .= "<media-announcer></media-announcer>";

    $output .= '</div>';

    if (!$is_gutenberg) {

      echo $firstOutput;

      if ( 'eager' === $loadingUI || BricksExtras\Helpers::maybePreview() ) { 
        echo $output;
      } else {
        // Output template for lazy loading
        echo "<template data-x-mediaplayer-template=\"" . esc_attr($playerIndentifier) . "\">" . $output . "</template>";
      }

    echo "</media-player>";

    } else {

      $this->set_attribute( '_root', 'class', 'x-media-player_editor' );

      echo "<div {$this->render_attributes( '_root' )}>";
      echo "<div class='x-media-player_placeholder'>Media Player</div>";
      echo "</div>";

    }
    
  }


  /**
 * Render the video player layout
 */
  private function render_video_player(
    $output,
    $provider,
    $layoutType,
    $controlsTop,
    $controlsCenter,
    $controls,
    $smallControlsTop,
    $smallcontrolsCenter,
    $smallControls,
    $maybeTimeSlider,
    $maybeTimeSliderSmall,
    $maybeToolTips,
    $defaultTooltipPlacement,
    $title,
    $thumbnailAttr,
    $colorScheme,
    $portalSettings,
    $playerIndentifier,
    $settings
    ) 
    {

    if ('type_two' === $layoutType) {
      $output .= '<media-layout class="xmp-layout_type-two">';
      
      if ('custom' !== $layoutType && 'type_two' === $layoutType) {
        $playerControls = '';
        $playerControls .= "<media-controls {$this->render_attributes('media-controls')}>";
        
        // Render top controls for large layout
        if (!!$controlsTop && ('custom' !== $layoutType)) {
          $controlsTopVisibility = isset($settings['controlsTopVisibility']) ? 
            'data-x-control-visibility="' . esc_attr($settings['controlsTopVisibility']) . '"' : 'data-x-control-visibility="default"';
          
          $playerControls .= '<div class="xmp-controls_group xmp-controls_group_top" ' . $controlsTopVisibility . '>';
          foreach ($controlsTop as $control) {
            $playerControls .= $this->renderControl(
              $control, 
              $provider, 
              $maybeToolTips, 
              $defaultTooltipPlacement, 
              'top', 
              $title, 
              '' /* artist */,
              $thumbnailAttr, 
              $colorScheme, 
              $portalSettings, 
              $playerIndentifier
            );
          }
          $playerControls .= '</div>';
        }
        
        // Render center controls for large layout
        if (!!$controlsCenter) {
          $controlsCenterVisibility = isset($settings['controlsCenterVisibility']) ? 
            'data-x-control-visibility="' . esc_attr($settings['controlsCenterVisibility']) . '"' : 'data-x-control-visibility="default"';
          
          $playerControls .= '<div class="xmp-controls_group xmp-controls_group_center" ' . $controlsCenterVisibility . '>';
          foreach ($controlsCenter as $control) {
            $playerControls .= $this->renderControl(
              $control, 
              $provider, 
              $maybeToolTips, 
              $defaultTooltipPlacement, 
              'top', 
              $title, 
              '' /* artist */,
              $thumbnailAttr, 
              $colorScheme, 
              $portalSettings, 
              $playerIndentifier
            );
          }
          $playerControls .= '</div>';
        }
        
        // Render time slider for large layout
        $controlsBottomVisibility = isset($settings['controlsBottomVisibility']) ? 
          'data-x-control-visibility="' . esc_attr($settings['controlsBottomVisibility']) . '"' : 'data-x-control-visibility="default"';
        
        if ($maybeTimeSlider) {
          $playerControls .= $this->render_time_slider($controlsBottomVisibility, $thumbnailAttr);
        }
        
        // Render bottom controls for large layout
        $playerControls .= '<div class="xmp-controls_group xmp-controls_group_bottom" ' . $controlsBottomVisibility . '>';
        
        if (!!$controls && ('custom' !== $layoutType)) {
          foreach ($controls as $control) {
            $playerControls .= $this->renderControl(
              $control, 
              $provider, 
              $maybeToolTips, 
              $defaultTooltipPlacement, 
              'bottom', 
              $title, 
              '' /* artist */,
              $thumbnailAttr, 
              $colorScheme, 
              $portalSettings, 
              $playerIndentifier
            );
          }
        }
        
        $playerControls .= "</div>";
        $playerControls .= "</media-controls>";
        
        $output .= $playerControls;
      }
      
      $output .= "</media-layout>";
    }
    
    // Now handle the small layout (when layoutType is not 'type_two')
    if ('type_two' !== $layoutType) {
      $output .= '<media-layout class="xmp-layout_type-one">';
      $playerControls = '';
      
      if ('custom' !== $layoutType) {
        $playerControls .= "<media-controls {$this->render_attributes('media-controls')}>";
        
        // Render top controls for small layout
        if (!!$smallControlsTop && ('custom' !== $layoutType)) {
          $smallcontrolsTopVisibility = isset($settings['smallcontrolsTopVisibility']) ? 
            'data-x-control-visibility="' . esc_attr($settings['smallcontrolsTopVisibility']) . '"' : 'data-x-control-visibility="default"';
          
          $playerControls .= '<div class="xmp-controls_group xmp-controls_group_top" ' . $smallcontrolsTopVisibility . '>';
          foreach ($smallControlsTop as $control) {
            $playerControls .= $this->renderControl(
              $control, 
              $provider, 
              $maybeToolTips, 
              $defaultTooltipPlacement, 
              'top', 
              $title, 
              '' /* artist */,
              $thumbnailAttr, 
              $colorScheme, 
              $portalSettings, 
              $playerIndentifier
            );
          }
          $playerControls .= '</div>';
        }
        
        // Render center controls for small layout
        if (!!$smallcontrolsCenter) {
          $smallcontrolsCenterVisibility = isset($settings['smallcontrolsCenterVisibility']) ? 
            'data-x-control-visibility="' . esc_attr($settings['smallcontrolsCenterVisibility']) . '"' : 'data-x-control-visibility="default"';
          
          $playerControls .= '<div class="xmp-controls_group xmp-controls_group_center" ' . $smallcontrolsCenterVisibility . '>';
          foreach ($smallcontrolsCenter as $control) {
            $playerControls .= $this->renderControl(
              $control, 
              $provider, 
              $maybeToolTips, 
              $defaultTooltipPlacement, 
              'top', 
              $title, 
              '' /* artist */,
              $thumbnailAttr, 
              $colorScheme, 
              $portalSettings, 
              $playerIndentifier
            );
          }
          $playerControls .= '</div>';
        }
        
        // Render bottom controls for small layout
        $smallcontrolsBottomVisibility = isset($settings['smallcontrolsBottomVisibility']) ? 
          'data-x-control-visibility="' . esc_attr($settings['smallcontrolsBottomVisibility']) . '"' : 'data-x-control-visibility="default"';
        
        $playerControls .= '<div class="xmp-controls_group xmp-controls_group_bottom" ' . $smallcontrolsBottomVisibility . '>';
        
        if (!!$smallControls && ('custom' !== $layoutType)) {
          foreach ($smallControls as $control) {
            $playerControls .= $this->renderControl(
              $control, 
              $provider, 
              $maybeToolTips, 
              $defaultTooltipPlacement, 
              'bottom', 
              $title, 
              '' /* artist */,
              $thumbnailAttr, 
              $colorScheme, 
              $portalSettings, 
              $playerIndentifier
            );
          }
        }
        
        $playerControls .= "</div>";
        
        // Render time slider for small layout
        if ($maybeTimeSliderSmall) {
          $playerControls .= $this->render_time_slider($smallcontrolsBottomVisibility, $thumbnailAttr);
        }
        
        $playerControls .= "</media-controls>";
      }
      
      $output .= $playerControls;
      $output .= "</media-layout>";
    }
    
    return $output;
  }

  
}