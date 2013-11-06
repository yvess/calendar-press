<?php

if ( preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']) ) {
     die('You are not allowed to call this page directly.');
}

/**
 * multisite-support.php - Temporary functions until WP adds these needed functions.
 *
 * @package CalendarPress
 * @subpackage includes
 * @author GrandSlambert
 * @copyright 2009-2011
 * @access public
 * @since 0.4
 */

/**
 * Returns an array of blogs for a user (all blogs if user is super-admin
 *
 * @param array $args
 * @return array
 * @since 0.4
 */
function wp_get_multisites($args = array()) {
     global $calendarPressOBJ;

     if (!$calendarPressOBJ->isNetwork) {
          return array();
     }
     
     $defaults = array(
          'user' => 1,
          'get_all' => true
     );

     $args = wp_parse_args($args, $defaults);

     return get_blogs_of_user($args['user'], $args['get_all']);
}

/**
 * Function to list all blogs for a user.
 * @param array $args
 * @return string
 * @since 0.4
 */
function wp_list_multisites($args = array()) {
     $defaults = array(
          'list-class' => 'multisite-list',
          'item-class' => 'multisite-list-item',
          'item-tag' => 'li',
          'echo' => true,
     );

     $args = wp_parse_args($args, $defaults);

     switch ($args['item-tag']) {
          default:
               $output = '<ul class="' . $args['list-class'] . '">';
     }

     foreach ( wp_get_multisites($args) AS $blog ) {
          $site_id = 'multisite_' . $blog->userblog_id;

          switch ($args['item-tag']) {
               default:
                    $output.= '<li><a class="' . $args['item-class'] . '" id="' . $site_id . '" href="' . $blog->siteurl . '">' . $blog->blogname . '</a></li>';
          }
     }

     switch ($args['item-tag']) {
          default:
               $output .= '</ul>';
     }

     if ( $args['echo'] ) {
          echo $output;
     } else {
          return $output;
     }
}

function wp_dropdown_multisites($args = array()) {
     $defaults = array(
          'name' => 'site-id',
          'id' => 'site_id',
          'class' => 'multisite-dropdown',
          'selected' => false,
          'onchange' => false,
          'echo' => true,
          'value_field' => 'userblog_id',
          'show_field' => 'blogname',
     );

     $args = wp_parse_args($args, $defaults);

     $output = '<select name="' . $args['name'] . '" id = "' . $args['id'] . '" class="' . $args['class'] . '" ';

     if ( $args['onchange'] ) {
          $output.= 'onchange="' . $args['onchange'] . '"';
     }
     $output.= '>';

     foreach ( wp_get_multisites ( ) as $blog ) {
          $output.= '<option value="' . $blog->$args['value_field'] . '" ' . selected($args['selected'], $blog->$args['value_field'], false) . '>' . $blog->$args['show_field'] . '</option>';
     }

     $output.= '</select>';

     if ( $args['echo'] ) {
          echo $output;
     } else {
          return $output;
     }
}