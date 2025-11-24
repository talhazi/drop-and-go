<?php

namespace BricksExtras;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( class_exists( 'BricksExtrasQueryLoop' ) ) {
	return;
}

class BricksExtrasQueryLoop {

	static string $prefix = '';

	public function init( $prefix ) {

		self::$prefix    = $prefix;

		add_action( 'init', [ $this, 'add_queryLoopExtras_controls' ], 40 );
		add_filter( 'bricks/setup/control_options', [ $this, 'setup_queryLoopExtras_controls' ]);
		add_filter( 'bricks/query/run', [ $this, 'run_queryLoopExtras' ], 10, 2 );
		add_filter( 'bricks/query/loop_object', [ $this, 'extras_setup_post_data' ], 10, 3);

		// Add filter to display menu item IDs in WordPress admin
		if ( is_admin() ) {
			add_filter( 'wp_nav_menu_item_custom_fields', [ $this, 'display_menu_item_id' ], 10, 4 );
		}

	}


	function add_queryLoopExtras_controls() {

		$elements = [
			'container',
			'block',
			'div',
			'xdynamictable'
		];

		foreach ( $elements as $element ) {
			add_filter( "bricks/elements/{$element}/controls", [ $this, 'queryLoopExtras_controls' ], 40 );
		}


	}



