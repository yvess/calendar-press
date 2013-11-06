<?php

if ( preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']) ) {
     die('You are not allowed to call this page directly.');
}

/**
 * template_tags.php - Additinal template tags for CalendarPress
 *
 * @package CalendarPress
 * @subpackage includes
 * @author GrandSlambert
 * @copyright 2009-2011
 * @access public
 * @since 0.1
 */

/* Conditionals */
if (!function_exists('use_overflow_option')) {
     function use_overflow_option() {
          global $calendarPressOBJ;

          return $calendarPressOBJ->options['use-overflow'];
     }
}


function event_get_date_form($post = NULL, $field = 'begin', $type = 'date') {
     if ( is_int($post) ) {
          $post = get_post($post);
     } elseif ( !is_object($post) ) {
          global $post;
     }

     $date = get_post_meta($post->ID, '_' . $field . '_' . $type . '_value', true);

     if ( !$date ) {
          $date = time();
     }

     switch ($type) {
          case 'time':
               $output = '<input class="cp-' . $type . '-form" type="text" id="' . $field . ucfirst($type) . '" name="event_dates[' . $field . '_' . $type . ']" value="' . date('g', $date) . '" style="width:25px" /> : ';
               $output.= '<input class="cp-' . $type . '-form" type="text" id="' . $field . ucfirst($type) . '" name="' . $field . '_' . $type . '_minutes" value="' . date('i', $date) . '" style="width:25px" />';
               break;
          default:
               $output = '<input class="cp-' . $type . '-form" type="text" id="' . $field . ucfirst($type) . '" name="event_dates[' . $field . '_' . $type . ']" value="' . date('m/d/Y', $date) . '" style="width:100px" />';
               break;
     }

     return $output;
}

function event_date_form($post = NULL, $field = 'begin', $type = 'date') {
     print event_get_date_form($post, $field, $type);
}

function event_get_meridiem($post = NULL, $field = 'begin', $type = 'am') {
     if ( is_int($post) ) {
          $post = get_post($post);
     } elseif ( !is_object($post) ) {
          global $post;
     }

     $date = get_post_meta($post->ID, '_' . $field . '_time_value', true);

     if ( $date <= 0 ) {
          $date = date("U");
     }

     if (
             (date('G', $date) < 12 and $type == 'am')
             or (date('G', $date) >= 12 and $type == 'pm')
     ) {
          print ' selected="selected"';
     }
}

function event_meridiem($post = NULL, $field = 'begin', $type = 'am') {
     print event_get_meridiem($post, $field, $type);
}

function event_get_calendar($month = NULL, $year = NULL) {
     global $calendarPressOBJ;

     $output = '';

     if ( !$month ) {
          $month = date('m', strtotime($calendarPressOBJ->currDate));
     }

     if ( !$year ) {
          $year = date('Y', strtotime($calendarPressOBJ->currDate));
     }

     $day = date('d');
     $firstDay = date('w', strtotime("$year-$month-1"));
     $totalDays = date('t', strtotime("$year-$month-1"));

     $days = 0;
     $output.= '<div id="calendar"><dl class="cp-boxes">';
     for ( $ctr = 1 - $firstDay; $ctr <= $totalDays; ++$ctr ) {
          $output.= '<dd class="cp-month-box ';
          ++$days;

          if ( $days > 7 ) {
               $output.= ' cp-break';
               $days = 1;
          }

          if ( $ctr < 1 ) {
               $output.= ' cp-empty-day';
          }

          if ( $ctr == $day and $month == date('m') and $year == date('Y') ) {
               $output.= ' cp-active-day';
          }

          $output.= '">';

          if ( $ctr > 0 and $ctr <= $totalDays ) {
               $output.= '<span class="cp-month-numeral">' . $ctr . '</span>';
               $output.= '<span class="cp-month-contents">' . event_get_daily_events($month, $ctr, $year) . '</span>';
          }

          $output.= '</dd>';
     }

     for ( $ctr = $days; $ctr < 7; ++$ctr ) {
          $output.= '<dd class="cp-month-box cp-empty-day"></dd>';
     }
     $output.= '</dl></div>';

     return $output;
}

