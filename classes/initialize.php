<?php

if ( preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']) ) {
     die('You are not allowed to call this page directly.');
}

/**
 * initialize.php - Initialize the post types and taxonomies
 *
 * @package CalendarPress
 * @subpackage includes
 * @author GrandSlambert
 * @copyright 2009-2011
 * @access public
 * @since 0.1
 */
class calendar_press_init extends calendar_press_core {

     static $instance;

     /**
      * Initialize the class.
      *
      * @global object $wpdb
      */
     public function __construct() {
          parent::__construct();

          add_action('init', array($this, 'setup_taxonomies'));
          add_action('init', array($this, 'create_event_type'));

          if ( $this->options['use-locations'] ) {
               add_action('init', array($this, 'create_location_type'));
          }

          add_filter('manage_edit-event_columns', array(&$this, 'event_edit_columns'));
          add_action("manage_posts_custom_column", array(&$this, 'event_custom_columns'));

          if ( $this->options['use-post-categories'] ) {
               register_taxonomy_for_object_type('post_categories', 'event');
          }

          if ( $this->options['use-post-tags'] ) {
               register_taxonomy_for_object_type('post_tag', 'event');
          }

          /* Check if an update is needed. */
          global $wpdb;

          $query = "SELECT * FROM `$wpdb->usermeta` WHERE `meta_key` LIKE '_event_signups' OR `meta_key` LIKE '_event_overflow'";
          $old_signups = $wpdb->get_results($query);

          if ( count($old_signups) >= 1 ) {
               add_action('admin_menu', array(&$this, 'add_upgrade_menu_option'));

               if ( !preg_match('/calendar-press-update/', $_SERVER['QUERY_STRING']) ) {
                    add_action('admin_notices', array(&$this, 'upgrade_warning'));
               }
          }
     }

     /**
      * Initialize the shortcodes.
      */
     static function initialize() {
          $instance = self::get_instance();
     }

     /**
      * Returns singleton instance of object
      *
      * @return instance
      */
     static function get_instance() {
          if ( is_null(self::$instance) ) {
               self::$instance = new calendar_press_init();
          }
          return self::$instance;
     }

     /**
      *  Puts a warning on the admin side that an update is needed.
      */
     function upgrade_warning() {
          echo "<div id='calendar-press-warning' class='updated fade'><p><strong>" . __('CalendarPress requires an update.') . "</strong> " . sprintf(__('You must <a href="%1$s">run the update tool</a> for it to work.'), admin_url() . "edit.php?post_type=event&page=calendar-press-update") . "</p></div>";
     }

     /**
      * Adds an option to the plugins menu to perform the update to version 2.0
      */
     function add_upgrade_menu_option() {
          add_submenu_page('edit.php?post_type=event', __('Update'), __('Update'), 'manage_options', 'calendar-press-update', array(&$this, 'update_function'));
     }

     /**
      * Runs any necessary update functions.
      */
     function update_function() {
          global $wpdb;

          echo '<div class="wrap">
               <div class="icon32" id="icon-calendar-press"><br/></div>
               <h2>' . $this->pluginName . ' &raquo; ' . __('Update to version 0.4', 'recipe-press') . '</h2>
               <ol>';

          $query = "SELECT * FROM `$wpdb->usermeta` WHERE `meta_key` LIKE '_event_signups' OR `meta_key` LIKE '_event_overflow'";
          $old_signups = $wpdb->get_results($query);

          /* Convert old signups if there are any */
          if ( count($old_signups) >= 1 ) {
               echo '<li>' . __('Converting old style signups to new style<ol>', 'calendar-press');
               $events = array();

               foreach ( $old_signups as $signup ) {
                    switch ($signup->meta_key) {
                         case '_event_overflow':
                              print "<li>Converting overflow registrations for user ID $signup->user_id, event IDs: ";
                              $meta_key = '_event_registrations_overflow';
                              break;
                         default:
                              print "<li>Converting signup registrations for user ID $signup->user_id, event IDs: ";
                              $meta_key = '_event_registrations_signups';
                    }

                    foreach ( unserialize($signup->meta_value) as $event ) {
                         if ( $event != 0 ) {
                              print "$event, ";

                              if ( !array_key_exists($event, $events) ) {
                                   $events[$event][$meta_key] = get_post_meta($event, $meta_key, true);
                              }

                              if ( is_array($events[$event]) and (!array_key_exists($signup->user_id, $events[$event])) ) {
                                   $events[$event][$meta_key][$signup->user_id]['date'] = current_time('timestamp');
                              }
                         }
                    }

                    print "</li>";
               }

               foreach ( $events as $id => $event ) {
                    if ( isset($event['_event_registrations_signups']) ) {
                         update_post_meta($id, '_event_registrations_signups', $event['_event_registrations_signups']);
                    }
                    if ( isset($event['_event_registrations_overflow']) ) {
                         update_post_meta($id, '_event_registrations_overflow', $event['_event_registrations_overflow']);
                    }
               }

               $query = "DELETE FROM `$wpdb->usermeta` WHERE `meta_key` LIKE '_event_signups' OR `meta_key` LIKE '_event_overflow'";
               $wpdb->get_results($query);

               echo '</ol></li>';
          }

          echo '<li>All processes complete.</li>';
          echo '</ol>';
     }

