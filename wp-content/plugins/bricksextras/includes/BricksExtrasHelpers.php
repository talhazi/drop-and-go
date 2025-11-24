<?php

namespace BricksExtras;

use Bricks\Database;
use Bricks\Query;
use Bricks\Templates;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Helpers {

	/*
     Get most relevant setting from template settings or overriding page settings currently active
    */
	public static function getCurrentTemplateSetting( $key, $default = false) {

		//$headerSetting = Templates::get_templates_by_type( 'header' ) ? \Bricks\Helpers::get_template_setting( $key, Templates::get_templates_by_type( 'header' )[0] ) : false;
		$headerSetting = Database::$active_templates[ 'header' ] ? \Bricks\Helpers::get_template_setting( $key, Database::$active_templates[ 'header' ] ) : false;
		$contentSetting = Database::$active_templates[ 'content' ] ? \Bricks\Helpers::get_template_setting( $key, Database::$active_templates[ 'content' ] ) : false;
		$pageSetting = Database::$page_settings[$key] ?? false;

        if ( isset( Database::$page_settings[$key] ) || array_key_exists( $key, Database::$page_settings ) ) {
			return $pageSetting;
		} elseif ( !!$contentSetting ) {
			return $contentSetting;
		} elseif ( !!$headerSetting ) {
			return $headerSetting;
		} else {
			return $default;
		}
		
	}


	/* 
	 Get looping parent query Id by level - https://itchycode.com/bricks-builder-useful-functions-and-tips/
	*/
	public static function get_bricks_looping_parent_query_id_by_level( $level = 1 ) {
		
		global $bricks_loop_query;
	
		if ( empty( $bricks_loop_query ) || $level < 1 ) {
			return false;
		}
	
		$current_query_id = Query::is_any_looping();
		
		if ( !$current_query_id ) { 
			return false;
		}
		
		if ( !isset( $bricks_loop_query[ $current_query_id ] ) ) {
			return false;
		}
	
		$query_ids = array_reverse( array_keys( $bricks_loop_query ) );
	
		if ( !isset( $query_ids[ $level ] )) {
			return false;
		}
	
		if ( $bricks_loop_query[ $query_ids[ $level ] ]->is_looping ) {
			return $query_ids[ $level ];
		}
	
		return false;
	}


	/* 
	 True if we are viewing inside builder
	*/
	public static function maybePreview(): bool {

		$builder = isset($_GET["bricks"]) && strstr(sanitize_text_field($_GET["bricks"]), 'run');
		$referrer = isset($_SERVER['HTTP_REFERER']) && strstr(sanitize_text_field($_SERVER['HTTP_REFERER']), 'brickspreview');

		return ( $builder || $referrer );
	}


	/* 
	 Create CSS settings
	*/
	public static function doCSSRules($property, array $selectors, $general = false): array {

		$output = [];

		foreach( $selectors as $selector ) {

			if ( !$general ) {
				$selector = '.wsf-form ' . $selector;
			}

			$output[] = [
				'property' => $property,
				'selector' => $selector, 
			];
		}

		return $output;
	}

	/* 
	 Return true if elements CSS already added in <head>
	*/
	public static function elementCSSAdded($name) {

		global $bricksExtrasElementCSSAdded;
		return $bricksExtrasElementCSSAdded[$name] ?? true;

	}

	/* 
	 Maybe enqueue CSS
	*/
	public static function maybeAddElementCSS($name,$stylesheet,$handle): void {

		if ( Helpers::maybePreview() && Helpers::elementCSSAdded($name) ) {
			wp_enqueue_style( $handle, BRICKSEXTRAS_URL . 'components/assets/css/' . $stylesheet . '.css', [], '' );
		}

	}



	/* 
	 Get elements on page
	*/
	public static function getElementsOnPage() {

		static $cached_elements = null;
		
		if ($cached_elements !== null) {
			return $cached_elements;
		}
		
		$templateTypes = [
			'header',
			'content',
			'footer',
		];

		if ( ! method_exists( '\Bricks\Database', 'get_template_data' ) || 
			 ! method_exists( '\Bricks\Database', 'get_setting' ) || 
			 ! method_exists( '\Bricks\Assets', 'minify_css' ) || 
			 ! method_exists( '\Bricks\Query', 'get_query_object_type' )  ||
			 ! method_exists( '\Bricks\Helpers', 'get_template_setting' ) ) {
			return;
		}

		$elementsOnPageArray = [];

		foreach ($templateTypes as $templateType) {

			$templateData = Database::get_template_data( $templateType );

			if ( !!$templateData ) {

				foreach ($templateData as $templateElements) {
					$elementsOnPageArray[] = $templateElements;
				}

			}

		}

        $templateIDs = [];

		/* find elements inside templates, post content, shortcodes */

		foreach ($elementsOnPageArray as $elementOnPage) {

			if ( isset( $elementOnPage['settings']['template'] ) ) {
				$templateIDs[] = ! empty( $elementOnPage['settings']['template'] ) ? intval( $elementOnPage['settings']['template'] ) : 0;
			}

			if ( isset( $elementOnPage['settings']['offcanvas_template'] ) ) {
				$templateIDs[] = ! empty( $elementOnPage['settings']['offcanvas_template'] ) ? intval( $elementOnPage['settings']['offcanvas_template'] ) : 0;
			}

			if ( isset( $elementOnPage['settings']['modal_template'] ) ) {
				$templateIDs[] = ! empty( $elementOnPage['settings']['modal_template'] ) ? intval( $elementOnPage['settings']['modal_template'] ) : 0;
			}

			if ( isset( $elementOnPage['settings']['templateId'] ) ) {
				$templateIDs[] = ! empty( $elementOnPage['settings']['templateId'] ) ? intval( $elementOnPage['settings']['templateId'] ) : 0;
			}

			if ( isset( $elementOnPage['settings']['shortcode'] ) ) {
				$templateIDs[] = strstr( $elementOnPage['settings']['shortcode'], '[bricks_template') ? (int) filter_var($elementOnPage['settings']['shortcode'], FILTER_SANITIZE_NUMBER_INT) : 0;
			}

			if ( isset( $elementOnPage['settings']['dataSource'] ) ) {

				if ( 'bricks' === $elementOnPage['settings']['dataSource'] ) {

					$post_id = get_the_ID();

					if ( ! empty( $post_id ) ) {
						$templateIDs[] = $post_id;
					}

				}

			}

		}

		foreach ($templateIDs as $templateID) {
			
			$templateElements = get_post_meta( $templateID, \BRICKS_DB_PAGE_CONTENT, true );

			if ( !empty( $templateElements ) ) {
				foreach ($templateElements as $templateElement) {
					$elementsOnPageArray[] = $templateElement;
				}
			}
			
		}

		// Get all components data
		$all_components = get_option( defined('\BRICKS_DB_COMPONENTS') ? \BRICKS_DB_COMPONENTS : 'bricks_components', [] );

		// Keep track of processed component IDs to avoid infinite loops
		$processed_component_ids = [];

		// Recursive function to process component elements
		$process_component_elements = function($elements_array) use (&$process_component_elements, &$processed_component_ids, $all_components, &$elementsOnPageArray) {
			// Convert elements array to JSON to search for cid references
			$elements_json = json_encode($elements_array);
			
			// Find all cid references in the data
			preg_match_all('/"cid":"(.*?)"/', $elements_json, $matches);
			
			if (!empty($matches[1])) {
				$found_component_ids = array_unique($matches[1]);
				
				// For each found component ID, get its elements and add them to our array
				foreach ($found_component_ids as $component_id) {
					// Skip if we've already processed this component
					if (in_array($component_id, $processed_component_ids)) {
						continue;
					}
					
					// Mark this component as processed
					$processed_component_ids[] = $component_id;
					
					// Find the component in the components data
					foreach ($all_components as $component) {
						if ($component['id'] === $component_id && !empty($component['elements'])) {
							// Add all elements from this component to our array
							foreach ($component['elements'] as $component_element) {
								$elementsOnPageArray[] = $component_element;
							}
							
							// Recursively process the component elements
							$process_component_elements($component['elements']);
							break;
						}
					}
				}
			}
		};

		// Start the recursive processing with the current elements
		$process_component_elements($elementsOnPageArray);

		// Store the results in the cache before returning
		$cached_elements = $elementsOnPageArray;

		return $cached_elements;
	}


	public static function getAllPostOptions($postType = 'post'): array {

			if ( !bricks_is_builder() ) {
				return [];
			}

			$args = array(
				'post_type' => $postType,
				'posts_per_page' => -1,
				'orderby' => 'title',
				'order' => 'ASC'
			);

			$posts = get_posts( $args );

			$postsOptions = [];

			if ( ! empty( $posts ) ) {
				foreach ( $posts as $post ) {
					$postsOptions[$post->ID] = $post->post_title;
				}
			}

			wp_reset_postdata();

			return $postsOptions;

	}

    // Function to get public CPTs
    public static function getPostTypes( $builtIn = false ): array {
        $postTypes = get_post_types(
            array(
                'public'   => true,
                '_builtin' => $builtIn,
            ),
            'objects'
        );
        unset( $postTypes['bricks_template'] );

        $postTypesArr = [];
        foreach ( $postTypes as $postType ) {
            $postTypesArr[$postType->name] = $postType->label;
        }

        return $postTypesArr;
        // https://d.pr/i/VGYKqF
    }


	 // Function to get product terms
	 public static function getProductTerms($taxonomy): array {

		if ( !bricks_is_builder() ) {
			return [];
		}

		$product_terms = get_terms( 
			[ 
				'taxonomy' => $taxonomy,
			 	'hide_empty' => true 
			] 
		);

		$product_terms_array = [];

		foreach ( $product_terms as $product_term ) {
			$product_terms_array[ $product_term->term_id ] = $product_term->name;
		}

        return $product_terms_array;
    }

	public static function getMathsCompareOptions(): array {

		return [
			'==' => esc_html__( '==', 'bricks' ),
			'!=' => esc_html__( '!=', 'bricks' ),
			'>=' => esc_html__( '>=', 'bricks' ),
			'<=' => esc_html__( '<=', 'bricks' ),
			'>' => esc_html__( '>', 'bricks' ),
			'<' => esc_html__( '<', 'bricks' ),
		];

	}


	public static function getMathsCompare( $amount, $value, $compare, $float = true ) {

		switch ( $compare ) {
			case '==':
				return $float ? $amount === floatval( $value ) : $amount === intval( $value );
				break;
			
			case '>':
				return $float ? $amount > floatval( $value ) : $amount > intval( $value );
				break;
			
			case '>=':
				return $float ? $amount >= floatval( $value ) : $amount >= intval( $value );
				break;
			
			case '<':
				return $float ? $amount < floatval( $value ) : $amount < intval( $value );
				break;
			
			case '<=':
				return $float ? $amount <= floatval( $value ) : $amount <= intval( $value );
				break;
			
			case '!=':
				return $float ? $amount !== floatval( $value ) : $amount !== intval( $value );
				break;
		}

	}

	public static function timeToSeconds(string $time): int {

      if ('' !== $time) {
		if ( str_contains($time, ':') ) {
			$arr = explode(':', $time);
			if (count($arr) === 3) {
				return intval($arr[0]) * 3600 + intval($arr[1]) * 60 + intval($arr[2]);
			}
			// Handle incomplete time format (e.g., "05:" without seconds)
			if (count($arr) === 2 && $arr[1] !== '') {
				return intval($arr[0]) * 60 + intval($arr[1]);
			} else {
				return intval($arr[0]) * 60;
			}
		} else {
			return intval($time);
		}
      } else {
        return 0;
      }
	  
    }

	public static function breadcrumb_item( $url, $content, $maybeLink = true, $maybeSchema = true ) {

		$linkSchema = $maybeSchema ? " itemprop='item'><span itemtype='". $url ."' itemprop='name'" : '';

		if ( $maybeLink ) {
			$output = "<a href='" . $url . "' " . $linkSchema . "><span>" . $content . "</span></a>";
		} else {
			$output = "<span " . $linkSchema . "><span>" . $content . "</span></span>";
		}

		return $output;

	}

	public static function getExtrasMiscOptions(): array {

		$options = [];

			$options = [
				'interactions' =>[
					'title' => 'Element Interactions',
					'docs_slug' => 'interactions',
					'description' => 'Triggers & Actions for BricksExtras elements',
				],
				'query_loop_extras' =>[
					'title' => 'Query Loop Extras',
					'file_name' => 'x-query-loop-extras',
					'description' => 'Adjacent Posts, Related Posts, WP Menu, Favorites, Gallery',
					'docs_slug' => 'query-loop-extras',
					'stylesheet' => false,
					'element' => false,
				],
				'x_ray' =>[
					'title' => 'X-Ray Mode',
					'docs_slug' => 'x-ray-mode',
				],
				
			];

		return $options;

	}


	public static function getExtrasConditions( $conditiontype ): array {

		$conditions = [];

		if ('general' === $conditiontype) {

			$conditions = [
				'archive_type' => [
					'title' => 'Archive type',
					'docs_slug' => 'conditions/#archive-type'
				],
				'at_least_1_search_result' => [
					'title' => 'At least one search result',
					'docs_slug' => 'conditions/#at-least-one-search-result'
				],
				'author_has_cpt_entry' => [
					'title' => 'Author has CPT entry',
					'docs_slug' => 'conditions/#author-has-cpt-entry'
				],
                'authored_by_logged_in_user' => [
					'title' => 'Authored by logged in user',
					'docs_slug' => 'conditions/#authored-by-loggedin-user'
				],
				'body_classes' => [
					'title' => 'Body classes',
					'docs_slug' => 'conditions/#body-classes'
				],
				'category_archive' => [
					'title' => 'Category archive',
					'docs_slug' => 'conditions/#category-archive'
				],
				'current_day' => [
					'title' => 'Current day (of month)',
					'docs_slug' => 'conditions/#current-day-of-month'
				],
				'current_month' => [
					'title' => 'Current month',
					'docs_slug' => 'conditions/#current-month'
				],
				'current_year' => [
					'title' => 'Current year',
					'docs_slug' => 'conditions/#current-year'
				],
				'current_taxonomy_term_has_child' => [
					'title' => 'Current taxonomy term has child',
					'docs_slug' => 'conditions/#current-taxonomy-term-has-child'
				],
				'current_taxonomy_term_has_parent' => [
					'title' => 'Current taxonomy term has parent',
					'docs_slug' => 'conditions/#current-taxonomy-term-has-parent'
				],
        		'cpt_has_at_least_1_entry' => [
					'title' => 'CPT has at least 1 published entry',
					'docs_slug' => 'conditions/#cpt-has-at-least-1-published-entry'
				],
				'date_field_value' => [
					'title' => 'Date field value',
					'docs_slug' => 'conditions/#date-field-value'
				],
				'datetime_field_value' => [
					'title' => 'Datetime field value',
					'docs_slug' => 'conditions/#datetime-field-value'
				],
				'has_child_category' => [
					'title' => 'Has child category',
					'docs_slug' => 'conditions/#has-child-category'
				],
				'has_custom_excerpt' => [
					'title' => 'Has custom excerpt',
					'docs_slug' => 'conditions/#has-custom-excerpt'
				],
				'has_post_content' => [
					'title' => 'Has post content',
					'docs_slug' => 'conditions/#has-post-content'
				],
				'is_child' => [
					'title' => 'Is child',
					'docs_slug' => 'conditions/#is-child'
				],
				'is_parent' => [
					'title' => 'Is parent',
					'docs_slug' => 'conditions/#is-parent'
				],
				'in_favorites_loop' => [
					'title' => 'In favorites loop',
					'docs_slug' => 'conditions/#in-favorites-loop'
				],
				'polylang_language' => [
					'title' => 'Language (Polylang)',
					'docs_slug' => 'conditions/#language-polylang'
				],
				'translatepress_language' => [
					'title' => 'Language (TranslatePress)',
					'docs_slug' => 'conditions/#language-translatepress'
				],
				'language_visitor' => [
					'title' => 'Language (visitor)',
					'docs_slug' => 'conditions/#language-visitor'
				],
				'wpml_language' => [
					'title' => 'Language (WPML)',
					'docs_slug' => 'conditions/#language-wpml'
				],
				'loop_item_number' => [
					'title' => 'Loop item number',
					'docs_slug' => 'conditions/#loop-item-number'
				],
				'page_type' => [
					'title' => 'Page type',
					'docs_slug' => 'conditions/#page-type'
				],
				'post_category' => [
					'title' => 'Post category',
					'docs_slug' => 'conditions/#post-category'
				],
				'post_ancestor' => [
					'title' => 'Post ancestor',
					'docs_slug' => 'conditions/#post-ancestor'
				],
				'post_comment_count' => [
					'title' => 'Post comment count',
					'docs_slug' => 'conditions/#post-comment-count'
				],
				'page_parent' => [
					'title' => 'Page parent',
					'docs_slug' => 'conditions/#page-parent'
				],
				'post_publish_date' => [
					'title' => 'Post publish date',
					'docs_slug' => 'conditions/#post-publish-date'
				],
				'post_tag' => [
					'title' => 'Post tag',
					'docs_slug' => 'conditions/#post-tag'
				],
				'post_type' => [
					'title' => 'Post type',
					'docs_slug' => 'conditions/#post-type'
				],
				'published_during_last' => [
					'title' => 'Published during the last',
					'docs_slug' => 'conditions/#published-during-the-last'
				],
				'tag_archive' => [
					'title' => 'Tag archive',
					'docs_slug' => 'conditions/#tag-archive'
				],
				

				
			];

		} 
		
		elseif ( 'member' === $conditiontype ) {

			$conditions = [
				'easy_digital_downloads' => [
					'title' => 'Easy Digital Downloads',
					'docs_slug' => 'easy-digital-downloads' 
				],
				'fluentcart' => [
					'title' => 'FluentCart',
					'docs_slug' => 'fluentcart'
				],
				'memberpress' => [
					'title' => 'MemberPress',
					'docs_slug' => 'memberpress'
				],
				'pmp_membership_level' => [
					'title' => 'Paid Memberships Pro',
					'docs_slug' => 'paid-memberships-pro'
				],
				'restrict_content' => [
					'title' => 'Restrict Content',
					'docs_slug' => 'restrict-content'
				],
				'sure_members' => [
					'title' => 'SureMembers',
					'docs_slug' => 'suremembers'
				],
				'wishlist_member' => [
					'title' => 'WishList Member',
					'docs_slug' => 'wishlist-member'
				],
				'woocommerce_subscriptions' => [
					'title' => 'WooCommerce Subscriptions',
					'docs_slug' => 'woocommerce-subscriptions'
				],
				'woocommerce_memberships' => [
					'title' => 'WooCommerce Memberships',
					'docs_slug' => 'woocommerce-memberships'
				],
				'wp_member' => [
					'title' => 'WP Members',
					'docs_slug' => 'wp-members'
				]
				
			];

		}
		
		elseif ( 'wc' === $conditiontype ) {

			$conditions = [
				'cart_count' => [
					'title' => 'Cart count (items)',
					'docs_slug' => 'woocommerce-conditions/#cart-count'
				],
				'cart_empty' => [
					'title' => 'Cart empty',
					'docs_slug' => 'woocommerce-conditions/#cart-empty'
				],
				'cart_total' => [
					'title' => 'Cart total (cost)',
					'docs_slug' => 'woocommerce-conditions/#cart-total'
				],
				'cart_total_minus_shipping' => [
					'title' => 'Cart total (Excluding shipping)',
					'docs_slug' => 'woocommerce-conditions/#cart-total'
				],
				'cart_weight' => [
					'title' => 'Cart weight',
					'docs_slug' => 'woocommerce-conditions/#cart-weight'
				],
				'current_product_in_cart' => [
					'title' => 'Current product in cart',
					'docs_slug' => 'woocommerce-conditions/#current-product-in-cart'
				],
				'product_backorders_allowed' => [
					'title' => 'Product allows backorders',
					'docs_slug' => 'woocommerce-conditions/#product-allows-backorders'
				],
				'product_has_category' => [
					'title' => 'Product has category',
					'docs_slug' => 'woocommerce-conditions/#product-has-category'
				],
				'product_has_tag' => [
					'title' => 'Product has tag',
					'docs_slug' => 'woocommerce-conditions/#product-has-tag'
				],
				'product_crosssells_count' => [
					'title' => 'Product cross-sells count',
					'docs_slug' => 'woocommerce-conditions/#product-cross-sells-count'
				],
				'product_in_cart' => [
					'title' => 'Product in cart',
					'docs_slug' => 'woocommerce-conditions/#product-in-cart'
				],
				'product_in_cart_has_coupon' => [
					'title' => 'Product in cart has a coupon applied',
					'docs_slug' => 'woocommerce-conditions/#product-in-cart-has-a-coupon-applied'
				],
				'product_in_stock' => [
					'title' => 'Product in stock',
					'docs_slug' => 'woocommerce-conditions/#product-in-stock'
				],
				'product_on_backorder' => [
					'title' => 'Product on backorder',
					'docs_slug' => 'woocommerce-conditions/#product-on-backorder'
				],
				'product_is_downloadable' => [
					'title' => 'Product is downloadable',
					'docs_slug' => 'woocommerce-conditions/#product-is-downloadable'
				],
				'product_is_virtual' => [
					'title' => 'Product is virtual',
					'docs_slug' => 'woocommerce-conditions/#product-is-virtual'
				],
				'product_rating' => [
					'title' => 'Product rating',
					'docs_slug' => 'woocommerce-conditions/#product-rating'
				],
				'product_type' => [
					'title' => 'Product type',
					'docs_slug' => 'woocommerce-conditions/#product-type'
				],
				'product_upsell_count' => [
					'title' => 'Product upsell count',
					'docs_slug' => 'woocommerce-conditions/#product-upsell-count'
				],
				'product_weight' => [
					'title' => 'Product weight',
					'docs_slug' => 'woocommerce-conditions/#product-weight'
				],
				'user_has_pending_order' => [
					'title' => 'User has pending order',
					'docs_slug' => 'woocommerce-conditions/#user-has-pending-order'
				],
				'user_purchased_current_product' => [
					'title' => 'User purchased current product',
					'docs_slug' => 'woocommerce-conditions/#user-purchased-current-product'
				],
				
				'user_purchased_product' => [
					'title' => 'User purchased product',
					'docs_slug' => 'woocommerce-conditions/#user-purchased-product'
				],
				'user_just_purchased_product' => [
					'title' => 'User just purchased product (thankyou page)',
					'docs_slug' => 'woocommerce-conditions/#user-just-purchased-product'
				],
				
				
			];

		}

		return $conditions;

	}

	/* get video id from Youtube/vimeo different urls */
	
	public static function get_video_id_from_url($url) {
		
		$video_id = false;
		$parts = parse_url($url);
		
		if(isset($parts['query'])){
			parse_str($parts['query'], $qs);
			if(isset($qs['v'])){
				$video_id = $qs['v'];
			}else if(isset($qs['vi'])){
				$video_id = $qs['vi'];
			}
		}  
		
		if(!$video_id && isset($parts['path'])){
			$path = explode('/', trim($parts['path'], '/'));
			$video_id = $path[count($path)-1];
		}
		
		if($video_id && preg_match('/^[a-zA-Z0-9_-]{6,20}$/', $video_id)) {
			return $video_id;
		}
		
		return false;

	}


	/* 
	 Maybe minify JS (filter if needs to be disabled)
	*/
	public static function maybeMinifyScripts($script_name) {

		$minifyScripts = true;

		/* allow users to disable */
		$minifyScripts = apply_filters( 'bricksextras/minify', $minifyScripts, $script_name, 10, 2 );

		return $minifyScripts ? $script_name. '.min' : $script_name;
	}



	/*
	  Get favorite IDs
	*/
	public static function get_favorite_ids_array($list = false) {

		$key = 'x_favorite_ids';
		$cookie_prefix = 'x_favorite_ids__';
		$transient_key = sanitize_text_field( 'x_favorite_ids_transient_' . get_current_user_id() ); // unique key for each user
		$id_array = [];
	
		// Check if user is logged in
		if (is_user_logged_in()) {

			global $x_favorite_data_global;

			if ( $x_favorite_data_global && is_array( $x_favorite_data_global ) ) {
				$id_array = $x_favorite_data_global;
			} else {

				// Try to get data from transient
				$id_array_transient = get_transient( $transient_key );

				if ( $id_array_transient !== false ) {

					if ( \BricksExtras\Helpers::is_json($id_array_transient) && '' !== $id_array_transient ) {
						$decode_transient = json_decode($id_array_transient, true);
						if (json_last_error() === JSON_ERROR_NONE && is_array($decode_transient)) {
							$id_array = $decode_transient;
						}
					} else {
						$id_array = [];
					}
				
					if (json_last_error() !== JSON_ERROR_NONE) {

						// Transient not found, fetch from user meta
						$user_meta = get_user_meta(get_current_user_id(), $key, true);
						
						if ( $user_meta && \BricksExtras\Helpers::is_json($user_meta) ) {
							$id_array = json_decode($user_meta, true);
							if (json_last_error() === JSON_ERROR_NONE && is_array($id_array)) {
								$id_array = self::arrayValuesFromMetaAsIntegers($id_array);
							} else {
								$id_array = []; // fallback if JSON error occurs
							}
						} else {
							$favorite_data = self::getFavoriteDataFromCookie($cookie_prefix);
							$id_array = is_array($favorite_data) ? $favorite_data : [];
						}

						// Set a 12hr transient for caching
						set_transient($transient_key, wp_json_encode( $id_array ), 12 * HOUR_IN_SECONDS);

					}

				} 

			}
	
		} else {

			// Use cookie for guests
			$favorite_data = self::getFavoriteDataFromCookie($cookie_prefix);
			$id_array = is_array($favorite_data) ? $favorite_data : [];
		}
	
		// Retrieve only the post_type data if specified
		$output = (!$list || !empty($id_array[$list])) ? ($id_array[$list] ?? []) : [];
	
		return is_array($output) ? array_filter(array_map('absint', $output)) : [];
	}
	



	/* Get favorite data from all relevant cookies */
	public static function getFavoriteDataFromCookie($cookie_prefix): array {

		$favorite_data = [];

		// Iterate through all cookies to find those that start with the prefix
		foreach ($_COOKIE as $cookie_name => $cookie_value) {

			$cookie_name = sanitize_text_field($cookie_name);

			if (strpos($cookie_name, $cookie_prefix) === 0) {

				// Remove prefix from cookie name for use as key
				$key_name = substr($cookie_name, strlen($cookie_prefix));

				$cookieValue = wp_unslash($cookie_value);

				if ( \BricksExtras\Helpers::is_json($cookieValue) ) {

					$data = json_decode($cookieValue, true);

					// Check if the JSON is valid & sanitize IDs as integers
					if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
						$favorite_data[$key_name] = self::arrayValuesAsIntegers($data);
					}

				}
			}
		}

		return $favorite_data;
	}


	/* Ensure IDs in nested arrays are integers */
	public static function arrayValuesFromMetaAsIntegers($array): array {

		if (!is_array($array)) {
			return [];
		}

		foreach ($array as $key => $value) {

			$key = sanitize_text_field($key);

			if (is_array($value)) {
				// Use the arrayValuesAsIntegers function to process nested arrays
				$array[$key] = self::arrayValuesAsIntegers($value);
			} else {
				// If not an array, make it an empty array
				$array[$key] = [];
			}
		}

		return $array;
	}


	/* Ensure all items in a flat array are integers */
	public static function arrayValuesAsIntegers($array): array {

		// If it's not an array, return an empty array
		if (!is_array($array)) {
			return [];
		}
	
		// Filter the array to keep only integer values
		return array_filter(array_map('intval', $array), 'is_int');
	}

	// Helper function to validate if a string is JSON
	public static function is_json($string) {
		json_decode($string);
		return (json_last_error() === JSON_ERROR_NONE);
	}

	// Get parent loop ID (taking into account components)
	public static function get_parent_loop_id( $element, $is_component = false ) {

		$id = false;
		
		if ( method_exists('\Bricks\Query','is_any_looping') ) {

			$query_id = \Bricks\Query::is_any_looping();

			if ( $query_id ) {

				if (!$is_component) {
					$id = strtok(\Bricks\Query::get_query_element_id( $query_id ), '-'); 

					if ( $id === 'jet' ) {
						$id = \Bricks\Query::get_query_element_id( $query_id );
					}

				} else {
					$id = isset($element['parentComponent']) && $element['parentComponent'] ? $element['parentComponent'] : strtok(\Bricks\Query::get_query_element_id( $query_id ), '-');

					if ( $id === 'jet' ) {
						$id = isset($element['parentComponent']) && $element['parentComponent'] ? $element['parentComponent'] : \Bricks\Query::get_query_element_id( $query_id );
					}
				}
			}

		}

		return $id;

	}

	// Check if an element is in component instance
	public static function is_component_instance( $element ) {

		if ( empty( $element ) ) {
            return false;
        }

        // Case 1: Element is a component root
        $is_component_root = ! empty( $element['cid'] );

        // Case 2: Element is inside a component
        $is_inside_component = ! empty( $element['instanceId'] ) && ! empty( $element['parentComponent'] );

        return $is_component_root || $is_inside_component;

	}

	/**
	 * Generate a unique identifier for an element, taking into account component context
	 * and the full component hierarchy in Bricks v2.
	 * 
	 * @param array  $element    The element data array.
	 * @param string $element_id The original element identifier.
	 * @return string The unique identifier.
	 */
	public static function generate_unique_identifier( $element, $element_id ) {
		// If not in a component context, return original element ID
		if ( ! self::is_component_instance( $element ) ) {
			return $element_id;
		}
		
		// Check if Bricks Frontend elements are available
		if ( class_exists( '\Bricks\Frontend' ) && property_exists( '\Bricks\Frontend', 'elements' ) && ! empty( \Bricks\Frontend::$elements ) ) {
			// Build a unique path that includes instance IDs
			$path = array();
			
			// Start with the element's instanceId (if available)
			if (!empty($element['instanceId'])) {
				// This is the direct container of the element
				$container_id = $element['instanceId'];
				$path[] = $container_id;
				
				// Now find the container element to get its instanceId (the wrapper)
				if (isset(\Bricks\Frontend::$elements[$container_id])) {
					$container_element = \Bricks\Frontend::$elements[$container_id];
					
					// If the container has an instanceId, that's our wrapper
					if (!empty($container_element['instanceId'])) {
						$wrapper_id = $container_element['instanceId'];
						$path = array($wrapper_id, $container_id); // Ensure wrapper is first
					}
				}
			}
			
			// If we have a path, create a unique identifier
			if (!empty($path)) {
				// Add the element ID to the end of the path
				$final_id = implode('__', $path) . '__' . $element_id;
				return $final_id;
			}
		}
		
		// Fallback: If we couldn't use Bricks Frontend elements or find instance IDs,
		// use the component ID if available
		if (!empty($element['cid'])) {
			return $element['cid'] . '__' . $element_id;
		}
		
		// Last resort: return the original element ID
		return $element_id;
	}

	public static function get_parent_component_id( $element ) {

		if ( empty( $element ) ) {
			return false;
		}

		if ( ! self::is_component_instance( $element ) ) {
			return false;
		}

		if ( empty( $element['parentComponent'] ) ) {
			return false;
		}

		return sanitize_text_field( $element['parentComponent'] );
	}


	public static function is_vidstack_script_enqueued() {
      
		global $wp_scripts;
		
		if (!is_object($wp_scripts)) {
			return false;
		}
		
		// Check all registered scripts
		foreach ($wp_scripts->registered as $handle => $script) {
			// Check if the handle or source contains 'vidstack'
			if (strpos($handle, 'vidstack') !== false || 
				(isset($script->src) && strpos($script->src, 'vidstack') !== false)) {
				
				// Check if this script is actually enqueued
				if (wp_script_is($handle, 'enqueued')) {
					return true;
				}
			}
		}
		
		return false;
	}
	

	


	/**
	 * Normalize image settings for poster images
	 * 
	 * @param array $settings Element settings
	 * @param string $setting_name The name of the image setting (default: 'image')
	 * @param bool $force_original_image Force original image URL for 'full' size (default: false)
	 * @return array Normalized image settings
	 */
	public static function get_normalized_image_settings( $settings, $setting_name = 'image', $force_original_image = false ) {

		if ( empty( $settings[$setting_name] ) ) {
			return [
				'id'   => 0,
				'url'  => false,
				'size' => \BRICKS_DEFAULT_IMAGE_SIZE,
			];
		}

		$image = $settings[$setting_name];

		// Size
		$image['size'] = empty( $image['size'] ) ? \BRICKS_DEFAULT_IMAGE_SIZE : $settings[$setting_name]['size'];

		if ( ! empty( $image['useDynamicData'] ) ) {
			
			// Try with 'image' type first as it's most appropriate for our use case
			$dynamic_content = \Bricks\Integrations\Dynamic_Data\Providers::render_tag( $image['useDynamicData'], 'image', [ 'size' => $image['size'] ] );
			
			// If we got a result, process it
			if ( ! empty( $dynamic_content ) ) {
				// Handle different return types from dynamic data
				if ( is_numeric( $dynamic_content ) ) {
					// It's an attachment ID
					$image['id'] = $dynamic_content;
					// Clear any existing URL to ensure we generate from the ID
					unset( $image['url'] );
				} elseif ( is_array( $dynamic_content ) && ! empty( $dynamic_content[0] ) ) {
					$content = $dynamic_content[0];
					
					if ( is_numeric( $content ) ) {
						// It's an attachment ID in an array
						$image['id'] = $content;
						// Clear any existing URL to ensure we generate from the ID
						unset( $image['url'] );
					} elseif ( is_string( $content ) ) {
						if ( strpos( $content, '<img' ) !== false ) {
							// Extract the src attribute from HTML
							preg_match('/src=["\']([^"\']*)["\']/i', $content, $matches);
							if ( ! empty( $matches[1] ) ) {
								$image['url'] = $matches[1];
							}
						} else {
							// It's probably a direct URL
							$image['url'] = $content;
						}
					}
				} elseif ( is_string( $dynamic_content ) ) {
					// Direct string result
					if ( strpos( $dynamic_content, '<img' ) !== false ) {
						// Extract the src attribute from HTML
						preg_match('/src=["\']([^"\']*)["\']/i', $dynamic_content, $matches);
						if ( ! empty( $matches[1] ) ) {
							$image['url'] = $matches[1];
						}
					} else {
						// It's probably a direct URL
						$image['url'] = $dynamic_content;
					}
				}
			}
		}

		$image['id'] = empty( $image['id'] ) ? 0 : $image['id'];

		if ( ! isset( $image['url'] ) ) {
			$image['url'] = ! empty( $image['id'] ) ? wp_get_attachment_image_url( $image['id'], $image['size'] ) : false;
		}

		// Force original image URL if requested and conditions are met
		if ( $force_original_image && $image['size'] === 'full' && ! empty( $image['id'] ) ) {
			// Check if current URL contains 'scaled' indicating WordPress has scaled the image
			if ( isset( $image['url'] ) && strpos( $image['url'], '-scaled.' ) !== false ) {
				$original_url = wp_get_original_image_url( $image['id'] );
				
				if ( $original_url && $original_url !== $image['url'] ) {
					$image['url'] = $original_url;
				}
			}
		}

		return $image;
	}
	

	/**
	 * Set a unique identifier attribute for an element, taking into account loop context
	 *
	 * @param object $element_instance The element instance (typically $this in an element class)
	 * @param string $attribute_name The attribute name to set (default: 'data-x-id')
	 * @param string $prefix Optional prefix to add to the identifier
	 * @return string The generated identifier
	 */
	public static function set_identifier_attribute( $element_instance, $attribute_name = 'data-x-id', $prefix = '' ) {
		
		$element_id = $element_instance->id;
		$element = $element_instance->element;
		
		// Start with the element ID
		$identifier = self::generate_unique_identifier( $element, $element_id );
		$final_identifier = $identifier;
		
		// Handle loop context if Bricks Query class exists and has the required method
		if ( method_exists('\Bricks\Query','is_any_looping') ) {
			$query_id = \Bricks\Query::is_any_looping();
			
			if ( $query_id ) {
				$loop_index = false;
				
				// Check for nested loops up to 2 levels deep
				if ( self::get_bricks_looping_parent_query_id_by_level(2) ) {
					// We're in a doubly-nested loop
					$loop_index = \Bricks\Query::get_query_for_element_id( 
						\Bricks\Query::get_query_element_id( self::get_bricks_looping_parent_query_id_by_level(2) ) 
					)->loop_index . '_' . 
					\Bricks\Query::get_query_for_element_id( 
						\Bricks\Query::get_query_element_id( self::get_bricks_looping_parent_query_id_by_level(1) ) 
					)->loop_index . '_' . 
					\Bricks\Query::get_loop_index();
				} else if ( self::get_bricks_looping_parent_query_id_by_level(1) ) {
					// We're in a singly-nested loop
					$loop_index = \Bricks\Query::get_query_for_element_id( 
						\Bricks\Query::get_query_element_id( self::get_bricks_looping_parent_query_id_by_level(1) ) 
					)->loop_index . '_' . \Bricks\Query::get_loop_index();
				} else {
					// We're in a simple loop
					$loop_index = \Bricks\Query::get_loop_index();
				}
				
				// Append the loop index to make the identifier unique within loops
				$final_identifier = $identifier . '_' . $loop_index;
			}
		}
		
		// Add prefix if provided
		if ( !empty($prefix) ) {
			$final_identifier = $prefix . '_' . $final_identifier;
		}
		
		// Set the attribute on the element's root
		$element_instance->set_attribute( '_root', $attribute_name, esc_attr($final_identifier) );
		
		// Return the identifier in case it's needed for other operations
		return $final_identifier;
	}

	/**
	 * Get the array of user IDs who favorited a post
	 *
	 * @param int $post_id The ID of the post to check
	 * @return array Array of user IDs who favorited the post, or empty array if none or if post meta is disabled
	 */
	public static function get_favorite_users($post_id) {
		// Check if post meta functionality is enabled
		$use_post_meta = apply_filters('bricksextras/favorite_post_meta', true);
		if (!$use_post_meta) {
			return array();
		}

		$post_meta_key = 'x_favorite_users';
		$favorite_users = get_post_meta($post_id, $post_meta_key, true);
		
		if (empty($favorite_users)) {
			return array();
		}
		
		// Decode the JSON data
		$favorite_users = json_decode($favorite_users, true);
		
		// Check for JSON errors or non-array result
		if (json_last_error() !== JSON_ERROR_NONE || !is_array($favorite_users)) {
			return array();
		}
		
		$favorite_users = array_filter(array_map('absint', $favorite_users));
		
		// Return array of user IDs
		return $favorite_users;
	}


	/**
	 * Get the count of users who favorited a post
	 *
	 * @param int $post_id The ID of the post to check
	 * @return int Number of users who favorited the post, or 0 if none or if post meta is disabled
	 */
	public static function get_favorite_users_count($post_id) {
		
		// Check if post meta functionality is enabled
		$use_post_meta = apply_filters('bricksextras/favorite_post_meta', true);
		if (!$use_post_meta) {
			return 0;
		}

		$count = get_post_meta($post_id, 'x_favorite_users_count', true);
		
		// If count is not set, fall back to counting the users array
		if (empty($count) && $count !== '0') {
			$users = self::get_favorite_users($post_id);
			$count = count($users);
		}
		
		return (int) $count;
	}

	/**
	 * Clean repeater field arrays to prevent circular references
	 * 
	 * This function removes self-referential properties in repeater fields that cause
	 * circular references. When Bricks elements are used in components, they can create
	 * nested copies of repeater fields inside each item, leading to infinite recursion.
	 *
	 * @param array|string $settings The settings array containing repeater fields, or a single repeater field array
	 * @param string|array $field_names The name(s) of repeater fields to watch for
	 * @return array Clean copy of the settings with circular references removed from all repeater fields
	 */
	public static function clean_repeater_field($settings, $field_names = '') {
		
		// If not an array, return as is
		if (!is_array($settings)) {
			return $settings;
		}
		
		// Convert single field name to array for consistent processing
		if (!is_array($field_names)) {
			$field_names = $field_names ? [$field_names] : [];
		}
		
		// If field_names is empty, nothing to clean
		if (empty($field_names)) {
			return $settings;
		}
		
		// If this is a settings array with multiple repeater fields
		$cleaned_settings = $settings;
		
		// Clean each repeater field in the settings
		foreach ($field_names as $field_name) {
			// If this is a direct repeater field array
			if (isset($settings[0]) && !isset($settings[$field_name])) {
				// This is a repeater field array itself, clean it directly
				return self::clean_array_recursive($settings, $field_names);
			}
			
			// Otherwise, look for the named field in the settings
			if (isset($settings[$field_name]) && is_array($settings[$field_name])) {
				$cleaned_settings[$field_name] = self::clean_array_recursive($settings[$field_name], $field_names);
			}
		}
		
		return $cleaned_settings;
	}
	
	/**
	 * Recursively cleans an array by removing properties that could cause circular references
	 *
	 * @param array $array The array to clean
	 * @param array $field_names Names of fields to remove
	 * @return array Cleaned array
	 */
	private static function clean_array_recursive($array, $field_names) {
		if (!is_array($array)) {
			return $array;
		}
		
		$clean_array = [];
		
		foreach ($array as $key => $value) {
			// Skip properties with names that match any field name we're watching for
			if (in_array($key, $field_names, true)) {
				continue;
			}
			
			if (is_scalar($value) || is_null($value)) {
				$clean_array[$key] = $value;
			} elseif (is_array($value)) {
				$clean_array[$key] = self::clean_array_recursive($value, $field_names);
			} elseif (is_object($value)) {
				if (method_exists($value, 'to_array')) {
					$clean_array[$key] = self::clean_array_recursive($value->to_array(), $field_names);
				} else {
					$clean_array[$key] = self::clean_array_recursive((array)$value, $field_names);
				}
			}
		}
		
		return $clean_array;
	}
	
	/**
	 * Clean element settings to prevent circular references in repeater fields
	 * 
	 * This method is hooked to the 'bricks/element/settings' filter and cleans
	 * repeater fields based on the element type to prevent memory exhaustion
	 * and infinite recursion when elements are used in components.
	 *
	 * @param array $settings The element settings
	 * @param object $element The element object
	 * @return array Cleaned settings
	 */
	public static function clean_repeater_settings($settings, $element) {

		if (!is_array($settings)) {
			return $settings;
		}
		
		// Get element name
		$element_name = isset($element->name) ? $element->name : '';
		
		// Define repeater fields to clean for each element type
		$element_repeater_fields = [];
		
		switch ($element_name) {

			case 'xbreadcrumbs':
				$element_repeater_fields = ['cpt'];
				break;

			case 'xcountdown':
				$element_repeater_fields = ['fields', 'fieldsFixed'];
				break;
				
			case 'xdynamicchart':
				$element_repeater_fields = ['content_items', 'datasetItems'];
				break; 

			case 'xdynamictable':
				$element_repeater_fields = ['content_items', 'attributes','content_items_static','row_items'];
			break;

			case 'xheadersearch':
				$element_repeater_fields = ['additionalParams'];
				break;

			case 'ximagehotspots':
				$element_repeater_fields = ['markers'];
				break;

			case 'xmediaplayeraudio':
				$element_repeater_fields = ['controlsTopAudio', 'controlsCenterAudio','controlsBottomAudio','srcRepeaterAudio','chapters','textTracks'];
			break;

			case 'xsocialshare':
				$element_repeater_fields = ['items'];
				break;
				
			case 'xmediaplayer':
				$element_repeater_fields = [
					'controls', 'controlsTop', 'controlsCenter', 
					'smallControlsTop', 'smallControls', 'smallcontrolsCenter',
					'srcRepeater','chapters','textTracks'
				];
				break;
			case 'xmediaplaylist':
				$element_repeater_fields = ['srcRepeater', 'chapters','textTracks'];
				break;
			case 'xproslidergallery':
				$element_repeater_fields = ['linkCustom'];
				break;
			case 'xtoggleswitch':
				$element_repeater_fields = ['labels','labelsQuery'];
				break;

			case 'xpromodal':
			case 'xpromodalnestable':
				$element_repeater_fields = ['triggers'];
				break;
				
			
		}
		
		// Clean the repeater fields if we have any defined for this element
		if (!empty($element_repeater_fields)) {
			$cleaned_settings = self::clean_repeater_field($settings, $element_repeater_fields);
			
			return $cleaned_settings;
		}
		
		return $settings;
	}

}
