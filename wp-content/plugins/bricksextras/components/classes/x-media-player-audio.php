<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once BRICKSEXTRAS_PATH . 'components/traits/media-player-trait.php';

use BricksExtras\Components\Traits\Media_Player_Trait;

class X_Media_Player_Audio extends \Bricks\Element {

  use Media_Player_Trait;

  // Element properties
  public $category     = 'extras';
	public $name         = 'xmediaplayeraudio';
	public $icon         = 'ti-microphone';
	public $scripts      = ['Vp','xDoMediaPlayer'];
  public $loop_index  = 0;
  public $nestable    = true;
  public $controlFields = [];

  
  public function get_label() {
	  return esc_html__( 'Media Player (Audio)', 'extras' );
  }

  public function get_keywords() {
    return [ 'video', 'audio', 'media', 'player', 'controls' ];
    }
  

  public function set_control_groups() {
    
    // Audio-specific control groups
    $this->control_groups['controlsAudio'] = [
      'title' => esc_html__( 'Player UI', 'bricks' ),
    ];
    
    $this->control_groups['controlsAudioLayout'] = [
      'title' => esc_html__( 'Customize UI', 'bricks' ),
      'required' => [
        ['layoutTypeAudio','!=',['custom']],
      ]
    ];

    $this->control_groups['styleGeneral'] = [
      'title' => esc_html__( 'General Styling', 'bricks' ),
    ];

    $this->control_groups['styleControls'] = [
      'title' => esc_html__( 'Individual control styling', 'bricks' ),
    ];

    $this->control_groups['poster'] = [
      'title' => esc_html__( 'Poster Image', 'bricks' ),
      'required' => ['playlistMode', '!=', true]
    ];
    

    $this->control_groups['behaviour'] = [
        'title' => esc_html__( 'Config / Behaviour', 'bricks' ),
        ['layoutType','!=','custom']
    ];
    
    // Tooltips group
    $this->control_groups['tooltips'] = [
      'title' => esc_html__( 'Tooltips', 'bricks' ),
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
    
    // Downloads group
    $this->control_groups['downloads'] = [
      'title' => esc_html__( 'Downloads', 'bricks' ),
      'required' => ['playlistMode', '!=', true]
    ];
  }

  public function set_controls() {



    // Set audio-specific background and border selectors
    if (isset($this->controls['_background'])) {
      $this->controls['_background']['css'][1]['selector'] = '&[data-x-view-type=audio]';
    }
    if (isset($this->controls['_border'])) {
      $this->controls['_border']['css'][1]['selector'] = '&[data-x-view-type=audio]';
    }

    // Define shared variables at the beginning of the method to make them available throughout
    $shared_vars = $this->get_media_player_shared_variables();
    $activeControls = $shared_vars['activeControls'];
    $inactiveControls = $shared_vars['inactiveControls'];
    $placementOptions = $shared_vars['placementOptions'];
    $optionControls = $shared_vars['optionControls'];
    $controlFields = $shared_vars['controlFields'];

    $this->set_common_controls();
   
      // Add audio-specific waveform controls
      $controlFields['enableWaveform'] = [
        'tab'      => 'content',
        'label'    => esc_html__('Enable waveform', 'bricks'),
        'type'     => 'checkbox',
        'inline'   => true,
        'required' => ['control', '=', ['time-slider']],
      ];

      $controlFields['waveformHeight'] = [
        'tab'      => 'content',
        'label'    => esc_html__('Height', 'bricks'),
        'type'     => 'number',
        'unit'     => false,
        'inline'   => true,
        'small'    => true,
        'placeholder' => '26',
        'required' => [
          ['control', '=', ['time-slider']],
          ['enableWaveform', '=', true],
        ],
      ];
      
      $controlFields['waveformBarWidth'] = [
        'tab'      => 'content',
        'label'    => esc_html__('Bar width', 'bricks'),
        'type'     => 'number',
        'unit'     => false,
        'inline'   => true,
        'small'    => true,
        'placeholder' => '2',
        'required' => [
          ['control', '=', ['time-slider']],
          ['enableWaveform', '=', true],
        ],
      ];
      
      $controlFields['waveformBarGap'] = [
        'tab'      => 'content',
        'label'    => esc_html__('Bar gap', 'bricks'),
        'type'     => 'number',
        'unit'     => false,
        'inline'   => true,
        'small'    => true,
        'placeholder' => '1',
        'required' => [
          ['control', '=', ['time-slider']],
          ['enableWaveform', '=', true],
        ],
      ];
      
      $controlFields['waveformColor'] = [
        'tab'      => 'content',
        'label'    => esc_html__('Waveform color', 'bricks'),
        'type'     => 'color',
        'inline'   => true,
        'required' => [
          ['control', '=', ['time-slider']],
          ['enableWaveform', '=', true],
        ],
      ];
      
      $controlFields['waveformPlayedColor'] = [
        'tab'      => 'content',
        'label'    => esc_html__('Played color', 'bricks'),
        'type'     => 'color',
        'inline'   => true,
        'required' => [
          ['control', '=', ['time-slider']],
          ['enableWaveform', '=', true],
        ],
      ];
    
    
    $this->controlFields = $controlFields;

    $this->controls = array_merge($this->controls, $this->get_media_player_controls('audio'));

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

     /* poster image control */

     $this->controls['posterImageSep'] = [
      'group' => 'styleControls',
      'type'        => 'separator',
      'label'  => esc_html__( 'Poster image control', 'extras' ),
      'required' => [
        ['layoutTypeAudio','!=','custom'],
      ],
    ];

    $posterImageControl = '.xmp-image img';

      $this->controls['posterImageHeight'] = [
        'tab'      => 'content',
        'placeholder' => '36px',
        'label'    => esc_html__( 'Height', 'bricks' ),
        'type'     => 'number',
        'css'    => [
          [
            'property' => 'height',
            'selector' => $posterImageControl
          ],
        ],
        'group' => 'styleControls',
        'units'   => true,
        'rerender' => false,
        'required' => [
          ['layoutTypeAudio','!=','custom'],
        ],
      ];

      $this->controls['posterImageWidth'] = [
        'tab'      => 'content',
        'placeholder' => '36px',
        'label'    => esc_html__( 'Width', 'bricks' ),
        'type'     => 'number',
        'css'    => [
          [
            'property' => 'width',
            'selector' => $posterImageControl
          ],
        ],
        'placeholder' => 'auto',
        'group' => 'styleControls',
        'units'   => true,
        'rerender' => false,
        'required' => [
            ['layoutTypeAudio','!=','custom'],
          ],
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
            'selector' => $posterImageControl
          ],
        ],
        'required' => [
          ['layoutTypeAudio','!=','custom'],
        ],
      ];