     /**
      * Register the post type for the plugin.
      *
      * @global object $wp_version
      * @global $wp_version $wp_rewrite
      */
     function create_event_type() {
          global $wp_version;

          $labels = array(
               'name' => $this->options['plural-name'],
               'singular_name' => $this->options['singular-name'],
               'add_new' => __('New Event', 'calendar-press'),
               'add_new_item' => sprintf(__('Add New %1$s', 'calendar-press'), $this->options['singular-name']),
               'edit_item' => sprintf(__('Edit %1$s', 'calendar-press'), $this->options['singular-name']),
               'edit' => __('Edit', 'calendar-press'),
               'new_item' => sprintf(__('New %1$s', 'calendar-press'), $this->options['singular-name']),
               'view_item' => sprintf(__('View %1$s', 'calendar-press'), $this->options['singular-name']),
               'search_items' => sprintf(__('Search %1$s', 'calendar-press'), $this->options['singular-name']),
               'not_found' => sprintf(__('No %1$s found', 'calendar-press'), $this->options['plural-name']),
               'not_found_in_trash' => sprintf(__('No %1$s found in Trash', 'calendar-press'), $this->options['plural-name']),
               'view' => sprintf(__('View %1$s', 'calendar-press'), $this->options['singular-name']),
               'parent_item_colon' => ''
          );
          $args = array(
               'labels' => $labels,
               'public' => true,
               'publicly_queryable' => true,
               'show_ui' => true,
               'show_in_menu' => true,
               'query_var' => true,
               'rewrite' => false,
               'capability_type' => 'post',
               'hierarchical' => false,
               'description' => __('Post type created by CalendarPress for events.', 'calendar-press'),
               'menu_position' => 5,
               'menu_icon' => $this->options['menu-icon'],
               'supports' => array('title', 'editor', 'author', 'excerpt'),
               'register_meta_box_cb' => array(&$this, 'init_metaboxes'),
          );

          if ( $this->options['use-custom-fields'] ) {
               $args['supports'][] = 'custom-fields';
          }

          if ( $this->options['use-thumbnails'] ) {
               $args['supports'][] = 'thumbnail';
          }

          if ( $this->options['use-comments'] ) {
               $args['supports'][] = 'comments';
          }

          if ( $this->options['use-trackbacks'] ) {
               $args['supports'][] = 'trackbacks';
          }

          if ( $this->options['use-revisions'] ) {
               $args['supports'][] = 'revisions';
          }

          if ( $this->options['use-post-tags'] ) {
               $args['taxonomies'][] = 'post_tag';
          }

          if ( $this->options['use-post-categories'] ) {
               $args['taxonomies'][] = 'category';
          }

          /* Set up rewrite rules */
          if ( version_compare($wp_version, '3.0.999', '>') and !$this->options['use-plugin-permalinks'] ) {
               $args['rewrite'] = true;
               $args['has_archive'] = $this->options['index-slug'];
          } else {
               $args['rewrite'] = false;
               $args['has_archive'] = false;

               /* Flush the rewrite rules */
               global $wp_rewrite;
               $this->calendar_press_rewrite_rules(array('slug' => $this->options['index-slug'], 'identifier' => $this->options['identifier'], 'structure' => $this->options['permalink'], 'type' => 'event'));

               $wp_rewrite->flush_rules();
               add_filter('post_type_link', array($this, 'event_post_link'), 10, 3);
          }

          register_post_type('event', $args);
     }

