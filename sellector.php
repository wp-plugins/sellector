<?php
/*
Plugin Name: Sellector
Version: 0.0.1
Plugin URI: http://http://www.easeofchoice.com/plugins/wordpress-sellector/
Description: Tool to conveniently customize and integrate a Sellector data feed into any wordpress website.
Author: Sellector GmbH
Author URI: http://www.sellector.com/
License: GPLv2
*/


// -------------------------------
// 1. Defining and storing sellectors - Custom Post Type
// -------------------------------

// Define sellector post_type

function sellector_post() {
    $labels = array(
		'menu_name' => _x('Sellector', 'sellector'),
    );
    $args = array(
       'labels' => $labels,
       'hierarchical' => true,
       'description' => 'Sellector',
       'supports' => array('title', 'thumbnail'),
       'public' => false,
       'show_ui' => true,
       'show_in_menu' => true,
       'show_in_nav_menus' => true,
       'publicly_queryable' => true,
       'exclude_from_search' => false,
       'has_archive' => true,
       'query_var' => true,
       'can_export' => true,
       'rewrite' => true,
       'capability_type' => 'post'
    );
    register_post_type('sellector', $args);
}
add_action('init', 'sellector_post');

/*//Sellector taxonomy
register_taxonomy("sellectors", array("sellector"), array(
	"hierarchical" => false,
	"label" => "Sellectors",
	"singular_label" => "Sellector",
	"rewrite" => true
));
*/

// Add cafe custom fields to post_type
function add_sellector_meta_boxes() {
	add_meta_box("sellector_meta", "Sellector Configuration", "add_sellector_meta_box", "sellector", "normal", "low");
}
function add_sellector_meta_box()
{
	global $post;
	$custom = get_post_custom( $post->ID );
	?>

	<style>.width99 {width:99%;}</style>

	<h2><?php _e('Edit') ?> Sellector<span style="font-size: small"></span></h2>
	<p>
		<label>Sellecor ID:</label><br />
		<input type="text" name="sid" id="sid" value="<?= @$custom["sid"][0] ?>"/>
	</p>
	<p>
		<label>Template Code:</label><br/><span style="font-size: smaller">(Use the Wizard below to initially generate the template code. If required you can later modify it here.)</span><br />
		<textarea rows="10" name="template" id="template" class="width99"><?= @$custom["template"][0] ?></textarea>
	</p>
	<input name="selectBoxIds" id="selectBoxIds" type="hidden" value="<?= @$custom["selectBoxIds"][0] ?>"/>

	<div style="margin-left: -12px; margin-right:-12px;height:2em; background-color:#eee"></div>

	<link href="/sellectorng/css/wizard.css" rel="stylesheet" type="text/css" />
	<link href='http://fonts.googleapis.com/css?family=Permanent+Marker' rel='stylesheet' type='text/css'/>
	<link href='http://fonts.googleapis.com/css?family=Titillium+Web:600italic,700italic' rel='stylesheet' type='text/css'/>
	<script src="http://www.easeofchoice.com/sellectorng/js/wizard/pageLoader.js" type="text/javascript"></script>
	<script type="text/javascript">var imageBase="http://www.easeofchoice.com/sellectorng/images/wizard/"</script>
	<script src="http://www.easeofchoice.com/sellectorng/js/wizard/wizard.js" type="text/javascript"></script>
	<script src="http://www.easeofchoice.com/sellectorng/js/wizard/sliderDesignSet.js" type="text/javascript"></script>
	<script src="http://www.sellector.com/scripts/sellectorngControl.js" type="text/javascript"></script>
	<h2>Configuration Wizard</h2>
	<div id="sellectorWizard"></div>
	<script type="text/javascript">
	  pageLoader.fetchHTMLContent("http://www.easeofchoice.com/sellectorng/wizard/container", "sellectorWizard", wizard.initialize);
	</script>
	<br/>
	<input type="button" class="button-secondary" value="Overwrite Template Code with Configurator result" onclick="jQuery('#template').val('<script type=\'text/javascript\'>\n' + wizard.generateTemplateSnippet(5) + '\n</script>');jQuery('#sid').val(wizard.sellectorId);"/>
	<!-- add grabber for multiple sellector input boxes at the end of the above line format: divID|$widthpx divID2|$width2px ...-->
	<?php
}

// Save custom field data when creating/updating posts
function save_sellector_fields(){
  global $post;
 
  if ( $post )
  {
    update_post_meta($post->ID, "sid", @$_POST["sid"]);
    update_post_meta($post->ID, "template", @$_POST["template"]);
	$selectBoxIds = "sel_selectbox";
	if (@$_POST["selectBoxIds"] != ""){
		$selectBoxIds = @$_POST["selectBoxIds"];
	}
    update_post_meta($post->ID, "selectBoxIds", $selectBoxIds);
  }
}
add_action( 'admin_init', 'add_sellector_meta_boxes' );
add_action( 'save_post', 'save_sellector_fields' );




