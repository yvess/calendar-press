<?php
if ( preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']) ) {
     die('You are not allowed to call this page directly.');
}

/**
 * details-box.php - Adds the box with addtional event details.
 *
 * @package CalendarPress
 * @subpackage includes/meta-boxes
 * @author GrandSlambert
 * @copyright 2009-2011
 * @access public
 * @since 0.1
 */
?>

<input type="hidden" name="details_noncename" id="details_noncename" value="<?php echo wp_create_nonce('calendar_press_details'); ?>" />

<div class="detailsbox">
     <div class="details-minor">

          <?php if ( $this->options['use-featured'] ) : ?>
               <div class="event-details event-details-featured">
                    <label for="event_featured"><?php _e('Featured Event:', 'calendar-press'); ?></label>
                    <input type="checkbox" class="checkbox" name="event_details[event_featured]" id="event_featured" value="1" <?php checked(get_post_meta($post->ID, '_event_featured_value', true), 1); ?> />
               </div>
          <?php endif; ?>

          <?php if ( $this->options['use-popups'] ) : ?>
                    <div class="event-details event-details-popup no-border">
                         <label for="event_popup"><?php _e('Enable popup:', 'calendar-press'); ?></label>
                         <input type="checkbox" class="checkbox" name="event_details[event_popup]" id="event_popup" value="1" <?php checked(get_post_meta($post->ID, '_event_popup_value', true), 1); ?> />
                    </div>
          <?php endif; ?>
     </div>
</div>