     /**
      * Register the post type for the locations.
      *
      * @global object $wp_version
      * @global $wp_version $wp_rewrite
      */
     function create_location_type() {
          global $wp_version;

          $labels = array(
               'name' => $this->options['location-plural-name'],
               'singular_name' => $this->options['location-singular-name'],
               'add_new' => __('New Location', 'calendar-press'),
               'add_new_item' => sprintf(__('Add New %1$s', 'calendar-press'), $this->options['location-singular-name']),
               'edit_item' => sprintf(__('Edit %1$s', 'calendar-press'), $this->options['location-singular-name']),
               'edit' => __('Edit', 'calendar-press'),
               'new_item' => sprintf(__('New %1$s', 'calendar-press'), $this->options['location-singular-name']),
               'view_item' => sprintf(__('View %1$s', 'calendar-press'), $this->options['location-singular-name']),
               'search_items' => sprintf(__('Search %1$s', 'calendar-press'), $this->options['location-singular-name']),
               'not_found' => sprintf(__('No %1$s found', 'calendar-press'), $this->options['location-plural-name']),
               'not_found_in_trash' => sprintf(__('No %1$s found in Trash', 'calendar-press'), $this->options['location-plural-name']),
               'view' => sprintf(__('View %1$s', 'calendar-press'), $this->options['location-singular-name']),
               'parent_item_colon' => ''
          );
          $args = array(
               'labels' => $labels,
               'public' => true,
               'publicly_queryable' => true,
               'show_ui' => true,
               'show_in_menu' => 'edit.php?post_type=event',
               'query_var' => true,
               'rewrite' => false,
               'capability_type' => 'post',
               'hierarchical' => false,
               'description' => __('Post type created by CalendarPress for locations.', 'calendar-press'),
               'menu_position' => 5000,
               'menu_icon' => $this->options['menu-icon'],
               'supports' => array('title', 'editor', 'author', 'excerpt'),
               'register_meta_box_cb' => array(&$this, 'init_location_metaboxes'),
          );

          if ( $this->options['use-custom-fields'] ) {
               $args['supports'][] = 'custom-fields';
          }

          if ( $this->options['use-thumbnails'] ) {
               $args['supports'][] = 'thumbnail';
          }

          if ( $this->options['use-comments'] ) {
               $args['supports'][] = 'comments';
          }

          if ( $this->options['use-trackbacks'] ) {
               $args['supports'][] = 'trackbacks';
          }

          if ( $this->options['use-revisions'] ) {
               $args['supports'][] = 'revisions';
          }

          if ( $this->options['use-post-tags'] ) {
               $args['taxonomies'][] = 'post_tag';
          }

          if ( $this->options['use-post-categories'] ) {
               $args['taxonomies'][] = 'category';
          }

          /* Set up rewrite rules */
          if ( version_compare($wp_version, '3.0.999', '>') and !$this->options['use-plugin-permalinks'] ) {
               $args['rewrite'] = true;
               $args['has_archive'] = $this->options['location-slug'];
          } else {
               $args['rewrite'] = false;
               $args['has_archive'] = false;

               /* Flush the rewrite rules */
               global $wp_rewrite;
               $this->calendar_press_rewrite_rules(array('slug' => $this->options['location-slug'], 'identifier' => $this->options['location-identifier'], 'structure' => $this->options['location-permalink'], 'type' => 'event-location'));

               $wp_rewrite->flush_rules();
               add_filter('post_type_link', array($this, 'location_post_link'), 10, 3);
          }

          register_post_type('event-location', $args);
     }

