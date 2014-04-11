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


// ---------------------------------------------------------------------------------------------
// Include additional classes
// ---------------------------------------------------------------------------------------------

@include_once 'additional_classes/class-metabox.php';

// ---------------------------------------------------------------------------------------------


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
 *  1.  You can (and should) override any method defined in this class, with the exception of
 *      the set_args() and set_manual_args() methods. You can also add your own methods, which
 *      you will have to do if you do anything that requires a callback. For example, when you
 *      override $this->add_meta_boxes() to register your own meta boxes, you'll call
 *      WordPress's add_meta_box() function, which requires a callback for actually rendering
 *      the content of the meta box. You'll want to define that callback in your class.
 *
 *  2.  The constructor sets basically every hook you could possibly need, so it's recommended
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
   *
   */
  public $post_type = '';

  /**
   * @var  array   The arguments used when the post type is registered. This class provides a
   *               method for setting them automatically (provided a few parameters), as well as
   *               one for setting them automatically.
   * @see  set_args(), set_manual_args()
   *
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

    // Restore post meta when restoring a revision (if post type supports it)
    add_action( 'wp_restore_post_revision', [ $this, 'restore_meta_from_revision'], 10, 2 );

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
   * @link  http://codex.wordpress.org/Function_Reference/register_post_type
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
   * @link  http://codex.wordpress.org/Function_Reference/flush_rewrite_rules
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
   * This method is final (it cannot be overridden by your class).
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
   * @link   http://codex.wordpress.org/Function_Reference/register_post_type
   *
   */
  final public function set_args( $singular, $plural, $pos, $icon = 'dashicons-admin-post', $args = [] )
  {
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
   * This method is final (it cannot be overridden by your class).
   *
   * @param  array  $args  An array of arguments.
   *
   * @link   http://codex.wordpress.org/Function_Reference/register_post_type
   *
   */
  final public function set_manual_args( $args )
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


  /**
   * void populate_custom_columns( string $column )
   *
   * Control what is put in custom columns for each post in the table. Contrary to its title,
   * you can also use this function to control what is shown in the standard columns, but use
   * this idea with caution!
   *
   * @param  string  $column  The name (ID) of the current column.
   *
   */
  public function populate_custom_columns( $column )
  {}


  /**
   * array add_sortable_columns( array $columns )
   *
   * Add an ordering directive to columns to make them sortable. The column's value
   * corresponds to the 'orderby' value in WP_Query.
   *
   * @param   array  $columns  The array of sortable columns. Format: [id] => orderby
   * @return  array            The new array of sortable columns.
   *
   * @link    http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters
   *
   */
  public function add_sortable_columns( $columns )
  {
    return $columns;
  }


  /**
   * void sortable_columns_orderby( WP_Query $query )
   *
   * If a custom column requires something other than one of the build-in 'orderby' values
   * to function properly, its procedure should be defined here. Once established, the same
   * ordering can be used anywhere in WordPress, not just in the post manager table.
   *
   * @param  WP_Query  $query  The current query, passed by reference.
   *
   * @link   http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters
   *
   */
  public function sortable_columns_orderby( $query )
  {}

  // ===========================================================================================


  // -------------------------------------------------------------------------------------------
  // Messages and contextual help
  // -------------------------------------------------------------------------------------------
  
  /**
   * array post_updated_messages( array $messages )
   *
   * Modify the list of messages shown on the editor screen when a post is saved or updated.
   *
   * @param   array  $messages  The existing array of messages. See the example in the WordPress
   *                            Codex entry for register_post_type() for details on the format
   *                            of $messages.
   * @return  array             The updated array of messages.
   *
   * @link    http://codex.wordpress.org/Function_Reference/register_post_type#Example
   *
   */
  public function post_updated_messages( $messages )
  {
    return $messages;
  }


  /**
   * void add_help_tabs()
   *
   * Modify the contextual help for a screen. Use get_current_screen() to get information about
   * the current view and build your help content accordingly.
   *
   * @link  http://codex.wordpress.org/Function_Reference/get_current_screen
   * @link  http://codex.wordpress.org/Function_Reference/register_post_type#Example
   *
   */
  public function add_help_tabs()
  {}

  // ===========================================================================================


  // -------------------------------------------------------------------------------------------
  // Meta boxes
  // -------------------------------------------------------------------------------------------
  
  /**
   * void add_meta_boxes()
   *
   * Register meta boxes to be shown in the post editor, using add_meta_box().
   *
   * @link  http://codex.wordpress.org/Function_Reference/add_meta_box
   *
   */
  public function add_meta_boxes()
  {}

  // ===========================================================================================


  // -------------------------------------------------------------------------------------------
  // Metadata management
  // -------------------------------------------------------------------------------------------
  
  /**
   * void update_post_meta( int $ID )
   *
   * Use this function to sanitize and update any post meta that your meta boxes modify.
   *
   * IMPORTANT NOTE: If your post type supports revisions, and you want your meta data to be
   * under revision control too, use update_metadata() instead of update_post_meta(). The latter
   * will divert to the parent ID if it's given the ID of a revision. Also, if you want your
   * meta data under revision control, you must implement restore_meta_from_revision() to 
   * handle the process of transferring the restored revision's meta to the parent post.
   *
   * @param  int  $ID  The ID of the post being updated.
   *
   * @see    restore_meta_from_revision()
   * @link   http://codex.wordpress.org/Function_Reference/update_post_meta
   * @link   http://codex.wordpress.org/Function_Reference/update_metadata
   *
   */
  public function update_post_meta( $ID )
  {}


  /**
   * void restore_meta_from_revision( int $parent_ID, int $revision_ID )
   *
   * If your post type supports revisions, your implementation of this method should handle
   * transferring a revision's meta data to its parent when that revision is restored.
   *
   * NOTE: You must use get_metadata() instead of get_post_meta() in this method, because
   * the latter will switch its focus to the parent post if it's given a revision ID.
   *
   * @param  int  $parent_ID    The ID of the actual post (parent post).
   * @param  int  $revision_ID  The revision's ID.
   *
   * @link   http://codex.wordpress.org/Function_Reference/get_metadata
   *
   */
  public function restore_meta_from_revision( $parent_ID, $revision_ID )
  {}


  /**
   * void remove_linked_meta( int $ID )
   *
   * WordPress automatically handles deleting a posts' meta data when that post is deleted, so
   * that is NOT what this method is for.
   *
   * Implement this method only if a post of your post type uses meta data to associate itself
   * with another post (a post-to-post relationship). If you have such a relationship, and a
   * "related" post is deleted, this method should take care of removing the meta that defined
   * that relationship, to prevent broken links and keep your data consistent.
   *
   * @param  int  $ID  The ID of the post that was deleted.
   *
   */
  public function remove_linked_meta( $ID )
  {}

  // ===========================================================================================


  // -------------------------------------------------------------------------------------------
  // Query modifications
  // -------------------------------------------------------------------------------------------
  
  /**
   * void modify_query( WP_Query $query )
   *
   * Run on pre_get_posts; allows you to modify parameters of the current query before it is
   * actually made (before posts are retrieved).
   *
   * @param  WP_Query  $query  The current query.
   *
   * @link   http://codex.wordpress.org/Class_Reference/WP_Query
   *
   */
  public function modify_query( $query )
  {}

  // ===========================================================================================


  // -------------------------------------------------------------------------------------------
  // Script and style enqueuing
  // -------------------------------------------------------------------------------------------
  
  /**
   * void enqueue_frontend_scripts()
   *
   * Runs on wp_enqueue_scripts; enqueue any front-end scripts or stylesheets in your
   * implementation of this method.
   *
   * @link  http://codex.wordpress.org/Plugin_API/Action_Reference/wp_enqueue_scripts
   * @link  http://codex.wordpress.org/Function_Reference/wp_enqueue_script
   * @link  http://codex.wordpress.org/Function_Reference/wp_enqueue_style
   *
   */
  public function enqueue_frontend_scripts()
  {}


  /**
   * void enqueue_admin_scripts( string $hook )
   *
   * Runs on admin_enqueue_scripts; enqueue admin scripts and/or stylesheets here. You can also
   * target specific admin pages (e.g. edit.php, post-new.php, etc.) using $hook.
   *
   * @param  string  $hook  The hook suffix for the current admin screen. See the documentation
   *                        for the admin_enqueue_scripts action for more information.
   *
   * @link   http://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
   * @link   http://codex.wordpress.org/Function_Reference/wp_enqueue_script
   * @link   http://codex.wordpress.org/Function_Reference/wp_enqueue_style
   *
   */
  public function enqueue_admin_scripts( $hook )
  {}

  // ===========================================================================================


  // -------------------------------------------------------------------------------------------
  // AJAX endpoints
  // -------------------------------------------------------------------------------------------
  
  /**
   * void add_ajax_endpoints()
   *
   * Register all your wp_ajax_{endpoint_name} actions here. You'll obviously have to define
   * your own callback methods for any endpoints you set here.
   *
   * @link  http://codex.wordpress.org/Plugin_API/Action_Reference/wp_ajax_(action)
   *
   */
  public function add_ajax_endpoints()
  {}

  // ===========================================================================================


  // -------------------------------------------------------------------------------------------
  // Miscellaneous
  // -------------------------------------------------------------------------------------------
  
  // ===========================================================================================

}
