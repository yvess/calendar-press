<?php
if ( preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']) ) {
     die('You are not allowed to call this page directly.');
}

/**
 * locations-box.php - Adds the box with event location fields.
 *
 * @package CalendarPress
 * @subpackage includes/meta-boxes
 * @author GrandSlambert
 * @copyright 2009-2011
 * @access public
 * @since 0.3
 */
?>

<input type="hidden" name="location_noncename" id="location_noncename" value="<?php echo wp_create_nonce('calendar_press_location'); ?>" />

<div class="detailsbox">
     <div class="details-minor">
          <div class="event-details">
               <label for="location_lookup"><?php _e('Search for Location', 'calendar-press'); ?>:</label>
               <input type="text" name="event_lookup" id="event_lookup" />
          </div>
          <div id="location_details" class="event-details" style="display: none">
               <h4><?php _e('Location Details', 'calendar-press'); ?></h4>
          </div>
     </div>
</div>