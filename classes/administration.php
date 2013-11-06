<?php

if ( preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']) ) {
     die('You are not allowed to call this page directly.');
}

/**
 * administration.php - Administration Functions
 *
 * @package CalendarPress
 * @subpackage classes
 * @author GrandSlambert
 * @copyright 2009-2011
 * @access public
 * @since 0.4
 */
class calendar_press_admin extends calendar_press_core {

     static $instance;

     /**
      * Initialize the plugin.
      */
     function __construct() {
          parent::__construct();

          /* Add actions */
          add_action('admin_menu', array(&$this, 'add_admin_pages'));
          add_action('admin_init', array(&$this, 'admin_init'));
          add_action('admin_print_styles', array(&$this, 'admin_print_styles'));
          add_action('admin_print_scripts', array(&$this, 'admin_print_scripts'));
          add_action('save_post', array(&$this, 'save_event'));
          add_action('update_option_' . $this->optionsName, array(&$this, 'update_option'));
          add_action('right_now_content_table_end', array(&$this, 'right_now_content_table_end'));
          add_action('in_admin_footer', array(&$this, 'in_admin_footer'));

          /* Add filters */
          add_filter('plugin_action_links', array(&$this, 'add_configure_link'), 10, 2);
     }

     /**
      * Initialize the administration area.
      */
     public static function initialize() {
          $instance = self::get_instance();
     }

     /**
      * Returns singleton instance of object
      *
      * @return instance
      */
     protected static function get_instance() {
          if ( is_null(self::$instance) ) {
               self::$instance = new calendar_press_admin;
          }
          return self::$instance;
     }

     /**
      * Add the number of events to the Right Now on the Dasboard.
      */
     public function right_now_content_table_end() {
          if ( !post_type_exists('event') ) {
               return false;
          }

          /* Show for events */
          $num_posts = wp_count_posts('event');
          $num = number_format_i18n($num_posts->publish);
          $text = _n('Event', 'Events', intval($num_posts->publish));
          if ( current_user_can('edit_posts') ) {
               $num = "<a href='edit.php?post_type=event'>$num</a>";
               $text = "<a href='edit.php?post_type=event'>$text</a>";
          }
          echo '<td class="first b b-event">' . $num . '</td>';
          echo '<td class="t events">' . $text . '</td>';

          echo '</tr>';

          if ( $num_posts->pending > 0 ) {
               $num = number_format_i18n($num_posts->pending);
               $text = _n('Event Pending', 'Events Pending', intval($num_posts->pending));
               if ( current_user_can('edit_posts') ) {
                    $num = "<a href='edit.php?post_status=pending&post_type=events'>$num</a>";
                    $text = "<a href='edit.php?post_status=pending&post_type=events'>$text</a>";
               }
               echo '<td class="first b b-events">' . $num . '</td>';
               echo '<td class="t events">' . $text . '</td>';

               echo '</tr>';
          }

          /* Show for locations */
          if ( $this->options['use-locations'] ) {
               $num_posts = wp_count_posts('event-location');
               $num = number_format_i18n($num_posts->publish);
               $text = _n('Location', 'Locations', intval($num_posts->publish));
               if ( current_user_can('edit_posts') ) {
                    $num = "<a href='edit.php?post_type=event-location'>$num</a>";
                    $text = "<a href='edit.php?post_type=event-location'>$text</a>";
               }
               echo '<td class="first b b-event">' . $num . '</td>';
               echo '<td class="t events">' . $text . '</td>';

               echo '</tr>';

               if ( $num_posts->pending > 0 ) {
                    $num = number_format_i18n($num_posts->pending);
                    $text = _n('Location Pending', 'Locations Pending', intval($num_posts->pending));
                    if ( current_user_can('edit_posts') ) {
                         $num = "<a href='edit.php?post_status=pending&post_type=event-location'>$num</a>";
                         $text = "<a href='edit.php?post_status=pending&post_type=event-location'>$text</a>";
                    }
                    echo '<td class="first b b-events">' . $num . '</td>';
                    echo '<td class="t events">' . $text . '</td>';

                    echo '</tr>';
               }
          }
     }

     /**
      * Add the admin page for the settings panel.
      *
      * @global string $wp_version
      */
     function add_admin_pages() {
          $pages = array();

          $pages[] = add_submenu_page('edit.php?post_type=event', $this->pluginName . __(' Settings', 'calendar-press'), __('Settings', 'calendar-press'), 'manage_options', 'calendar-press-settings', array(&$this, 'settings'));
          $pages[] = add_submenu_page('edit.php?post_type=event', $this->pluginName . __(' Contributors', 'calendar-press'), __('Contributors', 'calendar-press'), 'manage_options', 'calendar-press-contributors', array(&$this, 'credits_page'));

          foreach ( $pages as $page ) {
               add_action('admin_print_styles-' . $page, array(&$this, 'admin_styles'));
               add_action('admin_print_scripts-' . $page, array(&$this, 'admin_scripts'));
          }
     }

