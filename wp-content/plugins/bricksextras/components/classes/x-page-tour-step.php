<?php
/**
 * Page Tour Step Element
 *
 * @package BricksExtras
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class X_Page_Tour_Step extends \Bricks\Element {
  public $category     = 'extras';
  public $name         = 'xpagetourstep';
  public $icon         = 'ti-direction';
  public $css_selector = '';
  public $scripts      = ['xPageTourPopover'];
  public $nestable     = true;

  public function get_label() {
    return esc_html__( 'Page Tour Step', 'bricks' );
  }

  public function set_control_groups() {

    $this->control_groups['steps'] = [
      'title' => esc_html__( 'Popover Styling', 'extras' ),
      'tab' => 'content',
    ];

  }

  public function set_controls() {


    // Step Settings
    $this->controls['stepTitle'] = [
      'tab'         => 'content',
      'label'       => esc_html__( 'Step Title', 'bricks' ),
      'type'        => 'text',
      'placeholder' => esc_html__( '', 'bricks' ),
      'inline'      => true,
    ];

    

    // Target Element
    $this->controls['stepSelector'] = [
      'tab'         => 'content',
      'label'       => esc_html__( 'Target Element Selector', 'bricks' ),
      'type'        => 'text',
      'info'        => esc_html__( 'Leave blank for step centered in viewport', 'bricks' ),
      'placeholder' => '.your-class',
      'inline'      => true,
    ];

    
    /*
    // Highlight Element
    $this->controls['highlightElement'] = [
      'tab'         => 'content',
      'label'       => esc_html__( 'Highlight Target Element', 'bricks' ),
      'type'        => 'select',
      'options'     => [
        'true'       => esc_html__( 'True', 'bricks' ),
        'false'      => esc_html__( 'False', 'bricks' ),
      ],
      'inline'      => true,
      'placeholder' => esc_html__( 'inherit', 'bricks' ),
    ];

    // Scroll To Element
    $this->controls['scrollTo'] = [
      'tab'         => 'content',
      'label'       => esc_html__( 'Scroll To Element', 'bricks' ),
      'type'        => 'checkbox',
    ];

    */

    // Position
    $this->controls['stepPosition'] = [
        'tab'         => 'content',
        'label'       => esc_html__( 'Preferred Placement', 'bricks' ),
        'inline'      => true,
        'type'        => 'select',
        'options'     => [
          'auto'       => esc_html__( 'Auto', 'bricks' ),
          'top'        => esc_html__( 'Top', 'bricks' ),
          'bottom'     => esc_html__( 'Bottom', 'bricks' ),
          'left'       => esc_html__( 'Left', 'bricks' ),
          'right'      => esc_html__( 'Right', 'bricks' ),
          'top-start'  => esc_html__( 'Top Start', 'bricks' ),
          'top-end'    => esc_html__( 'Top End', 'bricks' ),
          'bottom-start' => esc_html__( 'Bottom Start', 'bricks' ),
          'bottom-end' => esc_html__( 'Bottom End', 'bricks' ),
          'left-start' => esc_html__( 'Left Start', 'bricks' ),
          'left-end'   => esc_html__( 'Left End', 'bricks' ),
          'right-start' => esc_html__( 'Right Start', 'bricks' ),
          'right-end'  => esc_html__( 'Right End', 'bricks' ),
        ],
        'placeholder'  => esc_html__( 'Auto', 'bricks' ),
      ];

      

    // Button Text - Previous
    $this->controls['buttonTextSep'] = [
     'label'       => esc_html__( 'Navigation Button Text', 'bricks' ),
     //'group'       => 'buttons',
      'tab'         => 'content',
      'type'        => 'separator',
      'description' => esc_html__( 'Will override the global settings for this step', 'bricks' ),
    ];

    // Button Text - Previous
    $this->controls['buttonPrevText'] = [
      'tab'         => 'content',
      'label'       => esc_html__( 'Previous Button', 'bricks' ),
      'inline'      => true,
      //'group'       => 'buttons',
      'type'        => 'text',
      'placeholder' => esc_html__( '', 'bricks' ),
    ];

    // Button Text - Next
    $this->controls['buttonNextText'] = [
      'tab'         => 'content',
      'label'       => esc_html__( 'Next Button', 'bricks' ),
      'inline'      => true,
      //'group'       => 'buttons',
      'type'        => 'text',
      'placeholder' => esc_html__( '', 'bricks' ),
    ];

    // Button Text - Close
    $this->controls['buttonCloseText'] = [
      'tab'         => 'content',
      'label'       => esc_html__( 'Finish Button', 'bricks' ),
      'inline'      => true,
      //'group'       => 'buttons',
      'type'        => 'text',
      'placeholder' => esc_html__( '', 'bricks' ),
    ];


    /* steps */

    $shepardContent = '&.extras-theme.shepherd-element .shepherd-content.shepherd-content';
    
    $this->controls['stepContentBackground'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Background color', 'bricks' ),
      'type'     => 'color',
      'group'    => 'steps',
      'css'      => [
        [
          'property' => '--x-tour-background',
          'selector' => '.shepherd-content',
        ],
        [
          'property' => '--x-tour-background',
          'selector' => '.shepherd-arrow',
        ],
      ],
    ];

    $this->controls['stepContentTypography'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Typography', 'bricks' ),
      'type'     => 'typography',
      'group'    => 'steps',
      'css'      => [
        [
          'property' => 'font',
          'selector' => $shepardContent,
        ],
      ],
    ];

    $this->controls['stepTitleTypography'] = [
        'tab'      => 'content',
        'label'    => esc_html__( 'Step Title Typography', 'bricks' ),
        'type'     => 'typography',
        'group'    => 'steps',
        'css'      => [
          [
            'property' => 'font',
            'selector' => '.shepherd-title',
          ],
        ],
      ];

    $this->controls['stepContentPadding'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Padding', 'bricks' ),
      'type'     => 'dimensions',
      'group'    => 'steps',
      'css'      => [
        [
          'property' => 'padding',
          'selector' => $shepardContent,
        ],
      ],
    ];

    $this->controls['stepContentBorder'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Border', 'bricks' ),
      'type'     => 'border',
      'group'    => 'steps',
      'css'      => [
        [
          'property' => 'border',
          'selector' => $shepardContent,
        ],
      ],
    ];

    $this->controls['stepContentBoxShadow'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Box Shadow', 'bricks' ),
      'type'     => 'box-shadow',
      'group'    => 'steps',
      'css'      => [
        [
          'property' => 'box-shadow',
          'selector' => $shepardContent,
        ],
      ],
    ];


    /* close button */

    $closeButtonSelector = "&.shepherd-element .shepherd-cancel-icon";

    $this->controls['closeButtonSep'] = [
      'tab'   => 'content',
      'label' => esc_html__( 'Close Button', 'bricks' ),
      'type'  => 'separator',
      'group'    => 'steps',
    ];

    $this->controls['closeButtonBackground'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Background', 'bricks' ),
      'type'     => 'color',
      'group'    => 'steps',
      'css'      => [
        [
          'property' => 'background-color',
          'selector' => $closeButtonSelector,
        ],
      ],
    ];

    $this->controls['closeButtonColor'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Color', 'bricks' ),
      'type'     => 'color',
      'group'    => 'steps',
      'css'      => [
        [
          'property' => 'color',
          'selector' => $closeButtonSelector,
        ],
      ],
    ];

    $this->controls['closeButtonSize'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Size', 'bricks' ),
      'type'     => 'number',
      'units'    => true,
      'group'    => 'steps',
      'css'      => [
          [
            'property' => 'height',
            'selector' => '&.extras-theme.shepherd-element.shepherd-element .shepherd-cancel-icon:after',
          ],
          [
            'property' => 'height',
            'selector' => '&.extras-theme.shepherd-element.shepherd-element .shepherd-cancel-icon:before',
          ],
        ],
    ];

    $this->controls['closeButtonBorder'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Border', 'bricks' ),
      'type'     => 'border',
      'group'    => 'steps',
      'css'      => [
        [
          'property' => 'border',
          'selector' => $closeButtonSelector,
        ],
      ],
    ];

    $this->controls['closeButtonBoxShadow'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Box Shadow', 'bricks' ),
      'type'     => 'box-shadow',
      'group'    => 'steps',
      'css'      => [
        [
          'property' => 'box-shadow',
          'selector' => $closeButtonSelector,
        ],
      ],
    ];

    $this->controls['closeButtonPadding'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Padding', 'bricks' ),
      'type'     => 'dimensions',
      'group'    => 'steps',
      'css'      => [
        [
          'property' => 'padding',
          'selector' => $closeButtonSelector,
        ],
      ],
    ];

    $this->controls['closeButtonMargin'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Margin', 'bricks' ),
      'type'     => 'dimensions',
      'group'    => 'steps',
      'css'      => [
        [
          'property' => 'margin',
          'selector' => $closeButtonSelector,
        ],
      ],
    ];

  }

  public function get_nestable_item() {
		return [
			'name'     => 'text-basic',
			'label'    => esc_html__( 'Text', 'bricks' ),
			'children' => [
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
    $title = !empty($settings['stepTitle']) ? bricks_render_dynamic_data( $settings['stepTitle'] ) : '';
    
    // Add data attributes for step configuration
    if (!empty($title)) {
      $this->set_attribute('_root', 'data-step-title', esc_attr($title));
    }
    
    if (!empty($settings['stepSelector'])) {
      $this->set_attribute('_root', 'data-step-selector', esc_attr($settings['stepSelector']));
    }
    
    if (!empty($settings['stepPosition'])) {
      $this->set_attribute('_root', 'data-step-position', esc_attr($settings['stepPosition']));
    }

    $highlightElement = !empty($settings['highlightElement']) ? esc_attr($settings['highlightElement']) : 'inherit';
    
    if ('inherit' !== $highlightElement) {
      $this->set_attribute('_root', 'data-step-highlight', esc_attr($highlightElement));
    }
    
    if (!empty($settings['scrollTo'])) {
      $this->set_attribute('_root', 'data-step-scroll', 'true');
    }
    
    // Add skip if target not found attribute
    if (!empty($settings['skipIfNoTarget'])) {
      $this->set_attribute('_root', 'data-skip-if-no-target', 'true');
    }
    
    // Advanced options as JSON
    $advancedOptions = [];
    
    if (!empty($settings['buttonPrevText'])) {
      $advancedOptions['buttonPrevText'] = $settings['buttonPrevText'];
    }
    
    if (!empty($settings['buttonNextText'])) {
      $advancedOptions['buttonNextText'] = $settings['buttonNextText'];
    }
    
    if (!empty($settings['buttonCloseText'])) {
      $advancedOptions['buttonCloseText'] = $settings['buttonCloseText'];
    }
    
    if (isset($settings['showButtons'])) {
      $advancedOptions['showButtons'] = !empty($settings['showButtons']);
    }

   
    
    if (!empty($advancedOptions)) {
      $this->set_attribute('_root', 'data-step-advanced', esc_attr(wp_json_encode($advancedOptions)));
    }
    
    // Render element with template tag
    echo "<template {$this->render_attributes('_root')}>";
    
    // Render children elements
    echo \Bricks\Frontend::render_children($this);
    
    echo "</template>";
  }


  public static function render_builder() { ?>

    <script type="text/x-template" id="tmpl-bricks-element-xpagetourstep">

        <component
          class="x-page-tour-step shepherd-element extras-theme"
            v-show="!settings.builderHidden" 
        >

        <div aria-describedby="step-0-description" role="dialog" tabindex="0" class="shepherd-has-cancel-icon shepherd-element extras-theme shepherd-enabled" data-popper-placement="bottom">
          
        <div class="shepherd-arrow" data-popper-arrow="" style="position: absolute; left: 0px; transform: translate3d(192px, 0px, 0px);"></div>

        <div class="shepherd-content">
            <header class="shepherd-header">
            <contenteditable
                tag="h3"
                class="shepherd-title"
                :name="name"
                controlKey="stepTitle"
                toolbar="style"
                :settings="settings"
          />
                <button aria-label="Close Tour" class="shepherd-cancel-icon" type="button"><span aria-hidden="true">×</span></button>
            </header> 
            <div class="shepherd-text" id="step-0-description">
                <bricks-element-children :element="element" />
            </div> 
            <footer class="shepherd-footer">
                <button class="x-page-tour-button x-page-tour-button__back shepherd-button" tabindex="0">
                    {{ settings.buttonPrevText ? settings.buttonPrevText : 'Back' }}
                </button>
                <button class="x-page-tour-button x-page-tour-button__next shepherd-button" tabindex="0">
                    {{ settings.buttonNextText ? settings.buttonNextText : 'Next' }}
                </button>
                <button class="x-page-tour-button x-page-tour-button__complete shepherd-button" tabindex="0">
                    {{ settings.buttonCloseText ? settings.buttonCloseText : 'Finish' }}
                </button>
            </footer>
            <div class="shepherd-progress-container">
              <div class="shepherd-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="20"></div>
            </div>
        </div>

        </div>
            

        </component>

    </script>

<?php }

}
