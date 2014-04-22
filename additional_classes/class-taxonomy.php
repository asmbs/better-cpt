<?php

/**
 * ---------------------------------------------------------------------------------------------
 * WP_Taxonomy
 * ---------------------------------------------------------------------------------------------
 *
 */
abstract class WP_Taxonomy
{
  // -------------------------------------------------------------------------------------------
  // Variables
  // -------------------------------------------------------------------------------------------

  /**
   * @var  string  The name of the taxonomy.
   *
   */
  public $name = '';


  /**
   * @var  bool  Whether this is a hierarchical taxonomy.
   *
   */
  public $hierarchical = false;


  /**
   * @var  array  The object/post types this taxonomy is registered for.
   */
  protected $post_types = [];


  /**
   * @var  array  The arguments used to construct the taxonomy.
   *
   */
  protected $args = [];

  // -------------------------------------------------------------------------------------------


  // -------------------------------------------------------------------------------------------
  // Setup
  // -------------------------------------------------------------------------------------------

  /**
   * __construct( ... )
   *
   * Sets argument list for registering the taxonomy and adds the init hook to do the actual registration.
   * @param  string  $singular      The singular of the taxonomy name, marked for l10n.
   * @param  string  $plural        The plural of the taxonomy name, marked for l10n.
   *                                categories) or not (like tags).
   * @param  bool    $custom_cb     If set to TRUE, will use this class's render_meta_box()
   *                                function instead of using the default UI.
   * @param  array   $extra_args    Additional arguments; these will override any auto-generated
   *                                arguments.
   *
   * @link   http://codex.wordpress.org/Function_Reference/register_taxonomy
   *
   */
  public final function __construct( $singular, $plural, $custom_cb = false, $extra_args = [] )
  {
    // Set standard labels.
    $labels = [
      'name'          => $plural,
      'singular_name' => $singular,
      'menu_name'     => $plural,
      'all_items'     => sprintf( __( 'All %s' ), $plural ),
      'edit_item'     => sprintf( __( 'Edit %s' ), $singular ),
      'view_item'     => sprintf( __( 'View %s' ), $singular ),
      'update_item'   => sprintf( __( 'Update %s' ), $singular ),
      'add_new_item'  => sprintf( __( 'Add New %s' ), $singular ),
      'new_item_name' => sprintf( __( 'New %s Name' ), $singular ),
      'search_items'  => sprintf( __( 'Search %s' ), $plural )
    ];

    // Set specific labels for hierarchical/non-hierarchical use.
    if ( $this->hierarchical )
    {
      $labels = array_replace_recursive( $labels, [
        'parent_item'       => sprintf( __( 'Parent %s' ), $singular ),
        'parent_item_colon' => sprintf( __( 'Parent %s:' ), $singular )
      ] );
    }
    else
    {
      $labels = array_replace_recursive( $labels, [
        'popular_items'              => sprintf( __( 'Popular %s' ), $plural ),
        'separate_items_with_commas' => sprintf( __( 'Separate %s with commas' ), strtolower( $plural ) ),
        'add_or_remove_items'        => sprintf( __( 'Add/remove %s' ), strtolower( $plural ) ),
        'choose_from_most_used'      => sprintf( __( 'Choose from the most-used %s' ), strtolower( $plural ) ),
        'not_found'                  => sprintf( __( 'No %s found' ), strtolower( $plural ) )
      ] );
    }

    // Set up the rest of the default arguments.
    $default_args = [
      'label'   => $plural,
      'labels'  => $labels,
      'public'  => true,
      'show_ui' => true,
      'show_in_nav_menus' => true,
      'show_tagcloud'     => false,
      'meta_box_cb'       => $custom_cb ? [ $this, 'render_meta_box' ] : NULL,
      'show_admin_column' => true,
      'hierarchical'      => $this->hierarchical,
      'rewrite'           => [
        'slug'       => sanitize_title( $plural, 'taxonomy slug' ),
        'with_front' => true
      ],
      'sort'              => false
    ];

    $this->args = array_replace_recursive( $this->args, $default_args, $extra_args );

    add_action( 'init', [ $this, 'register_taxonomy' ] );
  }

  public final function register_taxonomy()
  {
    // Register the taxonomy itself.
    register_taxonomy( $this->name, NULL, $this->args );

    // Register it for the specified post types.
    foreach ( $this->post_types as $post_type )
    {
      $this->add_to_post_type( $post_type );
    }
  }

  // -------------------------------------------------------------------------------------------


  // -------------------------------------------------------------------------------------------
  // Custom meta box handling
  // -------------------------------------------------------------------------------------------

  public function render_meta_box()
  {}

  // -------------------------------------------------------------------------------------------


  // -------------------------------------------------------------------------------------------
  // Taxonomy association
  // -------------------------------------------------------------------------------------------

  /**
   * @param   string  $post_type  The post type to attach this taxonomy to.
   * @return  bool                The result of register_taxonomy_for_object_type().
   *
   * @link    http://codex.wordpress.org/Function_Reference/register_taxonomy_for_object_type
   *
   */
  public final function add_to_post_type( $post_type )
  {
    return register_taxonomy_for_object_type( $this->name, $post_type );
  }

  // -------------------------------------------------------------------------------------------

}
