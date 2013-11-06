<?php
if ( preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']) ) {
     die('You are not allowed to call this page directly.');
}

/**
 * single-event.php - The Template for displaying an event.
 *
 * @package CalendarPress
 * @subpackage templates
 * @author GrandSlambert
 * @copyright 2009-2011
 * @access public
 * @since 0.3
 */
?>
<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
     <div class="event-meta">
          <div class="event-dates"><?php the_event_dates(); ?></div>
          <div class="event-category"><?php the_event_category(); ?></div>
     </div>

     <div class="entry-content">
          <?php the_content(); ?>
     </div>

     <?php event_event_registrations(); ?>

          <div class="entry-content">
          <?php wp_link_pages(array('before' => '<div class="page-link">' . __('Pages:', 'calendar-press'), 'after' => '</div>')); ?>
          <?php edit_post_link(__('Edit Event', 'recipe-press'), '<span class="edit-link">', '</span>'); ?>
     </div><!-- .entry-content -->

</div><!-- #post-## -->