	public function queryLoopExtras_controls( $controls ) {

		$taxonomies = \Bricks\Setup::$control_options['taxonomies'];
	 	 unset( $taxonomies['Wp_template_part'] );

		  $extrasQueryOptions = [
			'adjacent'  => esc_html__( 'Adjacent Posts' ),
			'gallery'	=> esc_html__( 'Gallery' ),
			'related'  => esc_html__( 'Related Posts' ),
			'wpmenu' => esc_html__( 'WP Menu' ),
		];

		if ( get_option( self::$prefix . 'favorite') ) {
			$extrasQueryOptions['favorite'] = 'Favorites';
		}

		$newControls['extrasQuery'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Query Type', 'bricks' ),
			'type'        => 'select',
			'inline'      => true,
			'options'     => $extrasQueryOptions,
			'placeholder' => esc_html__( 'Select...', 'bricks' ),
			'required'    => array(
				[ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'hasLoop', '!=', false ]
			),
			'rerender'    => true,
			'multiple'    => false,
		];


	  /* adjacentPost */

	  $newControls['adjacentPost'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Adjacent Post', 'bricks' ),
			'type'        => 'select',
			'inline'      => true,
			'options'     => [
				'prev'  => esc_html__( 'Previous', 'bricks' ),
				'next'  => esc_html__( 'Next', 'bricks' ),
			],
			'placeholder' => esc_html__( 'Previous', 'bricks' ),
			'required' => [
				[ 'extrasQuery', '=', 'adjacent'],
				[ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'hasLoop', '!=', false ]
			],
			'rerender'    => true,
			'multiple'    => false,
		];

		$newControls['adjacentPostSameTerm'] = [
			'tab'      => 'content',
			'label'    => esc_html__( 'Post should be in the same taxonomy term', 'bricks' ),
			'type'     => 'checkbox',
			'rerender'  => true,
			'required' => [
				[ 'extrasQuery', '=', 'adjacent'],
				[ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'hasLoop', '!=', false ]
			]
		  ];

		  $newControls['adjacentTaxonomy'] = [
			'tab'      => 'content',
			'label'    => esc_html__( 'Taxonomy', 'bricks' ),
			'type'        => 'select',
			'options'     => $taxonomies,
			'multiple'    => false,
			'description' => esc_html__( 'Taxonomy adjacent posts must have in common.', 'bricks' ),
			'placeholder' => [
				'category',
			],
			'required' => [
				[ 'extrasQuery', '=', 'adjacent'],
				[ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'hasLoop', '!=', false ],
				['adjacentPostSameTerm', '=', true]
			]
		  ];

		 $newControls['adjacentPostExcludedTerms'] = [
			'tab'      => 'content',
			'label'    => esc_html__( 'Excluded Term IDs', 'bricks' ),
			'type'     => 'text',
			'required' => [
				[ 'extrasQuery', '=', 'adjacent'],
				[ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'hasLoop', '!=', false ],
				['adjacentPostSameTerm', '=', true]
			]
		  ];

		  $newControls['adjacentPostCount'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Number of posts', 'bricks' ),
			'type'        => 'number',
			'min'         => 1,
			'max'         => 10,
			'placeholder' => 1,
			'small'       => true,
			'required' => [
				[ 'extrasQuery', '=', 'adjacent'],
				[ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'hasLoop', '!=', false ]
			],
		  ];

		  $newControls['adjacentPostOrderby'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Adjacent Post Order', 'bricks' ),
			'type'        => 'select',
			'options'     => [
				'date'           => esc_html__( 'Published date', 'bricks' ),
				'modified'       => esc_html__( 'Modified date', 'bricks' ),
				'menu_order'     => esc_html__( 'Menu order', 'bricks' ),
				'ID'             => esc_html__( 'ID', 'bricks' ),
				'title'          => esc_html__( 'Title', 'bricks' ),
			],
			'inline'      => true,
			'placeholder' => esc_html__( 'Published date', 'bricks' ),
			'required' => [
				[ 'extrasQuery', '=', 'adjacent'],
				[ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'hasLoop', '!=', false ]
			],
		  ];

		  $newControls['adjacentPostOrder'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Order', 'bricks' ),
			'type'        => 'select',
			'options'     => [
				'ASC'  => esc_html__( 'Ascending', 'bricks' ),
				'DESC' => esc_html__( 'Descending', 'bricks' ),
			],
			'inline'      => true,
			'placeholder' => esc_html__( 'Ascending', 'bricks' ),
			'required' => [
				[ 'extrasQuery', '=', 'adjacent'],
				[ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'adjacentPostCount', '!=', [1,''] ],
				[ 'hasLoop', '!=', false ]
			],
		  ];



	  /* related posts */

	  $post_type_options = bricks_is_builder() ? \Bricks\Helpers::get_registered_post_types() : [];
	  $post_type_options['any'] = esc_html__( 'Any', 'bricks' );

	  $newControls['post_type'] = [
		'tab'         => 'content',
		'label'       => esc_html__( 'Post Type', 'bricks' ),
		'type'        => 'select',
		'options'     => $post_type_options,
		'clearable'   => true,
		'inline'      => true,
		'searchable'  => true,
		'placeholder' => esc_html__( 'Default', 'bricks' ),
		'required' => [
		  [ 'extrasQuery', '=', ['related','favorite']],
		  [ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'hasLoop', '!=', false ]
		],
	  ];

	  $newControls['count'] = [
		'tab'         => 'content',
		'label'       => esc_html__( 'Max. related posts', 'bricks' ),
		'type'        => 'number',
		'min'         => 1,
		'max'         => 4,
		'placeholder' => 3,
		'required' => [
		  [ 'extrasQuery', '=', 'related'],
		  [ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'hasLoop', '!=', false ]
		],
	  ];

	  $newControls['order'] = [
		'tab'         => 'content',
		'label'       => esc_html__( 'Order', 'bricks' ),
		'type'        => 'select',
		'options'     => [
		  'ASC' => esc_html__( 'Ascending', 'bricks' ),
		  'DESC' => esc_html__( 'Descending', 'bricks' ),
		],
		'inline'      => true,
		'placeholder' => esc_html__( 'Descending', 'bricks' ),
		'required' => [
		  [ 'extrasQuery', '=', 'related'],
		  [ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'hasLoop', '!=', false ]
		],
	  ];

	  $newControls['orderby'] = [
		'tab'         => 'content',
		'label'       => esc_html__( 'Order by', 'bricks' ),
		'type'        => 'select',
		'options'     => \Bricks\Setup::$control_options['queryOrderBy'],
		'inline'      => true,
		'placeholder' => esc_html__( 'Random', 'bricks' ),
		'required' => [
		  [ 'extrasQuery', '=', 'related'],
		  [ 'query.objectType', '=', 'queryLoopExtras' ],
		  [ 'hasLoop', '!=', false ]
		],
	  ];

	  $newControls['taxonomies'] = [
		'tab'         => 'content',
		'label'       => esc_html__( 'Common taxonomies', 'bricks' ),
		'type'        => 'select',
		'options'     => $taxonomies,
		'multiple'    => true,
		'default'     => [
		  'category',
		  'post_tag'
		],
		'required' => [
		  [ 'extrasQuery', '=', 'related'],
		  [ 'query.objectType', '=', 'queryLoopExtras' ],
		  [ 'hasLoop', '!=', false ]
		],
	  ];


	  /* wpmenu */

	    $nav_menus = [];

		if ( bricks_is_builder() ) {
			foreach ( wp_get_nav_menus() as $menu ) {
				$nav_menus[ $menu->term_id ] = $menu->name;
			}
		}

		$newControls['menuSource'] = [
			'tab' => 'content',
			'label' => esc_html__( 'Menu source', 'bricks' ),
			'type' => 'select',
			'options' => [
			  'dropdown' => esc_html__( 'Select menu', 'bricks' ),
			  'dynamic' => esc_html__( 'Dynamic data', 'bricks' ),
			],
			'inline'      => true,
			'clearable' => false,
			'placeholder' => esc_html__( 'Select menu', 'bricks' ),
			'required' => [
				[ 'extrasQuery', '=', 'wpmenu'],
				[ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'hasLoop', '!=', false ]
			],
		  ];

		  $newControls['x_menu_id'] = [
			'tab' => 'content',
			'label' => esc_html__( 'Menu name, menu slug or menu ID', 'bricks' ),
			'type' => 'text',
			//'inline' => true,
			'placeholder' => esc_html__( '', 'bricks' ),
			'description' => sprintf( '<a href="' . admin_url( 'nav-menus.php' ) . '" target="_blank">' . esc_html__( 'Manage my menus in WordPress.', 'bricks' ) . '</a>' ),
			'required' => [
				[ 'extrasQuery', '=', 'wpmenu'],
				[ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'menuSource', '=', 'dynamic' ],
				[ 'hasLoop', '!=', false ]
			],
		  ];

		$newControls['menu'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Select Menu..', 'bricks' ),
			'type'        => 'select',
			'options'     => $nav_menus,
			'placeholder' => esc_html__( 'Select nav menu', 'bricks' ),
			'description' => sprintf( '<a href="' . admin_url( 'nav-menus.php' ) . '" target="_blank">' . esc_html__( 'Manage my menus in WordPress.', 'bricks' ) . '</a>' ),
			'required' => [
				[ 'extrasQuery', '=', 'wpmenu'],
				[ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'menuSource', '!=', 'dynamic' ],
				[ 'hasLoop', '!=', false ]
			],
		];

		$newControls['x_menu_filter_type'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Menu Items Filter', 'bricks' ),
			'type'        => 'select',
			'options'     => [
				'all'     => esc_html__( 'All Items', 'bricks' ),
				'top'     => esc_html__( 'Top Level Items Only', 'bricks' ),
				'children' => esc_html__( 'Child Items of Parent', 'bricks' ),
			],
			'inline'      => true,
			'placeholder' => esc_html__( 'All Items', 'bricks' ),
			'required' => [
				[ 'extrasQuery', '=', 'wpmenu'],
				[ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'hasLoop', '!=', false ]
			],
		];

		$newControls['x_menu_parent_id'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Parent Menu Item ID', 'bricks' ),
			'type'        => 'text',
			'inline'      => true,
			'placeholder' => '',
			'description' => esc_html__( 'Enter the ID of the parent menu item to show only its children, or use {x_menu_item_id} for current menu ID if inside menu query.', 'bricks' ),
			'required' => [
				[ 'extrasQuery', '=', 'wpmenu'],
				[ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'x_menu_filter_type', '=', 'children' ],
				[ 'hasLoop', '!=', false ]
			],
		];

		/* favorites */

		$newControls['x_favorites_orderby'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Order by', 'bricks' ),
			'type'        => 'select',
			'inline'      => true,
			'options'     => [
				'post__in'       => esc_html( 'As added to list', 'bricks' ),
				'none'           => esc_html( 'None', 'bricks' ),
				'ID'             => esc_html( 'ID', 'bricks' ),
				'author'         => esc_html( 'Author', 'bricks' ),
				'title'          => esc_html( 'Title', 'bricks' ),
				'date'           => esc_html( 'Published date', 'bricks' ),
				'modified'       => esc_html( 'Modified date', 'bricks' ),
				'rand'           => esc_html( 'Random', 'bricks' ),
				'meta_value'     => esc_html( 'Meta value', 'bricks' ),
				'meta_value_num' => esc_html( 'Meta numeric value', 'bricks' ),
			],
			'placeholder' => esc_html__( 'Added to list', 'bricks' ),
			'required' => [
				[ 'extrasQuery', '=', 'favorite'],
				[ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'hasLoop', '!=', false ]
			],
		];

		$newControls['x_favorites_meta_key'] = [
			'type'           => 'text',
			'label'          => esc_html__( 'Meta Key', 'bricks' ),
			'hasDynamicData' => false,
			'inline'      => true,
			'required' => [
				[ 'extrasQuery', '=', 'favorite'],
				[ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'hasLoop', '!=', false ],
				[ 'optionSource', '=', [ 'meta_value','meta_value_num' ] ],
			],
			'required'       => [
				[ 'x_favorites_orderby', '=', [ 'meta_value','meta_value_num' ] ],
			],
		];

		$newControls['x_favorites_order'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Order', 'bricks' ),
			'type'        => 'select',
			'inline'      => true,
			'options'     => [
				'ASC'  => esc_html__( 'Ascending', 'bricks' ),
				'DESC' => esc_html__( 'Descending', 'bricks' ),
			],
			'placeholder' => esc_html__( 'Ascending', 'bricks' ),
			'required' => [
				[ 'extrasQuery', '=', 'favorite'],
				[ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'hasLoop', '!=', false ],
			],
		];

		$newControls['x_newList_separator'] = [
			'tab' => 'content',
			'type' => 'separator',
			'required' => [
				[ 'extrasQuery', '=', 'favorite'],
				[ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'hasLoop', '!=', false ]
			],
		];

		$newControls['newList'] = [
			'tab' => 'content',
			'type' => 'checkbox',
      		'label' => esc_html__( 'Custom List', 'extras' ),
			'required' => [
				[ 'extrasQuery', '=', 'favorite'],
				[ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'hasLoop', '!=', false ]
			],
		];

		$newControls['listSlug'] = [
			'tab'   => 'content',
			//'group' => 'button',
			'label' => esc_html__( 'List Indentifier', 'extras' ),
			'type'  => 'text',
			'inline' => true,
			//'small' => true,
			'placeholder' => esc_html__(''),
			'required' => [
				[ 'extrasQuery', '=', 'favorite'],
				[ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'hasLoop', '!=', false ],
				[ 'newList', '=', true ]
			],
		  ];


		  /* gallery */

		  $newControls['x_gallery_data'] = [
			'tab' => 'content',
			'label' => esc_html__( 'Gallery', 'bricks' ),
			'type' => 'text',
			'inline' => true,
			'placeholder' => esc_html__( 'Dynamic Data', 'bricks' ),
			'required' => [
				[ 'extrasQuery', '=', 'gallery'],
				[ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'hasLoop', '!=', false ]
			],
		  ];

		  $newControls['x_gallery_orderby'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Order by', 'bricks' ),
			'type'        => 'select',
			'inline'      => true,
			'options'     => [
				'post__in'       => esc_html( 'Default', 'bricks' ),
				'date'           => esc_html( 'Published date', 'bricks' ),
				'modified'       => esc_html( 'Modified date', 'bricks' ),
				'ID'             => esc_html( 'ID', 'bricks' ),
				'author'         => esc_html( 'Author', 'bricks' ),
				'title'          => esc_html( 'Title', 'bricks' ),
				'rand'           => esc_html( 'Random', 'bricks' ),
			],
			'placeholder' => esc_html__( 'Default', 'bricks' ),
			'required' => [
				[ 'extrasQuery', '=', 'gallery'],
				[ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'hasLoop', '!=', false ]
			],
		];

		$newControls['x_gallery_order'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Order', 'bricks' ),
			'type'        => 'select',
			'inline'      => true,
			'options'     => [
				'ASC'  => esc_html__( 'Ascending', 'bricks' ),
				'DESC' => esc_html__( 'Descending', 'bricks' ),
			],
			'placeholder' => esc_html__( 'Ascending', 'bricks' ),
			'required' => [
				[ 'extrasQuery', '=', 'gallery'],
				[ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'hasLoop', '!=', false ],
				[ 'x_gallery_orderby', '!=', ['post__in',''] ]
			],
		];

		$newControls['x_gallery_offset'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Offset', 'bricks' ),
			'type'        => 'number',
			'units'       => false,
			'inline'      => true,
			'placeholder' => '0',
			'required' => [
				[ 'extrasQuery', '=', 'gallery'],
				[ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'hasLoop', '!=', false ]
			],
		];

		$newControls['x_gallery_max'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Max no. of images', 'bricks' ),
			'type'        => 'number',
			'units'       => false,
			'inline'      => true,
			'required' => [
				[ 'extrasQuery', '=', 'gallery'],
				[ 'query.objectType', '=', 'queryLoopExtras' ],
				[ 'hasLoop', '!=', false ]
			],
		];


		$query_key_index = absint( array_search( 'query', array_keys( $controls ) ) );
		$new_controls    = array_slice( $controls, 0, $query_key_index + 1, true ) + $newControls + array_slice( $controls, $query_key_index + 1, null, true );

		return $new_controls;

	}



