<?php
if ( preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']) ) {
     die('You are not allowed to call this page directly.');
}

/**
 * event-registrations.php - The Template for displaying a event registrations with limited signups.
 *
 * @package CalendarPress
 * @subpackage templates
 * @author GrandSlambert
 * @copyright 2009-2011
 * @access public
 * @since 0.4
 */
?>
<div id="event_buttons">
     <div class="event-registration">
          <h3 class="event-registration-text"><?php _e('Registration', 'calendar-press'); ?></h3>
          <div>
               <?php the_event_signup_button(); ?>
               <?php the_event_overflow_button(); ?>
          </div>
     </div>

     <div class="event-signups">
          <h3 class="event-registration-title"><?php the_signup_title(); ?></h3>
          <?php the_event_signups(); ?>
          </div>

     <?php if ( use_overflow_option ( ) ) : ?>
                    <div class="event-overflow">
                         <h3 class="event-registration-title"><?php the_overflow_title(); ?></h3>
          <?php the_event_overflow(); ?>
               </div>
     <?php endif; ?>
</div>