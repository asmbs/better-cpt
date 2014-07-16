<?php

if ( !function_exists( 'quick_register_post_type' ) )
{
/**
   * quick_register_post_type()
   *
   * Registers a post type using auto-generated labels and a default set of arguments. Use this
   * in place of `register_post_type` (call it inside the `init` action).
   *
   * @param  string  $post_type  The internal name (ID) of the post type.
   * @param  string  $singular   The singular name of a post of this type, marked for translation.
   * @param  string  $plural     The plural name of a group of posts of this type, also marked
   *                             for translation.
   * @param  array   $args       Any arguments you wish to supply manually; arguments given here
   *                             will override default arguments AND generated labels.
   *
   * @link   http://codex.wordpress.org/Function_Reference/register_post_type
   *
   */
  function quick_register_post_type( $post_type, $singular, $plural, $args = [] )
  {
    // Set slug for rewrites
    $slug = sanitize_title( $plural, 'post type slug' );

    // Generate labels
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

    // Set default arguments
    $default_args = [
        'label'               => $plural,
        'labels'              => $labels,
        'public'              => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_nav_menus'   => true,
        'show_in_menu'        => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => '25',
        'menu_icon'           => 'dashicons-admin-post',
        'hierarchical'        => false,
        'has_archive'         => true,
        'rewrite'             => [ 'slug' => $slug, 'with_front' => true ],
        'supports'            => [],
        'can_export'          => true
    ];

    // Merge arguments
    $merged_args = array_replace_recursive( $default_args, $args );

    // Register the post type
    register_post_type( $post_type, $merged_args );
  }
}
  
if ( !function_exists( 'quick_register_taxonomy' ) )
{
/**
   * quick_register_taxonomy()
   *
   * Registers a taxonomy using auto-generated labels and a default set of arguments. Use in place
   * of `register_taxonomy` inside an `init` hook.
   *
   * @param  string        $name        The internal name of the taxonomy.
   * @param  string        $singular    The singular name, marked for translation.
   * @param  string        $plural      The plural name, marked for translation.
   * @param  string|array  $post_types  The post/object types to associate with this taxonomy.
   * @param  array         $args        Any arguments you wish to supply manually; arguments given
   *                                    here will override default arguments AND generated labels.
   *
   * @link   http://codex.wordpress.org/Function_Reference/register_taxonomy
   * @link   http://codex.wordpress.org/Function_Reference/register_taxonomy_for_object_type
   *
   */
  function quick_register_taxonomy( $name, $singular, $plural, $post_types = NULL, $args = [] )
  {
    // Set slug for rewrites
    $slug = sanitize_title( $plural, 'taxonomy slug' );

    // Generate labels
    $labels = [
      'name'                       => $plural,
      'singular_name'              => $singular,
      'menu_name'                  => $plural,
      'all_items'                  => sprintf( __( 'All %s' ), $plural ),
      'edit_item'                  => sprintf( __( 'Edit %s' ), $singular ),
      'view_item'                  => sprintf( __( 'View %s' ), $singular ),
      'update_item'                => sprintf( __( 'Update %s' ), $singular ),
      'add_new_item'               => sprintf( __( 'Add New %s' ), $singular ),
      'new_item_name'              => sprintf( __( 'New %s Name' ), $singular ),
      'search_items'               => sprintf( __( 'Search %s' ), $plural ),
      'parent_item'                => sprintf( __( 'Parent %s' ), $singular ),
      'parent_item_colon'          => sprintf( __( 'Parent %s:' ), $singular ),
      'popular_items'              => sprintf( __( 'Popular %s' ), $plural ),
      'separate_items_with_commas' => sprintf( __( 'Separate %s with commas' ), strtolower( $plural ) ),
      'add_or_remove_items'        => sprintf( __( 'Add/remove %s' ), strtolower( $plural ) ),
      'choose_from_most_used'      => sprintf( __( 'Choose from the most-used %s' ), strtolower( $plural ) ),
      'not_found'                  => sprintf( __( 'No %s found' ), strtolower( $plural ) )
    ];

    // Set default args
    $default_args = [
        'label'             => $plural,
        'labels'            => $labels,
        'public'            => true,
        'show_ui'           => true,
        'show_in_nav_menus' => true,
        'show_tagcloud'     => false,
        'meta_box_cb'       => NULL,
        'show_admin_column' => true,
        'hierarchical'      => false,
        'rewrite'           => [ 'slug' => $slug, 'with_front' => true ],
        'sort'              => false
    ];

    // Merge manual args
    $merged_args = array_replace_recursive( $default_args, $args );

    // Register the taxonomy
    register_taxonomy( $name, $post_types, $merged_args );

    // Manually register for given post types, to be thorough
    if ( isset( $post_types ) )
    {
      if ( is_array( $post_types ) )  // More than one post/object type
      {
        foreach ( $post_types as $post_type )
          register_taxonomy_for_object_type( $name, $post_type );
      }
      else                            // Just one post/object type
      {
        register_taxonomy_for_object_type( $name, $post_types );
      }
    }
  }
}
  