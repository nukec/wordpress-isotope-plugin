<?php
/* 
Plugin Name: FZ Isotope
Description: Custom implementation of Isotope to Wordpress
Author: Fourth Zone 
Version: 1.0 
Plugin URI: http://fourthzone.si
Author URI: http://fourthzone.si
*/


// settings for custom metabox
function fzproject_meta_box()
{
    add_meta_box('fzproject_meta_box', 'New portfolio item', 'display_fzproject_meta_box', 'fzproject_post', 'normal', 'high');
}

// html settings for custom meta_box
add_action('admin_init', 'fzproject_meta_box');
function display_fzproject_meta_box($item)
{
    // based on current item, we display selected data (type, source, divid, width, height)
    $source = esc_html(get_post_meta($item->ID, 'source', true));
    $content = get_post_meta($item->ID, 'about', true);

?>

<div style="float:left; width:50%;">


<div class="url postbox" style="float:left; width:100%;">
   <h3>Isotope item URL</h3>
   <div class="inside">
      <h4>This will be used for linking Isotope items with post or outside adress</h4>
      <font style="font-size:11px;">(Where to redirect, when you click Isotope item)</font>
      <input id="fzproject_source" onchange="preview('srcpreview');" type="text" size="80" name="fzproject_source" value="<?php
    echo $source; ?>" />
   </div>
</div>


<div style="clear:both;"></div>
<div class="thumb" style="float:left; width:100%;"></div>
<div style="clear:both;"></div>
<div class="cat" style="float:left; width:100%;"></div>
<div style="clear:both;"></div>

</div>


<div style="float:left; width:50%;">

<div class="postbox" style="float:left; width:100%;">
   <h3>About project</h3>
   <div class="inside">
<div class="about" style="float:left; width:100%;">
<?php wp_editor( $content, 'fzproject_about' ); ?>

</div>
   </div>
</div>

</div>


<div class="postbox" style="float:left; width:100%;">
   <h3>Content</h3>
   <div class="inside">
      <div class="editor"></div>
   </div>
</div>
<div style="clear:both;"></div>

	<?php
}

/* saves fields */
add_action('save_post', 'add_fzproject_fields', 10, 2);


/* function reads values and saves them */
function add_fzproject_fields($fzproject_id, $item)
{
    // check post type for slider
    if ($item->post_type == 'fzproject_post') {
        // store data in post meta table if present in post data
        if (isset($_POST['fzproject_source']) && $_POST['fzproject_source'] != '') {
            update_post_meta($fzproject_id, 'source', $_POST['fzproject_source']);
        }
	if (isset($_POST['fzproject_about']) && $_POST['fzproject_about'] != '') {
            update_post_meta($fzproject_id, 'about', $_POST['fzproject_about']);
        }
    }
}

add_filter('admin_head','ShowTinyMCE');
function ShowTinyMCE() {
	// conditions here
	wp_enqueue_script( 'common' );
	wp_enqueue_script( 'jquery-color' );
	wp_print_scripts('editor');
	if (function_exists('add_thickbox')) add_thickbox();
	wp_print_scripts('media-upload');
	if (function_exists('wp_tiny_mce')) wp_tiny_mce();
	wp_admin_css();
	wp_enqueue_script('utils');
	do_action("admin_print_styles-post-php");
	do_action('admin_print_styles');
}




// loading jQuery for back-end(adminko metalko)

function pw_load_scripts($hook) {

//load js only with specified hooks
if( !is_admin() && $hook != 'post.php' OR !is_admin() && $hook != 'post-new.php'  )
return;
// now check to see if the $post type is 'fzproject_post'
global $post;
if ( !isset($post) || 'fzproject_post' != $post->post_type )
return;

wp_register_script('fzproject_admin_script', plugins_url('admin.js', __FILE__));  
// enqueue  
wp_enqueue_script('jquery');  
wp_enqueue_script('fzproject_admin_script');

}
add_action('admin_enqueue_scripts', 'pw_load_scripts');

