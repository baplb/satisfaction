<?php

class PluginSatisfactionSurvey extends CommonDBTM {
   function canCreate() {
      return Session::haveRight('config', 'w');
   }

   function canView() {
      return Session::haveRight('config', 'w');
   }

   static function getTypeName() {
      global $LANG;
      return $LANG['plugin_satisfaction']['survey']['name'];
   }

   static function install(Migration $migration) {
      global $DB;

      //create table
      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $query = "CREATE TABLE `$table` (
                           `id` INT( 11 ) NOT NULL AUTO_INCREMENT,
                           `entities_id` INT( 11 ) NOT NULL DEFAULT 0,
                           `is_recursive` TINYINT(1) NOT NULL default '0',
                           `is_active` TINYINT(1) NOT NULL default '0',
                           `name` VARCHAR(255) collate utf8_unicode_ci default NULL,
                           `comment` TEXT collate utf8_unicode_ci default NULL,
                           PRIMARY KEY ( `id` )
                           ) ENGINE = MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die($DB->error());
      }

      return true;
   }

   static function uninstall() {
      global $DB;
      
      $query = "DROP TABLE IF EXISTS `".getTableForItemType(__CLASS__)."`";
      return $DB->query($query) or die($DB->error());
   }


   function defineTabs($options=array()) {
      $ong = array();
      $this->addStandardTab('PluginSatisfactionSurveyQuestion', $ong, $options);
      return $ong;
   }


   function showForm($ID, $options=array()) {
      global $CFG_GLPI, $LANG;

      if (!$this->isNewID($ID)) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w');
      }
      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['common'][16]."&nbsp;:&nbsp;</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";
      echo "<td>".$LANG['common'][25]."&nbsp;:&nbsp;</td>";
      echo "<td>";
      echo "<textarea cols='60' rows='6' name='comment' >".$this->fields["comment"]."</textarea>";
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['common'][60]."&nbsp;:&nbsp;</td>";
      echo "<td>";
      Dropdown::showYesNo("is_active", $this->fields["is_active"]);
      echo"</td></tr>\n";

      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;
   }

   function prepareInputForAdd($input) {
      global $LANG;

      //we must store only one survey by entity
      $found = $this->find("entities_id = ".$input['entities_id']);
      if (count($found) > 0) {
         Session::addMessageAfterRedirect($LANG['plugin_satisfaction']['survey']['error'][0]);
         return false;
      }

      return $input;
   }
}