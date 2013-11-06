<?php
if ( preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']) ) {
     die('You are not allowed to call this page directly.');
}

/**
 * date-box.php - Adds the meta box with dates and times for an event.
 *
 * @package CalendarPress
 * @subpackage includes/meta-boxes
 * @author GrandSlambert
 * @copyright 2009-2011
 * @access public
 * @since 0.1
 */
?>

<input type="hidden" name="dates_noncename" id="dates_noncename" value="<?php echo wp_create_nonce('calendar_press_dates'); ?>" />

<div class="detailsbox">
     <div class="details-minor">

          <div class="event-date">
               <label for="event_start"><?PHP _e('Start Date', 'calendar-press'); ?></label>

               <?php event_date_form($post, 'begin', 'date'); ?>

          </div>
          <div class="event-date">
               <label for="event_start"><?PHP _e('Start Time', 'calendar-press'); ?></label>

               <?php event_date_form($post, 'begin', 'time'); ?>

               <select id="beginMeridiem" name="beginMeridiem">
                    <option value="0" <?php event_meridiem($post, 'begin', 'am'); ?>>A.M.</option>
                    <option value="12" <?php event_meridiem($post, 'begin', 'pm'); ?>>P.M.</option>
               </select>


          </div>
          <div class="event-date">
               <label for="event_start"><?PHP _e('End Date', 'calendar-press'); ?></label>

               <?php event_date_form($post, 'end', 'date'); ?>


          </div>
          <div class="event-date">
               <label for="event_start"><?PHP _e('End Time', 'calendar-press'); ?></label>

               <?php event_date_form($post, 'end', 'time'); ?>
               <select id="endMeridiem" name="endMeridiem">
                    <option value="0" <?php event_meridiem($post->ID, 'end', 'am'); ?>>A.M.</option>
                    <option value="12" <?php event_meridiem($post->ID, 'end', 'pm'); ?>>P.M.</option>
               </select>

          </div>
     </div>
</div>