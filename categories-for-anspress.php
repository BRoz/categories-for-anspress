<?php
/**
 * Categories extension for AnsPress
 *
 * AnsPress - Question and answer plugin for WordPress
 *
 * @package   Categories for AnsPress
 * @author    Rahul Aryan <support@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://wp3.in/categories-for-anspress
 * @copyright 2014 WP3.in & Rahul Aryan
 *
 * @wordpress-plugin
 * Plugin Name:       Categories for AnsPress
 * Plugin URI:        http://wp3.in/categories-for-anspress
 * Description:       The most advance community question and answer system for WordPress
 * Donate link: https://www.paypal.com/cgi-bin/webscr?business=rah12@live.com&cmd=_xclick&item_name=Donation%20to%20AnsPress%20development
 * Version:           1.0
 * Author:            Rahul Aryan
 * Author URI:        http://wp3.in
 * Text Domain:       ap
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}


class Categories_For_AnsPress
{

    /**
     * Class instance
     * @var object
     * @since 1.0
     */
    private static $instance;


    /**
     * Get active object instance
     *
     * @since 1.0
     *
     * @access public
     * @static
     * @return object
     */
    public static function get_instance() {

        if ( ! self::$instance )
            self::$instance = new Categories_For_AnsPress();

        return self::$instance;
    }
    /**
     * Initialize the class
     * @since 2.0
     */
    public function __construct()
    {
        //Register question categories
        add_action('init', array($this, 'register_question_categories'), 1);
        add_filter('term_link', array($this, 'custom_category_link'), 10, 3);
        add_action('ap_admin_menu', array($this, 'admin_category_menu'));
    }
    
    /**
     * Register category taxonomy for question cpt
     * @return void
     * @since 2.0
     */
    public function register_question_categories(){
        if(ap_opt('enable_categories')){

            /**
             * Labesl for category taxonomy
             * @var array
             */
            $categories_labels = array(
                'name' => __('Question Categories', 'ap'),
                'singular_name' => _x('Category', 'ap'),
                'all_items' => __('All Categories', 'ap'),
                'add_new_item' => _x('Add New Category', 'ap'),
                'edit_item' => __('Edit Category', 'ap'),
                'new_item' => __('New Category', 'ap'),
                'view_item' => __('View Category', 'ap'),
                'search_items' => __('Search Category', 'ap'),
                'not_found' => __('Nothing Found', 'ap'),
                'not_found_in_trash' => __('Nothing found in Trash', 'ap'),
                'parent_item_colon' => ''
            );

            /**
             * FILTER: ap_question_category_labels
             * Filter ic called before registering question_category taxonomy
             */
           $categories_labels = apply_filters( 'ap_question_category_labels',  $categories_labels);

            /**
             * Arguments for category taxonomy
             * @var array
             * @since 2.0
             */
            $category_args = array(
                'hierarchical' => true,
                'labels' => $categories_labels,
                'rewrite' => false
            );

            /**
             * FILTER: ap_question_category_args
             * Filter ic called before registering question_category taxonomy
             */
            $category_args = apply_filters( 'ap_question_category_args',  $category_args);

            /**
             * Now let WordPress know about our taxonomy
             */
            register_taxonomy('question_category', array('question'), $category_args);
        }
    }
    
    //TODO: check if this is needed
    public function custom_category_link($url, $term, $taxonomy){
        if(ap_opt('enable_categories')){
            /* change category link if permalink not enabled */
            if ( 'question_category' == $term->taxonomy && !get_option('permalink_structure')) {
                return add_query_arg( array('question_category' => false, 'page_id' => ap_opt('base_page'), 'qcat_id' =>$term->term_id), $url );
                
            }elseif('question_category' == $term->taxonomy && get_option('permalink_structure')){
                return ap_get_link_to('category/'.$term->slug);
            }
        }
        return $url;
    }

    /**
     * Add category menu in wp-admin
     * @return void
     * @since 2.0
     */
    public function admin_category_menu(){
        if(ap_opt('enable_categories'))
            add_submenu_page('anspress', 'Questions Category', 'Category', 'manage_options', 'edit-tags.php?taxonomy=question_category');
    }

}

/**
 * Get everything running
 *
 * @since 1.0
 *
 * @access private
 * @return void
 */

function categories_for_anspress() {
    $discounts = new Categories_For_AnsPress();
}
add_action( 'plugins_loaded', 'categories_for_anspress' );


function ap_question_categories_html($post_id = false, $list = true, $label = false){
    if(!ap_opt('enable_categories'))
        return;
    
    if(!$post_id)
        $post_id = get_the_ID();
        
    $cats = get_the_terms( $post_id, 'question_category' );
    
    if($cats){
        if($list){
            $o = '<ul class="question-categories">';
            foreach($cats as $c){
                $o .= '<li><a href="'.esc_url( get_term_link($c)).'" title="'.$c->description.'">'. $c->name .'</a></li>';
            }
            $o .= '</ul>';
            echo $o;
        }else{
            $o = 'Categories:';
            if($label)
                $o = $label;
                
            $o .= ' <span class="question-categories-list">';
            foreach($cats as $c){
                $o .= '<a href="'.esc_url( get_term_link($c)).'" title="'.$c->description.'">'. $c->name .'</a>';
            }
            $o .= '</span>';
            echo $o;
        }
    }

}


function ap_category_details(){
    if(!ap_opt('enable_categories'))
        return;
        
    $var = get_query_var('question_category');

    $category = get_term_by('slug', $var, 'question_category');

    echo '<div class="clearfix">';
    echo '<h3><a href="'.get_category_link( $category ).'">'. $category->name .'</a></h3>';
    echo '<div class="ap-taxo-meta">';
    echo '<span class="count">'. $category->count .' '.__('Questions', 'ap').'</span>'; 
    echo '<a class="aicon-rss feed-link" href="' . get_term_feed_link($category->term_id, 'question_category') . '" title="Subscribe to '. $category->name .'" rel="nofollow"></a>';
    echo '</div>';
    echo '</div>';
    
    echo '<p class="desc clearfix">'. $category->description .'</p>';
    
    $child = get_terms( array('taxonomy' => 'question_category'), array( 'parent' => $category->term_id, 'hierarchical' => false, 'hide_empty' => false )); 
                   
    if($child) : 
        echo '<ul class="ap-child-list clearfix">';
            foreach($child as $key => $c) :
                echo '<li><a class="taxo-title" href="'.get_category_link( $c ).'">'.$c->name.'<span>'.$c->count.'</span></a>';
                echo '</li>';
            endforeach;
        echo'</ul>';
    endif;  
}
function ap_child_cat_list($parent){
    $categories = get_terms( array('taxonomy' => 'question_category'), array( 'parent' => $parent, 'hide_empty' => false ));
    
    if($categories){
        echo '<ul class="child clearfix">'; 
        foreach ($categories as $cat){
            echo '<li><a href="'.get_category_link( $cat ).'">' .$cat->name.'<span>'.$cat->count.'</span></a></li>';
        }
        echo '</ul>';
    }
}

function ap_question_have_category($post_id = false){
    if(!$post_id)
        $post_id = get_the_ID();
        
    if(!ap_opt('enable_categories'))
        return false;
    
    $categories = wp_get_post_terms( $post_id, 'question_category');
    if(!empty($categories))
        return true;
    
    return false;
}