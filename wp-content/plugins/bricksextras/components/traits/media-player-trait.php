<?php
/**
 * Media Player Trait
 * 
 * Shared functionality for media player components.
 * 
 * @package BricksExtras
 * @since 1.0.0
 */

namespace BricksExtras\Components\Traits;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Media Player Trait
 * 
 * Contains shared functionality for both video and audio media player components.
 * This includes chapter handling, controls configuration, and other shared features.
 */
trait Media_Player_Trait {

    /**
     * Static property to track if script has been localized
     */
    protected static $script_localized = false;
   
    /**
     * Determine if we're in the Gutenberg editor
     *
     * @return bool True if in Gutenberg editor, false otherwise
     */
    protected function is_gutenberg_editor() {
        $is_gutenberg = false;
        if (defined('REST_REQUEST') && REST_REQUEST) {
            $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            $is_gutenberg = strpos($referer, 'post.php') !== false || 
                            strpos($referer, 'post-new.php') !== false;
        }
        return $is_gutenberg;
    }

    /**
     * Get media source URL
     *
     * Determines the source URL for the media player based on settings
     * and provider type. Handles both video and audio sources.
     *
     * @param array $settings Element settings
     * @param string $provider Provider type ('video' or 'audio')
     * @return string The source URL for the media player
     */
    protected function get_media_source($settings, $provider) {

        // Define default sources based on provider type
        $defaultSrc = ($provider === 'video') ? 'https://files.vidstack.io/sprite-fight/720p.mp4' : (isset( $settings['playlistMode'] ) ? '' : 'https://files.vidstack.io/sprite-fight/audio.mp3');
        
        // Get the source type (media library or URL) 
        $sourceType = isset($settings['sourceType']) ? $settings['sourceType'] : 'url';
        
        // Set a flag to track if we've found a valid source
        $sourceFound = false;

        if ('video' === $provider) {
          
             // Get the original source URL based on source type
            if ($sourceType === 'media' && !isset($settings['playlistMode'])) {
                if (isset($settings['media']['url']) && !empty($settings['media']['url'])) {
                    // Get URL from media setting
                    $original_src = esc_url($settings['media']['url']);
                    $sourceFound = true;
                }
            } elseif (isset($settings['src']) && !empty($settings['src']) && !isset($settings['playlistMode'])) {
                // Get URL from src field
                $original_src = esc_url( bricks_render_dynamic_data($settings['src']) );
                $sourceFound = true;
            }

        } else {

             // Get the original source URL based on source type
             if ($sourceType === 'media' && !isset($settings['playlistMode'])) {
                if (isset($settings['audio']['url']) && !empty($settings['audio']['url'])) {
                    // Get URL from audio setting
                    $original_src = esc_url($settings['audio']['url']);
                    $sourceFound = true;
                }
            } elseif (isset($settings['src']) && !empty($settings['src']) && !isset($settings['playlistMode'])) {
                // Get URL from src field
                $original_src = esc_url(bricks_render_dynamic_data($settings['src']));
                $sourceFound = true;
            }

        }
        
       
        
        // If no source was found, use the default source based on provider type
        if (!$sourceFound) {
            $original_src = $defaultSrc;
        }
        
        // Check if we need to secure the video URL
        if (class_exists('\BricksExtras\BricksExtrasVideo')) {
            $src = apply_filters('bricksextras/video/src', $original_src, $settings);
        } else {
            $src = $original_src;
        }
        
        return $src;
    }
    
    /**
     * Set common controls for media players
     * 
     * This method sets up the common CSS controls that are shared between
     * both video and audio media player components.
     */
    public function set_common_controls() {
        // Set common CSS selectors for controls
        if (isset($this->controls['_justifyContent'])) {
            $this->controls['_justifyContent']['css'][0]['selector'] = '.xmp-controls';
        }
        if (isset($this->controls['_alignItems'])) {
            $this->controls['_alignItems']['css'][0]['selector'] = '.xmp-controls';
        }
        if (isset($this->controls['_gap'])) {
            $this->controls['_gap']['css'][0]['selector'] = '.xmp-controls';
        }
        if (isset($this->controls['_flexDirection'])) {
            $this->controls['_flexDirection']['css'][0]['selector'] = '.xmp-controls';
        }
    }

    /**
     * Get shared variables for media player controls
     * 
     * This function returns an array of shared control variables used by both
     * audio and video media player components.
     * 
     * @return array Array of shared control variables
     */
    protected function get_media_player_shared_variables() {
      $activeControls = [
        '',
        'play',
        'play-large',
        'pip',
        'fullscreen',
        'captions'
      ];
      
      $inactiveControls = [
        '',
        'play',
        'play-large',
        'seek-forward',
        'seek-backward',
        'pip',
        'download',
        'fullscreen',
        'settings',
        'captions'
      ];
      
      $placementOptions = [
        'top' => esc_html__('Top', 'bricks' ), 
        'bottom' => esc_html__( 'Bottom', 'bricks' ), 
        'top-start' => esc_html__( 'Top Start', 'bricks' ), 
        'top-end' => esc_html__( 'Top End', 'bricks' ),
        'bottom-start' => esc_html__( 'Bottom Start', 'bricks' ), 
        'bottom-end' => esc_html__( 'Bottom End', 'bricks' ),
      ];
      
      $optionControls = 
      [
        'artist' => 'Artist (audio player)',
        'captions' => 'Caption Toggle *',
        'chapters' => 'Chapters Menu *',
        'chapter-title' => 'Chapter Title *',
        'custom-text' => 'Custom Text',
        'download' => 'Download (audio player) *',
        'fullscreen' => 'Fullscreen *',
        'image' => 'Image',
        'next' => 'Next item (playlist)',
        'play'   => 'Play / Pause',
        'play-large'  => 'Play / Pause (large)',
        'poster-image' => 'Poster Image',
        'previous' => 'Previous item (playlist)',
        'seek-backward' => 'Seek Backward',
        'seek-forward' => 'Seek Forward',
        'pip' => 'PIP *',
        'settings' => 'Settings Menu',
        'spacer' => 'Spacer',
        'title' => 'Title',
        'time'  => 'Time',
        'time-slider' => 'Time Slider',
        'mute' => 'Volume / Mute',
        'live-button' => 'Live Button'
      ];
      
      $controlFields = [
        'control'    => [
          'label'     => esc_html__( 'Control', 'bricks' ),
          'type'      => 'select',
          'clearable' => false,
          'searchable' => true,
          'inline'    => true,
          'options'   => $optionControls
        ],
        'image' => [
          'tab' => 'content',
          'label' => esc_html__( 'Image', 'bricks' ),
          'type' => 'image',
          'required'  => ['control', '=', ['image']],
        ],
        'altText' => [
          'tab'      => 'content',
          'label'    => esc_html__( 'Custom alt text', 'bricks' ),
          'type'     => 'text',
          'inline'   => true,
          'rerender' => false,
          'required'  => ['control', '=', ['image']],
        ],
        'playLabel'    => [
          'label'     => esc_html__( 'Play label', 'bricks' ),
          'type'      => 'text',
          'required'  => ['control', '=', ['play','play-large']],
          'placeholder' => 'Play',
          'inline'    => true
        ],
        'pauseLabel'    => [
          'label'     => esc_html__( 'Pause label', 'bricks' ),
          'type'      => 'text',
          'required'  => ['control', '=', ['play','play-large']],
          'inline'    => true,
          'placeholder' => 'Pause'
        ],
        'downloadLabel'    => [
          'label'     => esc_html__('Download Label', 'bricks' ),
          'type'      => 'text',
          'required'  => ['control', '=', ['download']],
          'placeholder' => 'Download',
          'inline'    => true
        ],
        'downloadSource'    => [
          'label'     => esc_html__('Download Source', 'bricks' ),
          'description' => esc_html__('Select the source for the URL to download', 'bricks' ),
          'type'      => 'select',
          'required'  => ['control', '=', ['download']],
          'placeholder' => esc_html__('Media Source URL', 'bricks' ),
          'options'   => [
            'source' => esc_html__('Media Source URL', 'bricks' ),
            'download-option' => esc_html__('Download Option', 'bricks' ),
          ]
        ], 
        'downloadOptionId'    => [
          'label'     => esc_html__('Download Option ID', 'bricks' ),
          'type'      => 'text',
          'required'  => [
            ['downloadSource', '=', ['download-option']],
            ['control', '=', ['download']]
          ],
          'placeholder' => '',
          'description' => esc_html__('Enter the ID of the download option (from Downloads tab)', 'bricks' ),
        ],
        'downloadURLAttribute'    => [
          'label'     => esc_html__('Download URL Attribute', 'bricks' ),
          'type'      => 'text',
          'required'  => [
            ['downloadSource', '=', ['data-attribute']],
            ['control', '=', ['download']]
          ],
          'placeholder' => 'data-your-attribute'
        ],
        'downloadURLFileName'    => [
          'label'     => esc_html__('File Name Attribute', 'bricks' ),
          'type'      => 'text',
          'required'  => [
            ['downloadSource', '=', ['data-attribute']],
            ['control', '=', ['download']]
          ],
          'placeholder' => 'data-your-attribute'
        ],
        'captionsOnLabel'    => [
          'label'     => esc_html__('Captions On Label', 'bricks' ),
          'type'      => 'text',
          'required'  => ['control', '=', ['captions']],
          'placeholder' => 'Closed-Captions On',
          'inline'    => true
        ],
        'previousLabel'    => [
          'label'     => esc_html__('Previous label', 'bricks' ),
          'type'      => 'text',
          'required'  => ['control', '=', ['previous']],
          'placeholder' => 'Previous',
          'inline'    => true
        ],
        'nextLabel'    => [
          'label'     => esc_html__('Next label', 'bricks' ),
          'type'      => 'text',
          'required'  => ['control', '=', ['next']],
          'placeholder' => 'Next',
          'inline'    => true
        ],
        'captionsOffLabel'    => [
          'label'     => esc_html__('Captions Off Label', 'bricks' ),
          'type'      => 'text',
          'required'  => ['control', '=', ['captions']],
          'placeholder' => 'Closed-Captions Off',
          'inline'    => true
        ],
        'seekAmountBack'    => [
          'label'     => esc_html__( 'Seek Distance', 'bricks' ),
          'type'      => 'select',
          'inline'    => true,
          'options'   => [
            '-10' => '-10',
            '-15' => '-15',
            '-30' => '-30',
          ],
          'required'  => ['control', '=', 'seek-backward'],
          'placeholder' => '-10'
        ],
        'seekLabelBack'    => [
          'label'     => esc_html__( 'Seek Label', 'bricks' ),
          'type'      => 'text',
          'inline'    => true,
          'required'  => ['control', '=', 'seek-backward'],
          'placeholder' => 'Rewind'
        ],
        'seekAmountForward'    => [
          'label'     => esc_html__( 'Seek Distance', 'bricks' ),
          'type'      => 'select',
          'inline'    => true,
          'options'   => [
            '+10' => '+10',
            '+15' => '+15',
            '+30' => '+30',
          ],
          'required'  => ['control', '=', 'seek-forward'],
          'placeholder' => '+10'
        ],
        'seekLabelForward'    => [
          'label'     => esc_html__( 'Seek Label', 'bricks' ),
          'type'      => 'text',
          'inline'    => true,
          'required'  => ['control', '=', 'seek-forward'],
          'placeholder' => 'Forward'
        ],
        'volumeSlider'    => [
          'label'     => esc_html__( 'Volume slider', 'bricks' ),
          'type'      => 'select',
          'options'   => [
            'focus' => esc_html__( 'Enable (visible on focus)', 'bricks' ),
            'visible' => esc_html__( 'Enable (always visible)', 'bricks' ),
            'disable' => esc_html__( 'Disable', 'bricks' ),
          ],
          'inline'    => true,
          'placeholder' => esc_html__( 'Disable', 'bricks' ),
          'required'  => ['control', '=', 'mute'],
        ],
        'muteLabel'    => [
          'label'     => esc_html__( 'Mute Label', 'bricks' ),
          'type'      => 'text',
          'required'  => ['control', '=', 'mute'],
          'inline'    => true,
          'placeholder' => 'Mute'
        ],
        'unmuteLabel'    => [
          'label'     => esc_html__( 'Unmute Label', 'bricks' ),
          'type'      => 'text',
          'required'  => ['control', '=', 'mute'],
          'inline'    => true,
          'placeholder' => 'Unmute'
        ],
        'currentTime'    => [
          'label'     => esc_html__( 'Current time', 'bricks' ),
          'type'      => 'select',
          'options'   => [
            'enable' => esc_html__( 'Enable', 'bricks' ),
            'disable' => esc_html__( 'Disable', 'bricks' ),
            'remainder' => esc_html__( 'Show remaining time', 'bricks' ),
          ],
          'inline'    => true,
          'required'  => ['control', '=', 'time'],
          'placeholder' => esc_html__( 'Enable', 'bricks' ),
        ],
        'timeDivider'    => [
          'label'     => esc_html__( 'Divider', 'bricks' ),
          'type'      => 'select',
          'inline'    => true,
          'options'   => [
            'enable' => esc_html__( 'Enable', 'bricks' ),
            'disable' => esc_html__( 'Disable', 'bricks' ),
          ],
          'required'  => ['control', '=', 'time'],
          'placeholder' => esc_html__( 'Enable', 'bricks' ),
        ],
        'timeDividerText'    => [
          'label'     => esc_html__( 'Divider', 'bricks' ),
          'type'      => 'text',
          'inline'    => true,
          'required'  => [
            ['control', '=', 'time'],
            ['timeDivider', '!=', 'disable'],
          ],
          'placeholder' => '/',
        ],
        'duration'    => [
          'label'     => esc_html__( 'Duration', 'bricks' ),
          'type'      => 'select',
          'inline'    => true,
          'options'   => [
            'enable' => esc_html__( 'Enable', 'bricks' ),
            'disable' => esc_html__( 'Disable', 'bricks' ),
          ],
          'required'  => ['control', '=', 'time'],
          'placeholder' => esc_html__( 'Enable', 'bricks' ),
        ],
        
        'enterPipLabel'    => [
          'label'     => esc_html__( 'Enter PIP Label', 'bricks' ),
          'type'      => 'text',
          'inline'    => true,
          'required'  => ['control', '=', 'pip'],
          'placeholder' => esc_html__( 'Enter PIP' )
        ],
        'exitPipLabel'    => [
          'label'     => esc_html__( 'Exit PIP Label', 'bricks' ),
          'type'      => 'text',
          'inline'    => true,
          'required'  => ['control', '=', 'pip'],
          'placeholder' => esc_html__( 'Exit PIP' )
        ],
        
        'enterFullscreenLabel'    => [
          'label'     => esc_html__( 'Enter Fullscreen Label', 'bricks' ),
          'type'      => 'text',
          'inline'    => true,
          'required'  => ['control', '=', 'fullscreen'],
          'placeholder' => esc_html__( 'Enter Fullscreen' )
        ],
        'exitFullscreenLabel'    => [
          'label'     => esc_html__( 'Exit Fullscreen Label', 'bricks' ),
          'type'      => 'text',
          'inline'    => true,
          'required'  => ['control', '=', 'fullscreen'],
          'placeholder' => esc_html__( 'Exit Fullscreen' )
        ],
        'settingsLabel'    => [
          'label'     => esc_html__( 'Settings Label', 'bricks' ),
          'type'      => 'text',
          'required'  => ['control', '=', 'settings'],
          'inline'    => true,
          'placeholder' => esc_html__( 'Settings' )
        ],
        'speedLabel'    => [
          'label'     => esc_html__( 'Playback Label', 'bricks' ),
          'type'      => 'text',
          'required'  => ['control', '=', 'settings'],
          'inline'    => true,
          'placeholder' => esc_html__( 'Playback' )
        ],
        'qualityLabel'    => [
          'label'     => esc_html__( 'Quality Label', 'bricks' ),
          'type'      => 'text',
          'required'  => ['control', '=', 'settings'],
          'inline'    => true,
          'placeholder' => esc_html__( 'Quality' )
        ],
        'accessibilityLabel'    => [
          'label'     => esc_html__( 'Accessibility Label', 'bricks' ),
          'type'      => 'text',
          'required'  => ['control', '=', 'settings'],
          'inline'    => true,
          'placeholder' => esc_html__( 'Accessibility' )
        ],
        'announcementsLabel'    => [
          'label'     => esc_html__( 'Announcements Label', 'bricks' ),
          'type'      => 'text',
          'required'  => ['control', '=', 'settings'],
          'inline'    => true,
          'placeholder' => esc_html__( 'Announcements' )
        ],
        'captionsLabel'    => [
          'label'     => esc_html__( 'Captions Label', 'bricks' ),
          'type'      => 'text',
          'required'  => ['control', '=', 'settings'],
          'inline'    => true,
          'placeholder' => esc_html__( 'Captions' )
        ], 
        'captionsOffHint'    => [
          'label'     => esc_html__( 'Captions "Off" Option Label', 'bricks' ),
          'type'      => 'text',
          'required'  => ['control', '=', 'settings'],
          'inline'    => true,
          'placeholder' => esc_html__( 'Off' )
        ],
        'loopLabel'    => [
          'label'     => esc_html__( 'Loop Label', 'bricks' ),
          'type'      => 'text',
          'required'  => ['control', '=', 'settings'],
          'inline'    => true,
          'placeholder' => esc_html__( 'Loop' )
        ],
        'chaptersLabel'    => [
          'label'     => esc_html__( 'Chapters Label', 'bricks' ),
          'type'      => 'text',
          'required'  => ['control', '=', 'chapters'],
          'inline'    => true,
          'placeholder' => esc_html__( 'Chapters' )
        ],
        'settingsPlacement'    => [
          'label'     => esc_html__( 'Placement', 'bricks' ),
          'type'      => 'select',
          'options' => $placementOptions,
          'required'  => ['control', '=', ['settings','chapters']],
          'inline'    => true,
          'placeholder' => 'Auto'
        ],
        'menuXOffset' => [
            'tab'    => 'content',
            'type'   => 'number',
            'units'   => false,
            'label'  => esc_html__( 'Menu X Offset', 'extras' ),
            'placeholder' => '0',
            'required'  => ['control', '=', ['chapters','settings']],
        ],
        'menuYOffset' => [
            'tab'    => 'content',
            'type'   => 'number',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Menu Y Offset', 'extras' ),
            'placeholder' => '20',
            'required'  => ['control', '=', ['chapters','settings']],
        ],
       
        'visibility'    => [
          'label'     => esc_html__( 'Visibility', 'bricks' ),
          'type'      => 'select',
          'options'   => [
            'default' => esc_html__( 'Default', 'bricks' ),
            'first'  => esc_html__( 'Only visible before first play', 'bricks' ),
            'play'  => esc_html__( 'Only visible after first play', 'bricks' ),
            'loaded' => esc_html__( 'Only visible when player ready to play', 'bricks' ),
            'before_loaded' => esc_html__( 'Only visible before player ready to play', 'bricks' ),
          ],
          'required'  => ['control', '!=', 'spacer'],
          'placeholder' => esc_html__( 'Default', 'bricks' )
        ],
        'tooltipPlacement'    => [
          'label'     => esc_html__( 'Tooltip Placement', 'bricks' ),
          'type'      => 'select',
          'inline'    => true,
          'options' => $placementOptions,
          'required'  => ['control', '!=', ['spacer','title','custom-text','image','live-button','time','time-slider'] ],
          'placeholder' => esc_html__( 'Default', 'bricks' )
        ],
        'text'    => [
          'label'     => esc_html__( 'Text', 'bricks' ),
          'type'      => 'text',
          'required'  => ['control', '=', 'custom-text'],
          'inline'    => true,
          'placeholder' => ''
        ],
        'artist_prefix'    => [
          'label'     => esc_html__( 'Prefix', 'bricks' ),
          'type'      => 'text',
          'required'  => ['control', '=', 'artist'],
          'inline'    => true,
          'placeholder' => ''
        ],
        'artist_suffix'    => [
          'label'     => esc_html__( 'Suffix', 'bricks' ),
          'type'      => 'text',
          'required'  => ['control', '=', 'artist'],
          'inline'    => true,
          'placeholder' => ''
        ],
        'inactiveIconSep' => [
          'tab'      => 'content',
          'label'    => esc_html__( 'Text', 'bricks' ),
          'type'     => 'text',
          'inline'   => true,
          'required'  => ['control', '=', ['custom-text']],
        ],
        'inactiveIconSep' => [
          'tab'      => 'content',
          'group' => 'buttonIcons',
          'label'    => esc_html__( 'Icon', 'bricks' ),
          'type'     => 'separator',
          'required'  => ['control', '=', $inactiveControls],
        ],
       'inactiveIcon' => [
          'tab'      => 'content',
          'group' => 'buttonIcons',
          'label'    => esc_html__( 'Icon', 'bricks' ),
          'type'     => 'icon',
          'css'      => [
            [
              'selector' => '.xmp-icon.xmp-icon-custom-inactive', 
            ],
          ],
          'required'  => ['control', '=', $inactiveControls],
        ],
        'activeIconSep' => [
          'tab'      => 'content',
          'group' => 'buttonIcons',
          'label'    => esc_html__( 'Icon - active state', 'bricks' ),
          'type'     => 'separator',
          'required'  => ['control', '=', $activeControls],
        ],
        'activeIcon' => [
          'tab'      => 'content',
          'group' => 'buttonIcons',
          'label'    => esc_html__( 'Icon', 'bricks' ),
          'type'     => 'icon',
          'css'      => [
            [
              'selector' => '.xmp-icon.xmp-icon-custom-active',
            ],
          ],
          'required'  => ['control', '=', $activeControls],
        ],
        'replayIconSep' => [
          'tab'      => 'content',
          'group' => 'buttonIcons', 
          'label'    => esc_html__( 'Icon - replay state', 'bricks' ),
          'type'     => 'separator',
          'required'  => ['control', '=', ['play','play-large']],
        ],
        'replayIcon' => [
          'tab'      => 'content',
          'group' => 'buttonIcons',
          'label'    => esc_html__( 'Icon', 'bricks' ),
          'type'     => 'icon',
          'css'      => [
            [
              'selector' => '.xmp-icon.xmp-icon-custom-active',
            ],
          ],
          'required'  => ['control', '=', ['play','play-large']],
        ],
        /* mute icons */

        'muteIconSep' => [
          'tab'      => 'content',
          'group' => 'buttonIcons',
          'label'    => esc_html__( 'Icon - muted', 'bricks' ),
          'type'     => 'separator',
          'required'  => ['control', '=', 'mute'],
        ],
       'muteIcon' => [
          'tab'      => 'content',
          'group' => 'buttonIcons',
          'label'    => esc_html__( 'Icon', 'bricks' ),
          'type'     => 'icon',
          'css'      => [
            [
              'selector' => '.xmp-icon.xmp-mute-icon',
            ],
          ],
          'required'  => ['control', '=', 'mute'],
        ],
        'highVolumeIconSep' => [
          'tab'      => 'content',
          'group' => 'buttonIcons',
          'label'    => esc_html__( 'Icon - high volume', 'bricks' ),
          'type'     => 'separator',
          'required'  => ['control', '=', 'mute'],
        ],
        'highVolumeIcon' => [
          'tab'      => 'content',
          'group' => 'buttonIcons',
          'label'    => esc_html__( 'Icon', 'bricks' ),
          'type'     => 'icon',
          'css'      => [
            [
              'selector' => '.xmp-icon.xmp-volume-high-icon',
            ],
          ],
          'required'  => ['control', '=', 'mute'],
        ],
        'lowVolumeIconSep' => [
          'tab'      => 'content',
          'group' => 'buttonIcons',
          'label'    => esc_html__( 'Icon - low volume', 'bricks' ),
          'type'     => 'separator',
          'required'  => ['control', '=', 'mute'],
        ],
        'lowVolumeIcon' => [
          'tab'      => 'content',
          'group' => 'buttonIcons',
          'label'    => esc_html__( 'Icon', 'bricks' ),
          'type'     => 'icon',
          'css'      => [
            [
              'selector' => '.xmp-icon.xmp-volume-low-icon',
            ],
          ],
          'required'  => ['control', '=', 'mute'],
        ],

        'settingsSpeedIcon' => [
          'tab'      => 'content',
          'group' => 'buttonIcons',
          'label'    => esc_html__( 'Playback setting Icon', 'bricks' ),
          'type'     => 'icon',
          'css'      => [
            [
              'selector' => '.xmp-playback-menu .xmp-menu-item-icon',
            ],
          ],
          'required'  => ['control', '=', 'settings'],
        ],
        'settingsChaptersIcon' => [
          'tab'      => 'content',
          'group' => 'buttonIcons',
          'label'    => esc_html__( 'Chapters setting icon', 'bricks' ),
          'type'     => 'icon',
          'css'      => [
            [
              'selector' => '.xmp-icon.xmp-active-icon',
            ],
          ],
          'required'  => ['control', '=', 'chapters'],
        ],
        'settingsAccessibilityIcon' => [
          'tab'      => 'content',
          'group' => 'buttonIcons',
          'label'    => esc_html__( 'Accessibility setting icon', 'bricks' ),
          'type'     => 'icon',
          'css'      => [
            [
              'selector' => '.xmp-accessibility-menu .xmp-menu-item-icon',
            ],
          ],
          'required'  => ['control', '=', 'settings'],
        ],
        'settingsCaptionsIcon' => [
          'tab'      => 'content',
          'group' => 'buttonIcons',
          'label'    => esc_html__( 'Captions setting icon', 'bricks' ),
          'type'     => 'icon',
          'css'      => [
            [
              'selector' => '.xmp-captions-menu .xmp-menu-item-icon',
            ],
          ],
          'required'  => ['control', '=', 'settings'],
        ],
      ];

      
      return [
        'activeControls' => $activeControls,
        'inactiveControls' => $inactiveControls,
        'placementOptions' => $placementOptions,
        'optionControls' => $optionControls,
        'controlFields' => $controlFields,
      ];
    }

