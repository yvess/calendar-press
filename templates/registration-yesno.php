<?php
if ( preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']) ) {
     die('You are not allowed to call this page directly.');
}

/**
 * event-yesno.php - The Template for displaying a event registrations with a yes/no/maybe setting.
 *
 * @package CalendarPress
 * @subpackage templates
 * @author GrandSlambert
 * @copyright 2009-2011
 * @access public
 * @since 0.4
 */
?>
<div  id="event_buttons" >
     <div class="event-registration">
          <h3 class="event-registration-text"><?php _e('Are you attending?', 'calendar-press'); ?></h3>
          <div>
               <?php the_event_yes_button(); ?>
               <?php the_event_no_button(); ?>
               <?php the_event_maybe_button(); ?>
          </div>
     </div>

     <div class="event_registrations">
          <div class="event-yes">
               <h3 class="event-registration-title"><?php _e('Attending', 'calendar-press'); ?></h3>
               <?php the_event_yes(); ?>
          </div>

          <div class="event-maybe">
               <h3 class="event-registration-title"><?php _e('Maybe Attending', 'calendar-press'); ?></h3>
               <?php the_event_maybe(); ?>
          </div>
          <div class="event-no">
               <h3 class="event-registration-title"><?php _e('Not Attending', 'calendar-press'); ?></h3>
               <?php the_event_no(); ?>
          </div>
     </div>
</div>
<div class="cleared"></div>