<?php
/**
 * Plugin Name:       Post View Counter
 * Description:       Display Post View Count
 * Version:           1.0.0
 * Author:            Raziul Islam
 * License:           GPLV2
 * Text Domain:       post-view-counter
 */

if (!defined('ABSPATH')) {
    exit;
}


require_once __DIR__ . '/vendor/autoload.php';

class We_Post_View_Counter{

    /**
     * construct
     */
    function __construct(){
        add_action('init', [$this, 'init']);
    }

    /**
     * Manage Hook
     *
     * @return void
     */
    public function init(){

        $this->define_constants();

        //asssets Hook
        add_action('wp_enqueue_scripts', [$this, 'frontend_scripts']);


        //Manage Column Hook
        add_filter('manage_posts_columns', [$this, 'admin_view_count_column']);
        add_action('manage_posts_custom_column', [$this, 'display_admin_view_count_column'], 10, 2);

        add_filter('manage_edit-post_sortable_columns', [$this, 'add_sortable_column']);

        add_action('wp_head', [$this, 'post_view_count']);
        
        
        //Render Shortcode
        add_shortcode('post_view_shortcode', [$this, 'post_view_display_shortcode']);
    }

     /**
     * Define File Path
     *
     * @return void
     */
    public function define_constants(){
        define('POVC_URL', plugins_url('', __FILE__));
        define('POVC_ASSETS', POVC_URL . '/assets');
    }

    /**
     * Post View Count
     *
     * @return void
     */
    function post_view_count(){
        if ( !is_single() || !is_singular( 'post' ) ) {
            return;
        }
    
        $id = get_the_ID();
    
        $number = absint( get_post_meta( $id, 'view_count', true ) );
        if ( empty( $number ) ) {
            $number = 1;
            add_post_meta( $id, 'view_count', $number );
        } else {
            $number++;
            update_post_meta( $id, 'view_count', $number );
        }
    }


    /**
     * Display Post View In column
     *
     * @param [type] $column
     * @param [type] $post_id
     * @return void
     */
    public function display_admin_view_count_column($column, $post_id){
        if($column == 'view_count'){
            $id = get_the_ID();
            $view_count = get_post_meta($id, 'view_count', true);
            $view_count = $view_count ? $view_count : 0;
            echo $view_count;
        }
    }
    

    /**
     * Post View Column Title
     *
     * @param [type] $column
     * @return void
     */
    public function admin_view_count_column($column){
        $column['view_count'] = 'View Count';
        return $column;
    }

    /**
     * Post View Column Title
     *
     * @param [type] $column
     * @return void
     */
    public function add_sortable_column($columns){
        $columns['id'] = 'ID';
        $columns['view_count'] = 'View Count';
        return $columns;
    }

    /**
     * Render Shortcode
     *
     * @return void
     */
    public function post_view_display_shortcode( $atts ){
        $values = [
            'id' => '',
        ];
        $shortcode_atts = shortcode_atts($values, $atts);

        $post_id = isset($shortcode_atts['id']) ? intval($shortcode_atts['id']) : 0;

        $markup ='';

        if ($post_id > 0) {
            $view_count = get_post_meta($post_id, 'view_count', true);
            $view_count = $view_count ? $view_count : 0;
            return '
            <div class="viewcounter--wrapr">
                <h1>Post View Count</h1>
                <h3>Total Post View:</h3>
                <span>'.$view_count.'</span>
            </div>
            ';
        }

        return $markup;
    }

    

    /**
     * Assets Load
     *
     * @return void
     */
    public function frontend_scripts(){
        wp_enqueue_style('werpp-style', POVC_ASSETS . '/css/style.css');

    }



}

//plugin run
new We_Post_View_Counter();