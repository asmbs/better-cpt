<?php

/**
 * ---------------------------------------------------------------------------------------------
 * WP_Metabox
 * ---------------------------------------------------------------------------------------------
 *
 * Offers a static API for registering and rendering meta boxes, as well as managing the
 * metadata that they hold.
 *
 * Extend this class and override its methods, then hook your methods to the appropriate actions
 * in your WP_CPT-extended class.
 *
 */
abstract class WP_Metabox
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
   * @var  string  Accepted values: `core`, `high`, `default`, `low`
   *
   * @link http://codex.wordpress.org/Function_Reference/add_meta_box#Parameters
   *
   */
  public static $priority = '';

  // -------------------------------------------------------------------------------------------


  // -------------------------------------------------------------------------------------------
  // Hookable methods
  // -------------------------------------------------------------------------------------------

  /**
   * void register( string $post_type, string $title [, array $args = NULL] )
   *
   * Adds the meta box with the given parameters. Should be run from the
   * `add_meta_boxes` action.
   *
   * This method is FINAL; it cannot be overridden by a child class.
   *
   * @param  string      $post_type  The post type the meta box should be displayed on.
   * @param  string      $title      The title of the meta box (should be marked for
   *                                 translation).
   * @param  array|null  $args       Additional arguments to be passed.
   *
   * @link http://codex.wordpress.org/Function_Reference/add_meta_box
   *
   */
  public static final function register( $post_type, $title, $args = NULL )
  {
    add_meta_box(
      self::$id,
      $title,
      [ get_called_class(), 'render' ],
      $post_type,
      static::$context,
      static::$priority,
      $args
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
   * void restore_from_revision( int $parent_ID, int $revision_ID )
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
  public static function restore_from_revision( $parent_ID, $revision_ID )
  {}

  /**
   * void remove_linked_meta( int $ID )
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
  public static function remove_linked_meta( $ID )
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
