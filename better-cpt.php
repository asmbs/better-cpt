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

  public function additional_hooks()
  {}

  // ===========================================================================================


  // -------------------------------------------------------------------------------------------
  // Post type registration
  // -------------------------------------------------------------------------------------------

  /**
   * void register_post_type()
   *
   * Registers the post type using the args set in $this->args.
   *
   * @link  https://codex.wordpress.org/Function_Reference/register_post_type
   * @see  set_args(), set_manual_args()
   *
   */
  public function register_post_type()
  {
    register_post_type( $this->post_type, $this->args );
  }


  /**
   * void register_and_flush()
   *
   * Calls $this->register_post_types(), then flushes rewrite rules. This method should ONLY be
   * called on plugin activation or theme activation...flush_rewrite_rules() is a very
   * expensive operation and will cause big overhead problems if you use it on every page load.
   *
   * @link  https://codex.wordpress.org/Function_Reference/flush_rewrite_rules
   * @see   register_post_type()
   *
   */
  public function register_and_flush()
  {
    register_post_type( $this->post_type, $this->args );
    flush_rewrite_rules();
  }


  /**
   * void set_args( string $singular, string $plural, int|string $pos [, string $icon = 'dashicons-admin-post' [, array $args = [] ]] )
   *
   * Sets arguments for register_post_type(). Sets labels automatically based on the $singular
   * and $plural arguments. This method should be called immediately after instantiation.
   * Alternatively, override the register_post_type() function to call this method AND call
   * parent::register_post_types().
   *
   * @param  string      $singular  A translatable singular name for this post type.
   * @param  string      $plural    A translatable plural name.
   * @param  int|string  $pos       The desired menu position (use decimals, e.g. '20.1' for
   *                                more granular control).
   * @param  string      $icon      The dashicons helper name for the icon you wish to use (pass
   *                                NULL or an empty string if you plan on using your own).
   * @param  array       $args      Additional earguments to merge in. Note that arguments
   *                                generated from previous parameters will be overwritten by
   *                                this array, so be mindful.
   *
   * @link   https://codex.wordpress.org/Function_Reference/register_post_type
   *
   */
  public function set_args( $singular, $plural, $pos, $icon = 'dashicons-admin-post', $args = [] )
  {
    // Standardize case of provided labels.
    $singular = ucwords( strtolower( $singular ) );
    $plural   = ucwords( strtolower( $plural ) );

    // Set labels.
    $labels = [
      'name'               => $plural,
      'singular_name'      => $singular,
      'menu_name'          => $plural,
      'menu_admin_bar'     => $singular,
      'all_items'          => sprintf( __( 'All %s' ), $plural ),
      'add_new'            => __( 'Add New' ),
      'add_new_item'       => sprintf( __( 'Add New %s' ), $singular ),
      'edit_item'          => sprintf( __( 'Edit %s' ), $singular ),
      'new_item'           => sprintf( __( 'New %s' ), $singular ),
      'view_item'          => sprintf( __( 'View %s' ), $singular ),
      'search_items'       => sprintf( __( 'Search %s' ), $plural ),
      'not_found'          => sprintf( __( 'No %s found.' ), strtolower( $plural ) ),
      'not_found_in_trash' => sprintf( __( 'No %s found in trash.' ), strtolower( $plural ) ),
      'parent_item'        => sprintf( __( 'Parent %s' ), $singular ),
      'parent_item_colon'  => sprintf( __( 'Parent %s' ), $singular )
    ];

    // Generate slug.
    $slug = sanitize_title( $plural, 'post type slug' );

    // Set generated arguments.
    $auto_args = [
      'label'               => $plural,
      'labels'              => $labels,
      'public'              => true,
      'exclude_from_search' => false,
      'publicly_queryable'  => true,
      'show_ui'             => true,
      'show_in_nav_menus'   => true,
      'show_in_menu'        => true,
      'show_in_admin_bar'   => true,
      'menu_position'       => $pos,
      'menu_icon'           => $icon,
      'hierarchical'        => false,
      'supports'            => [ 'title', 'editor' ],
      'has_archive'         => true,
      'rewrite'             => [ 'slug' => $slug, 'with_front' => true ],
      'can_export'          => true
    ];

    // Merge and save arguments.
    $this->args = array_merge_recursive( $this->args, $auto_args, $args );
  }


  /**
   * void set_manual_args( array $args )
   *
   * Manually set arguments for register_post_type, just like the old-fashioned way. This method
   * should be called immediately after instantiation, or called in an overridden
   * register_post_type() implementation in conjunction with parent::register_post_types().
   *
   * @param  array  $args  An array of arguments.
   *
   * @link   https://codex.wordpress.org/Function_Reference/register_post_type
   *
   */
  public function set_manual_args( $args )
  {
    $this->args = array_merge_recursive( $this->args, $args );
  }

  // ===========================================================================================


  // -------------------------------------------------------------------------------------------
  // Custom post manager columns
  // -------------------------------------------------------------------------------------------
  
  /**
   * array add_custom_columns( array $columns )
   *
   * Modify the list of columns that show on the post manager screen.
   *
   * @param   array  $columns  The current array of columns. Format: [id] => Display Title.
   * @return  array            The updated column array.
   *
   */
  public function add_custom_columns( $columns )
  {
    return $columns;
  }

  public function populate_custom_columns( $column )
  {}

  public function add_sortable_columns( $columns )
  {}

  public function sortable_columns_orderby( $query )
  {}

  // ===========================================================================================


  // -------------------------------------------------------------------------------------------
  // Messages and contextual help
  // -------------------------------------------------------------------------------------------
  
  public function post_updated_messages( $messages )
  {}

  public function add_help_tabs()
  {}

  // ===========================================================================================


  // -------------------------------------------------------------------------------------------
  // Meta boxes
  // -------------------------------------------------------------------------------------------
  
  public function add_meta_boxes()
  {}

  // ===========================================================================================


  // -------------------------------------------------------------------------------------------
  // Metadata management
  // -------------------------------------------------------------------------------------------
  
  public function update_post_meta( $ID )
  {}

  public function remove_linked_meta( $ID )
  {}

  // ===========================================================================================


  // -------------------------------------------------------------------------------------------
  // Query modifications
  // -------------------------------------------------------------------------------------------
  
  public function modify_query( $query )
  {}

  // ===========================================================================================


  // -------------------------------------------------------------------------------------------
  // Script and style enqueuing
  // -------------------------------------------------------------------------------------------
  
  public function enqueue_frontend_scripts( $hook )
  {}

  public function enqueue_admin_scripts( $hook )
  {}

  // ===========================================================================================


  // -------------------------------------------------------------------------------------------
  // AJAX endpoints
  // -------------------------------------------------------------------------------------------
  
  public function add_ajax_endpoints()
  {}

  // ===========================================================================================


  // -------------------------------------------------------------------------------------------
  // Miscellaneous
  // -------------------------------------------------------------------------------------------
  
  // ===========================================================================================

}