/* registering jQuery for front-end */
function fzproject_register_scripts()
{
    if (!is_admin()) {
        // register  
        wp_register_script('fzproject-script', plugins_url('js/jquery.isotope.min.js', __FILE__), array(
            'jquery'
        ));
        wp_register_script('fzproject-start', plugins_url('start.js', __FILE__));
        // enqueue  
        wp_enqueue_script('fzproject-script');
        wp_enqueue_script('fzproject-start');
    }
}

/* registering css on page */
function fzproject_register_styles()
{
    // register  
    //wp_register_style('zoneslider_styles', plugins_url('zoneslider/style.css', __FILE__));  
    wp_register_style('fzproject_styles_theme', plugins_url('css/style.css', __FILE__));
    // enqueue  
    //wp_enqueue_style('fzproject_styles');  
    wp_enqueue_style('fzproject_styles_theme');
}

function printTerms()
{

$terms = get_the_terms($post->ID, 'fzproject_categories');
        
	// looping slug for class
        foreach ($terms as $term) {
            $result .= $term->name;
	    
	    // not the last element
	    if(end($terms) !== $term){
		$result .= ', '; 
		}
        }

	return $result;
}

function printAbout()
{

$about = get_post_meta(get_the_ID(), 'about', true);

return $about;
}

add_action('admin_head', 'content_textarea_height');
function content_textarea_height() {
	echo'<style type="text/css">
			#fzproject_about{ height:500px; width:100% !important; }
		</style>';
}


// function used to print data on front-end page
function fzproject_function($type = 'fzproject_function')
{
    $args = array(
        'post_type' => 'fzproject_post',
        'posts_per_page' => 20
    );
    
    $result = '<section id="options" class="clearfix">';
    
    $terms = get_terms("fzproject_categories");
    $count = count($terms);
    
    if ($count > 0) {
        $result .= '<ul id="filters" class="option-set clearfix" data-option-key="filter">
		  <li><a href="#filter" data-option-value="*" class="selected">show all</a></li>';
        
        foreach ($terms as $term) {
            $result .= '<li><a href="#filter" data-option-value=".' . $term->slug . '">' . $term->name . '</a></li>';   
        }
        $result .= '</ul></section> <!-- #options -->';
    }
    
    $result .= '<div id="containerk" class="containersmall clearfix">';
    
    //the loop  
    $loop = new WP_Query($args);
    while ($loop->have_posts()) {
        $loop->the_post();
	
	// thumbnail url
        $the_url = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );
        
	// url to single post
	$source = get_post_meta(get_the_ID(), 'source', true);
		
	// get title of current post
        $title = get_post($post->ID)->post_title;

        $terms = get_the_terms($post->ID, 'fzproject_categories');
        
	// looping slug for class
        $result .= '<a href="' . $source . '"><div class="element ';
        foreach ($terms as $term) {
            $result .= ' ' . $term->slug;
        }
        
        $result .= '" data-symbol="Hg" data-category="';
        
	// looping slug for data category
        foreach ($terms as $term) {
            $result .= ' ' . $term->slug;
        }
        
        
        $result .= '" style="background-image:url(\'' . $the_url . '\')">

      <p class="weight">' . $title . '</p>
    </div></a>';
    }
    $result .= '</div> <!-- #container -->';
    return $result;
}