        $this->controls['posterImageBorder'] = [
          'tab'    => 'content',
          'type'   => 'border',
          'group' => 'styleControls',
          'label'  => esc_html__( 'Border', 'extras' ),
          'css'    => [
            [
              'property' => 'border',
              'selector' => $posterImageControl
            ],
          ],
          'required' => [
          ['layoutTypeAudio','!=','custom'],
        ],
      ];




      /* audio UI */

      $this->controls['layoutTypeAudio'] = [
        'group' => 'controlsAudio',
        'label'    => esc_html__( 'UI type', 'bricks' ),
        'type'        => 'select',
        'inline' => true,
        'options'     => [
          'default' => esc_html__( 'Default UI', 'bricks' ),
          'custom' => esc_html__( 'Custom UI (advanced)', 'bricks' ),
        ],
        'placeholder' => esc_html__( 'Default UI', 'bricks' ),
      ];

      $this->controls['controlsAdvancedSep'] = [
        'tab' => 'content',
        'label'    => esc_html__( 'Custom UI', 'bricks' ),
        'description' => esc_html__( 'All default UI is removed, add a block element inside the media player for the layout. Add "media controls" element inside block to build custom UI. The style settings below can still be used to style the controls globally for the player', 'bricks' ),
        'type' => 'separator',
        'group' => 'controlsAudio',
        'required' => ['layoutTypeAudio','=','custom']
    ];

      $this->controls['controlsTopSepAudio'] = [
        'tab' => 'content',
        'label'    => esc_html__( 'Top UI controls', 'bricks' ),
        'description' => esc_html__( 'Add/Remove/Re-order the internal controls', 'bricks' ),
        'type' => 'separator',
        'group' => 'controlsAudioLayout',
        'required' => ['layoutTypeAudio','!=','custom']
      ];

      

      $this->controls['controlsTopAudio'] = [
          'group' => 'controlsAudioLayout',
          //'label'    => esc_html__( 'Controls', 'bricks' ),
          'placeholder'   => esc_html__( 'Control', 'bricks' ),
          'titleProperty' => 'control',
          'type'  => 'repeater',
          'required' => ['layoutTypeAudio','!=','custom'],
          'fields'        => $this->controlFields,
          'default'       => [],
      ];

