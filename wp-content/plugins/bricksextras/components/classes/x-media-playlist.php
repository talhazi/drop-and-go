<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class X_Media_Playlist extends \Bricks\Element {

  // Element properties
   public $category     = 'extras';
	public $name         = 'xmediaplaylist';
	public $icon         = 'ti-list';
	public $css_selector = '';
	//public $vue_component = 'bricks-nestable';
	public $nestable      = true;
	public $loop_index = 0;

  
  public function get_label() {
	  return esc_html__( 'Media Playlist', 'extras' );
  }

  public function get_keywords() {
    return [ 'video', 'audio' ];
    }


  public function set_control_groups() {

	$this->control_groups['poster'] = [
		'title' => esc_html__( 'Poster Image', 'bricks' ),
	 ];

	$this->control_groups['activeStyles'] = [
        'title' => esc_html__( 'Item styles', 'bricks' ),
    ];

	$this->control_groups['layout'] = [
		'title' => esc_html__( 'Layout / Spacing', 'extras' ),
		'tab' => 'content',
	];

	$this->control_groups['chapters'] = [
		'title' => esc_html__( 'Chapters', 'bricks' ),
	  ];

	$this->control_groups['textTracks'] = [
		'title' => esc_html__( 'Text Tracks', 'bricks' ),
	];

	$this->control_groups['behaviour'] = [
    	'title' => esc_html__( 'Config / Behaviour', 'bricks' ),
	];

	$this->control_groups['downloads'] = [
		'title' => esc_html__( 'Downloads', 'bricks' ),
		'required' => [
			['provider', '=', 'audio'],
		]
	];

  }

  public function set_controls() {

        $this->controls['provider'] = [
            'label'    => esc_html__( 'Media type', 'bricks' ),
            'type'        => 'select',
            'options'     => [
                'video' => esc_html__( 'Video', 'bricks' ),
                'audio'  => esc_html__( 'Audio', 'bricks' ),
            ],
            'placeholder' => esc_html__( 'Video', 'bricks' ),
            'inline'      => true,
        ];

        $this->controls['sourceType'] = [
            'tab'       => 'content',
            'label'     => esc_html__( 'Source', 'bricks' ),
            'type'      => 'select',
            'options'   => [
                'media'   => esc_html__( 'Media Library', 'bricks' ),
                'url'    => esc_html__( 'URL', 'bricks' ),
            ],
            'placeholder'   => esc_html__( 'URL', 'bricks' ),
            'inline'    => true,
            'clearable' => false,
        ];

        $this->controls['media'] = [ 
            'tab'      => 'content',
            'label'    => esc_html__( 'Media', 'bricks' ),
            'type'     => 'video',
            'required' => [
                ['provider', '!=', 'audio'],
                ['sourceType', '=', 'media'],
            ],
        ];

        $this->controls['audio'] = [ 
            'tab'      => 'content',
            'label'    => esc_html__( 'Audio', 'bricks' ),
            'type'     => 'audio',
            'required' => [
                ['provider', '=', 'audio'],
                ['sourceType', '=', 'media'],
            ],
        ];

        $this->controls['src'] = [
            'label'    => esc_html__( 'Media source URL', 'bricks' ),
            'type'        => 'text',
            'required' => [
                ['sourceType', '!=', 'media'],
				['multipleSources', '!=', true]
            ],
        ];

		$this->controls['srcRepeater'] = [ 
            'label'    => esc_html__( 'Media sources', 'bricks' ),
			'info' => esc_html__( 'For manually adding multiple quality URLs or file types.', 'bricks' ),
            'type'        => 'repeater',
            'fields' => [
                'source' => [
                    'label'    => esc_html__( 'Media source URL', 'bricks' ),
                    'type'        => 'text',
                    'placeholder' => esc_html( 'https://example.com/video-1920x1080.mp4', 'bricks' ),
                ],
                'type' => [
                    'label'    => esc_html__( 'Media type', 'bricks' ),
                    'type'        => 'text',
                    'placeholder' => esc_html( 'video/mp4', 'bricks' ),
                    'inline'      => true,
                ],
                'width' => [
                    'label'    => esc_html__( 'Width', 'bricks' ),
                    'type'        => 'text',
                    'placeholder' => esc_html( '1920', 'bricks' ),
                    'inline'      => true,
                ],
                'height' => [
                    'label'    => esc_html__( 'Height', 'bricks' ),
                    'type'        => 'text',
                    'placeholder' => esc_html( '1080', 'bricks' ),
                    'inline'      => true,
                ],
            ],
            'required' => [
                ['sourceType', '!=', 'media'],
                ['playlistMode', '!=', true],
                ['multipleSources', '=', true],
				['provider', '!=', 'audio'],
            ],
            //'group' => 'mediaSource'
        ]; 

		$this->controls['srcRepeaterAudio'] = [ 
            'label'    => esc_html__( 'Media sources', 'bricks' ),
			'info' => esc_html__( 'For manually adding multiple file types.', 'bricks' ),
            'type'        => 'repeater',
            'fields' => [
                'source' => [
                        'label'    => esc_html__( 'Media source URL', 'bricks' ),
                        'type'        => 'text',
                        'placeholder' => esc_html( 'https://example.com/audio.mp3', 'bricks' ),
                    ],
                    'type' => [
                        'label'    => esc_html__( 'Media type', 'bricks' ),
                        'type'        => 'text',
                        'placeholder' => esc_html( 'audio/mpeg', 'bricks' ),
                        'inline'      => true,
                    ],
            ],
            'required' => [
                ['sourceType', '!=', 'media'],
                ['playlistMode', '!=', true],
                ['multipleSources', '=', true],
				['provider', '=', 'audio'],
            ],
            //'group' => 'mediaSource'
        ];

		$this->controls['title'] = [
            'tab' => 'content',
            'label' => esc_html__( 'Title', 'bricks' ),
            'type' => 'text',
			'inline'      => true,
        ];

		$this->controls['artist'] = [
			'tab' => 'content',
			'label'    => esc_html__( 'Artist', 'bricks' ),
			'type'        => 'text',
			'inline'      => true,
			'required' => [
				['provider', '=', 'audio'],
			]
		];

		/*

		$this->controls['clipStart'] = [
			'label'    => esc_html__( 'Clip start time', 'bricks' ),
			'type'        => 'text',
			'inline'      => true,
			'placeholder' => '0:00'
		];

		$this->controls['clipEnd'] = [
			'label'    => esc_html__( 'Clip end time', 'bricks' ),
			'type'        => 'text',
			'inline'      => true,
		];

		*/

		$this->controls['image'] = [
            'tab' => 'content',
            'label' => esc_html__( 'Poster Image', 'bricks' ),
            'type' => 'image',
			'group' => 'poster'
        ];


		/* active styles */

		$activeItem = '&[data-x-item-active]';
		$activePlaying = $activeItem . '[data-x-item-playing]';
		$activePaused = $activeItem . '[data-x-item-paused]';
		$endedItem = '[data-x-item-ended]';

		
		$this->controls['activeSep'] = [
            'tab' => 'content',
            'label' => esc_html__( 'Active', 'bricks' ),
            'type' => 'separator',
			'group' => 'activeStyles'
        ];
	  
		  $this->controls['activeBackgroundColor'] = [
			'tab'    => 'content',
			'group'  => 'activeStyles',
			'type'   => 'color',
			'label'  => esc_html__( 'Background', 'extras' ),
			'css'    => [
			  [
				'property' => 'background-color',
				'selector' => $activeItem
			  ],
			],
		  ];
	  
		  $this->controls['activeBorder'] = [
			'tab'    => 'content',
			'group'  => 'activeStyles',
			'type'   => 'border',
			'label'  => esc_html__( 'Border', 'extras' ),
			'css'    => [
			  [
				'property' => 'border',
				'selector' => $activeItem
			  ],
			],
		  ];
	  
		  $this->controls['activeBoxShadow'] = [
			'tab'    => 'content',
			'group'  => 'activeStyles',
			'label'  => esc_html__( 'Box Shadow', 'extras' ),
			'type'   => 'box-shadow',
			'css'    => [
			  [
				'property' => 'box-shadow',
				'selector' => $activeItem
			  ],
			],
		  ];

		  $this->controls['activeTypography'] = [
			'tab'      => 'content',
			'label'    => esc_html__( 'Typography', 'bricks' ),
			'type'     => 'typography',
			'group' => 'activeStyles',
			'units'    => true,
			'css'      => [
				[
				'property' => 'font',
				'selector' => $activeItem
				],
			],
		];

		$this->controls['activePlayingSep'] = [
            'tab' => 'content',
            'label' => esc_html__( 'Playing', 'bricks' ),
            'type' => 'separator',
			'group' => 'activeStyles'
        ];


		$this->controls['activePlayingBackgroundColor'] = [
			'tab'    => 'content',
			'group'  => 'activeStyles',
			'type'   => 'color',
			'label'  => esc_html__( 'Background', 'extras' ),
			'css'    => [
			  [
				'property' => 'background-color',
				'selector' => $activePlaying
			  ],
			],
		  ];
	  
		  $this->controls['activePlayingBorder'] = [
			'tab'    => 'content',
			'group'  => 'activeStyles',
			'type'   => 'border',
			'label'  => esc_html__( 'Border', 'extras' ),
			'css'    => [
			  [
				'property' => 'border',
				'selector' => $activePlaying
			  ],
			],
		  ];
	  
		  $this->controls['activePlayingBoxShadow'] = [
			'tab'    => 'content',
			'group'  => 'activeStyles',
			'label'  => esc_html__( 'Box Shadow', 'extras' ),
			'type'   => 'box-shadow',
			'css'    => [
			  [
				'property' => 'box-shadow',
				'selector' => $activePlaying
			  ],
			],
		  ];

		  $this->controls['activePlayingTypography'] = [
			'tab'      => 'content',
			'label'    => esc_html__( 'Typography', 'bricks' ),
			'type'     => 'typography',
			'group' => 'activeStyles',
			'units'    => true,
			'css'      => [
				[
				'property' => 'font',
				'selector' => $activePlaying
				],
			],
		];


		$this->controls['activePausedSep'] = [
            'tab' => 'content',
            'label' => esc_html__( 'Paused', 'bricks' ),
            'type' => 'separator',
			'group' => 'activeStyles'
        ];

		$this->controls['activePausedBackgroundColor'] = [
			'tab'    => 'content',
			'group'  => 'activeStyles',
			'type'   => 'color',
			'label'  => esc_html__( 'Background', 'extras' ),
			'css'    => [
			  [
				'property' => 'background-color',
				'selector' => $activePaused
			  ],
			],
		  ];
	  
		  $this->controls['activePausedPlayingBorder'] = [
			'tab'    => 'content',
			'group'  => 'activeStyles',
			'type'   => 'border',
			'label'  => esc_html__( 'Border', 'extras' ),
			'css'    => [
			  [
				'property' => 'border',
				'selector' => $activePaused
			  ],
			],
		  ];
	  
		  $this->controls['activePausedPlayingBoxShadow'] = [
			'tab'    => 'content',
			'group'  => 'activeStyles',
			'label'  => esc_html__( 'Box Shadow', 'extras' ),
			'type'   => 'box-shadow',
			'css'    => [
			  [
				'property' => 'box-shadow',
				'selector' => $activePaused
			  ],
			],
		  ];

		  $this->controls['activePausedPlayingTypography'] = [
			'tab'      => 'content',
			'label'    => esc_html__( 'Typography', 'bricks' ),
			'type'     => 'typography',
			'group' => 'activeStyles',
			'units'    => true,
			'css'      => [
				[
				'property' => 'font',
				'selector' => $activePaused
				],
			],
		];


		  $this->controls['flexWrap'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Flex wrap', 'bricks' ),
			'group'		  => 'layout',
			'tooltip'     => [
				'content'  => 'flex-wrap',
				'position' => 'top-left',
			],
			'type'        => 'select',
			'options'  => [
				'nowrap'       => esc_html__( 'No wrap', 'bricks' ),
				'wrap'         => esc_html__( 'Wrap', 'bricks' ),
				'wrap-reverse' => esc_html__( 'Wrap reverse', 'bricks' ),
			],
			'inline'      => true,
			'css'         => [
				[
					'property' => 'flex-wrap',
					'selector' => '',
				],
			],
			'placeholder' => esc_html__( 'No wrap', 'bricks' ),
		];

		$this->controls['direction'] = [
			'tab'      => 'content',
			'label'    => esc_html__( 'Direction', 'bricks' ),
			'group'		  => 'layout',
			'tooltip'  => [
				'content'  => 'flex-direction',
				'position' => 'top-left',
			],
			'type'     => 'direction',
			'css'      => [
				[
					'property' => 'flex-direction',
					'selector' => '',
				],
			],
			'inline'   => true,
			'rerender' => true,
		];

		$this->controls['justifyContent'] = [
			'tab'      => 'content',
			'label'    => esc_html__( 'Align main axis', 'bricks' ),
			'group'		  => 'layout',
			'tooltip'  => [
				'content'  => 'justify-content',
				'position' => 'top-left',
			],
			'type'     => 'justify-content',
			'css'      => [
				[
					'property' => 'justify-content',
					'selector' => '',
				],
			],
		];

		$this->controls['alignItems'] = [
			'tab'      => 'content',
			'label'    => esc_html__( 'Align cross axis', 'bricks' ),
			'group'		  => 'layout',
			'tooltip'  => [
				'content'  => 'align-items',
				'position' => 'top-left',
			],
			'type'     => 'align-items',
			'css'      => [
				[
					'property' => 'align-items',
					'selector' => '',
				],
			],
		];

		$this->controls['columnGap'] = [
			'tab'      => 'content',
			'label'    => esc_html__( 'Column gap', 'bricks' ),
			'group'		  => 'layout',
			'type'     => 'number',
			'units'    => true,
			'css'      => [
				[
					'property' => 'column-gap',
					'selector' => '',
				],
			],
		];

		$this->controls['rowGap'] = [
			'tab'      => 'content',
			'label'    => esc_html__( 'Row gap', 'bricks' ),
			'group'		  => 'layout',
			'type'     => 'number',
			'units'    => true,
			'css'      => [
				[
					'property' => 'row-gap',
					'selector' => ''
				],
			],
		];



		 /* chapters */

		 $this->controls['hasLoop'] = [
			'tab'   => 'content',
			'label' => esc_html__( 'Add chapters with query loop', 'bricks' ),
			'type'  => 'checkbox',
      		'group' => 'chapters',
		];

		$this->controls['query'] = [
			'tab'      => 'content',
			'label'    => esc_html__( 'Query', 'bricks' ),
			'type'     => 'query',
      		'group' => 'chapters',
			'popup'    => true,
			'inline'   => true,
			'required' => [ 
				[ 'hasLoop', '!=', '' ],
			],
		];

    	$this->controls['chapters'] = [
			'tab'         => 'content',
			'placeholder' => esc_html__( 'Chapter', 'bricks' ),
			'type'        => 'repeater',
      		'group' => 'chapters',
			'fields'      => [
				'text'    => [
					'label' => esc_html__( 'Title', 'bricks' ),
					'type'  => 'text',
          'hasDynamicData' => false,
				],
        	'startTime'    => [
					'label' => esc_html__( 'Start time', 'bricks' ),
					'type'  => 'text',
          'hasDynamicData' => false,
          'inline' => true,
				],
        	'endTime'    => [
					'label' => esc_html__( 'End time', 'bricks' ),
					'type'  => 'text',
          'hasDynamicData' => false,
          'inline' => true,
				],
			],
			'required' => [
				['hasLoop', '!=', true]
			]
		];


    	$this->controls['chapterText'] = [
			'tab'   => 'content',
			'label' => esc_html__( 'Text', 'bricks' ),
			'type'  => 'text',
			'group' => 'chapters',
			'required' => [
				['hasLoop', '=', true]
			]
		];

    		$this->controls['chapterStart'] = [
			'tab'   => 'content',
			'label' => esc_html__( 'Start time', 'bricks' ),
			'type'  => 'text',
			'group' => 'chapters',
			'inline' => true,
			'required' => [
				['hasLoop', '=', true]
			]
		];

    		$this->controls['chapterEnd'] = [
			'tab'   => 'content',
			'label' => esc_html__( 'End time', 'bricks' ),
			'type'  => 'text',
			'group' => 'chapters',
			'inline' => true,
			'required' => [
				['hasLoop', '=', true]
			]
		];

    		$this->controls['chaptersSep'] = [
			'tab'   => 'content',
			'type'  => 'seperator',
      		'group' => 'chapters',
		];


		/* textTracks */

		$this->controls['textTracksSep'] = [
			'group' => 'textTracks',
				  'label'    => esc_html__( 'Subtitles / Captions', 'bricks' ),
			'descriptions'    => esc_html__( 'Here you can add subtitles/captions to the media player.', 'bricks' ),
				  'type'        => 'separator',
			  ];

		$this->controls['textTracks'] = [
			'tab'           => 'content',
			'type'          => 'repeater',
			'group'			=> 'textTracks',
			'label'         => esc_html__( 'Text Tracks', 'bricks' ),
			'titleProperty' => 'label',
			'fields'        => [
				'label'       => [
					'type'    => 'text',
					'label'   => esc_html__( 'Label', 'bricks' ),
					'inline' => true
				],	
				'src'       => [
					'type'    => 'text',
					'label'   => esc_html__( 'Src (.vtt, .srt, .json)', 'bricks' ),
					'inline' => true
				],
				'kind'       => [
					'type'    => 'select',
					'label'   => esc_html__( 'Kind', 'bricks' ),
					'inline' => true,
					'options' => [
						'subtitles' => 'Subtitles',
						'chapters' => 'Chapters',
						'captions' => 'Captions'
					],
				],
				'language' => [
					'type'    => 'text',
					'label'   => esc_html__( 'Language (en, fr, etc.)', 'bricks' ),
					'inline' => true
				],
			],
		];

		/* */

		$this->controls['thumbnailSep'] = [
			'group' => 'textTracks',
				'label'    => esc_html__( 'Thumbnails', 'bricks' ),
				'type'        => 'separator',
			];
	  
		  $this->controls['thumbnailFile'] = [
			'group'			=> 'textTracks',
			'type'    => 'text',
			'label'   => esc_html__( 'Src (.vtt)', 'bricks' ),
			'inline' => true
		  ];

		  $this->controls['multipleSources'] = [
			'group' => 'behaviour',
			'label'    => esc_html__( 'Allow multiple source URLs', 'bricks' ),
			'type'        => 'checkbox',
			'inline'      => true,
			'small' => true,
			'rerender' => false,
			'required' => 
			['sourceType', '!=', 'media'],
		  ];

		  $this->controls['securitySep'] = [
			'tab' => 'content',
			'label' => esc_html__('Bunny Stream Authentication', 'bricks'),
			'group' => 'behaviour',
			'description' => esc_html__('Auto-generate authentication tokens for Bunny Stream videos with CDN Token Authentication enabled (Enable to create expiring video URLs that prevent permanent access and unauthorized sharing)', 'bricks'),
			'type' => 'separator',
			'required' => [['provider', '!=', 'audio']],
		 ];
		 
		  
		  // Add security controls
		  $this->controls['enableSecurity'] = [
			  'tab' => 'content',
			  'group' => 'behaviour',
			  'label' => esc_html__('Generate Authentication Tokens', 'bricksextras'),
			  'info' => esc_html__('Enable if your Bunny Stream has CDN Token Authentication enabled', 'bricksextras'),
			  'type' => 'checkbox',
			  'required' => [['provider', '!=', 'audio']],
		  ];
		
		  $this->controls['securityExpiry'] = [
			  'tab' => 'content',
			  'group' => 'behaviour',
			  'label' => esc_html__('Token Expiry Time (seconds)', 'bricksextras'),
			  'info' => esc_html__('How long the authentication token will be valid', 'bricksextras'),
			  'type' => 'number',
			  'placeholder' => '3600 (1 hour)',
			  'required' => [
				  ['provider', '!=', 'audio'],
				  ['enableSecurity', '=', true],
			  ],
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



  public function get_nestable_children() {
		return [
				[
					'name'     => 'block',
					'settings' => [
						'text' => esc_html__( 'Item description goes here', 'bricks' ),
					],
					'children' => [
					[
					'name'     => 'text-basic',
					'settings' => [
						'text' => esc_html__( 'Item description goes here', 'bricks' ),
					],
					],
				],
			],
			
		];
 	}


 	public function get_normalized_image_settings( $settings ) {

    if ( empty( $settings['image'] ) ) {
        return [
            'id'   => 0,
            'url'  => false,
            'size' => \BRICKS_DEFAULT_IMAGE_SIZE,
        ];
    }

        $image = $settings['image'];

    // Size
        $image['size'] = empty( $image['size'] ) ? \BRICKS_DEFAULT_IMAGE_SIZE : $settings['image']['size'];

        // Image ID or URL from dynamic data
        if ( ! empty( $image['useDynamicData'] ) ) {

            $images = $this->render_dynamic_data_tag( $image['useDynamicData'], 'image', [ 'size' => $image['size'] ] );

            if ( ! empty( $images[0] ) ) {
                if ( is_numeric( $images[0] ) ) {
                    $image['id'] = $images[0];
                } else {
                    $image['url'] = $images[0];
                }
            }
        }

        $image['id'] = empty( $image['id'] ) ? 0 : $image['id'];

        // If External URL, $image['url'] is already set
        if ( ! isset( $image['url'] ) ) {
            $image['url'] = ! empty( $image['id'] ) ? wp_get_attachment_image_url( $image['id'], $image['size'] ) : false;
        }

        return $image;

    }


  public function render() {

	$settings = $this->settings;

    // Get the provider type (audio or video)
    $provider = isset($settings['provider']) ? esc_attr($settings['provider']) : 'video';
    
    // Define default sources based on provider type
    $defaultAudioSrc = 'https://files.vidstack.io/sprite-fight/audio.mp3';
    $defaultVideoSrc = 'https://files.vidstack.io/sprite-fight/hls/stream.m3u8';
    
    // Get the source type (media library or URL)
    $sourceType = isset($settings['sourceType']) ? $settings['sourceType'] : 'url';
    
    // Set a flag to track if we've found a valid source
    $sourceFound = false;
    $src = '';
    
    // Get the original source URL based on source type
    if ($sourceType === 'media') {
        if ($provider === 'audio' && isset($settings['audio']['url']) && !empty($settings['audio']['url'])) {
            // Get URL from audio setting when provider is audio
            $src = esc_url($settings['audio']['url']);
            $sourceFound = true;
        } elseif ($provider !== 'audio' && isset($settings['media']['url']) && !empty($settings['media']['url'])) {
            // Get URL from media setting when provider is video
            $src = esc_url($settings['media']['url']);
            $sourceFound = true;
        }
    } elseif (isset($settings['src']) && !empty($settings['src'])) {
        // Get URL from src field
        $srcSetting = $settings['src'];
        $src = strstr($srcSetting, '{') ? $this->render_dynamic_data_tag($srcSetting, 'text') : $srcSetting;
        $sourceFound = true;
    }
    
    // If no source was found, use the default source based on provider type
    if (!$sourceFound) {
        $src = ($provider === 'audio') ? $defaultAudioSrc : $defaultVideoSrc;
    } 
    
    // Check if we need to secure the video URL
    if (class_exists('\BricksExtras\BricksExtrasVideo')) {
        $src = apply_filters('bricksextras/video/src', $src, $settings);
    }

	if (isset($settings['multipleSources']) && $sourceType === 'url') {
      
		$additionalSources = [];

		if ('video' === $provider) {
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
	 	} else {
			if (isset($settings['srcRepeaterAudio']) && is_array($settings['srcRepeaterAudio'])) {
				foreach ($settings['srcRepeaterAudio'] as $item) {
					$source = [];
					
					if (isset($item['source'])) $source['src'] = $item['source'];
					if (isset($item['type'])) $source['type'] = $item['type'];
					
					$additionalSources[] = $source;
				}
			}
		}
		

		$additional_sources_json = wp_json_encode($additionalSources);
  
	  
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

	$title = isset($settings['title']) ? bricks_render_dynamic_data($settings['title']) : false;
	$artist = isset($settings['artist']) ? bricks_render_dynamic_data($settings['artist']) : false;

	$image  = $this->get_normalized_image_settings($this->settings);
    $image_id   = $image['id'];
    $image_url  = $image['url'];
    $image_size = $image['size'];

	$textTracks = !empty($settings['textTracks']) ? $settings['textTracks'] : false;

	$clipStart = isset($settings['clipStart']) ? $settings['clipStart'] : false;
	$clipEnd = isset($settings['clipEnd']) ? $settings['clipEnd'] : false;

	$clipStartTime = $clipStart ? \BricksExtras\Helpers::timeToSeconds($this->render_dynamic_data($clipStart)) : false;
	$clipEndTime = $clipEnd ? \BricksExtras\Helpers::timeToSeconds($this->render_dynamic_data($clipEnd)) : false;

	$thumbnailFile = isset($settings['thumbnailFile']) ? esc_attr($settings['thumbnailFile']) : false;

	$this->set_attribute('x-media-player-playlist_inner', 'class', 'x-media-player-playlist_inner'); 

	$this->set_attribute('_root', 'data-x-item-paused', 'true');

	if (!empty($src)) {
		$this->set_attribute('_root', 'data-x-src', esc_url($src)); 
	}

	if (!!$title) {
		$this->set_attribute( '_root', 'data-x-title', esc_attr( $title ) );
	}

	if (!!$artist && 'audio' === $provider) {
		$this->set_attribute( '_root', 'data-x-artist', esc_attr( $artist ) ); 
	}
	
	if ($image_url) {
		$this->set_attribute( '_root', 'data-x-poster', esc_url( $image_url ) );
	}

	if ($clipStartTime) {
		$this->set_attribute( '_root', 'data-x-clip-start', esc_attr( $clipStartTime ) );
	}

	if ($clipEndTime) {
		$this->set_attribute( '_root', 'data-x-clip-end', esc_attr( $clipEndTime ) );
	}

	if ($thumbnailFile) {
		$this->set_attribute( '_root', 'data-x-thumbnails', esc_url( $thumbnailFile ) );
	}

	 
	 /* find if poster image has been saved */

	 if ( get_option( 'bricksextras_media_poster_url') ) {

		$savedPosterImages = json_decode( get_option( 'bricksextras_media_poster_url'), TRUE);
		$videoID = \BricksExtras\Helpers::get_video_id_from_url( $src );

		if (str_contains($src, 'youtu') || str_contains($src, 'vimeo')) {
  
			if ($savedPosterImages) {
	
			if (array_key_exists($videoID, $savedPosterImages)) {
					if (!str_contains($savedPosterImages[$videoID], 'null')) {

						$thumbnail_dir_info = wp_get_upload_dir();
						$newVideoURL = esc_url( $thumbnail_dir_info['baseurl'] . '/bricksextras/posters/' . $savedPosterImages[$videoID] );
						$this->set_attribute( '_root', 'data-x-local-poster', esc_attr( $newVideoURL ) );
						
					}
				}
	
			}

		}
  
	  }



	$textTrackConfig = [];

	if ( $textTracks ) {		

		foreach ( $textTracks as $index => $textTrack ) {

			$label = ! empty( $textTrack['label'] ) ? $this->render_dynamic_data( $textTrack['label'] ) : 'English';
			$src = ! empty( $textTrack['src'] ) ? $this->render_dynamic_data( $textTrack['src'] ) : 'https://media-files.vidstack.io/sprite-fight/subs/english.vtt';
			$kind = ! empty( $textTrack['kind'] ) ? $textTrack['kind'] : 'subtitles';
			$language = ! empty( $textTrack['language'] ) ? $textTrack['language'] : 'English';

			$default = 0 === $index ? 'default' : '';

			$textTrackConfig[] = [
				'label' => $label,
				'src'	=> $src,
				'kind'	=> $kind,
				'language' => $language
			];

		}

	}

	if (count($textTrackConfig) !== 0) {
		$this->set_attribute( '_root', 'data-x-texttracks', wp_json_encode( $textTrackConfig ) );
	}


    

    /* chapters from dynamic data */

	$chapters = ! empty( $this->settings['chapters'] ) ? $this->settings['chapters'] : false;
    $chapterText = isset( $settings['chapterText'] ) ? esc_attr( $settings['chapterText'] ) : '';
    $chapterStart = isset( $settings['chapterStart'] ) ? esc_attr( $settings['chapterStart'] ) : '';
    $chapterEnd = isset( $settings['chapterEnd'] ) ? esc_attr( $settings['chapterEnd'] ) : '';

	$chaptersArray = [];

     // Query Loop
	if ( isset( $this->settings['hasLoop'] ) ) {

        $query = new \Bricks\Query( [
					'id'       => $this->id,
					'settings' => $settings,
				] );

				
			$chapterText = isset( $this->settings['chapterText'] ) ? $this->settings['chapterText'] : '';
			$chapterStart = isset( $this->settings['chapterStart'] ) ? $this->settings['chapterStart'] : '';
			$chapterEnd = isset( $this->settings['chapterEnd'] ) ? $this->settings['chapterEnd'] : '';

			$chaptersArray = $query->render( [ $this, 'chapter_repeater_item' ], [ $chapterText, $chapterStart, $chapterEnd ], true );

			$query->destroy();
			unset( $query );

		}
      
      else {

        if ( !!$chapters ) {

          foreach ( $this->settings['chapters'] as $index => $chapter ) {

            $chapterText = isset( $chapter['text'] ) ? esc_attr( $chapter['text'] ) : '';
            $chapterStart = isset( $chapter['startTime'] ) ? esc_attr( $chapter['startTime'] ) : '';
            $chapterEnd = isset( $chapter['endTime'] ) ? esc_attr( $chapter['endTime'] ) : '';

            $chapterData = [
              'text' => $chapterText,
              'startTime' => \BricksExtras\Helpers::timeToSeconds( $this->render_dynamic_data( $chapterStart ) ),
              'endTime' => \BricksExtras\Helpers::timeToSeconds( $this->render_dynamic_data( $chapterEnd ) )
            ];

            array_push( $chaptersArray, $chapterData );

        }

      }

     }

	 // Filter the chapters array to only keep valid chapter objects
	 $chaptersArray = array_filter($chaptersArray, function($item) {
		// Only keep items that are arrays (objects) with the expected chapter structure
		return is_array($item) && 
			   isset($item['text']) && 
			   isset($item['startTime']) && 
			   isset($item['endTime']);
	});
	
	// Re-index the array to ensure sequential numeric keys
	$chaptersArray = array_values($chaptersArray);
	
	
	/* ignore if less than 2 chapters */
	if( 2 <= count( $chaptersArray ) ) {
	 $this->set_attribute( '_root', 'data-x-chapters', json_encode($chaptersArray) );
	}

	// Generate and set a unique identifier for this instance
	$indentifier = \BricksExtras\Helpers::set_identifier_attribute( $this );

	$this->set_attribute( '_root', 'aria-current', 'false' );
	

    echo "<button {$this->render_attributes( '_root' )}>";

	echo \Bricks\Frontend::render_children( $this );

	echo "</button>";
	
    
  }





  public function chapter_repeater_item( $chapterText,$chapterStart,$chapterEnd) {

		$settings = $this->settings;
		$index    = $this->loop_index;

		
		// Render
		ob_start();

		$startTime = \BricksExtras\Helpers::timeToSeconds( $this->render_dynamic_data( $chapterStart ) );
		$endTime = \BricksExtras\Helpers::timeToSeconds( $this->render_dynamic_data( $chapterEnd ) );

		$chapterData = [
		'text' => $this->render_dynamic_data( $chapterText ),
		'startTime' => $startTime,
		'endTime' => $endTime
		];

		$html = ob_get_clean();
	
		$this->loop_index++;

		return $chapterData;
  
	}

	public static function render_builder() {  

		?>

			<script type="text/x-template" id="tmpl-bricks-element-xmediaplaylist">
				<button>
				<bricks-element-children 
							:element="element" 
				/>
	</button>	
			</script>

	<?php 

}
  
	

}