	function setup_queryLoopExtras_controls( $control_options ) {

		$control_options['queryTypes']['queryLoopExtras'] = esc_html__( 'Extras', 'bricks' );
		return $control_options;

	}


	public function run_queryLoopExtras( $results, $query_obj ) {

		if ( $query_obj->object_type !== 'queryLoopExtras' ) {
			return $results;
		}

		$settings = $query_obj->settings;

		if ( ! $settings['hasLoop'] ) {
			return [];
		}

		$extrasQuery = isset( $settings['extrasQuery'] ) ? $settings['extrasQuery'] : false;

	  if ('adjacent' === $extrasQuery) {

		/* adjacent posts */

		$adjacentPost = isset( $settings['adjacentPost'] ) ? $settings['adjacentPost'] : 'prev';
		$adjacentPostSameTerm = isset( $settings['adjacentPostSameTerm'] );
		$excludedTerms = isset( $settings['adjacentPostExcludedTerms'] ) ? $settings['adjacentPostExcludedTerms'] : '';
		$adjacentTaxonomy = isset( $settings['adjacentTaxonomy'] ) ? $settings['adjacentTaxonomy'] : 'category';
		$adjacentPostCount = isset( $settings['adjacentPostCount'] ) ? intval( $settings['adjacentPostCount'] ) : 1;
		$adjacentPostOrderby = isset( $settings['adjacentPostOrderby'] ) ? $settings['adjacentPostOrderby'] : 'date';
		$adjacentPostOrder = isset( $settings['adjacentPostOrder'] ) ? $settings['adjacentPostOrder'] : 'ASC';

		// Only use WordPress's built-in functions when count=1 AND orderby=date
		// For all other cases (including menu_order), use our custom function
		if ( $adjacentPostCount <= 1 && $adjacentPostOrderby === 'date' ) {
			// For a single adjacent post with date ordering, WordPress's built-in functions work well
			if ( 'prev' === $adjacentPost && !empty( get_previous_post($adjacentPostSameTerm, $excludedTerms, $adjacentTaxonomy) ) ) {
				return [ get_previous_post($adjacentPostSameTerm, $excludedTerms, $adjacentTaxonomy) ];
			}

			if ( 'next' === $adjacentPost && !empty( get_next_post($adjacentPostSameTerm, $excludedTerms, $adjacentTaxonomy) ) ) {
				return [ get_next_post($adjacentPostSameTerm, $excludedTerms, $adjacentTaxonomy) ];
			}
		}

		// For all other cases (multiple posts or non-date ordering), use our custom query
		return $this->get_multiple_adjacent_posts(
			$adjacentPost,
			$adjacentPostCount,
			$adjacentPostSameTerm,
			$excludedTerms,
			$adjacentTaxonomy,
			$adjacentPostOrderby,
			$adjacentPostOrder
		);

	  } elseif ( 'related' === $extrasQuery ) {

		/* related posts */

		global $post;

		$post_id = isset($post) && $post ? $post->ID : get_the_ID();
		
		if (!$post_id) {
			$post_id = 0;
		}

			$args = [
				'posts_per_page' => isset( $settings['count'] ) ? intval( $settings['count'] ) : 3,
				'post__not_in'   => [ $post_id ],
				'no_found_rows'  => true, // No pagination
				'orderby'        => isset( $settings['orderby'] ) ? $settings['orderby'] : 'rand',
				'order'          => isset( $settings['order'] ) ? $settings['order'] : 'DESC',
			];

			if ( ! empty( $settings['post_type'] ) ) {
				$args['post_type'] = $settings['post_type'];
			}

			$taxonomies = ! empty( $settings['taxonomies'] ) ? $settings['taxonomies'] : [];

			foreach ( $taxonomies as $taxonomy ) {
				$terms_ids = wp_get_post_terms(
					$post_id,
					$taxonomy,
					[ 'fields' => 'ids' ]
				);

				if ( ! empty( $terms_ids ) ) {
					$args['tax_query'][] = [
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $terms_ids,
					];
				}
			}

			if ( count( $taxonomies ) > 1 && isset( $args['tax_query'] ) ) {
				$args['tax_query']['relation'] = 'OR';
			}

		$args['post_status'] = 'publish';

		$posts_query = new \WP_Query( $args );

		return $posts_query->posts;

	  } elseif ( 'wpmenu' === $extrasQuery ) {

		$menuSource = isset( $settings['menuSource'] ) ? $settings['menuSource'] : 'dropdown';
		$menu = isset( $settings['menu'] ) ? intval( $settings['menu'] ) : false;
		$menu_id = isset( $settings['x_menu_id'] ) ? bricks_render_dynamic_data( $settings['x_menu_id'] ) : false;

		$menuID = 'dropdown' === $menuSource ? $menu : $menu_id;

		return ! $menuID ? [] : $this->x_nav_menu_query( $query_obj, $menuID, $settings );

	  } elseif ( 'favorite' === $extrasQuery ) {

		$post_type = ! empty( $settings['post_type'] ) ? $settings['post_type'] : 'post';

		$favoritesOrderBy = isset( $settings['x_favorites_orderby'] ) ? $settings['x_favorites_orderby'] : 'post__in';
		$favoritesOrder = isset( $settings['x_favorites_order'] ) ? $settings['x_favorites_order'] : 'ASC';

		$args = [
			'post_type' => $post_type,
			'post_status' => ['publish', 'inherit'],
			'posts_per_page' => -1,
			'orderby' => $favoritesOrderBy,
			'order' => $favoritesOrder,
			'cache_results' => false,
			'meta_key' => isset( $settings['x_favorites_meta_key'] ) ? $settings['x_favorites_meta_key'] : '',
		];



		if ('attachment' === $post_type) {
			$args['post_mime_type'] = 'image';
			$args['post_status'] = 'inherit';
		}

		$listSlug = ! empty( $settings['listSlug'] ) ? $settings['listSlug'] : false;

		if (!!$listSlug && isset( $settings['newList'] ) ) {

			$listSlug = strtolower($listSlug);                     // Convert to lowercase
			$listSlug = preg_replace('/\s+/', '_', $listSlug);      // Replace spaces with underscores
			$suffix = preg_replace('/[^a-z0-9_]/', '', $listSlug);    // Remove characters that are not letters, numbers, or underscores

			if ('any' === $post_type) {
				$post_type = 'custom';
			}

			$post__in = Helpers::get_favorite_ids_array( $post_type . '__' . $suffix );
		} else {
			$post__in = Helpers::get_favorite_ids_array( $post_type );
		}

		$post__in_value = is_array( $post__in ) && !empty( $post__in ) ? $post__in : [0];

		if ( 'post__in' === $favoritesOrderBy && 'DESC' === $favoritesOrder ) {
			$post__in_value = array_reverse( $post__in_value );
		}

		$args['post__in'] = $post__in_value;

		$posts_query = new \WP_Query( $args );

		$key = 'x_favorite_ids';

		// Add in_favorites_loop flag for posts in favorites loop, for 'in favorites loop' condition
		foreach ( $posts_query->posts as $key => $post ) {
			$posts_query->posts[ $key ]->in_favorites_loop = true;
		}

		return is_array( $posts_query->posts ) ? $posts_query->posts : [];

	  } elseif ( 'gallery' === $extrasQuery ) {

		$gallery_data = ! empty( $settings['x_gallery_data'] ) ? $settings['x_gallery_data'] : false;

		if (!$gallery_data){
			return [];
		}

		$gallery_images = \Bricks\Integrations\Dynamic_Data\Providers::render_tag($gallery_data, get_the_ID(), 'image' );

		$x_gallery_orderby = isset( $settings['x_gallery_orderby'] ) ? $settings['x_gallery_orderby'] : 'post__in';
		$x_gallery_order = isset( $settings['x_gallery_order'] ) ? $settings['x_gallery_order'] : 'ASC';
		$x_gallery_offset = isset( $settings['x_gallery_offset'] ) ? intval( $settings['x_gallery_offset'] ) : 0;
		$x_gallery_max = isset( $settings['x_gallery_max'] ) ? intval( $settings['x_gallery_max'] ) : 999;

		if (!is_array($gallery_images) || empty($gallery_images)) {
			$post__in = [0];
		} else {
			$post__in = $gallery_images;
		}

		$args = [
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'posts_per_page' => $x_gallery_max,
			'no_found_rows' => true,
			'post__in' => $post__in,
			'orderby' => $x_gallery_orderby,
			'order' => $x_gallery_order,
			'offset' => $x_gallery_offset,
		];

		$posts_query = new \WP_Query( $args );

		return is_array( $posts_query->posts ) ? $posts_query->posts : [];

	}

	  else {
		return [];
	  }


	}