      $this->controls['audiocontrolGapTop'] = [
        'tab'    => 'content',
        'type'   => 'number',
        'units'   => true,
        'group' => 'controlsAudioLayout',
        'label'  => esc_html__( 'Gap', 'extras' ),
        'css'    => [
          [
            'property' => '--media-button-gap',
            'selector' => '.x-media-player_audio .xmp-controls_group_top',
          ],
        ],
        'required' => [
          ['layoutTypeAudio','!=','custom'],
        ],
      ];


      $this->controls['controlsCenterSepAudio'] = [
        'tab' => 'content',
        'label'    => esc_html__( 'Center UI controls', 'bricks' ),
        'description' => esc_html__( 'Add/Remove/Re-order the internal controls', 'bricks' ),
        'type' => 'separator',
        'group' => 'controlsAudioLayout',
        'required' => ['layoutTypeAudio','!=','custom']
      ];

      

      $this->controls['controlsCenterAudio'] = [
          'group' => 'controlsAudioLayout',
          //'label'    => esc_html__( 'Controls', 'bricks' ),
          'placeholder'   => esc_html__( 'Control', 'bricks' ),
          'titleProperty' => 'control',
          'type'  => 'repeater',
          'required' => ['layoutTypeAudio','!=','custom'],
          'fields'        => $this->controlFields,
          'default'       => [],
      ];

      $this->controls['audiocontrolGapCenter'] = [
        'tab'    => 'content',
        'type'   => 'number',
        'units'   => true,
        'group' => 'controlsAudioLayout',
        'label'  => esc_html__( 'Gap', 'extras' ),
        'css'    => [
          [
            'property' => '--media-button-gap',
            'selector' => '.x-media-player_audio .xmp-controls_group_center',
          ],
        ],
        'required' => [
          ['layoutTypeAudio','!=','custom'],
        ],
      ];


    $this->controls['controlsSepAudio'] = [
      'tab' => 'content',
      'label'    => esc_html__( 'Bottom UI Controls', 'bricks' ),
      'description' => esc_html__( 'Add/Remove/Re-order the internal controls', 'bricks' ),
      'type' => 'separator',
      'group' => 'controlsAudioLayout',
      'required' => ['layoutTypeAudio','!=','custom']
    ];


    $this->controls['controlsBottomAudio'] = [
        'group' => 'controlsAudioLayout',
        //'label'    => esc_html__( 'Controls', 'bricks' ),
        'placeholder'   => esc_html__( 'Control', 'bricks' ),
        'titleProperty' => 'control',
        'type'  => 'repeater',
        'required' => ['layoutTypeAudio','!=','custom'],
        'fields'        => $this->controlFields,
        'default'       => [
            [ 'control' => 'seek-backward' ],
            [ 'control' => 'play' ],
            [ 'control' => 'seek-forward' ],
            [ 'control' => 'time' ],
            [ 'control' => 'title' ],
            [ 'control' => 'spacer' ],
            [ 'control' => 'chapters' ],
            [ 'control' => 'mute' ],
            [ 'control' => 'settings' ],
        ],
    ];

    $this->controls['audiocontrolGapBottom'] = [
      'tab'    => 'content',
      'type'   => 'number',
      'units'   => true,
      'group' => 'controlsAudioLayout',
      'label'  => esc_html__( 'Gap', 'extras' ),
      'css'    => [
        [
          'property' => '--media-button-gap',
          'selector' => '.x-media-player_audio .xmp-controls_group_bottom',
        ],
      ],
      'required' => [
        ['layoutTypeAudio','!=','custom'],
      ],
    ];

    $this->controls['maybeTimeSliderAudio'] = [
      'group' => 'controlsAudioLayout',
      'label'    => esc_html__( 'Full width time slider', 'bricks' ),
      'type'        => 'select',
      'options'     => [
        'enable' => esc_html__( 'Enable', 'bricks' ),
        'disable'  => esc_html__( 'Disable', 'bricks' ),
      ],
      'placeholder' => esc_html__( 'Enable', 'bricks' ),
      'inline'      => true,
      'required' => ['layoutTypeAudio','!=','custom'],
      'info' => esc_html__( 'Above bottom UI controls', 'bricks' ),
    ];

