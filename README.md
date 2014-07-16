# Better Custom Post Types
_...and taxonomies too!_

**It's a plugin for your plugins.**

It's all the functionality you need to build robust custom post types and taxonomies, and nothing you don't.

-----

## How to use it
1.  Install this plugin like ya do, either as a normal plugin or as an [MU (must-use)][mu-plugins] plugin. We'd recommend the latter so it's guaranteed to be active — some pretty ugly things can happen if your plugin depends on it and it gets deactivated.
2.  Develop your plugin(s) separately — _not_ on top of ours.
3.  Celebrate.

## That's it?
Yep, Oh, but we definitely do recommend using [Advanced Custom Fields][acf] — especially the **pro** version — for all your post metadata needs. You're not even gonna believe how sweet it is. (No, we're not affiliated, we just know a bad-ass WordPress plugin when we see it.)

## The classes

### `CPT`
**Default usage:**
```php
$thing = new CPT(
  'thing',
  __( 'Thing' ),
  __( 'Things' )
);

// From inside an init hook
$thing->register();
```

Use it as-is for a plain-jane custom post type, or extend it and do great and powerful things. Just override `set_hooks()`, register some hooks in there, implement them, and _boom_. Maximum custom awesomeness, minimum effort.

### `Taxonomy`
**Default usage:**
```php
$food_group = new Taxonomy(
  'food-group',
  __( 'Food Group' ),
  __( 'Food Groups' ),
  [ 'thing' ]
);

// From inside an init hook
$food_group->register();
```

When you're looking at a taxonomy in `wp-admin`, the count WordPress offers by default doesn't care about what post type you're under, so you get a total of _all_ the posts of _all_ post types with the given term, which is really annoying. The vanilla `Taxonomy` class replaces that behavior with a post count that's actually correct.

[mu-plugins]: http://codex.wordpress.org/Must_Use_Plugins
[acf]: http://advancedcustomfields.com