function event_calendar($month = NULL, $year = NULL) {
     print event_get_calendar($month, $year);
}

function event_get_daily_events($month = NULL, $day = NULL, $year = NULL) {
     global $openEvents;
     $events = get_posts(
                     array(
                          'post_type' => 'event',
                          'meta_key' => '_begin_date_value',
                          'meta_value' => strtotime("$year-$month-$day"),
                     )
     );

     if ( is_array($openEvents) ) {
          $events = array_merge($openEvents, $events);
     }
     if ( count($events) <= 0 ) {
          return;
     }

     $output = '';

     foreach ( $events as $event ) {

          $output.= '<span class="event-title-month" ';

          if ( get_post_meta($event->ID, '_event_popup_value', true) ) {
               $output.= 'onmouseover="return overlib(\'' . esc_js(event_get_popup($event)) . '\');" onmouseout="return nd();" ';
          }

          $output.= '><a href="' . get_permalink($event->ID) . '">' . $event->post_title . '</a></span>';

          $eventBeginDate = get_post_custom_values('_begin_date_value', $event->ID);
          $eventEndDate = get_post_custom_values('_end_date_value', $event->ID);


          if ( (date('j-Y', $eventEndDate[0]) != date('j-Y', $eventBeginDate[0])) && date('j-Y', $eventEndDate[0]) == $day . "-" . $year ) {
               while ($openEvent = current($openEvents)) {
                    if ( $openEvent->ID == $event->ID ) {
                         $removeKey = key($openEvents);
                    }
                    next($openEvents);
               }
               if ( isset($openEvents[1]) and is_array($openEvents[1]) ) {
                    $removeEvent[$removeKey] = $openEvents[1];
               } else {
                    $removeEvent[$removeKey] = array();
               }
               
               $openEvents = array_diff_key($openEvents, $removeEvent);
          } elseif ( (date('j-Y', $eventEndDate[0]) != date('j-Y', $eventBeginDate[0])) && date('j-Y', $eventBeginDate[0]) == $day . "-" . $year ) {
               $openEvents[] = $event;
          }
     }

     return $output;
}

function event_daily_events($month = NULL, $day = NULL, $year = NULL) {
     print event_get_daily_events($month, $day, $year);
}

function event_get_popup($event) {
     global $calendarPressOBJ;

     ob_start();
     include ($calendarPressOBJ->get_template('popup-box'));
     $output = ob_get_contents();
     ob_end_clean();

     return $output;
}

function get_the_event_dates($attrs = array(), $post = NULL) {
     global $calendarPressOBJ;

     if ( is_int($post) ) {
          $post = get_post($post);
     } elseif ( !is_object($post) ) {
          global $post;
     }

     $defaults = array(
          'date_format' => get_option('date_format'),
          'time_format' => get_option('time_format'),
          'prefix' => __('When: ', 'calendar-press'),
          'before_time' => __(' from ', 'calendar-press'),
          'after_time' => '',
          'between_time' => __(' to ', 'calendar-press'),
          'after_end_date' => __(' at ', 'calendar-press'),
     );

     extract(wp_parse_args($attrs, $defaults));

     $startDate = get_post_meta($post->ID, '_begin_date_value', true);
     $startTime = get_post_meta($post->ID, '_begin_time_value', true);
     $endDate = get_post_meta($post->ID, '_end_date_value', true);
     $endTime = get_post_meta($post->ID, '_end_time_value', true);

     $output = $prefix . date($date_format, $startDate);

     if ( $startDate == $endDate ) {
          $output.= $before_time . date($time_format, $startTime) . $between_time . date($time_format, $endTime) . $after_time;
     } else {
          $output.= $before_time . date($time_format, $startTime) . $between_time . date($date_format, $endDate) . $after_end_date . date($time_format, $endTime) . $after_time;
     }

     return $output;
}

function the_event_dates($attrs = array(), $post = NULL) {
     print get_the_event_dates($attrs, $post);
}