    /**
     * Get media player controls
     * 
     * @param string $mediaType The type of media player (video or audio)
     * @return array Array of control definitions
     */
    
    /**
     * Render text tracks for media player
     * 
     * @param array $textTracks Array of text track objects
     * @return string HTML for text tracks
     */
    protected function render_text_tracks($textTracks) {
        $output = '';
        
        if ($textTracks) {
        $default = false;

        foreach ($textTracks as $index => $textTrack) {
            $label = !empty($textTrack['label']) ? bricks_render_dynamic_data( $textTrack['label'] ) : '';
            $src = !empty($textTrack['src']) ? bricks_render_dynamic_data( $textTrack['src'] ) : '';
            $kind = !empty($textTrack['kind']) ? $textTrack['kind'] : 'subtitles';
            $language = !empty($textTrack['language']) ? bricks_render_dynamic_data( $textTrack['language'] ) : 'en-US';

            // Skip tracks with empty source URLs
            if (empty($src)) {
                continue;
            }

            if ('chapters' === $kind) {
                $default = true;
            }

            $defaultAttr = $default ? 'default' : '';

            $output .= "<track label='" . esc_attr__($label) . "' src='" . esc_url($src) . "' srclang='" . esc_attr($language) . "' kind='" . esc_attr($kind) . "' " . esc_attr($defaultAttr) . ">";
        }
        }
        
        return $output;
    }

    /**
     * Render time slider
     */
    protected function render_time_slider($visibilityAttribute, $thumbnailAttr) {
        return '<div class="xmp-controls_group xmp-controls_group_time-slider" ' . $visibilityAttribute . '>
                <media-time-slider class="xmp-time-slider xmp-slider" data-x-control-visibility="default">
                    <media-slider-chapters class="xmp-slider-chapters">
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
                    </media-slider-chapters>
                    <div class="xmp-slider-thumb"></div>
                    <media-slider-preview class="xmp-slider-preview">
                    <media-slider-thumbnail ' . $thumbnailAttr . '
                        class="xmp-slider-thumbnail xmp-thumbnail"
                    ></media-slider-thumbnail>
                    <div
                        class="xmp-slider-chapter-title"
                        data-part="chapter-title"
                    ></div>
                    <media-slider-value
                        class="xmp-slider-value"
                    ></media-slider-value>
                    </media-slider-preview>
                </media-time-slider>
        </div>';
    }

