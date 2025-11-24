<?php
/**
 * Page Tour Element
 *
 * @package BricksExtras
 */

class X_Page_Tour extends \Bricks\Element {
  // Element properties
  public $category     = 'extras';
  public $name         = 'xpagetour';
  public $icon         = 'ti-direction-alt';
  public $css_selector = '';
  public $scripts      = ['xPageTour'];
  public $nestable = true;
  private static $script_localized = false;

  public function get_label() {
    return esc_html__( 'Page Tour', 'bricksextras' );
  }

  public function set_control_groups() {

    

    $this->control_groups['steps'] = [
      'title' => esc_html__( 'Step Popover Styling', 'extras' ),
      'tab' => 'content',
    ];

    $this->control_groups['navButtons'] = [
      'title' => esc_html__( 'Navigation Buttons', 'extras' ),
      'tab' => 'content',
    ];

    $this->control_groups['progress'] = [
      'title' => esc_html__( 'Progress Bar', 'extras' ),
      'tab' => 'content',
    ];

    $this->control_groups['overlay'] = [
      'title' => esc_html__( 'Overlay', 'extras' ),
      'tab' => 'content',
    ];

    $this->control_groups['closeButton'] = [
      'title' => esc_html__( 'Close Button', 'extras' ),
      'tab' => 'content',
    ];

    $this->control_groups['config'] = [
      'title' => esc_html__( 'Config', 'extras' ),
      'tab' => 'content',
    ];

  }