     /**
      * Register the options
      */
     function admin_init() {
          global $wp_version;

          register_setting($this->optionsName, $this->optionsName);
          wp_register_style('calendar-press-admin-css', $this->pluginURL . '/includes/calendar-press-admin.css');
          wp_register_style('calendar-press-datepicker-css', $this->get_template('datepicker', '.css', 'url'));
          wp_register_script('calendar-press-admin-js', $this->pluginURL . '/js/calendar-press-admin.js');
          wp_register_script('calendar-press-overlib-js', $this->pluginURL . '/js/overlib/overlib.js');
          wp_register_script('calendar-press-datepicker-js', $this->pluginURL . '/js/datepicker/js/datepicker.js');

          /* Relocate the location menu */
          if ( $this->options['use-locations'] and version_compare($wp_version, '3.1', '<') ) {
               global $menu, $submenu;

               $submenu['edit.php?post_type=event'][(int) 11] = $submenu['edit.php?post_type=event-location'][5];
               $submenu['edit.php?post_type=event'][(int) 12] = $submenu['edit.php?post_type=event-location'][10];
               unset($menu[5000]);
               ksort($submenu['edit.php?post_type=event']);
          }
     }

     /**
      * Print admin stylesheets while editting events.
      *
      * @global object $post
      */
     function admin_print_styles() {
          global $post;

          if ( is_object($post) and $post->post_type == 'event' ) {
               $this->admin_styles();
               wp_enqueue_style('calendar-press-datepicker-css');
          }
     }

     /**
      * Print admin javascripts while editting posts.
      * @global object $post
      */
     function admin_print_scripts() {
          global $post;

          if ( is_object($post) and $post->post_type == 'event' ) {
               $this->admin_scripts();
               wp_enqueue_script('calendar-press-datepicker-js');
          }
     }

     /**
      * Print admin stylesheets for all plugin pages.
      */
     function admin_styles() {
          wp_enqueue_style('calendar-press-admin-css');
     }

     /**
      * Print admin javascripts for all plugin pages.
      */
     function admin_scripts() {
          wp_enqueue_script('calendar-press-admin-js');
          wp_enqueue_script('calendar-press-overlib-js');
     }

     /**
      * Add a configuration link to the plugins list.
      *
      * @staticvar object $this_plugin
      * @param <array> $links
      * @param <array> $file
      * @return <array>
      */
     function add_configure_link($links, $file) {
          static $this_plugin;

          if ( !$this_plugin ) {
               $this_plugin = plugin_basename(__FILE__);
          }

          if ( $file == $this_plugin ) {
               $settings_link = '<a href="' . get_admin_url() . 'edit.php?post_type=event&page=' . $this->menuName . '">' . __('Settings', 'calendar-press') . '</a>';
               array_unshift($links, $settings_link);
          }

          return $links;
     }

     /**
      * Settings management panel.
      */
     function settings() {
          global $blog_id, $wp_version;
          include($this->pluginPath . '/includes/settings.php');
     }

     /**
      * Credits panel.
      */
     function credits_page() {
          include($this->pluginPath . '/classes/credits.php');
     }

     /**
      * Convert old events management panel.
      */
     function convert() {
          if ( !$_POST ) {
               include($this->pluginPath . '/includes/convert.php');
               return;
          }

          require($this->pluginPath . '/includes/converter.php');
     }

     /**
      * Check on update option to see if we need to create any pages.
      * @param array $input
      */
     function update_option($input) {
          if ( $_REQUEST['confirm-reset-options'] ) {
               delete_option($this->optionsName);
               wp_redirect(admin_url('edit.php?post_type=event&page=calendar-press-settings&tab=' . $_POST['active_tab'] . '&reset=true'));
               exit();
          } else {
               if ( $_POST['dashboard_site'] != get_site_option('dashboard_blog') ) {
                    update_site_option('dashboard_blog', $_POST['dashboard_site']);
               }

               if ( $_POST['allow_moving_events'] ) {
                    add_site_option('allow_moving_events', true);
               } else {
                    delete_site_option('allow_moving_events', true);
               }

               wp_redirect(admin_url('edit.php?post_type=event&page=calendar-press-settings&tab=' . $_POST['active_tab'] . '&updated=true'));
               exit();
          }
     }

