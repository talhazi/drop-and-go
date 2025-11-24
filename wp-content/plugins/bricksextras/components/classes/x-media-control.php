<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class X_Media_Control extends \Bricks\Element {

  // Element properties
    public $category     = 'extras';
    public $name         = 'xmediacontrol';
    public $icon         = 'ti-control-forward';
    public $css_selector = '';

  
  public function get_label() {
	  return esc_html__( 'Media Control', 'extras' );
  }

  public function get_keywords() {
    return [ 'video', 'audio', 'media', 'player', 'controls' ];
    }
    
  public function set_control_groups() {

    $this->control_groups['labels'] = [
      'title' => esc_html__( 'Labels', 'bricks' ),
      'required' => ['controlType', '!=', 
        ['time','title','artist','custom-text','nest','time-slider','live-button','chapter-title','poster-image','spacer']
      ]
    ];

    $this->control_groups['styleControls'] = [
        'title' => esc_html__( 'Styling', 'bricks' ),
      ];

    $this->control_groups['buttonIcons'] = [
      'title' => esc_html__( 'Icons', 'bricks' ),
      'required' => ['controlType', '!=', 
        ['time','title','artist','custom-text','nest','time-slider','live-button','chapter-title','poster-image','spacer']
      ]
    ];

    $this->control_groups['tooltips'] = [
      'title' => esc_html__( 'Tooltips', 'bricks' ),
      'required' => ['controlType', '!=', 
        ['time','title','artist','custom-text','nest','time-slider','live-button','chapter-title','poster-image','spacer']
      ]
    ];

  }

  public function set_controls() {

    $this->controls['intro'] = [
      'tab'         => 'content',
      'label'    => esc_html__( 'Media Control', 'bricks' ),
      'description'    => esc_html__( 'This element should be placed inside of the Media Player', 'bricks' ),
      'type'     => 'separator',
    ];

    $placementOptions = [
      'bottom' => esc_html__( 'Bottom', 'bricks' ), 
      'bottom-start' => esc_html__( 'Bottom Start', 'bricks' ), 
      'bottom-end' => esc_html__( 'Bottom End', 'bricks' ),
      'top' => esc_html__('Top', 'bricks' ), 
      'top-start' => esc_html__( 'Top Start', 'bricks' ), 
      'top-end' => esc_html__( 'Top End', 'bricks' ),
    ];


    $this->controls['controlType'] = [
        'label'    => esc_html__( 'Control type', 'bricks' ),
        'type'        => 'select',
        'clearable' => false,
        'searchable' => true,
        'rerender' => true,
        'options'     => [
          'artist' => esc_html__('Artist', 'bricks' ),
          'captions' => esc_html__('Caption Toggle *', 'bricks' ),
          'chapters' => esc_html__('Chapters Menu', 'bricks' ),
          'chapter-title' => esc_html__('Chapter Title *', 'bricks' ),
          'download' => esc_html__('Download *', 'bricks' ),
          'live-button' => esc_html__('Live Button', 'bricks' ),
          'fullscreen' => esc_html__('Fullscreen *', 'bricks' ),
          'next' => esc_html__('Next item (playlist)', 'bricks' ),
          'play-large'  => esc_html__('Play / Pause (large)', 'bricks' ),
          'play'   => esc_html__('Play / Pause', 'bricks' ),
          'pip' => esc_html__('PIP *', 'bricks' ),
          'poster-image' => esc_html__('Poster Image', 'bricks' ),
          'previous' => esc_html__('Previous item (playlist)', 'bricks' ),
          'spacer' => esc_html__('Spacer', 'bricks' ),
          'seek-backward' => esc_html__('Seek Backward', 'bricks' ),
          'seek-forward' => esc_html__('Seek Forward', 'bricks' ),
          'settings' => esc_html__('Settings Menu', 'bricks' ),
          'custom-text' => esc_html__('Text', 'bricks' ),
          'title' => esc_html__('Title', 'bricks' ),
          'time'  => esc_html__('Time', 'bricks' ),
          'time-slider' => esc_html__('Time Slider', 'bricks' ),
          'mute' => esc_html__('Volume / Mute', 'bricks' ),
          //'custom' => 'Custom',
         // 'nest' => 'Nest elements',
          //'airplay' => 'Airplay *',
          //'chromecast' => 'Chromecast *',
        ],
        'placeholder' => esc_html__( 'Play/pause', 'bricks' ),
        'inline'      => true,
        'small' => true,
    ];

   $this->controls['playLabel']    = [
      'label'     => esc_html__( 'Play label', 'bricks' ),
      'type'      => 'text',
      'required'  => ['controlType', '=', ['play','play-large','']],
      'placeholder' => 'Play',
      'group' => 'labels',
      'inline'    => true
    ];

   $this->controls['pauseLabel']    = [
      'label'     => esc_html__( 'Pause label', 'bricks' ),
      'type'      => 'text',
      'required'  => ['controlType', '=', ['play','play-large','']],
      'inline'    => true,
      'placeholder' => 'Pause',
      'group' => 'labels',
    ];

    $this->controls['captionsOnLabel']    = [
      'label'     => esc_html__('Captions On Label', 'bricks' ),
      'type'      => 'text',
      'required'  => ['controlType', '=', ['captions']],
      'placeholder' => 'Closed-Captions On',
      'inline'    => true,
      'group' => 'labels',
    ];

    $this->controls['captionsOffLabel']    = [
      'label'     => esc_html__('Captions Off Label', 'bricks' ),
      'type'      => 'text',
      'required'  => ['controlType', '=', ['captions']],
      'placeholder' => 'Closed-Captions Off',
      'inline'    => true,
      'group' => 'labels',
    ];

    $this->controls['downloadLabel']    = [
      'label'     => esc_html__('Download Label', 'bricks' ),
      'type'      => 'text',
      'required'  => ['controlType', '=', ['download']],
      'placeholder' => 'Download',
      'inline'    => true,
      'group' => 'labels', 
    ]; 

      $this->controls['downloadSource']    = [
          'label'     => esc_html__('Download Source', 'bricks' ),
          'description' => esc_html__('Select the source for the URL to download', 'bricks' ),
          'type'      => 'select',
          'required'  => ['controlType', '=', ['download']],
          'placeholder' => esc_html__('Media Source URL', 'bricks' ),
          'options'   => [
            'source' => esc_html__('Media Source URL', 'bricks' ),
            'download-option' => esc_html__('Download Option', 'bricks' ),
          ]
        ];

        $this->controls['downloadOptionId'] = [
          'label'     => esc_html__('Download Option ID', 'bricks' ),
          'type'      => 'text',
          'required'  => [
            ['downloadSource', '=', ['download-option']],
            ['controlType', '=', ['download']] 
          ],
          'placeholder' => '',
          'description' => esc_html__('Enter the ID of the download option (from player/playlist settings)', 'bricks' ),
        ]; 
      
        $this->controls['downloadURLAttribute'] = [
          'label'     => esc_html__('Download URL Attribute', 'bricks' ),
          'type'      => 'text',
          'required'  => [
            ['downloadSource', '=', ['data-attribute']],
            ['controlType', '=', ['download']]
          ],
          'placeholder' => 'data-your-attribute'
        ];

        $this->controls['downloadURLFileName'] = [
          'label'     => esc_html__('File Name Attribute', 'bricks' ),
          'type'      => 'text',
          'required'  => [
            ['downloadSource', '=', ['data-attribute']],
            ['controlType', '=', ['download']]
          ],
          'placeholder' => 'data-your-attribute'
        ];

    $this->controls['seekAmountBack']    = [
      'label'     => esc_html__( 'Seek Distance', 'bricks' ),
      'type'      => 'select',
      'inline'    => true,
      'options'   => [
        '-10' => '-10',
        '-15' => '-15',
        '-30' => '-30',
      ],
      'required'  => ['controlType', '=', 'seek-backward'],
      'placeholder' => '-10',
    ];

    $this->controls['seekLabelBack']    = [
      'label'     => esc_html__( 'Seek Label', 'bricks' ),
      'type'      => 'text',
      'inline'    => true,
      'required'  => ['controlType', '=', 'seek-backward'],
      'placeholder' => 'Rewind',
      'group' => 'labels',
    ];

    $this->controls['seekAmountForward']    = [
      'label'     => esc_html__( 'Seek Distance', 'bricks' ),
      'type'      => 'select',
      'inline'    => true,
      'options'   => [
        '+10' => '+10',
        '+15' => '+15',
        '+30' => '+30',
      ],
      'required'  => ['controlType', '=', 'seek-forward'],
      'placeholder' => '+10'
    ];

    $this->controls['seekLabelForward']    = [
      'label'     => esc_html__( 'Seek Label', 'bricks' ),
      'type'      => 'text',
      'inline'    => true,
      'required'  => ['controlType', '=', 'seek-forward'],
      'placeholder' => 'Forward',
      'group' => 'labels',
    ];

    $this->controls['volumeSlider' ]   = [
      'label'     => esc_html__( 'Include volume slider', 'bricks' ),
      'type'      => 'select',
      'options'   => [
        'focus' => esc_html__( 'Enable (visible on focus)', 'bricks' ),
        'visible' => esc_html__( 'Enable (always visible)', 'bricks' ),
        'disable' => esc_html__( 'Disable', 'bricks' ),
      ],
      'placeholder' => esc_html__( 'Disable', 'bricks' ),
      'required'  => ['controlType', '=', 'mute'],
    ];

    $this->controls['volumeSliderOrientation' ]   = [
      'label'     => esc_html__( 'Volume slider orientation', 'bricks' ),
      'type'      => 'select',
      'options'   => [
        'horizontal' => esc_html__( 'Horizontal', 'bricks' ),
        'vertical-above' => esc_html__( 'Vertical (above)', 'bricks' ),
        'vertical-below' => esc_html__( 'Vertical (below)', 'bricks' ),
      ],
      'placeholder' => esc_html__( 'Horizontal', 'bricks' ),
      'required'  => [
        ['controlType', '=', 'mute'], 
        ['volumeSlider', '=', ['visible','focus']],
      ],
    ];

    $this->controls['muteLabel']    = [
      'label'     => esc_html__( 'Mute Label', 'bricks' ),
      'type'      => 'text',
      'required'  => ['controlType', '=', 'mute'],
      'inline'    => true,
      'placeholder' => 'Mute',
      'group' => 'labels',
    ];

    $this->controls['unmuteLabel']    = [
      'label'     => esc_html__( 'Unmute Label', 'bricks' ),
      'type'      => 'text',
      'required'  => ['controlType', '=', 'mute'],
      'inline'    => true,
      'placeholder' => 'Unmute',
      'group' => 'labels',
    ];

    $this->controls['previousLabel']    = [
      'label'     => esc_html__( 'Previous item label', 'bricks' ),
      'type'      => 'text',
      'required'  => ['controlType', '=', 'previous'],
      'inline'    => true,
      'placeholder' => esc_html__( 'Previous' ),
      'group' => 'labels',
    ];

    $this->controls['nextLabel']    = [
      'label'     => esc_html__( 'Next item label', 'bricks' ),
      'type'      => 'text',
      'required'  => ['controlType', '=', 'next'],
      'inline'    => true,
      'placeholder' => esc_html__( 'Next' ),  
      'group' => 'labels',
    ];

    $this->controls['currentTime']    = [
      'label'     => esc_html__( 'Current time', 'bricks' ),
      'type'      => 'select',
      'options'   => [
        'enable' => esc_html__( 'Enable', 'bricks' ),
        'disable' => esc_html__( 'Disable', 'bricks' ),
        'remainder' => esc_html__( 'Show remaining time', 'bricks' ),
      ],
      'inline'    => true,
      'required'  => ['controlType', '=', 'time'],
      'placeholder' => esc_html__( 'Enable', 'bricks' ),
    ];

    $this->controls['timeDivider']    = [
      'label'     => esc_html__( 'Divider', 'bricks' ),
      'type'      => 'select',
      'inline'    => true,
      'options'   => [
        'enable' => esc_html__( 'Enable', 'bricks' ),
        'disable' => esc_html__( 'Disable', 'bricks' ),
      ],
      'required'  => ['controlType', '=', 'time'],
      'placeholder' => esc_html__( 'Enable', 'bricks' ),
    ];

    $this->controls['timeDividerText']    = [
      'label'     => esc_html__( 'Divider', 'bricks' ),
      'type'      => 'text',
      'inline'    => true,
      'required'  => [
        ['controlType', '=', 'time'],
        ['timeDivider', '!=', 'disable'],
      ],
      'placeholder' => '/',
    ];

    $this->controls['duration']    = [
      'label'     => esc_html__( 'Duration', 'bricks' ),
      'type'      => 'select',
      'inline'    => true,
      'options'   => [
        'enable' => esc_html__( 'Enable', 'bricks' ),
        'disable' => esc_html__( 'Disable', 'bricks' ),
      ],
      'required'  => ['controlType', '=', 'time'],
      'placeholder' => esc_html__( 'Enable', 'bricks' ),
    ];

    $this->controls['enterPipLabel']    = [
      'label'     => esc_html__( 'Enter PIP Label', 'bricks' ),
      'type'      => 'text',
      'inline'    => true,
      'required'  => ['controlType', '=', 'pip'],
      'placeholder' => esc_html__( 'Enter PIP' ),
      'group' => 'labels',
    ];

    $this->controls['exitPipLabel' ]   = [
      'label'     => esc_html__( 'Exit PIP Label', 'bricks' ),
      'type'      => 'text',
      'inline'    => true,
      'required'  => ['controlType', '=', 'pip'],
      'placeholder' => esc_html__( 'Exit PIP' ),
      'group' => 'labels',
    ];


    $this->controls['enterFullscreenLabel']    = [
      'label'     => esc_html__( 'Enter Fullscreen Label', 'bricks' ),
      'type'      => 'text',
      'inline'    => true,
      'required'  => ['controlType', '=', 'fullscreen'],
      'placeholder' => esc_html__( 'Enter Fullscreen' ),
      'group' => 'labels',
    ];

    $this->controls['exitFullscreenLabel']    = [
      'label'     => esc_html__( 'Exit Fullscreen Label', 'bricks' ),
      'type'      => 'text',
      'inline'    => true,
      'required'  => ['controlType', '=', 'fullscreen'],
      'placeholder' => esc_html__( 'Exit Fullscreen' ),
      'group' => 'labels',
    ];

    $this->controls['customLabel']    = [
      'label'     => esc_html__( 'Tooltip Label', 'bricks' ),
      'type'      => 'text',
      'required'  => ['controlType', '=', 'custom'],
      'inline'    => true,
      'placeholder' => esc_html__( 'Custom' ),
      'group' => 'labels',
    ];

    $this->controls['chaptersLabel']    = [
      'label'     => esc_html__( 'Chapters Label', 'bricks' ),
      'type'      => 'text',
      'required'  => ['controlType', '=', 'chapters'],
      'inline'    => true,
      'group' => 'labels',
      'placeholder' => esc_html__( 'Chapters' )
    ];

    $this->controls['settingsLabel']    = [
      'label'     => esc_html__( 'Settings Label', 'bricks' ),
      'type'      => 'text',
      'required'  => ['controlType', '=', 'settings'],
      'inline'    => true,
      'group' => 'labels',
      'placeholder' => esc_html__( 'Settings' )
    ];

    $this->controls['speedLabel']    = [
      'label'     => esc_html__( 'Playback Label', 'bricks' ),
      'type'      => 'text',
      'required'  => ['controlType', '=', 'settings'],
      'inline'    => true,
      'group' => 'labels',
      'placeholder' => esc_html__( 'Playback' )
    ];

    $this->controls['qualityLabel' ]   = [
      'label'     => esc_html__( 'Quality Setting Label', 'bricks' ),
      'type'      => 'text',
      'required'  => ['controlType', '=', 'settings'],
      'inline'    => true,
      'group' => 'labels',
      'placeholder' => esc_html__( 'Quality' )
    ];

    $this->controls['speedSettingLabel' ]   = [
      'label'     => esc_html__( 'Speed Setting Label', 'bricks' ),
      'type'      => 'text',
      'required'  => ['controlType', '=', 'settings'],
      'inline'    => true,
      'group' => 'labels',
      'placeholder' => 'Speed'
    ];

    /*
    $this->controls['audioLabel']    = [
      'label'     => esc_html__( 'Audio Label', 'bricks' ),
      'type'      => 'text',
      'required'  => ['controlType', '=', 'settings'],
      'inline'    => true,
      'placeholder' => esc_html__( 'Audio' )
    ];
    

    $this->controls['boostLabel']    = [
      'label'     => esc_html__( 'Boost Label', 'bricks' ),
      'type'      => 'text',
      'required'  => ['controlType', '=', 'settings'],
      'inline'    => true,
      'placeholder' => esc_html__( 'Boost' )
    ];
    */

    $this->controls['accessibilityLabel']    = [
      'label'     => esc_html__( 'Accessibility Label', 'bricks' ),
      'type'      => 'text',
      'required'  => ['controlType', '=', 'settings'],
      'inline'    => true,
      'group' => 'labels',
      'placeholder' => esc_html__( 'Accessibility' )
    ];

    $this->controls['announcementsLabel']    = [
      'label'     => esc_html__( 'Announcements Label', 'bricks' ),
      'type'      => 'text',
      'required'  => ['controlType', '=', 'settings'],
      'inline'    => true,
      'group' => 'labels',
      'placeholder' => esc_html__( 'Announcements' )
    ];

    $this->controls['captionsLabel']    = [
      'label'     => esc_html__( 'Captions Label', 'bricks' ),
      'type'      => 'text',
      'required'  => ['controlType', '=', 'settings'],
      'inline'    => true,
      'group' => 'labels',
      'placeholder' => esc_html__( 'Captions' )
    ];

    $this->controls['captionsOffHint'] = [
      'label'     => esc_html__( 'Captions "Off" Option Label', 'bricks' ),
      'type'      => 'text',
      'required'  => ['controlType', '=', 'settings'],
      'inline'    => true,
      'group' => 'labels',
      'placeholder' => esc_html__( 'Off' )
    ];

    $this->controls['loopLabel'] = [
      'label'     => esc_html__( 'Loop Label', 'bricks' ),
      'type'      => 'text',
      'required'  => ['controlType', '=', 'settings'],
      'inline'    => true,
      'group' => 'labels',
      'placeholder' => esc_html__( 'Loop' )
    ];

    $this->controls['settingsPlacement']    = [
      'label'     => esc_html__( 'Menu Placement', 'bricks' ),
      'type'      => 'select',
      'options' => $placementOptions,
      'required'  => ['controlType', '=', ['settings','chapters']],
      'inline'    => true,
      'placeholder' => esc_html__( 'Top End', 'bricks' )
    ];

    $this->controls['menuXOffset'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'small' => true,
      'label'  => esc_html__( 'Menu X Offset', 'extras' ),
      'placeholder' => '0',
      'required'  => ['controlType', '=', ['settings','chapters']],
      ];
  
      $this->controls['menuYOffset'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'small' => true,
      'label'  => esc_html__( 'Menu Y Offset', 'extras' ),
      'required'  => ['controlType', '=', ['settings','chapters']],
      'placeholder' => '20',
      ];


    $this->controls['controlText'] = [
        'tab' => 'content',
        'label' => esc_html__( 'Text', 'bricks' ),
        'type' => 'text',
        'default' => 'Text here',
        'required' => ['controlType', '=', 'custom-text'],
      ];

      $this->controls['artistPrefix'] = [
        'tab' => 'content',
        'label' => esc_html__( 'Prefix', 'bricks' ),
        'type' => 'text',
        'inline' => true,
        'required' => ['controlType', '=', 'artist'],
      ];

      $this->controls['artistSuffix'] = [
        'tab' => 'content',
        'label' => esc_html__( 'Suffix', 'bricks' ),
        'type' => 'text',
        'inline' => true,
        'required' => ['controlType', '=', 'artist'],
      ];

      $this->controls['gap'] = [
        'tab' => 'content',
        'label' => esc_html__( 'Gap', 'bricks' ),
        'type' => 'number',
        'units' => true,
        'small' => true,
        'css' => [
          [
            'property' => 'gap',
            'selector' => '',
          ],
        ],
        'required' => ['controlType', '=', 'artist'],
      ];


    $this->controls['controlHide'] = [
        'label'    => esc_html__( 'Visibility (for video only)', 'bricks' ),
        'type'        => 'select',
        //'rerender' => true,
        'options'   => [
            'default' => esc_html__( 'Default', 'bricks' ),
            'first'  => esc_html__( 'Only visible before first play', 'bricks' ),
            'play'  => esc_html__( 'Only visible after first play', 'bricks' ),
            'loaded' => esc_html__( 'Only visible when player ready to play', 'bricks' ),
            'before_loaded' => esc_html__( 'Only visible before player ready to play', 'bricks' ),
          ],
        'placeholder' => esc_html__( 'Default', 'bricks' ),
    ];




    $this->controls['enableWaveform'] = [
      'tab'      => 'content',
      'label'    => esc_html__('Enable waveform (audio player)', 'bricks'),
      'type'     => 'checkbox',
      'inline'   => true,
      'required' => ['controlType', '=', ['time-slider']],
    ];

    $this->controls['waveformHeight'] = [
      'tab'      => 'content',
      'label'    => esc_html__('Height', 'bricks'),
      'type'     => 'number',
      'unit'     => false,
      'inline'   => true,
      'small'    => true,
      'placeholder' => '26',
      'required' => [
        ['controlType', '=', ['time-slider']],
        ['enableWaveform', '=', true],
      ],
    ];
    
    $this->controls['waveformBarWidth'] = [
      'tab'      => 'content',
      'label'    => esc_html__('Bar width', 'bricks'),
      'type'     => 'number',
      'unit'     => false,
      'inline'   => true,
      'small'    => true,
      'placeholder' => '2',
      'required' => [
        ['controlType', '=', ['time-slider']],
        ['enableWaveform', '=', true],
      ],
    ];
    
    $this->controls['waveformBarGap'] = [
      'tab'      => 'content',
      'label'    => esc_html__('Bar gap', 'bricks'),
      'type'     => 'number',
      'unit'     => false,
      'inline'   => true,
      'small'    => true,
      'placeholder' => '1',
      'required' => [
        ['controlType', '=', ['time-slider']],
        ['enableWaveform', '=', true],
      ],
    ];
    
    $this->controls['waveformColor'] = [
      'tab'      => 'content',
      'label'    => esc_html__('Waveform color', 'bricks'),
      'type'     => 'color',
      'inline'   => true,
      'required' => [
        ['controlType', '=', ['time-slider']],
        ['enableWaveform', '=', true],
      ],
    ];
    
    $this->controls['waveformPlayedColor'] = [
      'tab'      => 'content',
      'label'    => esc_html__('Played color', 'bricks'),
      'type'     => 'color',
      'inline'   => true,
      'required' => [
        ['controlType', '=', ['time-slider']],
        ['enableWaveform', '=', true],
      ],
    ];



    $buttonControls = ['custom-text','time-slider','time','title','artist','nest','live-button','chapter-title','poster-image'];

    $buttonsWithOutActiveState = [
      'custom-text',
      'time-slider',
      'time',
      'title',
      'artist',
      'nest',
      'seek-backward',
      'seek-forward',
      'live-button',
      'chapter-title',
      'poster-image'
    ];


    $this->controls['controlsStyleSep'] = [
        'group' => 'styleControls',
        'label'    => esc_html__( 'Button Controls', 'bricks' ),
        'type'        => 'separator',
        'required' => ['controlType', '!=', $buttonControls],
    ];
  
  
      $this->controls['iconSize'] = [
        'tab'    => 'content',
        'type'   => 'number',
        'units'   => true,
        'group' => 'styleControls',
        'label'  => esc_html__( 'Icon size', 'extras' ),
        'css'    => [
          [
            'property' => '--media-button-icon-size',
            'selector' => '',
          ],
          [
            'property' => '--media-button-icon-size',
            'selector' => '&.xmp-button-large',
          ],
          
        ],
        'placeholder' => '24px',
        'required' => ['controlType', '!=', $buttonControls],
      ];

      $this->controls['iconColor'] = [
        'tab'    => 'content',
        'type'   => 'color',
        'group' => 'styleControls',
        'label'  => esc_html__( 'Color', 'extras' ),
        'css'    => [
          [
            'property' => 'color',
            'selector' => '',
          ],
        ],
        'placeholder' => '24px',
        'required' => ['controlType', '!=', $buttonControls],
      ];
  
      $this->controls['controlSize'] = [
        'tab'    => 'content',
        'type'   => 'number',
        'units'   => true,
        'group' => 'styleControls',
        'label'  => esc_html__( 'Control size', 'extras' ),
        'css'    => [
          [
            'property' => '--media-button-size',
            'selector' => '',
          ],
          [
            'property' => '--media-button-size',
            'selector' => '&.xmp-button-large',
          ],
        ],
        'placeholder' => '40px',
        'required' => ['controlType', '!=', $buttonControls],
      ];
  
      $this->controls['controlBg'] = [
        'tab'    => 'content',
        'type'   => 'color',
        'group' => 'styleControls',
        'label'  => esc_html__( 'Background', 'extras' ),
        'css'    => [
          [
            'property' => 'background-color',
            'selector' => '&.xmp-button',
          ],
          [
            'property' => 'background-color',
            'selector' => '.xmp-menu-button',
          ],
        ],
        'required' => ['controlType', '!=', $buttonControls],
      ];
  
      $this->controls['controlBorder'] = [
        'tab'    => 'content',
        'type'   => 'border',
        'group' => 'styleControls',
        'label'  => esc_html__( 'Border', 'extras' ),
        'css'    => [
          [
            'property' => 'border',
            'selector' => '&.xmp-button',
          ],
          [
            'property' => 'border',
            'selector' => '.xmp-menu-button',
          ],
        ],
        'required' => ['controlType', '!=', $buttonControls],
      ];

      $this->controls['controlTypography'] = [
        'tab'    => 'content',
        'type'   => 'typography',
        'group' => 'styleControls',
        'label'  => esc_html__( 'Typography', 'extras' ),
        'css'    => [
          [
            'property' => 'font',
            'selector' => '& > *',
          ],
        ],
        'required' => ['controlType', '!=', $buttonControls],
      ];

      

      $this->controls['controlsStyleActiveSep'] = [
        'group' => 'styleControls',
        'label'    => esc_html__( 'Button Controls (active)', 'bricks' ),
        'type'        => 'separator',
        'required' => ['controlType', '!=', $buttonsWithOutActiveState],
      ];



      $this->controls['iconSizeActive'] = [
        'tab'    => 'content',
        'type'   => 'number',
        'units'   => true,
        'group' => 'styleControls',
        'label'  => esc_html__( 'Icon size', 'extras' ),
        'css'    => [
          [
            'property' => '--media-button-icon-size',
            'selector' => '&[data-pressed]',
          ],
          [
            'property' => '--media-button-icon-size',
            'selector' => '&[data-pressed].xmp-button-large',
          ],
          
        ],
        'placeholder' => '24px',
        'required' => ['controlType', '!=', $buttonsWithOutActiveState],
      ];

      $this->controls['iconColorActive'] = [
        'tab'    => 'content',
        'type'   => 'color',
        'group' => 'styleControls',
        'label'  => esc_html__( 'Color', 'extras' ),
        'css'    => [
          [
            'property' => 'color',
            'selector' => '&[data-pressed].xmp-button:not(.xmp-settings-icon)',
          ],
          [
            'property' => 'color',
            'selector' => '[data-pressed].xmp-button',
          ],
        ],
        'placeholder' => '24px',
        'required' => ['controlType', '!=', $buttonsWithOutActiveState],
      ];
  
      $this->controls['controlSizeActive'] = [
        'tab'    => 'content',
        'type'   => 'number',
        'units'   => true,
        'group' => 'styleControls',
        'label'  => esc_html__( 'Control size', 'extras' ),
        'css'    => [
          [
            'property' => '--media-button-size',
            'selector' => '&[data-pressed]',
          ],
          [
            'property' => '--media-button-size',
            'selector' => '&[data-pressed].xmp-button-large',
          ],
          [
            'property' => '--media-button-size',
            'selector' => '[data-pressed]',
          ],
        ],
        'placeholder' => '40px',
        'required' => ['controlType', '!=', $buttonsWithOutActiveState],
      ];
  
      $this->controls['controlBgActive'] = [
        'tab'    => 'content',
        'type'   => 'color',
        'group' => 'styleControls',
        'label'  => esc_html__( 'Background', 'extras' ),
        'css'    => [
          [
            'property' => 'background-color',
            'selector' => '&[data-pressed].xmp-button:not(.xmp-settings-icon)',
          ],
          [
            'property' => 'background-color',
            'selector' => '[data-pressed].xmp-button',
          ],
        ],
        'required' => ['controlType', '!=', $buttonsWithOutActiveState],
      ];
  
      $this->controls['controlBorderActive'] = [
        'tab'    => 'content',
        'type'   => 'border',
        'group' => 'styleControls',
        'label'  => esc_html__( 'Border', 'extras' ),
        'css'    => [
          [
            'property' => 'border',
            'selector' => '&[data-pressed].xmp-button:not(.xmp-settings-icon)',
          ],
          [
            'property' => 'border',
            'selector' => '[data-pressed].xmp-button',
          ],
        ],
        'required' => ['controlType', '!=', $buttonsWithOutActiveState],
      ];

      $this->controls['controlTypographyActive'] = [
        'tab'    => 'content',
        'type'   => 'typography',
        'group' => 'styleControls',
        'label'  => esc_html__( 'Typography', 'extras' ),
        'css'    => [
          [
            'property' => 'font',
            'selector' => '&[data-pressed] > *',
          ],
          [
            'property' => 'font',
            'selector' => '[data-pressed] > *',
          ],
          [
            'property' => 'font',
            'selector' => '&[data-pressed] .xmp-time',
          ],
        ],
        'required' => ['controlType', '!=', $buttonsWithOutActiveState],
      ];

      /* Live button */

      $this->controls['liveButtonSep'] = [
        'group' => 'styleControls',
        'label'    => esc_html__( 'Live button', 'bricks' ),
        'type'        => 'separator',
        'required' => ['controlType', '=', ['live-button']],
      ];
      
      $liveButton = '.xmp-live-button-text';

      $this->controls['liveButtonBg'] = [
        'tab'    => 'content',
        'type'   => 'color',
        'group' => 'styleControls',
        'label'  => esc_html__( 'Background', 'extras' ),
        'required' => ['controlType', '=', ['live-button']],
        'css'    => [
          [
            'property' => 'background-color',
            'selector' => $liveButton,
          ],
        ],
      ];

      $this->controls['liveButtonBorder'] = [
        'tab'    => 'content',
        'type'   => 'border',
        'group' => 'styleControls',
        'label'  => esc_html__( 'Border', 'extras' ),
        'required' => ['controlType', '=', ['live-button']],
        'css'    => [
          [
            'property' => 'border',
            'selector' => $liveButton
          ],
        ],
      ];
      

      $this->controls['liveButtonTypography'] = [
        'tab'    => 'content',
        'type'   => 'typography',
        'group' => 'styleControls',
        'label'  => esc_html__( 'Typography', 'extras' ),
        'required' => ['controlType', '=', ['live-button']],
        'css'    => [
          [
            'property' => 'font',
            'selector' => $liveButton
          ],
        ],
      ];

      $this->controls['liveButtonPadding'] = [
        'tab'    => 'content',
        'type'   => 'dimensions',
        'group' => 'styleControls',
        'label'  => esc_html__( 'Padding', 'extras' ),
        'required' => ['controlType', '=', ['live-button']],
        'css'    => [
          [
            'property' => 'padding',
            'selector' => $liveButton
          ],
        ],
      ];

      /* time slider */

      $timeSliderSelector = '.xmp-time-slider.xmp-slider';

    $this->controls['sliderHeight'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'group' => 'styleControls',
      'label'  => esc_html__( 'Control height', 'extras' ),
      'css'    => [
        [
          'property' => '--media-slider-height',
          'selector' => $timeSliderSelector
        ],
      ],
      'required' => ['controlType', '=', ['time-slider']],
      'placeholder' => '40px'
    ];

    $this->controls['trackHeight'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'group' => 'styleControls',
      'label'  => esc_html__( 'Slider Track height', 'extras' ),
      'css'    => [
        [
          'property' => '--track-height',
          'selector' => $timeSliderSelector
        ],
      ],
      'required' => ['controlType', '=', ['time-slider']],
      'placeholder' => '5px'
    ];

    $this->controls['trackFocusHeight'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'group' => 'styleControls',
      'label'  => esc_html__( 'Slider Track Focus height', 'extras' ),
      'css'    => [
        [
          'property' => '--track-focus-height',
          'selector' => $timeSliderSelector
        ],
      ],
      'required' => ['controlType', '=', ['time-slider']],
      'placeholder' => '5px'
    ];


    $this->controls['timeSliderTrackSep'] = [
      'group' => 'styleControls',
			'type'        => 'separator',
      'required' => ['controlType', '=', ['time-slider']],
		];
    

    $this->controls['trackFillBackground'] = [
      'tab'    => 'content',
      'type'   => 'color',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Track progress color', 'extras' ),
      'css'    => [
        [
          'property' => '--media-slider-track-fill-bg',
          'selector' => $timeSliderSelector,
        ],
      ],
      'required' => ['controlType', '=', ['time-slider']],
    ];

    $this->controls['trackProgressBackground'] = [
      'tab'    => 'content',
      'type'   => 'color',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Track buffered color', 'extras' ),
      'css'    => [
        [
          'property' => '--media-slider-track-progress-bg',
          'selector' => $timeSliderSelector,
        ],
      ],
      'required' => ['controlType', '=', ['time-slider']],
    ];

    $this->controls['trackBackground'] = [
      'tab'    => 'content',
      'type'   => 'color',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Track background color', 'extras' ),
      'css'    => [
        [
          'property' => '--media-slider-track-bg',
          'selector' => $timeSliderSelector,
        ],
      ],
      'required' => ['controlType', '=', ['time-slider']],
    ];

    $this->controls['trackLiveBackground'] = [
      'tab'    => 'content',
      'type'   => 'color',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Track background (live)', 'extras' ),
      'css'    => [
        [
          'property' => '--media-slider-track-fill-live-bg',
          'selector' => $timeSliderSelector,
        ],
      ],
      'required' => ['controlType', '=', ['time-slider']],
    ];

    $this->controls['timeSliderThumbSep'] = [
      'group' => 'styleControls',
			'type'        => 'separator',
      'required' => ['controlType', '=', ['time-slider']],
		];

    $this->controls['thumbColor'] = [
      'tab'    => 'content',
      'type'   => 'color',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Thumb color', 'extras' ),
      'css'    => [
        [
          'property' => '--media-slider-thumb-bg',
          'selector' => $timeSliderSelector,
        ],
      ],
      'required' => ['controlType', '=', ['time-slider']],
    ];

    $this->controls['thumbSize'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'group' => 'styleControls',
      'label'  => esc_html__( 'Thumb size', 'extras' ),
      'css'    => [
        [
          'property' => '--thumb-size',
          'selector' => $timeSliderSelector
        ],
      ],
      'required' => ['controlType', '=', ['time-slider']],
      'placeholder' => '15px'
    ];

    $this->controls['thumbBorder'] = [
      'tab'    => 'content',
      'type'   => 'border',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Thumb border', 'extras' ),
      'css'    => [
        [
          'property' => 'border',
          'selector' => '.xmp-slider-thumb'
        ],
      ],
      'required' => ['controlType', '=', ['time-slider']],
    ];

    $this->controls['focusThumbSize'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'group' => 'styleControls',
      'label'  => esc_html__( 'Thumb size (focus)', 'extras' ),
      'css'    => [
        [
          'property' => '--thumb-focus-size',
          'selector' => $timeSliderSelector
        ],
      ],
      'required' => ['controlType', '=', ['time-slider']],
      'placeholder' => '17px'
    ];

    $this->controls['sliderValueSep'] = [
      'group' => 'styleControls',
			'label'    => esc_html__( 'Slider time value', 'bricks' ),
			'type'        => 'separator',
      'required' => ['controlType', '=', ['time-slider']],
		];
    
    $sliderValueSelector = '.xmp-time-slider .xmp-slider-value';

    $this->controls['sliderValueDisplay'] = [
      'tab'    => 'content',
      'type'   => 'select',
      'inline' => true,
      'options' => [
        'block' => 'Block',
        'none' => 'None',
      ],
      'group' => 'styleControls',
      'placeholder' => 'Block',
      'label'  => esc_html__( 'Value display', 'extras' ),
      'css'    => [
        [
          'property' => 'display',
          'selector' => '.xmp-slider-value',
        ],
      ],
      'required' => ['controlType', '=', ['time-slider']],
    ];


    $this->controls['sliderOffset'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units' => true,
      'group' => 'styleControls',
      'label'  => esc_html__( 'Value offset', 'extras' ),
      'css'    => [
        [
          'property' => '--media-slider-preview-offset',
          'selector' => $sliderValueSelector
        ],
      ],
      'required' => ['controlType', '=', ['time-slider']],
    ];

    $this->controls['sliderValueBg'] = [
      'tab'    => 'content',
      'type'   => 'color',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Background', 'extras' ),
      'css'    => [
        [
          'property' => '--media-slider-value-bg',
          'selector' => $sliderValueSelector,
        ],
      ],
      'required' => ['controlType', '=', ['time-slider']],
    ];

    $this->controls['sliderValueBorder'] = [
      'tab'    => 'content',
      'type'   => 'border',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Border', 'extras' ),
      'css'    => [
        [
          'property' => 'border',
          'selector' => $sliderValueSelector
        ],
      ],
      'required' => ['controlType', '=', ['time-slider']],
    ];
    

    $this->controls['sliderValueTypography'] = [
      'tab'    => 'content',
      'type'   => 'typography',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Typography', 'extras' ),
      'css'    => [
        [
          'property' => 'font',
          'selector' => $sliderValueSelector
        ],
      ],
      'required' => ['controlType', '=', ['time-slider']],
    ];

    $this->controls['sliderValuePadding'] = [
      'tab'    => 'content',
      'type'   => 'dimensions',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Padding', 'extras' ),
      'css'    => [
        [
          'property' => 'padding',
          'selector' => $sliderValueSelector
        ],
      ],
      'required' => ['controlType', '=', ['time-slider']],
    ];

    /* slider chapter title*/

    $this->controls['sliderChapterTitleSep'] = [
      'group' => 'styleControls',
			'label'    => esc_html__( 'Slider chapter value', 'bricks' ),
			'type'        => 'separator',
      'required' => ['controlType', '=', ['time-slider']],
		];
    
    $sliderChapterTitle = '.xmp-slider-chapter-title';

    $this->controls['sliderChapterDisplay'] = [
      'tab'    => 'content',
      'type'   => 'select',
      'inline' => true,
      'options' => [
        'block' => 'Block',
        'none' => 'None',
      ],
      'group' => 'styleControls',
      'placeholder' => 'Block',
      'label'  => esc_html__( 'Chapter display', 'extras' ),
      'css'    => [
        [
          'property' => 'display',
          'selector' => '.xmp-slider-chapter-title',
        ],
      ],
      'required' => ['controlType', '=', ['time-slider']],
    ];

    $this->controls['sliderChapterTitleBg'] = [
      'tab'    => 'content',
      'type'   => 'color',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Background', 'extras' ),
      'css'    => [
        [
          'property' => 'background-color',
          'selector' => $sliderChapterTitle,
        ],
      ],
      'required' => ['controlType', '=', ['time-slider']],
    ];

    $this->controls['sliderChapterTitleBorder'] = [
      'tab'    => 'content',
      'type'   => 'border',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Border', 'extras' ),
      'css'    => [
        [
          'property' => 'border',
          'selector' => $sliderChapterTitle
        ],
      ],
      'required' => ['controlType', '=', ['time-slider']],
    ];
    

    $this->controls['sliderChapterTitleTypography'] = [
      'tab'    => 'content',
      'type'   => 'typography',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Typography', 'extras' ),
      'css'    => [
        [
          'property' => 'font',
          'selector' => $sliderChapterTitle
        ],
      ],
      'required' => ['controlType', '=', ['time-slider']],
    ];

    $this->controls['sliderChapterTitlePadding'] = [
      'tab'    => 'content',
      'type'   => 'dimensions',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Padding', 'extras' ),
      'css'    => [
        [
          'property' => 'padding',
          'selector' => $sliderChapterTitle
        ],
      ],
      'required' => ['controlType', '=', ['time-slider']],
    ];



    /* volume slider */

    $this->controls['volumeSliderStyleSep'] = [
      'group' => 'styleControls',
			'label'    => esc_html__( 'Volume slider', 'bricks' ),
			'type'        => 'separator',
      'required' => ['controlType', '=', ['mute']],
		];

    $volumeSliderSelector = '.xmp-volume-slider.xmp-slider';

    $this->controls['sliderHeightVolume'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'group' => 'styleControls',
      'label'  => esc_html__( 'Control height', 'extras' ),
      'css'    => [
        [
          'property' => '--media-slider-height',
          'selector' => $volumeSliderSelector
        ],
      ],
      'placeholder' => '40px',
      'required' => ['controlType', '=', ['mute']],
    ];

    $this->controls['trackHeightVolume'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'group' => 'styleControls',
      'label'  => esc_html__( 'Slider Track height', 'extras' ),
      'css'    => [
        [
          'property' => '--track-height',
          'selector' => $volumeSliderSelector
        ],
      ],
      'placeholder' => '5px',
      'required' => ['controlType', '=', ['mute']],
    ];

    $this->controls['trackWidthVolume'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'group' => 'styleControls',
      'label'  => esc_html__( 'Max width', 'extras' ),
      'css'    => [
        [
          'property' => 'max-width',
          'selector' => '[data-hocus] + .xmp-volume-slider'
        ],
        [
          'property' => 'max-width',
          'selector' => '.xmp-volume-slider[data-active]'
        ],
        [
          'property' => 'max-width',
          'selector' => '.xmp-slider.xmp-visible-volume'
        ],
      ],
      'placeholder' => '80px',
      'required' => ['controlType', '=', ['mute']],
    ];

    

    $this->controls['timeSliderTrackSep'] = [
      'group' => 'styleControls',
			'type'        => 'separator',
      'required' => ['controlType', '=', ['mute']],
		];


    $this->controls['trackFillBackgroundVolume'] = [
      'tab'    => 'content',
      'type'   => 'color',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Track progress color', 'extras' ),
      'css'    => [
        [
          'property' => '--media-slider-track-fill-bg',
          'selector' => $volumeSliderSelector
        ],
      ],
      'required' => ['controlType', '=', ['mute']],
    ];

    $this->controls['trackProgressBackgroundVolume'] = [
      'tab'    => 'content',
      'type'   => 'color',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Track buffered color', 'extras' ),
      'css'    => [
        [
          'property' => '--media-slider-track-progress-bg',
          'selector' => $volumeSliderSelector
        ],
      ],
      'required' => ['controlType', '=', ['mute']],
    ];

    $this->controls['trackBackgroundVolume'] = [
      'tab'    => 'content',
      'type'   => 'color',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Track background color', 'extras' ),
      'css'    => [
        [
          'property' => '--media-slider-track-bg',
          'selector' => $volumeSliderSelector,
        ],
      ],
      'required' => ['controlType', '=', ['mute']],
    ];

    $this->controls['timeSliderThumbSepVolume'] = [
      'group' => 'styleControls',
			'type'        => 'separator',
      'required' => ['controlType', '=', ['mute']],
		];

    $this->controls['thumbColorVolume'] = [
      'tab'    => 'content',
      'type'   => 'color',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Thumb color', 'extras' ),
      'css'    => [
        [
          'property' => '--media-slider-thumb-bg',
          'selector' => $volumeSliderSelector
        ],
      ],
      'required' => ['controlType', '=', ['mute']],
    ];

    $this->controls['thumbSizeVolume'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'group' => 'styleControls',
      'label'  => esc_html__( 'Thumb size', 'extras' ),
      'css'    => [
        [
          'property' => '--thumb-size',
          'selector' => $volumeSliderSelector
        ],
      ],
      'placeholder' => '15px',
      'required' => ['controlType', '=', ['mute']],
    ];

    $this->controls['thumbBorderVolume'] = [
      'tab'    => 'content',
      'type'   => 'border',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Thumb border', 'extras' ),
      'css'    => [
        [
          'property' => 'border',
          'selector' => '.xmp-volume-slider .xmp-slider-thumb'
        ],
      ],
      'required' => ['controlType', '=', ['mute']],
    ];

    $this->controls['focusThumbSizeVolume'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'group' => 'styleControls',
      'label'  => esc_html__( 'Thumb size (focus)', 'extras' ),
      'css'    => [
        [
          'property' => '--thumb-focus-size',
          'selector' => $volumeSliderSelector
        ],
      ],
      'placeholder' => '17px',
      'required' => ['controlType', '=', ['mute']],
    ];

    $this->controls['sliderValueSepVolume'] = [
      'group' => 'styleControls',
			'label'    => esc_html__( 'Volume time value', 'bricks' ),
			'type'        => 'separator',
      'required' => ['controlType', '=', ['mute']],
		];
    
    $volumeValueSelector = '.xmp-volume-slider .xmp-slider-value';

    
    $this->controls['volumeValueOffset'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units' => true,
      'group' => 'styleControls',
      'label'  => esc_html__( 'Volume time value offset', 'extras' ),
      'css'    => [
        [
          'property' => '--media-slider-preview-offset',
          'selector' => '.xmp-volume-slider',
        ],
      ],
      'required' => ['controlType', '=', ['mute']],
    ];

    $this->controls['sliderValueBgVolume'] = [
      'tab'    => 'content',
      'type'   => 'color',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Background', 'extras' ),
      'css'    => [
        [
          'property' => '--media-slider-value-bg',
          'selector' => $volumeValueSelector,
        ],
      ],
      'required' => ['controlType', '=', ['mute']],
    ];

    $this->controls['sliderValueBorderVolume'] = [
      'tab'    => 'content',
      'type'   => 'border',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Border', 'extras' ),
      'css'    => [
        [
          'property' => 'border',
          'selector' => $volumeValueSelector
        ],
      ],
      'required' => ['controlType', '=', ['mute']],
    ];
    

    $this->controls['sliderValueTypographyVolume'] = [
      'tab'    => 'content',
      'type'   => 'typography',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Typography', 'extras' ),
      'css'    => [
        [
          'property' => 'font',
          'selector' => $volumeValueSelector
        ],
      ],
      'required' => ['controlType', '=', ['mute']],
    ];

    $this->controls['sliderValuePaddingVolume'] = [
      'tab'    => 'content',
      'type'   => 'dimensions',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Padding', 'extras' ),
      'css'    => [
        [
          'property' => 'padding',
          'selector' => $volumeValueSelector
        ],
      ],
      'required' => ['controlType', '=', ['mute']],
    ];



    /* settings menu */


    $settingsMenuSelector = ".xmp-menu-items:not([data-submenu])";
    $settingsMenuItemSelector = ".xmp-menu-items [role=menuitemradio]";
    $settingsMenuItemRadioSelector = ".xmp-menu-items [role=menuitem]";

    $this->controls['settingsMenuSep'] = [
      'required' => ['controlType', '=', ['settings','chapters']],
      'group' => 'styleControls',
			'label'    => esc_html__( 'Settings Menu', 'bricks' ),
			'type'        => 'separator',
      
		];

    $this->controls['menuBg'] = [
      'required' => ['controlType', '=', ['settings','chapters']],
      'tab'    => 'content',
      'type'   => 'color',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Background', 'extras' ),
      'css'    => [
        [
          'property' => 'background-color',
          'selector' => $settingsMenuSelector,
        ],
      ],
      
    ];

    $this->controls['menuBorder'] = [
      'required' => ['controlType', '=', ['settings','chapters']],
      'tab'    => 'content',
      'type'   => 'border',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Border', 'extras' ),
      'css'    => [
        [
          'property' => 'border',
          'selector' => $settingsMenuSelector,
        ],
      ],
      
    ];

    $this->controls['menuPadding'] = [
      'required' => ['controlType', '=', ['settings','chapters']],
      'tab'    => 'content',
      'type'   => 'dimensions',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Padding', 'extras' ),
      'css'    => [
        [
          'property' => 'padding',
          'selector' => $settingsMenuSelector,
        ],
      ],
      
    ];

    $this->controls['menuMinWidth'] = [
      'required' => ['controlType', '=', ['settings','chapters']],
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'group' => 'styleControls',
      'label'  => esc_html__( 'Min width', 'extras' ),
      'css'    => [
        [
          'property' => '--media-menu-min-width',
          'selector' => '.xmp-menu-items',
        ],
      ],
      'placeholder' => '220px',
      
    ];
    
    

    $this->controls['settingsMenuItemsSep'] = [
      'required' => ['controlType', '=', ['chapters']],
      'group' => 'styleControls',
			'label'    => esc_html__( 'Settings Menu Items', 'bricks' ),
			'type'        => 'separator',
      
		];

    

    

    
    $this->controls['menuItemBg'] = [
      'required' => ['controlType', '=', ['settings','chapters']],
      'tab'    => 'content',
      'type'   => 'color',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Background', 'extras' ),
      'css'    => [
        [
          'property' => 'background-color',
          'selector' => $settingsMenuItemSelector,
        ],
        [
          'property' => 'background-color',
          'selector' => $settingsMenuItemRadioSelector,
        ],
      ],
      
    ];

   

    $this->controls['menuItemTypography'] = [
      'required' => ['controlType', '=', ['settings']],
      'tab'    => 'content',
      'type'   => 'typography',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Setting label Typography', 'extras' ),
      'css'    => [
        [
          'property' => 'font',
          'selector' => $settingsMenuItemSelector,
        ],
        [
          'property' => 'font',
          'selector' => $settingsMenuItemRadioSelector,
        ],
      ],
      
    ];

    $this->controls['menuItemHitTypography'] = [
      'required' => ['controlType', '=', ['settings']],
      'tab'    => 'content',
      'type'   => 'typography',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Setting hint Typography', 'extras' ),
      'css'    => [
        [
          'property' => 'font',
          'selector' => '.xmp-menu-button-hint',
        ],
      ],
      
    ];

    $this->controls['menuItemBorder'] = [
      'required' => ['controlType', '=', ['settings','chapters']],
      'tab'    => 'content',
      'type'   => 'border',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Border', 'extras' ),
      'css'    => [
        [
          'property' => 'border',
          'selector' => $settingsMenuItemSelector,
        ],
        [
          'property' => 'border',
          'selector' => $settingsMenuItemRadioSelector,
        ],
      ],
      
    ];
    

    /*
    $this->controls['menuItemsGap'] = [
      'required' => ['controlType', '=', ['settings','chapters']],
      'tab'    => 'content',
      'units'   => true,
      'type'   => 'number',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Items Gap', 'extras' ),
      'css'    => [
        [
          'property' => 'gap',
          'selector' => '.xmp-menu-items:not(.xmp-menu-items .xmp-menu-items)'
        ],
        [
          'property' => 'gap',
          'selector' => '.xmp-radio-group'
        ],
      ],
      
    ];

    

    $this->controls['menuItemPadding'] = [
      'required' => ['controlType', '=', ['settings','chapters']],
      'tab'    => 'content',
      'type'   => 'dimensions',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Item Padding', 'extras' ),
      'css'    => [
        [
          'property' => 'padding',
          'selector' => '.xmp-menu-items [role=menuitem]',
        ],
        [
          'property' => 'padding',
          'selector' => '.xmp-menu-items [role=menuitemradio]',
        ],
      ],
      
    ];
    */


   

    $this->controls['chapterItemsSep'] = [
      'required' => ['controlType', '=', ['chapters']],
      'group' => 'styleControls',
			'label'    => esc_html__( 'Chaper Menu Items', 'bricks' ),
			'type'        => 'separator',
		];



    $this->controls['chapterLabel'] = [
      'required' => ['controlType', '=', ['chapters']],
      'tab'    => 'content',
      'type'   => 'typography',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Chapter label typography', 'extras' ),
      'css'    => [
        [
          'property' => 'font',
          'selector' => '.xmp-chapter-radio-label',
        ],
      ],
      
    ];

    $this->controls['chapterStartTime'] = [
      'required' => ['controlType', '=', ['chapters']],
      'tab'    => 'content',
      'type'   => 'typography',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Chapter start time typography', 'extras' ),
      'css'    => [
        [
          'property' => 'font',
          'selector' => '.xmp-chapter-radio-start-time',
        ],
      ],
      
    ];

    $this->controls['chapterDuration'] = [
      'required' => ['controlType', '=', ['chapters']],
      'tab'    => 'content',
      'type'   => 'typography',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Chapter duration typography', 'extras' ),
      'css'    => [
        [
          'property' => 'font',
          'selector' => '.xmp-chapter-radio-duration',
        ],
      ],
      
    ];


    $this->controls['chapterItemsHocusSep'] = [
      'required' => ['controlType', '=', ['chapters']],
      'group' => 'styleControls',
			'label'    => esc_html__( 'Chapter Menu Items (hover/focus)', 'bricks' ),
			'type'        => 'separator',
		];

    $this->controls['chapterProgressColor'] = [
      'tab'    => 'content',
      'type'   => 'color',
      'required' => ['controlType', '=', ['chapters']],
      'group' => 'styleControls',
      'label'  => esc_html__( 'Chapter progress bar color', 'extras' ),
      'css'    => [
        [
          'property' => '--media-chapters-progress-bg',
          'selector' => ''
        ],
      ],
    ];

    $this->controls['chapterProgressHeight'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units' => true,
      'group' => 'styleControls',
      'placeholder' => '3px',
      'label'  => esc_html__( 'Chapter progress height', 'extras' ),
      'required' => ['controlType', '=', ['chapters']],
      'css'    => [
        [
          'property' => '--media-chapters-progress-height',
          'selector' => ''
        ],
      ],
    ];


    $this->controls['chapterLabelHocus'] = [
      'required' => ['controlType', '=', ['chapters']],
      'tab'    => 'content',
      'type'   => 'typography',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Chapter label typography', 'extras' ),
      'css'    => [
        [
          'property' => 'font',
          'selector' => '.xmp-radio[data-hocus] .xmp-chapter-radio-label',
        ],
      ],
      
    ];

    $this->controls['chapterStartTimeHocus'] = [
      'required' => ['controlType', '=', ['chapters']],
      'tab'    => 'content',
      'type'   => 'typography',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Chapter start time typography', 'extras' ),
      'css'    => [
        [
          'property' => 'font',
          'selector' => '.xmp-radio[data-hocus] .xmp-chapter-radio-start-time',
        ],
      ],
      
    ];

    $this->controls['chapterDurationHocus'] = [
      'required' => ['controlType', '=', ['chapters']],
      'tab'    => 'content',
      'type'   => 'typography',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Chapter duration typography', 'extras' ),
      'css'    => [
        [
          'property' => 'font',
          'selector' => '.xmp-radio[data-hocus] .xmp-chapter-radio-duration',
        ],
      ],
      
    ];


    $this->controls['chapterItemsSelectedSep'] = [
      'required' => ['controlType', '=', ['chapters']],
      'group' => 'styleControls',
			'label'    => esc_html__( 'Chapter Menu Items (selected)', 'bricks' ),
			'type'        => 'separator',
		];



    $this->controls['chapterLabelSelected'] = [
      'required' => ['controlType', '=', ['chapters']],
      'tab'    => 'content',
      'type'   => 'typography',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Chapter label typography', 'extras' ),
      'css'    => [
        [
          'property' => 'font',
          'selector' => '.xmp-radio[aria-checked=true] .xmp-chapter-radio-label',
        ],
      ],
      
    ];

    $this->controls['chapterStartTimeSelected'] = [
      'required' => ['controlType', '=', ['chapters']],
      'tab'    => 'content',
      'type'   => 'typography',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Chapter start time typography', 'extras' ),
      'css'    => [
        [
          'property' => 'font',
          'selector' => '.xmp-radio[aria-checked=true] .xmp-chapter-radio-start-time',
        ],
      ],
      
    ];

    $this->controls['chapterDurationSelected'] = [
      'required' => ['controlType', '=', ['chapters']],
      'tab'    => 'content',
      'type'   => 'typography',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Chapter duration typography', 'extras' ),
      'css'    => [
        [
          'property' => 'font',
          'selector' => '.xmp-radio[aria-checked=true] .xmp-chapter-radio-duration',
        ],
      ],
      
    ];



    /* time */


    $this->controls['timeSep'] = [
      'group' => 'styleControls',
			'label'    => esc_html__( 'Time', 'bricks' ),
			'type'        => 'separator',
      'required' => ['controlType', '=', ['time']],
		];
    
    $time = '';
    $currentTime = '&[data-type=current]';
    $divider = '.xmp-time-divider';
    $duration = '&[data-type=duration]';

    $this->controls['timeTypography'] = [
      'tab'    => 'content',
      'type'   => 'typography',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Typography', 'extras' ),
      'css'    => [
        [
          'property' => 'font',
          'selector' => $time,
        ],
      ],
      'required' => ['controlType', '=', ['time']],
    ];

    $this->controls['dividerTypography'] = [
      'tab'    => 'content',
      'type'   => 'typography',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Divider Typography', 'extras' ),
      'css'    => [
        [
          'property' => 'font',
          'selector' => $divider,
        ],
      ],
      'required' => ['controlType', '=', ['time']],
    ];
    

    $this->controls['timeGap'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'group' => 'styleControls',
      'label'  => esc_html__( 'Gap', 'extras' ),
      'css'    => [
        [
          'property' => 'gap',
          'selector' => '.xmp-time-group'
        ],
        [
          'property' => 'gap',
          'selector' => '.xmp-chapter-title'
        ],
      ],
      'required' => ['controlType', '=', ['time']],
    ];

    

    /* custom text */

    $this->controls['Typography'] = [
      'tab'    => 'content',
      'type'   => 'typography',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Typography', 'extras' ),
      'css'    => [
        [
          'property' => 'font',
          'selector' => ''
        ],
        
      ],
      'required' => ['controlType', '=', ['custom-text', 'title', 'artist']],
    ];


     /* chapter title text */

     $this->controls['chapterTitleTypography'] = [
      'tab'    => 'content',
      'type'   => 'typography',
      'group' => 'styleControls',
      'label'  => esc_html__( 'Typography', 'extras' ),
      'css'    => [
        [
          'property' => 'font',
          'selector' => '.xmp-chapter-title'
        ],
        
      ],
      'required' => ['controlType', '=', ['chapter-title']],
    ];


    
     /* poster image */

     $this->controls['altText'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Custom alt text', 'bricks' ),
      'type'     => 'text',
      'inline'   => true,
      'rerender' => false,
      'required' => [ 'controlType', '=', 'poster-image' ],
   ];

    $this->controls['posterImageHeight'] = [
      'tab'      => 'content',
      'placeholder' => 'auto',
      'label'    => esc_html__( 'Height', 'bricks' ),
      'type'     => 'number',
      'css'    => [
        [
          'property' => 'height',
          'selector' => 'img',
        ],
      ],
      'group' => 'styleControls',
      'units'   => true,
      'rerender' => false,
      'required' => [ 'controlType', '=', 'poster-image' ],
      ];

    $this->controls['posterImageWidth'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Width', 'bricks' ),
      'type'     => 'number',
      'css'    => [
        [
          'property' => 'width',
          'selector' => 'img',
        ],
      ],
      'placeholder' => 'auto',
      'group' => 'styleControls',
      'units'   => true,
      'rerender' => false,
      'required' => [ 'controlType', '=', 'poster-image' ],
    ];

    $this->controls['posterImageAspectRatio'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'group' => 'styleControls',
      'placeholder' => '1/1',
      'label'  => esc_html__( 'Aspect ratio', 'extras' ),
      'css'    => [
        [
          'property' => 'aspect-ratio',
          'selector' => 'img',
        ],
      ],
      'required' => [ 'controlType', '=', 'poster-image' ],
    ];

      $this->controls['objectFit'] = [
        'tab'         => 'content',
        'label'       => esc_html__( 'Object fit', 'bricks' ),
        'group' => 'styleControls',
        'type'        => 'select',
        'options'     => [
          'fill'       => esc_html__( 'Fill', 'bricks' ),
          'contain'    => esc_html__( 'Contain', 'bricks' ),
          'cover'      => esc_html__( 'Cover', 'bricks' ),
          'none'       => esc_html__( 'None', 'bricks' ),
          'scale-down' => esc_html__( 'Scale down', 'bricks' ),
          'fill'       => esc_html__( 'Fill', 'bricks' ),
        ],
        'css'         => [
          [
            'property' => 'object-fit',
            'selector' => 'img',
          ],
        ],
        'inline'      => true,
        'placeholder' => esc_html__( 'Cover', 'bricks' ),
        'required' => [ 'controlType', '=', 'poster-image' ],
      ];



      /* tooltips */

      $this->controls['toolTipSep'] = [
        'tab'      => 'content',
        'group' => 'tooltips',
        //'label'    => esc_html__( 'Control Tooltip', 'bricks' ),
        'description' => esc_html__( 'Tooltips will first need to enabled in the media player settings. Customise or disable for this control specifically here.', 'bricks' ),
        'type'     => 'separator',
      ];

    $this->controls['maybeToolTips'] = [
      'group' => 'tooltips',
			'label'    => esc_html__( 'Control Tooltip', 'bricks' ),
			'type'        => 'select',
      'info' => esc_html__( 'Inherit = from player settings', 'bricks' ),
			'options'     => [
				'inherit' => esc_html__( 'Inherit', 'bricks' ),
				'disable'  => esc_html__( 'Disable', 'bricks' ),
			],
			'placeholder' => esc_html__( 'Inherit', 'bricks' ),
			'inline'      => true,
			'small' => true,
      ];

      $this->controls['defaultTooltipPlacement'] = [
        'tab'    => 'content',
        'group' => 'tooltips',
        'inline' => true,
        'label'  => esc_html__( 'Tooltip Placement', 'extras' ),
        'type'      => 'select',
          'options' => $placementOptions,
          'required' => ['maybeToolTips','!=','disable'],
        'placeholder' => esc_html__('Top', 'bricks' ), 
      ];


      /*
      $this->controls['tooltipBg'] = [
        'tab'    => 'content',
        'type'   => 'color',
        'group' => 'tooltips',
        'label'  => esc_html__( 'Background', 'extras' ),
        'css'    => [
          [
            'property' => '--media-tooltip-bg-color',
            'selector' => '',
          ],
        ],
        'required' => ['maybeToolTips','!=','disable'],
      ];
  
      $this->controls['tooltipBorder'] = [
        'tab'    => 'content',
        'type'   => 'border',
        'group' => 'tooltips',
        'label'  => esc_html__( 'Border', 'extras' ),
        'css'    => [
          [
            'property' => 'border',
            'selector' => '.xmp-tooltip-content',
          ],
        ],
        'required' => ['maybeToolTips','!=','disable'],
      ];
      
  
      $this->controls['tooltipTypography'] = [
        'tab'    => 'content',
        'type'   => 'typography',
        'group' => 'tooltips',
        'label'  => esc_html__( 'Typography', 'extras' ),
        'css'    => [
          [
            'property' => 'font',
            'selector' => '.xmp-tooltip-content',
          ],
        ],
        'required' => ['maybeToolTips','!=','disable'],
      ];
  
      $this->controls['tooltipPadding'] = [
        'tab'    => 'content',
        'type'   => 'dimensions',
        'group' => 'tooltips',
        'label'  => esc_html__( 'Padding', 'extras' ),
        'css'    => [
          [
            'property' => 'padding',
            'selector' => '.xmp-tooltip-content',
          ],
        ],
        'required' => ['maybeToolTips','!=','disable'],
      ]; */
  
      $this->controls['tooltipXOffset'] = [
        'tab'    => 'content',
        'type'   => 'number',
        'units'   => false,
        'small' => true,
        'group' => 'tooltips',
        'label'  => esc_html__( 'X Offset', 'extras' ),
        'placeholder' => '',
        'required' => ['maybeToolTips','!=','disable'],
      ];
  
      $this->controls['tooltipYOffset'] = [
        'tab'    => 'content',
        'type'   => 'number',
        'units'   => false,
        'small' => true,
        'group' => 'tooltips',
        'label'  => esc_html__( 'Y Offset', 'extras' ),
        'placeholder' => '',
        'required' => ['maybeToolTips','!=','disable'],
      ];
  
      



      /* button icons */

      $inactiveControls = [
        '',
        'play',
        'play-large',
        'seek-forward',
        'seek-backward',
        //'mute',
        'pip',
        'fullscreen',
        'settings',
        'captions',
        'next',
        'previous',
        'download'
      ];


      $this->controls['inactiveIconSep'] = [
        'tab'      => 'content',
		    'group' => 'buttonIcons',
        'label'    => esc_html__( 'Icon', 'bricks' ),
        'type'     => 'separator',
        'required'  => ['controlType', '=', $inactiveControls],
      ];

      $this->controls['inactiveIcon'] = [
        'tab'      => 'content',
		    'group' => 'buttonIcons',
        'label'    => esc_html__( 'Icon', 'bricks' ),
        'type'     => 'icon',
        'css'      => [
          [
            'selector' => '.xmp-icon.xmp-icon-custom-inactive',
          ],
        ],
        'required'  => ['controlType', '=', $inactiveControls],
      ];


      $this->controls['inactiveIconSize'] = [
        'tab'      => 'content',
		    'group' => 'buttonIcons',
        'label'    => esc_html__( 'Icon size', 'bricks' ),
        'type'     => 'number',
        'units'     => true,
        'css'      => [
          [
            'property' => '--media-button-icon-size',
            'selector' => '.xmp-icon.xmp-icon-custom-inactive',
          ],
        ],
        'required'  => ['controlType', '=', $inactiveControls],
      ];


      $activeControls = [
        '',
        'play',
        'play-large',
        //'mute',
        'pip',
        'fullscreen',
        'captions'
      ];

      $this->controls['activeIconSep'] = [
        'tab'      => 'content',
		    'group' => 'buttonIcons',
        'label'    => esc_html__( 'Icon - active state', 'bricks' ),
        'type'     => 'separator',
        'required'  => ['controlType', '=', $activeControls],
      ];

      $this->controls['activeIcon'] = [
        'tab'      => 'content',
		    'group' => 'buttonIcons',
        'label'    => esc_html__( 'Icon', 'bricks' ),
        'type'     => 'icon',
        'css'      => [
          [
            'selector' => '.xmp-icon.xmp-icon-custom-active',
          ],
        ],
        'required'  => ['controlType', '=', $activeControls],
      ];

      $this->controls['replayIconSep'] = [
        'tab'      => 'content',
		    'group' => 'buttonIcons',
        'label'    => esc_html__( 'Icon - replay state', 'bricks' ),
        'type'     => 'separator',
        'required'  => ['controlType', '=', ['','play','play-large']],
      ];

      $this->controls['replayIcon'] = [
        'tab'      => 'content',
		    'group' => 'buttonIcons',
        'label'    => esc_html__( 'Icon', 'bricks' ),
        'type'     => 'icon',
        'css'      => [
          [
            'selector' => '.xmp-icon.xmp-icon-custom-replay',
          ],
        ],
        'required'  => ['controlType', '=', ['','play','play-large']],
      ];

      $this->controls['activeIconSize'] = [
        'tab'      => 'content',
		    'group' => 'buttonIcons',
        'label'    => esc_html__( 'Icon Size', 'bricks' ),
        'type'     => 'number',
        'units'     => true,
        'css'      => [
          [
            'property' => '--media-button-icon-size',
            'selector' => '.xmp-icon.xmp-icon-custom-active',
          ],
        ],
        'required'  => ['controlType', '=', $activeControls],
      ];


      /* mute icons */

      $this->controls['muteIcon'] = [
        'tab'      => 'content',
		    'group' => 'buttonIcons',
        'label'    => esc_html__( 'Muted Icon', 'bricks' ),
        'type'     => 'icon',
        'css'      => [
          [
            'selector' => '.xmp-mute-icon',
          ],
        ],
        'required'  => ['controlType', '=', 'mute'],
      ];

      $this->controls['muteIconSize'] = [
        'tab'      => 'content',
		    'group' => 'buttonIcons',
        'label'    => esc_html__( 'Muted Icon Size', 'bricks' ),
        'type'     => 'number',
        'units'     => true,
        'css'      => [
          [
            'property' => '--media-button-icon-size',
            'selector' => '.xmp-mute-icon',
          ],
        ],
        'required'  => ['controlType', '=', 'mute']
      ];

      $this->controls['highVolumeIcon'] = [
        'tab'      => 'content',
		    'group' => 'buttonIcons',
        'label'    => esc_html__( 'High Volume Icon', 'bricks' ),
        'type'     => 'icon',
        'css'      => [
          [
            'selector' => '.xmp-volume-high-icon',
          ],
        ],
        'required'  => ['controlType', '=', 'mute'],
      ];

      $this->controls['highVolumeIconSize'] = [
        'tab'      => 'content',
		    'group' => 'buttonIcons',
        'label'    => esc_html__( 'High Volume Icon Size', 'bricks' ),
        'type'     => 'number',
        'units'     => true,
        'css'      => [
          [
            'property' => '--media-button-icon-size',
            'selector' => '.xmp-volume-high-icon',
          ],
        ],
        'required'  => ['controlType', '=', 'mute']
      ];

      $this->controls['lowVolumeIcon'] = [
        'tab'      => 'content',
		    'group' => 'buttonIcons',
        'label'    => esc_html__( 'Low Volume Icon', 'bricks' ),
        'type'     => 'icon',
        'css'      => [
          [
            'selector' => '.xmp-icon',
          ],
        ],
        'required'  => ['controlType', '=', 'mute'],
      ];

      $this->controls['lowVolumeIconSize'] = [
        'tab'      => 'content',
		    'group' => 'buttonIcons',
        'label'    => esc_html__( 'Low Volume Icon Size', 'bricks' ),
        'type'     => 'number',
        'units'     => true,
        'css'      => [
          [
            'property' => '--media-button-icon-size',
            'selector' => '.xmp-low-high-icon',
          ],
        ],
        'required'  => ['controlType', '=', 'mute']
      ];

      $this->controls['menuItemsIconsSep'] = [
        'tab'      => 'content',
        'group' => 'buttonIcons',
        'label'    => esc_html__( 'Menu items icons', 'bricks' ),
        'type'     => 'separator',
        'required'  => ['controlType', '=', 'settings'],
      ];

      $this->controls['menuItemsIconSize'] = [
        'tab'      => 'content',
		    'group' => 'buttonIcons',
        'label'    => esc_html__( 'Icon Size', 'bricks' ),
        'type'     => 'number',
        'units'     => true,
        'css'      => [
          [
            'property' => '--media-menu-item-icon-size',
            'selector' => '.xmp-menu-items',
          ],
          [
            'property' => 'font-size',
            'selector' => '.xmp-menu-item-icon > *',
          ],
        ],
        'required'  => ['controlType', '=', 'settings']
      ];
      
      $this->controls['settingsSpeedIcon'] = [
        'tab'      => 'content',
        'group' => 'buttonIcons',
        'label'    => esc_html__( 'Playback setting Icon', 'bricks' ),
        'type'     => 'icon',
        'css'      => [
          [
            'selector' => '.xmp-playback-menu-item > .xmp-menu-item-icon',
          ],
        ],
        'required'  => ['controlType', '=', 'settings'],
      ];

      $this->controls['settingsAccessibilityIcon'] = [
        'tab'      => 'content',
        'group' => 'buttonIcons',
        'label'    => esc_html__( 'Accessibility setting icon', 'bricks' ),
        'type'     => 'icon',
        'css'      => [
          [
            'selector' => '.xmp-accessibility-menu .xmp-menu-item-icon',
          ],
        ],
        'required'  => ['controlType', '=', 'settings'],
      ];

      $this->controls['settingsCaptionsIcon'] = [
        'tab'      => 'content',
        'group' => 'buttonIcons',
        'label'    => esc_html__( 'Captions setting icon', 'bricks' ),
        'type'     => 'icon',
        'css'      => [
          [
            'selector' => '.xmp-captions-menu .xmp-menu-item-icon',
          ],
        ],
        'required'  => ['controlType', '=', 'settings'],
      ];

      $this->controls['settingsChaptersIcon'] = [
        'tab'      => 'content',
        'group' => 'buttonIcons',
        'label'    => esc_html__( 'Chapters icon', 'bricks' ),
        'type'     => 'icon',
        'css'      => [
          [
            'selector' => '.xmp-icon',
          ],
        ],
        'required'  => ['controlType', '=', 'chapters'],
      ];
      
      


  }
  
  public function render() {

    $settings = $this->settings;
    $controlType = isset( $settings['controlType'] ) ? $settings['controlType'] : 'play';
    $controlHide = isset( $settings['controlHide'] ) ? $settings['controlHide'] : 'default';

    $maybeToolTips = isset( $settings['maybeToolTips'] ) ? 'inherit' === $settings['maybeToolTips'] : true;
    $defaultTooltipPlacement = isset( $settings['defaultTooltipPlacement'] ) ? esc_attr( $settings['defaultTooltipPlacement'] ) : false;

    $this->set_attribute( '_root', 'data-x-control-visibility', $controlHide );
    $this->set_attribute( '_root', 'data-x-control-type', $controlType ); 

    if ($maybeToolTips) {
      $tooltipXOffset = isset( $settings['tooltipXOffset'] ) ? esc_attr($settings['tooltipXOffset']) : false;
      $tooltipYOffset = isset( $settings['tooltipYOffset'] ) ? esc_attr($settings['tooltipYOffset']) : false;
    }

    $inactiveIcon = empty( $this->settings['inactiveIcon'] ) ? false : self::render_icon( $this->settings['inactiveIcon'] );
    $activeIcon = empty( $this->settings['activeIcon'] ) ? false : self::render_icon( $this->settings['activeIcon'] );
    $replayIcon = empty( $this->settings['replayIcon'] ) ? false : self::render_icon( $this->settings['replayIcon'] );

    $menuXOffset = isset( $settings['menuXOffset'] ) ? esc_attr( $settings['menuXOffset'] ) : '0';
    $menuYOffset = isset( $settings['menuYOffset'] ) ? esc_attr( $settings['menuYOffset'] ) : '20';

    $output = '';
    $button = '';
    $tooltip = '';
    $isToolTip = false;
    $tag = 'media-play-button';
    $buttonClass = 'xmp-button';
    $volumeSlider = 'disable';
    $isRoot = false;
		
        switch ( $controlType ) {

            case 'play':

                $tag = 'media-play-button';
                $isRoot = true;
                $buttonClass = 'xmp-button xmp-play-button';

                $playLabel = !empty( $settings['playLabel'] ) ? esc_attr__( $settings['playLabel'] ) : esc_attr__( 'Play' );
                $pauseLabel = !empty( $settings['pauseLabel'] ) ? esc_attr__( $settings['pauseLabel'] ) : esc_attr__( 'Pause' );

                if ($maybeToolTips) {
                  $this->set_attribute( '_root', 'data-x-tooltip', $playLabel );
                  $this->set_attribute( '_root', 'data-x-pressed-tooltip', $pauseLabel );
                }
                
                $button .= $inactiveIcon ? '<div class="xmp-play-icon xmp-icon xmp-icon-custom xmp-icon-custom-inactive">'  . $inactiveIcon . '</div>' : '<div class="xmp-play-icon xmp-icon" type="play"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M10.6667 6.6548C10.6667 6.10764 11.2894 5.79346 11.7295 6.11862L24.377 15.4634C24.7377 15.7298 24.7377 16.2692 24.3771 16.5357L11.7295 25.8813C11.2895 26.2065 10.6667 25.8923 10.6667 25.3451L10.6667 6.6548Z" fill="currentColor"></path></svg></div>';
                $button .= $activeIcon ? '<div class="xmp-pause-icon xmp-icon xmp-icon-custom xmp-icon-custom-active">' . $activeIcon . '</div>' : '<div class="xmp-pause-icon xmp-icon" type="pause"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M8.66667 6.66667C8.29848 6.66667 8 6.96514 8 7.33333V24.6667C8 25.0349 8.29848 25.3333 8.66667 25.3333H12.6667C13.0349 25.3333 13.3333 25.0349 13.3333 24.6667V7.33333C13.3333 6.96514 13.0349 6.66667 12.6667 6.66667H8.66667Z" fill="currentColor"></path> <path d="M19.3333 6.66667C18.9651 6.66667 18.6667 6.96514 18.6667 7.33333V24.6667C18.6667 25.0349 18.9651 25.3333 19.3333 25.3333H23.3333C23.7015 25.3333 24 25.0349 24 24.6667V7.33333C24 6.96514 23.7015 6.66667 23.3333 6.66667H19.3333Z" fill="currentColor"></path></svg></div>';
                $button .= $replayIcon ? '<div class="xmp-replay-icon xmp-icon xmp-icon-custom xmp-icon-custom-reply">'  . $replayIcon . '</div>' : '<div class="xmp-icon xmp-replay-icon" type="replay"><svg fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" class="vds-icon vds-replay-icon" viewBox="0 0 32 32" data-icon="replay"><path d="M15.6038 12.2147C16.0439 12.5399 16.6667 12.2257 16.6667 11.6786V10.1789C16.6667 10.1001 16.7351 10.0384 16.8134 10.0479C20.1116 10.4494 22.6667 13.2593 22.6667 16.6659C22.6667 20.3481 19.6817 23.3332 15.9995 23.3332C12.542 23.3332 9.69927 20.7014 9.36509 17.332C9.32875 16.9655 9.03371 16.6662 8.66548 16.6662L6.66655 16.6666C6.29841 16.6666 5.99769 16.966 6.02187 17.3334C6.36494 22.5454 10.7012 26.6667 16 26.6667C21.5228 26.6667 26 22.1895 26 16.6667C26 11.4103 21.9444 7.10112 16.7916 6.69757C16.7216 6.69209 16.6667 6.63396 16.6667 6.56372V4.98824C16.6667 4.44106 16.0439 4.12689 15.6038 4.45206L11.0765 7.79738C10.7159 8.06387 10.7159 8.60326 11.0766 8.86973L15.6038 12.2147Z" fill="currentColor"></path></svg></div>';
        
                $this->set_attribute( '_root', 'data-paused', '' );
        
                break;

              case 'play-large':

                $tag = 'media-play-button';
                $isRoot = true;
                $buttonClass = 'xmp-button xmp-button-large';

                $playLabel = !empty( $settings['playLabel'] ) ? esc_attr__( $settings['playLabel'] ) : esc_attr__('Play');
                $pauseLabel = !empty( $settings['pauseLabel'] ) ? esc_attr__( $settings['pauseLabel'] ) : esc_attr__('Pause');
                $visibility = isset( $settings['visibility'] ) ? esc_attr( $settings['visibility'] ) : "default";

                if ($maybeToolTips) {
                  $this->set_attribute( '_root', 'data-x-tooltip', $playLabel );
                  $this->set_attribute( '_root', 'data-x-pressed-tooltip', $pauseLabel );
                }

                $button .= $inactiveIcon ? '<div class="xmp-play-icon xmp-icon xmp-icon-custom xmp-icon-custom-inactive">'  . $inactiveIcon . '</div>' : '<div class="xmp-play-icon xmp-icon" type="play"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M10.6667 6.6548C10.6667 6.10764 11.2894 5.79346 11.7295 6.11862L24.377 15.4634C24.7377 15.7298 24.7377 16.2692 24.3771 16.5357L11.7295 25.8813C11.2895 26.2065 10.6667 25.8923 10.6667 25.3451L10.6667 6.6548Z" fill="currentColor"></path></svg></div>';
                $button .= $activeIcon ? '<div class="xmp-pause-icon xmp-icon xmp-icon-custom xmp-icon-custom-active">' . $activeIcon . '</div>' : '<div class="xmp-pause-icon xmp-icon" type="pause"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M8.66667 6.66667C8.29848 6.66667 8 6.96514 8 7.33333V24.6667C8 25.0349 8.29848 25.3333 8.66667 25.3333H12.6667C13.0349 25.3333 13.3333 25.0349 13.3333 24.6667V7.33333C13.3333 6.96514 13.0349 6.66667 12.6667 6.66667H8.66667Z" fill="currentColor"></path> <path d="M19.3333 6.66667C18.9651 6.66667 18.6667 6.96514 18.6667 7.33333V24.6667C18.6667 25.0349 18.9651 25.3333 19.3333 25.3333H23.3333C23.7015 25.3333 24 25.0349 24 24.6667V7.33333C24 6.96514 23.7015 6.66667 23.3333 6.66667H19.3333Z" fill="currentColor"></path></svg></div>';
                $button .= $replayIcon ? '<div class="xmp-replay-icon xmp-icon xmp-icon-custom xmp-icon-custom-reply">'  . $replayIcon . '</div>' : '<div class="xmp-icon xmp-replay-icon" type="replay"><svg fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" class="vds-icon vds-replay-icon" viewBox="0 0 32 32" data-icon="replay"><path d="M15.6038 12.2147C16.0439 12.5399 16.6667 12.2257 16.6667 11.6786V10.1789C16.6667 10.1001 16.7351 10.0384 16.8134 10.0479C20.1116 10.4494 22.6667 13.2593 22.6667 16.6659C22.6667 20.3481 19.6817 23.3332 15.9995 23.3332C12.542 23.3332 9.69927 20.7014 9.36509 17.332C9.32875 16.9655 9.03371 16.6662 8.66548 16.6662L6.66655 16.6666C6.29841 16.6666 5.99769 16.966 6.02187 17.3334C6.36494 22.5454 10.7012 26.6667 16 26.6667C21.5228 26.6667 26 22.1895 26 16.6667C26 11.4103 21.9444 7.10112 16.7916 6.69757C16.7216 6.69209 16.6667 6.63396 16.6667 6.56372V4.98824C16.6667 4.44106 16.0439 4.12689 15.6038 4.45206L11.0765 7.79738C10.7159 8.06387 10.7159 8.60326 11.0766 8.86973L15.6038 12.2147Z" fill="currentColor"></path></svg></div>';

                $this->set_attribute( '_root', 'data-paused', '' );
        
                break;
        
            case 'seek-forward':

                $tag = 'media-seek-button';
                $isRoot = true;
        
                $seekAmountForward = !empty( $settings['seekAmountForward'] ) ? esc_attr( $settings['seekAmountForward'] ) : '+10';
                $seekLabelForward = !empty( $settings['seekLabelForward'] ) ? esc_attr( $settings['seekLabelForward'] ) : 'Forward';

                if ($maybeToolTips) {
                  $this->set_attribute( '_root', 'data-x-tooltip', $seekLabelForward );
                }

                switch ( $seekAmountForward ) {

                  case '+10':
                    $icon = 'seek-forward-10';
                    $iconsvg = '<svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M15.3333 10.3452C15.3333 10.8924 15.9561 11.2066 16.3962 10.8814L20.9234 7.5364C21.2841 7.26993 21.2841 6.73054 20.9235 6.46405L16.3962 3.11873C15.9561 2.79356 15.3333 3.10773 15.3333 3.6549V5.22682C15.3333 5.29746 15.2778 5.35579 15.2073 5.36066C9.31791 5.76757 4.66667 10.674 4.66667 16.6667C4.66667 22.9259 9.74078 28 16 28C22.0352 28 26.9686 23.2827 27.314 17.3341C27.3354 16.9665 27.0348 16.6673 26.6666 16.6673H24.6666C24.2984 16.6673 24.0029 16.9668 23.9726 17.3337C23.6336 21.4399 20.1937 24.6667 16 24.6667C11.5817 24.6667 8 21.085 8 16.6667C8 12.5225 11.1517 9.11428 15.1887 8.70739C15.2663 8.69957 15.3333 8.76096 15.3333 8.83893V10.3452Z" fill="currentColor"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M17.0879 19.679C17.4553 19.9195 17.8928 20.0398 18.4004 20.0398C18.9099 20.0398 19.3474 19.9205 19.7129 19.6818C20.0803 19.4413 20.3635 19.0938 20.5623 18.6392C20.7612 18.1847 20.8606 17.6373 20.8606 16.9972C20.8625 16.3608 20.764 15.8192 20.5652 15.3722C20.3663 14.9252 20.0822 14.5853 19.7129 14.3523C19.3455 14.1175 18.908 14 18.4004 14C17.8928 14 17.4553 14.1175 17.0879 14.3523C16.7224 14.5853 16.4402 14.9252 16.2413 15.3722C16.0443 15.8173 15.9449 16.3589 15.943 16.9972C15.9411 17.6354 16.0396 18.1818 16.2385 18.6364C16.4373 19.089 16.7205 19.4366 17.0879 19.679ZM19.1362 18.4262C18.9487 18.7349 18.7034 18.8892 18.4004 18.8892C18.1996 18.8892 18.0225 18.8211 17.8691 18.6847C17.7157 18.5464 17.5964 18.3372 17.5112 18.0568C17.4278 17.7765 17.3871 17.4233 17.389 16.9972C17.3909 16.3684 17.4847 15.9025 17.6703 15.5995C17.8559 15.2945 18.0992 15.1421 18.4004 15.1421C18.603 15.1421 18.7801 15.2093 18.9316 15.3438C19.0831 15.4782 19.2015 15.6828 19.2867 15.9574C19.372 16.2301 19.4146 16.5767 19.4146 16.9972C19.4165 17.6392 19.3237 18.1156 19.1362 18.4262Z" fill="currentColor"></path> <path d="M13.7746 19.8978C13.8482 19.8978 13.9079 19.8381 13.9079 19.7644V14.2129C13.9079 14.1393 13.8482 14.0796 13.7746 14.0796H12.642C12.6171 14.0796 12.5927 14.0865 12.5716 14.0997L11.2322 14.9325C11.1931 14.9568 11.1693 14.9996 11.1693 15.0457V15.9497C11.1693 16.0539 11.2833 16.1178 11.3722 16.0635L12.464 15.396C12.4682 15.3934 12.473 15.3921 12.4779 15.3921C12.4926 15.3921 12.5045 15.404 12.5045 15.4187V19.7644C12.5045 19.8381 12.5642 19.8978 12.6378 19.8978H13.7746Z" fill="currentColor"></path></svg>';
                    break;
        
                  case '+15':
                    $icon = 'seek-forward-15';
                    $iconsvg = '<svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M15.3333 10.3452C15.3333 10.8924 15.9561 11.2066 16.3962 10.8814L20.9234 7.5364C21.2841 7.26993 21.2841 6.73054 20.9234 6.46405L16.3962 3.11873C15.9561 2.79356 15.3333 3.10773 15.3333 3.6549V5.22682C15.3333 5.29746 15.2777 5.35579 15.2073 5.36066C9.3179 5.76757 4.66666 10.674 4.66666 16.6667C4.66666 22.9259 9.74076 28 16 28C22.0352 28 26.9686 23.2827 27.314 17.3341C27.3353 16.9665 27.0348 16.6673 26.6666 16.6673H24.6666C24.2984 16.6673 24.0029 16.9668 23.9726 17.3337C23.6336 21.4399 20.1936 24.6667 16 24.6667C11.5817 24.6667 7.99999 21.085 7.99999 16.6667C7.99999 12.5225 11.1517 9.11428 15.1887 8.70739C15.2663 8.69957 15.3333 8.76096 15.3333 8.83893V10.3452Z" fill="currentColor"></path> <path d="M13.9647 19.8182C14.0383 19.8182 14.098 19.7585 14.098 19.6849V14.1334C14.098 14.0597 14.0383 14 13.9647 14H12.8321C12.8072 14 12.7828 14.007 12.7617 14.0201L11.4223 14.8529C11.3832 14.8773 11.3594 14.9201 11.3594 14.9662V15.8702C11.3594 15.9743 11.4734 16.0383 11.5622 15.984L12.6541 15.3164C12.6583 15.3139 12.6631 15.3125 12.668 15.3125C12.6827 15.3125 12.6946 15.3244 12.6946 15.3391V19.6849C12.6946 19.7585 12.7543 19.8182 12.8279 19.8182H13.9647Z" fill="currentColor"></path> <path d="M17.2808 19.6676C17.6104 19.8211 17.9873 19.8978 18.4115 19.8978C18.8641 19.8978 19.259 19.8106 19.5962 19.6364C19.9352 19.4603 20.1984 19.2178 20.3859 18.9091C20.5753 18.6004 20.67 18.2462 20.67 17.8466C20.67 17.4773 20.5905 17.1497 20.4314 16.8637C20.2742 16.5777 20.0583 16.3542 19.7837 16.1932C19.5109 16.0303 19.2003 15.9489 18.8518 15.9489C18.5772 15.9489 18.3319 16.0019 18.116 16.108C17.9064 16.21 17.7531 16.3393 17.6562 16.4958C17.6524 16.5019 17.6457 16.5057 17.6386 16.5057C17.6265 16.5057 17.6171 16.4953 17.6182 16.4833L17.7378 15.254C17.7445 15.1857 17.8019 15.1336 17.8706 15.1336H20.1929C20.2666 15.1336 20.3263 15.0739 20.3263 15.0002V14.1334C20.3263 14.0597 20.2666 14 20.1929 14H16.7163C16.6468 14 16.589 14.0534 16.5834 14.1226L16.3501 17.0168C16.3446 17.0848 16.3914 17.1459 16.4584 17.1585L17.4903 17.3522C17.5454 17.3625 17.6001 17.3366 17.6343 17.2923C17.7078 17.1974 17.804 17.1199 17.9229 17.0597C18.0744 16.9839 18.2344 16.947 18.403 16.9489C18.581 16.9489 18.7382 16.9887 18.8746 17.0682C19.0128 17.1459 19.1208 17.2548 19.1984 17.3949C19.278 17.5351 19.3168 17.6989 19.3149 17.8864C19.3168 18.0701 19.2789 18.233 19.2013 18.375C19.1236 18.5152 19.0166 18.6241 18.8802 18.7017C18.7439 18.7794 18.5876 18.8182 18.4115 18.8182C18.1861 18.8182 17.992 18.7538 17.8291 18.625C17.6993 18.5209 17.6176 18.3903 17.5841 18.2333C17.5692 18.1639 17.5122 18.108 17.4413 18.108H16.3404C16.2656 18.108 16.2049 18.1696 16.2119 18.244C16.2393 18.5373 16.3352 18.8007 16.4996 19.0341C16.6909 19.3031 16.9513 19.5142 17.2808 19.6676Z" fill="currentColor"></path></svg>';
                    break;
        
                  case '+30': 
                    $icon = 'seek-forward-30';
                    $iconsvg = '<svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M15.3334 10.3452C15.3334 10.8924 15.9561 11.2066 16.3962 10.8814L20.9235 7.5364C21.2841 7.26993 21.2841 6.73054 20.9235 6.46405L16.3962 3.11873C15.9561 2.79356 15.3334 3.10773 15.3334 3.6549V5.22682C15.3334 5.29746 15.2778 5.35579 15.2073 5.36066C9.31793 5.76757 4.66669 10.674 4.66669 16.6667C4.66669 22.9259 9.74079 28 16 28C22.0352 28 26.9686 23.2827 27.314 17.3341C27.3354 16.9665 27.0349 16.6673 26.6667 16.6673H24.6666C24.2985 16.6673 24.0029 16.9668 23.9726 17.3337C23.6337 21.4399 20.1937 24.6667 16 24.6667C11.5817 24.6667 8.00002 21.085 8.00002 16.6667C8.00002 12.5225 11.1517 9.11428 15.1887 8.70739C15.2663 8.69957 15.3334 8.76096 15.3334 8.83893V10.3452Z" fill="currentColor"></path> <path d="M11.5845 19.75C11.9273 19.9015 12.3203 19.9773 12.7635 19.9773C13.2181 19.9773 13.6205 19.9034 13.9709 19.7557C14.3232 19.6061 14.5978 19.4015 14.7948 19.1421C14.9936 18.8807 15.0931 18.5815 15.0931 18.2443C15.095 17.8864 14.9804 17.5881 14.7493 17.3495C14.524 17.1149 14.1917 16.97 13.7524 16.9148C13.7393 16.9131 13.7294 16.9021 13.7294 16.8889C13.7294 16.876 13.7389 16.8651 13.7516 16.8631C14.0787 16.8115 14.3468 16.6819 14.5561 16.4745C14.772 16.2623 14.879 15.9925 14.8772 15.6648C14.8772 15.3485 14.7891 15.0654 14.6129 14.8154C14.4368 14.5635 14.1906 14.3646 13.8743 14.2188C13.558 14.0729 13.1915 14 12.7749 14C12.3563 14 11.9832 14.0748 11.6556 14.2245C11.3298 14.3741 11.0722 14.5815 10.8828 14.8466C10.7184 15.0752 10.6239 15.3353 10.5994 15.6268C10.5932 15.7007 10.6537 15.7614 10.7279 15.7614H11.8072C11.8794 15.7614 11.9367 15.7034 11.9525 15.633C11.9707 15.5522 12.0043 15.4785 12.0533 15.412C12.1272 15.3116 12.2266 15.2339 12.3516 15.179C12.4785 15.1241 12.6196 15.0966 12.7749 15.0966C12.9226 15.0966 13.0523 15.1241 13.1641 15.179C13.2777 15.2339 13.3658 15.3116 13.4283 15.412C13.4927 15.5104 13.5239 15.625 13.522 15.7557C13.5239 15.8902 13.4879 16.0095 13.4141 16.1137C13.3402 16.2159 13.237 16.2955 13.1044 16.3523C12.9737 16.4091 12.8222 16.4375 12.6499 16.4375H12.2179C12.1442 16.4375 12.0845 16.4972 12.0845 16.5709V17.3042C12.0845 17.3778 12.1442 17.4375 12.2179 17.4375H12.6499C12.8468 17.4375 13.0173 17.4669 13.1612 17.5256C13.3071 17.5843 13.4198 17.6667 13.4993 17.7728C13.5789 17.8788 13.6177 18 13.6158 18.1364C13.6177 18.2728 13.5826 18.394 13.5107 18.5C13.4387 18.6042 13.3383 18.6866 13.2095 18.7472C13.0826 18.8059 12.9359 18.8353 12.7692 18.8353C12.6025 18.8353 12.4539 18.8078 12.3232 18.7529C12.1925 18.6979 12.0893 18.6222 12.0135 18.5256C11.9638 18.461 11.9292 18.3903 11.9097 18.3135C11.8921 18.2444 11.8352 18.1875 11.7639 18.1875H10.6114C10.5372 18.1875 10.4767 18.2482 10.483 18.3221C10.5081 18.6178 10.6064 18.8825 10.7777 19.1165C10.9747 19.3854 11.2436 19.5966 11.5845 19.75Z" fill="currentColor"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M17.7801 19.679C18.1476 19.9195 18.5851 20.0398 19.0926 20.0398C19.6021 20.0398 20.0396 19.9205 20.4051 19.6818C20.7726 19.4413 21.0557 19.0938 21.2546 18.6392C21.4534 18.1847 21.5529 17.6373 21.5529 16.9972C21.5548 16.3608 21.4563 15.8192 21.2574 15.3722C21.0585 14.9252 20.7745 14.5853 20.4051 14.3523C20.0377 14.1175 19.6002 14 19.0926 14C18.5851 14 18.1476 14.1175 17.7801 14.3523C17.4146 14.5853 17.1324 14.9252 16.9335 15.3722C16.7366 15.8173 16.6371 16.3589 16.6352 16.9972C16.6334 17.6354 16.7318 18.1818 16.9307 18.6364C17.1296 19.089 17.4127 19.4366 17.7801 19.679ZM19.8284 18.4262C19.6409 18.7349 19.3957 18.8892 19.0926 18.8892C18.8919 18.8892 18.7148 18.8211 18.5614 18.6847C18.408 18.5464 18.2887 18.3372 18.2034 18.0568C18.1201 17.7765 18.0794 17.4233 18.0813 16.9972C18.0832 16.3684 18.1769 15.9025 18.3625 15.5995C18.5481 15.2945 18.7915 15.1421 19.0926 15.1421C19.2953 15.1421 19.4724 15.2093 19.6239 15.3438C19.7754 15.4782 19.8938 15.6828 19.979 15.9574C20.0642 16.2301 20.1068 16.5767 20.1068 16.9972C20.1087 17.6392 20.0159 18.1156 19.8284 18.4262Z" fill="currentColor"></path></svg>';
                    break;
                }

                $this->set_attribute( 'mediaButton', 'seconds', $seekAmountForward );
        
                $button .= $inactiveIcon ? '<div class="xmp-icon xmp-icon-custom xmp-icon-custom-inactive">'  . $inactiveIcon . '</div>' : '<div class="xmp-icon" type="' . $icon . '">' . $iconsvg . '</div>';
        
                break;
        
            case 'seek-backward':

                $tag = 'media-seek-button';
                $isRoot = true;
        
                $seekAmountBack = !empty( $settings['seekAmountBack'] ) ? esc_attr( $settings['seekAmountBack'] ) : '-10';
                $seekLabelBack = !empty( $settings['seekLabelBack'] ) ? esc_attr( $settings['seekLabelBack'] ) : 'Rewind';

                if ($maybeToolTips) {
                  $this->set_attribute( '_root', 'data-x-tooltip', $seekLabelBack );
                } 

                switch ( $seekAmountBack ) {

                  case '-10':
                    $icon = 'seek-backward-10';
                    $iconsvg = '<svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M16.6667 10.3452C16.6667 10.8924 16.0439 11.2066 15.6038 10.8814L11.0766 7.5364C10.7159 7.26993 10.7159 6.73054 11.0766 6.46405L15.6038 3.11873C16.0439 2.79356 16.6667 3.10773 16.6667 3.6549V5.22682C16.6667 5.29746 16.7223 5.35579 16.7927 5.36066C22.6821 5.76757 27.3333 10.674 27.3333 16.6667C27.3333 22.9259 22.2592 28 16 28C9.96483 28 5.03145 23.2827 4.68601 17.3341C4.66466 16.9665 4.96518 16.6673 5.33339 16.6673H7.3334C7.70157 16.6673 7.99714 16.9668 8.02743 17.3337C8.36638 21.4399 11.8064 24.6667 16 24.6667C20.4183 24.6667 24 21.085 24 16.6667C24 12.5225 20.8483 9.11428 16.8113 8.70739C16.7337 8.69957 16.6667 8.76096 16.6667 8.83893V10.3452Z" fill="currentColor"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M17.0879 19.679C17.4553 19.9195 17.8928 20.0398 18.4004 20.0398C18.9099 20.0398 19.3474 19.9205 19.7129 19.6818C20.0803 19.4413 20.3635 19.0938 20.5623 18.6392C20.7612 18.1847 20.8606 17.6373 20.8606 16.9972C20.8625 16.3608 20.764 15.8192 20.5652 15.3722C20.3663 14.9252 20.0822 14.5853 19.7129 14.3523C19.3455 14.1175 18.908 14 18.4004 14C17.8928 14 17.4553 14.1175 17.0879 14.3523C16.7224 14.5853 16.4402 14.9252 16.2413 15.3722C16.0443 15.8173 15.9449 16.3589 15.943 16.9972C15.9411 17.6354 16.0396 18.1818 16.2385 18.6364C16.4373 19.089 16.7205 19.4366 17.0879 19.679ZM19.1362 18.4262C18.9487 18.7349 18.7034 18.8892 18.4004 18.8892C18.1996 18.8892 18.0226 18.8211 17.8691 18.6847C17.7157 18.5464 17.5964 18.3372 17.5112 18.0568C17.4279 17.7765 17.3871 17.4233 17.389 16.9972C17.3909 16.3684 17.4847 15.9025 17.6703 15.5995C17.8559 15.2945 18.0993 15.1421 18.4004 15.1421C18.603 15.1421 18.7801 15.2093 18.9316 15.3438C19.0832 15.4782 19.2015 15.6828 19.2868 15.9574C19.372 16.2301 19.4146 16.5767 19.4146 16.9972C19.4165 17.6392 19.3237 18.1156 19.1362 18.4262Z" fill="currentColor"></path> <path d="M13.7746 19.8978C13.8482 19.8978 13.9079 19.8381 13.9079 19.7644V14.2129C13.9079 14.1393 13.8482 14.0796 13.7746 14.0796H12.642C12.6171 14.0796 12.5927 14.0865 12.5716 14.0997L11.2322 14.9325C11.1931 14.9568 11.1693 14.9996 11.1693 15.0457V15.9497C11.1693 16.0539 11.2833 16.1178 11.3722 16.0635L12.464 15.396C12.4682 15.3934 12.473 15.3921 12.4779 15.3921C12.4926 15.3921 12.5045 15.404 12.5045 15.4187V19.7644C12.5045 19.8381 12.5642 19.8978 12.6378 19.8978H13.7746Z" fill="currentColor"></path></svg>';
                    break;
        
                  case '-15':
                    $icon = 'seek-backward-15';
                    $iconsvg = '<svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M16.6667 10.3452C16.6667 10.8924 16.0439 11.2066 15.6038 10.8814L11.0766 7.5364C10.7159 7.26993 10.7159 6.73054 11.0766 6.46405L15.6038 3.11873C16.0439 2.79356 16.6667 3.10773 16.6667 3.6549V5.22682C16.6667 5.29746 16.7223 5.35579 16.7927 5.36066C22.6821 5.76757 27.3333 10.674 27.3333 16.6667C27.3333 22.9259 22.2592 28 16 28C9.96483 28 5.03145 23.2827 4.68601 17.3341C4.66466 16.9665 4.96518 16.6673 5.33339 16.6673H7.3334C7.70157 16.6673 7.99714 16.9668 8.02743 17.3337C8.36638 21.4399 11.8064 24.6667 16 24.6667C20.4183 24.6667 24 21.085 24 16.6667C24 12.5225 20.8483 9.11428 16.8113 8.70739C16.7337 8.69957 16.6667 8.76096 16.6667 8.83893V10.3452Z" fill="currentColor"></path> <path d="M13.9647 19.8182C14.0383 19.8182 14.098 19.7585 14.098 19.6849V14.1334C14.098 14.0597 14.0383 14 13.9647 14H12.8321C12.8072 14 12.7829 14.007 12.7617 14.0201L11.4223 14.8529C11.3832 14.8773 11.3594 14.9201 11.3594 14.9662V15.8702C11.3594 15.9743 11.4734 16.0383 11.5623 15.984L12.6541 15.3164C12.6583 15.3139 12.6631 15.3125 12.668 15.3125C12.6827 15.3125 12.6946 15.3244 12.6946 15.3391V19.6849C12.6946 19.7585 12.7543 19.8182 12.828 19.8182H13.9647Z" fill="currentColor"></path> <path d="M17.2808 19.6676C17.6104 19.8211 17.9873 19.8978 18.4115 19.8978C18.8642 19.8978 19.2591 19.8106 19.5962 19.6364C19.9352 19.4603 20.1985 19.2178 20.386 18.9091C20.5753 18.6004 20.67 18.2462 20.67 17.8466C20.67 17.4773 20.5905 17.1497 20.4314 16.8637C20.2742 16.5777 20.0583 16.3542 19.7837 16.1932C19.511 16.0303 19.2003 15.9489 18.8519 15.9489C18.5772 15.9489 18.332 16.0019 18.1161 16.108C17.9064 16.21 17.7531 16.3393 17.6562 16.4958C17.6524 16.5019 17.6458 16.5057 17.6386 16.5057C17.6265 16.5057 17.6171 16.4953 17.6182 16.4833L17.7379 15.254C17.7445 15.1857 17.8019 15.1336 17.8706 15.1336H20.193C20.2666 15.1336 20.3263 15.0739 20.3263 15.0002V14.1334C20.3263 14.0597 20.2666 14 20.193 14H16.7164C16.6469 14 16.589 14.0534 16.5835 14.1226L16.3501 17.0168C16.3447 17.0848 16.3914 17.1459 16.4585 17.1585L17.4904 17.3522C17.5454 17.3625 17.6001 17.3366 17.6344 17.2923C17.7078 17.1974 17.804 17.1199 17.9229 17.0597C18.0744 16.9839 18.2344 16.947 18.403 16.9489C18.581 16.9489 18.7382 16.9887 18.8746 17.0682C19.0128 17.1459 19.1208 17.2548 19.1985 17.3949C19.278 17.5351 19.3168 17.6989 19.3149 17.8864C19.3168 18.0701 19.2789 18.233 19.2013 18.375C19.1236 18.5152 19.0166 18.6241 18.8803 18.7017C18.7439 18.7794 18.5877 18.8182 18.4115 18.8182C18.1861 18.8182 17.992 18.7538 17.8291 18.625C17.6993 18.5209 17.6177 18.3903 17.5841 18.2333C17.5693 18.1639 17.5122 18.108 17.4413 18.108H16.3404C16.2656 18.108 16.2049 18.1696 16.2119 18.244C16.2393 18.5373 16.3352 18.8007 16.4996 19.0341C16.6909 19.3031 16.9513 19.5142 17.2808 19.6676Z" fill="currentColor"></path></svg>';
                    break;
        
                  case '-30': 
                    $icon = 'seek-backward-30';
                    $iconsvg = '<svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M16.6667 10.3452C16.6667 10.8924 16.0439 11.2066 15.6038 10.8814L11.0766 7.5364C10.7159 7.26993 10.7159 6.73054 11.0766 6.46405L15.6038 3.11873C16.0439 2.79356 16.6667 3.10773 16.6667 3.6549V5.22682C16.6667 5.29746 16.7223 5.35579 16.7927 5.36066C22.6821 5.76757 27.3333 10.674 27.3333 16.6667C27.3333 22.9259 22.2592 28 16 28C9.96483 28 5.03145 23.2827 4.68601 17.3341C4.66466 16.9665 4.96518 16.6673 5.33339 16.6673H7.3334C7.70157 16.6673 7.99714 16.9668 8.02743 17.3337C8.36638 21.4399 11.8064 24.6667 16 24.6667C20.4183 24.6667 24 21.085 24 16.6667C24 12.5225 20.8483 9.11428 16.8113 8.70739C16.7337 8.69957 16.6667 8.76096 16.6667 8.83893V10.3452Z" fill="currentColor"></path> <path d="M11.5845 19.75C11.9273 19.9015 12.3203 19.9773 12.7635 19.9773C13.2181 19.9773 13.6205 19.9034 13.9709 19.7557C14.3232 19.6061 14.5978 19.4015 14.7948 19.1421C14.9936 18.8807 15.0931 18.5815 15.0931 18.2443C15.0949 17.8864 14.9804 17.5881 14.7493 17.3495C14.524 17.1149 14.1917 16.97 13.7524 16.9148C13.7393 16.9131 13.7294 16.9021 13.7294 16.8889C13.7294 16.876 13.7389 16.8651 13.7516 16.8631C14.0786 16.8115 14.3468 16.6819 14.5561 16.4745C14.772 16.2623 14.879 15.9925 14.8771 15.6648C14.8771 15.3485 14.7891 15.0654 14.6129 14.8154C14.4368 14.5635 14.1906 14.3646 13.8743 14.2188C13.558 14.0729 13.1915 14 12.7749 14C12.3563 14 11.9832 14.0748 11.6556 14.2245C11.3298 14.3741 11.0722 14.5815 10.8828 14.8466C10.7184 15.0752 10.6239 15.3353 10.5994 15.6268C10.5932 15.7007 10.6536 15.7614 10.7279 15.7614H11.8072C11.8794 15.7614 11.9367 15.7034 11.9525 15.633C11.9707 15.5522 12.0043 15.4785 12.0533 15.412C12.1271 15.3116 12.2266 15.2339 12.3516 15.179C12.4785 15.1241 12.6196 15.0966 12.7749 15.0966C12.9226 15.0966 13.0523 15.1241 13.1641 15.179C13.2777 15.2339 13.3658 15.3116 13.4283 15.412C13.4927 15.5104 13.5239 15.625 13.522 15.7557C13.5239 15.8902 13.4879 16.0095 13.4141 16.1137C13.3402 16.2159 13.237 16.2955 13.1044 16.3523C12.9737 16.4091 12.8222 16.4375 12.6499 16.4375H12.2179C12.1442 16.4375 12.0845 16.4972 12.0845 16.5709V17.3042C12.0845 17.3778 12.1442 17.4375 12.2179 17.4375H12.6499C12.8468 17.4375 13.0173 17.4669 13.1612 17.5256C13.3071 17.5843 13.4198 17.6667 13.4993 17.7728C13.5789 17.8788 13.6177 18 13.6158 18.1364C13.6177 18.2728 13.5826 18.394 13.5107 18.5C13.4387 18.6042 13.3383 18.6866 13.2095 18.7472C13.0826 18.8059 12.9359 18.8353 12.7692 18.8353C12.6025 18.8353 12.4539 18.8078 12.3232 18.7529C12.1925 18.6979 12.0893 18.6222 12.0135 18.5256C11.9638 18.461 11.9292 18.3903 11.9097 18.3135C11.8921 18.2444 11.8352 18.1875 11.7639 18.1875H10.6114C10.5372 18.1875 10.4767 18.2482 10.483 18.3221C10.5081 18.6178 10.6064 18.8825 10.7777 19.1165C10.9747 19.3854 11.2436 19.5966 11.5845 19.75Z" fill="currentColor"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M17.7801 19.679C18.1476 19.9195 18.5851 20.0398 19.0926 20.0398C19.6021 20.0398 20.0396 19.9205 20.4051 19.6818C20.7726 19.4413 21.0557 19.0938 21.2546 18.6392C21.4534 18.1847 21.5529 17.6373 21.5529 16.9972C21.5548 16.3608 21.4563 15.8192 21.2574 15.3722C21.0585 14.9252 20.7744 14.5853 20.4051 14.3523C20.0377 14.1175 19.6002 14 19.0926 14C18.5851 14 18.1476 14.1175 17.7801 14.3523C17.4146 14.5853 17.1324 14.9252 16.9335 15.3722C16.7366 15.8173 16.6371 16.3589 16.6352 16.9972C16.6333 17.6354 16.7318 18.1818 16.9307 18.6364C17.1296 19.089 17.4127 19.4366 17.7801 19.679ZM19.8284 18.4262C19.6409 18.7349 19.3957 18.8892 19.0926 18.8892C18.8919 18.8892 18.7148 18.8211 18.5614 18.6847C18.408 18.5464 18.2887 18.3372 18.2034 18.0568C18.1201 17.7765 18.0794 17.4233 18.0813 16.9972C18.0832 16.3684 18.1769 15.9025 18.3625 15.5995C18.5481 15.2945 18.7915 15.1421 19.0926 15.1421C19.2953 15.1421 19.4724 15.2093 19.6239 15.3438C19.7754 15.4782 19.8938 15.6828 19.979 15.9574C20.0642 16.2301 20.1068 16.5767 20.1068 16.9972C20.1087 17.6392 20.0159 18.1156 19.8284 18.4262Z" fill="currentColor"></path></svg>';
                    break;
        
                }

                $this->set_attribute( '_root', 'seconds', $seekAmountBack );
        
                $button .= $inactiveIcon ? '<div class="xmp-icon xmp-icon-custom xmp-icon-custom-inactive">'  . $inactiveIcon . '</div>' : '<div class="xmp-icon" type="' . $icon . '">' . $iconsvg . '</div>';
        
                $tooltip .= '<span class="xmp-seek-backward-tooltip-text">' . $seekLabelBack . '</span>';
        
                break;
        
            case 'mute':

                $tag = 'media-mute-button';
        
                $unmuteLabel = !empty( $settings['unmuteLabel'] ) ? esc_attr__( $settings['unmuteLabel'] ) : esc_attr__('Unmute');
                $muteLabel = !empty( $settings['muteLabel'] ) ? esc_attr__( $settings['muteLabel'] ) : esc_attr__('Mute');
                $volumeSlider = isset( $settings['volumeSlider'] ) ? esc_attr( $settings['volumeSlider'] ) : 'disable';

                $muteIcon = empty( $settings['muteIcon'] ) ? false : self::render_icon( $settings['muteIcon'] );
                $highVolumeIcon = empty( $settings['highVolumeIcon'] ) ? false : self::render_icon( $settings['highVolumeIcon'] );
                $lowVolumeIcon = empty( $settings['lowVolumeIcon'] ) ? false : self::render_icon( $settings['lowVolumeIcon'] );

        
                $button .= $muteIcon ? '<div class="xmp-mute-icon xmp-icon xmp-icon-custom">'  . $muteIcon . '</div>' : '<div class="xmp-mute-icon xmp-icon" aria-label="Mute" type="mute"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M17.5091 24.6594C17.5091 25.2066 16.8864 25.5208 16.4463 25.1956L9.44847 20.0252C9.42553 20.0083 9.39776 19.9991 9.36923 19.9991H4.66667C4.29848 19.9991 4 19.7006 4 19.3325V12.6658C4 12.2976 4.29848 11.9991 4.66667 11.9991H9.37115C9.39967 11.9991 9.42745 11.99 9.45039 11.973L16.4463 6.8036C16.8863 6.47842 17.5091 6.79259 17.5091 7.33977L17.5091 24.6594Z" fill="currentColor"></path> <path d="M28.8621 13.6422C29.1225 13.3818 29.1225 12.9597 28.8621 12.6994L27.9193 11.7566C27.659 11.4962 27.2368 11.4962 26.9765 11.7566L24.7134 14.0197C24.6613 14.0717 24.5769 14.0717 24.5248 14.0197L22.262 11.7568C22.0016 11.4964 21.5795 11.4964 21.3191 11.7568L20.3763 12.6996C20.116 12.9599 20.116 13.382 20.3763 13.6424L22.6392 15.9053C22.6913 15.9573 22.6913 16.0418 22.6392 16.0938L20.3768 18.3562C20.1165 18.6166 20.1165 19.0387 20.3768 19.299L21.3196 20.2419C21.58 20.5022 22.0021 20.5022 22.2624 20.2418L24.5248 17.9795C24.5769 17.9274 24.6613 17.9274 24.7134 17.9795L26.976 20.2421C27.2363 20.5024 27.6585 20.5024 27.9188 20.2421L28.8616 19.2992C29.122 19.0389 29.122 18.6168 28.8616 18.3564L26.599 16.0938C26.547 16.0418 26.547 15.9573 26.599 15.9053L28.8621 13.6422Z" fill="currentColor"></path></svg></div>';
                $button .= $highVolumeIcon ? '<div class="xmp-volume-high-icon xmp-icon xmp-icon-custom xmp-icon-custom-inactive">'  . $highVolumeIcon . '</div>' : '<div class="xmp-volume-high-icon xmp-icon" type="volume-high"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M17.5091 24.6595C17.5091 25.2066 16.8864 25.5208 16.4463 25.1956L9.44847 20.0252C9.42553 20.0083 9.39776 19.9992 9.36923 19.9992H4.66667C4.29848 19.9992 4 19.7007 4 19.3325V12.6658C4 12.2976 4.29848 11.9992 4.66667 11.9992H9.37115C9.39967 11.9992 9.42745 11.99 9.45039 11.9731L16.4463 6.80363C16.8863 6.47845 17.5091 6.79262 17.5091 7.3398L17.5091 24.6595Z" fill="currentColor"></path> <path d="M27.5091 9.33336C27.8773 9.33336 28.1758 9.63184 28.1758 10V22C28.1758 22.3682 27.8773 22.6667 27.5091 22.6667H26.1758C25.8076 22.6667 25.5091 22.3682 25.5091 22V10C25.5091 9.63184 25.8076 9.33336 26.1758 9.33336L27.5091 9.33336Z" fill="currentColor"></path> <path d="M22.1758 12C22.544 12 22.8424 12.2985 22.8424 12.6667V19.3334C22.8424 19.7016 22.544 20 22.1758 20H20.8424C20.4743 20 20.1758 19.7016 20.1758 19.3334V12.6667C20.1758 12.2985 20.4743 12 20.8424 12H22.1758Z" fill="currentColor"></path></svg></div>';
                $button .= $lowVolumeIcon ? '<div class="xmp-volume-low-icon xmp-icon xmp-icon-custom xmp-icon-custom-active">' . $lowVolumeIcon . '</div>' : '<div class="xmp-volume-low-icon xmp-icon" type="volume-low"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M17.5091 24.6594C17.5091 25.2066 16.8864 25.5207 16.4463 25.1956L9.44847 20.0252C9.42553 20.0083 9.39776 19.9991 9.36923 19.9991H4.66667C4.29848 19.9991 4 19.7006 4 19.3324V12.6658C4 12.2976 4.29848 11.9991 4.66667 11.9991H9.37115C9.39967 11.9991 9.42745 11.99 9.45039 11.973L16.4463 6.80358C16.8863 6.4784 17.5091 6.79258 17.5091 7.33975L17.5091 24.6594Z" fill="currentColor"></path> <path d="M22.8424 12.6667C22.8424 12.2985 22.544 12 22.1758 12H20.8424C20.4743 12 20.1758 12.2985 20.1758 12.6667V19.3333C20.1758 19.7015 20.4743 20 20.8424 20H22.1758C22.544 20 22.8424 19.7015 22.8424 19.3333V12.6667Z" fill="currentColor"></path></svg></div>';


                if ($maybeToolTips ) {
                  $this->set_attribute( 'mediaButton', 'data-x-tooltip', $muteLabel );
                  $this->set_attribute( 'mediaButton', 'data-x-pressed-tooltip', $unmuteLabel );
                }

                $this->set_attribute( 'mediaButton', 'data-state', 'high' );
        
                break;
        
        
            case 'time':

                $tag = 'div';
                $isRoot = true;
                $buttonClass = 'xmp-time-group';
        
                $currentTime = isset( $settings['currentTime'] ) ? $settings['currentTime'] : 'enable';
                $duration = isset( $settings['duration'] ) ? 'enable' === $settings['duration'] : true;
                $timeDivider = isset( $settings['timeDivider'] ) ? 'enable' === $settings['timeDivider'] : true;
                $timeDividerText = isset( $settings['timeDividerText'] ) ? esc_attr__( $settings['timeDividerText'] ) : "/";

                $isToolTip = false; 

                $remainder = 'remainder' === $currentTime ? 'remainder' : '';
        
                $button .= 'enable' === $currentTime || 'remainder' === $currentTime ? '<media-time class="xmp-time"  ' . $remainder . '  type="current" data-type="current">0:00</media-time>' : '';
                $button .= $timeDivider ? '<div class="xmp-time-divider">' . $timeDividerText . '</div>' : '';
                $button .= $duration ? '<media-time class="xmp-time" type="duration" data-type="duration">0:00</media-time>' : '';
        
                break;
        
        
            case 'pip':

                $tag = 'media-pip-button';
                $buttonClass = 'xmp-button';
                $isRoot = true;
        
                $enterPipLabel = isset( $settings['enterPipLabel'] ) ? esc_attr__( $settings['enterPipLabel'] ) : esc_attr__( "Enter PIP" );
                $exitPipLabel = isset( $settings['exitPipLabel'] ) ? esc_attr__( $settings['exitPipLabel'] ) : esc_attr__( "Exit PIP" );

                $button .= $inactiveIcon ? '<div class="xmp-pip-enter-icon xmp-icon xmp-icon-custom xmp-icon-custom-inactive">'  . $inactiveIcon . '</div>' : '<div class="xmp-pip-enter-icon xmp-icon" type="picture-in-picture"><svg width="32" height="32" viewBox="0 0 32 32" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M16 15.3333C15.6318 15.3333 15.3333 15.6318 15.3333 16V20C15.3333 20.3682 15.6318 20.6667 16 20.6667H21.3333C21.7015 20.6667 22 20.3682 22 20V16C22 15.6318 21.7015 15.3333 21.3333 15.3333H16Z" fill="currentColor" /><path fill-rule="evenodd" clip-rule="evenodd" d="M5.33333 7.33334C5.33333 6.96515 5.63181 6.66667 5.99999 6.66667H26C26.3682 6.66667 26.6667 6.96515 26.6667 7.33334V24.6667C26.6667 25.0349 26.3682 25.3333 26 25.3333H5.99999C5.63181 25.3333 5.33333 25.0349 5.33333 24.6667V7.33334ZM7.99999 10C7.99999 9.63182 8.29847 9.33334 8.66666 9.33334H23.3333C23.7015 9.33334 24 9.63182 24 10V22C24 22.3682 23.7015 22.6667 23.3333 22.6667H8.66666C8.29847 22.6667 7.99999 22.3682 7.99999 22V10Z" fill="currentColor"></path></svg></div> ';
                $button .= $activeIcon ? '<div class="xmp-pip-exit-icon xmp-icon xmp-icon-custom xmp-icon-custom-active">' . $activeIcon . '</div>' : '<div class="xmp-pip-exit-icon xmp-icon" type="picture-in-picture-exit"><svg width="32" height="32" viewBox="0 0 32 32" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M5.33334 26V19.4667C5.33334 19.393 5.39304 19.3333 5.46668 19.3333H7.86668C7.94031 19.3333 8.00001 19.393 8.00001 19.4667V23.3333C8.00001 23.7015 8.29849 24 8.66668 24H23.3333C23.7015 24 24 23.7015 24 23.3333V8.66666C24 8.29847 23.7015 7.99999 23.3333 7.99999H19.4667C19.393 7.99999 19.3333 7.9403 19.3333 7.86666V5.46666C19.3333 5.39302 19.393 5.33333 19.4667 5.33333H26C26.3682 5.33333 26.6667 5.63181 26.6667 5.99999V26C26.6667 26.3682 26.3682 26.6667 26 26.6667H6.00001C5.63182 26.6667 5.33334 26.3682 5.33334 26Z" fill="currentColor" /><path d="M14.0098 8.42359H10.806C10.6872 8.42359 10.6277 8.56721 10.7117 8.6512L16.5491 14.4886C16.8094 14.7489 16.8094 15.171 16.5491 15.4314L15.3234 16.657C15.0631 16.9174 14.641 16.9174 14.3806 16.657L8.63739 10.9138C8.55339 10.8298 8.40978 10.8893 8.40978 11.0081V14.0236C8.40978 14.3918 8.1113 14.6903 7.74311 14.6903H6.00978C5.64159 14.6903 5.34311 14.3918 5.34311 14.0236L5.34311 6.02359C5.34311 5.6554 5.64159 5.35692 6.00978 5.35692L14.0098 5.35692C14.378 5.35692 14.6764 5.6554 14.6764 6.02359V7.75692C14.6764 8.12511 14.378 8.42359 14.0098 8.42359Z" fill="currentColor" /></svg></div> ';

                if ($maybeToolTips) {
                  $this->set_attribute( '_root', 'data-x-tooltip', $enterPipLabel );
                  $this->set_attribute( '_root', 'data-x-pressed-tooltip', $exitPipLabel );
                }
                
        
                break;
        
            case 'fullscreen':

                $tag = 'media-fullscreen-button';
                $isRoot = true;
                $buttonClass = 'xmp-button';
        
                $enterFullscreenLabel = isset( $settings['enterFullscreenLabel'] ) ? esc_attr__( $settings['enterFullscreenLabel'] ) : esc_attr__( "Enter Fullscreen" );
                $exitFullscreenLabel = isset( $settings['exitFullscreenLabel'] ) ? esc_attr__( $settings['exitFullscreenLabel'] ) : esc_attr__( "Exit Fullscreen" );

                $button .= $inactiveIcon ? '<div class="xmp-fs-enter-icon xmp-icon xmp-icon-custom xmp-icon-custom-inactive">'  . $inactiveIcon . '</div>' : '<div class="xmp-fs-enter-icon xmp-icon" type="fullscreen"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M25.3299 7.26517C25.2958 6.929 25.0119 6.66666 24.6667 6.66666H19.3334C18.9652 6.66666 18.6667 6.96514 18.6667 7.33333V9.33333C18.6667 9.70152 18.9652 10 19.3334 10L21.8667 10C21.9403 10 22 10.0597 22 10.1333V12.6667C22 13.0349 22.2985 13.3333 22.6667 13.3333H24.6667C25.0349 13.3333 25.3334 13.0349 25.3334 12.6667V7.33333C25.3334 7.31032 25.3322 7.28758 25.3299 7.26517Z" fill="currentColor"></path> <path d="M22 21.8667C22 21.9403 21.9403 22 21.8667 22L19.3334 22C18.9652 22 18.6667 22.2985 18.6667 22.6667V24.6667C18.6667 25.0349 18.9652 25.3333 19.3334 25.3333L24.6667 25.3333C25.0349 25.3333 25.3334 25.0349 25.3334 24.6667V19.3333C25.3334 18.9651 25.0349 18.6667 24.6667 18.6667H22.6667C22.2985 18.6667 22 18.9651 22 19.3333V21.8667Z" fill="currentColor"></path> <path d="M12.6667 22H10.1334C10.0597 22 10 21.9403 10 21.8667V19.3333C10 18.9651 9.70154 18.6667 9.33335 18.6667H7.33335C6.96516 18.6667 6.66669 18.9651 6.66669 19.3333V24.6667C6.66669 25.0349 6.96516 25.3333 7.33335 25.3333H12.6667C13.0349 25.3333 13.3334 25.0349 13.3334 24.6667V22.6667C13.3334 22.2985 13.0349 22 12.6667 22Z" fill="currentColor"></path> <path d="M10 12.6667V10.1333C10 10.0597 10.0597 10 10.1334 10L12.6667 10C13.0349 10 13.3334 9.70152 13.3334 9.33333V7.33333C13.3334 6.96514 13.0349 6.66666 12.6667 6.66666H7.33335C6.96516 6.66666 6.66669 6.96514 6.66669 7.33333V12.6667C6.66669 13.0349 6.96516 13.3333 7.33335 13.3333H9.33335C9.70154 13.3333 10 13.0349 10 12.6667Z" fill="currentColor"></path></svg></div>';
                $button .= $activeIcon ? '<div class="xmp-fs-exit-icon xmp-icon xmp-icon-custom xmp-icon-custom-active">' . $activeIcon . '</div>' : '<div class="xmp-fs-exit-icon xmp-icon" type="fullscreen-exit"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M19.3334 13.3333C18.9652 13.3333 18.6667 13.0349 18.6667 12.6667L18.6667 7.33333C18.6667 6.96514 18.9652 6.66666 19.3334 6.66666H21.3334C21.7015 6.66666 22 6.96514 22 7.33333V9.86666C22 9.9403 22.0597 10 22.1334 10L24.6667 10C25.0349 10 25.3334 10.2985 25.3334 10.6667V12.6667C25.3334 13.0349 25.0349 13.3333 24.6667 13.3333L19.3334 13.3333Z" fill="currentColor"></path> <path d="M13.3334 19.3333C13.3334 18.9651 13.0349 18.6667 12.6667 18.6667H7.33335C6.96516 18.6667 6.66669 18.9651 6.66669 19.3333V21.3333C6.66669 21.7015 6.96516 22 7.33335 22H9.86669C9.94032 22 10 22.0597 10 22.1333L10 24.6667C10 25.0349 10.2985 25.3333 10.6667 25.3333H12.6667C13.0349 25.3333 13.3334 25.0349 13.3334 24.6667L13.3334 19.3333Z" fill="currentColor"></path> <path d="M18.6667 24.6667C18.6667 25.0349 18.9652 25.3333 19.3334 25.3333H21.3334C21.7015 25.3333 22 25.0349 22 24.6667V22.1333C22 22.0597 22.0597 22 22.1334 22H24.6667C25.0349 22 25.3334 21.7015 25.3334 21.3333V19.3333C25.3334 18.9651 25.0349 18.6667 24.6667 18.6667L19.3334 18.6667C18.9652 18.6667 18.6667 18.9651 18.6667 19.3333L18.6667 24.6667Z" fill="currentColor"></path> <path d="M10.6667 13.3333H12.6667C13.0349 13.3333 13.3334 13.0349 13.3334 12.6667L13.3334 10.6667V7.33333C13.3334 6.96514 13.0349 6.66666 12.6667 6.66666H10.6667C10.2985 6.66666 10 6.96514 10 7.33333L10 9.86666C10 9.9403 9.94033 10 9.86669 10L7.33335 10C6.96516 10 6.66669 10.2985 6.66669 10.6667V12.6667C6.66669 13.0349 6.96516 13.3333 7.33335 13.3333L10.6667 13.3333Z" fill="currentColor"></path></svg></div>';
        

                if ($maybeToolTips) {
                  $this->set_attribute( '_root', 'data-x-tooltip', $enterFullscreenLabel );
                  $this->set_attribute( '_root', 'data-x-pressed-tooltip', $exitFullscreenLabel );
                }
        
                break;

              case 'captions':

                $tag = 'media-caption-button';
                $isRoot = true;
                $buttonClass = 'xmp-button';

                $captionsOnLabel = isset( $settings['captionsOnLabel'] ) ? esc_attr__( $settings['captionsOnLabel'] ) : esc_attr__( "Closed-Captions On" );
                $captionsOffLabel = isset( $settings['captionsOffLabel'] ) ? esc_attr__( $settings['captionsOffLabel'] ) : esc_attr__( "Closed-Captions Off" );

                $button .= $inactiveIcon ? '<div class="xmp-cc-on-icon xmp-icon xmp-icon-custom xmp-icon-custom-inactive">'  . $inactiveIcon . '</div>' : '<div class="xmp-cc-on-icon xmp-icon" type="closed-captions-on"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M8 28.0003C8 27.6321 8.29848 27.3336 8.66667 27.3336H23.3333C23.7015 27.3336 24 27.6321 24 28.0003V29.3336C24 29.7018 23.7015 30.0003 23.3333 30.0003H8.66667C8.29848 30.0003 8 29.7018 8 29.3336V28.0003Z" fill="currentColor"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M4.66602 6.66699C4.29783 6.66699 3.99935 6.96547 3.99935 7.33366V24.667C3.99935 25.0352 4.29783 25.3337 4.66602 25.3337H27.3327C27.7009 25.3337 27.9994 25.0352 27.9994 24.667V7.33366C27.9994 6.96547 27.7009 6.66699 27.3327 6.66699H4.66602ZM8.66659 21.3333C8.2984 21.3333 7.99992 21.0349 7.99992 20.6667V11.3333C7.99992 10.9651 8.2984 10.6667 8.66659 10.6667H13.9999C14.3681 10.6667 14.6666 10.9651 14.6666 11.3333V12.6667C14.6666 13.0349 14.3681 13.3333 13.9999 13.3333H10.7999C10.7263 13.3333 10.6666 13.393 10.6666 13.4667V18.5333C10.6666 18.607 10.7263 18.6667 10.7999 18.6667H13.9999C14.3681 18.6667 14.6666 18.9651 14.6666 19.3333V20.6667C14.6666 21.0349 14.3681 21.3333 13.9999 21.3333H8.66659ZM17.9999 21.3333C17.6317 21.3333 17.3333 21.0349 17.3333 20.6667V11.3333C17.3333 10.9651 17.6317 10.6667 17.9999 10.6667H23.3333C23.7014 10.6667 23.9999 10.9651 23.9999 11.3333V12.6667C23.9999 13.0349 23.7014 13.3333 23.3333 13.3333H20.1333C20.0596 13.3333 19.9999 13.393 19.9999 13.4667V18.5333C19.9999 18.607 20.0596 18.6667 20.1333 18.6667H23.3333C23.7014 18.6667 23.9999 18.9651 23.9999 19.3333V20.6667C23.9999 21.0349 23.7014 21.3333 23.3333 21.3333H17.9999Z" fill="currentColor"></path></svg></div>';
                $button .= $activeIcon ? '<div class="xmp-cc-off-icon xmp-icon xmp-icon-custom xmp-icon-custom-active">' . $activeIcon . '</div>' : '<div class="xmp-cc-off-icon xmp-icon" type="closed-captions"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M4.6661 6.66699C4.29791 6.66699 3.99943 6.96547 3.99943 7.33366V24.667C3.99943 25.0352 4.29791 25.3337 4.6661 25.3337H27.3328C27.701 25.3337 27.9994 25.0352 27.9994 24.667V7.33366C27.9994 6.96547 27.701 6.66699 27.3328 6.66699H4.6661ZM8.66667 21.3333C8.29848 21.3333 8 21.0349 8 20.6667V11.3333C8 10.9651 8.29848 10.6667 8.66667 10.6667H14C14.3682 10.6667 14.6667 10.9651 14.6667 11.3333V12.6667C14.6667 13.0349 14.3682 13.3333 14 13.3333H10.8C10.7264 13.3333 10.6667 13.393 10.6667 13.4667V18.5333C10.6667 18.607 10.7264 18.6667 10.8 18.6667H14C14.3682 18.6667 14.6667 18.9651 14.6667 19.3333V20.6667C14.6667 21.0349 14.3682 21.3333 14 21.3333H8.66667ZM18 21.3333C17.6318 21.3333 17.3333 21.0349 17.3333 20.6667V11.3333C17.3333 10.9651 17.6318 10.6667 18 10.6667H23.3333C23.7015 10.6667 24 10.9651 24 11.3333V12.6667C24 13.0349 23.7015 13.3333 23.3333 13.3333H20.1333C20.0597 13.3333 20 13.393 20 13.4667V18.5333C20 18.607 20.0597 18.6667 20.1333 18.6667H23.3333C23.7015 18.6667 24 18.9651 24 19.3333V20.6667C24 21.0349 23.7015 21.3333 23.3333 21.3333H18Z" fill="currentColor"></path></svg></div>';

                if ($maybeToolTips) {
                  $this->set_attribute( '_root', 'data-x-tooltip', $captionsOnLabel );
                  $this->set_attribute( '_root', 'data-x-pressed-tooltip', $captionsOffLabel ); 
                }
        
                break;
        
              case 'title':
        
                $isToolTip = false;
                $isRoot = true;

                $buttonClass = 'xmp-title';

                if ( \BricksExtras\Helpers::maybePreview() ) {
                  $tag = 'media-title';
                  $button .= 'Title Here';
                } else {
                  $tag = 'media-title';
                }

                break;

              case 'artist':
        
                $isToolTip = false;
                $isRoot = true;

                $buttonClass = 'xmp-artist';

                $artistPrefix = isset( $settings['artistPrefix'] ) ? esc_attr__( $settings['artistPrefix'] ) : '';
                $artistSuffix = isset( $settings['artistSuffix'] ) ? esc_attr__( $settings['artistSuffix'] ) : '';

                if ( \BricksExtras\Helpers::maybePreview() ) {
                  $tag = 'div';
                  $button .= '<span class="xmp-artist_prefix">' . $artistPrefix . '</span>';
                  $button .= '<span class="xmp-artist_name">Artist Here</span>'; 
                  $button .= '<span class="xmp-artist_suffix">' . $artistSuffix . '</span>'; 
                } else {
                  $tag = 'div'; 
                  $button .= '<span class="xmp-artist_prefix">' . $artistPrefix . '</span>';
                  $button .= '<span class="xmp-artist_name"></span>'; 
                  $button .= '<span class="xmp-artist_suffix">' . $artistSuffix . '</span>'; 
                }

                $this->set_attribute( '_root', 'data-artist-display', '' );

                break;

              case 'download':
      
                $isToolTip = false; 
                $isRoot = true;

                $tag = 'button'; 
                $buttonClass = 'xmp-download xmp-button';
        
                $downloadLabel = isset( $settings['downloadLabel'] ) ? esc_attr__( $settings['downloadLabel'] ) : esc_attr__( "Download" );

                $downloadSource = isset( $settings['downloadSource'] ) ? esc_attr( $settings['downloadSource'] ) : 'source';
                $downloadOptionId = '';

                if ( ! empty( $settings['downloadOptionId'] ) ) {
                    $downloadOptionId = esc_attr( $this->render_dynamic_data( $settings['downloadOptionId'] ) );
                }

                if ($maybeToolTips) { 
                  $this->set_attribute( '_root', 'data-x-tooltip', $downloadLabel );
                } 

                $this->set_attribute( '_root', 'data-x-download-source', $downloadSource );

                if ( $downloadOptionId ) {
                  $this->set_attribute( '_root', 'data-x-download-option-id', $downloadOptionId );
                }

                $iconsvg = '<svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M14.2225 13.7867C14.3065 13.8706 14.4501 13.8112 14.4501 13.6924V5.99955C14.4501 5.63136 14.7486 5.33289 15.1167 5.33289H16.8501C17.2183 5.33289 17.5167 5.63136 17.5167 5.99955V13.6916C17.5167 13.8104 17.6604 13.8699 17.7444 13.7859L19.9433 11.5869C20.2037 11.3266 20.6258 11.3266 20.8861 11.5869L22.1118 12.8126C22.3722 13.0729 22.3722 13.4951 22.1118 13.7554L16.4549 19.4123C16.1946 19.6726 15.772 19.6731 15.5116 19.4128L9.85479 13.7559C9.59444 13.4956 9.59444 13.0734 9.85479 12.8131L11.0804 11.5874C11.3408 11.3271 11.7629 11.3271 12.0233 11.5874L14.2225 13.7867Z" fill="currentColor" /><path d="M5.99998 20.267C5.63179 20.267 5.33331 20.5654 5.33331 20.9336V25.9997C5.33331 26.3678 5.63179 26.6663 5.99998 26.6663H26C26.3682 26.6663 26.6666 26.3678 26.6666 25.9997V20.9336C26.6666 20.5654 26.3682 20.267 26 20.267H24.2666C23.8985 20.267 23.6 20.5654 23.6 20.9336V22.9333C23.6 23.3014 23.3015 23.5999 22.9333 23.5999H9.06638C8.69819 23.5999 8.39972 23.3014 8.39972 22.9333V20.9336C8.39972 20.5654 8.10124 20.267 7.73305 20.267H5.99998Z" fill="currentColor" /></svg>';
                
                $button .= $inactiveIcon ? '<div class="xmp-icon xmp-icon-custom xmp-icon-custom-inactive">'  . $inactiveIcon . '</div>' : '<div class="xmp-icon">' . $iconsvg . '</div> ';
 
                break; 

              case 'chapter-title':
      
                $isToolTip = false;
                $isRoot = true;

                $buttonClass = 'xmp-chapter-title';

                if ( \BricksExtras\Helpers::maybePreview() ) {
                  $tag = 'medias-chapter-title';
                  $button .= 'Chapter Titles Here';
                } else {
                  $tag = 'media-chapter-title';
                }

                break;
          
              case 'live-button';
          
                $isToolTip = false;
                $isRoot = true;

                $tag = 'media-live-button';
                $buttonClass = 'xmp-live-button';
        
                $button .= '<span class="xmp-live-button-text">LIVE</span>';
      
                break;
        
            case 'settings':
        
                $tag = 'media-menu';
                $buttonClass = 'xmp-menu xmp-settings-menu';
        
                $settingsLabel = isset( $settings['settingsLabel'] ) ? esc_attr__( $settings['settingsLabel'] ) : "Settings";

                $isToolTip = false;
                $visibility = isset( $settings['visibility'] ) ? esc_attr( $settings['visibility'] ) : "default";
                $settingsLabel = isset( $settings['settingsLabel'] ) ? esc_attr__( $settings['settingsLabel'] ) : esc_attr__( "Settings" );
                $captionsLabel = isset( $settings['captionsLabel'] ) ? esc_attr__( $settings['captionsLabel'] ) : esc_attr__( "Captions" );
                $captionsOffHint = isset( $settings['captionsOffHint'] ) ? esc_attr__( $settings['captionsOffHint'] ) : esc_attr__( "Off" );
                $loopLabel = isset( $settings['loopLabel'] ) ? esc_attr__( $settings['loopLabel'] ) : esc_attr__( "Loop" );
                $audioLabel = isset( $settings['audioLabel'] ) ? esc_attr__( $settings['audioLabel'] ) : esc_attr__( "Audio" );
                $boostLabel = isset( $settings['boostLabel'] ) ? esc_attr__( $settings['boostLabel'] ) : esc_attr__( "Boost" );
                $speedLabel = isset( $settings['speedLabel'] ) ? esc_attr__( $settings['speedLabel'] ) : esc_attr__( "Playback" );
                $accessibilityLabel = isset( $settings['accessibilityLabel'] ) ? esc_attr__( $settings['accessibilityLabel'] ) : esc_attr__( "Accessibility" );
                $announcementsLabel = isset( $settings['announcementsLabel'] ) ? esc_attr__( $settings['announcementsLabel'] ) : esc_attr__( "Announcements" );
                $qualityLabel = isset( $settings['qualityLabel'] ) ? esc_attr__( $settings['qualityLabel'] ) : esc_attr__( "Quality" );
                $speedSettingLabel = isset( $settings['speedSettingLabel'] ) ? esc_attr__( $settings['speedSettingLabel'] ) : esc_attr__( "Speed" );
                
                $settingsPlacement = isset( $settings['settingsPlacement'] ) ? esc_attr( $settings['settingsPlacement'] ) : "top-end";

                $settingsSpeedIcon = empty( $settings['settingsSpeedIcon'] ) ? false : self::render_icon( $settings['settingsSpeedIcon'] );
                $settingsAccessibilityIcon = empty( $settings['settingsAccessibilityIcon'] ) ? false : self::render_icon( $settings['settingsAccessibilityIcon'] );
                $settingsAudioIcon = empty( $settings['settingsAudioIcon'] ) ? false : self::render_icon( $settings['settingsAudioIcon'] );
                $settingsCaptionsIcon = empty( $settings['settingsCaptionsIcon'] ) ? false : self::render_icon( $settings['settingsCaptionsIcon'] );
        
                $button .= '<media-menu class="' . $buttonClass . '" data-x-control-visibility="' . $visibility . '">';

                $placement = $defaultTooltipPlacement ? 'data-x-placement="' . esc_attr( $defaultTooltipPlacement ) . '"' : '';
        
                $button .= '<media-menu-button class="xmp-menu-button xmp-settings-button xmp-button" aria-label="' . $settingsLabel . '"';
                $button .= $maybeToolTips ? 'data-x-tooltip="' . $settingsLabel .'" ' : '';
                
                if ($tooltipXOffset || \BricksExtras\Helpers::maybePreview() ) {
                 $button .= 'data-x-tooltip-x="' . $tooltipXOffset . '" ';
                }
                if ($tooltipYOffset || \BricksExtras\Helpers::maybePreview() ) {
                  $button .= 'data-x-tooltip-y="' . $tooltipYOffset . '" '; 
                }
                $button .= $placement . '>'; 
                $button .= $inactiveIcon ? '<div class="xmp-icon xmp-icon-custom xmp-icon-custom-inactive">'  . $inactiveIcon . '</div>' : '<div class="xmp-icon xmp-rotate-icon" type="settings"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M13.5722 5.33333C13.2429 5.33333 12.9629 5.57382 12.9132 5.89938L12.4063 9.21916C12.4 9.26058 12.3746 9.29655 12.3378 9.31672C12.2387 9.37118 12.1409 9.42779 12.0444 9.48648C12.0086 9.5083 11.9646 9.51242 11.9255 9.49718L8.79572 8.27692C8.48896 8.15732 8.14083 8.27958 7.9762 8.56472L5.5491 12.7686C5.38444 13.0538 5.45271 13.4165 5.70981 13.6223L8.33308 15.7225C8.3658 15.7487 8.38422 15.7887 8.38331 15.8306C8.38209 15.8867 8.38148 15.9429 8.38148 15.9993C8.38148 16.0558 8.3821 16.1121 8.38332 16.1684C8.38423 16.2102 8.36582 16.2503 8.33313 16.2765L5.7103 18.3778C5.45334 18.5836 5.38515 18.9462 5.54978 19.2314L7.97688 23.4352C8.14155 23.7205 8.48981 23.8427 8.79661 23.723L11.926 22.5016C11.9651 22.4864 12.009 22.4905 12.0449 22.5123C12.1412 22.5709 12.2388 22.6274 12.3378 22.6818C12.3745 22.7019 12.4 22.7379 12.4063 22.7793L12.9132 26.0993C12.9629 26.4249 13.2429 26.6654 13.5722 26.6654H18.4264C18.7556 26.6654 19.0356 26.425 19.0854 26.0995L19.5933 22.7801C19.5997 22.7386 19.6252 22.7027 19.6619 22.6825C19.7614 22.6279 19.8596 22.5711 19.9564 22.5121C19.9923 22.4903 20.0362 22.4862 20.0754 22.5015L23.2035 23.7223C23.5103 23.842 23.8585 23.7198 24.0232 23.4346L26.4503 19.2307C26.6149 18.9456 26.5467 18.583 26.2898 18.3771L23.6679 16.2766C23.6352 16.2504 23.6168 16.2104 23.6177 16.1685C23.619 16.1122 23.6196 16.0558 23.6196 15.9993C23.6196 15.9429 23.619 15.8866 23.6177 15.8305C23.6168 15.7886 23.6353 15.7486 23.668 15.7224L26.2903 13.623C26.5474 13.4172 26.6156 13.0544 26.451 12.7692L24.0239 8.56537C23.8592 8.28023 23.5111 8.15797 23.2043 8.27757L20.0758 9.49734C20.0367 9.51258 19.9927 9.50846 19.9569 9.48664C19.8599 9.42762 19.7616 9.37071 19.6618 9.31596C19.6251 9.2958 19.5997 9.25984 19.5933 9.21843L19.0854 5.89915C19.0356 5.57369 18.7556 5.33333 18.4264 5.33333H13.5722ZM16.0001 20.2854C18.3672 20.2854 20.2862 18.3664 20.2862 15.9993C20.2862 13.6322 18.3672 11.7132 16.0001 11.7132C13.6329 11.7132 11.714 13.6322 11.714 15.9993C11.714 18.3664 13.6329 20.2854 16.0001 20.2854Z" fill="currentColor"></path></svg></div>';
                $button .= '</media-menu-button>';
        
                $button .= '<media-menu-items class="xmp-settings-menu-items xmp-menu-items" placement="' . $settingsPlacement . '" align-offset="' . $menuXOffset . '" offset="' . $menuYOffset . '" role="menu" style="display: none;">';
               
                 /* playback/speed */
                $button .= '<media-menu class="xmp-playback-menu xmp-menu">
                        <media-menu-button class="xmp-menu-item xmp-playback-menu-item" aria-label="'. $speedLabel . '" role="menuitem" type="button">
                        <div class="xmp-icon xmp-menu-close-icon" type="chevron-left"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M14.8206 15.9049C14.7685 15.9569 14.7685 16.0413 14.8206 16.0934L21.1381 22.4109C21.3984 22.6712 21.3984 23.0934 21.1381 23.3537L19.5353 24.9565C19.275 25.2168 18.8529 25.2168 18.5925 24.9565L11.7386 18.1026C11.7283 18.0935 11.7183 18.084 11.7084 18.0742L10.1057 16.4714C9.84531 16.2111 9.84531 15.789 10.1057 15.5286L18.5909 7.04335C18.8513 6.783 19.2734 6.783 19.5338 7.04335L21.1365 8.64612C21.3969 8.90647 21.3969 9.32858 21.1365 9.58893L14.8206 15.9049Z" fill="currentColor"></path></svg></div>';

                        
                $button .= $settingsSpeedIcon ? '<div class="xmp-icon xmp-icon-custom xmp-menu-item-icon">'  . $settingsSpeedIcon . '</div>' : '<div class="xmp-icon xmp-menu-playback-icon xmp-menu-item-icon" type="playback-speed-circle"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M13.9213 5.53573C14.3146 5.45804 14.6666 5.76987 14.6666 6.17079V7.57215C14.6666 7.89777 14.4305 8.17277 14.114 8.24925C12.5981 8.61559 11.2506 9.41368 10.2091 10.506C9.98474 10.7414 9.62903 10.8079 9.34742 10.6453L8.14112 9.94885C7.79394 9.7484 7.69985 9.28777 7.96359 8.98585C9.48505 7.24409 11.5636 6.00143 13.9213 5.53573Z" fill="currentColor"></path> <path d="M5.88974 12.5908C6.01805 12.2101 6.46491 12.0603 6.81279 12.2611L8.01201 12.9535C8.29379 13.1162 8.41396 13.4577 8.32238 13.7699C8.11252 14.4854 7.99998 15.2424 7.99998 16.0257C7.99998 16.809 8.11252 17.566 8.32238 18.2814C8.41396 18.5936 8.29378 18.9352 8.01201 19.0979L6.82742 19.7818C6.48051 19.9821 6.03488 19.8337 5.90521 19.4547C5.5345 18.3712 5.33331 17.2091 5.33331 16C5.33331 14.8078 5.5289 13.6613 5.88974 12.5908Z" fill="currentColor"></path> <path d="M8.17106 22.0852C7.82291 22.2862 7.72949 22.7486 7.99532 23.0502C9.51387 24.773 11.5799 26.0017 13.9213 26.4642C14.3146 26.5419 14.6666 26.2301 14.6666 25.8291V24.4792C14.6666 24.1536 14.4305 23.8786 14.114 23.8021C12.5981 23.4358 11.2506 22.6377 10.2091 21.5453C9.98474 21.31 9.62903 21.2435 9.34742 21.4061L8.17106 22.0852Z" fill="currentColor"></path> <path d="M17.3333 25.8291C17.3333 26.2301 17.6857 26.5418 18.079 26.4641C22.9748 25.4969 26.6666 21.1796 26.6666 16C26.6666 10.8204 22.9748 6.50302 18.079 5.5358C17.6857 5.4581 17.3333 5.76987 17.3333 6.17079V7.57215C17.3333 7.89777 17.5697 8.17282 17.8862 8.24932C21.3942 9.09721 24 12.2572 24 16.0257C24 19.7942 21.3942 22.9542 17.8862 23.802C17.5697 23.8785 17.3333 24.1536 17.3333 24.4792V25.8291Z" fill="currentColor"></path> <path d="M14.3961 10.4163C13.9561 10.0911 13.3333 10.4053 13.3333 10.9525L13.3333 21.0474C13.3333 21.5946 13.9561 21.9087 14.3962 21.5836L21.2273 16.5359C21.5879 16.2694 21.5879 15.73 21.2273 15.4635L14.3961 10.4163Z" fill="currentColor"></path></svg></div>';

                $button .= '<span class="xmp-menu-item-label">' . $speedLabel . '</span>
                    <span class="xmp-menu-item-hint" data-part="hint"></span>
                    <div class="xmp-icon xmp-menu-item-open-icon" type="chevron-right"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M17.947 16.095C17.999 16.043 17.999 15.9585 17.947 15.9065L11.6295 9.58899C11.3691 9.32864 11.3691 8.90653 11.6295 8.64618L13.2323 7.04341C13.4926 6.78306 13.9147 6.78306 14.1751 7.04341L21.0289 13.8973C21.0392 13.9064 21.0493 13.9158 21.0591 13.9257L22.6619 15.5285C22.9223 15.7888 22.9223 16.2109 22.6619 16.4713L14.1766 24.9565C13.9163 25.2169 13.4942 25.2169 13.2338 24.9565L11.631 23.3538C11.3707 23.0934 11.3707 22.6713 11.631 22.411L17.947 16.095Z" fill="currentColor"></path></svg></div> </media-menu-button>

 

                        <media-menu-items class="xmp-menu-items">

                        <section class="xmp-menu-section xmp-speed-menu-section">
                          <div class="xmp-menu-section-title">
                            <header>' . $speedSettingLabel . '</header>
                            <div class="xmp-menu-section-value"></div>
                          </div>
                          <div class="xmp-menu-section-body">

                        <div class="xmp-menu-item xmp-menu-slider-item">
                        <div type="fast-backward"></div>
                        
                      <media-speed-slider class="xmp-slider">
                          <div class="xmp-slider-track"></div>
                          <div class="xmp-slider-track-fill xmp-slider-track"></div>
                          <div class="xmp-slider-thumb"></div>
                          <media-slider-steps class="xmp-slider-steps">
                            <template>
                              <div class="xmp-slider-step"></div>
                            </template>
                          </media-slider-steps>
                        </media-speed-slider>

                        <div type="fast-backward"></div>
                        </div>
                        </div>
                        </section>


                        <section class="xmp-menu-section xmp-quality-menu-section">
                        <div class="xmp-menu-section-title">
                          <header>' . $qualityLabel . '</header>
                          <div class="xmp-menu-section-value"></div>
                        </div>
                        <div class="xmp-menu-section-body">

                      <div class="xmp-menu-item xmp-menu-slider-item">
                      <div type="arrow-down"></div>
                      
                      <media-quality-slider class="xmp-slider">
                      <div class="xmp-slider-track"></div>
                      <div class="xmp-slider-track-fill xmp-slider-track"></div>
                      <div class="xmp-slider-thumb"></div>
                      <media-slider-steps class="xmp-slider-steps">
                        <template>
                          <div class="xmp-slider-step"></div>
                        </template>
                      </media-slider-steps>
                    </media-quality-slider>

                      <div type="arrow-up"></div>
                      </div>
                      </div>
                      </section>

                      <section class="xmp-menu-section xmp-loop-menu-section" role="group">
                                    <div class="xmp-menu-section-body">
                                    
                                     <div class="xmp-menu-item">
                                        <div class="xmp-menu-item-label">'. $loopLabel .'</div>  
                                        <button class="xmp-menu-checkbox" aria-pressed="false" aria-label="'. $loopLabel .'"></button>
                                    </div>
        
                                </div>
                                </section>

                        </media-menu-items>
                      </media-menu>';

                   /* captions */
                  $button .=  ' <media-menu class="xmp-captions-menu xmp-menu">
                          <media-menu-button class="xmp-menu-item" aria-label="'. $captionsLabel . '" role="menuitem" type="button">
                            <div class="xmp-icon xmp-menu-close-icon" type="chevron-left"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M14.8206 15.9049C14.7685 15.9569 14.7685 16.0413 14.8206 16.0934L21.1381 22.4109C21.3984 22.6712 21.3984 23.0934 21.1381 23.3537L19.5353 24.9565C19.275 25.2168 18.8529 25.2168 18.5925 24.9565L11.7386 18.1026C11.7283 18.0935 11.7183 18.084 11.7084 18.0742L10.1057 16.4714C9.84531 16.2111 9.84531 15.789 10.1057 15.5286L18.5909 7.04335C18.8513 6.783 19.2734 6.783 19.5338 7.04335L21.1365 8.64612C21.3969 8.90647 21.3969 9.32858 21.1365 9.58893L14.8206 15.9049Z" fill="currentColor"></path></svg></div>';

                  $button .= $settingsCaptionsIcon ? '<div class="xmp-icon xmp-icon-custom xmp-menu-item-icon">'  . $settingsCaptionsIcon . '</div>' : '<div class="xmp-icon xmp-menu-item-icon" type="closed-captions"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M4.6661 6.66699C4.29791 6.66699 3.99943 6.96547 3.99943 7.33366V24.667C3.99943 25.0352 4.29791 25.3337 4.6661 25.3337H27.3328C27.701 25.3337 27.9994 25.0352 27.9994 24.667V7.33366C27.9994 6.96547 27.701 6.66699 27.3328 6.66699H4.6661ZM8.66667 21.3333C8.29848 21.3333 8 21.0349 8 20.6667V11.3333C8 10.9651 8.29848 10.6667 8.66667 10.6667H14C14.3682 10.6667 14.6667 10.9651 14.6667 11.3333V12.6667C14.6667 13.0349 14.3682 13.3333 14 13.3333H10.8C10.7264 13.3333 10.6667 13.393 10.6667 13.4667V18.5333C10.6667 18.607 10.7264 18.6667 10.8 18.6667H14C14.3682 18.6667 14.6667 18.9651 14.6667 19.3333V20.6667C14.6667 21.0349 14.3682 21.3333 14 21.3333H8.66667ZM18 21.3333C17.6318 21.3333 17.3333 21.0349 17.3333 20.6667V11.3333C17.3333 10.9651 17.6318 10.6667 18 10.6667H23.3333C23.7015 10.6667 24 10.9651 24 11.3333V12.6667C24 13.0349 23.7015 13.3333 23.3333 13.3333H20.1333C20.0597 13.3333 20 13.393 20 13.4667V18.5333C20 18.607 20.0597 18.6667 20.1333 18.6667H23.3333C23.7015 18.6667 24 18.9651 24 19.3333V20.6667C24 21.0349 23.7015 21.3333 23.3333 21.3333H18Z" fill="currentColor"></path></svg></div> ';              


                  $button .=  '<span class="xmp-menu-item-label">'. $captionsLabel . '</span>
                            <span class="xmp-menu-item-hint" data-part="hint"></span>
                            <div class="xmp-icon xmp-menu-item-open-icon" type="chevron-right"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M17.947 16.095C17.999 16.043 17.999 15.9585 17.947 15.9065L11.6295 9.58899C11.3691 9.32864 11.3691 8.90653 11.6295 8.64618L13.2323 7.04341C13.4926 6.78306 13.9147 6.78306 14.1751 7.04341L21.0289 13.8973C21.0392 13.9064 21.0493 13.9158 21.0591 13.9257L22.6619 15.5285C22.9223 15.7888 22.9223 16.2109 22.6619 16.4713L14.1766 24.9565C13.9163 25.2169 13.4942 25.2169 13.2338 24.9565L11.631 23.3538C11.3707 23.0934 11.3707 22.6713 11.631 22.411L17.947 16.095Z" fill="currentColor"></path></svg></div> 
                    </media-menu-button>

                          <media-menu-items class="xmp-menu-items" role="menu">
                            <media-captions-radio-group class="xmp-captions-radio-group xmp-radio-group" off-label="' . $captionsOffHint . '">
                              <template>
                                <media-radio class="xmp-radio">
                                  <div class="xmp-icon" type="check"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M24.9266 7.57992C25.015 7.60672 25.0886 7.64746 25.2462 7.80506L26.956 9.51488C27.1136 9.67248 27.1543 9.74604 27.1811 9.83447C27.2079 9.9229 27.2079 10.0133 27.1811 10.1018C27.1543 10.1902 27.1136 10.2638 26.956 10.4214L13.1822 24.1951C13.0246 24.3527 12.951 24.3935 12.8626 24.4203C12.797 24.4402 12.7304 24.4453 12.6642 24.4357L12.7319 24.4203C12.6435 24.4471 12.553 24.4471 12.4646 24.4203C12.3762 24.3935 12.3026 24.3527 12.145 24.1951L5.04407 17.0942C4.88647 16.9366 4.84573 16.863 4.81893 16.7746C4.79213 16.6862 4.79213 16.5957 4.81893 16.5073C4.84573 16.4189 4.88647 16.3453 5.04407 16.1877L6.7539 14.4779C6.9115 14.3203 6.98506 14.2796 7.07349 14.2528C7.16191 14.226 7.25235 14.226 7.34078 14.2528C7.42921 14.2796 7.50277 14.3203 7.66037 14.4779L12.6628 19.4808L24.3397 7.80506C24.4973 7.64746 24.5709 7.60672 24.6593 7.57992C24.7477 7.55311 24.8382 7.55311 24.9266 7.57992Z" fill="currentColor"></path></svg></div> 
                            <div class="xmp-radio-check"></div>
                                  <span class="xmp-radio-label" data-part="label"></span>
                                </media-radio>
                              </template>
                            </media-captions-radio-group>
                          </media-menu-items>
                        </media-menu>';


                /* accessibility */
                $button .=  '<media-menu class="xmp-accessibility-menu xmp-menu">
                  <media-menu-button class="xmp-menu-item" aria-label="'. $accessibilityLabel . '">
                    <div class="xmp-icon xmp-menu-close-icon" type="chevron-left"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M14.8206 15.9049C14.7685 15.9569 14.7685 16.0413 14.8206 16.0934L21.1381 22.4109C21.3984 22.6712 21.3984 23.0934 21.1381 23.3537L19.5353 24.9565C19.275 25.2168 18.8529 25.2168 18.5925 24.9565L11.7386 18.1026C11.7283 18.0935 11.7183 18.084 11.7084 18.0742L10.1057 16.4714C9.84531 16.2111 9.84531 15.789 10.1057 15.5286L18.5909 7.04335C18.8513 6.783 19.2734 6.783 19.5338 7.04335L21.1365 8.64612C21.3969 8.90647 21.3969 9.32858 21.1365 9.58893L14.8206 15.9049Z" fill="currentColor"></path></svg></div> ';
                        
                    $button .= $settingsAccessibilityIcon ? '<div class="xmp-icon xmp-icon-custom xmp-menu-item-icon">'  . $settingsAccessibilityIcon . '</div>' : '<div class="xmp-icon xmp-menu-item-icon" type="accessibility"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M15.0007 28.7923C15.0007 29.0152 14.9774 29.096 14.9339 29.1775C14.8903 29.259 14.8263 29.323 14.7449 29.3665C14.6634 29.4101 14.5826 29.4333 14.3597 29.4333H12.575C12.3521 29.4333 12.2713 29.4101 12.1898 29.3665C12.1083 29.323 12.0443 29.259 12.0008 29.1775C11.9572 29.096 11.934 29.0152 11.934 28.7923V12.2993L5.97496 12.3C5.75208 12.3 5.67125 12.2768 5.58977 12.2332C5.50829 12.1896 5.44434 12.1257 5.40077 12.0442C5.35719 11.9627 5.33398 11.8819 5.33398 11.659V9.87429C5.33398 9.65141 5.35719 9.57059 5.40077 9.48911C5.44434 9.40762 5.50829 9.34368 5.58977 9.3001C5.67125 9.25652 5.75208 9.23332 5.97496 9.23332H26.0263C26.2492 9.23332 26.33 9.25652 26.4115 9.3001C26.493 9.34368 26.557 9.40762 26.6005 9.48911C26.6441 9.57059 26.6673 9.65141 26.6673 9.87429V11.659C26.6673 11.8819 26.6441 11.9627 26.6005 12.0442C26.557 12.1257 26.493 12.1896 26.4115 12.2332C26.33 12.2768 26.2492 12.3 26.0263 12.3L20.067 12.2993L20.0673 28.7923C20.0673 29.0152 20.0441 29.096 20.0005 29.1775C19.957 29.259 19.893 29.323 19.8115 29.3665C19.73 29.4101 19.6492 29.4333 19.4263 29.4333H17.6416C17.4187 29.4333 17.3379 29.4101 17.2564 29.3665C17.175 29.323 17.111 29.259 17.0674 29.1775C17.0239 29.096 17.0007 29.0152 17.0007 28.7923L17 22.7663H15L15.0007 28.7923Z" fill="currentColor"></path> <path d="M16.0007 7.89998C17.4734 7.89998 18.6673 6.70608 18.6673 5.23332C18.6673 3.76056 17.4734 2.56665 16.0007 2.56665C14.5279 2.56665 13.334 3.76056 13.334 5.23332C13.334 6.70608 14.5279 7.89998 16.0007 7.89998Z" fill="currentColor"></path></svg></div> ';              
                

                    $button .=  '<span class="xmp-menu-item-label">'. $accessibilityLabel . '</span>
                    <div class="xmp-icon xmp-menu-item-open-icon" type="chevron-right"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M17.947 16.095C17.999 16.043 17.999 15.9585 17.947 15.9065L11.6295 9.58899C11.3691 9.32864 11.3691 8.90653 11.6295 8.64618L13.2323 7.04341C13.4926 6.78306 13.9147 6.78306 14.1751 7.04341L21.0289 13.8973C21.0392 13.9064 21.0493 13.9158 21.0591 13.9257L22.6619 15.5285C22.9223 15.7888 22.9223 16.2109 22.6619 16.4713L14.1766 24.9565C13.9163 25.2169 13.4942 25.2169 13.2338 24.9565L11.631 23.3538C11.3707 23.0934 11.3707 22.6713 11.631 22.411L17.947 16.095Z" fill="currentColor"></path></svg></div> 
                  </media-menu-button>


                        <media-menu-items class="xmp-menu-items" role="menu">

                        <section class="xmp-menu-section" role="group">
                          <div class="xmp-menu-section-body">
                            
                            <div class="xmp-menu-item">
                                <div class="xmp-menu-item-label">'. $announcementsLabel .'</div>  
                                <button class="xmp-menu-checkbox" aria-pressed="false" aria-label="'. $announcementsLabel .'"></button>
                            </div>

                        </div>
                        </section>

                        </media-menu-items>



                      </media-menu>';
        
                break;

                case 'chapters':
        
                  $tag = 'media-menu';
                  $buttonClass = 'xmp-menu';
  
                  $isToolTip = false;

                  $visibility = isset( $settings['visibility'] ) ? esc_attr( $settings['visibility'] ) : "default";
                  $chaptersLabel = isset( $settings['chaptersLabel'] ) ? esc_attr__( $settings['chaptersLabel'] ) : esc_attr__( "Chapters" );
                  $settingsChaptersIcon = empty( $settings['settingsChaptersIcon'] ) ? false : self::render_icon( $settings['settingsChaptersIcon'] );

                  $thumbnailAttr = '';

                  $settingsPlacement = isset( $settings['settingsPlacement'] ) ? esc_attr( $settings['settingsPlacement'] ) : "top-end";
          
                  $button .= '<media-menu class="xmp-menu" data-x-control-visibility="' . $visibility . '">';

                  $placement = $defaultTooltipPlacement ? 'data-x-placement="' . esc_attr( $defaultTooltipPlacement ) . '"' : '';
          
                  $button .= '<media-menu-button class="xmp-menu-button xmp-button" type="button" aria-label="' . $chaptersLabel . '" ';
                  $button .= $maybeToolTips ? 'data-x-tooltip="' . $chaptersLabel .'" ' . $placement . ' ' : '';
                  if ($tooltipXOffset || \BricksExtras\Helpers::maybePreview() ) {
                    $button .= 'data-x-tooltip-x="' . $tooltipXOffset . '" ';
                   }
                   if ($tooltipYOffset || \BricksExtras\Helpers::maybePreview() ) {
                     $button .= 'data-x-tooltip-y="' . $tooltipYOffset . '" ';
                   }
                  $button .= '>'; 
                  $button .= $settingsChaptersIcon ? '<div class="xmp-icon xmp-icon-custom xmp-icon-custom-inactive">'  . $settingsChaptersIcon . '</div>' : '<div class="xmp-icon" type="chapters"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M16.6927 25.3346C16.3245 25.3346 16.026 25.0361 16.026 24.6679L16.026 7.3346C16.026 6.96641 16.3245 6.66794 16.6927 6.66794L18.6927 6.66794C19.0609 6.66794 19.3594 6.96642 19.3594 7.3346L19.3594 24.6679C19.3594 25.0361 19.0609 25.3346 18.6927 25.3346H16.6927Z" fill="currentColor"></path> <path d="M24.026 25.3346C23.6578 25.3346 23.3594 25.0361 23.3594 24.6679L23.3594 7.3346C23.3594 6.96641 23.6578 6.66794 24.026 6.66794L26.026 6.66794C26.3942 6.66794 26.6927 6.96642 26.6927 7.3346V24.6679C26.6927 25.0361 26.3942 25.3346 26.026 25.3346H24.026Z" fill="currentColor"></path> <path d="M5.48113 23.9407C5.38584 24.2963 5.59689 24.6619 5.95254 24.7572L7.88439 25.2748C8.24003 25.3701 8.60559 25.159 8.70089 24.8034L13.1871 8.06067C13.2824 7.70503 13.0713 7.33947 12.7157 7.24417L10.7838 6.72654C10.4282 6.63124 10.0626 6.8423 9.96733 7.19794L5.48113 23.9407Z" fill="currentColor"></path></svg></div>';
                  $button .= '</media-menu-button>';
                               
                  $button .= '<media-menu-items class="xmp-menu-items xmp-chapters-menu-items" placement="' . $settingsPlacement . '" role="menu" align-offset="' . $menuXOffset . '" offset="' . $menuYOffset . '">
                          <media-chapters-radio-group class="xmp-chapters-radio-group xmp-radio-group">
                            <template>
                              <media-radio class="xmp-chapter-radio xmp-radio">
                                <media-thumbnail class="xmp-thumbnail" src=""></media-thumbnail>
                                <div class="xmp-chapter-radio-content">
                                  <span class="xmp-chapter-radio-label" data-part="label"></span>
                                  <span class="xmp-chapter-radio-start-time" data-part="start-time"></span>
                                  <span class="xmp-chapter-radio-duration" data-part="duration"></span>
                                </div>
                              </media-radio>
                            </template>
                          </media-chapters-radio-group>
                          </media-menu-items>
          
                              </media-menu>';
          
                  break;


            case 'time-slider':

              $tag = 'media-time-slider';
              $buttonClass = "xmp-time-slider xmp-slider";
        
              $isToolTip = false;

               // Get waveform settings
               $enableWaveform = isset($settings['enableWaveform']) ? $settings['enableWaveform'] : false;
                    
               if ($enableWaveform) {
                   // Only add waveform markup if enableWaveform is true
                   $waveformBarWidth = isset($settings['waveformBarWidth']) ? $settings['waveformBarWidth'] : '';
                   $waveformBarGap = isset($settings['waveformBarGap']) ? $settings['waveformBarGap'] : 1;
                   $waveformHeight = isset($settings['waveformHeight']) ? $settings['waveformHeight'] : '26';
                   $waveformColor = isset($settings['waveformColor']) ? $settings['waveformColor'] : '';
                   $waveformPlayedColor = isset($settings['waveformPlayedColor']) ? $settings['waveformPlayedColor'] : '';
                   
                   // Add data attributes for waveform settings
                   $waveformAttrs = '';
                   $waveformAttrs .= ' data-enable-waveform="true"';
                   if ($waveformBarWidth) {
                       $waveformAttrs .= ' data-bar-width="' . esc_attr($waveformBarWidth) . '"';
                   }
                   if ($waveformHeight) {
                       $waveformAttrs .= ' data-height="' . esc_attr($waveformHeight) . '"';
                       $waveformAttrs .= ' style="height:' . esc_attr($waveformHeight) . 'px"';
                   }
                   $waveformAttrs .= ' data-bar-gap="' . esc_attr($waveformBarGap) . '"';
                   
                   // Add color settings as JSON-encoded data attributes
                   if ($waveformColor) {
                       $waveformAttrs .= ' data-waveform-color=\'' . wp_json_encode($waveformColor) . '\'';
                   }
                   if ($waveformPlayedColor) {
                       $waveformAttrs .= ' data-played-color=\'' . wp_json_encode($waveformPlayedColor) . '\'';
                   }
                   
                   $button .= '<canvas class="xmp-time-slider_waveform"' . $waveformAttrs . '></canvas>';
               } else {
                   // Only add chapters markup if enableWaveform is false
                   $button .= '<media-slider-chapters class="xmp-slider-chapters">
                           <template>
                               <div class="xmp-slider-chapter">
                               <div class="xmp-slider-track"></div>
                               <div class="xmp-slider-track-fill xmp-slider-track"></div>
                               <div class="xmp-slider-progress xmp-slider-track"></div>
                               </div>
                           </template>
                           <div class="xmp-slider-chapter xmp-slider-chapter_initial">
                               <div class="xmp-slider-track"></div>
                               <div class="xmp-slider-track-fill xmp-slider-track"></div>
                               <div class="xmp-slider-progress xmp-slider-track"></div>
                               </div>
                           </media-slider-chapters><div class="xmp-slider-thumb"></div>';
               }

               $button .= '<media-slider-preview class="xmp-slider-preview">
                                <div
                                    class="xmp-slider-chapter-title"
                                    data-part="chapter-title"
                                ></div>
                                <media-slider-value
                                    class="xmp-slider-value"
                                ></media-slider-value>
                                </media-slider-preview>';


              break;


            case 'custom-text':

                $tag = 'div';
                $buttonClass = 'xmp-custom';
                $isRoot = true;
        
                $text = isset( $settings['controlText'] ) ? esc_html( $settings['controlText'] ) : "";

                $button = $text;
        
                break;

            case 'poster-image':

              $tag = 'div';
              $buttonClass = 'xmp-poster-image';
              $isRoot = true;

              if ( !BricksExtras\Helpers::maybePreview() ) {
                $button .= '<img aria-label="media image" data-x-poster src="">';
              } else {
                $image_placeholder_url = \Bricks\Builder::get_template_placeholder_image();
                $button .= '<img aria-label="media image" data-x-poster loading="lazy" src="' . $image_placeholder_url . '">';
              }
      
              break;


            case 'previous':

              $tag = 'button';
              $buttonClass = 'xmp-previous xmp-button';
              $isRoot = true;

              $previousLabel = !empty( $settings['previousLabel'] ) ? esc_attr__( $settings['previousLabel'] ) : esc_attr__('Previous');

              $isToolTip = $maybeToolTips;
              $tooltip .= '<span class="xmp-previous-tooltip-text">' . $previousLabel . '</span>';

              $this->set_attribute( '_root', 'data-x-tooltip', $previousLabel );

              $button .= $inactiveIcon ? '<div class="xmp-previous-icon xmp-icon xmp-icon-custom">'  . $inactiveIcon . '</div>' : '<div class="xmp-icon xmp-previous-icon" type="previous"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M25.1377 6.78532C25.5778 6.46017 26.2005 6.77434 26.2005 7.32151V24.6785C26.2005 25.2257 25.5777 25.5398 25.1377 25.2147L13.3924 16.5358C13.0317 16.2693 13.0317 15.7299 13.3924 15.4634L25.1377 6.78532Z" fill="currentColor"></path> <path d="M8 6.6667C8.36819 6.6667 8.66667 6.96518 8.66667 7.33337V24.6667C8.66667 25.0349 8.36819 25.3334 8 25.3334H6C5.63181 25.3334 5.33333 25.0349 5.33333 24.6667V7.33337C5.33333 6.96518 5.63181 6.6667 6 6.6667H8Z" fill="currentColor"></path></svg></div>';

              break;

            case 'next':

              $tag = 'button';
              $buttonClass = 'xmp-next xmp-button';
              $isRoot = true;
              $nextLabel = !empty( $settings['nextLabel'] ) ? esc_attr__( $settings['nextLabel'] ) : esc_attr__('Next');

              $isToolTip = $maybeToolTips;
              $tooltip .= '<span class="xmp-next-tooltip-text">' . $nextLabel . '</span>';

              $button .= $inactiveIcon ? '<div class="xmp-next-icon xmp-icon xmp-icon-custom">'  . $inactiveIcon . '</div>' : '<div class="xmp-icon xmp-next-icon" type="next"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M6.39617 6.78532C5.9561 6.46017 5.33334 6.77434 5.33334 7.32151V24.6785C5.33334 25.2257 5.95612 25.5398 6.39619 25.2147L18.1415 16.5358C18.5021 16.2693 18.5021 15.7299 18.1415 15.4634L6.39617 6.78532Z" fill="currentColor"></path> <path d="M23.5339 6.6667C23.1657 6.6667 22.8672 6.96518 22.8672 7.33337V24.6667C22.8672 25.0349 23.1657 25.3334 23.5339 25.3334H25.5339C25.902 25.3334 26.2005 25.0349 26.2005 24.6667V7.33337C26.2005 6.96518 25.902 6.6667 25.5339 6.6667H23.5339Z" fill="currentColor"></path></svg></div>';

              $this->set_attribute( '_root', 'data-x-tooltip', $nextLabel );

              break;
  

            case 'custom':

              $customLabel = isset( $settings['customLabel'] ) ? esc_attr__( $settings['customLabel'] ) : esc_attr__( "Custom" );
              $isRoot = true;

              $isToolTip = $maybeToolTips;
              $tooltip .= '<span class="xmp-custom-tooltip-text">' . $customLabel . '</span>';

              $tag = 'div';
              $buttonClass = 'xmp-custom';

              if ( method_exists('\Bricks\Frontend','render_children') ) {
                $button .= \Bricks\Frontend::render_children( $this );
              }

              break;


        }

    
      if ( 'previous' === $controlType ) {
        $ariaLabel = $previousLabel;
      } elseif ( 'next' === $controlType ) {
        $ariaLabel = $nextLabel;
      } else {
        $ariaLabel = false;
      }

      if ( $buttonClass && $isRoot ) {
        $this->set_attribute( '_root', 'class', $buttonClass );
      }

      if ($isRoot && $maybeToolTips) {
        if ( $defaultTooltipPlacement ) {
          $this->set_attribute( '_root', 'data-x-placement', $defaultTooltipPlacement );
        }
        if ($tooltipXOffset || \BricksExtras\Helpers::maybePreview() ) {
          $this->set_attribute( '_root', 'data-x-tooltip-x', $tooltipXOffset );
        }
        if ($tooltipYOffset || \BricksExtras\Helpers::maybePreview() ) {
          $this->set_attribute( '_root', 'data-x-tooltip-y', $tooltipYOffset );
        }
      }

    if ('settings' !== $controlType && 'chapters' !== $controlType) { 

      $volumeSliderOrientation = isset( $settings['volumeSliderOrientation'] ) ? $settings['volumeSliderOrientation'] : 'horizontal';
      $volumeSliderAttr = $volumeSliderOrientation === 'vertical-above' || $volumeSliderOrientation === 'vertical-below' ? 'vertical' : 'horizontal';

      $this->set_attribute( '_root', 'data-x-volume-slider-orientation', esc_attr($volumeSliderOrientation) );

        if ($isRoot) {
          $output .= !!$ariaLabel ? "<{$tag} {$this->render_attributes( '_root' )} aria-label='" . $ariaLabel . "' class='" . $buttonClass . "'>" : "<{$tag} {$this->render_attributes( '_root' )} class='" . $buttonClass . "'>";
          $output .= $button;
          $output .= "</{$tag}>";
        } else {
          $output .= !!$ariaLabel ? "<{$tag} {$this->render_attributes( 'mediaButton' )} aria-label='" . $ariaLabel . "' class='" . $buttonClass . "'>" : "<{$tag} {$this->render_attributes( 'mediaButton' )} class='" . $buttonClass . "'>";
          $output .= $button;
          $output .= "</{$tag}>";
        }

        if ('disable' !== $volumeSlider) {
          $volumeVisibility = 'visible' === $volumeSlider ? 'xmp-visible-volume' : '';
          $output .= '<media-volume-slider orientation="' . esc_attr($volumeSliderAttr) . '" class="xmp-slider xmp-volume-slider ' . $volumeVisibility . '">';
          $output .= '<div class="xmp-slider-track"></div>';
          $output .= '<div class="xmp-slider-track-fill xmp-slider-track"></div>';
          /*$output .= '<media-slider-preview class="xmp-slider-preview" no-clamp>';
          $output .= '<media-slider-value
                        class="xmp-slider-value"
                        type="pointer"
                        format="percent"
                      ></media-slider-value>';

          $output .= '</media-slider-preview>';*/
          $output .= '<div class="xmp-slider-thumb"></div>';
          $output .= '</media-volume-slider>';
        }
      } else {
        $output .= $button;
      }

      

      $innerContent = 'mute' === $controlType ? '<div class="xmp-volume">' . $output . '</div>' : $output;

      echo !$isRoot ? "<div {$this->render_attributes( '_root' )}>" . $innerContent . "</div>" : $innerContent;
    
  }

}