     /**
      * Rewrite rules for custom event permalinks.
      *
      * @global  $wp_rewrite
      * @param array $permastructure (identifier, structure, type)
      */
     function calendar_press_rewrite_rules($permastructure) {
          global $wp_rewrite;
          $structure = $permastructure['structure'];
          $front = substr($structure, 0, strpos($structure, '%'));
          $type_query_var = $permastructure['type'];
          $structure = str_replace('%identifier%', $permastructure['identifier'], $structure);
          $rewrite_rules = $wp_rewrite->generate_rewrite_rules($structure, EP_NONE, true, true, true, true, true);

          //build a rewrite rule from just the identifier if it is the first token
          preg_match('/%.+?%/', $permastructure['structure'], $tokens);
          if ( $tokens[0] == '%identifier%' ) {
               $rewrite_rules = array_merge($wp_rewrite->generate_rewrite_rules($front . $permastructure['slug'] . '/'), $rewrite_rules);
               $rewrite_rules[$front . $permastructure['slug'] . '/?$'] = 'index.php?paged=1';
          }

          foreach ( $rewrite_rules as $regex => $redirect ) {
               if ( strpos($redirect, 'attachment=') === false ) {
                    //don't set the post_type for attachments
                    $redirect .= '&post_type=' . $permastructure['type'];
               }

               if ( 0 < preg_match_all('@\$([0-9])@', $redirect, $matches) ) {
                    for ( $i = 0; $i < count($matches[0]); $i++ ) {
                         $redirect = str_replace($matches[0][$i], '$matches[' . $matches[1][$i] . ']', $redirect);
                    }
               }

               $redirect = str_replace('name=', $type_query_var . '=', $redirect);

               add_rewrite_rule($regex, $redirect, 'top');
          }
     }

     function event_post_link($permalink, $id, $leavename = false) {
          if ( is_object($id) && isset($id->filter) && 'sample' == $id->filter ) {
               $post = $id;
          } else {
               $post = &get_post($id);
          }

          if ( empty($post->ID) || $post->post_type != 'event' ) {
               return $permalink;
          }

          $permastructure = array('identifier' => $this->options['identifier'], 'structure' => $this->options['permalink']);
          return $this->rewrite_post_link($permastructure, $permalink, $id, $leavename);
     }

     function location_post_link($permalink, $id, $leavename = false) {
          if ( is_object($id) && isset($id->filter) && 'sample' == $id->filter ) {
               $post = $id;
          } else {
               $post = &get_post($id);
          }

          if ( empty($post->ID) || $post->post_type != 'event-location' ) {
               return $permalink;
          }

          $permastructure = array('identifier' => $this->options['location-identifier'], 'structure' => $this->options['location-permalink']);
          return $this->rewrite_post_link($permastructure, $permalink, $id, $leavename);
     }

