<?php
/***
* Plugin Name: Custom Post
* Description: Plugin to Create Custom Post.
* Version: 1.0
* Author: Syed Amir Hussain
***/
if ( ! defined('ABSPATH') ) {
	die('Please do not load this file directly.');
}
define('plugin_name', 'Custom-Post');
if(!class_exists('Custom_Post')) {	
	class Custom_Post {
		function __construct() {
			if(is_admin()) {
				// hook for adding admin menus
				add_action( 'admin_menu', array( &$this, 'sy_add_pages' ) );
				// hook to load custom posts
				add_action( 'init', array( &$this, 'sy_load_custom_post' ) );
				// hook to handle de-activation
				register_deactivation_hook( __FILE__, array( &$this, 'sy_deactivate' ) );
			}
		}
		function sy_deactivate(){
			delete_option( 'sy_cust_post_option' );
		}
		function sy_add_pages() {
			// add a new top-level menu
			add_menu_page('Custom Post', 'Custom Post', 'manage_options', 'sy-custom-post', array( &$this, 'sy_echo_page' ) );
		}
		function sy_echo_page(){
			$option = '<style>
						.tab tr td{ line-height:25px; font-size:13px; }
						.short{ width:40px; }
						#nanMsg, #noTitle{ display:none; color:red;}
						.borderRed{ border-color:#ff0000!important; }
						</style>
						<h2>Custom Post</h2>
					  <form name="sy_cust_post_frm" method="POST" id="sy_cust_post_frm" onsubmit="return validate( this );">
					  <table class="tab">
					  	<tr><td><label for="sy_cust_post_title">Title :</label></td><td><input type="text" name="sy_cust_post_title" id="sy_cust_post_title" /></td><td><span id="noTitle">&nbsp;Please enter post title.</span></td></tr>
						<tr><td><label for="sy_cust_post_seq">Sequence :</label></td><td><input type="text" class="short" name="sy_cust_post_seq" id="sy_cust_post_seq" /></td><td><span id="nanMsg">&nbsp;Please enter valid number.</span></td></tr>
						<tr><td><label for="sy_cust_post_thumb">Thumbnail :</label></td><td colspan="2"><input type="checkbox" name="sy_cust_post_opt[]" value="thumbnail" id="sy_cust_post_thumb" /></td></tr>
						<tr><td><label for="sy_cust_post_exc">Excerpt :</label></td><td colspan="2"><input type="checkbox" name="sy_cust_post_opt[]" value="excerpt" id="sy_cust_post_exc" /></td></tr>
						<tr><td><label for="sy_cust_post_cmt">Comments :</label></td><td colspan="2"><input type="checkbox" name="sy_cust_post_opt[]" value="comments" id="sy_cust_post_cmt" /></td></tr>
						<tr><td colspan="3"><input type="submit" value="Submit" name="sy_act" class="button" /></td></tr>
					  </table>
					  </form>
						<script type="text/javascript">
						//<![CDATA[
						var jq = jQuery.noConflict();
						function validate( obj ){
							jq("#noTitle, #nanMsg").hide();
							jq("#sy_cust_post_title , #sy_cust_post_title").removeClass("borderRed");
							if( jq("#sy_cust_post_title").val() == "" ){
								jq("#noTitle").show();
								jq("#sy_cust_post_title").addClass("borderRed");
								return false;
							}
							if( !jq.isNumeric( jq("#sy_cust_post_seq").val() ) ){
								jq("#nanMsg").show();
								jq("#sy_cust_post_seq").addClass("borderRed");
								return false;
							}
							return true;
						}
						// ]]>
						</script>';
			echo $option;
		}
		function sy_create_custom_post(){
			$sy_cust_post_opt = 'foo:foo';
			if( !empty( $_POST['sy_cust_post_opt'] ) ){
				$sy_cust_post_opt = array();
				foreach( $_POST['sy_cust_post_opt'] as $opt ){
					array_push( $sy_cust_post_opt, $opt.':on' );
				}
				$sy_cust_post_opt = implode( ';', $sy_cust_post_opt );
			}
			$sy_cust_post = '{labels:'.$_POST['sy_cust_post_title'].';'.$sy_cust_post_opt.';menu_position:'.$_POST['sy_cust_post_seq'].'}';
			
			$sy_cust_post_opt_arr = array();
			$sy_cust_post_opt_arr = array_filter( explode( ',', get_option('sy_cust_post_option') ) );
			array_push( $sy_cust_post_opt_arr, $sy_cust_post );
			$sy_cust_post_opt_arr = $this->array_sort( $sy_cust_post_opt_arr );
			$sy_cust_post_opt_str = implode( ',', $sy_cust_post_opt_arr );
			update_option( 'sy_cust_post_option', $sy_cust_post_opt_str );
		}
		function array_sort( $arr = array() ){
			$array = array();
			foreach( $arr as $opt ){
				$opt_array = explode( ';', str_replace( array('{', '}'), '', $opt ) );
				$index = count($opt_array) - 1;
				list( $foo, $seq ) = explode( ':', $opt_array[$index] );
				$array["$seq"] = $opt;
			}
			ksort( $array );
			return $array;
		}
		function sy_load_custom_post( $title = "" ){
			$sy_cust_post_opt_arr = explode( ',', get_option('sy_cust_post_option') );
			$errors = array_filter($sy_cust_post_opt_arr);
			if( !empty( $errors ) ){
				foreach( $sy_cust_post_opt_arr as $cust_post ){
					$cust_post = str_replace( array('{', '}'), '', $cust_post );
					$cust_post_array = explode( ';', $cust_post );
					$errors_ = array_filter($cust_post_array);
					if( !empty( $errors_ ) ){
						$label = array();	$menu_position = '';	$supports = '';		$name = '';
						foreach( $cust_post_array as $opt ){
							list( $key, $val ) = explode( ':', $opt );
							switch( $key ){
								case 'labels':
									$name = strtolower(str_replace(' ', '-', $val));
									$labels = array(
										'name' => __( $val ),
										'singular_name' => __( $val )
									);
								break;
								case 'menu_position':
									$menu_position = $val;
								break;
								case 'thumbnail':
								case 'excerpt':
								case 'comments':
									$supports .= "'$key', ";
								break;
							}
						}
					}
					$supports = substr( $supports, 0, strlen($supports)-2 );
					eval( '$support_array'." = array( 'title', 'editor', $supports );" );
					$args = array(
						'labels'        => $labels,
						'public'        => true,
						'menu_position' => $menu_position,
						'supports'      => $support_array,
						'has_archive'   => true,
					);
					register_post_type( $name, $args );
				}
			}
		}
	}
}
$wpCustPost = new Custom_Post();
if( 'Submit' == $_POST['sy_act'] ){
	$wpCustPost->sy_create_custom_post();
}
?>