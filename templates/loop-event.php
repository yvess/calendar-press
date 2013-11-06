<?php if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

/**
 * loop-event.php - The Template for displaying an event in the loop.
 *
 * @package CalendarPress
 * @subpackage templates
 * @author GrandSlambert
 * @copyright 2009-2011
 * @access public
 * @since 1.0
 */

?>
<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="event-meta">
        <div class="event-dates"><?php the_event_dates(); ?></div>
    </div>

    <div class="entry-content">
        <?php the_content(); ?>
    </div>
</div><!-- #post-## -->