     /**
      * Save the meta boxes for a event.
      *
      * @global object $post
      * @param integer $post_id
      * @return integer
      */
     function save_event($post_id) {
          global $post;

          /* Save the dates */
          if ( isset($_POST['dates_noncename']) AND wp_verify_nonce($_POST['dates_noncename'], 'calendar_press_dates') ) {
               $details = $_POST['event_dates'];

               foreach ( $details as $key => $value ) {

                    list($type, $field) = split('_', $key);

                    if ( $field == 'time' ) {
                         $meridiem = $_POST[$type . 'Meridiem'];
                         $minutes = $_POST[$type . '_time_minutes'];
                         if ( $value < 12 ) {
                              $value = $value + $meridiem;
                         }
                         $dateType = "{$type}Date";
                         $value = $$dateType . ' ' . $value . ':' . $minutes;
                    }

                    if ( $key == 'begin_date' ) {
                         $beginDate = $value;
                    }

                    if ( $key == 'begin_time' ) {
                         $beginTime = $value;
                    }

                    if ( $key == 'end_date' ) {
                         $endDate = $value;
                    }

                    if ( $key == 'end_time' ) {
                         $endTime = $value;
                    }
                    $key = '_' . $key . '_value';
                    $value = strtotime($value);

                    if ( get_post_meta($post_id, $key) == "" ) {
                         add_post_meta($post_id, $key, $value, true);
                    } elseif ( $value != get_post_meta($post_id, $key . '_value', true) ) {
                         update_post_meta($post_id, $key, $value);
                    } elseif ( $value == "" ) {
                         delete_post_meta($post_id, $key, get_post_meta($post_id, $key, true));
                    }
               }
          }

          /* Save the details */
          if ( isset($_POST['details_noncename']) AND wp_verify_nonce($_POST['details_noncename'], 'calendar_press_details') ) {
               $input = $_POST['event_details'];

               $details = array('featured', 'popup');

               foreach ( $details as $detail ) {

                    $key = '_event_' . $detail . '_value';

                    if ( isset($input['event_' . $detail]) ) {
                         $value = $input['event_' . $detail];
                    } else {
                         $value = false;
                    }

                    if ( get_post_meta($post_id, $key) == "" ) {
                         add_post_meta($post_id, $key, $value, true);
                    } elseif ( $value != get_post_meta($post_id, $key, true) ) {
                         update_post_meta($post_id, $key, $value);
                    } elseif ( $value == false ) {
                         delete_post_meta($post_id, $key, get_post_meta($post_id, $key, true));
                    }
               }
          }

          /* Save the signups information */
          if ( isset($_POST['signups_noncename']) AND wp_verify_nonce($_POST['signups_noncename'], 'calendar_press_signups') ) {
               $input = $_POST['event_signups'];

               $fields = array('registration_type', 'event_signups', 'event_overflow', 'yes_option', 'no_option', 'maybe_option');

               foreach ( $fields as $field ) {

                    $key = '_' . $field . '_value';

                    if ( isset($input[$field]) ) {
                         $value = $input[$field];
                    } else {
                         $value = false;
                    }

                    if ( get_post_meta($post_id, $key) == "" ) {
                         add_post_meta($post_id, $key, $value, true);
                    } elseif ( $value != get_post_meta($post_id, $key, true) ) {
                         update_post_meta($post_id, $key, $value);
                    } elseif ( $value == "" ) {
                         delete_post_meta($post_id, $key, get_post_meta($post_id, $key, true));
                    }
               }
          }

          /* Save the location */
          if ( isset($_POST['location_noncename']) AND wp_verify_nonce($_POST['location_noncename'], 'calendar_press_location') ) {
               $input = $_POST['event_location'];

               $fields = array('registration_type', 'event_location', 'event_overflow', 'yes_option', 'no_option', 'maybe_option');

               foreach ( $fields as $field ) {

                    $key = '_' . $field . '_value';

                    if ( isset($input[$field]) ) {
                         $value = $input[$field];
                    } else {
                         $value = false;
                    }

                    if ( get_post_meta($post_id, $key) == "" ) {
                         add_post_meta($post_id, $key, $value, true);
                    } elseif ( $value != get_post_meta($post_id, $key, true) ) {
                         update_post_meta($post_id, $key, $value);
                    } elseif ( $value == "" ) {
                         delete_post_meta($post_id, $key, get_post_meta($post_id, $key, true));
                    }
               }
          }

          /* Flush the rewrite ruels */
          global $wp_rewrite;
          $wp_rewrite->flush_rules();

          return $post_id;
     }

     /**
      * Add acknowledgemnt to the admin footer.
      */
     function in_admin_footer() {
          printf(__('Thank you for using %1$s version %2$s.', 'better-admin_menu'),
                  '<a href="http://calendarpress.net" target="_blank">' . $this->pluginName . '</a>',
                  $this->version
          );
          print '<br>';
     }

}