  public function set_controls() {

    $this->controls['builderHidden'] = [
      'tab'   => 'content',
      'inline' => true,
      'small' => true,
      'label' => esc_html__( 'Hide preview in builder', 'bricks' ),
      'type'  => 'checkbox',
    ];
  
    $this->controls['builderHidden_sep'] = [
      'tab'   => 'content',
      'type'  => 'separator',
    ];

    $this->controls['trigger'] = [
      'tab'   => 'content',
      'label' => esc_html__( 'Trigger', 'bricks' ),
      'inline' => true,
      'type'  => 'select',
      'options' => [
        'page_load' => esc_html__( 'Page Load', 'bricks' ),
        'click' => esc_html__( 'Click', 'bricks' ),
        'interaction' => esc_html__( 'Interaction', 'bricks' ),
      ],
      'placeholder' => esc_html__( 'Page Load', 'bricks' ),
    ];

    $this->controls['clickSelector'] = [
      'tab'   => 'content',
      'label' => esc_html__( 'Click Selector', 'bricks' ),
      'inline' => true,
      'type'  => 'text',
      'required' => ['trigger', '=', 'click'],
    ];

    $this->controls['show_again'] = [
      'tab'   => 'content',
      'label' => esc_html__( 'Show again', 'bricks' ),
      'type'  => 'select',
      'options' => [
        'page_load' => esc_html__( 'Show on every visit', 'bricks' ),
        'never' => esc_html__( 'Never show again', 'bricks' ),
        'until_complete' => esc_html__( 'Show again until completed', 'bricks' ),
        'after' => esc_html__( 'Only allow to show again after:', 'bricks' ),
        'manual' => esc_html__( 'Manual (if using with code)', 'bricks' ),
      ],
      'placeholder' => esc_html__( 'Show on every visit', 'bricks' ),
      'required' => ['trigger', '!=', 'click'],
      
    ];

    $this->controls['show_again_info'] = [
      'tab' => 'content',
      'description' => esc_html__( 'See the docs for details on showing tours in your code', 'bricks' ),
      'type' => 'info',
      'required' => ['show_again', '=', 'manual'],
    ];
    
    
  
    $this->controls['show_again_days'] = [
      'tab' => 'content',
      'label' => esc_html__( 'Days', 'bricks' ),
      'type' => 'number',
      'inline' => true,
      'required' => ['show_again', '=', 'after'],
    ];
  
    $this->controls['show_again_hours'] = [
      'tab' => 'content',
      'label' => esc_html__( 'Hours', 'bricks' ),
      'type' => 'number',
      'inline' => true,
      'required' => ['show_again', '=', 'after'],
    ];

    

    // Button Style

    $this->controls['buttonBackground'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Background', 'bricks' ),
      'type'     => 'color',
      'group'    => 'buttons',
      'css'      => [
        [
          'property' => 'background-color',
          'selector' => '.x-page-tour-button',
        ],
      ],
      'required' => ['autoStart', '=', ''],
    ];

    $this->controls['buttonTypography'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Typography', 'bricks' ),
      'type'     => 'typography',
      'group'    => 'buttons',
      'css'      => [
        [
          'property' => 'font',
          'selector' => '.x-page-tour-button',
        ],
      ],
      'required' => ['autoStart', '=', ''],
    ];

    $this->controls['buttonBorder'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Border', 'bricks' ),
      'type'     => 'border',
      'group'    => 'buttons',
      'css'      => [
        [
          'property' => 'border',
          'selector' => '.x-page-tour-button',
        ],
      ],
      'required' => ['autoStart', '=', ''],
    ];

    $this->controls['buttonPadding'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Padding', 'bricks' ),
      'type'     => 'dimensions',
      'group'    => 'buttons',
      'css'      => [
        [
          'property' => 'padding',
          'selector' => '.x-page-tour-button',
        ],
      ],
      'required' => ['autoStart', '=', ''],
    ];

    /* steps */

    $shepardContent = '.extras-theme.shepherd-element .shepherd-content';
    
    $this->controls['stepContentBackground'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Background color', 'bricks' ),
      'type'     => 'color',
      'group'    => 'steps',
      'css'      => [
        [
          'property' => '--x-tour-background',
          'selector' => '.extras-theme.shepherd-element',
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
          'selector' => '.extras-theme.shepherd-element',
        ],
      ],
    ];

    /* overlay */

    /* close button */

    $closeButtonSelector = ".shepherd-cancel-icon";

    $this->controls['disableCloseButton'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Disable Close Button', 'bricks' ),
      'type'     => 'checkbox',
      'group'    => 'closeButton',
    ];

    $this->controls['closeButtonBackground'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Background', 'bricks' ),
      'type'     => 'color',
      'group'    => 'closeButton',
      'css'      => [
        [
          'property' => 'background-color',
          'selector' => $closeButtonSelector,
        ],
      ],
      'required' => ['disableCloseButton', '!=', true],
    ];

    $this->controls['closeButtonColor'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Color', 'bricks' ),
      'type'     => 'color',
      'group'    => 'closeButton',
      'css'      => [
        [
          'property' => 'color',
          'selector' => $closeButtonSelector,
        ],
      ],
      'required' => ['disableCloseButton', '!=', true],
    ];

    $this->controls['closeButtonSize'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Size', 'bricks' ),
      'type'     => 'number',
      'units'    => true,
      'group'    => 'closeButton',
    'placeholder'  => '17px',
      'css'      => [
        [
          'property' => 'height',
          'selector' => '.extras-theme.shepherd-element .shepherd-cancel-icon:after',
        ],
        [
          'property' => 'height',
          'selector' => '.extras-theme.shepherd-element .shepherd-cancel-icon:before',
        ],
      ],
      'required' => ['disableCloseButton', '!=', true],
    ];

    $this->controls['closeButtonBorder'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Border', 'bricks' ),
      'type'     => 'border',
      'group'    => 'closeButton',
      'css'      => [
        [
          'property' => 'border',
          'selector' => $closeButtonSelector,
        ],
      ],
      'required' => ['disableCloseButton', '!=', true],
    ];

    $this->controls['closeButtonBoxShadow'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Box Shadow', 'bricks' ),
      'type'     => 'box-shadow',
      'group'    => 'closeButton',
      'css'      => [
        [
          'property' => 'box-shadow',
          'selector' => $closeButtonSelector,
        ],
      ],
      'required' => ['disableCloseButton', '!=', true],
    ];

    $this->controls['closeButtonPadding'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Padding', 'bricks' ),
      'type'     => 'dimensions',
      'group'    => 'closeButton',
      'css'      => [
        [
          'property' => 'padding',
          'selector' => $closeButtonSelector,
        ],
      ],
      'required' => ['disableCloseButton', '!=', true],
    ];

    $this->controls['closeButtonMargin'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Margin', 'bricks' ),
      'type'     => 'dimensions',
      'group'    => 'closeButton',
      'css'      => [
        [
          'property' => 'margin',
          'selector' => $closeButtonSelector,
        ],
      ],
      'required' => ['disableCloseButton', '!=', true],
    ];
      

   /* nav buttons */

   $this->controls['navButtons'] = [
    'tab'         => 'content',
    'label'       => esc_html__( 'Navigation Buttons', 'bricks' ),
    'description' => esc_html__( 'Disable if adding your own buttons/links inside each step', 'bricks' ),
    'inline'      => true,
    'group'       => 'navButtons',
    'type'        => 'select',
    'options'     => [
      'enable'       => esc_html__( 'Enable', 'bricks' ),
      'disable'      => esc_html__( 'Disable', 'bricks' ),
    ],
    'placeholder' => esc_html__( 'Enable', 'bricks' ),
  ];

  $this->controls['previewNavButtons'] = [
    'tab'         => 'content',
    'label'       => esc_html__( 'Preview all buttons in builder', 'bricks' ),
    'inline'      => true,
    'group'       => 'navButtons',
    'type'        => 'checkbox',
    'required'    => ['navButtons', '!=', 'disable'],
  ];

  

  $this->controls['navButtons_sep'] = [
    'tab'   => 'content',
    'type'  => 'separator',
    'group'       => 'navButtons',
    'label' => esc_html__( 'Next Button', 'bricks' ),
    'required'    => ['navButtons', '!=', 'disable'],
  ];

  $this->controls['customButtons_sep'] = [
    'tab'   => 'content',
    'type'  => 'separator',
    'group' => 'navButtons',
    'required'    => ['navButtons', '=', 'disable'],
    'label' => esc_html__( 'Custom Button Selectors', 'bricks' ),
    'description' => esc_html__( 'This allows you to create custom navigation using your own buttons.', 'bricks' ),
  ];

  
 

  $this->controls['customBackSelector'] = [
    'tab'   => 'content',
    'label' => esc_html__( 'Back Button Selector', 'bricks' ),
    'type'  => 'text',
    'required'    => ['navButtons', '=', 'disable'],
    'placeholder' => '.x-tour-back',
    'group' => 'navButtons',
  ];

  $this->controls['customNextSelector'] = [
    'tab'   => 'content',
    'label' => esc_html__( 'Next Button Selector', 'bricks' ),
    'type'  => 'text',
    'required'    => ['navButtons', '=', 'disable'],
    'placeholder' => '.x-tour-next',
    'group' => 'navButtons',
  ];

  $this->controls['customCompleteSelector'] = [
    'tab'   => 'content',
    'label' => esc_html__( 'Complete Button Selector', 'bricks' ),
    'type'  => 'text',
    'required'    => ['navButtons', '=', 'disable'],
    'placeholder' => '.x-tour-complete',
    'group' => 'navButtons',
  ];

  $this->controls['customSkipSelector'] = [
    'tab'   => 'content',
    'label' => esc_html__( 'Skip Button Selector', 'bricks' ),
    'type'  => 'text',
    'required'    => ['navButtons', '=', 'disable'],
    'placeholder' => '.x-tour-skip',
    'group' => 'navButtons',
  ];

  $this->controls['autoHideCustomButtons'] = [
    'tab'         => 'content',
    'label'       => esc_html__( 'Auto hide custom buttons', 'bricks' ),
    'inline'      => true,
    'group'       => 'navButtons',
    'type'        => 'checkbox',
    'required'    => ['navButtons', '=', 'disable'],
    'description' => esc_html__( 'This option will only show each button/link as it is needed. (will hide the complete button until the last step, for example)', 'bricks' ),
  ];


  $nextButton = '.x-page-tour-button__next';

  // Button Text - Next
  $this->controls['buttonNextText'] = [
    'tab'         => 'content',
    'label'       => esc_html__( 'Button Text', 'bricks' ),
    'inline'      => true,
    //'group'       => 'buttons',
    'type'        => 'text',
    'placeholder' => esc_html__( 'Next', 'bricks' ),
    'group' => 'navButtons',
    'required'    => ['navButtons', '!=', 'disable'],
    'info' => esc_html__( 'Can also be changed per step', 'bricks' ),
  ];

  

  $this->controls['nextButtonBackground'] = [
    'tab'         => 'content',
    'label'       => esc_html__( 'Background color', 'bricks' ),
    'inline'      => true,
    'group'       => 'navButtons',
    'type'        => 'color',
    'css'      => [
      [
        'property' => 'background-color',
        'selector' => $nextButton,
      ],
    ],
    'required'    => ['navButtons', '!=', 'disable'],
  ];

  $this->controls['nextButtonTypography'] = [
    'tab'         => 'content',
    'label'       => esc_html__( 'Typography', 'bricks' ),
    'inline'      => true,
    'group'       => 'navButtons',
    'type'        => 'typography',
    'css'      => [
      [
        'property' => 'font',
        'selector' => $nextButton,
      ],
    ],
    'required'    => ['navButtons', '!=', 'disable'],
    ];

  $this->controls['nextButtonBorder'] = [
    'tab'         => 'content',
    'label'       => esc_html__( 'Border', 'bricks' ),
    'inline'      => true,
    'group'       => 'navButtons',
    'type'        => 'border',
    'css'      => [
      [
        'property' => 'border',
        'selector' => $nextButton,
      ],
    ],
    'required'    => ['navButtons', '!=', 'disable'],
  ];

  $this->controls['nextButtonBoxShadow'] = [
    'tab'         => 'content',
    'label'       => esc_html__( 'Box Shadow', 'bricks' ),
    'inline'      => true,
    'group'       => 'navButtons',
    'type'        => 'box-shadow',
    'css'      => [
      [
        'property' => 'box-shadow',
        'selector' => $nextButton,
      ],
    ],
    'required'    => ['navButtons', '!=', 'disable'],
  ];

  $this->controls['nextButtonPadding'] = [
    'tab'         => 'content',
    'label'       => esc_html__( 'Padding', 'bricks' ),
    'group'       => 'navButtons',
    'type'        => 'dimensions',
    'css'      => [
      [
        'property' => 'padding',
        'selector' => $nextButton,
      ],
    ],
    'required'    => ['navButtons', '!=', 'disable'],
  ];

  $this->controls['backButtonSep'] = [
    'tab'   => 'content',
    'type'  => 'separator',
    'group'       => 'navButtons',
    'label' => esc_html__( 'Back Button', 'bricks' ),
    'required'    => ['navButtons', '!=', 'disable'],
  ];

 
   // Button Text - Previous
   $this->controls['buttonPrevText'] = [
     'tab'         => 'content',
     'label'       => esc_html__( 'Button Text', 'bricks' ),
     'inline'      => true,
     //'group'       => 'buttons',
     'type'        => 'text',
     'placeholder' => esc_html__( 'Back', 'bricks' ),
     'group' => 'navButtons',
    'required'    => ['navButtons', '!=', 'disable'],
    'info' => esc_html__( 'Can also be changed per step', 'bricks' ),
   ];

   

  $backButton = '.x-page-tour-button__back';

  $this->controls['backButtonBackground'] = [
    'tab'         => 'content',
    'label'       => esc_html__( 'Background color', 'bricks' ),
    'inline'      => true,
    'group'       => 'navButtons',
    'type'        => 'color',
    'css'      => [
      [
        'property' => 'background-color',
        'selector' => $backButton,
      ],
    ],
    'required'    => ['navButtons', '!=', 'disable'],
  ];

  $this->controls['backButtonTypography'] = [ 
    'tab'         => 'content',
    'label'       => esc_html__( 'Typography', 'bricks' ),
    'inline'      => true,
    'group'       => 'navButtons',
    'type'        => 'typography',
    'css'      => [
      [
        'property' => 'font',
        'selector' => $backButton,
      ],
    ],
    'required'    => ['navButtons', '!=', 'disable'],
  ];

  $this->controls['backButtonBorder'] = [
    'tab'         => 'content',
    'label'       => esc_html__( 'Border', 'bricks' ),
    'inline'      => true,
    'group'       => 'navButtons',
    'type'        => 'border',
    'css'      => [
      [
        'property' => 'border',
        'selector' => $backButton,
      ],
    ],
    'required'    => ['navButtons', '!=', 'disable'],
    ];

  $this->controls['backButtonBoxShadow'] = [
    'tab'         => 'content',
    'label'       => esc_html__( 'Box Shadow', 'bricks' ),
    'inline'      => true,
    'group'       => 'navButtons',
    'type'        => 'box-shadow',
    'css'      => [
      [
        'property' => 'box-shadow',
        'selector' => $backButton,
      ],
    ],
    'required'    => ['navButtons', '!=', 'disable'],
    ];

  $this->controls['backButtonPadding'] = [
    'tab'         => 'content',
    'label'       => esc_html__( 'Padding', 'bricks' ),
    'group'       => 'navButtons',
    'type'        => 'dimensions',
    'css'      => [
      [
        'property' => 'padding',
        'selector' => $backButton,
      ],
    ],
    'required'    => ['navButtons', '!=', 'disable'],
    ];

  $this->controls['completeButtonSep'] = [ 
    'tab'   => 'content',
    'type'  => 'separator',
    'group'       => 'navButtons',
    'label' => esc_html__( 'Complete Button', 'bricks' ),
    'required'    => ['navButtons', '!=', 'disable'],
  ];

  // Button Text - Close
  $this->controls['buttonCloseText'] = [
    'tab'         => 'content',
    'label'       => esc_html__( 'Button Text', 'bricks' ),
    'inline'      => true,
    //'group'       => 'buttons',
    'type'        => 'text',
    'placeholder' => esc_html__( 'Finish', 'bricks' ),
    'group' => 'navButtons',
    'required'    => ['navButtons', '!=', 'disable'],
    'info' => esc_html__( 'Can also be changed per step', 'bricks' ),
  ];

  $completeButton = '.x-page-tour-button__complete';

  $this->controls['completeButtonBackground'] = [
    'tab'         => 'content',
    'label'       => esc_html__( 'Background color', 'bricks' ),
    'inline'      => true,
    'group'       => 'navButtons',
    'type'        => 'color',
    'css'      => [
      [
        'property' => 'background-color',
        'selector' => $completeButton,
      ],
    ],
    'required'    => ['navButtons', '!=', 'disable'],
  ];

  $this->controls['completeButtonTypography'] = [ 
    'tab'         => 'content',
    'label'       => esc_html__( 'Typography', 'bricks' ),
    'inline'      => true,
    'group'       => 'navButtons',
    'type'        => 'typography',
    'css'      => [
      [
        'property' => 'font',
        'selector' => $completeButton,
      ],
    ],
    'required'    => ['navButtons', '!=', 'disable'],
    ];

  $this->controls['completeButtonBorder'] = [
    'tab'         => 'content',
    'label'       => esc_html__( 'Border', 'bricks' ),
    'inline'      => true,
    'group'       => 'navButtons',
    'type'        => 'border',
    'css'      => [
      [
        'property' => 'border',
        'selector' => $completeButton,
      ],
    ],
    'required'    => ['navButtons', '!=', 'disable'],
  ];

  $this->controls['completeButtonBoxShadow'] = [
    'tab'         => 'content',
    'label'       => esc_html__( 'Box Shadow', 'bricks' ),
    'inline'      => true,
    'group'       => 'navButtons',
    'type'        => 'box-shadow',
    'css'      => [
      [
        'property' => 'box-shadow',
        'selector' => $completeButton,
      ],
    ],
    'required'    => ['navButtons', '!=', 'disable'],
      ];

  $this->controls['completeButtonPadding'] = [
    'tab'         => 'content',
    'label'       => esc_html__( 'Padding', 'bricks' ),
    'group'       => 'navButtons',
    'type'        => 'dimensions',
    'css'      => [
      [
        'property' => 'padding',
        'selector' => $completeButton,
      ],
    ],
    'required'    => ['navButtons', '!=', 'disable'],
  ];




   /* progress */

  $this->controls['showProgress'] = [
    'tab'   => 'content',
    'group' => 'progress',
    'label' => esc_html__( 'Enable Progress Bar', 'bricks' ),
    'type'  => 'checkbox',
  ];

  $this->controls['progressPosition'] = [
    'tab'   => 'content',
    'group' => 'progress',
    'label' => esc_html__( 'Progress Bar Position', 'bricks' ),
    'type'  => 'select',
    'inline' => true,
    'options' => [
      'top' => esc_html__( 'Top', 'bricks' ),
      'bottom' => esc_html__( 'Bottom', 'bricks' ),
    ],
    'placeholder' => esc_html__( 'Bottom', 'bricks' ),
    'required' => ['showProgress', '=', true],
  ];
    

  $this->controls['progressHeight'] = [
    'tab'   => 'content',
    'group' => 'progress',
    'label' => esc_html__( 'Progress Bar Height', 'bricks' ),
    'type'  => 'number',
    'units' => true,
    'placeholder' => '6px',
    'css' => [
      [
        'property' => 'height',
        'selector' => '.shepherd-progress-container',
      ],
    ],
    'required' => ['showProgress', '=', true],
  ];

  $this->controls['progressColor'] = [
    'tab'   => 'content',
    'group' => 'progress',
    'label' => esc_html__( 'Progress Fill Color', 'bricks' ),
    'type'  => 'color',
    'css' => [
      [
        'property' => 'background-color',
        'selector' => '.shepherd-progress-bar',
      ],
    ],
    'required' => ['showProgress', '=', true],
  ];

  

  $this->controls['progressBackground'] = [
    'tab'   => 'content',
    'group' => 'progress',
    'label' => esc_html__( 'Progress Bar Background', 'bricks' ),
    'type'  => 'color',
    'css' => [
      [
        'property' => 'background-color',
        'selector' => '.shepherd-progress-container',
      ],
    ],
    'required' => ['showProgress', '=', true],
  ];

  
    /* overlay */

    $this->controls['useModalOverlay'] = [
      'tab'   => 'content',
      'group' => 'overlay',
      'label' => esc_html__( 'Enable Overlay', 'bricks' ),
      'type'  => 'checkbox',
    ];

    $this->controls['overlayColor'] = [
      'tab'   => 'content',
      'group' => 'overlay',
      'label' => esc_html__( 'Overlay color', 'bricks' ),
      'type'  => 'color',
      'css' => [
        [
          'property' => 'fill',
          'selector' => '.shepherd-modal-overlay-container',
        ],
      ],
      'required' => ['useModalOverlay', '=', true],
    ];

    $this->controls['exitOnOverlayClick'] = [
      'tab'   => 'content',
      'group' => 'overlay',
      'label' => esc_html__( 'Click overlay to exit tour', 'bricks' ),
      'type'  => 'checkbox',
      'required' => ['useModalOverlay', '=', true],
    ];

    $this->controls['clickTargetSep'] = [
      'tab'   => 'content',
      'group' => 'overlay',
      'label' => esc_html__( 'Allow clicking target element', 'bricks' ),
      'type'  => 'separator',
      'required' => ['useModalOverlay', '=', true],
    ];

    $this->controls['canClickTarget'] = [
      'tab'   => 'content',
      'label' => esc_html__( 'Allowing Clicking Target element', 'bricks' ),
      'description' => esc_html__( 'Allow clicking the target element through a hole in the overlay', 'bricks' ),
      'type'  => 'checkbox',
      'group' => 'overlay',
      'required' => ['useModalOverlay', '=', true],
    ];
    
    $this->controls['modalOverlayOpeningPadding'] = [
      'tab'   => 'content',
      'label' => esc_html__( 'Opening hole padding (px)', 'bricks' ),
      'type'  => 'number',
      'group' => 'overlay',
      'small' => true,
      'required' => [
        ['useModalOverlay', '=', true],
        ['canClickTarget', '=', true],
      ],
    ];

    $this->controls['modalOverlayOpeningRadius'] = [
      'tab'   => 'content',
      'label' => esc_html__( 'Opening hole radius (px)', 'bricks' ),
      'type'  => 'number',
      'group' => 'overlay',
      'small' => true,
      'required' => [
        ['useModalOverlay', '=', true],
        ['canClickTarget', '=', true],
      ],
    ];

    
    
    $this->controls['keyboardNavigation'] = [
      'tab'   => 'content',
      'label' => esc_html__( 'Keyboard Navigation', 'bricks' ),
      'type'  => 'checkbox',
      'group' => 'config',
      'description' => esc_html__( 'Enable arrow keys for navigation', 'bricks' ),
    ];
    
    $this->controls['exitOnEsc'] = [
      'tab'   => 'content',
      'label' => esc_html__( 'Exit on ESC key', 'bricks' ),
      'type'  => 'checkbox',
      'group' => 'config',
    ];
    
    $this->controls['confirmCancel'] = [
      'tab'   => 'content',
      'label' => esc_html__( 'Confirm Cancel', 'bricks' ),
      'type'  => 'checkbox',
      'group' => 'config',
      'description' => esc_html__( 'Show confirmation dialog if canceling tour', 'bricks' ),
    ];

    
    
    $this->controls['confirmCancelMessage'] = [
      'tab'   => 'content',
      'label' => esc_html__( 'Confirm Cancel Message', 'bricks' ),
      'type'  => 'text',
      'placeholder' => esc_html__( 'Are you sure you want to exit the tour?', 'bricks' ),
      'required' => ['confirmCancel', '=', true],
      'group' => 'config',
    ];

    

    $this->controls['defaultHighlight'] = [
      'tab'   => 'content',
      'label' => esc_html__( 'Highlight Target Elements', 'bricks' ),
      'type'  => 'checkbox',
      'group' => 'config',
    ];
    
    $this->controls['defaultScrollTo'] = [
      'tab'   => 'content',
      'label' => esc_html__( 'Scroll To Target Elements', 'bricks' ),
      'type'  => 'checkbox',
      'group' => 'config',
    ];

    // When target not found
    $this->controls['targetNotFound'] = [
      'tab' => 'content',
      'label' => esc_html__( 'When target elements not found:', 'bricks' ),
      'type' => 'select',
      'options' => [
        'show' => esc_html__( 'Show step in center of viewport', 'bricks' ),
        'skip' => esc_html__( 'Skip that step', 'bricks' ),
      ],
      'group' => 'config',
      'placeholder' => esc_html__( 'Show step in center of viewport', 'bricks' ),
    ];

    $this->controls['componentScope'] = [
      'tab' => 'content',
      'label' => esc_html__( 'Component scope', 'bricks' ),
      'type' => 'select',
      'options' => [
        'true' => esc_html__( 'True', 'bricks' ),
        'false' => esc_html__( 'False', 'bricks' ),
      ],
      'inline'      => true,
      'placeholder' => esc_html__( 'False', 'bricks' ),
      'group' => 'config',
  ];

    $this->controls['rememberSep'] = [
      'tab'   => 'content',
      'group' => 'config',
      'type'  => 'separator',
    ];

    
    
    $this->controls['rememberProgress'] = [
      'tab'   => 'content',
      'label' => esc_html__( 'Remember User Progress', 'bricks' ),
      'type'  => 'checkbox',
      'group' => 'config',
      'description' => esc_html__( 'Will resume tour when user returns (will override the show again settings until the user completes or cancels)', 'bricks' ),
    ];
    
    $this->controls['saveNextStep'] = [
      'tab'   => 'content',
      'label' => esc_html__( 'Step to return to', 'bricks' ),
      'type'  => 'select',
      'inline' => true,
      'options' => [
        'current' => esc_html__( 'Current Step', 'bricks' ),
        'next'    => esc_html__( 'Next Step', 'bricks' ),
      ],
      'description' => esc_html__( 'Next step is useful for multi-page tours', 'bricks' ),
      'required' => ['rememberProgress', '=', true],
      'group' => 'config',
      'placeholder' => esc_html__( 'Current Step', 'bricks' ),
    ];

    
      

    
  }

