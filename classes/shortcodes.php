<?php

if ( preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']) ) {
     die('You are not allowed to call this page directly.');
}

/**
 * shortcodes.php - Initialize the post types and taxonomies
 *
 * @package CalendarPress
 * @subpackage classes
 * @author GrandSlambert
 * @copyright 2009-2011
 * @access public
 * @since 0.4
 */
class calendar_press_shortcodes extends calendar_press_core {

     static $instance;

     /**
      * Initialize the plugin.
      */
     function __construct() {
          parent::__construct();

          /* Add Shortcodes */
          add_shortcode('event-calendar', array(&$this, 'calendar_shortcode'));
          add_shortcode('event-list', array(&$this, 'list_shortcode'));
          add_shortcode('event-show', array(&$this, 'show_shortcode'));

          /* Deprciated shortcoces */
          add_shortcode('calendarpress', array(&$this, 'calendar_shortcode'));
          add_shortcode('calendar-press', array(&$this, 'calendar_shortcode'));
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
               self::$instance = new calendar_press_shortcodes;
          }
          return self::$instance;
     }

     /**
      * Calendar View shortcocde.
      *
      * @global object $wp
      * @global object $calendarPressOBJ
      * @param arrat $atts
      * @return string
      */
     function calendar_shortcode($atts) {
          global $wp, $calendarPressOBJ;

          $this->in_shortcode = true;

          $defaults = array(
               'scope' => 'month',
               'element' => 'li',
               'title' => $this->options['calendar-title'],
               'hide-title' => false,
               'title-class' => 'calendar-press-title',
               'title-id' => 'calendar-press-title',
               'month' => date('m'),
               'year' => date('Y'),
          );

          $atts = wp_parse_args($atts, $defaults);

          if ( isset($atts['type']) and $atts['type'] == 'list' ) {
               /* Make this shortcode backward compatible */
               $this->list_shortcode($atts);
          } else {

               if ( isset($wp->query_vars['viewmonth']) ) {
                    $atts['month'] = $wp->query_vars['viewmonth'];
               }
               if ( isset($wp->query_vars['viewyear']) ) {
                    $atts['year'] = $wp->query_vars['viewyear'];
               }

               if ( !$atts['hide-title'] and $atts['title'] ) {
                    $title = '<h3 id="' . $atts['title-id'] . '" class="' . $atts['title-class'] . '">' . $atts['title'] . '</h3>';
               } else {
                    $title == '';
               }

               $output = $calendarPressOBJ->show_the_calendar($atts['month'], $atts['year'], &$this);
               $this->in_shortcode = false;

               return $title . $output;
          }
     }

     /**
      * Event list shortcocde.
      *
      * @global object $wp
      * @global object $calendarPressOBJ
      * @param arrat $atts
      * @return string
      */
     function list_shortcode($atts) {
          global $wp, $calendarPressOBJ;

          $this->in_shortcode = true;

          $defaults = array(
               'scope' => 'month',
               'element' => 'li',
               'title' => $this->options['calendar-title'],
               'hide-title' => false,
               'title-class' => 'calendar-press-title',
               'title-id' => 'calendar-press-title',
               'month' => date('m'),
               'year' => date('Y'),
          );

          $atts = wp_parse_args($atts, $defaults);

          if ( isset($wp->query_vars['viewmonth']) ) {
               $atts['month'] = $wp->query_vars['viewmonth'];
          }
          if ( isset($wp->query_vars['viewyear']) ) {
               $atts['year'] = $wp->query_vars['viewyear'];
          }

          if ( !$atts['hide-title'] and $atts['title'] ) {
               $title = '<h3 id="' . $atts['title-id'] . '" class="' . $atts['title-class'] . '">' . $atts['title'] . '</h3>';
          } else {
               $title == '';
          }

          $output = $calendarPressOBJ->show_the_list($atts);
          $this->in_shortcode = false;

          return $title . $output;
     }

     function show_shortcode($atts) {
          global $wpdb, $post, $calendarPressOBJ;
          $tmp_post = $post;
          $calendarPressOBJ->in_shortcode = true;

          $defaults = array(
               'event' => NULL,
               'template' => 'single-event',
          );

          $atts = wp_parse_args($atts, $defaults);

          if ( !$atts['event'] ) {
               return __('Sorry, no event found', 'calendar-press');
          }

          $post = get_post($wpdb->get_var('select `id` from `' . $wpdb->prefix . 'posts` where `post_name` = "' . $atts['event'] . '" and `post_status` = "publish" limit 1'));
          setup_postdata($post);

          ob_start();
          include ($this->get_template($atts['template']));
          $output = ob_get_contents();
          ob_end_clean();

          $post = $tmp_post;
          $calendarPressOBJ->in_shortcode = false;

          return $output;
     }

}