    $this->controls['downloadSep'] = [
      'tab' => 'content',
      'label'    => esc_html__( 'Downloads', 'bricks' ),
      'description' => esc_html__( 'Optionally add extra download options for the media (for use with the download control)', 'bricks' ), 
      'type' => 'separator',
      'group' => 'downloads',
    ]; 

    $this->controls['downloadAttributes'] = [
        'group' => 'downloads',
					'type'  => 'repeater',
          'placeholder' => esc_html__( 'Download', 'bricks' ),
          'fields'      => [
            'downloads_id'    => [
              'label' => esc_html__( 'ID', 'bricks' ),
              'type'  => 'text',
              'placeholder' => 'mp3-version',
              'description' => esc_html__( 'Unique identifier for this download (use in download button settings)', 'bricks' ),
            ],
            'url'    => [
              'label' => esc_html__( 'URL', 'bricks' ),
              'type'  => 'text',
              'placeholder' => 'https://...',
            ],
            'title'    => [
              'label' => esc_html__( 'Name (optional)', 'bricks' ),
              'type'  => 'text',
              'placeholder' => '',
            ],
          ]
				];  

  } 


  public function render() {

    $settings = $this->settings;

    $provider = 'audio';

    $this->set_attribute('_root', 'class', 'brxe-xmediaplayer');

    $this->set_attribute('_root', 'playsinline', 'true');

    $is_gutenberg = $this->is_gutenberg_editor(); 
    
    $src = $this->get_media_source($settings, $provider);

    $sourceType = isset($settings['sourceType']) ? $settings['sourceType'] : 'url';

    if (isset($settings['multipleSources']) && $sourceType === 'url') {
      
      $additionalSources = [];

      if (isset($settings['srcRepeaterAudio']) && is_array($settings['srcRepeaterAudio'])) {
          foreach ($settings['srcRepeaterAudio'] as $item) {
              $source = [];
              
              if (isset($item['source'])) $source['src'] = esc_url($item['source']);
              if (isset($item['type'])) $source['type'] = $item['type'];
              
              $additionalSources[] = $source; 
          }
      }

      $additional_sources_json = htmlspecialchars(
        wp_json_encode($additionalSources)
      );

    
      $this->set_attribute('_root', 'data-x-srcs', $additional_sources_json);
    }

    // Output download attributes if set
    if (isset($settings['downloadAttributes']) && is_array($settings['downloadAttributes']) && !empty($settings['downloadAttributes'])) {
      $downloadAttributes = [];
      
      foreach ($settings['downloadAttributes'] as $item) {
        $download = [];
        
        if (isset($item['downloads_id'])) {
          $renderedId = bricks_render_dynamic_data($item['downloads_id']);
          $download['id'] = sanitize_key($renderedId); 
        }
        if (isset($item['title'])) {
          $renderedTitle = bricks_render_dynamic_data($item['title']);
          $download['title'] = esc_attr($renderedTitle); 
        }
        if (isset($item['url'])) {
          $renderedUrl = bricks_render_dynamic_data($item['url']);
          $download['url'] = esc_url($renderedUrl, array('http', 'https'));
        } 
        
        if (!empty($download) && !empty($download['id'])) {
          $downloadAttributes[] = $download;
        }
      }  

      
      if (!empty($downloadAttributes)) {
        $download_attributes_json = htmlspecialchars(
          wp_json_encode($downloadAttributes, JSON_HEX_APOS | JSON_HEX_QUOT),
          ENT_QUOTES,
          'UTF-8'
        );
        
        $this->set_attribute('_root', 'data-x-downloads', $download_attributes_json);
      }
    }

    $colorScheme = isset( $settings['colorScheme'] ) ? esc_attr( $settings['colorScheme'] ) : 'dark';

    $positioning = isset( $settings['positioning'] ) ? $settings['positioning'] : 'default';
    $altText = isset( $settings['altText'] ) ? esc_attr( $settings['altText'] ) : 'video cover image';
    $layout = isset( $settings['layout'] ) ? $settings['layout'] : 'default';
    $providerControls = isset( $settings['providerControls'] ) ? 'enable' === $settings['providerControls'] : false;
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
    $artist = isset( $settings['artist'] ) ? esc_attr( $settings['artist'] ) : '';
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
    $controlsTopAudio = ! empty( $settings['controlsTopAudio'] ) ? $settings['controlsTopAudio'] : false;
    $controlsCenterAudio = ! empty( $settings['controlsCenterAudio'] ) ? $settings['controlsCenterAudio'] : false;
    $controlsBottomAudio = ! empty( $settings['controlsBottomAudio'] ) ? $settings['controlsBottomAudio'] : false;
    $initControls = ! empty( $settings['initControls'] ) ? $settings['initControls'] : false;
    $chapters = ! empty( $settings['chapters'] ) ? $settings['chapters'] : false;
    $chapterText = isset( $settings['chapterText'] ) ? esc_attr( $settings['chapterText'] ) : '';
    $chapterStart = isset( $settings['chapterStart'] ) ? esc_attr( $settings['chapterStart'] ) : '';
    $chapterEnd = isset( $settings['chapterEnd'] ) ? esc_attr( $settings['chapterEnd'] ) : '';
    $thumbnailAttr = isset( $settings['thumbnailFile'] ) ? 'src="' . esc_url( $settings['thumbnailFile'] ) . '"' : '';
    $thumbnails = isset( $settings['thumbnailFile'] ) ? esc_url( $settings['thumbnailFile'] ) : false;
    $layoutType = isset( $settings['layoutType'] ) ? esc_attr( $settings['layoutType'] ) : 'small';
    $layoutTypeAudio = isset( $settings['layoutTypeAudio'] ) ? $settings['layoutTypeAudio'] : 'default';
    $maybeTimeSlider = isset( $settings['maybeTimeSlider'] ) ? 'enable' === $settings['maybeTimeSlider'] : true;
    $maybeTimeSliderSmall = isset( $settings['maybeTimeSliderSmall'] ) ? 'enable' === $settings['maybeTimeSliderSmall'] : true;
    $maybeTimeSliderAudio = isset( $settings['maybeTimeSliderAudio'] ) ? 'enable' === $settings['maybeTimeSliderAudio'] : true;
    $rememberPlayerSettings = isset( $settings['rememberPlayerSettings'] );
    $autoLocalPoster = isset( $settings['autoLocalPoster'] );
    $clickToPlay = isset( $settings['clickToPlay'] ) ? 'enable' === $settings['clickToPlay'] : true;
    $doubleClickToFullScreen = isset( $settings['doubleClickToFullScreen'] ) ? 'enable' === $settings['doubleClickToFullScreen'] : false;
    $componentScope = isset( $this->settings['componentScope'] ) ? $this->settings['componentScope'] : 'false';
    $whichPlaylist = isset( $settings['whichPlaylist'] ) ? $settings['whichPlaylist'] : 'section';
    $playlistSelector = isset( $settings['playlistSelector'] ) ? $settings['playlistSelector'] : '';
    $streamType = isset( $settings['streamType'] ) ? $settings['streamType'] : 'on-demand';

    $menuXOffset = isset( $settings['menuXOffset'] ) ? $settings['menuXOffset'] : '0';
    $menuYOffset = isset( $settings['menuYOffset'] ) ? $settings['menuYOffset'] : '20'; 

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
    
    // Process chapters using the trait method
    $chaptersArray = $this->process_chapters($settings);
    
    // Set chapters attribute if valid chapters exist
    $this->set_chapters_attribute($chaptersArray);
    
    // Set common attributes using the trait method
    $this->set_common_attributes(
      $settings,
      'audio',
      $config,
      $src,
      $layoutType,
      $positioning,
      $rememberPlayerSettings ? $playerIndentifier : '',
      $is_gutenberg
    );
  
    
    $firstOutput = '';
    $output = '';
    $lastOutput = '';

    $firstOutput .= "<media-player {$this->render_attributes( '_root' )}>";
    $firstOutput .=  "<media-provider>";

			if ( $textTracks ) {
        $firstOutput .= $this->render_text_tracks($textTracks);
			}


      $firstOutput .=  "</media-provider>";

    if (!$providerControls) {

      if ('custom' !== $layoutTypeAudio ) {

          /* audio layout */

          if ('audio' === $provider ) {
            $output = $this->render_audio_player(
              $output,
              $provider,
              $layoutTypeAudio,
              $controlsTopAudio,
              $controlsCenterAudio,
              $controlsBottomAudio,
              $maybeTimeSliderAudio,
              $maybeToolTips,
              $defaultTooltipPlacement,
              $title,
              $artist,
              $thumbnailAttr,
              $colorScheme,
              $portalSettings,
              $playerIndentifier
            );
          }

      }

      if ( 'custom' === $layoutTypeAudio ) {
        $output .= "<media-layout {$this->render_attributes( 'x-media-player_inner' )}>";
      } else {
        $output .= BricksExtras\Helpers::maybePreview() ? "<media-layout {$this->render_attributes( 'x-media-player_hidden-inner' )}>" : "";
      }

      /* nesting elements */
      if ( 'custom' === $layoutTypeAudio || BricksExtras\Helpers::maybePreview() ) {

        if ( method_exists('\Bricks\Frontend','render_children') ) { 
          $output .= "<media-controls  {$this->render_attributes( 'media-controls' )}>";
          $output .=  \Bricks\Frontend::render_children( $this );
          $output .= "</media-controls>";
        }

      }

      if ( 'custom' === $layoutTypeAudio ) {
        $output .= "</media-layout>";
      } else {
        $output .= BricksExtras\Helpers::maybePreview() ? "</media-layout>" : "";
      }

      $output .= '<div class="xmp-media-features">';


    } /* end of !providerControls */


    $output .= '<media-captions class="xmp-captions"></media-captions>';

    $output .= "<media-announcer></media-announcer>";

    $output .= '</div>';

    if (!$is_gutenberg) {

      echo $firstOutput;
      echo $output;


    echo "</media-player>";

    } else {

      $this->set_attribute( '_root', 'class', 'x-media-player_editor' );

      echo "<div {$this->render_attributes( '_root' )}>";
      echo "<div class='x-media-player_placeholder'>Media Player</div>";
      echo "</div>";

    }
    
  }
 

  /**
   * Render the audio player layout
   */
  private function render_audio_player(
      $output,
      $provider,
      $layoutTypeAudio,
      $controlsTopAudio,
      $controlsCenterAudio,
      $controlsBottomAudio,
      $maybeTimeSliderAudio,
      $maybeToolTips,
      $defaultTooltipPlacement,
      $title,
      $artist,
      $thumbnailAttr,
      $colorScheme,
      $portalSettings,
      $playerIndentifier
    ) {
    $output .= '<media-layout class="x-media-player_audio">';

    $playerControlsAudio = '';

    if ('custom' !== $layoutTypeAudio) {
      $playerControlsAudio .= "<media-controls {$this->render_attributes( 'media-controls' )}>";

      if ( !!$controlsTopAudio && ('audio' === $provider ) ) {
        $playerControlsAudio .= '<div class="xmp-controls_group xmp-controls_group_top">';
        foreach ( $controlsTopAudio as $control ) {
            $playerControlsAudio .= $this->renderControl( 
              $control, 
              $provider, 
              $maybeToolTips, 
              $defaultTooltipPlacement, 
              'top', 
              $title, 
              $artist,
              $thumbnailAttr, 
              $colorScheme, 
              $portalSettings, 
              $playerIndentifier
            );
        }
        $playerControlsAudio .= '</div>';
      }

      if (!!$controlsCenterAudio) {
        $playerControlsAudio .= '<div class="xmp-controls_group xmp-controls_group_center">';
        foreach ( $controlsCenterAudio as $control ) {
            $playerControlsAudio .= $this->renderControl( 
              $control, 
              $provider, 
              $maybeToolTips, 
              $defaultTooltipPlacement, 
              'top', 
              $title, 
              $artist,
              $thumbnailAttr, 
              $colorScheme, 
              $portalSettings, 
              $playerIndentifier 
            );
        }
        $playerControlsAudio .= '</div>';
      }

      if ($maybeTimeSliderAudio) {
        $playerControlsAudio .= $this->render_time_slider('', $thumbnailAttr);
      }
      
      $playerControlsAudio .= '<div class="xmp-controls_group xmp-controls_group_bottom">';

      if ( !!$controlsBottomAudio && 'audio' === $provider ) {
        foreach ( $controlsBottomAudio as $control ) {
            $playerControlsAudio .= $this->renderControl( 
              $control, 
              $provider, 
              $maybeToolTips, 
              $defaultTooltipPlacement, 
              'bottom', 
              $title, 
              $artist,
              $thumbnailAttr, 
              $colorScheme, 
              $portalSettings, 
              $playerIndentifier 
            );
        }
      }

      $playerControlsAudio .= "</div>";
      $playerControlsAudio .= "</media-controls>";
    }

    $output .= $playerControlsAudio;
    $output .= "</media-layout>";
    
    return $output;
  }


}