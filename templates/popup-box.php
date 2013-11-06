<?php
if ( preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']) ) {
     die('You are not allowed to call this page directly.');
}

/**
 * popup-box.php - Template file for the pop-up box.
 *
 * @package CalendarPress
 * @subpackage templates
 * @author GrandSlambert
 * @copyright 2009-2011
 * @access public
 * @since 0.1
 */
?>
<div class="popup-contents">
    <div class="event_times_popup"><?php echo date('g:i a', get_post_meta($event->ID, '_begin_time_value', true)); ?></div>
    <div class="event_content_popup"><?php echo $event->post_content; ?></div>
</div>