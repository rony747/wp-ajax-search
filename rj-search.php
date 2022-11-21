<?php
/**
 * Plugin Name:       Rj Search
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Handle the basics with this plugin.
 * Version:           1.10.3
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            John Smith
 * Author URI:        https://author.example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       rjs
 * Domain Path:       /languages
 */

function rjs_load_textdomain(): void
{
  load_default_textdomain('rjs', false, dirname(__FILE__) . '/languages');
}
add_action('plugin_loaded', 'rjs_load_textdomain');


function rjs_plugin_scripts(): void
{
  wp_enqueue_style('rjs-main-style', plugin_dir_url(__FILE__) . 'assets/css/style.css');
  wp_enqueue_script('rjs-main-script', plugin_dir_url(__FILE__) . 'assets/js/main.js', ['jquery'], time(), true);
  $nonce_action = 'rjs_search_nonce';
  $rjs_nonce = wp_create_nonce($nonce_action);
  wp_localize_script('rjs-main-script', 'rjs',['ajax_url' => admin_url('admin-ajax.php'), 'rjs_nonce'=>$rjs_nonce]);
}
add_action('wp_enqueue_scripts', 'rjs_plugin_scripts');


function rjs_callback_function($attributes): string
{
  $default=[
  'post_type'=>'post'
  ];
  $atts = shortcode_atts($default, $attributes);

$search_html = <<<EOD
<form action="/" method="get">
	<input type="text" name="s" id="rjs_search"  />
	<input type="hidden" value="{$atts['post_type']}" name="post_type" id="rjs_post_type_field" />
	<input type="submit" id="searchsubmit" value="search" />
	<div id="rjs_search_result_area"></div>
</form>
EOD;
return $search_html;
}
add_shortcode('rjs-search', 'rjs_callback_function');

function rjs_search_ajax(){
  $rjs_user_text = $_POST['rjs_search_text'];
  $nonce  = $_POST[ 'rjs_nonce' ];
  $action = 'rjs_search_nonce';
  $rjs_pt = $_POST['rjs_post_type'];
  if ( wp_verify_nonce($nonce, $action) ) {
    global $wpdb;
    $sql ="SELECT post_title,ID FROM {$wpdb->prefix}posts WHERE post_type='{$rjs_pt}' AND post_status='publish' AND post_title LIKE '%$rjs_user_text%'" ;

$all_search = $wpdb->get_results($sql);
foreach ($all_search as $rjs_result){
  $rjs_post_permalink = get_permalink($rjs_result->ID);
  $rjs_post_title = $rjs_result->post_title;
  $rjs_result_html =<<<EOD
<a href="{$rjs_post_permalink}"> $rjs_post_title</a><br>
EOD;

 echo $rjs_result_html;
}


  } else {
    echo "You are not authorized";
  }
  die();
}

add_action('wp_ajax_nopriv_rjs_search_ajax','rjs_search_ajax');
add_action('wp_ajax_rjs_search_ajax','rjs_search_ajax');
