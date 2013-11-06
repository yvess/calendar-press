<?php

if ( preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']) ) {
     die('You are not allowed to call this page directly.');
}

/**
 * list-widget.php - sidebar widget for listing events.
 *
 * @package CalendarPress
 * @subpackage widgets
 * @author GrandSlambert
 * @copyright 2009-2011
 * @access public
 * @since 0.1
 */
class cp_Widget_List_Events extends WP_Widget {

     var $options = array();

     /**
      * Constructor
      */
     function cp_Widget_List_Events() {
          /* translators: The description of the Recpipe List widget on the Appearance->Widgets page. */
          $widget_ops = array('description' => __('List events on your sidebar. By GrandSlambert.', 'calendar-press'));
          /* translators: The title for the calendar List widget. */
          $this->WP_Widget('calendar_press_list_widget', __('CalendarPress &raquo; List', 'calendar-press'), $widget_ops);

          $this->pluginPath = WP_CONTENT_DIR . '/plugins/' . plugin_basename(dirname(__FILE__));
          $this->options = get_option('calendar-press-options');
     }

     /**
      * Widget code
      */
     function widget($args, $instance) {
          global $calendarPressOBJ, $wp_query, $customFields, $cp_widget_order;

          if ( isset($instance['error']) && $instance['error'] ) {
               return;
          }

          $instance = wp_parse_args($instance, $this->defaults());

          $posts = new WP_Query;
          $posts->set('post_type', 'event');
          $posts->set('posts_per_age', $instance['items']); /* Does not seem to work */
          $posts->set('showposts', $instance['items']);
          $customFields = array('_begin_time_value' => time());

          switch ($instance['type']) {
               case 'next':
                    $posts->set('order', 'ASC');
                    $cp_widget_order = '_begin_time_value ASC';
                    add_filter('posts_orderby', array($calendarPressOBJ, 'get_custom_fields_posts_orderby'));
                    break;
               case 'newest':
                    $posts->set('ordeby', 'date');
                    break;
               case 'featured':
                    $customFields['_event_featured_value'] = true;
                    add_filter('posts_orderby', array($calendarPressOBJ, 'get_custom_fields_posts_orderby'));
                    break;
               case 'updated':
                    $posts->set('orderby', 'modified');
                    break;
               case 'random':
                    $posts->set('orderby', 'rand');
                    break;
               default:
                    break;
          }

          /* Grab the posts for the widget */
          add_filter('posts_fields', array(&$calendarPressOBJ, 'get_custom_fields_posts_select'));
          add_filter('posts_join', array(&$calendarPressOBJ, 'get_custom_field_posts_join'));
          add_filter('posts_groupby', array($calendarPressOBJ, 'get_custom_field_posts_group'));
          $posts->get_posts();
          remove_filter('posts_fields', array(&$calendarPressOBJ, 'get_custom_fields_posts_select'));
          remove_filter('posts_join', array(&$calendarPressOBJ, 'get_custom_field_posts_join'));
          remove_filter('posts_groupby', array($calendarPressOBJ, 'get_custom_field_posts_group'));
          remove_filter('posts_orderby', array($calendarPressOBJ, 'get_custom_fields_posts_orderby'));

          /* Output Widget */
          echo $args['before_widget'];
          if ( $instance['title'] ) {
               echo $args['before_title'] . $instance['title'] . $args['after_title'];
          }

          $template = $calendarPressOBJ->get_template('list-widget');
          include($template);

          echo $args['after_widget'];
     }

     /** @see WP_Widget::form */
     function form($instance) {
          global $calendarPressOBJ;

          $instance = wp_parse_args($instance, $this->defaults());


          if ( $instance['items'] < 1 || 20 < $instance['type'] )
               $instance['type'] = $this->options['widget-items'];

          include( $this->pluginPath . '/list-form.php');
     }

     function defaults() {
          global $calendarPressOBJ;
          return array(
               'title' => false,
               'items' => $calendarPressOBJ->options['widget-items'],
               'type' => $calendarPressOBJ->options['widget-type'],
               'linktarget' => $calendarPressOBJ->options['widget-target'],
               'showicon' => $calendarPressOBJ->options['widget-show-icon'],
               'iconsize' => $calendarPressOBJ->options['widget-icon-size'],
          );
     }

}

add_action('widgets_init', create_function('', 'return register_widget("cp_Widget_List_Events");'));