function get_the_event_category($attrs = array(), $post = NULL) {
     global $calendarPressOBJ;

     if ( !$calendarPressOBJ->options['use-categories'] ) {
          return false;
     }

     if ( is_int($post) ) {
          $post = get_post($post);
     } elseif ( !is_object($post) ) {
          global $post;
     }

     if ( is_null($args['prefix']) ) {
          $args['prefix'] = __('Posted In: ', 'calendar-press');
     }

     if ( is_null($args['divider']) ) {
          $args['divider'] = ', ';
     }

     if ( wp_get_object_terms($post->ID, 'event-categories') ) {
          $cats = $args['prefix'] . get_the_term_list($post->ID, 'event-categories', $args['before-category'], $args['divider'], $args['after-category']) . $args['suffix'];
          return $cats;
     }
}

function the_event_category($attrs = array(), $post = NULL) {
     print get_the_event_category($attrs, $post);
}

function get_event_button($type = 'signups', $post = NULL, $attrs = array()) {
     global $calendarPressOBJ, $wpdb, $current_user;
     get_currentuserinfo();
     $used = 0;

     if ( !is_user_logged_in() ) {
          if ( $type == 'signups' ) {
               return sprintf(__('You must be %1$s to register', 'calendar-press'), '<a href="' . wp_login_url(get_permalink()) . '">' . __('logged in', 'calendar-press') . '</a>');
          } else {
               return;
          }
     }

     if ( !$calendarPressOBJ->options['registration-type'] == 'none' ) {
          print "DOH!";
          return false;
     }

     if ( is_int($post) ) {
          $post = get_post($post);
     } elseif ( !is_object($post) ) {
          global $post;
     }

     if ( $calendarPressOBJ->options['registration-type'] == 'select' ) {
          $method = get_post_meta($post->ID, '_registration_type_value', true);
     } else {
          $method = $calendarPressOBJ->options['registration-type'];
     }

     switch ($method) {
          case 'signups':
               $alt = array('signups' => 'overflow', 'overflow' => 'signups');

               $registrations = get_post_meta($post->ID, '_event_registrations_' . $type, true);
               $alt_registrations = get_post_meta($post->ID, '_event_registrations_' . $alt[$type], true);
               $available = get_post_meta($post->ID, '_event_' . $type . '_value', true);

               if ( is_array($registrations) and array_key_exists($current_user->ID, $registrations) ) {
                    $registered = true;
               } else {
                    $registered = false;
               }

               if ( is_array($registrations) ) {
                    $remaining = $available - count($registrations);
               } else {
                    $remaining = $available;
               }

               $buttonText = $calendarPressOBJ->options[$type . '-title'];

               if ( $registered ) {
                    $addButtonText = __(' - Cancel Registration', 'calendar-press');
                    $clickEvent = 'onClickCancel(\'' . $type . '\', ' . $post->ID . ')';
               } elseif ( $remaining > 0 ) {
                    if ( $registered or (is_array($alt_registrations) and array_key_exists($current_user->id, $alt_registrations)) ) {
                         $addButtonText = sprintf(__('- Move (%1$s of %2$s Available)'), $remaining, $available);
                         $clickEvent = 'onClickMove(\'' . $type . '\', ' . $post->ID . ')';
                    } else {
                         $addButtonText = sprintf(__(' (%1$s of %2$s Available)'), $remaining, $available);
                         $clickEvent = 'onClickRegister(\'' . $type . '\', ' . $post->ID . ')';
                    }
               } else {
                    $addButtonText = __(' Full');
                    $clickEvent = 'onClickWaiting(' . $post->ID . ')';
               }

               $buttonText.= $addButtonText;

               return '<input id="button_' . $type . '" type="button" value="' . $buttonText . '" onclick="' . $clickEvent . '">';
               break;
          case 'yesno':
               $registrations = get_post_meta($post->ID, '_event_registrations_yesno', true);
               if (!is_array($registrations)) {
                    $registrations = array();
               }
               $buttonText = ucfirst($type);

               if ( array_key_exists($current_user->ID, $registrations) and $registrations[$current_user->ID]['type'] == $type ) {
                    $disabled = 'disabled';
                    $buttonStyle = 'event_button_selected';
               } else {
                    $disabled = '';
                    $buttonStyle = 'event_button_not_selected';
               }

               $clickEvent = 'onClickYesNo(\'' . $type . '\',' . $post->ID . ')';
               return '<input class="' . $buttonStyle . '" id="button_' . $type . '" type="button" value="' . $buttonText . '" onclick="' . $clickEvent . '" ' . $disabled . '>';
               break;
     }
}

