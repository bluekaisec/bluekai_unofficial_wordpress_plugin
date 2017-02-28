<?php
/*
Plugin Name: BlueKai (Unofficial) Tagger
Plugin URI: http://www.unofficialbluekai.com
Description: Adds the BlueKai tag and declares data points (phints) for your site
Version: 0.1
Author: Anonymous
Author URI: http://www.unofficialbluekai.com
Text Domain: unofficialbluekai
*/

function activate_bluekai() {

	// Set data style to underscore for fresh installations
	update_option( 'bluekaiDataStyle', '1' );
	
	add_option( 'bluekaiTag', '' );
	add_option( 'bluekaiTagCode', '' );
	add_option( 'bluekaiTagLocation', '' );
	add_option( 'bluekaiExclusions', '' );
	add_option( 'bluekaiSiteID', '' );
	add_option( 'bluekaiLimit', '' );		
}

function deactive_bluekai() {
	delete_option( 'bluekaiExclusions' );
	delete_option( 'bluekaiDataStyle', '' );
	delete_option( 'bluekaiTagLocation' );
	delete_option( 'bluekaiTagCode' );
	delete_option( 'bluekaiTag' );
	delete_option( 'bluekaiSiteID' );
	delete_option( 'bluekaiLimit' );
	
}

function admin_init_bluekai() {
	register_setting( 'bluekaiTagBasic', 'bluekaiSiteID' );
	register_setting( 'bluekaiTagBasic', 'bluekaiLimit' );	
	register_setting( 'bluekaiTagBasic', 'bluekaiExclusions' );	// remember to add in advanced page to add in exclusions again

	wp_register_style( 'bluekai-stylesheet', plugins_url('bluekai.css', __FILE__) );
}

function admin_menu_bluekai() {
	$page = add_options_page( __( 'bluekai Tag Settings', 'bluekai' ), __( 'BlueKai (Unofficial) Settings', 'bluekai' ), 'manage_options' , 'bluekai', 'options_page_bluekai' );
	add_action( 'admin_print_styles-' . $page, 'admin_styles_bluekai' );
}

function options_page_bluekai() {
	include plugin_dir_path( __FILE__ ).'bluekai.options.php';
}

function admin_styles_bluekai() {
	wp_enqueue_style( 'bluekai-stylesheet' );
}


/*
 * Admin messages
 */
function admin_notices_bluekai() {
	global $pagenow;
	$currentScreen = get_current_screen();
	$bluekaiTagCode = get_option( 'bluekaiTagCode' );
	$bluekaiSiteID = get_option( 'bluekaiSiteID' );
	$bluekaiLimit = get_option( 'bluekaiLimit' );	

	// Add an admin message when looking at the plugins page if the bluekai tag is not found
	if ( $pagenow == 'plugins.php' ) {
		if ( empty( $bluekaiTagCode ) && ( empty( $bluekaiSiteID ) || empty( $bluekaiLimit ) ) ) {
			$html = '<div class="updated">';
			$html .= '<p>';
			$html .= sprintf( __( 'Your BlueKai Plug-in <b>WILL NOT WORK</b> without entering your bluekai site/container ID and limit <a href="%s">OVER HERE &raquo;</a>', 'bluekai' ), esc_url( 'options-general.php?page=bluekai' ) );
			$html .= '</p>';
			$html .= '</div>';
			echo $html;
		}
	}		
}

/*
 * Removes exclusions listed in admin setting
 */
function removeAllExclusions( $bluekaidata ) {
	$exclusions = get_option( 'bluekaiExclusions' );
	if ( !empty( $exclusions ) ) {

		// Convert list to array and trim whitespace
		$exclusions = array_map( 'trim', explode( ',', $exclusions ) );

		foreach ( $exclusions as $exclusion ) {
			if ( array_key_exists( $exclusion, $bluekaidata ) ) {
				// Remove from bluekai data array
				unset( $bluekaidata[$exclusion] );
			}
		}
	}
	return $bluekaidata;
}
add_filter( 'bluekai_removeAllExclusions', 'removeAllExclusions' );


/*
 * Convert camel case to underscores
 */