// -------------------------------
// 2. Using sellectors in Posts and Pages - Editor
// -------------------------------

// TinyMCE-Button to add all necessary sellector shortcodes

add_action('admin_head', 'sellector_register_buttons');

function sellector_register_buttons() {
    global $typenow;
    // check user permissions
    if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
		return;
    }
    // verify the post type
    if( ! in_array( $typenow, array( 'post', 'page' ) ) )
        return;
    // check if WYSIWYG is enabled
    if ( true || get_user_option('rich_editing') == 'true') {
        add_filter("mce_external_plugins", "sellector_add_tinymce_plugin");
        add_filter('mce_buttons', 'sellector_register_add_button');
    }
}

function sellector_add_tinymce_plugin($plugin_array) {
    $plugin_array['sellector_button'] = plugins_url( '/sellector_tinymce_button.js', __FILE__ ); // CHANGE THE BUTTON SCRIPT HERE
    return $plugin_array;
}

function sellector_register_add_button($buttons) {
   array_push($buttons, "sellector_button");
   return $buttons;
}

// Enable Button ICON

function sellector_button_css() {
    wp_enqueue_style('sellector-button', plugins_url('/style.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'sellector_button_css');

// Add function to query posts through AJAX (when displaying list box in editor dialog)
function get_sellector_posts() {
    $args = array(
        'post_type' => 'sellector'
        //'posts_per_page' => 10
    );
    //the loop
    $res = array();
	$loop = new WP_Query($args);
    while ($loop->have_posts()) {
        $loop->the_post();
		$id = get_the_id();
        $customFields = get_post_custom($id);	//$post->ID
		$sellectorValues = array_merge($customFields, array('spi' => array($id)));
		$item = array('text' => get_the_title(), 'value' => $sellectorValues);//array('spi' => array($id)));
		array_push($res, $item);
    };
/*	$value1 = array('sid' => '108', 'template' => 'script');
	$item2 = array('text' => 'Sellector 2', 'value' => $customFields);
	$item1 = array('text' => 'Sellector 1', 'value' => $value1);
	$res = array($item1, $item2);*/
	die(json_encode($res));
} 
add_action( 'wp_ajax_get_sellector_posts', 'get_sellector_posts' );




// -------------------------------
// 3. Inject the sellector when showing the post or page
// -------------------------------

// Shortcodes
// [sellector_selectbox]
function sellector_selectbox_shortcode_translator( $atts ) {
    $a = shortcode_atts( array(
		'width' => '19%',
        'divid' => 'sel_selectbox'
    ), $atts );
	
	$selectBox = "<div id='{$a['divid']}' style='width:{$a['width']}; display:inline-block; vertical-align:top'></div>";

    return $selectBox;
}
add_shortcode( 'sellector_selectbox', 'sellector_selectbox_shortcode_translator' );

// [sellector_resultbox]
function sellector_resultbox_shortcode_translator( $atts ) {
    // retrieve params from shortcode
	$a = shortcode_atts( array(
		'spi' => '',
		'width' => '80%',
		'height' => '800px'
    ), $atts );

	$selboxid = 'sel_selectbox';		// In case there is only one input box
	$resboxid = 'sel_resultbox';
	$markup = "";
	// If sellectorPostID could be found
	if($a['spi']){
		$custom = get_post_custom($a['spi']);	//$post->ID
		$sid = @$custom['sid'][0];
		if($sid){
			$feature = 'sellectorFeature';
			$sellectorScript = "<script type='text/javascript' src='http://www.sellector.com/scripts/sellectorControl.js'></script>";
			$sellectorTemplate = preg_replace('/'.$feature.'\s*=\s*{/', $feature.' = {"noLabel":true,', @$custom['template'][0]);
			$sellectorLaunchCode = "<script type='text/javascript'>"
								   . "document.body[window.addEventListener ? 'addEventListener' : 'attachEvent'](window.addEventListener ? 'load' : 'onload', new function(){sellectorLoader( {$sid}, '{$selboxid}', '{$resboxid}')}, false)"
								 . "</script>";
			$resultBox = "<div id='{$resboxid}' style='width:{$a['width']};height:{$a['height']}; display:inline-block; vertical-align:top'></div>";
			$markup = $sellectorScript . $sellectorTemplate . $resultBox . $sellectorLaunchCode;
		}
		else{
			$markup = "<p>The referenced Sellector content is no longer available.<br/>Please refer to the site administrator.</p>";
		}
	}
    return $markup;
}
add_shortcode( 'sellector_resultbox', 'sellector_resultbox_shortcode_translator' );


?>
