<?php
/*
  Plugin Name: CalendarPress
  Plugin URI: http://calendarpress.net/
  Description: Add an event calendar with details, Google Maps, directions, RSVP system and more.
  Version: 0.4.3
  Author: grandslambert
  Author URI: http://grandslambert.com/

 * *************************************************************************

  Copyright (C) 2009-2011 GrandSlambert

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General License for more details.

  You should have received a copy of the GNU General License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * *************************************************************************

 */

require_once('classes/calendar-press-core.php');
require_once('classes/initialize.php');
require_once('includes/template_tags.php');
require_once('includes/inflector.php');
require_once('includes/multisite-support.php');

/* Loads the recaptcha library if it is not already loaded. */
if ( !function_exists('recaptcha_get_html') ) {
     require_once('includes/recaptchalib.php');
}

class calendar_press extends calendar_press_core {

     /**
      * Initialize the plugin.
      */
     function __construct() {
          parent::__construct();

          /* Add actions */
          add_action('send_headers', array(&$this, 'cookies'));
          add_action('wp_loaded', array(&$this, 'wp_loaded'));
          add_action('wp_print_styles', array(&$this, 'wp_print_styles'));
          add_action('wp_print_scripts', array(&$this, 'wp_print_scripts'));
          add_action('template_redirect', array(&$this, 'template_redirect'));
          add_action('pre_get_posts', array(&$this, 'pre_get_posts'));

          /* Add filters */
          add_filter('the_content', array(&$this, 'the_content_filter'));
          add_filter('the_excerpt', array(&$this, 'the_content_filter'));
          add_filter('query_vars', array(&$this, 'add_queryvars'));

          /* Setup AJAX */
          add_action('wp_ajax_event_registration', array(&$this, 'event_registration'));
          add_action('wp_ajax_nopriv_event_registration', array(&$this, 'event_registration'));

          if ( is_admin ( ) ) {
               require_once('classes/administration.php');
               calendar_press_admin::initialize();
          } else {
               require_once 'classes/shortcodes.php';
               calendar_press_shortcodes::initialize();
          }

          calendar_press_init::initialize();
     }

     /**
      * Add cookies for calendar display.
      *
      * @global <type> $wp_query
      */
     function cookies() {
          global $wp_query;

          if ( $this->options['use-cookies'] and (isset($_REQUEST['viewmonth']) or isset($_REQUEST['viewyear'])) ) {
               $url = parse_url(get_option('home'));
               if ( !isset($url['path']) ) {
                    $url['path'] = '';
               }
               setcookie('cp_view_month', $_REQUEST['viewmonth'], time() + 3600, $url['path'] . DIRECTORY_SEPARATOR, $url['host']);
               setcookie('cp_view_year', $_REQUEST['viewyear'], time() + 3600, $url['path'] . DIRECTORY_SEPARATOR, $url['host']);
          }
     }

     function wp_loaded() {
          /* Add CSS stylesheets */
          wp_register_style('calendar-press-style', $this->get_template('calendar-press', '.css', 'url'));

          /* Handle javascript */
          wp_register_script('calendar-press-script', $this->pluginURL . '/js/calendar-press.js');
          wp_register_script('calendar-press-overlib', $this->pluginURL . '/js/overlib/overlib.js');
          wp_register_script('calendar-press-encode', $this->pluginURL . '/js/encode.js');
     }

     function wp_print_styles() {
          wp_enqueue_style('calendar-press-style');
?>
          <style type="text/css" media="screen">
               .cp-box-width, dd.cp-month-box {
                    width: <?php echo $this->options['box-width']; ?>px;
               }
          </style>
<?
     }

     function wp_print_scripts() {
          wp_localize_script('calendar-press-script', 'CPAJAX', array('ajaxurl' => admin_url('admin-ajax.php')));
          wp_enqueue_script('sack');
          wp_enqueue_script('calendar-press-script');
          wp_enqueue_script('calendar-press-overlib');
          wp_enqueue_script('calendar-press-encode');
     }

     function add_queryvars($qvars) {
          $qvars[] = 'viewmonth';
          $qvars[] = 'viewyear';
          return $qvars;
     }