	function extras_setup_post_data( $loop_object, $loop_key, $query_obj ) {

		if ( $query_obj->object_type !== 'queryLoopExtras' ) {
			return $loop_object;
		}

		// Handle menu items
		if ( isset( $query_obj->query_vars['extrasQuery'] ) && $query_obj->query_vars['extrasQuery'] === 'wpmenu' ) {
			global $bricksextras_current_menu_item;
			$bricksextras_current_menu_item = $loop_object;
			return $loop_object;
		}

		// Handle regular posts
		global $post;
		$post = get_post( $loop_object );
		setup_postdata( $post );

		return $loop_object;

	}


	function x_nav_menu_query( $query_obj, $menu_id, $settings ) {
		$menu_items = wp_get_nav_menu_items( $menu_id );
		
		$menu_filter_type = isset( $settings['x_menu_filter_type'] ) ? $settings['x_menu_filter_type'] : 'all';
		$menu_parent_id = isset( $settings['x_menu_parent_id'] ) ? bricks_render_dynamic_data( $settings['x_menu_parent_id'] ) : '';
		
		if ( 'top' === $menu_filter_type ) {
			$menu_items = array_filter( $menu_items, function( $item ) {
				return empty( $item->menu_item_parent ) || $item->menu_item_parent === '0';
			} );
		} elseif ( 'children' === $menu_filter_type && ! empty( $menu_parent_id ) ) {
			$menu_items = array_filter( $menu_items, function( $item ) use ( $menu_parent_id ) {
				// Convert both to strings for comparison
				return (string)$item->menu_item_parent === (string)$menu_parent_id;
			} );
		}
		
		return $menu_items ? $menu_items : [];
	}

