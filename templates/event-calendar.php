<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage CalendarPress
 * @since CalendarPress 0.1
 */

?>

<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;" class="overDiv"></div>

<div class="cp-navigation">
    <div class="cp-prev-month"><?php the_event_last_month(); ?></div>
    <div class="cp-curr-month"><?php the_event_this_month(); ?></div>
    <div class="cp-next-month"><?php the_event_next_month(); ?></div>
</div>
<dl class="cp-list-dow">
    <dt class="cp-box-width"><?php _e('Sunday'); ?></dt>
    <dt class="cp-box-width"><?php _e('Monday'); ?></dt>
    <dt class="cp-box-width"><?php _e('Tuesday'); ?></dt>
    <dt class="cp-box-width"><?php _e('Wednesday'); ?></dt>
    <dt class="cp-box-width"><?php _e('Thursday'); ?></dt>
    <dt class="cp-box-width"><?php _e('Friday'); ?></dt>
    <dt class="cp-box-width"><?php _e('Saturday'); ?></dt>
</dl>
<div class="cleared" style="clear:both"></div>

<?php event_calendar($this->currMonth, $this->currYear); ?>

<div class="cleared" style="clear:both"></div>
