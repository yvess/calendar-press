<?php
if ( preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']) ) {
     die('You are not allowed to call this page directly.');
}
/**
 * admin.php - View for the administration tab.
 *
 * @package CalendarPress
 * @subpackage includes
 * @author GrandSlambert
 * @copyright 2009-2011
 * @access public
 * @since 0.4
 */
?>
<div style="width:49%; float:left">

     <div class="postbox">
          <h3 class="handl" style="margin:0;padding:3px;cursor:default;">
               <?php _e('Administration', 'calendar-press'); ?>
          </h3>
          <div class="table">
               <table class="form-table cp-table">
                    <tbody>
                         <?php if ( $blog_id == 1 ) : ?>
                              <tr align="top">
                                   <th scope="row"><label for="calendar_press_default_site"><?php _e('Global Site: ', 'calendar-press'); ?></label></th>
                                   <td>
                                   <?php wp_dropdown_multisites(array('name' => 'dashboard_site]', 'id' => 'calendar_press_default_site', 'selected' => get_site_option('dashboard_blog'))); ?>
                                   <?php $this->help(__('What site should events be shared with if sharing is active?', 'calendar-press')); ?>
                              </td>
                         </tr>
                         <tr align="top">
                              <th scope="row"><label for="calendar_press_allow_moving"><?php _e('Allow moving events', 'calendar-press'); ?></label></th>
                              <td>
                                   <input type="checkbox" name="allow_moving_events" id="calendar_press_allow_moving" <?php checked(get_site_option('allow_moving_events')); ?> value="1" />
                                   <?php $this->help(__('Tick this checkbox if you want to allow users the ability to move events to a different site in the network.', 'calendar-press')); ?>
                              </td>
                         </tr>
                         <?php else : ?>
                                        <tr align="top">
                                             <th scope="row"><label for="calendar_press_share_events"><?php _e('Share events', 'calendar-press'); ?></label></th>
                                             <td>
                                                  <input type="checkbox" name="<?php echo $this->optionsName; ?>[share-events]" id="calendar_press_share_events" <?php checked($this->options['share-events']); ?> value="1" />
                                   <?php $this->help(__('Tick this checkbox if you want all events created on any site to be added to the . Links on the default site will link to the event on this site.', 'calendar-press')); ?>
                                   </td>
                              </tr>
                         <?php endif; ?>
                                   </tbody>
                              </table>
                         </div>
                    </div>
               </div>
               <div  style="width:49%; float:right">
                    <div class="postbox">
                         <h3 class="handl" style="margin:0;padding:3px;cursor:default;">
               <?php _e('Instructions', 'calendar-press'); ?>
                                   </h3>
                                   <div style="padding:8px">
               <?php printf(__('Visit the %1$s page for insructions for this page', 'calendar-press'), '<a href="http://wiki.calendarpress.net/wiki/Settings#Network" target="_blank">' . __('Documentation', 'calendar-press') . '</a>'); ?>
          </div>
     </div>
</div>