// function used to print data on front-end page
function fzisotope_function($type = 'fzisotope_function')
{
    $args = array(
        'post_type' => 'fzisotope_post',
        'posts_per_page' => 20
    );
    
    $result = '<section id="options" class="clearfix">';
    
    $terms = get_terms("fzisotope_categories");
    $count = count($terms);
    
    if ($count > 0) {
        $result .= '<ul id="filters" class="option-set clearfix" data-option-key="filter">
		  <li><a href="#filter" data-option-value="*" class="selected">show all</a></li>';
        
        foreach ($terms as $term) {
            $result .= '<li><a href="#filter" data-option-value=".' . $term->slug . '">' . $term->name . '</a></li>';   
        }
        $result .= '</ul></section> <!-- #options -->';
    }
    
    $result .= '<div id="containerk" class="containersmall clearfix">';
    
    //the loop  
    $loop = new WP_Query($args);
    while ($loop->have_posts()) {
        $loop->the_post();
	
	// thumbnail url
        $the_url = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );
        
	$source = get_post_meta(get_the_ID(), 'source', true);
	
	// get title of current post
        $title = get_post($post->ID)->post_title;

        $terms = get_the_terms($post->ID, 'fzisotope_categories');
        
	// looping slug for class
        $result .= '<a href="' . $source . '"><div class="element ';
        foreach ($terms as $term) {
            $result .= ' ' . $term->slug;
        }
        
        $result .= '" data-symbol="Hg" data-category="';
        
	// looping slug for data category
        foreach ($terms as $term) {
            $result .= ' ' . $term->slug;
        }
        
        
        $result .= '" style="background-image:url(\'' . $the_url . '\')">

      <p class="weight">' . $title . '</p>
    </div></a>';
    }
    $result .= '</div> <!-- #container -->';
    return $result;
}


//hook into the init action and call create_book_taxonomies when it fires
add_action('init', 'create_isotope_taxonomies', 0);

//create two taxonomies, genres and writers for the post type "book"
function create_isotope_taxonomies()
{
    // Add new taxonomy, NOT hierarchical (like tags)
    $labels = array(
        'name' => _x('Select Category', 'taxonomy general name'),
        'singular_name' => _x('Category', 'taxonomy singular name'),
        'search_items' => __('Search Categories'),
        'popular_items' => __('Popular Categories'),
        'all_items' => null,
        'parent_item' => null,
        'parent_item_colon' => null,
        'edit_item' => __('Edit'),
        'update_item' => __('Update'),
        'add_new_item' => __('Add New'),
        'new_item_name' => __('New Category'),
        'separate_items_with_commas' => __('Separate writers with commas'),
        'add_or_remove_items' => __('Add or remove categories'),
        'choose_from_most_used' => __('Choose from the most used categories'),
        'menu_name' => __('Categories')
    );
    
    register_taxonomy('fzproject_categories', 'fzproject_post', array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var' => true,
        'rewrite' => array(
            'slug' => 'fzproject_categories'
        )
    ));
}


// moving taxonomies box from side to normal
add_action('do_meta_boxes', 'fzproject_categories');
function fzproject_categories()
{
    remove_meta_box('fzproject_categoriesdiv', 'fzproject_post', 'side');
    add_meta_box('fzproject_categoriesdiv', 'Select Category', 'post_categories_meta_box', 'fzproject_post', 'normal', 'high', array(
        'taxonomy' => 'fzproject_categories'
    ));
    //print '<pre>';print_r( $wp_meta_boxes['post'] );print '<pre>';
}


// moving thumbnail box from side to normal
add_action('do_meta_boxes', 'customposttype_image_box');
function customposttype_image_box()
{
    remove_meta_box('postimagediv', 'fzproject_post', 'side');
    
    add_meta_box('postimagediv', __('Isotope item thumbnail'), 'post_thumbnail_meta_box', 'fzproject_post', 'normal', 'high');
    
}




/* main function */
function fzproject_init()
{
    // [np-shortcode] used for front-ent print
    add_shortcode('fzproject', 'fzproject_function');
    add_shortcode('fzisotope', 'fzisotope_function');
    add_shortcode('fzterms', 'printTerms');
    add_shortcode('fzabout', 'printAbout');
    
    //image size
    add_image_size('fzproject_function', 227, 130, true);
    
    $args = array(
        'public' => true,
        'label' => 'FZ Project',
        'supports' => array(
            'title',
	    'editor',
            'thumbnail'
        ),
        'menu_icon' => plugins_url('images/image.png', __FILE__),
        'has_archive' => true
    );
    
    
    register_post_type('fzproject_post', $args);
}


/* actions */



add_theme_support('post-thumbnails');
add_action('init', 'fzproject_init');
add_action('wp_print_scripts', 'fzproject_register_scripts');
add_action('wp_print_styles', 'fzproject_register_styles');


?>