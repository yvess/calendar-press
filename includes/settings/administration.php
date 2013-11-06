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
 * @since 0.3
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
                         <tr align="top">
                              <th scope="row"><label for="calendar_press_reset_options"><?php _e('Reset to default: ', 'calendar-press'); ?></label></th>
                              <td><input type="checkbox" id="calendar_press_reset_options" name="confirm-reset-options" value="1" onclick="verifyResetOptions(this)" /></td>
                         </tr>
                         <!--
                         <tr align="top">
                              <th scope="row"><label for="calendar_press_backup_options"><?php _e('Back-up Options: ', 'calendar-press'); ?></label></th>
                              <td><input type="checkbox" id="calendar_press_backup_options" name="confirm-backup-options" value="1" onclick="backupOptions(this)" /></td>
                         </tr>
                         <tr align="top">
                              <th scope="row"><label for="calendar_press_restore_options"><?php _e('Restore Options: ', 'calendar-press'); ?></label></th>
                              <td><input type="file" id="calendar_press_restore_options" name="calendar-press-restore-options"/></td>
                         </tr>
                         -->
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
               <?php printf(__('Visit the %1$s page for insructions for this page', 'calendar-press'), '<a href="http://wiki.calendarpress.net/wiki/Settings#Administration" target="_blank">' . __('Documentation', 'calendar-press') . '</a>'); ?>
          </div>
     </div>
</div>