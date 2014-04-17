<?php

/**
 * ---------------------------------------------------------------------------------------------
 * WP_Meta
 * ---------------------------------------------------------------------------------------------
 *
 * Offers a static API for registering and rendering meta boxes, as well as managing the
 * metadata that they hold.
 *
 * Extend this class and override its methods, then just call the register() method inside your
 * post type
 *
 */
abstract class WP_Meta
{
  // -------------------------------------------------------------------------------------------
  // Variables
  // -------------------------------------------------------------------------------------------

  /**
   * @var  string  The `id` attribute the meta box will receive.
   *
   * @link http://codex.wordpress.org/Function_Reference/add_meta_box#Parameters
   *
   */
  public static $id = '';

  /**
   * @var  string  Where the box will be shown on the edit screen.
   *               Accepted values: `normal`, `advanced`, `side`
   *
   * @link http://codex.wordpress.org/Function_Reference/add_meta_box#Parameters
   *
   */
  public static $context = '';

  /**
   * @var  string  Accepted values: `high`, `core`, `default`, `low`
   *
   * @link http://codex.wordpress.org/Function_Reference/add_meta_box#Parameters
   *
   */
  public static $priority = '';

  /**
   * @var  array  Arguments to use for the call to WP's add_meta_box() function.
   *
   * @link http://codex.wordpress.org/Function_Reference/add_meta_box#Parameters
   *
   */
  public static $args = [];

  // -------------------------------------------------------------------------------------------

  // -------------------------------------------------------------------------------------------
  // Setup
  // -------------------------------------------------------------------------------------------

  /**
   * void register( string $post_type, string $title [, string $context = NULL [, string $priority = NULL [, array $args = NULL]]] )
   *
   * Registers this metadata manager; adds the meta box and sets hooks for saving, updating
   * and removing metadata.
   *
   * @param  string  $post_type  The post type this class is being registered for.
   * @param  string  $title      The title to display on the meta box (marked for translation).
   * @param  string  $context    Overrides the context defined by the class.
   * @param  string  $priority   Overrides the priority defined by the class.
   * @param  array   $args       Additional meta box args to pass.
   *
   * @see   WP_Meta::$context, WP_Meta::$priority
   *
   */
  public static final function register( $post_type, $title, $context = NULL, $priority = NULL, $args = NULL )
  {
    // Use class's context and priority definitions if an override wasn't specified.
    $context = empty( $context ) ? static::$context : $context;
    $priority = empty( $priority ) ? static::$priority : $priority;

    // Set the arguments that add_meta_box() will have to reference.
    static::$args = [
      'post_type' => $post_type,
      'title'     => $title,
      'context'   => $context,
      'priority'  => $priority,
      'args'      => $args
    ];

    // Set all the necessary hooks.
    add_action( 'add_meta_boxes_'. $post_type, [ get_called_class(), 'add_meta_box' ] );
    add_action( 'save_post', [ get_called_class(), 'save_metadata' ] );
    add_action( 'wp_restore_post_revision', [ get_called_class(), 'restore_metadata_from_revision' ], 10, 2 );
    add_action( 'delete_post', [ get_called_class(), 'delete_linked_metadata' ] );
  }

  // -------------------------------------------------------------------------------------------


  // -------------------------------------------------------------------------------------------
  // Hooked methods
  // -------------------------------------------------------------------------------------------

  /**
   * void add_meta_box()
   *
   * Registers the meta box for the specified post type.
   *
   * @link  http://codex.wordpress.org/Function_Reference/add_meta_box
   *
   */
  public static final function add_meta_box()
  {
    add_meta_box(
      static::$id,
      static::$args['title'],
      [ get_called_class(), 'render' ],
      static::$args['post_type'],
      static::$args['context'],
      static::$args['priority'],
      static::$args['args']
    );
  }


  /**
   * void render( WP_Post $post, array $metabox )
   *
   * Renders the content of the meta box. Must be overridden by your child class, otherwise
   * you'll just get the snarky message below on your edit screen.
   *
   * @param  WP_Post  $post     The post currently being edited.
   * @param  array    $metabox  An array describing the meta box.
   *
   */
  public static function render( $post, $metabox )
  {
    echo '<p>Uh oh, somebody didn\'t override their render method...</p>';
  }


  /**
   * void save_metadata( int $ID )
   *
   * Override this method to handle saving/updating any metadata associated with your
   * meta box.
   *
   * If you're using this with a WP_CPT child class, you should call this in your
   * update_post_meta() method.
   *
   * Use $_POST or $_REQUEST to retrieve the submitted data.
   *
   * @param  int  $ID  The ID of the post being saved/updated.
   * @see    WP_CPT::update_post_meta()
   *
   */
  public static function save_metadata( $ID )
  {}

  /**
   * void restore_metadata_from_revision( int $parent_ID, int $revision_ID )
   *
   * If your metadata is being kept for revisions, override this method to handle restoring
   * metadata when a revision is restored.
   *
   * If you're using this with a WP_CPT child class, call this in your
   * restore_meta_from_revision() method.
   *
   * @param  int  $parent_ID    The ID of the parent post.
   * @param  int  $revision_ID  The ID of the revision.
   * @see    WP_CPT::restore_meta_from_revision()
   *
   */
  public static function restore_metadata_from_revision( $parent_ID, $revision_ID )
  {}

  /**
   * void delete_linked_metadata( int $ID )
   *
   * If any metadata being managed by your WP_Metabox child class is being used to associate
   * the target post type with posts of another type, this method should handle removing those
   * links when a post of the linked type is deleted.
   *
   * If you're using this with a WP_CPT child class, call this in your remove_linked_meta()
   * method.
   *
   * @param  int  $ID  The ID of the post that was deleted.
   * @see    WP_CPT::remove_linked_meta()
   *
   */
  public static function delete_linked_metadata( $ID )
  {}

  // -------------------------------------------------------------------------------------------


  // -------------------------------------------------------------------------------------------
  // Utilities
  // -------------------------------------------------------------------------------------------

  /**
   * object nonce()
   *
   * Generate action and name values for a nonce field.
   *
   * @return  object  The generated values. `$obj->name` contains the nonce name, which is just
   *                  the meta box ID with `-nonce` appended to it. `$obj->action` is a
   *                  substring of the sha1 digest of the meta box ID.
   *
   * @link    http://codex.wordpress.org/Function_Reference/wp_nonce_field
   * @link    http://codex.wordpress.org/Function_Reference/wp_verify_nonce
   * 
   */
  public static final function nonce()
  {
    return (object) [
      'name' => static::$id .'-nonce',
      'action' => substr( sha1( static::$id ), 7 )
    ];
  }

  // -------------------------------------------------------------------------------------------
}
