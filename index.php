<?php
/*
 * Plugin Name: Simple Quotes
 * Plugin URI: http://www.jasonernst.com/projects/quotes/
 * Description: Adds support for a custom post type called a quote. The easiest way to display a quote is to add a widget. Alternatively, you  may use the function <strong>quote_display_random()</strong> to display a random quote in your template.
 * 
 * template, or 
 * Version: 2.0
 * Author: Jason B. Ernst
 * Author URI: http://www.jasonernst.com/
 * License: GPL2
 */

/*  Copyright 2014  Jason Ernst  (email : jernst@uoguelph.ca)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action('init', 'quote_init'); 
add_action('save_post', 'quote_save');
register_activation_hook( __FILE__, 'quote_flush_rewrite' );
register_deactivation_hook( __FILE__, 'quote_flush_rewrite' );
register_activation_hook(__FILE__, 'quote_add_defaults');
register_uninstall_hook(__FILE__, 'quote_delete_plugin_options');
add_action('admin_menu', 'quote_create_menu');
add_action('admin_init', 'quote_admin_init');
add_action("widgets_init","quote_widget_init");

/*
 * Creates the new "quote" post type and registers it in wordpress
 */ 
function quote_init()
{
	//register the post type
	$labels = array(
		'name' 					=> _x('Quotes', 'post type general name', 'quote'),
		'singular_name' 		=> _x('Quote', 'post type singular name', 'quote'),
		'menu_name' 			=> _x('Quotes', 'admin menu', 'quote'),
		'name_admin_bar' 		=> _x('Quote', 'add new on admin bar', 'quote'),
		'add_new' 				=> _x('Add New', 'quote', 'quote'),
		'add_new_item' 			=> __('Add New Quote', 'quote'),
		'new_item'				=> __('New Quote', 'quote'),
		'edit_item' 			=> __('Edit Quote', 'quote'),
		'view_item'				=> __('View Quote', 'quote'),
		'all_items'				=> __('All Quotes', 'quote'),
		'search_items'			=> __('Search Quotes', 'quote'),
		'parent_item_colon' 	=> __('Parent Quotes:', 'quote'),
		'not_found' 			=> __('No quotes found', 'quote'),
		'not_found_in_trash'	=> __('No quotes found in Trash', 'quote'), 
	);
	$args = array(
		'labels' 				=> $labels,
		'public' 				=> false,
		'publicly_queryable' 	=> false,
		'show_ui' 				=> true, 
		'show_in_menu' 			=> true, 
		'query_var'				=> true,
		'rewrite' 				=> array('slug' => 'quote'),
		'capability_type'		=> 'post',
		'has_archive'			=> true, 
		'hierarchical'			=> false,
		'menu_position'			=> null,
		'supports'				=> array('title', 'editor'),
		'register_meta_box_cb'	=> 'quote_addfields',
	); 
	register_post_type('quote', $args);
	
	//register the taxonomy type (according to the WP-3.9 documentation this must be done to prevent problems with queries)
	$labels = array(
		'name'                       => _x( 'Quotes', 'taxonomy general name' ),
		'singular_name'              => _x( 'Quote', 'taxonomy singular name' ),
		'search_items'               => __( 'Search Quotes' ),
		'popular_items'              => __( 'Popular Quotes' ),
		'all_items'                  => __( 'All Quotes' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => __( 'Edit Quotes' ),
		'update_item'                => __( 'Update Quote' ),
		'add_new_item'               => __( 'Add New Quote' ),
		'new_item_name'              => __( 'New Quote Name' ),
		'separate_items_with_commas' => __( 'Separate quotes with commas' ),
		'add_or_remove_items'        => __( 'Add or remove quotes' ),
		'choose_from_most_used'      => __( 'Choose from the most used quotes' ),
		'not_found'                  => __( 'No quotes found.' ),
		'menu_name'                  => __( 'Quotes' ),
	);
	$args = array(
		'hierarchical'          => false,
		'labels'                => $labels,
		'show_ui'               => false,
		'show_admin_column'     => false,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'quote' ),
	);
	register_taxonomy( 'quote', 'quote', $args );
}
 
function quote_widget_init()
{
    register_widget("Quote_Widget");
}
 
/*
 * Force a refresh of the rewrite rules
 */
function quote_flush_rewrite()
{
	//flush re-write rules to ensure we don't get 404s
	global $wp_rewrite;
    $wp_rewrite->flush_rules();
}

/*
 * Sets up the default plugin values on activation
 */