  // Methods: Frontend-specific
  public function enqueue_scripts() {
    if ( bricks_is_builder_main() ) {
      return;
    }

    // Enqueue Shepherd.js library
    wp_enqueue_script( 'shepherd', BRICKSEXTRAS_URL . 'components/assets/js/shepherd.min.js', [], '10.0.1', true );
    

    // Enqueue our custom script
    wp_enqueue_script( 'x-page-tour', BRICKSEXTRAS_URL . 'components/assets/js/' . \BricksExtras\Helpers::maybeMinifyScripts('page-tour') . '.js', ['shepherd'], \BricksExtras\Plugin::VERSION, true );

    if (! \BricksExtras\Helpers::elementCSSAdded($this->name) ) {
      wp_enqueue_style( 'x-page-tour', BRICKSEXTRAS_URL . 'components/assets/css/page-tour.css', [], \BricksExtras\Plugin::VERSION );
    }
  }

  public function get_nestable_item() {
		return [
			'name'     => 'xpagetourstep',
			'label'    => esc_html__( 'Page Tour Step', 'bricks' ),
			'children' => [
        [
					'name'     => 'text-basic',
					'settings' => [
						'text' => esc_html__( 'Step content..', 'bricks' ),
					],
				],
			],
		];
	}

	public function get_nestable_children() {
		$children = [];

		for ( $i = 0; $i < 2; $i++ ) {
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

    // Generate a unique identifier for this tour
    $identifier = \BricksExtras\Helpers::set_identifier_attribute( $this );

    $navButtons = isset($settings['navButtons']) ? 'enable' === $settings['navButtons'] : true;

    $trigger = isset($settings['trigger']) ? $settings['trigger'] : 'page_load';

    
    
    // Build tour configuration
    $tourConfig = [
      'trigger' => $trigger,
      'showProgress' => isset($settings['showProgress']),
      'progressPosition' => isset($settings['progressPosition']) ? $settings['progressPosition'] : 'bottom',
      'exitOnOverlayClick' => isset($settings['exitOnOverlayClick']),
      'useModalOverlay' => isset($settings['useModalOverlay']),
      'confirmCancel' => isset($settings['confirmCancel']),
      'keyboardNavigation' => isset($settings['keyboardNavigation']),
      'exitOnEsc' => isset($settings['exitOnEsc']),
      'confirmCancelMessage' => isset($settings['confirmCancelMessage']) ? $settings['confirmCancelMessage'] : esc_html__('Are you sure you want to exit the tour?', 'bricksextras'),
      'defaultHighlight' => isset($settings['defaultHighlight']),
      'defaultScrollTo' => isset($settings['defaultScrollTo']),
      'targetNotFound' => isset($settings['targetNotFound']) ? $settings['targetNotFound'] : 'show',
      'rememberProgress' => isset($settings['rememberProgress']),
      'saveNextStep' => isset($settings['saveNextStep']) ? $settings['saveNextStep'] : 'current',
      'showAgain' => isset($settings['show_again']) ? $settings['show_again'] : 'page_load',
      'showAgainDays' => isset($settings['show_again_days']) ? intval($settings['show_again_days']) : 0,
      'showAgainHours' => isset($settings['show_again_hours']) ? intval($settings['show_again_hours']) : 0,
      'disableCloseButton' => isset($settings['disableCloseButton']),
    ];

    if (isset($settings['canClickTarget'])) {
      $tourConfig['canClickTarget'] = true;
      $tourConfig['modalOverlayOpeningPadding'] = isset($settings['modalOverlayOpeningPadding']) ? intval($settings['modalOverlayOpeningPadding']) : 0;
      $tourConfig['modalOverlayOpeningRadius'] = isset($settings['modalOverlayOpeningRadius']) ? intval($settings['modalOverlayOpeningRadius']) : 0;
    }

    $componentScope = !empty($settings['componentScope']) ? esc_attr($settings['componentScope']) : 'false';

    if ($componentScope === 'true') {
      $tourConfig['component'] =  \BricksExtras\Helpers::is_component_instance( $this->element );
      $tourConfig['parentComponent'] = \BricksExtras\Helpers::get_parent_component_id( $this->element );
    }
    
    if ('click' === $trigger) {
      $tourConfig['clickSelector'] = isset($settings['clickSelector']) ? $settings['clickSelector'] : '';
    }

    if (!$navButtons) {
       $tourConfig['navButtons'] = false;
       $tourConfig['autoHideCustomButtons'] = isset($settings['autoHideCustomButtons']);
       $tourConfig['customNextSelector'] = isset($settings['customNextSelector']) ? $settings['customNextSelector'] : '.x-tour-next';
       $tourConfig['customBackSelector'] = isset($settings['customBackSelector']) ? $settings['customBackSelector'] : '.x-tour-back';
       $tourConfig['customCompleteSelector'] = isset($settings['customCompleteSelector']) ? $settings['customCompleteSelector'] : '.x-tour-complete';
       $tourConfig['customSkipSelector'] = isset($settings['customSkipSelector']) ? $settings['customSkipSelector'] : '.x-tour-skip';
    } else {
      $tourConfig['buttonPrevText'] = isset($settings['buttonPrevText']) ? $settings['buttonPrevText'] : 'Back';
      $tourConfig['buttonNextText'] = isset($settings['buttonNextText']) ? $settings['buttonNextText'] : 'Next';
      $tourConfig['buttonCloseText'] = isset($settings['buttonCloseText']) ? $settings['buttonCloseText'] : 'Finish';
    }
    
    // Set data attributes
    $this->set_attribute('_root', 'class', 'x-page-tour');
    $this->set_attribute('_root', 'data-x-page-tour', wp_json_encode($tourConfig));
    
    // Render element
    echo "<div {$this->render_attributes('_root')}>";

    echo \Bricks\Frontend::render_children( $this );
    
    echo "</div>";
  }


  public static function render_builder() { ?>

    <script type="text/x-template" id="tmpl-bricks-element-xpagetour">

        <component 
        v-show="!settings.builderHidden"
        :data-show-progress="settings.showProgress ? 'true' : 'false'"
        :data-progress-position="settings.progressPosition"
        :data-nav-buttons="!settings.navButtons || 'enable' === settings.navButtons ? 'true' : 'false'"
        :data-preview-nav-buttons="settings.previewNavButtons ? 'true' : 'false'"
        :data-disable-close-button="settings.disableCloseButton ? 'true' : 'false'"
        
        >
                <bricks-element-children 
                  :element="element" 
                />

                <svg 
                  v-if="settings.useModalOverlay"
                  class="shepherd-modal-is-visible shepherd-modal-overlay-container"
                  viewBox="0 0 100 100" preserveAspectRatio="none"
                ><rect x="0" y="0" width="100" height="100"></rect>
                  
                </svg>

        </component>

    </script>

<?php }

}
