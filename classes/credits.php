<?php
if ( preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']) ) {
     die('You are not allowed to call this page directly.');
}

/**
 * credits.php - View for the credits page.
 *
 * @package CalendarPress
 * @subpackage classes
 * @author GrandSlambert
 * @copyright 2009-2011
 * @access public
 * @since 0.4
 */
class calendar_press_credits extends calendar_press_core {

     private $makeLink = false;

     /**
      * Display the contributors list.
      *
      * @param <string> $type
      * @return <string>
      */
     public function contributor_list($type = 'contributors') {
          $this->showFields = array('NAME', 'LOCATION', 'COUNTRY');
          echo '<ul>';

          $xml_parser = xml_parser_create();
          xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
          xml_set_element_handler($xml_parser, array($this, "xml_start_element"), array($this, "xml_end_element"));
          xml_set_character_data_handler($xml_parser, array($this, "xml_character_data"));

          if ( !($fp = @fopen($this->xmlURL . $type . '.xml', "r")) ) {
               /* translators: Message displayed when the contributors list cannot be accessed. */
               _e('There was an error getting the list. Try again later.', 'calendar-press');
               return;
          }

          while ($data = fread($fp, 4096)) {
               if ( !xml_parse($xml_parser, $data, feof($fp)) ) {
                    die(sprintf("XML error: %s at line %d",
                                    xml_error_string(xml_get_error_code($xml_parser)),
                                    xml_get_current_line_number($xml_parser)));
               }
          }

          xml_parser_free($xml_parser);
          echo '</ul>';
     }

     function xml_start_element($parser, $name, $attrs) {
          if ( $name == 'NAME' ) {
               echo '<li class="rp-contributor">';
          } elseif ( $name == 'ITEM' ) {
               /* translators: Used on the Contributors list to denote what a person contributed. */
               echo '<br><span class="rp_contributor_notes">' . __('Contributed: ', 'calendar-press');
          }

          if ( $name == 'URL' ) {
               $this->makeLink = true;
          }
     }

     function xml_end_element($parser, $name) {
          if ( $name == 'ITEM' ) {
               echo '</li>';
          } elseif ( $name == 'ITEM' ) {
               echo '</span>';
          } elseif ( in_array($name, $this->showFields) ) {
               echo ', ';
          }


          $this->makeLink = false;
     }

     function xml_character_data($parser, $data) {
          if ( $this->makeLink ) {
               echo '<a href="http://' . $data . '" target="_blank">' . $data . '</a>';
               $this->makeLink = false;
          } else {
               echo $data;
          }
     }

}

$cp_contrib = new calendar_press_credits;
?>

<div class="wrap">
     <div class="icon32" id="icon-calendar-press"><br/></div>
     <h2><?php echo $cp_contrib->pluginName; ?> &raquo; <?php _e('Contributors', 'calendar-press'); ?></h2>
     <p><?php _e('This page includes a list of CalendarPress users who have contributed time or money to the development of this plugin.', 'calendar-press'); ?></p>
     <div class="col-wrap">
          <div style="clear:both; margin-top:10px;">
               <div class="postbox" style="width:49%; float: left;">
                    <h3 class="handl" style="margin:0; padding:3px;cursor:default;"><?php _e('Major Contributors', 'calendar-press'); ?></h3>
                    <div style="padding:8px"><?php $cp_contrib->contributor_list('major'); ?></div>
               </div>
               <div class="postbox" style="width:49%; float: right;">
                    <h3 class="handl" style="margin:0; padding:3px;cursor:default;"><?php _e('Additional Contributors', 'calendar-press'); ?></h3>
                    <div style="padding:8px;"><?php $cp_contrib->contributor_list('contributors'); ?></div>
               </div>
          </div>
     </div>

</div>

<?php include($cp_contrib->pluginPath . '/includes/footer.php'); ?>