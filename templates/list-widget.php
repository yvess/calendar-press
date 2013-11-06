<?php
if ( preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']) ) {
     die('You are not allowed to call this page directly.');
}

/**
 * list-widget.php - Template to list the events in a sidebar widget.
 *
 * @package CalendarPress
 * @subpackage includes/meta-boxes
 * @author GrandSlambert
 * @copyright 2009-2011
 * @access public
 * @since 0.1
 */
?>

<ul class="calendar-press-list-widget">
     <?php while ($posts->have_posts()) : $posts->the_post(); ?>

          <li>
               <a href="<?php the_permalink(); ?>" target="<?php echo $instance['target']; ?>"><?php the_title(); ?></a>
               <br><?php the_start_date(); ?>
               <br><?php the_start_time(); ?> - <?php the_end_time(); ?>
          </li>

     <?php endwhile; ?>
</ul>