function quote_add_defaults() {
	$tmp = get_option('quote_options');
    if((!is_array($tmp))) {
		delete_option('quote_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
		$arr = array(	"quote_italic" => "0",
						"quote_bold" => "0",
						"quote_align" => "right",
						"quote_width" => "500",
						"quote_color" => "000000",
						"author_disabled" => "0",
						"author_italic" => "0",
						"author_bold" => "0",
						"author_align" => "right",
						"date_disabled" => "0",
						"date_italic" => "0",
						"date_bold" => "0",
						"quote_css" => "",
		);
		update_option('quote_options', $arr);
	}
}

/*
 * Removes the plugin options on deactivate
 */
function quote_delete_plugin_options() {
	delete_option('quote_options');
}

/*
 * Adds extra submenus for the quote post type
 * and registers the options for the plugin
 */
function quote_create_menu()
{
	add_submenu_page('edit.php?post_type=quote', 'Options', 'Options', 'manage_options', 'quote-options', 'quote_options_form' );
}

/*
 * Registers the quote options settings
 */
function quote_admin_init()
{
	register_setting( 'quote_options', 'quote_options', 'quote_validate_options' );
}

/*
 * Validations for any text based settings so that HTML injection is not possible
 */
function quote_validate_options($input)
{
	$input['quote_width'] = wp_filter_nohtml_kses($input['quote_width']);
	$input['quote_colour'] = strtoupper(wp_filter_nohtml_kses($input['quote_colour']));
	$input['css'] = wp_filter_nohtml_kses($input['css']);
	if($input['quote_width'] < '100' || $input['quote_width'] > '2000')
		$input['quote_width'] = '500';
	if($input['quote_colour'] < '000000' || $input['quote_colour'] > 'FFFFFF')
		$input['quote_colour'] = '000000';
	return $input;
}

/*
 * This is what is displayed when the user clicks quotes->options
 * ref: http://codex.wordpress.org/Creating_Options_Pages
 * ref: http://ottodestruct.com/blog/2009/wordpress-settings-api-tutorial/
 * ref: http://www.presscoders.com/plugins/plugin-options-starter-kit/
 */
function quote_options_form()
{
	$base = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
	?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>Simple Quotes Options</h2>
		<p>Use this configuration page to set the look of the simple quote plugin on your wordpress website. You can see the changes reflected below in the sample quote.</p>
		<p><strong>Note</strong>: This configuration only works if you use the 'quote_display_random()' function call to show the quote. If you 'quote_random()' and style it yourself, you will not see these changes reflected. If you are using the widget, this should also work.</p>
		<form action="options.php" method="post">
			<?php settings_fields('quote_options'); ?>
			<?php $options = get_option('quote_options'); ?>
			
			<table class="form-table">
								
				<tr valign="top">
					<th scope="row"><img src="<?php echo $base?>/quote.png" alt="Quote"/> Quote Style</th>
					<td>
						<table>
							<tr>
								<td style="margin:0px; padding:2px;"><label><input name="quote_options[quote_italic]" type="checkbox" value="1" <?php if (isset($options['quote_italic'])) { checked('1', $options['quote_italic']); } ?> /> <em>Italic</em></label> &nbsp;</td>
								<td style="margin:0px; padding:2px;"><label><input name="quote_options[quote_bold]" type="checkbox" value="1" <?php if (isset($options['quote_bold'])) { checked('1', $options['quote_bold']); } ?> /> <strong>Bold</strong></label></td>
								<td style="margin:0px; padding:2px;">&nbsp;</td>
							</tr>
							<tr>
								<td style="margin:0px; padding:2px;"><label><input name="quote_options[quote_align]" type="radio" value="left" <?php checked('left', $options['quote_align']); ?> /> Left Aligned </label> &nbsp;</td>
								<td style="margin:0px; padding:2px;"><label><input name="quote_options[quote_align]" type="radio" value="right" <?php checked('right', $options['quote_align']); ?> /> Right Aligned </label></td>
								<td style="margin:0px; padding:2px;">&nbsp;</td>
							</tr>
							<tr>
								<td style="margin:0px; padding:2px;"><label>Maximum Width: </label></td>
								<td style="margin:0px; padding:2px;"><input style="padding:5px; width: 100px;" type="text" size="5" name="quote_options[quote_width]" value="<?php echo $options['quote_width']; ?>" /> </td>
								<td style="margin:0px; padding:2px;"><em>(in pixels between 100 and 2000)</em></td>
							</tr>
							<tr>
								<td style="margin:0px; padding:2px;"><label>Quote Colour: </label></td>
								<td style="margin:0px; padding:2px;"><input style="padding:5px; width: 100px;" type="text" size="7" name="quote_options[quote_colour]" value="<?php echo $options['quote_colour']; ?>" /></td>
								<td style="margin:0px; padding:2px;"><em>(Hex colour values between 000000 and FFFFFF - # not included)</em> <br/></td>
							</tr>
						</table>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><img src="<?php echo $base?>/author.png" alt="Quote Author"/> Author Style</th>
					<td>
						<table>
							<tr>
								<td style="margin:0px; padding:2px;"><label><input name="quote_options[author_disabled]" type="checkbox" value="1" <?php if (isset($options['author_disabled'])) { checked('1', $options['author_disabled']); } ?> /> Disabled</label></td>
								<td style="margin:0px; padding:2px;"><em>(prevents the author from being displayed)</em></td>
							</tr>
							<tr>
								<td style="margin:0px; padding:2px;"><label><input name="quote_options[author_italic]" type="checkbox" value="1" <?php if (isset($options['author_italic'])) { checked('1', $options['author_italic']); } ?> /> <em>Italic</em></label> &nbsp;</td>
								<td style="margin:0px; padding:2px;"><label><input name="quote_options[author_bold]" type="checkbox" value="1" <?php if (isset($options['author_bold'])) { checked('1', $options['author_bold']); } ?> /> <strong>Bold</strong></label></td>
							</tr>
						</table>						
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><img src="<?php echo $base?>/calendar.png" alt="Quote Date"/> Date Style</th>
					<td>
						<table>
							<tr>
								<td style="margin:0px; padding:2px;"><label><input name="quote_options[date_disabled]" type="checkbox" value="1" <?php if (isset($options['date_disabled'])) { checked('1', $options['date_disabled']); } ?> /> Disabled</label></td>
								<td style="margin:0px; padding:2px;"><em>(prevents the date from being displayed)</em></td>
							</tr>
							<tr>
								<td style="margin:0px; padding:2px;"><label><input name="quote_options[date_italic]" type="checkbox" value="1" <?php if (isset($options['date_italic'])) { checked('1', $options['date_italic']); } ?> /> Italic</label> &nbsp;</td>
								<td style="margin:0px; padding:2px;"><label><input name="quote_options[date_bold]" type="checkbox" value="1" <?php if (isset($options['date_bold'])) { checked('1', $options['date_bold']); } ?> /> Bold</label></td>
							</tr>
						</table>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><img src="<?php echo $base?>/align.png" alt="Alignment"/> Author / Date Alignment</th>
					<td>
						<label><input name="quote_options[author_align]" type="radio" value="left" <?php checked('left', $options['author_align']); ?> /> Left Aligned </label> &nbsp;
						<label><input name="quote_options[author_align]" type="radio" value="right" <?php checked('right', $options['author_align']); ?> /> Right Aligned </label><br />
					</td>
				</tr>
				
				<tr>
					<th scope="row">Advanced - CSS</th>
					<td>
						<table>
							<tr>
								<td><textarea name="quote_options[css]" rows="7" cols="50" type='textarea'><?php echo $options['css']; ?></textarea></td>
								<td><div style="color:#666666;margin-left:2px; width:425px; text-align: justify;">This style will be applied to the 'quote' class (the entire quote). For example '<strong>text-decoration:underline;</strong>' would underline all of the text. Similarly, '<strong>background-color: #000000;</strong>' would create a black background. This feature may also be useful if you find you need to align the quote using 'margin-top', 'margin-bottom' etc. <strong>Note: </strong> Make sure you separate each css statement with a colon.</div></td>
							</tr>
						</table>						
					</td>
				</tr>
			</table>
		
		<h2>Sample Quote Appearance:</h2>
		<?php if(isset($options['quote_width'])) { $width = "width:".$options['quote_width']."px; "; } ?>
		<?php if(isset($options['quote_align'])) { $quote_align = "text-align:".$options['quote_align']."; "; $quote_float = "float:".$options['quote_align']."; "; } ?>
		<?php if(isset($options['author_align'])) { $author_align = "float:".$options['author_align']."; text-align:".$options['author_align'].";"; } ?>
		<?php if(isset($options['quote_colour'])) { $quote_colour = "color:#".$options['quote_colour'].";"; } ?>
		<?php if(isset($options['css'])) { $quote_css = $options['css']; } ?>
		<div style="<?php echo $width; ?><?php echo $quote_align; ?><?php echo $quote_colour; ?><?php echo $quote_float; ?><?php echo $quote_css; ?>">
			"<?php if(isset($options['quote_italic']) && $options['quote_italic']=='1' ) { echo "<em>"; } ?><?php if(isset($options['quote_bold']) && $options['quote_bold']=='1' ) { echo "<strong>"; } ?>I have decided to stick with love. Hate is too great a burden to bear.<?php if(isset($options['quote_bold']) && $options['quote_bold']=='1' ) { echo "</strong>"; } ?><?php if(isset($options['quote_italic']) && $options['quote_italic']=='1') { echo "</em>"; } ?>" <br/>
			<div style="<?php echo $author_align; ?>"><?php if(!isset($options['author_disabled'])) { ?><?php if(isset($options['author_italic']) && $options['author_italic']=='1' ) { echo "<em>"; } ?><?php if(isset($options['author_bold']) && $options['author_bold']=='1' ) { echo "<strong>"; } ?>Martin Luther King Jr.<?php if(isset($options['author_bold']) && $options['author_bold']=='1' ) { echo "</strong>"; } ?><?php if(isset($options['author_italic']) && $options['author_italic']=='1' ) { echo "</em>"; } ?><?php } ?> <?php if(!isset($options['author_disabled']) && !isset($options['date_disabled'])) { echo " - ";  }?> <?php if(!isset($options['date_disabled'])) { ?><?php if(isset($options['date_italic']) && $options['date_italic']=='1' ) { echo "<em>"; } ?><?php if(isset($options['date_bold']) && $options['date_bold']=='1' ) { echo "<strong>"; } ?>1967<?php if(isset($options['date_bold']) && $options['date_bold']=='1' ) { echo "</strong>"; } ?><?php if(isset($options['date_italic']) && $options['date_italic']=='1' ) { echo "</em>"; } ?><?php } ?></div>
		</div>
		
		<div class="clear">&nbsp;</div>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
		</form>
		
	</div>
	<?php
}

/*
 * Adds the author field using meta boxes
 */
function quote_addfields()
{
	add_meta_box('quote-meta','Quote Information','quote_meta','quote','normal','high');
	do_meta_boxes('quote-meta','normal',null);
}

/*
 * Adds the html to the admin page for the quote author
 */
function quote_meta()
{
	//used to get to the plugin folder
	$base = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
	global $post;
	$quote_author = get_post_meta($post->ID, 'quote_author', true);
	$quote_date = get_post_meta($post->ID, 'quote_date', true);
	
	//create a nonce for security
	echo '<input type="hidden" name="quote_noncename" id="quote_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
	?>
		<div class="inside">
			<div class="form-field">
				<label for="quote_author"><img src="<?php echo $base; ?>/author.png" alt="Quote Author"/> Quote Author: </label>
				<input type="text" name="quote_author" tabindex="3" style="width: 100%;" value="<?php echo $quote_author; ?>"/>
				
				<div style="margin-bottom:10px;">&nbsp;</div>
				
				<label for="quote_date"><img src="<?php echo $base; ?>/calendar.png" alt="Quote Date"/> Quote Date: </label>
				<input type="text" name="quote_date" tabindex="3" style="width: 100%;" value="<?php echo $quote_date; ?>"/>
				<p>All of the fields here except the quote itself is optional, you may use as much of the information in your template as you wish.</p>
			</div> <!-- /form-field -->
		</div> <!-- /inside -->
	<?php
}

/*
 * Called on a post save, used to save the quote author using metadata
 */
function quote_save($post_id)
{	
	if ( !wp_verify_nonce( $_POST['quote_noncename'], plugin_basename(__FILE__) ))
		return $post_id;
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
		return $post_id;
			
	// Check permissions
	if ( !current_user_can( 'edit_posts', $post_id ) )
		return $post_id;
	
	$quote_author = $_POST['quote_author'];
	$quote_date = $_POST['quote_date'];
	update_post_meta($post_id, 'quote_author', $quote_author);
	update_post_meta($post_id, 'quote_date', $quote_date);
	return $post_id;
}

/*
 * Returns a random quote using an associative array
 * quote["title"] is the title of the quote
 * quote["content"] is the quote itself
 * quote["author"] is the author
 * quote["date"] is the date of the quote
 */
function quote_random()
{
	query_posts('orderby=rand&post_type=quote');
	if(have_posts())
	{
		the_post();
		global $post;
		$quote["title"] = get_the_title($post_>ID);
		$quote["content"] = get_the_content();
		if($quote["content"] == "")
			$quote["content"] = $quote["title"];
		$quote["author"] = get_post_meta($post->ID, 'quote_author', true );
		$quote["date"] = get_post_meta($post->ID, 'quote_date', true );
		wp_reset_query();
		return $quote;
	}
}

/*
 * Outputs a styled quote using the default style
 * Use quote_random() if you would like to style the
 * quote yourself
 * Note: in the default style, the title is not used
 */
function quote_display_random()
{
	$options = get_option('quote_options');
	$quote = quote_random();
	
	?>
	<?php if(isset($options['quote_width'])) { $width = "width:".$options['quote_width']."px; "; } ?>
	<?php if(isset($options['quote_align'])) { $quote_align = "text-align:".$options['quote_align']."; "; $quote_float = "float:".$options['quote_align']."; "; } ?>
	<?php if(isset($options['author_align'])) { $author_align = "float:".$options['author_align']."; text-align:".$options['author_align'].";"; } ?>
	<?php if(isset($options['quote_colour'])) { $quote_colour = "color:#".$options['quote_colour'].";"; } ?>
	<?php if(isset($options['css'])) { $quote_css = $options['css']; } ?>
	<div style="<?php echo $width; ?><?php echo $quote_align; ?><?php echo $quote_colour; ?><?php echo $quote_float; ?><?php echo $quote_css; ?>" class="quote">
		"<?php if(isset($options['quote_italic']) && $options['quote_italic']=='1' ) { echo "<em>"; } ?><?php if(isset($options['quote_bold']) && $options['quote_bold']=='1' ) { echo "<strong>"; } ?><?php echo $quote['content']; ?><?php if(isset($options['quote_bold']) && $options['quote_bold']=='1' ) { echo "</strong>"; } ?><?php if(isset($options['quote_italic']) && $options['quote_italic']=='1') { echo "</em>"; } ?>" <br/>
		<div style="<?php echo $author_align; ?>"><?php if(!isset($options['author_disabled'])) { ?><?php if(isset($options['author_italic']) && $options['author_italic']=='1' ) { echo "<em>"; } ?><?php if(isset($options['author_bold']) && $options['author_bold']=='1' ) { echo "<strong>"; } ?><?php echo $quote['author']; ?><?php if(isset($options['author_bold']) && $options['author_bold']=='1' ) { echo "</strong>"; } ?><?php if(isset($options['author_italic']) && $options['author_italic']=='1' ) { echo "</em>"; } ?><?php } ?> <?php if(!isset($options['author_disabled']) && !isset($options['date_disabled'])) { echo " - ";  }?> <?php if(!isset($options['date_disabled'])) { ?><?php if(isset($options['date_italic']) && $options['date_italic']=='1' ) { echo "<em>"; } ?><?php if(isset($options['date_bold']) && $options['date_bold']=='1' ) { echo "<strong>"; } ?><?php echo $quote['date']; ?><?php if(isset($options['date_bold']) && $options['date_bold']=='1' ) { echo "</strong>"; } ?><?php if(isset($options['date_italic']) && $options['date_italic']=='1' ) { echo "</em>"; } ?><?php } ?></div>
	</div>
	<?php
}

class Quote_Widget extends WP_Widget
{
	function Quote_Widget()
	{
		$widget_options = array('classname'=>'widget-quote','description'=>__('This widget shows a random quote.'));
		$control_options = array('height'=>300,'width' =>300);
		$this->WP_Widget('quote_widget','Quote Widget',$widget_options,$control_options);
	}
	
	function widget($args, $instance)
	{
		extract($args,EXTR_SKIP);
		$title =  ($instance['title'])?$instance['title']:"Random Quote";
		echo $before_widget;
		echo $before_title
		?><h3 class="widget-title"><?php echo $title; ?></h3><?php
		echo $after_title;
		quote_display_random();
		echo $after_widget;
	}
	
	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$instance["title"] = $new_instance["title"];
		return $instance;
	}
	
	function form($instance)
	{
		?>
		<label for="<?php echo $this->get_field_id("title"); ?>">
		<p>Title: <input type="text"  value="<?php echo $instance['title']; ?>" name="<?php echo $this->get_field_name("title"); ?>" id="<?php echo $this->get_field_id("title"); ?>"></p>
		</label>
		<?php
	}
}
?>
