<?php
// Create a dropdown field
function selectList( $id, $options, $multiple = false ) {
	$opt    = get_option( $id );
	$output = '<select class="select" name="' . $id . '" id="' . $id . '">';
	foreach ( $options as $val => $name ) {
		$sel = '';
		if ( $opt == $val )
			$sel = ' selected="selected"';
		if ( $name == '' )
			$name = $val;
		$output .= '<option value="' . $val . '"' . $sel . '>' . $name . '</option>';
	}
	$output .= '</select>';
	return $output;
}

function generatePhintsList() {
	$output = '';

	// Array of basic data sources
	$basicLayer = array(
		"site_name" => "Contains the site's name",
		"site_description" => "Contains the site's description",
		"post_category" => "Contains the post's category, e.g. 'technology'",
		"post_tags" => "Contains the post tags, e.g. 'tag management'",
		"page_type" => "Contains the page type, e.g. 'archive', 'homepage', or 'search'",
		"post_title" => "Contains the post's title",
		"post_author" => "Contains the post author",
		"post_date" => "Contains the post date",
		"search_query" => "Contains the search query conducted by user",
		"search_results" => "Contains the number of search results returned"
	);

	$basicLayer = apply_filters( 'bluekai_convertAllCamelCase', $basicLayer );

	// Remove excluded keys
	$basicLayer = apply_filters( 'bluekai_removeAllExclusions', $basicLayer );

	if ( $basicLayer ) {
		foreach ( $basicLayer as $key => $value ) {
			$output .= $key . ', "'. $value .'"&#13;&#10;';
		}
	}

	global $wpdb;
	$metaKeys = $wpdb->get_results( "SELECT DISTINCT(meta_key) FROM {$wpdb->postmeta} ORDER BY meta_id DESC" );

	$bulkString = ', "Imported from Wordpress"&#13;&#10;';

	
	if ( $metaKeys ) {
		foreach ( $metaKeys as $metaKey ) {

			// Exclude meta keys with invalid characters
			if ( !preg_match( '/[^a-zA-Z0-9_$.]/', $metaKey->meta_key ) ) {
				$output .= $metaKey->meta_key . $bulkString;
			}
		}	
	}

	return $output;
}

?>

<div class="wrap">
	<div class="bluekai-icon">
		<h2><?php _e( ' BlueKai (Unofficial) Settings', 'bluekai' ); ?></h2>
	</div>

	<?php 
	$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'basic_settings'; 
	?>

	<h2 class="nav-tab-wrapper">
    	<a href="?page=bluekai&tab=basic_settings" class="nav-tab <?php echo $active_tab == 'basic_settings' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Basic Settings', 'bluekai' ); ?></a>
    	<a href="?page=bluekai&tab=data_export" class="nav-tab <?php echo $active_tab == 'data_export' ? 'nav-tab-active' : ''; ?>"><?php _e( 'List of all Phints', 'bluekai' ); ?></a>

	</h2>

	<?php
	if( $active_tab == 'basic_settings' ) {
		?>
		<form method="post" action="options.php">
			<?php wp_nonce_field( 'update-options' ); ?>
			<?php settings_fields( 'bluekaiTagBasic' ); ?>
			
			<p>
				<p>
					<?php _e( 'Site/Container ID:', 'bluekai' ); ?>
					<br />
					<input name='bluekaiSiteID' size='30' type='text' value='<?php echo get_option( 'bluekaiSiteID' ); ?>' />
					<br />
					<small><?php _e( 'i.e. the <i>12345</i> in BKTAG.doTag(12345, 4);', 'bluekai' ); ?></small>
				</p>
				<p>
					<?php _e( 'Limit:', 'bluekai' ); ?>
					<br />
					<input name='bluekaiLimit' size='30' type='text' value='<?php echo get_option( 'bluekaiLimit' ); ?>' />
					<br />
					<small><?php _e( 'i.e. the <i>4</i> in BKTAG.doTag(12345, 4);', 'bluekai' ); ?></small>
				</p>				
			</p>
			<p>
				<?php _e( 'Keys to exclude from phints:', 'bluekai' ); ?>
				<br />
				<input name='bluekaiExclusions' size='50' type='text' value='<?php echo get_option( 'bluekaiExclusions' ); ?>' />
				<br />
				<small><?php _e( 'Comma separated list - <i>page_type, site_name</i>.', 'bluekai' ); ?></small>
			</p>
			<input type="hidden" name="action" value="update" />

			<p class="submit"><input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'bluekai' ); ?>" /></p>
		</form>
	<?php
	}
	else {
		?>
		<p>
			<?php _e( 'Bulk export of all phints. ', 'bluekai' ); ?>
			<p>
				<textarea readonly="readonly" name="csvExport" rows="20" cols="90"><?php echo generatePhintsList() ?></textarea>
			</p>
		</p>
		<?php
	}
	?>
</div>