function convertAllCamelCase( $bluekaidata, $arrayHolder = array() ) {
	$underscoreArray = !empty( $arrayHolder ) ? $arrayHolder : array();
	foreach ( $bluekaidata as $key => $val ) {
		$newKey = preg_replace( '/[A-Z]/', '_$0', $key );
		$newKey = strtolower( $newKey );
		$newKey = ltrim( $newKey, '_' );
		if ( !is_array( $val ) ) {
			$underscoreArray[$newKey] = $val;
		} else {
			$underscoreArray[$newKey] = convertAllCamelCase( $val, $underscoreArray[$newKey] );
		}
	}
	return $underscoreArray;
}
add_filter( 'bluekai_convertAllCamelCase', 'convertAllCamelCase' );


/*
 * Adds WooCommerce data to data layer
 */
function allWooCommerceData( $bluekaidata ) {
	global $woocommerce;

	// Get cart details
	$woocart = (array) $woocommerce->cart;
	$productData = array();

	if ( !empty( $woocart['cart_contents'] ) ) {

		// Get cart product IDs, SKUs, Titles etc.
		foreach ( $woocart['cart_contents'] as $cartItem ) {
			$productMeta = new WC_Product( $cartItem['product_id'] );

			$productData['product_id'][] = $cartItem['product_id'];
			$productData['product_sku'][] = $productMeta->post->sku;
			$productData['product_name'][] = $productMeta->post->post->post_title;
			$productData['product_quantity'][] = $cartItem['quantity'];
			$productData['product_regular_price'][] = get_post_meta( $cartItem['product_id'], '_regular_price', true );
			$productData['product_sale_price'][] = get_post_meta( $cartItem['product_id'], '_sale_price', true );
			$productData['product_type'][] = $productMeta->post->product_type;
		}
	}

	// Remove the extensive individual product details
	unset( $woocart['cart_contents'] );
	unset( $woocart['tax'] );

	// Get currency in use
	$woocart['site_currency'] = get_woocommerce_currency();

	// Merge shop and cart details into bluekaidata
	$bluekaidata = array_merge( $bluekaidata, $woocart );
	$bluekaidata = array_merge( $bluekaidata, $productData );

	return $bluekaidata;
}
add_filter( 'bluekai_allWooCommerceData', 'allWooCommerceData' );

/*
 * Creates the data object as an array
 */
function phintsObject() {
	global $bluekaidata;
	$bluekaidata = array();

	// Blog info
	$bluekaidata['siteName'] = get_bloginfo( 'name' );
	$bluekaidata['siteDescription'] = get_bloginfo( 'description' );

	if ( ( is_single() ) || is_page() ) {
		global $post;

		// Get categories
		$categories = get_the_category();
		$catout = array();

		if ( $categories ) {
			foreach ( $categories as $category ) {
				$catout[] = $category->slug;
			}
			$bluekaidata['postCategory'] = $catout;
		}

		// Get tags
		$tags = get_the_tags();
		$tagout = array();
		if ( $tags ) {
			foreach ( $tags as $tag ) {
				$tagout[] = $tag->slug;
			}
			$bluekaidata['postTags'] = $tagout;
		}

		// Misc post/page data
		$bluekaidata['pageType'] = get_post_type();
		$bluekaidata['postTitle'] = get_the_title();
		$bluekaidata['postAuthor'] = get_userdata( $post->post_author )->display_name;
		$bluekaidata['postDate'] = get_the_time( 'Y/m/d' );

		// Get and merge post meta data
		$meta = get_post_meta( get_the_ID() );
		if ( $meta ) {
			$bluekaidata = array_merge( $bluekaidata, $meta );
		}
	}
	else if ( is_archive() ) {
			$bluekaidata['pageType'] = "archive";
		}
	else if ( ( is_home() ) || ( is_front_page() ) ) {
			$bluekaidata['pageType'] = "homepage";
		}
	else if ( is_search() ) {
			// Collect search and result data
			$searchQuery = get_search_query();
			$searchResults = &new WP_Query( 's='.str_replace( ' ', '+', $searchQuery.'&showposts=-1' ) );
			$searchCount = $searchResults->post_count;
			wp_reset_query();

			// Add to udo
			$bluekaidata['pageType'] = "search";
			$bluekaidata['searchQuery'] = $searchQuery;
			$bluekaidata['searchResults'] = $searchCount;
		}

	// Add shop data if WooCommerce is installed
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		$bluekaidata = apply_filters( 'bluekai_allWooCommerceData', $bluekaidata );
	}

	// Include data layer additions from action if set
	if ( has_action( 'bluekai_addTophintsObject' ) ) {
		do_action( 'bluekai_addTophintsObject' );
	}

	if ( get_option( 'bluekaiDataStyle' ) == '1' ) {
		// Convert camel case to underscore
		$bluekaidata = apply_filters( 'bluekai_convertAllCamelCase', $bluekaidata );
	}

	// Remove excluded keys
	$bluekaidata = apply_filters( 'bluekai_removeAllExclusions', $bluekaidata );

	return $bluekaidata;
}