	/**
	 * Get multiple adjacent posts
	 *
	 * @param string $direction 'prev' or 'next'
	 * @param int $count Number of posts to retrieve
	 * @param bool $in_same_term Whether posts should be in the same taxonomy term
	 * @param string|array $excluded_terms Array or comma-separated list of excluded term IDs
	 * @param string $taxonomy Taxonomy name
	 * @param string $orderby Field to order by
	 * @param string $order Order direction ('ASC' or 'DESC')
	 *
	 * @return array Array of post objects
	 */
	function get_multiple_adjacent_posts( $direction = 'prev', $count = 3, $in_same_term = false, $excluded_terms = '', $taxonomy = 'category', $orderby = 'date', $order = 'ASC' ) {
		global $post, $wpdb;

		if ( empty( $post ) ) {
			return [];
		}

		// Store the current post ID and post type
		$current_post_id = $post->ID;
		$current_post_type = $post->post_type;

		// Map the orderby value to the database field
		$db_orderby_field = '';
		switch ( $orderby ) {
			case 'date':
				$db_orderby_field = 'post_date';
				break;
			case 'modified':
				$db_orderby_field = 'post_modified';
				break;
			case 'title':
				$db_orderby_field = 'post_title';
				break;
			case 'menu_order':
				$db_orderby_field = 'menu_order';
				break;
			case 'ID':
				$db_orderby_field = 'ID';
				break;
			default:
				$db_orderby_field = 'post_date'; // Default fallback
		}

		// First, let's try to get the first adjacent post
		// For custom ordering, we need to use a custom query
		$first_adjacent_post = null;

		if ($orderby === 'date' || $orderby === 'modified' || $orderby === 'ID') {
			// For date, modified, and ID, we can use WordPress's built-in functions
			if ($direction === 'prev') {
				$first_adjacent_post = get_previous_post($in_same_term, $excluded_terms, $taxonomy);
			} else {
				$first_adjacent_post = get_next_post($in_same_term, $excluded_terms, $taxonomy);
			}
		} else {
			// For other ordering methods like menu_order, we need a custom query
			// Build the query for the first adjacent post
			$join = '';
			$where = '';

			// Handle taxonomy constraints
			if ( $in_same_term && taxonomy_exists( $taxonomy ) ) {
				$join .= " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";
				$where .= $wpdb->prepare( "AND tt.taxonomy = %s", $taxonomy );

				if ( is_object_in_taxonomy( $current_post_type, $taxonomy ) ) {
					$term_array = wp_get_object_terms( $current_post_id, $taxonomy, array( 'fields' => 'ids' ) );

					// Remove any exclusions from the term array to include
					if ( !empty( $excluded_terms ) ) {
						if ( !is_array( $excluded_terms ) ) {
							$excluded_terms = explode( ',', $excluded_terms );
							$excluded_terms = array_map( 'intval', $excluded_terms );
						}
						$term_array = array_diff( $term_array, (array) $excluded_terms );
					}

					$term_array = array_map( 'intval', $term_array );

					if ( $term_array && !is_wp_error( $term_array ) ) {
						$where .= ' AND tt.term_id IN (' . implode( ',', $term_array ) . ')';
					}
				}
			}

			// Add post type constraint
			$where .= $wpdb->prepare(
				" AND p.post_type = %s AND p.post_status = 'publish'",
				$current_post_type
			);

			// Exclude the current post
			$where .= $wpdb->prepare( " AND p.ID != %d", $current_post_id );

			// Get the current post's ordering value
			$current_post_value = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT {$db_orderby_field} FROM $wpdb->posts WHERE ID = %d",
					$current_post_id
				)
			);
			