function the_event_signup_button($post = NULL, $attrs = array()) {
     print get_event_button('signups', $post, $attrs);
}

function the_event_overflow_button($post = NULL, $attrs = array()) {
     global $calendarPressOBJ;

     if ($calendarPressOBJ->options['use-overflow']) {
          print get_event_button('overflow', $post, $attrs);
     }
}

function the_event_yes_button($post = NULL, $attrs = array()) {
     print get_event_button('yes', $post, $attrs);
}

function the_event_no_button($post = NULL, $attrs = array()) {
     print get_event_button('no', $post, $attrs);
}

function the_event_maybe_button($post = NULL, $attrs = array()) {
     print get_event_button('maybe', $post, $attrs);
}

function get_event_month_link($date) {
     global $calendarPressOBJ;

     if ( $calendarPressOBJ->in_shortcode ) {
          global $post;
          $link = get_permalink($post);
     } else {
          $link = get_option('home') . '/' . $calendarPressOBJ->options['index-slug'];
     }
     $month = date('m', strtotime($date));
     $year = date('Y', strtotime($date));
     $text = date('F, Y', strtotime($date));

     if ( get_option('permalink_structure') ) {
          return '<a href="' . $link . '?viewmonth=' . $month . '&viewyear=' . $year . '">' . $text . '</a>';
     } else {
          return '<a href="' . $link . '&viewmonth=' . $month . '&viewyear=' . $year . '">' . $text . '</a>';
     }
}

function get_event_last_month() {
     global $calendarPressOBJ;
     return get_event_month_link($calendarPressOBJ->lastMonth);
}

function the_event_last_month() {
     print get_event_last_month();
}

function get_event_this_month() {
     global $calendarPressOBJ;
     return date('F, Y', strtotime($calendarPressOBJ->currDate));
}

function the_event_this_month() {
     print get_event_this_month();
}

function get_event_next_month() {
     global $calendarPressOBJ;
     return get_event_month_link($calendarPressOBJ->nextMonth);
}

function the_event_next_month() {
     print get_event_next_month();
}

function get_event_signups($attrs = array(), $post = NULL) {
     global $wpdb, $calendarPressOBJ;

     if ( is_int($post) ) {
          $post = get_post($post);
     } elseif ( !is_object($post) ) {
          global $post;
     }

     extract(shortcode_atts(array(
                  'type' => 'signups',
                  'divider' => '<br>',
                     ), $attrs)
     );

     $signups = get_post_meta($post->ID, '_event_registrations_' . $type, true);

     if ( is_array($signups) and count($signups) > 0 ) {
          $field = $calendarPressOBJ->options['user-display-field'];
          $prefix = '';
          $output = '';

          foreach ( $signups as $id => $signup ) {
               $signups[$id]['id'] = $id;
               $tempArray[$id] = &$signup['date'];
          }
          array_multisort($tempArray, $signups);

          foreach ( $signups as $user_id => $signup ) {
               $user = get_userdata($signup['id']);

               if ( $field == 'full_name' and ($user->first_name or $user->last_name) ) {
                    $username = $user->first_name . ' ' . $user->last_name;
               } elseif ( $field != 'display_name' and isset($user->$field) ) {
                    $username = $user->$field;
               } else {
                    $username = $user->display_name;
               }

               if ( $type != 'yesno' or ($type == 'yesno' and $signup['type'] == $attrs['match']) ) {
                    $output.= $prefix . $username;

                    if ( $calendarPressOBJ->options['show-signup-date'] ) {
                         $output.= ' - ' . date($calendarPressOBJ->options['signup-date-format'], $signup['date']);
                    }
                    $prefix = $divider;
               }
          }
     } else {
          $output = __('No Registrations', 'calendar-press');
     }

     return $output;
}

function the_event_signups($attrs = array(), $post = NULL) {
     $attrs['type'] = 'signups';
     print get_event_signups($attrs, $post);
}

