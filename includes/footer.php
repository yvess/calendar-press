<?php
if ( preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']) ) {
     die('You are not allowed to call this page directly.');
}
/**
 * footer.php - View for the footer of all special pages.
 *
 * @package CalendarPress
 * @subpackage includes
 * @author GrandSlambert
 * @copyright 2009-2011
 * @access public
 * @since 0.1
 */
?>

<div style="clear:both;">
     <div class="postbox" style="width:49%; float:left">
          <h3 class="handl" style="margin:0; padding:3px;cursor:default;"><?php _e('Credits', 'calendar-press'); ?></h3>
          <div style="padding:8px;">
               <p>
                    <?php
                    printf(__('Thank you for trying the %1$s plugin - I hope you find it useful. For the latest updates on this plugin, vist the %2$s. If you have problems with this plugin, please use our %3$s. For help using this plugin, visit the %4$s.', 'calendar-press'),
                            $this->pluginName,
                            '<a href="http://calendarpress.net" target="_blank">' . __('official site', 'calendar-press') . '</a>',
                            '<a href="http://support.calendarpress.net" target="_blank">' . __('Support Forum', 'calendar-press') . '</a>',
                            '<a href="http://wiki.calendarpress.net" target="_blank">' . __('Documentation Page', 'calendar-press') . '</a>'
                    ); ?>
               </p>
               <p>
                    <?php
                    printf(__('This plugin is &copy; %1$s by %2$s and is released under the %3$s', 'calendar-press'),
                            '2009-' . date("Y"),
                            '<a href="http://grandslambert.com" target="_blank">GrandSlambert, Inc.</a>',
                            '<a href="http://www.gnu.org/licenses/gpl.html" target="_blank">' . __('GNU General Public License', 'calendar-press') . '</a>'
                    );
                    ?>
               </p>
          </div>
     </div>
     <div class="postbox" style="width:49%; float:right">
          <h3 class="handl" style="margin:0; padding:3px;cursor:default;"><?php _e('Donate', 'calendar-press'); ?></h3>
          <div style="padding:8px">
               <p>
<?php printf(__('If you find this plugin useful, please consider supporting this and our other great %1$s.', 'calendar-press'), '<a href="http://wordpress.grandslambert.com/plugins.html" target="_blank">' . __('plugins', 'calendar-press') . '</a>'); ?>
                    <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=10528216" target="_blank"><?php _e('Donate a few bucks!', 'calendar-press'); ?></a>
               </p>
               <p style="text-align: center;"><a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=10528216"><img width="122" height="47" alt="paypal_btn_donateCC_LG" src="http://grandslambert.com/files/2010/06/btn_donateCC_LG.gif" title="paypal_btn_donateCC_LG" class="aligncenter size-full wp-image-174"/></a></p>
          </div>
     </div>
</div>