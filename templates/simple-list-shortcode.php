<?php
if ( preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']) ) {
     die('You are not allowed to call this page directly.');
}

/**
 * sinple-list-shortcode.php - A simple event list for the shortcode.
 *
 * @package CalendarPress
 * @subpackage templates
 * @author GrandSlambert
 * @copyright 2009-2011
 * @access public
 * @since 0.1
 */
?>

<ul class="calendar-press-list-widget" style="">
     <?php while ($posts->have_posts()) : $posts->the_post(); ?>

          <li style="line-height: 25px; border-bottom: solid 1px #ddd;">
               <a href="<?php the_permalink(); ?>" target="<?php echo $atts['target']; ?>"><?php the_title(); ?></a>
               on <?php the_start_date(); ?>
               from <?php the_start_time(); ?> to <?php the_end_time(); ?>
          </li>

     <?php endwhile; ?>
</ul>