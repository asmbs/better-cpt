<?php

/**
 * ---------------------------------------------------------------------------------------------
 * CPT
 * ---------------------------------------------------------------------------------------------
 */
class CPT
{
  // -------------------------------------------------------------------------------------------

  /**
   * @var  string  The post type identifier.
   */
  public $post_type;

  /**
   * @var  string  A singular label for the post type.
   */
  public $singular;

  /**
   * @var  string  A plural label for the post type.
   */
  public $plural;

  /**
   * @var  array  A set of argument overrides.
   */
  public $args;

  // -------------------------------------------------------------------------------------------

  /**
   * ::__construct()
   *
   * Sets post type properties and calls set_hooks() to register additional hooks.
   * This method is final and cannot be overridden.
   * 
   * @param  string  $post_type
   * @param  string  $singular
   * @param  string  $plural
   * @param  array   $args
   *
   */
  public final function __construct( $post_type, $singular, $plural, $args = [] )
  {
    // Set post type properties
    $this->post_type = $post_type;
    $this->singular = $singular;
    $this->plural = $plural;
    $this->args = $args;

    // Register post type hooks
    $this->set_hooks();
  }

  /**
   * ::register()
   *
   * Actually registers the post type. Should be called within an `init` hook.
   * This method is final and cannot be overridden.
   *
   */
  public final function register()
  {
    quick_register_post_type( $this->post_type, $this->singular, $this->plural, $this->args );
  }

  // -------------------------------------------------------------------------------------------

  /**
   * ::set_hooks()
   *
   * Override this method in your child class to register hooks for managing metadata, post
   * list columns, help tabs, etc.
   *
   */
  protected function set_hooks()
  {}

  // -------------------------------------------------------------------------------------------
}