     /**
      * Permalink handling for post types
      *
      * @param string $permalink
      * @param object $post
      * @param bool $leavename
      * @return string
      */
     function rewrite_post_link($permastructure, $permalink, $id, $leavename = false) {
          if ( is_object($id) && isset($id->filter) && 'sample' == $id->filter ) {
               $post = $id;
          } else {
               $post = &get_post($id);
          }

          $rewritecode = array(
               '%identifier%',
               '%year%',
               '%monthnum%',
               '%day%',
               '%hour%',
               '%minute%',
               '%second%',
               $leavename ? '' : '%postname%',
               '%post_id%',
               '%category%',
               '%author%',
               $leavename ? '' : '%pagename%',
          );

          $identifier = $permastructure['identifier'];
          $permalink = $permastructure['structure'];
          if ( '' != $permalink && !in_array($post->post_status, array('draft', 'pending', 'auto-draft')) ) {

               if ( !$unixtime = get_post_meta($post->ID, '_begin_time_value', true) ) {
                    $unixtime = strtotime($post->post_date);
               }

               $category = '';
               if ( strpos($permalink, '%category%') !== false ) {
                    $cats = get_the_terms($post->ID, 'event-categories');
                    if ( $cats ) {
                         usort($cats, '_usort_terms_by_ID'); // order by ID
                         $category = $cats[0]->slug;
                         if ( $parent = $cats[0]->parent )
                              $category = get_category_parents($parent, false, '/', true) . $category;
                    }
                    // show default category in permalinks, without
                    // having to assign it explicitly
                    if ( empty($category) ) {
                         $default_category = get_category(get_option('default_category'));
                         $category = is_wp_error($default_category) ? '' : $default_category->slug;
                    }
               }

               $author = '';
               if ( strpos($permalink, '%author%') !== false ) {
                    $authordata = get_userdata($post->post_author);
                    $author = $authordata->user_nicename;
               }

               $date = explode(" ", date('Y m d H i s', $unixtime));
               $rewritereplace =
                       array(
                            $identifier,
                            $date[0],
                            $date[1],
                            $date[2],
                            $date[3],
                            $date[4],
                            $date[5],
                            $post->post_name,
                            $post->ID,
                            $category,
                            $author,
                            $post->post_name,
               );
               $permalink = home_url(str_replace($rewritecode, $rewritereplace, $permalink));
               $permalink = user_trailingslashit($permalink, 'single');
          } else {
               $permalink = home_url('?p=' . $post->ID . '&post_type=' . urlencode('event'));
          }
          return $permalink;
     }

     /**
      * Adds extra columns to the edit screen.
      *
      * @param string $columns
      * @return string
      */
     function event_edit_columns($columns) {
          $columns = array(
               'cb' => '<input type="checkbox" />',
               'thumbnail' => __('Image', 'calendar-press'),
               'title' => __('Event Title', 'calendar-press'),
               'intro' => __('Introduction', 'calendar-press'),
               'category' => __('Categories', 'calendar-press'),
               'tag' => __('Tags', 'calendar-press'),
               'signups' => __('Signups', 'calendar-press'),
               'overflow' => __('Overflow', 'calendar-press'),
               'featured' => __('Featured', 'calendar-press'),
               'author' => __('Author', 'calendar-press')
          );

          if ( $this->options['use-comments'] ) {
               $columns['comments'] = '<img src="' . get_option('siteurl') . '/wp-admin/images/comment-grey-bubble.png" alt="Comments">';
          }

          $columns['date'] = 'Date';


          return $columns;
     }

     /**
      * Handles display of custom columns
      *
      * @global object $post
      * @param string $column
      * @return string
      */
     function event_custom_columns($column) {
          global $post;

          if ( $post->post_type != 'event' ) {
               return;
          }

          switch ($column) {
               case 'thumbnail':
                    if ( function_exists('has_post_thumbnail') && has_post_thumbnail() ) {
                         the_post_thumbnail(array(50, 50));
                    }
                    break;
               case 'intro':
                    echo cp_inflector::trim_excerpt($post->post_excerpt, 25);
                    break;
               case 'featured':
                    if ( get_post_meta($post->ID, '_event_featured_value', true) ) {
                         _e('Yes', 'calendar-press');
                    } else {
                         _e('No', 'calendar-press');
                    }
                    break;
               case 'category':
                    echo the_terms($post->ID, 'event-category', '', ', ', '');
                    break;
               case 'tag':
                    echo the_terms($post->ID, 'event-tag', '', ', ', '');
                    break;
               case 'signups':
                    $available = get_post_meta($post->ID, '_event_signups_value', true);
                    $signups = get_post_meta($post->ID, '_event_registrations_signups', true);
                    echo count($signups) . ' of ' . $available;
                    break;
               case 'overflow':
                    $available = get_post_meta($post->ID, '_event_overflow_value', true);
                    $overflow = get_post_meta($post->ID, '_event_registrations_overflow', true);
                    echo count($overflow) . ' of ' . $available;
                    break;
          }
     }

