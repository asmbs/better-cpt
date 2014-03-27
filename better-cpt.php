<?php
/**
 * Plugin Name: Better Custom Post Types
 * Plugin URI:  https://github.com/asmbs/better-cpts
 * Description: A plugin for your plugins. Just extend the WP_CPT class in your own plugin(s) for a clean, object-oriented way to encapsulate your custom post type's functionality.
 * 
 * Author:      ASMBS
 * Author URI:  https://github.com/asmbs
 *
 * License:     MIT License
 * License URI: http://opensource.org/licenses/MIT
 *
 * Version:     1.0.0-beta
 */


/**
 * ---------------------------------------------------------------------------------------------
 * WP_CPT
 * ---------------------------------------------------------------------------------------------
 *
 * Wraps all the functionality of WordPress custom post types together; includes post type
 * registration, creating/updating/deleting meta data, admin editing functionality, AJAX
 * endpoints and more.
 *
 * IMPORTANT NOTES:
 * 
 *  1.  This class is abstract, so you MUST extend it. You cannot instantiate it! It
 *      also contains several abstract methods, listed below. These methods MUST be overridden
 *      in your extending class.
 *
 *      ABSTRACT METHODS (must be overridden in your extending class):
 *
 *  2.  Any non-abstract method may also be overridden in your extending class, but is not
 *      required to be.
 *
 *  3.  The constructor sets basically every hook you could possibly need, so it's recommended
 *      that you not override it--you should instead override the additional_hooks() method
 *      and place your stuff in there.
 * ---------------------------------------------------------------------------------------------
 */
abstract class WP_CPT
{
  // -------------------------------------------------------------------------------------------
  // Variables
  // -------------------------------------------------------------------------------------------

  /**
   * @var  string  The post type slug (what you'll see for 'post_type' across the WordPress
   *               universe).
   */
  public $post_type = '';

  /**
   * @var  array   The arguments used when the post type is registered. This class provides a
   *               method for setting them automatically (provided a few parameters), as well as
   *               one for setting them automatically.
   * @see  set_args(), set_manual_args()
   */
  private $args = [];

  // ===========================================================================================


  // -------------------------------------------------------------------------------------------
  // Initialization
  // -------------------------------------------------------------------------------------------

  /**
   * __construct()
   *
   * Constructor. Sets a butt load of hooks (like, all of them).
   *
   */
  public function __construct()
  {
    // Set activation and deactivation hooks.
    $this->register_activation_hooks();
    $this->register_deactivation_hooks();

    // Register post type.
    add_action( 'init', [ $this, 'register_post_type' ] );

    // Add custom columns to the post manager
    add_filter( 'manage_edit-'. $this->post_type .'_columns', [ $this, 'add_custom_columns' ] );
    add_action( 'manage_'. $this->post_type .'_posts_custom_column', [ $this, 'populate_custom_columns' ] );

    // Manage sortable columns in the post manager
    add_filter( 'manage_edit-'. $this->post_type .'_sortable_columns', [ $this, 'add_sortable_columns' ] );
    add_action( 'pre_get_posts', [ $this, 'sortable_columns_orderby' ] );

    // Customize the confirmation messages shown on the post edit screen
    add_filter( 'post_updated_messages', [ $this, 'post_updated_messages' ] );

    // Add contextual help tabs
    add_action( 'admin_head', [ $this, 'add_help_tabs' ] );

    // Enqueue scripts.
    add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_scripts' ] );
    add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );

    // Add meta boxes.
    add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

    // Handle saving/updating/removal of post meta.
    add_action( 'save_post', [ $this, 'update_post_meta' ] );

    // If this post type has meta linking it to other post types, this hook should handle
    // what happens if the relates post is removed.
    add_action( 'delete_post', [ $this, 'remove_linked_meta'] );    

    // Modify queries
    add_action( 'pre_get_posts', [ $this, 'modify_query' ] );

    // Add AJAX endpoints
    $this->add_ajax_endpoints();

    // Set any additional hooks
    $this->additional_hooks();
  }


  /**
   * void register_activation_hooks()
   *
   * Override this method to call register_activation_hook() for your plugin, or if you're
   * building a theme, hook into the 'after_switch_theme' action.
   *
   * These hooks are commonly used for flushing permalink rules to support your post type's
   * rewrite rules, so this class includes a utility method for doing just that.
   *
   * @see  register_and_flush()
   *
   */
  public function register_activation_hooks()
  {}


  /**
   * void register_deactivation_hooks()
   *
   * Override this method if you need to register a deactivation hook for your plugin. If
   * you're building a theme, you should hook into the 'switch_theme' action.
   *
   */
  public function register_deactivation_hooks()
  {}

  // ===========================================================================================


  // -------------------------------------------------------------------------------------------
  // Post type registration
  // -------------------------------------------------------------------------------------------
}