/*
 * Get the bluekai tag code, applying filters if necessary
 */
function getbluekaiTagCode() {
	global $bluekaitag;	
	$bluekaiSiteID = get_option( 'bluekaiSiteID' );
	$bluekaiLimit = get_option( 'bluekaiLimit' );		
	$bluekaidata = phintsObject(); // Grab data object
	
	if (( !empty( $bluekaiSiteID ) ) && ( !empty( $bluekaiLimit ) ) ) {

		$bluekaitag = "<!--­­ Begin BlueKai Tag ­­-->\n";
		$bluekaitag .= "<script type=\"text/javascript\">\n";
		$bluekaitag .= "window.bk_async = function() {\n";
		$bluekaitag .= "\n";
		$bluekaitag .= "	bk_allow_multiple_calls=true; bk_use_multiple_iframes=true;\n";
		$bluekaitag .= "\n";
		$bluekaitag .= "		// Declare Variables (phints)\n";


		

		foreach($bluekaidata as $key => $value) {

			// Convert arrays into strings
			if(is_array($value)){$value = implode('|',$value);}
				
			// If var is not empty, trigger it
			if(!empty($value)){
				$bluekaitag .= "		bk_addPageCtx('$key', '$value');\n";    		
			}
		}

		$bluekaitag .= "\n";
		$bluekaitag .= "	// Send Data\n";
		$bluekaitag .= "	BKTAG.doTag({$bluekaiSiteID},{$bluekaiLimit});\n";		
		$bluekaitag .= "};\n";
		$bluekaitag .= "(function() {\n";
		$bluekaitag .= "var scripts = document.getElementsByTagName('script')[0];\n";
		$bluekaitag .= "var s = document.createElement('script');\n";
		$bluekaitag .= "s.async = true;\n";
		$bluekaitag .= "s.src = '//tags.bkrtx.com/js/bk-coretag.js';\n";
		$bluekaitag .= "scripts.parentNode.insertBefore(s, scripts);\n";
		$bluekaitag .= "}());\n";
		$bluekaitag .= "</script>\n";
		$bluekaitag .= "<!--­­ End BlueKai Tag ­­-->\n";
					
	}	
	
	return $bluekaitag;
}

function outputbluekaiTagCode() {
	echo getbluekaiTagCode();
}

/*
 * Enable output buffer
 */
function outputBluekaiFilter( $template ) {
	ob_start();
	return $template;
}

/*
 * Used in combination with outputBluekaiFilter() to add bluekai tag after <body>
 */
function bluekaiTagBody( $bluekaiTagCode ) {
	$content = ob_get_clean();
	$bluekaiTagCode = getbluekaiTagCode();

	// Insert bluekai tag after body tag (sadly there is no wp_body hook)
	$content = preg_replace( '#<body([^>]*)>#i', "<body$1>\n\n{$bluekaiTagCode}", $content, 1 );
	echo $content;
}

/*
 * Used in combination with outputBluekaiFilter() to add bluekai tag after <head>
 */

/*
 * Determine where the bluekai tag should be located and insert it
 */
function insertbluekaiTag() {
	$bluekaiTagLocation = get_option( 'bluekaiTagLocation' );
	$bluekaiTagCode = getbluekaiTagCode();

	if ( !empty( $bluekaiTagCode ) ) {
		
			add_filter( 'template_include', 'outputBluekaiFilter', 1 );
			
			// Inject bluekai tag, output page contents
			add_filter( 'shutdown', 'bluekaiTagBody', 0 );		
	}
}	


if ( is_admin() ) {
	register_activation_hook( __FILE__, 'activate_bluekai' );
	register_deactivation_hook( __FILE__, 'deactive_bluekai' );
	add_action( 'admin_init', 'admin_init_bluekai' );
	add_action( 'admin_menu', 'admin_menu_bluekai' );
	add_action( 'admin_notices', 'admin_notices_bluekai' );
}

// Insert the bluekai tag
add_action( 'init', 'insertbluekaiTag' );

?>