			// Direction constraint based on the ordering field
			$operator = ($direction === 'prev') ? '<' : '>';

			// Add the constraint for the ordering field
			$where .= $wpdb->prepare(" AND p.{$db_orderby_field} {$operator} %s", $current_post_value);

			// Set the order direction
			$sql_direction = ($direction === 'prev') ? 'DESC' : 'ASC';

			$sql_order = "ORDER BY p.{$db_orderby_field} {$sql_direction}";

			// Build and execute the query for the first adjacent post
			$query = "SELECT p.* FROM $wpdb->posts AS p $join WHERE 1=1 $where $sql_order LIMIT 1";

			// Add a comment to the query for debugging
			$query = "/* First Adjacent Post Query - Direction: $direction, OrderBy: $orderby, Order: $order */ " . $query;
			
			$first_post_result = $wpdb->get_row($query);
			if ($first_post_result) {
				$first_adjacent_post = get_post($first_post_result->ID);
			}
		}

		// If we couldn't get the first adjacent post, return empty array
		if (empty($first_adjacent_post)) {
			return [];
		}

		// If we only need one post, return just the first adjacent post
		if ($count <= 1) {
			// Add the flag for adjacent posts loop
			$first_adjacent_post->in_adjacent_posts_loop = true;
			$first_adjacent_post->adjacent_direction = $direction;
			return [$first_adjacent_post];
		}

		// For more posts, we need to run another query to get additional posts
		// Build the query for additional posts
		$join = '';
		$where = '';

		// Handle taxonomy constraints
		if ( $in_same_term && taxonomy_exists( $taxonomy ) ) {
			$join .= " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";
			$where .= $wpdb->prepare( "AND tt.taxonomy = %s", $taxonomy );

			if ( is_object_in_taxonomy( $current_post_type, $taxonomy ) ) {
				$term_array = wp_get_object_terms( $current_post_id, $taxonomy, array( 'fields' => 'ids' ) );

				// Remove any exclusions from the term array to include
				if ( !empty( $excluded_terms ) ) {
					if ( !is_array( $excluded_terms ) ) {
						$excluded_terms = explode( ',', $excluded_terms );
						$excluded_terms = array_map( 'intval', $excluded_terms );
					}
					$term_array = array_diff( $term_array, (array) $excluded_terms );
				}

				$term_array = array_map( 'intval', $term_array );

				if ( $term_array && !is_wp_error( $term_array ) ) {
					$where .= ' AND tt.term_id IN (' . implode( ',', $term_array ) . ')';
				}
			}
		}

		// Add post type constraint
		$where .= $wpdb->prepare(
			" AND p.post_type = %s AND p.post_status = 'publish'",
			$current_post_type
		);

		// Exclude the current post and the first adjacent post
		$where .= $wpdb->prepare( " AND p.ID NOT IN (%d, %d)", $current_post_id, $first_adjacent_post->ID );

		// Get the first adjacent post's ordering value
		$adjacent_post_value = $first_adjacent_post->$db_orderby_field;

		// Direction constraint based on the ordering field
		$operator = ($direction === 'prev') ? '<=' : '>=';

		// Add the constraint for the ordering field
		$where .= $wpdb->prepare(" AND p.{$db_orderby_field} {$operator} %s", $adjacent_post_value);

		// Set the order direction
		$sql_direction = ($direction === 'prev') ? 'DESC' : 'ASC';

		$sql_order = "ORDER BY p.{$db_orderby_field} {$sql_direction}";

		// Build and execute the query for additional posts
		$query = "SELECT p.* FROM $wpdb->posts AS p $join WHERE 1=1 $where $sql_order LIMIT %d";
		$query = $wpdb->prepare( $query, $count - 1 ); // -1 because we already have one post

		// Add a comment to the query for debugging
		$query = "/* Additional Adjacent Posts Query - Direction: $direction, OrderBy: $orderby, Order: $order */ " . $query;

		$additional_results = $wpdb->get_results( $query );

		// Start with the first adjacent post
		$results = [$first_adjacent_post];

		// Add additional posts if found
		if (!empty($additional_results)) {
			// Convert additional results to proper post objects
			foreach ($additional_results as $post_data) {
				$post_obj = get_post($post_data->ID);
				if ($post_obj) {
					$results[] = $post_obj;
				}
			}
		}

		// Apply final sorting according to the user's preference
		// This ensures consistent ordering, especially if we had to combine results from different queries
		usort( $results, function( $a, $b ) use ( $db_orderby_field, $order ) {
			
			$result = $a->$db_orderby_field <=> $b->$db_orderby_field;

			// Reverse the result if order is DESC
			return $order === 'DESC' ? -$result : $result;
		} );

		// Add a flag to each post to indicate it's in an adjacent posts loop
		// This can be useful for conditional logic in templates
		foreach ( $results as $key => $post_obj ) {
			$results[$key]->in_adjacent_posts_loop = true;
			$results[$key]->adjacent_direction = $direction;
		}

		return $results;
	}


	/**
	 * Display menu item ID in WordPress admin menu screen
	 *
	 * @param int $item_id Menu item ID
	 */
	public function display_menu_item_id( $item_id ) {
		echo '<p style="font-size:12px;color:#888;">Menu Item ID: ' . intval($item_id) . '</p>';
	}

}