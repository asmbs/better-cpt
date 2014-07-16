<?php

/**
 * ---------------------------------------------------------------------------------------------
 * Taxonomy
 * ---------------------------------------------------------------------------------------------
 */
class Taxonomy
{
  // -------------------------------------------------------------------------------------------

  /**
   * @var  string  The identifier (name) for this taxonomy.
   */
  public $name;

  /**
   * @var  string  The singular label for this taxonomy.
   */
  public $singular;

  /**
   * @var  string  The plural label for this taxonomy.
   */
  public $plural;

  /**
   * @var  string|array  The post/object types to associate this taxonomy with.
   */
  public $post_types;

  /**
   * @var  array  A list of argument overrides to apply when registering.
   */
  public $args;

  // -------------------------------------------------------------------------------------------

  /**
   * ::__construct()
   *
   * Sets class properties and registers default hooks.
   *
   * @param  string        $name
   * @param  string        $singular
   * @param  string        $plural
   * @param  string|array  $post_types
   * @param  array         $args
   *
   */
  public final function __construct( $name, $singular, $plural, $post_types = NULL, $args = [] )
  {
    // Set class properties
    $this->name = $name;
    $this->singular = $singular;
    $this->plural = $plural;
    $this->post_types = $post_types;
    $this->args = $args;

    // Manage custom columns for the term list table
    add_filter( 'manage_edit-'. $this->name .'_columns', [ $this, 'custom_columns' ] );
    add_filter( 'manage_'. $this->name .'_custom_column', [ $this, 'print_custom_columns' ], 10, 3 );
  }

  /**
   * ::register()
   *
   * Registers the taxonomy. Call this method from an `init` hook.
   *
   */
  public final function register()
  {
    quick_register_taxonomy( $this->name, $this->singular, $this->plural, $this->post_types, $this->args );
  }

  // -------------------------------------------------------------------------------------------

  /**
   * ::custom_columns()
   *
   * Adds custom columns to the term list for this taxonomy.
   * Filter: `manage_edit-{$taxonomy}_columns`
   *
   * @param   array  $columns  The column list.
   * @return  array            The updated column list.
   *
   */
  public function custom_columns( $columns )
  {
    // Get screen
    $screen = get_current_screen();

    // Remove description column
    unset( $columns['description'] );

    // Replace post count column with a custom count column
    if ( isset( $screen->post_type ) )
    {
      // Get post type details
      $post_type = get_post_type_object( $screen->post_type );

      // Remove the default post column
      unset( $columns['posts'] );

      // Add the new custom column
      $columns['post_count'] = $post_type->label;
    }

    // Return the modified column list
    return $columns;
  }

  /**
   * ::print_custom_columns()
   *
   * Filter: `manage_{$taxonomy}_custom_column`
   *
   * @param   string  $output   The current HTML content of the cell.
   * @param   string  $column   The column name.
   * @param   int     $term_ID  The ID of the current term.
   * @return  string            The new HTML content to be printed in the given cell.
   *
   */
  public function print_custom_columns( $output, $column, $term_ID )
  {
    // Get screen
    $screen = get_current_screen();

    // Get details for the current term ID
    $term = get_term( $term_ID, $this->name );

    // Print an accurate post count by post type
    if ( $column == 'post_count' )
    {
      // Query for posts
      $query = new WP_Query( [
        'post_type'   => $screen->post_type,
        'post_status' => 'publish',
        'tax_query'   => [
          [
            'taxonomy' => $this->name,
            'terms'    => $term_ID
          ]
        ]
      ] );
      
      // Get count
      $count = $query->found_posts;

      // Generate edit URL
      $edit_link = admin_url( add_query_arg(
        [
          'post_type' => $screen->post_type,
          $this->name => $term->slug
        ],
        'edit.php'
      ) );

      $output = sprintf( '<a href="%1$s">%2$s</a>', $edit_link, $count );
    }

    // Return the cell content
    return $output;
  }

  // -------------------------------------------------------------------------------------------
}