     function pre_get_posts() {
          global $wp_query;

          /* If author page, add events */
          if ( isset($wp_query->query_vars['author_name']) && $wp_query->query_vars['author_name'] != '' && $this->options['author-archives'] ) {
               if ( $wp_query->get('post_type') == '' ) {
                    $types = array('post', 'event');
               } elseif ( !is_array($wp_query->get('post_type')) ) {
                    $types = array($wp_query->get('post_type'), 'event');
               } elseif ( !in_array('event', $wp_query->get('post_type')) ) {
                    $types = array_merge($wp_query->get('post_type'), array('event'));
               } else {
                    return;
               }

               $wp_query->set('post_type', $types);
          }

          /* Get correct dates if event */
          if ( isset($wp_query->query_vars['post_type']) AND $wp_query->query_vars['post_type'] == 'event' and is_single() ) {
               $year = $wp_query->get('year');
               $monthnum = $wp_query->get('monthnum');
               $day = $wp_query->get('monthnum');
               $wp_query->set('year', false);
               $wp_query->set('monthnum', false);
               $wp_query->set('day', false);
          }

          /* Order by date, ascending, if a category list. */
          if ( is_archive() and array_key_exists('event-category', $wp_query->query_vars) ) {
               $wp_query->query_vars['orderby'] = 'post_date';
               $wp_query->query_vars['order'] = 'ASC';
          }
     }

     function template_redirect() {
          global $post, $wp;

          if ( isset($wp->query_vars['post_type']) AND $wp->query_vars['post_type'] == 'event' and !is_single() ) {
               $template = $this->get_template('index-event');
               include($template);
               exit;
          }
     }

     function the_content_filter($content) {
          global $post, $wp, $current_user;
          get_currentuserinfo();

          if ( $this->in_shortcode ) {
               return $content;
          }

          $files = get_theme(get_option('current_theme'));

          if ( is_single ( ) ) {
               $template_file = get_stylesheet_directory() . '/single-event.php';
          } elseif ( is_archive ( ) ) {
               $template_file = get_stylesheet_directory() . '/archive-event.php';
          } else {
               $template_file = get_stylesheet_directory() . '/index-event.php';
          }
          if ( $post->post_type != 'event' or in_array($template_file, $files['Template Files']) or $this->in_shortcode ) {
               return $content;
          }

          remove_filter('the_content', array(&$this, 'the_content_filter'));

          if ( is_archive ( ) ) {
               $template = $this->get_template('loop-event');
          } elseif ( is_single ( ) ) {
               $template = $this->get_template('single-event');
          } elseif ( $post->post_type == 'event' and in_the_loop() ) {
               $template = $this->get_template('loop-event');
          }

          ob_start();
          require ($template);
          $content = ob_get_contents();
          ob_end_clean();

          add_filter('the_content', array(&$this, 'the_content_filter'));

          return $content;
     }

