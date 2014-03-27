<?php
/**
 * Plugin Name: Better Custom Post Types
 * Plugin URI:  https://github.com/asmbs/better-cpt
 * Description: A plugin for your plugins. Just extend the WP_CPT class in your own plugin(s) for a clean, object-oriented way to encapsulate your custom post type's functionality.
 * 
 * Author:      ASMBS
 * Author URI:  https://githun.com/asmbs
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
 *      that you not override it, but you can. If you do, be sure to either manually override
 *      the hooks, or call parent::__construct() at the beginning of your constructor, to
 *      minimize the potential for missing stuff.
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

  }
  
  // ===========================================================================================
}