    /**
     * Render a media player control
     * 
     * @param array $control The control configuration
     * @param string $provider The media provider (video or audio)
     * @param bool $maybeToolTips Whether to show tooltips
     * @param string $defaultTooltipPlacement Default tooltip placement
     * @param string $position Control position
     * @param string $title Control title
     * @param string $artist Control artist
     * @param string $thumbnailAttr Thumbnail attributes
     * @param string $colorScheme Color scheme
     * @param bool $portalSettings Whether to use portal settings
     * @param string $playerIndentifier Player identifier
     * @return string Rendered control HTML
     */
    public function renderControl($control, $provider, $maybeToolTips, $defaultTooltipPlacement, $position, $title, $artist, $thumbnailAttr, $colorScheme, $portalSettings, $playerIndentifier) {

            $output = '';
            $button = '';
            $tooltip = '';
            $isToolTip = true;
            $spacer = false;
            $volumeSlider = 'disable';
        
            $inactiveIcon = empty( $control['inactiveIcon'] ) ? false : \Bricks\Element::render_icon( $control['inactiveIcon'] );
            $activeIcon = empty( $control['activeIcon'] ) ? false : \Bricks\Element::render_icon( $control['activeIcon'] );
            $replayIcon = empty( $control['replayIcon'] ) ? false : \Bricks\Element::render_icon( $control['replayIcon'] );

            $tooltipPlacementSetting = isset( $control['tooltipPlacement'] ) ? esc_attr( $control['tooltipPlacement'] ) : "default";
            $tooltipPlacement = 'default' !== $tooltipPlacementSetting ? $tooltipPlacementSetting : $defaultTooltipPlacement;

            $menuXOffset = isset( $control['menuXOffset'] ) ? esc_attr( $control['menuXOffset'] ) : '0';
            $menuYOffset = isset( $control['menuYOffset'] ) ? esc_attr( $control['menuYOffset'] ) : '20';
        
            switch ( isset( $control['control'] ) ? $control['control'] : '' ) {
        
            case 'spacer':
        
                $spacer = '<div class="xmp-controls_spacer"></div>';
        
                break;
        
            case 'play':
        
                $playLabel = !empty( $control['playLabel'] ) ? esc_attr__( $control['playLabel'] ) : esc_attr__( 'Play' );
                $pauseLabel = !empty( $control['pauseLabel'] ) ? esc_attr__( $control['pauseLabel'] ) : esc_attr__( 'Pause' );
                $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";
                
                $button .= '<media-play-button data-paused class="xmp-button xmp-play-button repeater-item" data-x-control-visibility="' . $visibility . '" data-x-tooltip="' . $playLabel . '" data-x-pressed-tooltip="' . $pauseLabel . '" data-x-placement="' . $tooltipPlacement . '">';
                $button .= $inactiveIcon ? '<div class="xmp-play-icon xmp-icon xmp-icon-custom xmp-icon-custom-inactive">'  . $inactiveIcon . '</div>' : '<div class="xmp-icon xmp-play-icon" type="play"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M10.6667 6.6548C10.6667 6.10764 11.2894 5.79346 11.7295 6.11862L24.377 15.4634C24.7377 15.7298 24.7377 16.2692 24.3771 16.5357L11.7295 25.8813C11.2895 26.2065 10.6667 25.8923 10.6667 25.3451L10.6667 6.6548Z" fill="currentColor"></path></svg></div> ';
                $button .= $activeIcon ? '<div class="xmp-pause-icon xmp-icon xmp-icon-custom xmp-icon-custom-active">' . $activeIcon . '</div>' : '<div class="xmp-icon xmp-pause-icon" type="pause"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M8.66667 6.66667C8.29848 6.66667 8 6.96514 8 7.33333V24.6667C8 25.0349 8.29848 25.3333 8.66667 25.3333H12.6667C13.0349 25.3333 13.3333 25.0349 13.3333 24.6667V7.33333C13.3333 6.96514 13.0349 6.66667 12.6667 6.66667H8.66667Z" fill="currentColor"></path> <path d="M19.3333 6.66667C18.9651 6.66667 18.6667 6.96514 18.6667 7.33333V24.6667C18.6667 25.0349 18.9651 25.3333 19.3333 25.3333H23.3333C23.7015 25.3333 24 25.0349 24 24.6667V7.33333C24 6.96514 23.7015 6.66667 23.3333 6.66667H19.3333Z" fill="currentColor"></path></svg></div>';
                $button .= $replayIcon ? '<div class="xmp-replay-icon xmp-icon xmp-icon-custom xmp-icon-custom-reply">'  . $replayIcon . '</div>' : '<div class="xmp-icon xmp-replay-icon" type="replay"><svg fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" class="vds-icon vds-replay-icon" viewBox="0 0 32 32" data-icon="replay"><path d="M15.6038 12.2147C16.0439 12.5399 16.6667 12.2257 16.6667 11.6786V10.1789C16.6667 10.1001 16.7351 10.0384 16.8134 10.0479C20.1116 10.4494 22.6667 13.2593 22.6667 16.6659C22.6667 20.3481 19.6817 23.3332 15.9995 23.3332C12.542 23.3332 9.69927 20.7014 9.36509 17.332C9.32875 16.9655 9.03371 16.6662 8.66548 16.6662L6.66655 16.6666C6.29841 16.6666 5.99769 16.966 6.02187 17.3334C6.36494 22.5454 10.7012 26.6667 16 26.6667C21.5228 26.6667 26 22.1895 26 16.6667C26 11.4103 21.9444 7.10112 16.7916 6.69757C16.7216 6.69209 16.6667 6.63396 16.6667 6.56372V4.98824C16.6667 4.44106 16.0439 4.12689 15.6038 4.45206L11.0765 7.79738C10.7159 8.06387 10.7159 8.60326 11.0766 8.86973L15.6038 12.2147Z" fill="currentColor"></path></svg></div>';
                $button .= ' </media-play-button>';
        
                break;
        
            case 'play-large':
        
                $playLabel = !empty( $control['playLabel'] ) ? esc_attr( $control['playLabel'] ) : esc_attr__( 'Play' );
                $pauseLabel = !empty( $control['pauseLabel'] ) ? esc_attr( $control['pauseLabel'] ) : esc_attr__( 'Pause' );
                $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";
                
                $button .= '<media-play-button data-paused class="xmp-button xmp-button-large repeater-item"  data-x-control-visibility="' . $visibility . '" data-paused  data-x-tooltip="' . $playLabel . '" data-x-pressed-tooltip="' . $pauseLabel . '" data-x-placement="' . $tooltipPlacement . '">';
                $button .= $inactiveIcon ? '<div class="xmp-play-icon xmp-icon xmp-icon-custom xmp-icon-custom-inactive">'  . $inactiveIcon . '</div>' : '<div class="xmp-play-icon xmp-icon" type="play"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M10.6667 6.6548C10.6667 6.10764 11.2894 5.79346 11.7295 6.11862L24.377 15.4634C24.7377 15.7298 24.7377 16.2692 24.3771 16.5357L11.7295 25.8813C11.2895 26.2065 10.6667 25.8923 10.6667 25.3451L10.6667 6.6548Z" fill="currentColor"></path></svg></div> ';
                $button .= $activeIcon ? '<div class="xmp-pause-icon xmp-icon xmp-icon-custom xmp-icon-custom-active">' . $activeIcon . '</div>' : '<div class="xmp-pause-icon xmp-icon" type="pause"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M8.66667 6.66667C8.29848 6.66667 8 6.96514 8 7.33333V24.6667C8 25.0349 8.29848 25.3333 8.66667 25.3333H12.6667C13.0349 25.3333 13.3333 25.0349 13.3333 24.6667V7.33333C13.3333 6.96514 13.0349 6.66667 12.6667 6.66667H8.66667Z" fill="currentColor"></path> <path d="M19.3333 6.66667C18.9651 6.66667 18.6667 6.96514 18.6667 7.33333V24.6667C18.6667 25.0349 18.9651 25.3333 19.3333 25.3333H23.3333C23.7015 25.3333 24 25.0349 24 24.6667V7.33333C24 6.96514 23.7015 6.66667 23.3333 6.66667H19.3333Z" fill="currentColor"></path></svg></div> ';
                $button .= $replayIcon ? '<div class="xmp-replay-icon xmp-icon xmp-icon-custom xmp-icon-custom-replay">'  . $replayIcon . '</div>' : '<div class="xmp-icon xmp-replay-icon" type="replay"><svg fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" class="vds-icon vds-replay-icon" viewBox="0 0 32 32" data-icon="replay"><path d="M15.6038 12.2147C16.0439 12.5399 16.6667 12.2257 16.6667 11.6786V10.1789C16.6667 10.1001 16.7351 10.0384 16.8134 10.0479C20.1116 10.4494 22.6667 13.2593 22.6667 16.6659C22.6667 20.3481 19.6817 23.3332 15.9995 23.3332C12.542 23.3332 9.69927 20.7014 9.36509 17.332C9.32875 16.9655 9.03371 16.6662 8.66548 16.6662L6.66655 16.6666C6.29841 16.6666 5.99769 16.966 6.02187 17.3334C6.36494 22.5454 10.7012 26.6667 16 26.6667C21.5228 26.6667 26 22.1895 26 16.6667C26 11.4103 21.9444 7.10112 16.7916 6.69757C16.7216 6.69209 16.6667 6.63396 16.6667 6.56372V4.98824C16.6667 4.44106 16.0439 4.12689 15.6038 4.45206L11.0765 7.79738C10.7159 8.06387 10.7159 8.60326 11.0766 8.86973L15.6038 12.2147Z" fill="currentColor"></path></svg></div>';
                $button .= ' </media-play-button>'; 
        
                $tooltip .= '<span class="xmp-play-tooltip-text">' . $playLabel . '</span>';
                $tooltip .= '<span class="xmp-pause-tooltip-text">' . $pauseLabel . '</span>';
        
                break;
        
            case 'seek-forward':
        
                $seekAmountForward = !empty( $control['seekAmountForward'] ) ? esc_attr( $control['seekAmountForward'] ) : '+10';
                $seekLabelForward = !empty( $control['seekLabelForward'] ) ? esc_attr__( $control['seekLabelForward'] ) : esc_attr__('Forward');
                $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";
        
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
        
                $button .= '<media-seek-button class="xmp-button xmp-seek-forward repeater-item" seconds="' . $seekAmountForward .'" data-x-control-visibility="' . $visibility . '" data-x-tooltip="' . $seekLabelForward .'">';
                $button .= $inactiveIcon ? '<div class="xmp-icon xmp-icon-custom xmp-icon-custom-inactive">'  . $inactiveIcon . '</div>' : '<div class="xmp-icon" type="' . $icon . '">' . $iconsvg . '</div> ';
                $button .= '</media-seek-button>';
        
                break;
        
            case 'seek-backward':
        
                $seekAmountBack = !empty( $control['seekAmountBack'] ) ? esc_attr( $control['seekAmountBack'] ) : '-10';
                $seekLabelBack = !empty( $control['seekLabelBack'] ) ? esc_attr__( $control['seekLabelBack'] ) : esc_attr__('Rewind');
                $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";
        
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
        
                $button .= '<media-seek-button class="xmp-button xmp-seek-backward repeater-item" seconds="' . $seekAmountBack . '" data-x-control-visibility="' . $visibility . '" data-x-tooltip="' . $seekLabelBack . '">';
                $button .= $inactiveIcon ? '<div class="xmp-icon xmp-icon-custom xmp-icon-custom-inactive">'  . $inactiveIcon . '</div>' : '<div class="xmp-icon" type="' . $icon . '">' . $iconsvg . '</div> ';
                $button .= '</media-seek-button>';
        
                break;
        
            case 'mute':
        
                $unmuteLabel = !empty( $control['unmuteLabel'] ) ? esc_attr__( $control['unmuteLabel'] ) : esc_attr__('Unmute');
                $muteLabel = !empty( $control['muteLabel'] ) ? esc_attr__( $control['muteLabel'] ) : esc_attr__('Mute');
                $volumeSlider = isset( $control['volumeSlider'] ) ? esc_attr( $control['volumeSlider'] ) : 'disable';
                $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";
        
                $muteIcon = empty( $control['muteIcon'] ) ? false : \Bricks\Element::render_icon( $control['muteIcon'] );
                $highVolumeIcon = empty( $control['highVolumeIcon'] ) ? false : \Bricks\Element::render_icon( $control['highVolumeIcon'] );
                $lowVolumeIcon = empty( $control['lowVolumeIcon'] ) ? false : \Bricks\Element::render_icon( $control['lowVolumeIcon'] );
        
                $button .= '<media-mute-button class="xmp-button xmp-mute-button" data-x-control-visibility="' . $visibility . '" data-state="high" data-x-tooltip="' . $muteLabel . '" data-x-pressed-tooltip="' . $unmuteLabel . '">'; 
        
                $button .= $muteIcon ? '<div class="xmp-mute-icon xmp-icon xmp-icon-custom">'  . $muteIcon . '</div>' : '<div class="xmp-mute-icon xmp-icon" aria-label="Mute" type="mute"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M17.5091 24.6594C17.5091 25.2066 16.8864 25.5208 16.4463 25.1956L9.44847 20.0252C9.42553 20.0083 9.39776 19.9991 9.36923 19.9991H4.66667C4.29848 19.9991 4 19.7006 4 19.3325V12.6658C4 12.2976 4.29848 11.9991 4.66667 11.9991H9.37115C9.39967 11.9991 9.42745 11.99 9.45039 11.973L16.4463 6.8036C16.8863 6.47842 17.5091 6.79259 17.5091 7.33977L17.5091 24.6594Z" fill="currentColor"></path> <path d="M28.8621 13.6422C29.1225 13.3818 29.1225 12.9597 28.8621 12.6994L27.9193 11.7566C27.659 11.4962 27.2368 11.4962 26.9765 11.7566L24.7134 14.0197C24.6613 14.0717 24.5769 14.0717 24.5248 14.0197L22.262 11.7568C22.0016 11.4964 21.5795 11.4964 21.3191 11.7568L20.3763 12.6996C20.116 12.9599 20.116 13.382 20.3763 13.6424L22.6392 15.9053C22.6913 15.9573 22.6913 16.0418 22.6392 16.0938L20.3768 18.3562C20.1165 18.6166 20.1165 19.0387 20.3768 19.299L21.3196 20.2419C21.58 20.5022 22.0021 20.5022 22.2624 20.2418L24.5248 17.9795C24.5769 17.9274 24.6613 17.9274 24.7134 17.9795L26.976 20.2421C27.2363 20.5024 27.6585 20.5024 27.9188 20.2421L28.8616 19.2992C29.122 19.0389 29.122 18.6168 28.8616 18.3564L26.599 16.0938C26.547 16.0418 26.547 15.9573 26.599 15.9053L28.8621 13.6422Z" fill="currentColor"></path></svg></div> ';
                $button .= $highVolumeIcon ? '<div class="xmp-volume-high-icon xmp-icon xmp-icon-custom xmp-icon-custom-inactive">'  . $highVolumeIcon . '</div>' : '<div class="xmp-volume-high-icon xmp-icon" type="volume-high"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M17.5091 24.6595C17.5091 25.2066 16.8864 25.5208 16.4463 25.1956L9.44847 20.0252C9.42553 20.0083 9.39776 19.9992 9.36923 19.9992H4.66667C4.29848 19.9992 4 19.7007 4 19.3325V12.6658C4 12.2976 4.29848 11.9992 4.66667 11.9992H9.37115C9.39967 11.9992 9.42745 11.99 9.45039 11.9731L16.4463 6.80363C16.8863 6.47845 17.5091 6.79262 17.5091 7.3398L17.5091 24.6595Z" fill="currentColor"></path> <path d="M27.5091 9.33336C27.8773 9.33336 28.1758 9.63184 28.1758 10V22C28.1758 22.3682 27.8773 22.6667 27.5091 22.6667H26.1758C25.8076 22.6667 25.5091 22.3682 25.5091 22V10C25.5091 9.63184 25.8076 9.33336 26.1758 9.33336L27.5091 9.33336Z" fill="currentColor"></path> <path d="M22.1758 12C22.544 12 22.8424 12.2985 22.8424 12.6667V19.3334C22.8424 19.7016 22.544 20 22.1758 20H20.8424C20.4743 20 20.1758 19.7016 20.1758 19.3334V12.6667C20.1758 12.2985 20.4743 12 20.8424 12H22.1758Z" fill="currentColor"></path></svg></div> ';
                $button .= $lowVolumeIcon ? '<div class="xmp-volume-low-icon xmp-icon xmp-icon-custom xmp-icon-custom-active">' . $lowVolumeIcon . '</div>' : '<div class="xmp-volume-low-icon xmp-icon" type="volume-low"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M17.5091 24.6594C17.5091 25.2066 16.8864 25.5207 16.4463 25.1956L9.44847 20.0252C9.42553 20.0083 9.39776 19.9991 9.36923 19.9991H4.66667C4.29848 19.9991 4 19.7006 4 19.3324V12.6658C4 12.2976 4.29848 11.9991 4.66667 11.9991H9.37115C9.39967 11.9991 9.42745 11.99 9.45039 11.973L16.4463 6.80358C16.8863 6.4784 17.5091 6.79258 17.5091 7.33975L17.5091 24.6594Z" fill="currentColor"></path> <path d="M22.8424 12.6667C22.8424 12.2985 22.544 12 22.1758 12H20.8424C20.4743 12 20.1758 12.2985 20.1758 12.6667V19.3333C20.1758 19.7015 20.4743 20 20.8424 20H22.1758C22.544 20 22.8424 19.7015 22.8424 19.3333V12.6667Z" fill="currentColor"></path></svg></div> ';
        
                $button .= '</media-mute-button>';
        
                break;
        
        
            case 'time':
        
                $isToolTip = false;
        
                $currentTime = isset( $control['currentTime'] ) ? $control['currentTime'] : 'enable';
                $duration = isset( $control['duration'] ) ? 'enable' === $control['duration'] : true;
                $timeDivider = isset( $control['timeDivider'] ) ? 'enable' === $control['timeDivider'] : true;
                $timeDividerText = isset( $control['timeDividerText'] ) ? esc_attr( $control['timeDividerText'] ) : "/";
                $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";
        
                $remainder = 'remainder' === $currentTime ? 'remainder' : '';
        
                $button .= '<div class="xmp-time-group repeater-item" data-x-control-visibility="' . $visibility . '">';
                $button .= 'enable' === $currentTime || 'remainder' === $currentTime ? '<media-time class="xmp-time" ' . $remainder . ' type="current" data-type="current">0:00</media-time>' : '';
                $button .= $timeDivider ? '<div class="xmp-time-divider">' . $timeDividerText . '</div>' : '';
                $button .= $duration ? '<media-time class="xmp-time" type="duration" data-type="duration">0:00</media-time>' : '';
                $button .= '</div>';
        
                break;
        
            case 'pip':
        
                $enterPipLabel = isset( $control['enterPipLabel'] ) ? esc_attr__( $control['enterPipLabel'] ) : esc_attr__("Enter PIP");
                $exitPipLabel = isset( $control['exitPipLabel'] ) ? esc_attr__( $control['exitPipLabel'] ) : esc_attr__("Exit PIP");
                $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";
        
                $button .= '<media-pip-button aria-hidden="true" class="xmp-button xmp-pip-button repeater-item" data-x-control-visibility="' . $visibility . '" data-x-tooltip="' . $enterPipLabel . '" data-x-pressed-tooltip="' . $exitPipLabel . '">';
                $button .= $inactiveIcon ? '<div class="xmp-pip-enter-icon xmp-icon xmp-icon-custom xmp-icon-custom-inactive">'  . $inactiveIcon . '</div>' : '<div class="xmp-pip-enter-icon xmp-icon" type="picture-in-picture"><svg width="32" height="32" viewBox="0 0 32 32" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M16 15.3333C15.6318 15.3333 15.3333 15.6318 15.3333 16V20C15.3333 20.3682 15.6318 20.6667 16 20.6667H21.3333C21.7015 20.6667 22 20.3682 22 20V16C22 15.6318 21.7015 15.3333 21.3333 15.3333H16Z" fill="currentColor" /><path fill-rule="evenodd" clip-rule="evenodd" d="M5.33333 7.33334C5.33333 6.96515 5.63181 6.66667 5.99999 6.66667H26C26.3682 6.66667 26.6667 6.96515 26.6667 7.33334V24.6667C26.6667 25.0349 26.3682 25.3333 26 25.3333H5.99999C5.63181 25.3333 5.33333 25.0349 5.33333 24.6667V7.33334ZM7.99999 10C7.99999 9.63182 8.29847 9.33334 8.66666 9.33334H23.3333C23.7015 9.33334 24 9.63182 24 10V22C24 22.3682 23.7015 22.6667 23.3333 22.6667H8.66666C8.29847 22.6667 7.99999 22.3682 7.99999 22V10Z" fill="currentColor"></path></svg></div> ';
                $button .= $activeIcon ? '<div class="xmp-pip-exit-icon xmp-icon xmp-icon-custom xmp-icon-custom-active">' . $activeIcon . '</div>' : '<div class="xmp-pip-exit-icon xmp-icon" type="picture-in-picture-exit"><svg width="32" height="32" viewBox="0 0 32 32" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M5.33334 26V19.4667C5.33334 19.393 5.39304 19.3333 5.46668 19.3333H7.86668C7.94031 19.3333 8.00001 19.393 8.00001 19.4667V23.3333C8.00001 23.7015 8.29849 24 8.66668 24H23.3333C23.7015 24 24 23.7015 24 23.3333V8.66666C24 8.29847 23.7015 7.99999 23.3333 7.99999H19.4667C19.393 7.99999 19.3333 7.9403 19.3333 7.86666V5.46666C19.3333 5.39302 19.393 5.33333 19.4667 5.33333H26C26.3682 5.33333 26.6667 5.63181 26.6667 5.99999V26C26.6667 26.3682 26.3682 26.6667 26 26.6667H6.00001C5.63182 26.6667 5.33334 26.3682 5.33334 26Z" fill="currentColor" /><path d="M14.0098 8.42359H10.806C10.6872 8.42359 10.6277 8.56721 10.7117 8.6512L16.5491 14.4886C16.8094 14.7489 16.8094 15.171 16.5491 15.4314L15.3234 16.657C15.0631 16.9174 14.641 16.9174 14.3806 16.657L8.63739 10.9138C8.55339 10.8298 8.40978 10.8893 8.40978 11.0081V14.0236C8.40978 14.3918 8.1113 14.6903 7.74311 14.6903H6.00978C5.64159 14.6903 5.34311 14.3918 5.34311 14.0236L5.34311 6.02359C5.34311 5.6554 5.64159 5.35692 6.00978 5.35692L14.0098 5.35692C14.378 5.35692 14.6764 5.6554 14.6764 6.02359V7.75692C14.6764 8.12511 14.378 8.42359 14.0098 8.42359Z" fill="currentColor" /></svg></div> ';
                $button .= '</media-pip-button>';
                 
        
                break;
        
            case 'fullscreen':
        
                if ('audio' === $provider) {
                    return;
                }
        
                $enterFullscreenLabel = isset( $control['enterFullscreenLabel'] ) ? esc_attr__( $control['enterFullscreenLabel'] ) : esc_attr__("Enter Fullscreen");
                $exitFullscreenLabel = isset( $control['exitFullscreenLabel'] ) ? esc_attr__( $control['exitFullscreenLabel'] ) : esc_attr__("Exit Fullscreen");
                $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";
        
                $button .= '<media-fullscreen-button aria-hidden="true" role=button aria-label="' . $enterFullscreenLabel . '" class="xmp-button xmp-fullscreen-button repeater-item" data-x-control-visibility="' . $visibility . '"  data-x-tooltip="' . $enterFullscreenLabel . '" data-x-pressed-tooltip="' . $exitFullscreenLabel . '">';
                $button .= $inactiveIcon ? '<div class="xmp-fs-enter-icon xmp-icon xmp-icon-custom xmp-icon-custom-inactive">'  . $inactiveIcon . '</div>' : '<div class="xmp-fs-enter-icon xmp-icon" type="fullscreen"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M25.3299 7.26517C25.2958 6.929 25.0119 6.66666 24.6667 6.66666H19.3334C18.9652 6.66666 18.6667 6.96514 18.6667 7.33333V9.33333C18.6667 9.70152 18.9652 10 19.3334 10L21.8667 10C21.9403 10 22 10.0597 22 10.1333V12.6667C22 13.0349 22.2985 13.3333 22.6667 13.3333H24.6667C25.0349 13.3333 25.3334 13.0349 25.3334 12.6667V7.33333C25.3334 7.31032 25.3322 7.28758 25.3299 7.26517Z" fill="currentColor"></path> <path d="M22 21.8667C22 21.9403 21.9403 22 21.8667 22L19.3334 22C18.9652 22 18.6667 22.2985 18.6667 22.6667V24.6667C18.6667 25.0349 18.9652 25.3333 19.3334 25.3333L24.6667 25.3333C25.0349 25.3333 25.3334 25.0349 25.3334 24.6667V19.3333C25.3334 18.9651 25.0349 18.6667 24.6667 18.6667H22.6667C22.2985 18.6667 22 18.9651 22 19.3333V21.8667Z" fill="currentColor"></path> <path d="M12.6667 22H10.1334C10.0597 22 10 21.9403 10 21.8667V19.3333C10 18.9651 9.70154 18.6667 9.33335 18.6667H7.33335C6.96516 18.6667 6.66669 18.9651 6.66669 19.3333V24.6667C6.66669 25.0349 6.96516 25.3333 7.33335 25.3333H12.6667C13.0349 25.3333 13.3334 25.0349 13.3334 24.6667V22.6667C13.3334 22.2985 13.0349 22 12.6667 22Z" fill="currentColor"></path> <path d="M10 12.6667V10.1333C10 10.0597 10.0597 10 10.1334 10L12.6667 10C13.0349 10 13.3334 9.70152 13.3334 9.33333V7.33333C13.3334 6.96514 13.0349 6.66666 12.6667 6.66666H7.33335C6.96516 6.66666 6.66669 6.96514 6.66669 7.33333V12.6667C6.66669 13.0349 6.96516 13.3333 7.33335 13.3333H9.33335C9.70154 13.3333 10 13.0349 10 12.6667Z" fill="currentColor"></path></svg></div> ';
                $button .= $activeIcon ? '<div class="xmp-fs-exit-icon xmp-icon xmp-icon-custom xmp-icon-custom-active">' . $activeIcon . '</div>' : '<div class="xmp-fs-exit-icon xmp-icon" type="fullscreen-exit"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M19.3334 13.3333C18.9652 13.3333 18.6667 13.0349 18.6667 12.6667L18.6667 7.33333C18.6667 6.96514 18.9652 6.66666 19.3334 6.66666H21.3334C21.7015 6.66666 22 6.96514 22 7.33333V9.86666C22 9.9403 22.0597 10 22.1334 10L24.6667 10C25.0349 10 25.3334 10.2985 25.3334 10.6667V12.6667C25.3334 13.0349 25.0349 13.3333 24.6667 13.3333L19.3334 13.3333Z" fill="currentColor"></path> <path d="M13.3334 19.3333C13.3334 18.9651 13.0349 18.6667 12.6667 18.6667H7.33335C6.96516 18.6667 6.66669 18.9651 6.66669 19.3333V21.3333C6.66669 21.7015 6.96516 22 7.33335 22H9.86669C9.94032 22 10 22.0597 10 22.1333L10 24.6667C10 25.0349 10.2985 25.3333 10.6667 25.3333H12.6667C13.0349 25.3333 13.3334 25.0349 13.3334 24.6667L13.3334 19.3333Z" fill="currentColor"></path> <path d="M18.6667 24.6667C18.6667 25.0349 18.9652 25.3333 19.3334 25.3333H21.3334C21.7015 25.3333 22 25.0349 22 24.6667V22.1333C22 22.0597 22.0597 22 22.1334 22H24.6667C25.0349 22 25.3334 21.7015 25.3334 21.3333V19.3333C25.3334 18.9651 25.0349 18.6667 24.6667 18.6667L19.3334 18.6667C18.9652 18.6667 18.6667 18.9651 18.6667 19.3333L18.6667 24.6667Z" fill="currentColor"></path> <path d="M10.6667 13.3333H12.6667C13.0349 13.3333 13.3334 13.0349 13.3334 12.6667L13.3334 10.6667V7.33333C13.3334 6.96514 13.0349 6.66666 12.6667 6.66666H10.6667C10.2985 6.66666 10 6.96514 10 7.33333L10 9.86666C10 9.9403 9.94033 10 9.86669 10L7.33335 10C6.96516 10 6.66669 10.2985 6.66669 10.6667V12.6667C6.66669 13.0349 6.96516 13.3333 7.33335 13.3333L10.6667 13.3333Z" fill="currentColor"></path></svg></div> ';
                $button .= ' </media-fullscreen-button>';
        
                break;
        
            case 'chapters':
        
                $isToolTip = false;
                $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";
        
                $chaptersLabel = isset( $control['chaptersLabel'] ) ? esc_attr__( $control['chaptersLabel'] ) : esc_attr__("Chapters");
        
                $settingsPlacement = 'top' === $position ? 'bottom end' : 'top end';
                $settingsPlacementOption = isset( $control['settingsPlacement'] ) ? esc_attr( $control['settingsPlacement'] ) : $settingsPlacement;
        
                $settingstooltipPlacementSetting = isset( $control['tooltipPlacement'] ) ? esc_attr( $control['tooltipPlacement'] ) : "default";
                $settingstooltipPlacement = 'default' !== $settingstooltipPlacementSetting ? $settingstooltipPlacementSetting : $defaultTooltipPlacement;
        
                $settingsChaptersIcon = empty( $control['settingsChaptersIcon'] ) ? false : \Bricks\Element::render_icon( $control['settingsChaptersIcon'] );
                
        
                $button .= '<media-menu class="xmp-menu xmp-chapter-menus repeater-item" data-x-control-visibility="' . $visibility . '">';
        
        
                $button .= '<media-menu-button class="xmp-button" aria-disabled="true" aria-label="' . $chaptersLabel . '" data-x-tooltip="' . $chaptersLabel . '">';
                $button .= $settingsChaptersIcon ? '<div class="xmp-icon xmp-icon-custom xmp-icon-custom-inactive">'  . $settingsChaptersIcon . '</div>' : '<div class="xmp-icon" type="chapters"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M16.6927 25.3346C16.3245 25.3346 16.026 25.0361 16.026 24.6679L16.026 7.3346C16.026 6.96641 16.3245 6.66794 16.6927 6.66794L18.6927 6.66794C19.0609 6.66794 19.3594 6.96642 19.3594 7.3346L19.3594 24.6679C19.3594 25.0361 19.0609 25.3346 18.6927 25.3346H16.6927Z" fill="currentColor"></path> <path d="M24.026 25.3346C23.6578 25.3346 23.3594 25.0361 23.3594 24.6679L23.3594 7.3346C23.3594 6.96641 23.6578 6.66794 24.026 6.66794L26.026 6.66794C26.3942 6.66794 26.6927 6.96642 26.6927 7.3346V24.6679C26.6927 25.0361 26.3942 25.3346 26.026 25.3346H24.026Z" fill="currentColor"></path> <path d="M5.48113 23.9407C5.38584 24.2963 5.59689 24.6619 5.95254 24.7572L7.88439 25.2748C8.24003 25.3701 8.60559 25.159 8.70089 24.8034L13.1871 8.06067C13.2824 7.70503 13.0713 7.33947 12.7157 7.24417L10.7838 6.72654C10.4282 6.63124 10.0626 6.8423 9.96733 7.19794L5.48113 23.9407Z" fill="currentColor"></path></svg></div> ';
                $button .= '</media-menu-button>';
        
                if ($portalSettings) {
                $button .= '<media-menu-portal data-x-player-id="' . esc_attr( $playerIndentifier ) . '" disabled="fullscreen" >';
                }
                            
                $button .= '<media-menu-items class="xmp-chapters-menu-items xmp-menu-items ' . esc_attr( $colorScheme ) .'" placement="' . $settingsPlacementOption . '" align-offset="' . $menuXOffset . '" offset="' . $menuYOffset . '"data-x-player-id="' . esc_attr( $playerIndentifier ) . '">
                                <media-chapters-radio-group class="xmp-chapters-radio-group xmp-radio-group">
                                    <template>
                                    <media-radio class="xmp-chapter-radio xmp-radio">
                                        <media-thumbnail class="xmp-thumbnail" ' . $thumbnailAttr . '></media-thumbnail>
                                        <div class="xmp-chapter-radio-content">
                                        <span class="xmp-chapter-radio-label" data-part="label"></span>
                                        <span class="xmp-chapter-radio-start-time" data-part="start-time"></span>
                                        <span class="xmp-chapter-radio-duration" data-part="duration"></span>
                                        </div>
                                    </media-radio>
                                    </template>
                                </media-chapters-radio-group>
                                </media-menu-items>';
        
                if ($portalSettings) {
                $button .= '</media-menu-portal>';
                }
        
                $button .= '</media-menu>';
        
                break;
        
            case 'captions':
        
                $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";
        
                $captionsOnLabel = isset( $control['captionsOnLabel'] ) ? esc_attr__( $control['captionsOnLabel'] ) : esc_attr__("Closed-Captions On");
                $captionsOffLabel = isset( $control['captionsOffLabel'] ) ? esc_attr__( $control['captionsOffLabel'] ) : esc_attr__("Closed-Captions Off");
        
                $button .= '<media-caption-button class="xmp-button xmp-captions-button repeater-item" data-x-control-visibility="' . $visibility . '" data-x-tooltip="' . $captionsOnLabel . '" data-x-pressed-tooltip="' . $captionsOffLabel . '">';
                $button .= $inactiveIcon ? '<div class="xmp-cc-on-icon xmp-icon xmp-icon-custom xmp-icon-custom-inactive">'  . $inactiveIcon . '</div>' : '<div class="xmp-cc-on-icon xmp-icon" type="closed-captions-on"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M8 28.0003C8 27.6321 8.29848 27.3336 8.66667 27.3336H23.3333C23.7015 27.3336 24 27.6321 24 28.0003V29.3336C24 29.7018 23.7015 30.0003 23.3333 30.0003H8.66667C8.29848 30.0003 8 29.7018 8 29.3336V28.0003Z" fill="currentColor"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M4.66602 6.66699C4.29783 6.66699 3.99935 6.96547 3.99935 7.33366V24.667C3.99935 25.0352 4.29783 25.3337 4.66602 25.3337H27.3327C27.7009 25.3337 27.9994 25.0352 27.9994 24.667V7.33366C27.9994 6.96547 27.7009 6.66699 27.3327 6.66699H4.66602ZM8.66659 21.3333C8.2984 21.3333 7.99992 21.0349 7.99992 20.6667V11.3333C7.99992 10.9651 8.2984 10.6667 8.66659 10.6667H13.9999C14.3681 10.6667 14.6666 10.9651 14.6666 11.3333V12.6667C14.6666 13.0349 14.3681 13.3333 13.9999 13.3333H10.7999C10.7263 13.3333 10.6666 13.393 10.6666 13.4667V18.5333C10.6666 18.607 10.7263 18.6667 10.7999 18.6667H13.9999C14.3681 18.6667 14.6666 18.9651 14.6666 19.3333V20.6667C14.6666 21.0349 14.3681 21.3333 13.9999 21.3333H8.66659ZM17.9999 21.3333C17.6317 21.3333 17.3333 21.0349 17.3333 20.6667V11.3333C17.3333 10.9651 17.6317 10.6667 17.9999 10.6667H23.3333C23.7014 10.6667 23.9999 10.9651 23.9999 11.3333V12.6667C23.9999 13.0349 23.7014 13.3333 23.3333 13.3333H20.1333C20.0596 13.3333 19.9999 13.393 19.9999 13.4667V18.5333C19.9999 18.607 20.0596 18.6667 20.1333 18.6667H23.3333C23.7014 18.6667 23.9999 18.9651 23.9999 19.3333V20.6667C23.9999 21.0349 23.7014 21.3333 23.3333 21.3333H17.9999Z" fill="currentColor"></path></svg></div> ';
                $button .= $activeIcon ? '<div class="xmp-cc-off-icon xmp-icon xmp-icon-custom xmp-icon-custom-active">' . $activeIcon . '</div>' : '<div class="xmp-cc-off-icon xmp-icon" type="closed-captions"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M4.6661 6.66699C4.29791 6.66699 3.99943 6.96547 3.99943 7.33366V24.667C3.99943 25.0352 4.29791 25.3337 4.6661 25.3337H27.3328C27.701 25.3337 27.9994 25.0352 27.9994 24.667V7.33366C27.9994 6.96547 27.701 6.66699 27.3328 6.66699H4.6661ZM8.66667 21.3333C8.29848 21.3333 8 21.0349 8 20.6667V11.3333C8 10.9651 8.29848 10.6667 8.66667 10.6667H14C14.3682 10.6667 14.6667 10.9651 14.6667 11.3333V12.6667C14.6667 13.0349 14.3682 13.3333 14 13.3333H10.8C10.7264 13.3333 10.6667 13.393 10.6667 13.4667V18.5333C10.6667 18.607 10.7264 18.6667 10.8 18.6667H14C14.3682 18.6667 14.6667 18.9651 14.6667 19.3333V20.6667C14.6667 21.0349 14.3682 21.3333 14 21.3333H8.66667ZM18 21.3333C17.6318 21.3333 17.3333 21.0349 17.3333 20.6667V11.3333C17.3333 10.9651 17.6318 10.6667 18 10.6667H23.3333C23.7015 10.6667 24 10.9651 24 11.3333V12.6667C24 13.0349 23.7015 13.3333 23.3333 13.3333H20.1333C20.0597 13.3333 20 13.393 20 13.4667V18.5333C20 18.607 20.0597 18.6667 20.1333 18.6667H23.3333C23.7015 18.6667 24 18.9651 24 19.3333V20.6667C24 21.0349 23.7015 21.3333 23.3333 21.3333H18Z" fill="currentColor"></path></svg></div> ';
                $button .= '</media-caption-button>';
        
                $tooltip .= '<span class="xmp-cc-on-tooltip-text">' . $captionsOnLabel  . '</span>';
                $tooltip .= '<span class="xmp-cc-off-tooltip-text">' . $captionsOffLabel  . '</span>';
        
                break;
        
            case 'title':
        
                $isToolTip = false;
                $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";
        
                $button .= '<media-title class="xmp-title repeater-item" data-x-control-visibility="' . $visibility . '"></media-title>';
        
                break;

            case 'artist': 
        
                $isToolTip = false;
                $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";
                $prefix = isset( $control['artist_prefix'] ) ? esc_attr__( $this->render_dynamic_data( $control['artist_prefix'] ) ) : '';
                $suffix = isset( $control['artist_suffix'] ) ? esc_attr__( $this->render_dynamic_data( $control['artist_suffix'] ) ) : ''; 
                $button .= '<div class="xmp-artist repeater-item" data-artist-display data-x-control-visibility="' . $visibility . '">';
                $button .= '<span class="xmp-artist_prefix">' . esc_html($prefix) . '</span>';
                $button .= '<span class="xmp-artist_name">' . esc_html($artist) . '</span>'; 
                $button .= '<span class="xmp-artist_suffix">' . esc_html($suffix) . '</span>';
                $button .= '</div>';
        
                break;

            case 'download': 
        
                $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";

                $downloadLabel = isset( $control['downloadLabel'] ) ? esc_attr__( $control['downloadLabel'] ) : esc_attr__("Download"); 

                $downloadSource = isset( $control['downloadSource'] ) ? esc_attr( $control['downloadSource'] ) : 'source';
                $downloadURLAttribute = '';
                $downloadURLFileName = '';

                $downloadOptionId = isset( $control['downloadOptionId'] ) ? esc_attr( $control['downloadOptionId'] ) : '';

                if ( ! empty( $control['downloadOptionId'] ) ) {
                    $downloadOptionId = esc_attr( $this->render_dynamic_data( $control['downloadOptionId'] ) );
                }

                if ( ! empty( $control['downloadURLAttribute'] ) ) {
                    $downloadURLAttribute = esc_attr( $this->render_dynamic_data( $control['downloadURLAttribute'] ) );
                } 

                if ( ! empty( $control['downloadURLFileName'] ) ) {
                    $downloadURLFileName = esc_attr( $this->render_dynamic_data( $control['downloadURLFileName'] ) ); 
                }

                $iconsvg = '<svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M14.2225 13.7867C14.3065 13.8706 14.4501 13.8112 14.4501 13.6924V5.99955C14.4501 5.63136 14.7486 5.33289 15.1167 5.33289H16.8501C17.2183 5.33289 17.5167 5.63136 17.5167 5.99955V13.6916C17.5167 13.8104 17.6604 13.8699 17.7444 13.7859L19.9433 11.5869C20.2037 11.3266 20.6258 11.3266 20.8861 11.5869L22.1118 12.8126C22.3722 13.0729 22.3722 13.4951 22.1118 13.7554L16.4549 19.4123C16.1946 19.6726 15.772 19.6731 15.5116 19.4128L9.85479 13.7559C9.59444 13.4956 9.59444 13.0734 9.85479 12.8131L11.0804 11.5874C11.3408 11.3271 11.7629 11.3271 12.0233 11.5874L14.2225 13.7867Z" fill="currentColor" /><path d="M5.99998 20.267C5.63179 20.267 5.33331 20.5654 5.33331 20.9336V25.9997C5.33331 26.3678 5.63179 26.6663 5.99998 26.6663H26C26.3682 26.6663 26.6666 26.3678 26.6666 25.9997V20.9336C26.6666 20.5654 26.3682 20.267 26 20.267H24.2666C23.8985 20.267 23.6 20.5654 23.6 20.9336V22.9333C23.6 23.3014 23.3015 23.5999 22.9333 23.5999H9.06638C8.69819 23.5999 8.39972 23.3014 8.39972 22.9333V20.9336C8.39972 20.5654 8.10124 20.267 7.73305 20.267H5.99998Z" fill="currentColor" /></svg>';

                $button .= '<button class="xmp-button xmp-download repeater-item" data-x-control-visibility="' . $visibility . '" aria-label="' . $downloadLabel . '" data-x-tooltip="' . $downloadLabel . '" data-x-download-source="' . $downloadSource . '"';

                if ( $downloadURLAttribute ) {
                    $button .= ' data-x-download-url-attr="' . $downloadURLAttribute . '"';
                }

                if ( $downloadURLFileName ) {
                    $button .= ' data-x-download-filename-attr="' . $downloadURLFileName . '"';
                }

                if ( $downloadOptionId ) {
                    $button .= ' data-x-download-option-id="' . $downloadOptionId . '"';
                }

                $button .= '>';
                $button .= $inactiveIcon ? '<div class="xmp-icon xmp-icon-custom xmp-icon-custom-inactive">'  . $inactiveIcon . '</div>' : '<div class="xmp-icon">' . $iconsvg . '</div> ';
                $button .= '</button>';

                break;
        
            case 'chapter-title':
        
                $isToolTip = false;
                $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";
        
                $button .= '<media-chapter-title class="xmp-chapter-title repeater-item" data-x-control-visibility="' . $visibility . '"></media-chapter-title>';
                break;
        
        
            case 'settings':
        
                $isToolTip = false;
                $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";
                $settingsLabel = isset( $control['settingsLabel'] ) ? esc_attr__( $this->render_dynamic_data( $control['settingsLabel'] ) ) : esc_attr__("Settings");
                $captionsLabel = isset( $control['captionsLabel'] ) ? esc_attr__( $this->render_dynamic_data( $control['captionsLabel'] ) ) : esc_attr__("Captions");
                $captionsOffHint = isset( $control['captionsOffHint'] ) ? esc_attr__( $this->render_dynamic_data( $control['captionsOffHint'] ) ) : esc_attr__("Off");
                $loopLabel = isset( $control['loopLabel'] ) ? esc_attr__( $this->render_dynamic_data( $control['loopLabel'] ) ) : esc_attr__("Loop");
        
                // $audioLabel = isset( $control['audioLabel'] ) ? esc_attr__( $this->render_dynamic_data( $control['audioLabel'] ) ) : esc_attr__("Audio");  
                //$boostLabel = isset( $control['boostLabel'] ) ? esc_attr__( $this->render_dynamic_data( $control['boostLabel'] ) ) : esc_attr__("Boost");
                $speedLabel = isset( $control['speedLabel'] ) ? esc_attr__( $this->render_dynamic_data(  $control['speedLabel'] ) ) : esc_attr__("Playback");
                $qualityLabel = isset( $control['qualityLabel'] ) ? esc_attr__( $this->render_dynamic_data( $control['qualityLabel'] ) ): esc_attr__("Quality");
                $accessibilityLabel = isset( $control['accessibilityLabel'] ) ? esc_attr__( $this->render_dynamic_data( $control['accessibilityLabel'] ) ): esc_attr__("Accessibility");
                $announcementsLabel = isset( $control['announcementsLabel'] ) ? esc_attr__( $this->render_dynamic_data( $control['announcementsLabel'] ) ) : esc_attr__("Announcements");
        
                $settingsPlacement = 'top' === $position ? 'bottom end' : 'top end';
                $settingsPlacementOption = isset( $control['settingsPlacement'] ) ? esc_attr( $control['settingsPlacement'] ) : $settingsPlacement;
        
                $settingstooltipPlacementSetting = isset( $control['tooltipPlacement'] ) ? esc_attr( $control['tooltipPlacement'] ) : "default";
                $settingstooltipPlacement = 'default' !== $settingstooltipPlacementSetting ? $settingstooltipPlacementSetting : $defaultTooltipPlacement;
        
        
                $settingsAudioIcon = empty( $control['settingsAudioIcon'] ) ? false : \Bricks\Element::render_icon( $control['settingsAudioIcon'] );
                $settingsSpeedIcon = empty( $control['settingsSpeedIcon'] ) ? false : \Bricks\Element::render_icon( $control['settingsSpeedIcon'] );
                $settingsCaptionsIcon = empty( $control['settingsCaptionsIcon'] ) ? false : \Bricks\Element::render_icon( $control['settingsCaptionsIcon'] );
                $settingsAccessibilityIcon = empty( $control['settingsAccessibilityIcon'] ) ? false : \Bricks\Element::render_icon( $control['settingsAccessibilityIcon'] );

                
                
        
                $button .= '<media-menu class="xmp-menu xmp-settings-menu repeater-item" data-x-control-visibility="' . $visibility . '">';
        
                $button .= '<media-menu-button role=button class="xmp-button xmp-settings-button" aria-label="' . $settingsLabel . '" data-x-tooltip="' . $settingsLabel .'">';
        
                $button .= $inactiveIcon ? '<div class="xmp-icon xmp-icon-custom xmp-icon-custom-inactive">'  . $inactiveIcon . '</div>' : '<div class="xmp-icon xmp-rotate-icon" type="settings"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M13.5722 5.33333C13.2429 5.33333 12.9629 5.57382 12.9132 5.89938L12.4063 9.21916C12.4 9.26058 12.3746 9.29655 12.3378 9.31672C12.2387 9.37118 12.1409 9.42779 12.0444 9.48648C12.0086 9.5083 11.9646 9.51242 11.9255 9.49718L8.79572 8.27692C8.48896 8.15732 8.14083 8.27958 7.9762 8.56472L5.5491 12.7686C5.38444 13.0538 5.45271 13.4165 5.70981 13.6223L8.33308 15.7225C8.3658 15.7487 8.38422 15.7887 8.38331 15.8306C8.38209 15.8867 8.38148 15.9429 8.38148 15.9993C8.38148 16.0558 8.3821 16.1121 8.38332 16.1684C8.38423 16.2102 8.36582 16.2503 8.33313 16.2765L5.7103 18.3778C5.45334 18.5836 5.38515 18.9462 5.54978 19.2314L7.97688 23.4352C8.14155 23.7205 8.48981 23.8427 8.79661 23.723L11.926 22.5016C11.9651 22.4864 12.009 22.4905 12.0449 22.5123C12.1412 22.5709 12.2388 22.6274 12.3378 22.6818C12.3745 22.7019 12.4 22.7379 12.4063 22.7793L12.9132 26.0993C12.9629 26.4249 13.2429 26.6654 13.5722 26.6654H18.4264C18.7556 26.6654 19.0356 26.425 19.0854 26.0995L19.5933 22.7801C19.5997 22.7386 19.6252 22.7027 19.6619 22.6825C19.7614 22.6279 19.8596 22.5711 19.9564 22.5121C19.9923 22.4903 20.0362 22.4862 20.0754 22.5015L23.2035 23.7223C23.5103 23.842 23.8585 23.7198 24.0232 23.4346L26.4503 19.2307C26.6149 18.9456 26.5467 18.583 26.2898 18.3771L23.6679 16.2766C23.6352 16.2504 23.6168 16.2104 23.6177 16.1685C23.619 16.1122 23.6196 16.0558 23.6196 15.9993C23.6196 15.9429 23.619 15.8866 23.6177 15.8305C23.6168 15.7886 23.6353 15.7486 23.668 15.7224L26.2903 13.623C26.5474 13.4172 26.6156 13.0544 26.451 12.7692L24.0239 8.56537C23.8592 8.28023 23.5111 8.15797 23.2043 8.27757L20.0758 9.49734C20.0367 9.51258 19.9927 9.50846 19.9569 9.48664C19.8599 9.42762 19.7616 9.37071 19.6618 9.31596C19.6251 9.2958 19.5997 9.25984 19.5933 9.21843L19.0854 5.89915C19.0356 5.57369 18.7556 5.33333 18.4264 5.33333H13.5722ZM16.0001 20.2854C18.3672 20.2854 20.2862 18.3664 20.2862 15.9993C20.2862 13.6322 18.3672 11.7132 16.0001 11.7132C13.6329 11.7132 11.714 13.6322 11.714 15.9993C11.714 18.3664 13.6329 20.2854 16.0001 20.2854Z" fill="currentColor"></path></svg></div> ';
                $button .= '</media-menu-button>';
        
                if ($portalSettings) {
                $button .= '<media-menu-portal data-x-player-id="' . esc_attr( $playerIndentifier ) . '" disabled="fullscreen" >';
                }
                            
                $button .= '<media-menu-items class="xmp-settings-menu-items xmp-menu-items ' . esc_attr( $colorScheme ) . '" placement="' . $settingsPlacementOption . '" align-offset="' . $menuXOffset . '" offset="' . $menuYOffset . '" data-x-player-id="' . esc_attr( $playerIndentifier ) . '" role="menu" style="display: none;">';
        
        
                //$settingsSpeedIcon
        
                /* playback/speed */
        
                $button .= '<media-menu class="xmp-playback-menu xmp-menu">
                                <media-menu-button class="xmp-menu-item" aria-label="'. $speedLabel . '">
                                <div class="xmp-icon xmp-menu-close-icon" type="chevron-left"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M14.8206 15.9049C14.7685 15.9569 14.7685 16.0413 14.8206 16.0934L21.1381 22.4109C21.3984 22.6712 21.3984 23.0934 21.1381 23.3537L19.5353 24.9565C19.275 25.2168 18.8529 25.2168 18.5925 24.9565L11.7386 18.1026C11.7283 18.0935 11.7183 18.084 11.7084 18.0742L10.1057 16.4714C9.84531 16.2111 9.84531 15.789 10.1057 15.5286L18.5909 7.04335C18.8513 6.783 19.2734 6.783 19.5338 7.04335L21.1365 8.64612C21.3969 8.90647 21.3969 9.32858 21.1365 9.58893L14.8206 15.9049Z" fill="currentColor"></path></svg></div> ';
                                
                $button .= $settingsSpeedIcon ? '<div class="xmp-icon xmp-icon-custom xmp-menu-item-icon">'  . $settingsSpeedIcon . '</div>' : '<div class="xmp-icon xmp-menu-playback-icon xmp-menu-item-icon" type="playback-speed-circle"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M13.9213 5.53573C14.3146 5.45804 14.6666 5.76987 14.6666 6.17079V7.57215C14.6666 7.89777 14.4305 8.17277 14.114 8.24925C12.5981 8.61559 11.2506 9.41368 10.2091 10.506C9.98474 10.7414 9.62903 10.8079 9.34742 10.6453L8.14112 9.94885C7.79394 9.7484 7.69985 9.28777 7.96359 8.98585C9.48505 7.24409 11.5636 6.00143 13.9213 5.53573Z" fill="currentColor"></path> <path d="M5.88974 12.5908C6.01805 12.2101 6.46491 12.0603 6.81279 12.2611L8.01201 12.9535C8.29379 13.1162 8.41396 13.4577 8.32238 13.7699C8.11252 14.4854 7.99998 15.2424 7.99998 16.0257C7.99998 16.809 8.11252 17.566 8.32238 18.2814C8.41396 18.5936 8.29378 18.9352 8.01201 19.0979L6.82742 19.7818C6.48051 19.9821 6.03488 19.8337 5.90521 19.4547C5.5345 18.3712 5.33331 17.2091 5.33331 16C5.33331 14.8078 5.5289 13.6613 5.88974 12.5908Z" fill="currentColor"></path> <path d="M8.17106 22.0852C7.82291 22.2862 7.72949 22.7486 7.99532 23.0502C9.51387 24.773 11.5799 26.0017 13.9213 26.4642C14.3146 26.5419 14.6666 26.2301 14.6666 25.8291V24.4792C14.6666 24.1536 14.4305 23.8786 14.114 23.8021C12.5981 23.4358 11.2506 22.6377 10.2091 21.5453C9.98474 21.31 9.62903 21.2435 9.34742 21.4061L8.17106 22.0852Z" fill="currentColor"></path> <path d="M17.3333 25.8291C17.3333 26.2301 17.6857 26.5418 18.079 26.4641C22.9748 25.4969 26.6666 21.1796 26.6666 16C26.6666 10.8204 22.9748 6.50302 18.079 5.5358C17.6857 5.4581 17.3333 5.76987 17.3333 6.17079V7.57215C17.3333 7.89777 17.5697 8.17282 17.8862 8.24932C21.3942 9.09721 24 12.2572 24 16.0257C24 19.7942 21.3942 22.9542 17.8862 23.802C17.5697 23.8785 17.3333 24.1536 17.3333 24.4792V25.8291Z" fill="currentColor"></path> <path d="M14.3961 10.4163C13.9561 10.0911 13.3333 10.4053 13.3333 10.9525L13.3333 21.0474C13.3333 21.5946 13.9561 21.9087 14.3962 21.5836L21.2273 16.5359C21.5879 16.2694 21.5879 15.73 21.2273 15.4635L14.3961 10.4163Z" fill="currentColor"></path></svg></div> ';
        
                $button .= '<span class="xmp-menu-item-label">' . $speedLabel . '</span>
                            <span class="xmp-menu-item-hint" data-part="hint"></span>
                            <div class="xmp-icon xmp-menu-item-open-icon" type="chevron-right"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M17.947 16.095C17.999 16.043 17.999 15.9585 17.947 15.9065L11.6295 9.58899C11.3691 9.32864 11.3691 8.90653 11.6295 8.64618L13.2323 7.04341C13.4926 6.78306 13.9147 6.78306 14.1751 7.04341L21.0289 13.8973C21.0392 13.9064 21.0493 13.9158 21.0591 13.9257L22.6619 15.5285C22.9223 15.7888 22.9223 16.2109 22.6619 16.4713L14.1766 24.9565C13.9163 25.2169 13.4942 25.2169 13.2338 24.9565L11.631 23.3538C11.3707 23.0934 11.3707 22.6713 11.631 22.411L17.947 16.095Z" fill="currentColor"></path></svg></div>  </media-menu-button>
        
        
        
                                <media-menu-items class="xmp-menu-items" role="menu">
        
                                
        
                                <section class="xmp-menu-section xmp-speed-menu-section">
                                    <div class="xmp-menu-section-title">
                                    <header>Speed</header>
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
        
                                <div type="fast-forward"></div> 
                                </div>
                                </div>
                                </section>
        
        
                                <section class="xmp-menu-section xmp-quality-menu-section">
                                <div class="xmp-menu-section-title">
                                    <header>Quality</header>
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
                                    <media-menu-button class="xmp-menu-item" aria-label="'. $captionsLabel . '">
                                    <div class="xmp-icon xmp-menu-close-icon" type="chevron-left"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M14.8206 15.9049C14.7685 15.9569 14.7685 16.0413 14.8206 16.0934L21.1381 22.4109C21.3984 22.6712 21.3984 23.0934 21.1381 23.3537L19.5353 24.9565C19.275 25.2168 18.8529 25.2168 18.5925 24.9565L11.7386 18.1026C11.7283 18.0935 11.7183 18.084 11.7084 18.0742L10.1057 16.4714C9.84531 16.2111 9.84531 15.789 10.1057 15.5286L18.5909 7.04335C18.8513 6.783 19.2734 6.783 19.5338 7.04335L21.1365 8.64612C21.3969 8.90647 21.3969 9.32858 21.1365 9.58893L14.8206 15.9049Z" fill="currentColor"></path></svg></div> ';
                
                        $button .= $settingsCaptionsIcon ? '<div class="xmp-icon xmp-icon-custom xmp-menu-item-icon">'  . $settingsCaptionsIcon . '</div>' : '<div class="xmp-icon xmp-menu-item-icon" type="closed-captions"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M4.6661 6.66699C4.29791 6.66699 3.99943 6.96547 3.99943 7.33366V24.667C3.99943 25.0352 4.29791 25.3337 4.6661 25.3337H27.3328C27.701 25.3337 27.9994 25.0352 27.9994 24.667V7.33366C27.9994 6.96547 27.701 6.66699 27.3328 6.66699H4.6661ZM8.66667 21.3333C8.29848 21.3333 8 21.0349 8 20.6667V11.3333C8 10.9651 8.29848 10.6667 8.66667 10.6667H14C14.3682 10.6667 14.6667 10.9651 14.6667 11.3333V12.6667C14.6667 13.0349 14.3682 13.3333 14 13.3333H10.8C10.7264 13.3333 10.6667 13.393 10.6667 13.4667V18.5333C10.6667 18.607 10.7264 18.6667 10.8 18.6667H14C14.3682 18.6667 14.6667 18.9651 14.6667 19.3333V20.6667C14.6667 21.0349 14.3682 21.3333 14 21.3333H8.66667ZM18 21.3333C17.6318 21.3333 17.3333 21.0349 17.3333 20.6667V11.3333C17.3333 10.9651 17.6318 10.6667 18 10.6667H23.3333C23.7015 10.6667 24 10.9651 24 11.3333V12.6667C24 13.0349 23.7015 13.3333 23.3333 13.3333H20.1333C20.0597 13.3333 20 13.393 20 13.4667V18.5333C20 18.607 20.0597 18.6667 20.1333 18.6667H23.3333C23.7015 18.6667 24 18.9651 24 19.3333V20.6667C24 21.0349 23.7015 21.3333 23.3333 21.3333H18Z" fill="currentColor"></path></svg></div> ';              
                
                
                        $button .=  '<span class="xmp-menu-item-label">'. $captionsLabel . '</span>
                                    <span class="xmp-menu-item-hint" data-part="hint"></span>
                                    <div class="xmp-icon xmp-menu-item-open-icon" type="chevron-right"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M17.947 16.095C17.999 16.043 17.999 15.9585 17.947 15.9065L11.6295 9.58899C11.3691 9.32864 11.3691 8.90653 11.6295 8.64618L13.2323 7.04341C13.4926 6.78306 13.9147 6.78306 14.1751 7.04341L21.0289 13.8973C21.0392 13.9064 21.0493 13.9158 21.0591 13.9257L22.6619 15.5285C22.9223 15.7888 22.9223 16.2109 22.6619 16.4713L14.1766 24.9565C13.9163 25.2169 13.4942 25.2169 13.2338 24.9565L11.631 23.3538C11.3707 23.0934 11.3707 22.6713 11.631 22.411L17.947 16.095Z" fill="currentColor"></path></svg></div> 
                                    </media-menu-button>
                
                                    <media-menu-items class="xmp-menu-items" role="menu">
                                    <media-captions-radio-group class="xmp-captions-radio-group xmp-radio-group" off-label="' . $captionsOffHint . '">
                                        <template>
                                        <media-radio class="xmp-caption-radio xmp-radio">
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
                                    <div class="xmp-menu-item-label">'. $announcementsLabel . '</div>  
                                    <button class="xmp-menu-checkbox" aria-pressed="true" aria-label="'. $announcementsLabel .'"></button>
                                    </div>
                
                                </div>
                                </section>
                
                                </media-menu-items>
                
                
                            </media-menu>';
                
                        $button .=  '</media-menu-items>';
                
                        if ($portalSettings) {
                            $button .= '</media-menu-portal>';
                        }
                
                        $button .=  '</media-menu>';
                
                        break;
        
            case 'time-slider':
        
                $isToolTip = false;
        
                $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";
        
                $button .= '<media-time-slider class="xmp-time-slider xmp-slider repeater-item" data-x-control-visibility="' . $visibility . '">';

                if ('audio' === $provider) {
                    // Get waveform settings
                    $enableWaveform = isset($control['enableWaveform']) ? $control['enableWaveform'] : false;
                    
                    if ($enableWaveform) {
                        // Only add waveform markup if enableWaveform is true
                        $waveformBarWidth = isset($control['waveformBarWidth']) ? $control['waveformBarWidth'] : '';
                        $waveformBarGap = isset($control['waveformBarGap']) ? $control['waveformBarGap'] : 1;
                        $waveformHeight = isset($control['waveformHeight']) ? $control['waveformHeight'] : '26';
                        $waveformColor = isset($control['waveformColor']) ? $control['waveformColor'] : '';
                        $waveformPlayedColor = isset($control['waveformPlayedColor']) ? $control['waveformPlayedColor'] : '';
                        
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
                } else {
                    // For video, always add chapters markup
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
                                <media-slider-thumbnail ' . $thumbnailAttr . '
                                    class="xmp-slider-thumbnail xmp-thumbnail"
                                ></media-slider-thumbnail>
                                <div
                                    class="xmp-slider-chapter-title"
                                    data-part="chapter-title"
                                ></div>
                                <media-slider-value
                                    class="xmp-slider-value"
                                ></media-slider-value>
                                </media-slider-preview>
        
                            </media-time-slider>';
        
                break;
        
            case 'custom-text':
        
                    $isToolTip = false;
            
                    $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";
        
                    $text = isset( $control['text'] ) ? wp_kses_post( bricks_render_dynamic_data( $control['text'] ) ) : '';
            
                    $button .= '<div class="xmp-custom xmp-custom-text repeater-item" data-x-control-visibility="' . $visibility . '">' . $text . '</div>';
            
                    break;
        
        
                case 'live-button';
                
                $isToolTip = false;
                
                $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";
        
                $button .= '<media-live-button class="xmp-live-button repeater-item" data-x-control-visibility="' . $visibility . '">';
                $button .= '<span class="xmp-live-button-text">LIVE</span>';
                $button .= '</media-live-button>';
        
                break;
        
            case 'airplay';
                    
                    $airplayLabel = isset( $control['airplayLabel'] ) ? esc_attr__( $control['airplayLabel'] ) : esc_attr__("Airplay");
                    $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";
            
                    $button .= '<media-airplay-button class="xmp-button repeater-item" data-x-control-visibility="' . $visibility . '">';
                    $button .= $inactiveIcon ? '<div class="xmp-airplay-icon xmp-icon xmp-icon-custom xmp-icon-custom-inactive">'  . $inactiveIcon . '</div>' : '<div type="airplay" class="xmp-icon"></div> ';
                    $button .= '</media-airplay-button>';
            
                    $tooltip .= '<span class="airplay-tooltip-text">' . $airplayLabel . '</span>';
        
                    break;
        
        
            case 'chromecast';
                    
                    $chromecastLabel = isset( $control['chromecastLabel'] ) ? esc_attr__( $control['chromecastLabel'] ) : esc_attr__("Chromecast");
                    $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";
            
                    $button .= '<media-google-cast-button aria-hidden=false class="xmp-button repeater-item" data-x-control-visibility="' . $visibility . '">';
                    $button .= $inactiveIcon ? '<div class="xmp-chrome-cast-icon xmp-icon xmp-icon-custom xmp-icon-custom-inactive">'  . $inactiveIcon . '</div>' : '<div type="chromecast" class="xmp-icon"></div> ';
                    $button .= '</media-google-cast-button>';
            
                    $tooltip .= '<span class="airplay-tooltip-text">' . $airplayLabel . '</span>';
        
                    break;
                    
        
            case 'image':
        
                    $isToolTip = false;
                    $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";
        
                    $image      = \BricksExtras\Helpers::get_normalized_image_settings( $control );
                    $image_id   = $image['id'];
                    $image_url  = $image['url'];
                    $image_size = $image['size'];
        
                    $isToolTip = false;
        
                    $image_placeholder_url = \Bricks\Builder::get_template_placeholder_image();
        
                    $button .= '<div class="xmp-image" data-x-control-visibility="' . $visibility . '">';
        
                    if ( isset( $control['image'] ) ) {
        
                    if ( ! empty( $control['altText'] ) ) {
                        $imageAttr = [
                        'loading' => 'eager',
                        'alt' => esc_attr( $control['altText'] )
                        ]; 
                    } else {
                        $imageAttr = ['loading' => 'eager'];
                    }
                
                    $button .= wp_get_attachment_image( 
                        $image_id, 
                        $image_size, 
                        false,
                        $imageAttr
                    );
                
                    } else {
                    $button .= '<img src="'. esc_url( $image_placeholder_url )  .'">';
                    }
        
                    $button .= '</div>';
        
                    break;
         
            case 'poster-image':
        
                    $isToolTip = false;
                    $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";
        
                    $button .= '<div class="xmp-image repeater-item" data-x-control-type="poster-image" data-x-control-visibility="' . $visibility . '">';
        
                    if ( !\BricksExtras\Helpers::maybePreview() ) {
                        $button .= '<img aria-label="media image" data-x-poster src="" loading="lazy">';
                    } else {
                        $image_placeholder_url = \Bricks\Builder::get_template_placeholder_image();
                        $button .= '<img aria-label="media image" data-x-poster src="' . $image_placeholder_url . '">';
                    }
        
                    $button .= '</div>';
        
                    break;
        
        
            case 'previous':
        
                        $previousLabel = !empty( $control['previousLabel'] ) ? esc_attr__( $control['previousLabel'] ) : esc_attr__( 'Previous' );
                        $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";
                        
                        $button .= '<button class="xmp-button xmp-previous repeater-item" data-x-control-visibility="' . $visibility . '" aria-label="' . $previousLabel . '" data-x-tooltip="' . $previousLabel . '">';
                        $button .= $inactiveIcon ? '<div class="xmp-previous-icon xmp-icon xmp-icon-custom">'  . $inactiveIcon . '</div>' : '<div class="xmp-icon xmp-previous-icon" type="previous"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M25.1377 6.78532C25.5778 6.46017 26.2005 6.77434 26.2005 7.32151V24.6785C26.2005 25.2257 25.5777 25.5398 25.1377 25.2147L13.3924 16.5358C13.0317 16.2693 13.0317 15.7299 13.3924 15.4634L25.1377 6.78532Z" fill="currentColor"></path> <path d="M8 6.6667C8.36819 6.6667 8.66667 6.96518 8.66667 7.33337V24.6667C8.66667 25.0349 8.36819 25.3334 8 25.3334H6C5.63181 25.3334 5.33333 25.0349 5.33333 24.6667V7.33337C5.33333 6.96518 5.63181 6.6667 6 6.6667H8Z" fill="currentColor"></path></svg></div> ';
                        $button .= ' </button>';
        
                        $tooltip .= '<span class="xmp-previous-tooltip-text">' . $previousLabel . '</span>';
        
        
                        break;
        
            case 'next':
        
                        $nextLabel = !empty( $control['nextLabel'] ) ? esc_attr__( $control['nextLabel'] ) : esc_attr__( 'Next' );
                        $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";
                        
                        $button .= '<button class="xmp-button xmp-next repeater-item" data-x-control-visibility="' . $visibility . '" aria-label="' . $nextLabel . '" data-x-tooltip="' . $nextLabel . '">';
                        $button .= $inactiveIcon ? '<div class="xmp-next-icon xmp-icon xmp-icon-custom">'  . $inactiveIcon . '</div>' : '<div class="xmp-icon xmp-next-icon" type="next"><svg width="100%" height="100%" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M6.39617 6.78532C5.9561 6.46017 5.33334 6.77434 5.33334 7.32151V24.6785C5.33334 25.2257 5.95612 25.5398 6.39619 25.2147L18.1415 16.5358C18.5021 16.2693 18.5021 15.7299 18.1415 15.4634L6.39617 6.78532Z" fill="currentColor"></path> <path d="M23.5339 6.6667C23.1657 6.6667 22.8672 6.96518 22.8672 7.33337V24.6667C22.8672 25.0349 23.1657 25.3334 23.5339 25.3334H25.5339C25.902 25.3334 26.2005 25.0349 26.2005 24.6667V7.33337C26.2005 6.96518 25.902 6.6667 25.5339 6.6667H23.5339Z" fill="currentColor"></path></svg></div> ';
                        $button .= ' </button>';
        
                        $tooltip .= '<span class="xmp-next-tooltip-text">' . $nextLabel . '</span>';
        
        
                        break;
        
        
            }
        
            
        
            if ( !!$spacer ) {
                $output .= $spacer;
            } else {
        
            $visibility = isset( $control['visibility'] ) ? esc_attr( $control['visibility'] ) : "default";
             $tooltipPlacementSetting = isset( $control['tooltipPlacement'] ) ? esc_attr( $control['tooltipPlacement'] ) : "default";
            $tooltipPlacement = 'default' !== $tooltipPlacementSetting ? $tooltipPlacementSetting : $defaultTooltipPlacement;
        
            /*if ($isToolTip && $maybeToolTips) {
                $output .= '<media-tooltip data-x-control-visibility="' . $visibility . '" showDelay=2000><media-tooltip-trigger>';
                $output .= $button;
                $output .= '</media-tooltip-trigger><media-tooltip-content class="xmp-tooltip-content" placement="' . $tooltipPlacement . '">';
                $output .= $tooltip;
                $output .= '</media-tooltip-content></media-tooltip>';
        
                if ('disable' !== $volumeSlider) {
                $volumeVisibility = 'visible' === $volumeSlider ? 'xmp-visible-volume' : '';
                $output .= '<media-volume-slider class="xmp-volume-slider xmp-slider ' . $volumeVisibility . '">';
                $output .= '<div class="xmp-slider-track"></div>';
                $output .= '<div class="xmp-slider-track-fill xmp-slider-track"></div>';
                $output .= '<media-slider-preview class="xmp-slider-preview" no-clamp>';
                $output .= '<media-slider-value
                                class="xmp-slider-value"
                                type="pointer"
                                format="percent"
                            ></media-slider-value>';
        
                $output .= '</media-slider-preview>';
                $output .= '<div class="xmp-slider-thumb"></div>';
                $output .= '</media-volume-slider>';
                }
        
            } else { */ 
                $output .= $button;
                if ('disable' !== $volumeSlider) {
                    $volumeVisibility = 'visible' === $volumeSlider ? 'xmp-visible-volume' : '';
                    $output .= '<media-volume-slider orientation="horizontal" class="xmp-volume-slider xmp-slider ' . $volumeVisibility . '">';
                    $output .= '<div class="xmp-slider-track"></div>';
                    $output .= '<div class="xmp-slider-track-fill xmp-slider-track"></div>';
                    /*$output .= '<media-slider-preview class="xmp-slider-preview" no-clamp>';
                    $output .= '<media-slider-value
                                    class="xmp-slider-value"
                                    type="pointer"
                                    format="percent"
                                ></media-slider-value>';
            
                    $output .= '</media-slider-preview>'; */
                    $output .= '<div class="xmp-slider-thumb"></div>';
                    $output .= '</media-volume-slider>';
                }
           /* } */
        
            }
        
            return isset( $control['control'] ) && 'mute' === $control['control'] ? '<div class="xmp-volume repeater-item">' . $output . '</div>' : $output;
    }

   
    /**
     * Set common attributes for media players
     * 
     * @param array $settings The element settings
     * @param string $viewType The view type ('video' or 'audio')
     * @param array $config The player configuration
     * @param string $src The media source URL
     * @param string $layoutType The layout type
     * @param string $positioning The positioning value
     * @param string $playerIndentifier The player identifier
     * @param bool $isGutenberg Whether we're in Gutenberg editor
     */
    protected function set_common_attributes($settings, $viewType, $config, $src, $layoutType, $positioning, $playerIndentifier, $isGutenberg = false) {
        // Basic player configuration
        $this->set_attribute('_root', 'keep-alive', '');
        $this->set_attribute('_root', 'logLevel', 'silent');
        $this->set_attribute('_root', 'data-x-layout', $layoutType);
        
        // Media query attribute
        if (isset($settings['mediaQuery']) && 'enable' === $settings['mediaQuery']) {
            $this->set_attribute('_root', 'x-media-query', '');
        }
        
        // Storage for player state
        if ($playerIndentifier) {
            $this->set_attribute('_root', 'storage', esc_attr($playerIndentifier));
        }
        
        // Clip start and end times
        $clipStart = isset($settings['clipStartTime']) ? $settings['clipStartTime'] : false;
        if ($clipStart) {
            $clipStartTime = \BricksExtras\Helpers::timeToSeconds($this->render_dynamic_data($clipStart));
            $this->set_attribute('_root', 'clip-start-time', esc_attr($clipStartTime));
        }
        
        $clipEnd = isset($settings['clipEndTime']) ? $settings['clipEndTime'] : false;
        if ($clipEnd) {
            $clipEndTime = \BricksExtras\Helpers::timeToSeconds($this->render_dynamic_data($clipEnd));
            $this->set_attribute('_root', 'clip-end-time', esc_attr($clipEndTime));
        }
        
        // View type attributes
        $this->set_attribute('_root', 'data-x-view-type', $viewType);
        $this->set_attribute('_root', 'class', 'xmp-' . $viewType . '-layout');
        $this->set_attribute('_root', 'data-view-type', $viewType);
        $this->set_attribute('_root', 'data-x-media-player', wp_json_encode($config));

        if ( isset($settings['maybeToolTips']) && 'enable' === $settings['maybeToolTips'] && isset($settings['defaultTooltipPlacement']) ) {
            $this->set_attribute('_root', 'data-x-tooltip-placement', esc_attr($settings['defaultTooltipPlacement']));
        }
        
        // Positioning
        if ('bottom' === $positioning) {
            $this->set_attribute('_root', 'data-x-positioning', 'bottom');
        }
        
        // Title, source and other basic attributes
        $title = isset($settings['title']) ? esc_attr__( $settings['title'] ) : '';

        $this->set_attribute('_root', 'title', esc_attr($title));
        $this->set_attribute('_root', 'data-title', esc_attr($title));
        $this->set_attribute('_root', 'src', esc_url($src)); 

        if (isset($settings['artist'])) {
            $this->set_attribute('_root', 'data-artist', esc_attr($settings['artist']));
        }

        
        // Loading type
        $loadingType = isset($settings['loadingType']) ? $settings['loadingType'] : 'visible';
        $this->set_attribute('_root', 'load', esc_attr('click' === $loadingType ? 'custom' : $loadingType));
        
        // Stream type
        $streamType = isset($settings['streamType']) ? $settings['streamType'] : 'on-demand';
        $this->set_attribute('_root', 'stream-type', esc_attr($streamType));
        
        // Loop, muted, autoplay
        $loop = isset($settings['loop']);
        if ($loop && !isset($settings['playlistMode'])) {
            $this->set_attribute('_root', 'loop', esc_attr($loop));
        }
        
        $muted = isset($settings['muted']);
        if ($muted) {
            $this->set_attribute('_root', 'muted', esc_attr($muted));
            $autoplay = isset($settings['autoplay']);
            if ($autoplay && !\BricksExtras\Helpers::maybePreview()) {
            $this->set_attribute('_root', 'autoPlay', esc_attr($autoplay));
            }
        }
        
        // Provider controls
        $providerControls = isset($settings['providerControls']) ? 'enable' === $settings['providerControls'] : false;
        if ($providerControls) {
            $this->set_attribute('_root', 'controls', true);
        }
        
        // Poster loading
        $posterURL = isset($settings['posterURL']) ? $settings['posterURL'] : '';
        if ($posterURL) {
            $this->set_attribute('_root', 'poster-load', 'custom');
        } else {
            $this->set_attribute('_root', 'poster-load', 'eager');
        }

        $crossorigin = isset($settings['crossorigin']) ? $settings['crossorigin'] : 'anonymous';
        
        
        // Crossorigin attribute
        if ('null' !== $crossorigin) {
            $this->set_attribute('_root', 'crossorigin', esc_attr($crossorigin));
        }
        
        // Gutenberg editor class
        if ($isGutenberg) {
            $this->set_attribute('_root', 'class', 'x-media-player_editor');
        }


        $maybeToolTips = isset( $settings['maybeToolTips'] ) ? 'enable' === $settings['maybeToolTips'] : false;
        $this->set_attribute('_root', 'data-x-tooltips', $maybeToolTips);
        
        
        // Get poster URL and alt text from settings
        $posterURL = isset($settings['posterURL']) ? $settings['posterURL'] : '';
        $altText = isset($settings['altText']) ? $settings['altText'] : '';
        $colorScheme = isset($settings['colorScheme']) ? $settings['colorScheme'] : 'dark';
        
        /* media-poster */
        $this->set_attribute('media-poster', 'class', 'xmp-poster');
        $this->set_attribute('media-poster', 'alt', esc_attr__($altText));
        $this->set_attribute('media-poster', 'src', esc_attr($posterURL));
        
        /* media-controls */
        $this->set_attribute('media-controls-group', 'class', 'xmp-controls_group');
        $this->set_attribute('media-controls', 'class', 'xmp-controls ' . esc_attr($colorScheme));
        $this->set_attribute('x-media-player_inner', 'class', 'x-media-player_inner ' . esc_attr($colorScheme));
        $this->set_attribute('x-media-player_hidden-inner', 'class', 'x-media-player_hidden-inner');

        /* fullscreen */
        $this->set_attribute('_root', 'fullscreen-orientation', 'no');

    }

    /**
     * Get media player controls
     * 
     * @param string $mediaType The type of media player (video or audio)
     * @return array Array of control definitions
     */
    protected function get_media_player_controls($mediaType = 'video') {
            

        $controls = [];

        /* small ui */
        $smallRequirement = [
            ['layoutType','!=','custom'],
            ['layoutType','!=','type_two']
        ];
    
        $largeRequirement = [
            ['layoutType','!=','custom'],
            ['layoutType','!=','small']
        ];
        
        $placementOptions = [
            'top' => esc_html__('Top', 'bricks' ), 
            'bottom' => esc_html__( 'Bottom', 'bricks' ), 
            'top-start' => esc_html__( 'Top Start', 'bricks' ), 
            'top-end' => esc_html__( 'Top End', 'bricks' ),
            'bottom-start' => esc_html__( 'Bottom Start', 'bricks' ), 
            'bottom-end' => esc_html__( 'Bottom End', 'bricks' ),
        ];
        
        $controls['sourceType'] = [
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
            'required' => [
            ['playlistMode', '!=', true],
            ],
        ];
        
        
        $controls['src'] = [
            'label'    => esc_html__( 'Media source URL', 'bricks' ),
            'type'        => 'text',
            //'inline'      => true,
            'required' => [
            ['sourceType', '!=', 'media'],
            ['playlistMode', '!=', true],
            ['multipleSources', '!=', true]
            ],
            //'group' => 'mediaSource'
        ];

       
        
        if ($mediaType === 'audio') {

            $controls['srcRepeaterAudio'] = [
                'label'    => esc_html__( 'Media sources', 'bricks' ),
                'info' => esc_html__( 'For manually adding multiple quality URLs or file types.', 'bricks' ),
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
                    ['multipleSources', '=', true]
                ],
                //'group' => 'mediaSource'
            ];
            
            $controls['audio'] = [ 
                'tab'      => 'content',
                'label'    => esc_html__( 'Audio', 'bricks' ),
                'type'     => 'audio',
                'required' => [
                    ['sourceType', '=', 'media'],
                    ['playlistMode', '!=', true],
                ],
            ];
        
        } else {

            $controls['srcRepeater'] = [
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
                    ['multipleSources', '=', true]
                ],
                //'group' => 'mediaSource'
            ];
        
            $controls['media'] = [ 
                'tab'      => 'content',
                'label'    => esc_html__( 'Media', 'bricks' ),
                'type'     => 'video',
                'required' => [
                    ['sourceType', '=', 'media'],
                    ['playlistMode', '!=', true],
                ],
            ];
        
        }
        
        
        
        $controls['title'] = [
            'label'    => esc_html__( 'Title', 'bricks' ),
            'type'        => 'text',
            'inline'      => true,
            'required' => ['playlistMode', '!=', true],
            //'group' => 'mediaSource'
        ];

        if ($mediaType === 'audio') {
            $controls['artist'] = [
                'label'    => esc_html__( 'Artist', 'bricks' ),
                'type'        => 'text',
                'inline'      => true,
                'required' => ['playlistMode', '!=', true],
            ];
        }
        
        $controls['clipStartTime'] = [
            'label'    => esc_html__( 'Clip start', 'bricks' ),
            'type'        => 'text',
            'inline'      => true,
            'small' => true,
            'placeholder' => esc_html__( '00:00', 'bricks' ),
            'required' => [
            ['playlistMode', '!=', true],
            ['providerControls', '!=', 'enable']
            ]
        ];
        
        $controls['clipEndTime'] = [
            'label'    => esc_html__( 'Clip end', 'bricks' ),
            'type'        => 'text',
            'inline'      => true,
            'small' => true,
            'required' => [
            ['playlistMode', '!=', true],
            ['providerControls', '!=', 'enable']
            ]
        ];
        
        $controls['streamType'] = [
            'label'    => esc_html__( 'Stream type', 'bricks' ),
            'type'        => 'select',
            'options'     => [
            'on-demand' => esc_html__( 'On-demand', 'bricks' ),
            'live' => esc_html__( 'Live', 'bricks' ),
            ],
            'placeholder' => esc_html__( 'On-demand', 'bricks' ),
            'inline'      => true,
            'small' => true,
            'required' => [
            ['providerControls', '!=', 'enable']
            ]
        ];
        
        
        
            /* style controls */
        
            $controls['brandColor'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Primary brand color', 'extras' ),
            'info'  => esc_html__( 'Applies color across various controls', 'extras' ),
            'css'    => [
                [
                    'property' => '--media-brand',
                    'selector' => '',
                ],
                [
                    'property' => '--media-menu-checkbox-bg-active',
                    'selector' => '',
                ],
                [
                    'property' => '--media-slider-track-fill-bg',
                    'selector' => '.xmp-slider-track-fill',
                ],
            ],
            ];
        
            $controls['colorScheme'] = [
            'tab'    => 'content',
            'type'   => 'select',
            'group' => 'styleGeneral',
            'inline' => true,
            'small' => true,
            'label'  => esc_html__( 'Menu color scheme', 'extras' ),
            'info' => esc_html__( 'Default color for menus', 'extras' ),
            'placeholder' => esc_html__( 'Dark', 'bricks' ),
            'options'     => [
                'light' => esc_html__( 'Light', 'bricks' ),
                'dark'  => esc_html__( 'Dark', 'bricks' ),
            ],
            ];
        
            $controls['playerColor'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Player background', 'extras' ),
            'css'    => [
                [
                'property' => 'background-color',
                'selector' => '&[data-x-view-type=video]',
                ],
                [
                'property' => 'background-color',
                'selector' => '&[data-x-view-type=audio]',
                ],
            ],
            ];
        
            $controls['focusRingColor'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Focus ring color', 'extras' ),
            'info'  => esc_html__( 'When tabbing through controls', 'extras' ),
            'css'    => [
                [
                'property' => '--media-focus-ring-color',
                'selector' => 'media-layout',
                ],
            ],
            ];
        
            
            
            
            $controls['playerBorder'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units'   => true,
            'small' => true,
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Player border radius', 'extras' ),
            'css'    => [
                [
                'property' => '--media-border-radius',
                'selector' => '&[data-x-view-type=video]',
                ],
                [
                'property' => 'border-radius',
                'selector' => '',
                ],
                [
                'property' => 'border-radius',
                'selector' => '&::before',
                ],
                [
                'property' => 'border-radius',
                'selector' => '.xmp-controls',
                ],
                [
                'property' => 'border-radius',
                'selector' => '.xmp-poster',
                ]
                
            ],
            ];
        
            $controls['controlsPadding'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units'   => true,
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Inner spacing', 'extras' ),
            'info'  => esc_html__( 'Move controls away from edge', 'extras' ),
            'css'    => [
                [
                'property' => '--media-controls-padding',
                'selector' => 'media-layout',
                ],
            ],
            'placeholder' => '8px',
            ];

            if ($mediaType === 'audio') {
        
                $controls['zoneGap'] = [
                'tab'    => 'content',
                'type'   => 'number',
                'units'   => true,
                'group' => 'styleGeneral',
                'label'  => esc_html__( 'Control zones gap', 'extras' ),
                'css'    => [
                    [
                    'property' => 'gap',
                    'selector' => '.xmp-controls',
                    ],
                ],
                'placeholder' => '0',
            ];

            }
        
            $controls['controlsStyleSep'] = [
            'group' => 'styleGeneral',
            'label'    => esc_html__( 'Buttons', 'bricks' ),
            'type'        => 'separator',
            ];
        
        
            $controls['iconSize'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units'   => true,
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Icon size', 'extras' ),
            'info'  => esc_html__( 'Increase control size also if need to go larger', 'extras' ),
            'css'    => [
                [
                'property' => '--media-button-icon-size',
                'selector' => 'media-layout',
                ],
            ],
            'placeholder' => '24px'
            ];
        
            $controls['controlSize'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units'   => true,
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Control size', 'extras' ),
            'css'    => [
                [
                'property' => '--media-button-size',
                'selector' => 'media-layout',
                ],
            ],
            'placeholder' => '36px'
            ];
        
            $controlButton = '.xmp-button';
            $controlButtonActive = '[data-pressed].xmp-button';
        
            $controls['iconColor'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Icon color', 'extras' ),
            'css'    => [
                [
                'property' => 'color',
                'selector' => $controlButton
                ],
            ],
            ];
        
            $controls['controlBg'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Background color', 'extras' ),
            'css'    => [
                [
                'property' => 'background-color',
                'selector' => $controlButton
                ],
            ],
            ];
        
            $controls['controlBorder'] = [
            'tab'    => 'content',
            'type'   => 'border',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Border', 'extras' ),
            'css'    => [
                [
                'property' => 'border',
                'selector' => $controlButton
                ],
            ],
            ];

            $controls['controlGap'] = [
                'tab'    => 'content',
                'type'   => 'number',
                'units'   => true,
                'group' => 'styleGeneral',
                'label'  => esc_html__( 'Gap', 'extras' ),
                'css'    => [
                    [
                    'property' => 'gap',
                    'selector' => '.xmp-controls_group'
                    ],
                ],
                ];
        
            $controls['buttonActiveStart'] = [
            'group' => 'styleGeneral',
            'type'        => 'separator',
            ];
        
            $controls['iconColorActive'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Color (active)', 'extras' ),
            'css'    => [
                [
                'property' => 'color',
                'selector' => $controlButtonActive
                ],
            ],
            ];
        
            $controls['controlBgActive'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Background color (active)', 'extras' ),
            'css'    => [
                [
                'property' => 'background-color',
                'selector' => $controlButtonActive
                ],
            ],
            ];
        
            $controls['controlBorderActive'] = [
            'tab'    => 'content',
            'type'   => 'border',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Border (active)', 'extras' ),
            'css'    => [
                [
                'property' => 'border',
                'selector' => $controlButtonActive
                ],
            ],
            ];

            $controls['controlTransition'] = [
                'tab'    => 'content',
                'type'   => 'number',
                'group' => 'styleGeneral',
                'placeholder' => '0.2s', 
                'units' => true,
                'inline' => true,
                'label'  => esc_html__( 'Button transition duration', 'extras' ),
                'css'    => [
                    [
                    'property' => '--media-control-duration',
                    'selector' => 'media-layout' 
                    ],
                ],
            ];
        
            $controls['buttonActiveEnd'] = [
            'group' => 'styleGeneral',
            'type'        => 'separator',
            ];
        
            
        
            $controls['controlPadding'] = [
            'tab'    => 'content',
            'type'   => 'dimensions',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Padding', 'extras' ),
            'css'    => [
                [
                'property' => 'padding',
                'selector' => $controlButton
                ],
                [
                'property' => 'padding',
                'selector' => '.xmp-time-group'
                ],
            ],
        
            
            ];
        
            $controls['controlsSlidersSep'] = [
            'group' => 'styleGeneral',
            'label'    => esc_html__( 'Sliders', 'bricks' ),
            'type'        => 'separator',
            ];
        
            $controlSliders = '.xmp-slider';
        
            $controls['controlsSliderHeight'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units'  => true,
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Track height', 'extras' ),
            'placeholder' => '3px',
            'css'    => [
                [
                'property' => '--track-height',
                'selector' => $controlSliders
                ],
            ],
            ];
        
            $controls['controlsSliderFocusHeight'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units'  => true,
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Track focus height', 'extras' ),
            'placeholder' => '9px',
            'css'    => [
                [
                'property' => '--track-focus-height',
                'selector' => $controlSliders
                ],
            ],
            ];
        
            $controls['controlsSliderWidth'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units'  => true,
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Track width', 'extras' ),
            'placeholder' => '100%',
            'css'    => [
                [
                'property' => '--track-width',
                'selector' => $controlSliders
                ],
            ],
            ];
            
        
            $controls['controlsSliderstrackBackground'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Track color', 'extras' ),
            'css'    => [
                [
                'property' => '--media-slider-track-bg',
                'selector' => $controlSliders
                ],
            ],
            ];
        
            $controls['controlsSliderstrackProgressBackground'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Track progress color', 'extras' ),
            'css'    => [
                [
                'property' => '--media-slider-track-progress-bg',
                'selector' => $controlSliders
                ],
            ],
            ];
        
            
        
            $controls['controlsSliderstrackFillBackground'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Fill color', 'extras' ),
            'css'    => [
                [
                'property' => '--media-slider-track-fill-bg',
                'selector' => $controlSliders
                ],
            ],
            ];
        
            
        
            $controls['controlsSliderstimeSliderThumbSep'] = [
            'group' => 'styleGeneral',
            'type'        => 'separator',
            ];
        
            $controls['controlsSlidersthumbColor'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Thumb color', 'extras' ),
            'css'    => [
                [
                'property' => '--media-slider-thumb-bg',
                'selector' => $controlSliders
                ],
            ],
            ];
        
            $controls['controlsSlidersthumbSize'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units'   => true,
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Thumb size', 'extras' ),
            'css'    => [
                [
                'property' => '--thumb-size',
                'selector' => $controlSliders
                ],
            ],
            'placeholder' => '15px'
            ];
        
            $controls['controlsSlidersthumbBorder'] = [
            'tab'    => 'content',
            'type'   => 'border',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Thumb border', 'extras' ),
            'css'    => [
                [
                'property' => 'border',
                'selector' => '.xmp-time-slider .xmp-slider-thumb'
                ],
            ],
            ];
        
            $controls['controlsSlidersfocusThumbSize'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units'   => true,
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Thumb size (focus)', 'extras' ),
            'css'    => [
                [
                'property' => '--thumb-focus-size',
                'selector' => $controlSliders
                ],
            ],
            'placeholder' => '17px'
            ];
        
        
        
            /* checkboxes */
        
            $controls['settingsMenuCheckboxSep'] = [
            'group' => 'styleGeneral',
            'type'        => 'separator',
            'label'  => esc_html__( 'Checkboxes', 'extras' ),
            'description' => esc_html__( 'Announcer & loop controls.', 'extras' ),
            ];
        
            $menuCheckbox = '.xmp-menu-checkbox';

            $controls['settingsCheckboxBackground'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Checkbox background', 'extras' ),
            'css'    => [
                [
                'property' => '--checkbox-bg',
                'selector' => $menuCheckbox
                ],
            ],
            ];

            $controls['settingsCheckboxActiveBackground'] = [
                'tab'    => 'content',
                'type'   => 'color',
                'group' => 'styleGeneral',
                'label'  => esc_html__( 'Checkbox background (active)', 'extras' ),
                'css'    => [
                    [
                    'property' => '--checkbox-active-bg',
                    'selector' => $menuCheckbox
                    ],
                ],
                ];
        
            $controls['settingsCheckboxControlBackground'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Checkbox control', 'extras' ),
            'css'    => [
                [
                'property' => '--checkbox-handle-bg',
                'selector' => $menuCheckbox
                ],
            ],
            ];
        
           
        
            $controls['settingsCheckboxHeight'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units' => true,
            'group' => 'styleGeneral',
            'placeholder' => '18px',
            'label'  => esc_html__( 'Checkbox height', 'extras' ),
            'css'    => [
                [
                'property' => '--media-menu-checkbox-height',
                'selector' => $menuCheckbox
                ],
            ],
            ];
        
            $controls['settingsCheckboxWidth'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units' => true,
            'group' => 'styleGeneral',
            'placeholder' => '40px',
            'label'  => esc_html__( 'Checkbox width', 'extras' ),
            'css'    => [
                [
                'property' => '--media-menu-checkbox-width',
                'selector' => $menuCheckbox
                ],
            ],
            ];
        
            $controls['settingsCheckboxGap'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units' => true,
            'group' => 'styleGeneral',
            'placeholder' => '2.5px',
            'label'  => esc_html__( 'Checkbox gap', 'extras' ),
            'css'    => [
                [
                'property' => '--media-menu-checkbox-gap',
                'selector' => $menuCheckbox
                ],
            ],
            ];
        
        
            
        
        
            /* settings menu */
        
        
            $settingsMenuSelector = ".xmp-menu-items:not([data-submenu])";
            $settingsMenuItemSelector = ".xmp-menu-items [role=menuitemradio]";
            $settingsMenuItemRadioSelector = ".xmp-menu-items [role=menuitem]";
        
            $controls['settingsMenuSep'] = [
            'group' => 'styleGeneral',
            'label'    => esc_html__( 'Menus', 'bricks' ),
            'type'        => 'separator',
            'desc' => esc_html__( 'Settings & chapters menus', 'bricks' ),
            ];
        
            $controls['menuBg'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Background', 'extras' ),
            'css'    => [
                [
                'property' => 'background-color',
                'selector' => $settingsMenuSelector,
                //'id' => '#maybe'
                ],
            ],
            ];
        
            $controls['defaultTextColor'] = [
                'tab'    => 'content',
                'type'   => 'color',
                'group' => 'styleGeneral',
                'label'  => esc_html__( 'Primary menu text color', 'extras' ),   
                'css'    => [
                    [
                    'property' => '--default-text',
                    'selector' => '.xmp-menu-items',
                    ],
                ],
            ];

            $controls['defaultSecondaryTextColor'] = [
                'tab'    => 'content',
                'type'   => 'color',
                'group' => 'styleGeneral',
                'label'  => esc_html__( 'Secondary menu text color', 'extras' ),   
                'css'    => [
                    [
                    'property' => '--text-secondary-color',
                    'selector' => '.xmp-menu-items',
                    ],
                ],
            ];
            
        
            $controls['menuBorder'] = [
            'tab'    => 'content',
            'type'   => 'border',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Border', 'extras' ),
            'css'    => [
                [
                'property' => 'border',
                'selector' => $settingsMenuSelector,
                ],
            ],
            
            ];
        
            
            $controls['menuPadding'] = [
            'tab'    => 'content',
            'type'   => 'dimensions',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Padding', 'extras' ),
            'css'    => [
                [
                'property' => 'padding',
                'selector' => $settingsMenuSelector,
                ],
            ],
            
            ];
        
            $controls['menuMinWidth'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units'   => true,
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Min width', 'extras' ),
            'css'    => [
                [
                'property' => '--media-menu-min-width',
                'selector' => '.xmp-menu-items',
                ],
            ],
            'placeholder' => '220px',
            ];
        
            $controls['settingsMenuMaxWidth'] = [
            'group' => 'styleGeneral',
            'label'    => esc_html__( 'Max Width', 'bricks' ),
            'type'        => 'number',
            'units'       => true,
            'css' => [
                [
                'property' => 'max-width',
                'selector' => '.xmp-settings-menu-items',
            ],
            [
                'property' => 'max-width',
                'selector' => '.xmp-chapters-menu-items',
            ],
            ]
            ];

            $controls['settingsMenuMaxHeight'] = [
            'group' => 'styleGeneral',
            'label'    => esc_html__( 'Max Height', 'bricks' ),
            'type'        => 'number',
            'units'       => true,
            'placeholder' => '340px',
            'css' => [
                [
                    'property' => '--media-menu-max-height',
                    'selector' => 'media-layout',
                ],
                [
                    'property' => '--media-menu-video-max-height',
                    'selector' => 'media-layout',
                ],
                ]
            ];

            $controls['settingsMenuItemsSep'] = [
                'group' => 'styleGeneral',
                'label'    => esc_html__( 'Menu items', 'bricks' ),
                'type'        => 'separator',
            ];

            $controls['menuItemBorderRadius'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units'  => true,
            'small' => true,
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Menu item border radius', 'extras' ),
            'css'    => [
                [
                'property' => '--item-border-radius',
                'selector' => '.xmp-menu-items',
                ],
                [
                    'property' => '--item-border-radius',
                    'selector' => '.xmp-radio-group',
                ]
            ],
            ];
            
            
            $controls['menuItemGap'] = [
                'tab'    => 'content',
                'type'   => 'number',
                'units'  => true,
                'group' => 'styleGeneral',
                'label'  => esc_html__( 'Menu item gap', 'extras' ),
                'css'    => [
                    [
                        'property' => 'gap',
                        'selector' => '.xmp-radio-group',
                    ],
                    [
                        'property' => 'margin-bottom',
                        'selector' => 'media-menu-button.xmp-menu-item',
                    ]
                ],
            ];

            
            /*
            $controls['menuItemPadding'] = [
                'tab'    => 'content',
                'type'   => 'number',
                'units'  => true,
                'group' => 'styleGeneral',
                'label'  => esc_html__( 'Menu item padding', 'extras' ),
                'css'    => [
                    [
                    'property' => '--media-menu-item-padding',
                    'selector' => '.xmp-menu-items',
                    ],
                ],
            ]; */
                
        
            $controls['menuItemTypography'] = [
            'tab'    => 'content',
            'type'   => 'typography',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Menu label Typography', 'extras' ),
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
        
            $controls['menuItemHitTypography'] = [
            'tab'    => 'content',
            'type'   => 'typography',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Hint Typography', 'extras' ),
            'css'    => [
                [
                'property' => 'font',
                'selector' => '.xmp-menu-item-hint',
                ],
            ],
            
            ];
        
            $controls['menuItemIconSize'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units'  => true,
            'group' => 'styleGeneral',
            'small' => true,
            'label'  => esc_html__( 'Menu icon size', 'extras' ),
            'css'    => [
                [
                'property' => '--item-icon-size',
                'selector' => '.xmp-menu-item',
                ],
            ],
            ];
        
            $controls['menuItemIconCloseSize'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units'  => true,
            'small' => true,
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Open/Close arrow size', 'extras' ),
            'css'    => [
                [
                'property' => '--size',
                'selector' => '.xmp-menu-close-icon',
                ],
                [
                'property' => '--size',
                'selector' => '.xmp-menu-item-open-icon',
                ],
            ],
            ];

            $menuSection = '.xmp-menu-section-body';
            
            $controls['settingsMenuSectionsSep'] = [
                'group' => 'styleGeneral',
                'label'    => esc_html__( 'Menu sections', 'bricks' ),
                'type'        => 'separator',
            ];

            $controls['settingsMenuSectionBg'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Background', 'extras' ),
            'css'    => [
                [
                'property' => '--section-bg',
                'selector' => $menuSection,
                ],
            ],
            ];

            $controls['settingsMenuSectionBorder'] = [
            'tab'    => 'content',
            'type'   => 'border',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Border', 'extras' ),
            'css'    => [
                [
                'property' => 'border',
                'selector' => $menuSection,
                ],
                ],
            ];

            $controls['settingsMenuSectionGap'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units'  => true,
            'small' => true,
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Gap', 'extras' ),
            'css'    => [
                [
                'property' => 'gap',
                'selector' => '.xmp-playback-menu .xmp-menu-items',
                ],
            ],
            ];


            /* cues */
        
        
            $controls['cuesSep'] = [
            'group' => 'styleGeneral',
            'label'    => esc_html__( 'Caption Cues', 'bricks' ),
            'type'        => 'separator',
            ];
            
            $cue = '.xmp-captions [data-part=cue]';
        
            $controls['cueBg'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Background', 'extras' ),
            'css'    => [
                [
                'property' => 'background-color',
                'selector' => $cue,
                ],
            ],
            ];
        
            $controls['cueBorder'] = [
            'tab'    => 'content',
            'type'   => 'border',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Border', 'extras' ),
            'css'    => [
                [
                'property' => 'border',
                'selector' => $cue
                ],
            ],
            ];
            
        
            $controls['cueTypography'] = [
            'tab'    => 'content',
            'type'   => 'typography',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Typography', 'extras' ),
            'css'    => [
                [
                'property' => 'font',
                'selector' => $cue
                ],
            ],
            ];
        
            $controls['cuePadding'] = [
            'tab'    => 'content',
            'type'   => 'dimensions',
            'group' => 'styleGeneral',
            'label'  => esc_html__( 'Padding', 'extras' ),
            'css'    => [
                    [
                        'property' => 'padding',
                        'selector' => $cue
                    ],
                ],
            ];

            $controls['captionsPadding'] = [
                'tab'    => 'content',
                'type'   => 'dimensions',
                'group' => 'styleGeneral',
                'label'  => esc_html__( 'Captions overlay padding', 'extras' ),
                'css'    => [
                        [
                            'property' => '--media-captions-padding',
                            'selector' => '.xmp-media-features'
                        ],
                    ],
                ];
        
            


            $controls['cueBottom'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'group' => 'styleGeneral',
            'units' => true,
            'info' => esc_html__( 'From bottom of player when controls visible', 'extras' ),
            'label'  => esc_html__( 'Captions offset', 'extras' ),
            'css'    => [
                [
                'property' => '--video-captions-offset', 
                'selector' => 'xmp-media-features'
                ],
            ],
            'placeholder' => '80px',
            ];
        
        
        
            /* individual control */
        
            $controls['controlsStyleSepPlay'] = [
            'group' => 'styleControls',
            'label'    => esc_html__( 'Play button', 'bricks' ),
            'type'        => 'separator',
            ];
        
            $play = '.xmp-play-button';
        
            $controls['iconSizePlay'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units'   => true,
            'group' => 'styleControls',
            'label'  => esc_html__( 'Icon size', 'extras' ),
            'css'    => [
                [
                'property' => '--media-button-icon-size',
                'selector' => $play,
                ],
                
            ],
            'placeholder' => '38px'
            ];
        
            $controls['controlSizePlay'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units'   => true,
            'group' => 'styleControls',
            'label'  => esc_html__( 'Control size', 'extras' ),
            'css'    => [
                [
                'property' => '--media-button-size',
                'selector' => $play,
                ],
            ],
            'placeholder' => '50px'
            ];
        
            
        
            $controls['controlBgPlay'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'styleControls',
            'label'  => esc_html__( 'Background', 'extras' ),
            'css'    => [
                [
                'property' => 'background-color',
                'selector' => $play,
                ],
            ],
            ];
        
            $controls['iconColorPlay'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'styleControls',
            'label'  => esc_html__( 'Color', 'extras' ),
            'css'    => [
                [
                'property' => 'color',
                'selector' => $play
                ],
            ],
            ];
        
            $controls['controlBorderPlay'] = [
            'tab'    => 'content',
            'type'   => 'border',
            'group' => 'styleControls',
            'label'  => esc_html__( 'Border', 'extras' ),
            'css'    => [
                [
                'property' => 'border',
                'selector' => $play,
                ],
            ],
            ];

            $playActive = '.xmp-play-button[data-pressed]';

            $controls['iconColorPlayActive'] = [
                'tab'    => 'content',
                'type'   => 'color',
                'group' => 'styleControls',
                'label'  => esc_html__( 'Color (active)', 'extras' ),
                'css'    => [
                    [
                    'property' => 'color',
                    'selector' => $playActive
                    ],
                ],
            ];
        
            $controls['controlBgPlayActive'] = [
                'tab'    => 'content',
                'type'   => 'color',
                'group' => 'styleControls',
                'label'  => esc_html__( 'Background color (active)', 'extras' ),
                'css'    => [
                    [
                    'property' => 'background-color',
                    'selector' => $playActive
                    ],
                ],
            ];
        
            $controls['controlBorderPlayActive'] = [
                'tab'    => 'content',
                'type'   => 'border',
                'group' => 'styleControls',
                'label'  => esc_html__( 'Border (active)', 'extras' ),
                'css'    => [
                    [
                    'property' => 'border',
                    'selector' => $playActive
                    ],
                ],
            ];
        
            $controls['controlPaddingPlay'] = [
            'tab'    => 'content',
            'type'   => 'dimensions',
            'group' => 'styleControls',
            'label'  => esc_html__( 'Padding', 'extras' ),
            'css'    => [
                [
                'property' => 'padding',
                'selector' => $play
                ],
            ],
            ];
        
        
        
            
        
            $controls['controlsStyleSepPlayLarge'] = [
            'group' => 'styleControls',
            'label'    => esc_html__( 'Play (large)', 'bricks' ),
            'type'        => 'separator',
            ];
        
            $largePlay = '.xmp-button-large';
        
            $controls['iconSizePlayLarge'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units'   => true,
            'group' => 'styleControls',
            'label'  => esc_html__( 'Icon size', 'extras' ),
            'css'    => [
                [
                'property' => '--media-button-icon-size',
                'selector' => $largePlay,
                ],
                
            ],
            'placeholder' => '38px'
            ];
        
            $controls['controlSizePlayLarge'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units'   => true,
            'group' => 'styleControls',
            'label'  => esc_html__( 'Control size', 'extras' ),
            'css'    => [
                [
                'property' => '--media-button-size',
                'selector' => $largePlay,
                ],
            ],
            'placeholder' => '50px'
            ];
        
            
        
            $controls['controlBgPlayLarge'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'styleControls',
            'label'  => esc_html__( 'Background', 'extras' ),
            'css'    => [
                [
                'property' => 'background-color',
                'selector' => $largePlay,
                ],
            ],
            ];
        
            $controls['iconColorPlayLarge'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'styleControls',
            'label'  => esc_html__( 'Color', 'extras' ),
            'css'    => [
                [
                'property' => 'color',
                'selector' => $largePlay
                ],
            ],
            ];
        
            $controls['controlBorderPlayLarge'] = [
            'tab'    => 'content',
            'type'   => 'border',
            'group' => 'styleControls',
            'label'  => esc_html__( 'Border', 'extras' ),
            'css'    => [
                [
                'property' => 'border',
                'selector' => $largePlay,
                ],
            ],
            ];

            $playLargeActive = '.xmp-button-large[data-pressed]';

            $controls['iconColorPlayLargeActive'] = [
                'tab'    => 'content',
                'type'   => 'color',
                'group' => 'styleControls',
                'label'  => esc_html__( 'Color (active)', 'extras' ),
                'css'    => [
                    [
                    'property' => 'color',
                    'selector' => $playLargeActive
                    ],
                ],
            ];
        
            $controls['controlBgPlayLargeActive'] = [
                'tab'    => 'content',
                'type'   => 'color',
                'group' => 'styleControls',
                'label'  => esc_html__( 'Background color (active)', 'extras' ),
                'css'    => [
                    [
                    'property' => 'background-color',
                    'selector' => $playLargeActive
                    ],
                ],
            ];
        
            $controls['controlBorderPlayLargeActive'] = [
                'tab'    => 'content',
                'type'   => 'border',
                'group' => 'styleControls',
                'label'  => esc_html__( 'Border (active)', 'extras' ),
                'css'    => [
                    [
                    'property' => 'border',
                    'selector' => $playLargeActive
                    ],
                ],
            ];
        
            $controls['controlPaddingPlayLarge'] = [
            'tab'    => 'content',
            'type'   => 'dimensions',
            'group' => 'styleControls',
            'label'  => esc_html__( 'Padding', 'extras' ),
            'css'    => [
                [
                'property' => 'padding',
                'selector' => $largePlay
                ],
            ],
            ];
        
            
            $controls['timeSliderStyleSep'] = [
            'group' => 'styleControls',
            'label'    => esc_html__( 'Time slider', 'bricks' ),
            'type'        => 'separator',
            ];
        
            $timeSliderSelector = '.xmp-time-slider.xmp-slider';
        
            $controls['trackHeight'] = [
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
            'placeholder' => '5px'
            ];
        
            $controls['sliderHeight'] = [
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
            'placeholder' => '30px'
            ];
        
            $controls['fullWidthTimeSliderPadding'] = [
            'tab'    => 'content',
            'type'   => 'dimensions',
            'group' => 'styleControls',
            'label'  => esc_html__( 'Full Width Time Slider Padding', 'extras' ),
            'css'    => [
                [
                'property' => 'padding',
                'selector' => '.xmp-controls_group_time-slider'
                ],
            ],
            ];
        
        
            $controls['timeSliderTrackSep'] = [
            'group' => 'styleControls',
            'type'        => 'separator',
            ];
        
        
            $controls['trackFillBackground'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'styleControls',
            'label'  => esc_html__( 'Track progress color', 'extras' ),
            'css'    => [
                [
                'property' => '--media-slider-track-fill-bg',
                'selector' => $timeSliderSelector
                ],
            ],
            ];
        
            $controls['trackProgressBackground'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'styleControls',
            'label'  => esc_html__( 'Track buffered color', 'extras' ),
            'css'    => [
                [
                'property' => '--media-slider-track-progress-bg',
                'selector' => $timeSliderSelector
                ],
            ],
            ];
        
            $controls['trackBackground'] = [
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
            ];
        
            $controls['trackLiveBackground'] = [
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
            ];
        
            
        
            $controls['timeSliderThumbSep'] = [
            'group' => 'styleControls',
            'type'        => 'separator',
            ];
        
            $controls['thumbColor'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'styleControls',
            'label'  => esc_html__( 'Thumb color', 'extras' ),
            'css'    => [
                [
                'property' => '--media-slider-thumb-bg',
                'selector' => $timeSliderSelector
                ],
            ],
            ];
        
            $controls['thumbSize'] = [
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
            'placeholder' => '15px'
            ];
        
            $controls['thumbBorder'] = [
            'tab'    => 'content',
            'type'   => 'border',
            'group' => 'styleControls',
            'label'  => esc_html__( 'Thumb border', 'extras' ),
            'css'    => [
                [
                'property' => 'border',
                'selector' => '.xmp-time-slider .xmp-slider-thumb'
                ],
            ],
            ];
        
            $controls['focusThumbSize'] = [
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
            'placeholder' => '17px'
            ];
        
            $controls['sliderValueSep'] = [
            'group' => 'styleControls',
            'label'    => esc_html__( 'Time slider value', 'bricks' ),
            'type'        => 'separator',
            ];
            
            $sliderValueSelector = '.xmp-time-slider .xmp-slider-value';
        
            $controls['sliderValueDisplay'] = [
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
            ];
        
            $controls['sliderOffset'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units' => true,
            'group' => 'styleControls',
            'label'  => esc_html__( 'Value Y Offset', 'extras' ),
            'css'    => [
                [
                'property' => '--media-slider-preview-offset',
                'selector' => '.xmp-time-slider',
                ],
            ],
            ];
        
            $controls['sliderValueBg'] = [
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
            ];
        
            $controls['sliderValueBorder'] = [
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
            ];
            
        
            $controls['sliderValueTypography'] = [
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
            ];
        
            $controls['sliderValuePadding'] = [
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
            ];
        
            /* slider chapter title*/
        
            $controls['sliderChapterTitleSep'] = [
            'group' => 'styleControls',
            'label'    => esc_html__( 'Slider chapter value', 'bricks' ),
            'type'        => 'separator',
            ];
            
            $sliderChapterTitle = '.xmp-slider-chapter-title';
        
            $controls['sliderChapterDisplay'] = [
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
            ];
        
            $controls['sliderChapterTitleBg'] = [
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
            ];
        
            $controls['sliderChapterTitleBorder'] = [
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
            ];
            
        
            $controls['sliderChapterTitleTypography'] = [
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
            ];
        
            $controls['sliderChapterTitlePadding'] = [
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
            ];
        
        
        
        
        
            /* time */
        
        
            $controls['timeSep'] = [
            'group' => 'styleControls',
            'label'    => esc_html__( 'Time', 'bricks' ),
            'type'        => 'separator',
            ];
            
            $time = '.xmp-time';
            $currentTime = '.xmp-time[data-type=current]';
            $divider = '.xmp-time-divider';
            $duration = '.xmp-time[data-type=duration]';
        
            $controls['timeTypography'] = [
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
            ];
        
            $controls['dividerTypography'] = [
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
            ];
            
        
            $controls['timeGap'] = [
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
            ],
            ];

            /* title */
        
            $controls['titleSep'] = [
                'group' => 'styleControls',
                'label'    => esc_html__( 'Title', 'bricks' ),
                'type'        => 'separator',
            ];

            $controls['titleTypography'] = [
            'tab'    => 'content',
            'type'   => 'typography',
            'group' => 'styleControls',
            'label'  => esc_html__( 'Typography', 'extras' ),
            'css'    => [
                [
                'property' => 'font',
                'selector' => '.xmp-title',
                ],
            ],
            ];

            /* artist */

            $controls['artistSep'] = [
                'group' => 'styleControls',
                'label'    => esc_html__( 'Artist', 'bricks' ),
                'type'        => 'separator',
            ];
            
            $controls['artistTypography'] = [
            'tab'    => 'content',
            'type'   => 'typography',
            'group' => 'styleControls',
            'label'  => esc_html__( 'Typography', 'extras' ),
            'css'    => [
                [
                'property' => 'font',
                'selector' => '.xmp-artist',
                ],
            ],
            ];

            $controls['artistPrefixTypography'] = [
            'tab'    => 'content',
            'type'   => 'typography',
            'group' => 'styleControls',
            'label'  => esc_html__( 'Prefix Typography', 'extras' ),
            'css'    => [
                [
                'property' => 'font',
                'selector' => '.xmp-artist_prefix',
                ],
            ],
            ];

            $controls['artistSuffixTypography'] = [
            'tab'    => 'content',
            'type'   => 'typography',
            'group' => 'styleControls',
            'label'  => esc_html__( 'Suffix Typography', 'extras' ),
            'css'    => [
                [
                'property' => 'font',
                'selector' => '.xmp-artist_suffix',
                ],
            ],
            ];

            $controls['artistGap'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units'   => true,
            'group' => 'styleControls',
            'label'  => esc_html__( 'Gap', 'extras' ),
            'css'    => [
                [
                'property' => 'gap',
                'selector' => '.xmp-artist'
                ],
            ],
            ];
        
        
        
            $controls['volumeSliderStyleSep'] = [
            'group' => 'styleControls',
            'label'    => esc_html__( 'Volume slider', 'bricks' ),
            'type'        => 'separator',
            ];
        
            $volumeSliderSelector = '.xmp-volume-slider.xmp-slider';
        
            $controls['sliderHeightVolume'] = [
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
            'placeholder' => '40px'
            ];
        
            $controls['trackHeightVolume'] = [
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
            'placeholder' => '3px'
            ];
        
            $controls['trackWidthVolume'] = [
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
            'placeholder' => '80px'
            ];
        
            
        
            $controls['timeSliderTrackSep'] = [
            'group' => 'styleControls',
            'type'        => 'separator',
            ];
        
        
            $controls['trackFillBackgroundVolume'] = [
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
            ];
        
            $controls['trackProgressBackgroundVolume'] = [
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
            ];
        
            $controls['trackBackgroundVolume'] = [
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
            ];
        
            $controls['timeSliderThumbSepVolume'] = [
            'group' => 'styleControls',
            'type'        => 'separator',
            ];
        
            $controls['thumbColorVolume'] = [
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
            ];
        
            $controls['thumbSizeVolume'] = [
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
            'placeholder' => '15px'
            ];
        
            $controls['thumbBorderVolume'] = [
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
            ];
        
            $controls['focusThumbSizeVolume'] = [
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
            'placeholder' => '17px'
            ];
        
            /*
            $controls['sliderValueSepVolume'] = [
            'group' => 'styleControls',
            'label'    => esc_html__( 'Volume time value', 'bricks' ),
            'type'        => 'separator',
            ];
            
            $volumeValueSelector = '.xmp-volume-slider .xmp-slider-value';
        
            
            $controls['volumeValueOffset'] = [
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
            ];
        
            $controls['sliderValueBgVolume'] = [
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
            ];
        
            $controls['sliderValueBorderVolume'] = [
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
            ];
            
        
            $controls['sliderValueTypographyVolume'] = [
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
            ];
        
            $controls['sliderValuePaddingVolume'] = [
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
            ];

            */
        
            /* live button */
        
            $controls['liveButtonSep'] = [
            'group' => 'styleControls',
            'type'        => 'separator',
            'label'  => esc_html__( 'Live button control', 'extras' ),
            'required' => [
                ['layoutTypeAudio','!=','custom'],
            ],
            ];
        
            $controls['liveButtonTypography'] = [
            'tab'    => 'content',
            'type'   => 'typography',
            'group' => 'styleControls',
            'label'  => esc_html__( 'Typography', 'extras' ),
            'css'    => [
                [
                'property' => 'font',
                'selector' => '.xmp-live-button-text'
                ],
            ],
            'required' => [
                ['layoutTypeAudio','!=','custom'],
            ],
            ];
        
            $controls['liveButtonBackground'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'styleControls',
            'label'  => esc_html__( 'Background', 'extras' ),
            'css'    => [
                [
                'property' => '--media-live-button-edge-bg',
                'selector' => '.xmp-live-button' 
                ],
            ],
            ];
        
            $controls['liveButtonBorder'] = [
            'tab'    => 'content',
            'type'   => 'border',
            'group' => 'styleControls',
            'label'  => esc_html__( 'Border', 'extras' ),
            'css'    => [
                [
                'property' => 'border',
                'selector' => '.xmp-live-button-text'
                ],
            ],
            ];
        
            $controls['liveButtonPadding'] = [
            'tab'    => 'content',
            'type'   => 'dimensions',
            'group' => 'styleControls',
            'label'  => esc_html__( 'Padding', 'extras' ),
            'css'    => [
                [
                'property' => 'padding',
                'selector' => '.xmp-live-button-text'
                ],
            ],
            ];


            $controls['chapterItemsSep'] = [
                
                'group' => 'styleControls',
                      'label'    => esc_html__( 'Chaper Menu Items', 'bricks' ),
                      'type'        => 'separator',
                  ];
          
          
          
              $controls['chapterLabel'] = [
                
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
          
              $controls['chapterStartTime'] = [
                
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
          
              $controls['chapterDuration'] = [
                
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

              $controls['chapterProgressColor'] = [
                'tab'    => 'content',
                'type'   => 'color',
                
                'group' => 'styleControls',
                'label'  => esc_html__( 'Chapter progress bar color', 'extras' ),
                'css'    => [
                  [
                    'property' => '--media-chapters-progress-bg',
                    'selector' => ''
                  ],
                ],
              ];
          
              $controls['chapterProgressHeight'] = [
                'tab'    => 'content',
                'type'   => 'number',
                'units' => true,
                'group' => 'styleControls',
                'placeholder' => '3px',
                'label'  => esc_html__( 'Chapter progress height', 'extras' ),
                
                'css'    => [
                  [
                    'property' => '--media-chapters-progress-height',
                    'selector' => ''
                  ],
                ],
              ];
          
          
              $controls['chapterItemsHocusSep'] = [
                
                'group' => 'styleControls',
                      'label'    => esc_html__( 'Chapter Menu Items (hover/focus)', 'bricks' ),
                      'type'        => 'separator',
                  ];
          
             
             $controls['chapterItemsHocusBackground'] = [
                'tab'    => 'content',
                'type'   => 'color',
                'group' => 'styleControls',
                'label'  => esc_html__( 'Background', 'extras' ),
                'css'    => [
                  [
                    'property' => 'background-color',
                    'selector' => '.xmp-chapter-radio:hover',
                  ],
                  [
                    'property' => 'background-color',
                    'selector' => '.xmp-chapter-radio:focus',
                  ],
                ],
                ]; 
          
              $controls['chapterLabelHocus'] = [
                
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
          
              $controls['chapterStartTimeHocus'] = [
                
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
          
              $controls['chapterDurationHocus'] = [
                
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
          
          
              $controls['chapterItemsSelectedSep'] = [
                
                'group' => 'styleControls',
                      'label'    => esc_html__( 'Chapter Menu Items (selected)', 'bricks' ),
                      'type'        => 'separator',
                  ];
          
          
                  $controls['chapterItemsSelectedBackground'] = [
                    'tab'    => 'content',
                    'type'   => 'color',
                    'group' => 'styleControls',
                    'label'  => esc_html__( 'Background', 'extras' ),
                    'css'    => [
                      [
                        'property' => 'background-color',
                        'selector' => '.xmp-chapter-radio[aria-checked="true"]',
                      ],
                    ],
                    ]; 
          
              $controls['chapterLabelSelected'] = [
                
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
          
              $controls['chapterStartTimeSelected'] = [
                
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
          
              $controls['chapterDurationSelected'] = [
                
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
        
        
        
        /* tooltips */

        $tooltipContent = '.tippy-content';
        
        $controls['maybeToolTips'] = [
            'group' => 'tooltips',
            'label'    => esc_html__( 'Control Tooltips', 'bricks' ),
            'type'        => 'select',
            'options'     => [
                'enable' => esc_html__( 'Enable', 'bricks' ),
                'disable'  => esc_html__( 'Disable', 'bricks' ),
            ],
            'placeholder' => esc_html__( 'Disable', 'bricks' ),
            'inline'      => true,
            'small' => true,
        ];
        
        
        $controls['tooltipBg'] = [
            'tab'    => 'content',
            'type'   => 'color',
            'group' => 'tooltips',
            'label'  => esc_html__( 'Background', 'extras' ),
            'css'    => [
            [
                'property' => 'background-color',
                'selector' => $tooltipContent,
            ],
            ],
            'required' => ['maybeToolTips','=','enable']
        ];
        
        $controls['tooltipBorder'] = [
            'tab'    => 'content',
            'type'   => 'border',
            'group' => 'tooltips',
            'label'  => esc_html__( 'Border', 'extras' ),
            'css'    => [
            [
                'property' => 'border',
                'selector' => $tooltipContent,
            ],
            ],
            'required' => ['maybeToolTips','=','enable']
        ];
        
        
        $controls['tooltipTypography'] = [
            'tab'    => 'content',
            'type'   => 'typography',
            'group' => 'tooltips',
            'label'  => esc_html__( 'Typography', 'extras' ),
            'css'    => [
            [
                'property' => 'font',
                'selector' => $tooltipContent,
            ],
            ],
            'required' => ['maybeToolTips','=','enable']
        ];
        
        $controls['tooltipPadding'] = [
            'tab'    => 'content',
            'type'   => 'dimensions',
            'group' => 'tooltips',
            'label'  => esc_html__( 'Padding', 'extras' ),
            'css'    => [
            [
                'property' => 'padding',
                'selector' => $tooltipContent,
            ],
            ],
            'required' => ['maybeToolTips','=','enable']
        ];
        
        $controls['tooltipXOffset'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units'   => false,
            'small' => true,
            'group' => 'tooltips',
            'label'  => esc_html__( 'X Offset', 'extras' ),
            'required' => ['maybeToolTips','=','enable'],
            'placeholder' => '0',
        ];
        
        $controls['tooltipYOffset'] = [
            'tab'    => 'content',
            'type'   => 'number',
            'units'   => false,
            'small' => true,
            'group' => 'tooltips',
            'label'  => esc_html__( 'Y Offset', 'extras' ),
            'placeholder' => '20',
            'required' => ['maybeToolTips','=','enable']
        ];
        
        $controls['defaultTooltipPlacement'] = [
            'tab'    => 'content',
            'group' => 'tooltips',
            'label'  => esc_html__( 'Default Tooltip Placement', 'extras' ),
            'type'      => 'select',
            'inline'  => true,
            'options' => $placementOptions,
            'required' => ['layoutType','!=','custom'],
            'placeholder' => esc_html__('Top', 'bricks' ), 
            'required' => ['maybeToolTips','=','enable']
        ];
        
        
        
        /* textTracks */
        
        
        $controls['textTracksSep'] = [
            'group' => 'textTracks',
            'label'    => esc_html__( 'Subtitles / Captions / Chapters', 'bricks' ),
            'descriptions'    => esc_html__( 'Here you can add subtitles/captions to the media player.', 'bricks' ),
            'type'        => 'separator',
        ];
        
        $controls['textTracks'] = [
            'tab'           => 'content',
            'type'          => 'repeater',
            'placeholder'   => esc_html__( 'Text Track', 'bricks' ),
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
                'placeholder' => 'Subtitles'
            ],
            'language' => [
                'type'    => 'text',
                'label'   => esc_html__( 'Language (en, fr, etc.)', 'bricks' ),
                'inline' => true,
                'placeholder' => 'en-US'
            ],
            ],
        ];
        
        
        
        $controls['thumbnailSep'] = [
            'group' => 'textTracks',
            'label'    => esc_html__( 'Thumbnails', 'bricks' ),
            'type'        => 'separator',
        ];
        
        $controls['thumbnailFile'] = [
            'group'			=> 'textTracks',
            'type'    => 'text',
            'label'   => esc_html__( 'Src (.vtt)', 'bricks' ),
            'inline' => true
        ];
        
        
        
        
        
        /* chapters */
        
        $controls['hasLoop'] = [
            'tab'   => 'content',
            'label' => esc_html__( 'Add chapters with query loop', 'bricks' ),
            'type'  => 'checkbox',
            'group' => 'chapters',
        ];
        
        $controls['query'] = [
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
        
        $controls['chapters'] = [
            'tab'         => 'content',
            'placeholder' => esc_html__( 'Chapter', 'bricks' ),
            'type'        => 'repeater',
            'rerender' => false,
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
        
        
        $controls['chapterText'] = [
            'tab'   => 'content',
            'label' => esc_html__( 'Text', 'bricks' ),
            'type'  => 'text',
            'group' => 'chapters',
            'rerender' => false,
            'required' => [
            ['hasLoop', '=', true]
            ]
        ];
        
        $controls['chapterStart'] = [
            'tab'   => 'content',
            'label' => esc_html__( 'Start time', 'bricks' ),
            'type'  => 'text',
            'group' => 'chapters',
            'rerender' => false,
            'inline' => true,
            'required' => [
            ['hasLoop', '=', true]
            ]
        ];
        
        $controls['chapterEnd'] = [
            'tab'   => 'content',
            'label' => esc_html__( 'End time', 'bricks' ),
            'type'  => 'text',
            'group' => 'chapters',
            'rerender' => false,
            'inline' => true,
            'required' => [
            ['hasLoop', '=', true]
            ]
        ];
        
        $controls['chaptersSep'] = [
            'tab'   => 'content',
            'type'  => 'seperator',
            'group' => 'chapters',
        ];
        
        // Keyboard Shortcuts Group
        $controls['keyboardShortcutsHeading'] = [
            'tab'   => 'content',
            'label' => esc_html__( 'Space-separated list of keys', 'bricks' ),
            'type'  => 'info',
            'group' => 'keyboardShortcuts',
            
        ];
        
        $controls['togglePausedShortcut'] = [
            'tab'   => 'content',
            'label' => esc_html__( 'Play/Pause', 'bricks' ),
            'type'  => 'text',
            'group' => 'keyboardShortcuts',
            'placeholder' => 'k Space',
            'inline' => true,
            'hasDynamicData' => false,
        ];
        
        $controls['toggleMutedShortcut'] = [
            'tab'   => 'content',
            'label' => esc_html__( 'Toggle Mute', 'bricks' ),
            'type'  => 'text',
            'group' => 'keyboardShortcuts',
            'placeholder' => 'm',
            'inline' => true,
            'hasDynamicData' => false,
        ];
        
        $controls['toggleFullscreenShortcut'] = [
            'tab'   => 'content',
            'label' => esc_html__( 'Toggle Fullscreen', 'bricks' ),
            'type'  => 'text',
            'group' => 'keyboardShortcuts',
            'placeholder' => 'f',
            'inline' => true,
            'hasDynamicData' => false,
        ];
        
        $controls['togglePictureInPictureShortcut'] = [
            'tab'   => 'content',
            'label' => esc_html__( 'Toggle Picture-in-Picture', 'bricks' ),
            'type'  => 'text',
            'group' => 'keyboardShortcuts',
            'placeholder' => 'i',
            'inline' => true,
            'hasDynamicData' => false,
        ];
        
        $controls['toggleCaptionsShortcut'] = [
            'tab'   => 'content',
            'label' => esc_html__( 'Toggle Captions', 'bricks' ),
            'type'  => 'text',
            'group' => 'keyboardShortcuts',
            'placeholder' => 'c',
            'inline' => true,
            'hasDynamicData' => false,
        ];
        
        $controls['seekBackwardShortcut'] = [
            'tab'   => 'content',
            'label' => esc_html__( 'Seek Backward', 'bricks' ),
            'type'  => 'text',
            'group' => 'keyboardShortcuts',
            'placeholder' => 'j ArrowLeft',
            'inline' => true,
            'hasDynamicData' => false,
        ];
        
        $controls['seekForwardShortcut'] = [
            'tab'   => 'content',
            'label' => esc_html__( 'Seek Forward', 'bricks' ),
            'type'  => 'text',
            'group' => 'keyboardShortcuts',
            'placeholder' => 'l ArrowRight',
            'inline' => true,
            'hasDynamicData' => false,
        ];
        
        $controls['volumeUpShortcut'] = [
            'tab'   => 'content',
            'label' => esc_html__( 'Volume Up', 'bricks' ),
            'type'  => 'text',
            'group' => 'keyboardShortcuts',
            'placeholder' => 'ArrowUp',
            'inline' => true,
            'hasDynamicData' => false,
        ];
        
        $controls['volumeDownShortcut'] = [
            'tab'   => 'content',
            'label' => esc_html__( 'Volume Down', 'bricks' ),
            'type'  => 'text',
            'group' => 'keyboardShortcuts',
            'placeholder' => 'ArrowDown',
            'inline' => true,
            'hasDynamicData' => false,
        ];
        
        $controls['speedUpShortcut'] = [
            'tab'   => 'content',
            'label' => esc_html__( 'Speed Up', 'bricks' ),
            'type'  => 'text',
            'group' => 'keyboardShortcuts',
            'placeholder' => '>',
            'inline' => true,
            'hasDynamicData' => false,
        ];
        
        $controls['slowDownShortcut'] = [
            'tab'   => 'content',
            'label' => esc_html__( 'Slow Down', 'bricks' ),
            'type'  => 'text',
            'group' => 'keyboardShortcuts',
            'placeholder' => '<',
            'inline' => true,
            'hasDynamicData' => false,
        ];
        
        
        
        /* behaviour */
        
        
        $controls['loop'] = [
            'group' => 'behaviour',
            'label'    => esc_html__( 'Loop', 'bricks' ),
            'type'        => 'checkbox',
            'inline'      => true,
            'small' => true,
            'required' => ['playlistMode', '!=', true],
        ];
        
        $controls['muted'] = [
            'group' => 'behaviour',
            'label'    => esc_html__( 'Muted', 'bricks' ),
            'type'        => 'checkbox',
            'inline'      => true,
            'small' => true,
        ];
        
        $controls['autoplay'] = [
            'group' => 'behaviour',
            'label'    => esc_html__( 'Autoplay', 'bricks' ),
            'type'        => 'checkbox',
            'inline'      => true,
            'small' => true,
            'required' => ['muted', '=', true],
            'rerender' => false,
        ];

        if ($mediaType === 'video') {

            $controls['playReset'] = [
                'group' => 'behaviour',
                'label'    => esc_html__( 'Reset on play (when muted autoplay)', 'bricks' ),
                'type'        => 'checkbox',
                'info' => esc_html__( 'Plays video from start when user clicks play', 'bricks' ),
                'required' => [
                ['muted', '=', true],
                ['autoplay', '=', true],
                ],
                'rerender' => false, 
            ];

        }
        
        
        $controls['pauseOutOfView'] = [
            'group' => 'behaviour',
            'label'    => esc_html__( 'Pause if scroll out of view', 'bricks' ),
            'type'        => 'checkbox',
            'inline'      => true,
            'small' => true,
            'rerender' => false,
        ];
        
        $controls['rememberPlayerSettings'] = [
            'group' => 'behaviour',
            'label'    => esc_html__( 'Remember player settings', 'bricks' ),
            'info'    => esc_html__( 'User settings stored in browser storage', 'bricks' ),
            'type'        => 'checkbox',
            'inline'      => true,
            'small' => true,
            'rerender' => false,
        ];
        
        
        $controls['multipleSources'] = [
            'group' => 'behaviour',
            'label'    => esc_html__( 'Allow multiple source URLs', 'bricks' ),
            'type'        => 'checkbox',
            'inline'      => true,
            'small' => true,
            'rerender' => false,
            'required' => 
              ['sourceType', '!=', 'media'], 
          ];

          $controls['crossorigin'] = [
            'group' => 'behaviour',
            'label'    => esc_html__( 'Crossorigin', 'bricks' ),
            'type'        => 'select',
            'options'     => [
                'anonymous' => esc_html__( 'Anonymous', 'bricks' ),
                'use-credentials'  => esc_html__( 'Use credentials', 'bricks' ),
                'null' => esc_html__( 'Null', 'bricks' ),
            ],
            'placeholder' => esc_html__( 'Anonymous', 'bricks' ),
            'inline'      => true,
            'small' => true,
            'rerender' => false,
          ];

          $controls['autoPause'] = [
            'group' => 'behaviour',
            'label'    => esc_html__( 'Auto pause', 'bricks' ),
            'info' => esc_html__( 'Pause other players when playback starts', 'bricks' ),
            'type'        => 'select',
            'options'     => [
            'enable' => esc_html__( 'Enable', 'bricks' ),
            'disable'  => esc_html__( 'Disable', 'bricks' ),
            ],
            'placeholder' => esc_html__( 'Enable', 'bricks' ),
            'inline'      => true,
            'small' => true,
        ];
        
        $controls['providerControls'] = [
            'group' => 'behaviour',
            'label'    => esc_html__( 'Provider controls', 'bricks' ),
            'info' => esc_html__( 'Replaces custom controls with provider controls, no clip start/end support', 'bricks' ),
            'type'        => 'select',
            'options'     => [
            'enable' => esc_html__( 'Enable', 'bricks' ),
            'disable'  => esc_html__( 'Disable', 'bricks' ),
            ],
            'placeholder' => esc_html__( 'Disable', 'bricks' ),
            'inline'      => true,
            'small' => true,
        ];

        $controls['startURLParam'] = [
            'group' => 'behaviour',
            'label'    => esc_html__( 'Start Time URL parameter', 'bricks' ),
            'info' => esc_html__( 'If URL includes ?key=xxx where xxx is the start time in seconds', 'bricks' ),
            'type'        => 'checkbox',
            'inline'      => true,
            'small' => true,
            'inline'      => true,
            'small' => true,
        ];

        $controls['startURLParamKey'] = [
            'group' => 'behaviour',
            'label'    => esc_html__( 'Parameter key', 'bricks' ),
            'type'        => 'text',
            'inline'      => true,
            'required' => ['startURLParam', '=', true],
        ];

        $controls['portalSettings'] = [
            'group' => 'behaviour',
            'label'    => esc_html__( 'Portal setting menus to footer', 'bricks' ),
            'type'        => 'checkbox',
            'inline'      => true,
            'required' => ['layoutType', '!=', ['custom']]
        ];
        
        
        
        
        
            /* playlist */
        
            $controls['playListSep'] = [
            'tab' => 'content',
            'label' => esc_html__( 'Playlists', 'bricks' ),
            'description' => esc_html__( 'Get media source dynamically from active playlist element', 'bricks' ),
            'type' => 'separator',
            'group' => 'playLists',
            ];
        
            $controls['playlistMode'] = [
            'tab' => 'content',
            'label' => esc_html__( 'Playlist mode', 'bricks' ),
            'inline'  => true,
            'type' => 'checkbox',
            'group' => 'playLists',
            ];
        
            $controls['whichPlaylist'] = [
            'tab' => 'content',
            'label' => esc_html__( 'Which playlist elements?', 'bricks' ),
            'group' => 'playLists',
            'type' => 'select',
            'options' => [
                'section' => esc_html__( 'All inside this section', 'bricks' ),
                'selector' => esc_html__( 'All inside a particular selector', 'bricks' ),
                'component' => esc_html__( 'All inside this component', 'bricks' ),
            ],
            'clearable' => false,
            'placeholder' => esc_html__( 'Inside this section', 'bricks' ),
            'required' => ['playlistMode', '=', true],
            ];
        
            $controls['playlistSelector'] = [
            'tab' => 'content',
            'label' => esc_html__( 'Selector', 'bricks' ),
            'group' => 'playLists',
            'type' => 'text',
            'required' => [
                ['whichPlaylist', '=', 'selector'],
                ['playlistMode', '=', true]
            ]
            ];

            $controls['componentScope'] = [
                'tab' => 'content',
                'label' => esc_html__( 'Component scope', 'bricks' ),
                'group' => 'playLists',
                'type' => 'select',
                'options' => [
                    'true' => esc_html__( 'True', 'bricks' ),
                    'false' => esc_html__( 'False', 'bricks' ),
                ],
                'placeholder' => esc_html__( 'False', 'bricks' ),
                'inline' => true,
                'required' => [
                    ['whichPlaylist', '=', 'selector'],
                    ['playlistMode', '=', true]
                    ]
                ];
        
            $controls['playBehaviourSep'] = [
                'tab' => 'content',
                'label' => esc_html__( 'Playlist Behaviour', 'bricks' ),
                'type' => 'separator',
                'group' => 'playLists',
                'required' => ['playlistMode', '=', true],
            ];
            
            $controls['playListNext'] = [
                'tab' => 'content',
                'label' => esc_html__( 'Move to next in playlist when reaches end', 'bricks' ),
                'inline'  => true,
                'type'        => 'checkbox',
                'group' => 'playLists',
                'required' => ['playlistMode', '=', true],
            ];
        
        
            
            $controls['playListLoop'] = [
                'tab' => 'content',
                'label' => esc_html__( 'Loop back to first in playlist when reaches end', 'bricks' ),
                'inline'  => true,
                'type'        => 'checkbox',
                'group' => 'playLists',
                'required' => ['playlistMode', '=', true]
            ];
        
        
            /* poster image */
        
            $controls['image'] = [
            'tab' => 'content',
            'label' => esc_html__( 'Poster Image', 'bricks' ),
            'type' => 'image',
            'group' => 'poster'
            ];
        
            $controls['altText'] = [
            'tab'      => 'content',
            'label'    => esc_html__( 'Custom alt text', 'bricks' ),
            'type'     => 'text',
            'inline'   => true,
            'rerender' => false,
            'group' => 'poster',
            'required' => [ 'image', '!=', '' ],
        ];
        
            $controls['objectFit'] = [
            'tab'         => 'content',
            'label'       => esc_html__( 'Object fit', 'bricks' ),
            'group' => 'poster',
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
                'selector' => '.xmp-poster img',
                ],
            ],
            'inline'      => true,
            'placeholder' => esc_html__( 'Contain', 'bricks' ),
            ];
        
            $controls['imgLoading'] = [
            'tab'      => 'content',
            'label'    => esc_html__( 'Loading', 'bricks' ),
            'type'        => 'select',
            'options'     => [
                'eager'       => esc_html__( 'Eager', 'bricks' ),
                'lazy'    => esc_html__( 'Lazy', 'bricks' ),
            ],
            'placeholder' => esc_html__( 'Lazy', 'bricks' ),
            'inline'   => true,
            'rerender' => false,
            'group' => 'poster',
            
        ];
        
        return $controls;
        
        
    }

    /**
     * Process chapter data for repeater items
     * 
     * @param string $chapterText Chapter text from settings
     * @param string $chapterStart Chapter start time from settings
     * @param string $chapterEnd Chapter end time from settings
     * @return array Processed chapter data
     */
    public function chapter_repeater_item( $chapterText, $chapterStart, $chapterEnd ) {
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

    /**
     * Process chapters from settings
     * 
     * @param array $settings Element settings
     * @return array Processed chapters array
     */
    protected function process_chapters( $settings ) {
            $chaptersArray = [];
            $chapters = isset( $settings['chapters'] ) ? $settings['chapters'] : false;

            // Query Loop
            if ( isset( $settings['hasLoop'] ) ) {
                $query = new \Bricks\Query( [
                    'id'       => $this->id,
                    'settings' => $settings,
                ] );
                
                $chapterText = isset( $settings['chapterText'] ) ? $settings['chapterText'] : '';
                $chapterStart = isset( $settings['chapterStart'] ) ? $settings['chapterStart'] : '';
                $chapterEnd = isset( $settings['chapterEnd'] ) ? $settings['chapterEnd'] : '';

                $chaptersArray = $query->render( [ $this, 'chapter_repeater_item' ], [ $chapterText, $chapterStart, $chapterEnd ], true );

                $query->destroy();
                unset( $query );
            } else {
                if ( !!$chapters ) {
                    foreach ( $settings['chapters'] as $index => $chapter ) {
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
            
            return $chaptersArray;
    }

    /**
     * Set up common configuration for media players
     * 
     * This method handles the common configuration setup for both video and audio players.
     * 
     * @param array $settings The element settings
     * @param string $layoutType The layout type
     * @param string $loadingType The loading type
     * @param bool $clickToPlay Whether to enable click to play
     * @param bool $autoLocalPoster Whether to enable auto local poster
     * @param string $whichPlaylist Which playlist type to use
     * @param string $playlistSelector The playlist selector
     * @param string $posterURL The poster URL
     * @param array $thumbnails Thumbnails configuration
     * @param bool $rememberPlayerSettings Whether to remember player settings
     * @return array The prepared configuration array
     */
    protected function setup_common_config($settings, $layoutType, $loadingType, $clickToPlay, $autoLocalPoster, $whichPlaylist, $playlistSelector, $posterURL, $thumbnails, $rememberPlayerSettings) {
        // Basic configuration
        $config = [
            'load' => $loadingType,
            'layoutType' => $layoutType,
            'clickToPlay' => $clickToPlay,
        ];

        if ( \BricksExtras\Helpers::get_parent_component_id($this->element) ) {
          $config += ['parentComponent' => \BricksExtras\Helpers::get_parent_component_id($this->element)];
        }

        if ( isset($settings['pauseOutOfView']) ) {
          $config += ['pauseOutOfView' => $settings['pauseOutOfView']];
        }

        if ( $autoLocalPoster ) {
          $config += ['autoLocalPoster' => $autoLocalPoster];
        }
        
        // Handle looping
        if (\BricksExtras\Helpers::get_parent_loop_id($this->element)) {
            $config += ['isLooping' => \BricksExtras\Helpers::get_parent_loop_id($this->element)];
        }
        
        if (\BricksExtras\Helpers::get_parent_loop_id($this->element, true)) {
            $config += ['isLoopingComponent' => \BricksExtras\Helpers::get_parent_loop_id($this->element, true)];
        }
        
        // Handle playlist mode
        if (isset($settings['playlistMode'])) {
            $config += [
                'playlist' => isset($settings['playlistMode']),
                'playListLoop' => isset($settings['playListLoop']),
                'playlistPlayOnClick' => isset($settings['playlistPlayOnClick']),
                'playListNext' => isset($settings['playListNext']),
                'scope' => $whichPlaylist,
            ];
        }
        
        // Handle remember player settings
        if ($rememberPlayerSettings) {
            $config += [
                'storage' => true,
            ];
        }
        
        // Handle playlist selector
        if ('selector' === $whichPlaylist) {
 
            $componentScope = isset($settings['componentScope']) ? $settings['componentScope'] : 'false';

            $config += [
                'scopeSelector' => $playlistSelector,
            ];

            if ('false' !== $componentScope) {
                $config += [
                    'componentScope' => $componentScope,
                ];
            }
        }
        
        // Handle poster URL
        if ($posterURL) {
            $config += ['poster' => $posterURL];
        }
        
        // Add thumbnails
        if ($thumbnails) {
          $config += ['thumbnails' => $thumbnails];
        }

        // autopause
        $autoPause = isset( $settings['autoPause'] ) ? 'enable' === $settings['autoPause'] : true;
        if ( $autoPause ) {
            $config += ['autoPause' => $autoPause];
        }

        // Tooltip config
        $maybeToolTips = isset( $settings['maybeToolTips'] ) ? 'enable' === $settings['maybeToolTips'] : false;

        if ( $maybeToolTips ) {
            $tooltipXOffset = isset( $settings['tooltipXOffset'] ) ? $settings['tooltipXOffset'] : '0';
            $tooltipYOffset = isset( $settings['tooltipYOffset'] ) ? $settings['tooltipYOffset'] : '20px';
            $config += ['tooltipXOffset' => $tooltipXOffset];
            $config += ['tooltipYOffset' => $tooltipYOffset];
        }

        /* URL parameter */
        $startURLParam = isset($settings['startURLParam']);
        if ($startURLParam) {
            $startURLParamKey = isset($settings['startURLParamKey']) ? esc_attr( $settings['startURLParamKey'] ) : 't';
            $config += ['startURLParamKey' => $startURLParamKey];
        }

        /* Keyboard Shortcuts */
        $keyboard = [];
        
        if (isset($settings['togglePausedShortcut']) && !empty($settings['togglePausedShortcut'])) {
            $keyboard['togglePaused'] = esc_attr($settings['togglePausedShortcut']);
        }
        
        if (isset($settings['toggleMutedShortcut']) && !empty($settings['toggleMutedShortcut'])) {
            $keyboard['toggleMuted'] = esc_attr($settings['toggleMutedShortcut']);
        }
        
        if (isset($settings['toggleFullscreenShortcut']) && !empty($settings['toggleFullscreenShortcut'])) {
            $keyboard['toggleFullscreen'] = esc_attr($settings['toggleFullscreenShortcut']);
        }
        
        if (isset($settings['togglePictureInPictureShortcut']) && !empty($settings['togglePictureInPictureShortcut'])) {
            $keyboard['togglePictureInPicture'] = esc_attr($settings['togglePictureInPictureShortcut']);
        }
        
        if (isset($settings['toggleCaptionsShortcut']) && !empty($settings['toggleCaptionsShortcut'])) {
            $keyboard['toggleCaptions'] = esc_attr($settings['toggleCaptionsShortcut']);
        }
        
        if (isset($settings['seekBackwardShortcut']) && !empty($settings['seekBackwardShortcut'])) {
            $keyboard['seekBackward'] = esc_attr($settings['seekBackwardShortcut']);
        }
        
        if (isset($settings['seekForwardShortcut']) && !empty($settings['seekForwardShortcut'])) {
            $keyboard['seekForward'] = esc_attr($settings['seekForwardShortcut']);
        }
        
        if (isset($settings['volumeUpShortcut']) && !empty($settings['volumeUpShortcut'])) {
            $keyboard['volumeUp'] = esc_attr($settings['volumeUpShortcut']);
        }
        
        if (isset($settings['volumeDownShortcut']) && !empty($settings['volumeDownShortcut'])) {
            $keyboard['volumeDown'] = esc_attr($settings['volumeDownShortcut']);
        }

        if (isset($settings['speedUpShortcut']) && !empty($settings['speedUpShortcut'])) {
            $keyboard['speedUp'] = esc_attr($settings['speedUpShortcut']);
        }

        if (isset($settings['slowDownShortcut']) && !empty($settings['slowDownShortcut'])) {
            $keyboard['slowDown'] = esc_attr($settings['slowDownShortcut']);
        }
        
        // Only add keyboard to config if we have at least one shortcut defined
        if (!empty($keyboard)) {
            $config['keyboard'] = $keyboard;
        }

        
        return $config;
    }

    /**
     * Set chapters attribute if valid chapters exist
     * 
     * @param array $chaptersArray Processed chapters array
     * @return void
     */
    protected function set_chapters_attribute( $chaptersArray ) {
            /* ignore if less than 2 chapters */
            if( 2 <= count( $chaptersArray ) ) {
                $this->set_attribute( '_root', 'data-x-chapters', json_encode($chaptersArray) );
            }
    }

    /**
     * Enqueue scripts and styles for the media player
     * 
     * This method is shared between both video and audio media player components.
     */
    public function enqueue_scripts() {

        if ( bricks_is_builder_main() ) {
            return;
          }

            $lazyVidstack = true;

            $lazyVidstack = apply_filters( 'bricksextras/lazyvidstack', $lazyVidstack );

            if ( \BricksExtras\Helpers::maybePreview() || !$lazyVidstack ) {


                if ( !\BricksExtras\Helpers::is_vidstack_script_enqueued() ) {
                    wp_enqueue_script( 'x-media-player',  BRICKSEXTRAS_URL . 'components/assets/vidstack/dist/assets/vidstack.js', '', '1.8.1', true );

                    wp_localize_script(
                        'x-media-player',
                        'xMediaPlayer', 
                        [
                            'Instances' => [],
                            'TextTracks' => [],
                            'vidstackDir' 	=> 'wp-content/plugins/' . basename( BRICKSEXTRAS_URL ) . '/components/assets/vidstack/dist/',
                            'pluginDir' => BRICKSEXTRAS_URL . 'components/assets/',
                            'ajaxurl' => admin_url( 'admin-ajax.php' ),
                            'sec' => wp_create_nonce('media-nonce'),
                            'captions' => wp_create_nonce('wp_rest'),
                            'isLoggedIn' => (int)is_user_logged_in()
                        ]
                    );
                }
                wp_enqueue_script( 'x-media-player-init',  BRICKSEXTRAS_URL . 'components/assets/js/mediaplayer.min.js', '', \BricksExtras\Plugin::VERSION, true );
                wp_enqueue_script( 'x-popper',  BRICKSEXTRAS_URL . 'components/assets/js/popper.min.js', '', \BricksExtras\Plugin::VERSION, true );
                wp_enqueue_script( 'x-media-player-tooltips',  BRICKSEXTRAS_URL . 'components/assets/js/mediaplayertooltips.min.js', ['x-popper'], \BricksExtras\Plugin::VERSION, true );
            } else {

                wp_enqueue_script( 'x-media-player-init',  BRICKSEXTRAS_URL . 'components/assets/js/mediaplayerinit.min.js', '', \BricksExtras\Plugin::VERSION, true );

                if (!self::$script_localized) {
                    wp_localize_script(
                        'x-media-player-init',
                        'xMediaPlayer', 
                        [
                            'Instances' => [],
                            'TextTracks' => [],
                            'vidstackDir' 	=> 'wp-content/plugins/' . basename( BRICKSEXTRAS_URL ) . '/components/assets/vidstack/dist/',
                            'pluginDir' => BRICKSEXTRAS_URL . 'components/assets/',
                            'ajaxurl' => admin_url( 'admin-ajax.php' ),
                            'sec' => wp_create_nonce('media-nonce'),
                            'captions' => wp_create_nonce('wp_rest'),
                            'version' => \BricksExtras\Plugin::VERSION,
                            'isLoggedIn' => (int)is_user_logged_in()  
                        ]
                    ); 

                    self::$script_localized = true;
                }
            }

            if (! \BricksExtras\Helpers::elementCSSAdded($this->name) ) {
                wp_enqueue_style( 'x-media-player', BRICKSEXTRAS_URL . 'components/assets/css/mediaplayer.css', [], \BricksExtras\Plugin::VERSION );
            }

            add_filter('script_loader_tag', array( $this, 'vidstack_module') , 10, 3);
    }

    /**
     * Add type="module" to the vidstack script tag
     * 
     * @param string $tag    The script tag.
     * @param string $handle The script handle.
     * @param string $src    The script source.
     * @return string Modified script tag.
     */
    public function vidstack_module($tag, $handle, $src) {
            if ( 'x-media-player' !== $handle ) {
                return $tag;
            }
            
            $tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
            return $tag;
        }
   

    

}    