function the_event_overflow($attrs = array(), $post = NULL) {
     $attrs['type'] = 'overflow';
     print get_event_signups($attrs, $post);
}

function the_event_yes($attrs = array(), $post = NULL) {
     $attrs['type'] = 'yesno';
     $attrs['match'] = 'yes';
     print get_event_signups($attrs, $post);
}

function the_event_no($attrs = array(), $post = NULL) {
     $attrs['type'] = 'yesno';
     $attrs['match'] = 'no';
     print get_event_signups($attrs, $post);
}

function the_event_maybe($attrs = array(), $post = NULL) {
     $attrs['type'] = 'yesno';
     $attrs['match'] = 'maybe';
     print get_event_signups($attrs, $post);
}

function get_signup_title() {
     global $calendarPressOBJ;
     return $calendarPressOBJ->options['signups-title'];
}

function the_signup_title() {
     print get_signup_title();
}

function get_overflow_title() {
     global $calendarPressOBJ;
     return $calendarPressOBJ->options['overflow-title'];
}

function the_overflow_title() {
     print get_overflow_title();
}

function show_registrations($post = NULL) {
     global $calendarPressOBJ;

     if ( is_int($post) ) {
          $post = get_post($post);
     } elseif ( !is_object($post) ) {
          global $post;
     }

     if ( $calendarPressOBJ->options['registration-type'] == 'select' ) {
          $method = get_post_meta($post->ID, '_registration_type_value', true);
     } else {
          $method = $calendarPressOBJ->options['registration-type'];
     }

     switch ($method) {
          case 'none':
               return false;
               break;
          case 'signups':
               return true;
               break;
          case 'yesno':
               return true;
               break;
          default:
               return false;
     }
}

function show_calendar_date_time($date, $format = NULL) {
     if ( !$format ) {
          $format = get_option('date_format');
     }

     return date($format, $date);
}

function get_start_date($date = NULL, $format = NULL) {
     if ( !$date ) {
          global $post;
          $date = get_post_meta($post->ID, '_begin_date_value', true);
     }
     return show_calendar_date_time($date, $format);
}

function the_start_date($date= NULL, $format = NULL) {
     echo get_start_date($date, $format);
}

function get_start_time($time = NULL, $format = NULL) {
     if ( !$time ) {
          global $post;
          $time = get_post_meta($post->ID, '_begin_time_value', true);
     }
     if ( !$format ) {
          $format = get_option('time_format');
     }
     return show_calendar_date_time($time, $format);
}

function the_start_time($time= NULL, $format = NULL) {
     echo get_start_time($time, $format);
}

function get_end_date($date = NULL, $format = NULL) {
     if ( !$date ) {
          global $post;
          $date = get_post_meta($post->ID, '_end_date_value', true);
     }
     return show_calendar_date_time($date, $format);
}

function the_end_date($date= NULL, $format = NULL) {
     echo get_end_date($date, $format);
}

function get_end_time($time = NULL, $format = NULL) {
     if ( !$time ) {
          global $post;
          $time = get_post_meta($post->ID, '_end_time_value', true);
     }
     if ( !$format ) {
          $format = get_option('time_format');
     }
     return show_calendar_date_time($time, $format);
}

function the_end_time($time= NULL, $format = NULL) {
     echo get_end_time($time, $format);
}

/**
 * Load the registration form based on the event options.
 *
 * @global object $post
 * @global object $calendarPressOBJ
 */
function event_event_registrations() {
     global $post, $calendarPressOBJ;

     if ( $calendarPressOBJ->options['registration-type'] == 'select' ) {
          switch (get_post_meta($post->ID, '_registration_type_value', true)) {
               case 'signups':
                    include $calendarPressOBJ->get_template('registration-signups');
                    break;
               case 'yesno':
                    include $calendarPressOBJ->get_template('registration-yesno');
                    break;
               default:
                    /* Do nothing */
          }
     } else {
          $template = $calendarPressOBJ->get_template('registration-' . $calendarPressOBJ->options['registration-type']);

          if (file_exists($template)) {
               include $template;
          }
     }
}