     /**
      * Set up all taxonomies.
      */
     function setup_taxonomies() {
          if ( !$this->options['use-taxonomies'] ) {
               return;
          }

          foreach ( $this->options['taxonomies'] as $key => $taxonomy ) {

               if ( isset($taxonomy['active']) and isset($taxonomy['plural']) ) {
                    $labels = array(
                         'name' => $taxonomy['plural'],
                         'singular_name' => $taxonomy['singular'],
                         'search_items' => sprintf(__('Search %1$s', 'calendar-press'), $taxonomy['plural']),
                         'popular_items' => sprintf(__('Popular %1$s', 'calendar-press'), $taxonomy['plural']),
                         'all_items' => sprintf(__('All %1$s', 'calendar-press'), $taxonomy['plural']),
                         'parent_item' => sprintf(__('Parent %1$s', 'calendar-press'), $taxonomy['singular']),
                         'edit_item' => sprintf(__('Edit %1$s', 'calendar-press'), $taxonomy['singular']),
                         'update_item' => sprintf(__('Update %1$s', 'calendar-press'), $taxonomy['singular']),
                         'add_new_item' => sprintf(__('Add %1$s', 'calendar-press'), $taxonomy['singular']),
                         'new_item_name' => sprintf(__('New %1$s', 'calendar-press'), $taxonomy['singular']),
                         'add_or_remove_items' => sprintf(__('Add ore remove %1$s', 'calendar-press'), $taxonomy['plural']),
                         'choose_from_most_used' => sprintf(__('Choose from the most used %1$s', 'calendar-press'), $taxonomy['plural'])
                    );

                    $args = array(
                         'hierarchical' => isset($taxonomy['hierarchical']),
                         'label' => $taxonomy['plural'],
                         'labels' => $labels,
                         'public' => true,
                         'show_ui' => true,
                         'rewrite' => true
                    );

                    register_taxonomy($key, array('event'), $args);
               }
          }
     }

     /**
      * Add all of the needed meta boxes to the edit screen.
      */
     function init_metaboxes() {
          add_meta_box('events_details', __('Details', 'calendar-press'), array(&$this, 'details_box'), 'event', 'side', 'high');
          add_meta_box('events_dates', __('Date and Time', 'calendar-press'), array(&$this, 'date_box'), 'event', 'side', 'high');

          if ( $this->options['registration-type'] != 'none' ) {
               add_meta_box('events_signup', __('Registration Settings', 'calendar-press'), array(&$this, 'signup_box'), 'event', 'side', 'high');
          }

          if ( $this->options['use-locations'] ) {
               add_meta_box('events_location', __('Event Location', 'calendar-press'), array(&$this, 'location_box'), 'event', 'side', 'high');
          }
     }

     /**
      * Add all of the needed meta boxes to the edit screen.
      */
     function init_location_metaboxes() {
          add_meta_box('location_details', __('Location Details', 'calendar-press'), array(&$this, 'location_details_box'), 'event-location', 'side', 'high');
     }

     /**
      * Add the details box.
      * @global object $post
      */
     function details_box() {
          global $post;
          include ($this->pluginPath . '/includes/meta-boxes/details-box.php');
     }

     /**
      * Add the date box.
      *
      * @global object $post
      */
     function date_box() {
          global $post;
          include ($this->pluginPath . '/includes/meta-boxes/date-box.php');
     }

     /**
      * Add the signup settings box.
      * @global object $post
      */
     function signup_box() {
          global $post;

          if ( !$signup_type = get_post_meta($post->ID, '_registration_type_value', true) ) {
               $signup_type = $this->options['registration-type'];
          }

          if ( !$signupCount = get_post_meta($post->ID, '_event_signups_value', true) ) {
               $signupCount = $this->options['signups-default'];
          }

          if ( !$overflowCount = get_post_meta($post->ID, '_event_overflow_value', true) ) {
               $overflowCount = $this->options['overflow-default'];
          }

          include ($this->pluginPath . '/includes/meta-boxes/signups-box.php');
     }

     /**
      * Add the location box.
      * 
      * @global object $post
      */
     function location_box() {
          global $post;
          include ($this->pluginPath . '/includes/meta-boxes/locations-box.php');
     }

     /**
      * Add the location details box.
      * @global object $post
      */
     function location_details_box() {
          global $post;
          include ($this->pluginPath . '/includes/meta-boxes/location-details-box.php');
     }
}