     /**
      * Ajax method for hading registrations.
      *
      * @global object $current_user
      */
     function event_registration() {
          global $current_user, $post;
          get_currentuserinfo();
          $type = $_POST['type'];
          $id = $_POST['id'];
          $post = get_post($id);
          $action = $_POST['click_action'];
          $event = get_post($id);
          $meta_prefix = '_event_registrations_';

          switch ($action) {
               case 'yesno':
                    $responses = array(
                         'yes' => __('are attending', 'calendar_press'),
                         'no' => __('are not attending', 'calendar_press'),
                         'maybe' => __('might attend', 'calendar_presss')
                    );

                    $registrations = get_post_meta($id, $meta_prefix . 'yesno', true);

                    if ( !is_array($registrations) ) {
                         $registrations = array();
                    }

                    if ( array_key_exists($current_user->ID, $registrations) ) {
                         if ( $registrations[$current_user->ID]['type'] == $type ) {
                              $message = sprintf(__('You have already indicated that you %1$s this event.', 'calendar-press'),
                                              $responses[$type]
                              );
                              $status = 'Duplicate';
                         } else {
                              $oldType = $registrations[$current_user->ID]['type'];
                              $registrations[$current_user->ID]['type'] = $type;
                              $message = sprintf(__('You have changed your response from %1$s to %2$s this event.', 'calendar-press'),
                                              $responses[$oldType],
                                              $responses[$type]
                              );
                              $status = 'Duplicate';
                         }
                    } else {
                         $registrations[$current_user->ID] = array(
                              'type' => $type,
                              'date' => current_time('timestamp')
                         );
                         $message = sprintf(__('You have indicated that you %1$s this event.', 'calendar-press'), $responses[$type]);
                         $status = 'Registered';
                    }

                    $results = update_post_meta($id, $meta_prefix . 'yesno', $registrations);

                    break;
               case 'delete':
                    $action = 'signups';
                    $registrations = get_post_meta($id, $meta_prefix . $type, true);

                    if ( array_key_exists($current_user->ID, $registrations) ) {
                         unset($registrations[$current_user->ID]);
                    }

                    $results = update_post_meta($id, $meta_prefix . $type, $registrations);

                    if ( $results ) {
                         $message = __('Your registration for ' . $event->post_title . ' has been canceled', 'calendar-press');
                         $status = 'Cancelled';
                    } else {
                         $status = 'Error';
                         $message = __('Sorry, I was unable to remove your registration for ' . $event->post_title . '. Pleae try again later.', 'calendar-press');
                    }
                    break;
               case 'move':
                    $action = 'signups';
                    $alt = array('signups' => 'overflow', 'overflow' => 'signups');

                    $registrations = get_post_meta($id, $meta_prefix . $alt[$type], true);

                    if ( array_key_exists($current_user->ID, $registrations) ) {
                         $original_date = $registrations[$current_user->ID]['date'];
                         unset($registrations[$current_user->ID]);
                         if ( count($registrations) < 1 ) {
                              delete_post_meta($id, $meta_prefix . $alt[$type]);
                         } else {
                              update_post_meta($id, $meta_prefix . $alt[$type], $registrations);
                         }
                    }

                    /* Add new registration */
                    $registrations = get_post_meta($id, $meta_prefix . $type, true);
                    $registrations[$current_user->ID] = array(
                         'date' => ($original_date) ? $original_date : current_time('timestamp')
                    );

                    $results = update_post_meta($id, $meta_prefix . $type, $registrations);

                    if ( $results ) {
                         $message = __('Your registration for ' . $event->post_title . ' has been moved to the ' . $this->options[$type . '-title'], 'calendar-press');
                         $status = 'Moved';
                    } else {
                         $status = 'Error';
                         $message = __('Sorry, I was unable to remove your registrationr for ' . $event->post_title . '. Pleae try again later.', 'calendar-press');
                    }
                    break;
               default:
                    $action = 'signups';
                    $registrations = get_post_meta($id, $meta_prefix . $type, true);

                    if ( !is_array($registrations) ) {
                         $registrations = array($registrations);
                    }

                    if ( array_key_exists($current_user->id, $registrations) ) {
                         $message = __('You are already registered on the ' . $this->options[$type . '-title'] . '  for the ' . $event->post_title . '.', 'calendar-press');
                         $status = 'Registered';
                    } else {
                         $registrations[$current_user->ID] = array(
                              'date' => current_time('timestamp')
                         );
                         unset($registrations[0]);
                         $results = update_post_meta($id, $meta_prefix . $type, $registrations);
                         $message = __('You are now registered on the ' . $this->options[$type . '-title'] . ' for the ' . $event->post_title . '.', 'calendar-press');
                         $status = 'Registered';
                    }
          }

          ob_start();
          include $this->get_template('registration-' . $action);
          $results = ob_get_contents();
          ob_end_clean();

          die('onSackSuccess("' . $status . '","' . $type . '", ' . $id . ', "' . esc_js($results) . '")');
     }

}

/* Instantiate the Plugin */
$calendarPressOBJ = new calendar_press;

/* Add Widgets */
require_once('widgets/list-widget.php');
//require_once('widgets/category-widget.php');

/* Activation Hook */
register_activation_hook(__FILE__, 'calendar_press_activation');

function calendar_press_activation() {
     global $wpdb;

     /* Set old posts to singular post type name */
     if ( !post_type_exists('events') ) {
          $wpdb->update($wpdb->prefix . 'posts', array('post_type' => 'event'), array('post_type' => 'events'));
     }

     /* Rename the built in taxonomies to be singular names */
     $wpdb->update($wpdb->prefix . 'term_taxonomy', array('taxonomy' => 'event-category'), array('taxonomy' => 'event-categories'));
     $wpdb->update($wpdb->prefix . 'term_taxonomy', array('taxonomy' => 'event-tag'), array('taxonomy' => 'event-tags'));
}