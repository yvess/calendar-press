<?php
if ( preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']) ) {
     die('You are not allowed to call this page directly.');
}
/**
 * features.php - View for the features tab.
 *
 * @package CalendarPress
 * @subpackage includes
 * @author GrandSlambert
 * @copyright 2009-2011
 * @access public
 * @since 0.5
 */
?>
<div style="width:49%; float:left">
     <div class="postbox">
          <h3 class="handl" style="margin:0;padding:3px;cursor:default;">
               <?php _e('Locations', 'calendar-press'); ?>
          </h3>
          <div class="table">
               <table class="form-table cp-table">
                    <tbody>
                         <tr align="top">
                              <th scope="row"><label for="calendar_press_location_slug"><?php _e('Location Slug', 'calendar-press'); ?></label></th>
                              <td colspan="3">
                                   <input type="text" name="<?php echo $this->optionsName; ?>[location-slug]" id="calendar_press_location_slug" value="<?php echo $this->options['location-slug']; ?>" />
                                   <?php $this->help(esc_js(__('This will be used as the slug (URL) for the locations index.', 'calendar-press'))); ?>
                                   <a href="<?php echo get_option('home'); ?>/<?php echo $this->options['location-slug']; ?>"><?php _e('View on Site', 'calendar-press'); ?></a>
                              </td>
                         </tr>
                         <tr align="top">
                              <th scope="row"><label for="calendar_press_location_identifier"><?php _e('Location Identifier', 'calendar-press'); ?></label></th>
                              <td>
                                   <input class="input" type="text" name="<?php echo $this->optionsName; ?>[location-identifier]" id="calendar_press_location_identifier" value="<?php echo $this->options['location-identifier']; ?>" />
                                   <?php $this->help(esc_js(__('This will be used in the permalink structure to identify the custom type for locations.', 'calendar-press'))); ?>
                              </td>
                         </tr>
                         <tr align="top">
                              <th scope="row"><label for="calendar_press_location_permalink"><?php _e('Location Permalink Structure'); ?></label></th>
                              <td>
                                   <input class="widefat" type="text" name="<?php echo $this->optionsName; ?>[locationpermalink]" id="calendar_press_location_permalink" value="<?php echo $this->options['location-permalink']; ?>" />
                              </td>
                         </tr>
                         <tr align="top">
                              <th scope="row"><label for="calendar_press_location_plural_name"><?php _e('Location Plural Name', 'calendar-press'); ?></label></th>
                              <td>
                                   <input type="text" name="<?php echo $this->optionsName; ?>[location-plural-name]" id="calendar_press_location_plural_name" value="<?php echo $this->options['location-plural-name']; ?>" />
                                   <?php $this->help(esc_js(__('Plural name to use in the menus for this plugin.', 'calendar-press'))); ?>
                              </td>
                         </tr>
                         <tr align="top">
                              <th scope="row"><label for="calendar_press_singular_location_name"><?php _e('Location Singular Name', 'calendar-press'); ?></label></th>
                              <td>
                                   <input type="text" name="<?php echo $this->optionsName; ?>[location-singular-name]" id="calendar_press_singular_location_name" value="<?php echo $this->options['location-singular-name']; ?>" />
                                   <?php $this->help(esc_js(__('Singular name to use in the menus for this plugin.', 'calendar-press'))); ?>
                              </td>
                         </tr>
                    </tbody>
               </table>
          </div>
     </div>
</div>
<div  style="width:49%; float:right">
     <div class="postbox">
          <h3 class="handl" style="margin:0; padding:3px;cursor:default;"><?php _e('Permalink Instructions', 'calendar-press'); ?></h3>
          <div style="padding:8px;">
               <p>
                    <?php
                                   printf(__('The permalink structure will be used to create the custom URL structure for your individual events. These follow WP\'s normal %1$s, but must also include the content type %2$s and at least one of these unique tags: %3$s or %4$s.', 'calendar-press'),
                                           '<a href="http://codex.wordpress.org/Using_Permalinks" target="_blank">' . __('permalink tags', 'calendar-press') . '</a>',
                                           '<strong>%identifier%</strong>',
                                           '<strong>%postname%</strong>',
                                           '<strong>%post_id%</strong>'
                                   );
                    ?>
                              </p>
                              <p>
                    <?php _e('Allowed tags: %year%, %monthnum%, %day%, %hour%, %minute%, %second%, %postname%, %post_id%', 'calendar-press'); ?>
                              </p>
                              <p>
                    <?php
                                   printf(__('For complete instructions on how to set up your permaliks, visit the %1$s.', 'calendar-press'),
                                           '<a href="http://wiki.calendarpress.net/wiki/Recipe_Permalinks" target="blank">' . __('Documentation Page', 'calendar-press') . '</a>'
                                   );
                    ?>
                              </p>
                         </div>
                    </div>
                    <div class="postbox">
                         <h3 class="handl" style="margin:0; padding:3px;cursor:default;"><?php _e('Available Shortcodes', 'calendar-press'); ?></h3>
                         <div style="padding:8px">
                              <p><?php _e('There are several shortcodes available in CalendarPress, but the most useful will likely be [event-list] and [event-calendar].', 'calendar-press'); ?></p>
                              <ul>
                                   <li><strong>[event-list]</strong>: <?php printf(__('Used to display a list of events. [%1$s]'), '<a href="http://wiki.calendarpress.net/wiki/Event-list" target="_blank">' . __('Documentation', 'calendar-press') . '</a>'); ?></li>
                                   <li><strong>[event-calendar]</strong>: <?php printf(__('Used to display a calendar of events. [%1$s]'), '<a href="http://wiki.calendarpress.net/wiki/Event-calendar" target="_blank">' . __('Documentation', 'calendar-press') . '</a>'); ?></li>
                                   <li><strong>[event-show]</strong>: <?php printf(__('Used to display a single event on any post or page. [%1$s]'), '<a href="http://wiki.calendarpress.net/wiki/event-show" target="_blank">' . __('Documentation', 'calendar-press') . '</a>'); ?></li>
               </ul>
          </div>
     </div>
</div>