<?php
/**
 * Plugin Name: CM Update History
 * Description: Keeps a log of plugin and theme changes or updates done by the user and displays it on the homepage. 
 * Version: 1.0
 * Author: Chris Malone
 * Author URI: chrismalone.dev
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * CM Update history is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 * 
 * CM Update history is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with CM Update history. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 **/

 add_action('wp_dashboard_setup','cm_update_history_log_plugin_history');
 add_action( 'upgrader_process_complete', 'cm_update_history_log_plugin_history_update',10, 2);
 add_action('admin_menu', 'cm_update_history_log_create_admin_page');
 add_action('activated_plugin', 'cm_update_history_log_plugin_activation_update');
 add_action('deactivated_plugin', 'cm_update_history_log_plugin_deactivation_update');
 register_activation_hook( __FILE__, 'cm_update_history_log_create_update_history_table', 10 );
 
 /**********************************************************
  * Hooks plugin updates, sends the info to the database
  *********************************************************/
 function cm_update_history_log_plugin_history_update($upgrader, $options) {
	 global $wpdb;     
	 echo 'Updating History Log. . .<br>';
	 $table_name = $wpdb->prefix.'cm_update_history_log';
 
	 if( $options['action'] == 'update' && $options['type'] == 'plugin' ) {
		 foreach( $options['plugins'] as $plugin ) {
			 $wpdb->insert(
				 $table_name,
				 array(
					 'time' => current_time( 'mysql' ),
					 'action' => $options['action'],
					 'type' => $options['type'],
					 'name' => $plugin,
				 )
			 );
		 }
	 }
	 
	 else if( $options['action'] == 'update' && $options['type'] == 'theme' ) {
		 foreach( $options['themes'] as $theme ) {
			 $wpdb->insert(
				 $table_name,
				 array(
					 'time' => current_time( 'mysql' ),
					 'action' => $options['action'],
					 'type' => $options['type'],
					 'name' => $theme,
				 )
			 );
		 }
	 }
		 
	 else if( $options['action'] == 'update' && $options['type'] == 'core' ) {
		 $wpdb->insert(
			 $table_name,
			 array(
				 'time' => current_time( 'mysql' ),
				 'action' => $options['action'],
				 'type' => $options['type'],
				 'name' => 'Wordpress',
			 )
		 );
	 }
	 
	 echo 'Update complete<br>';
 }
 
 function cm_update_history_log_plugin_activation_update($plugin) {
	 global $wpdb;     
	 $table_name = $wpdb->prefix.'cm_update_history_log';
	 $wpdb->insert(
		 $table_name,
		 array(
			 'time' => current_time( 'mysql' ),
			 'action' => 'Activate',
			 'type' => 'Plugin',
			 'name' => $plugin,
		 )
	 );
 }
 
 function cm_update_history_log_plugin_deactivation_update($plugin) {
	 global $wpdb;     
	 $table_name = $wpdb->prefix.'cm_update_history_log';
	 $wpdb->insert(
		 $table_name,
		 array(
			 'time' => current_time( 'mysql' ),
			 'action' => 'Deactivate',
			 'type' => 'Plugin',
			 'name' => $plugin,
		 )
	 );
 }
 
 /**********************************************************
  * Create Dashboard Widget
  *********************************************************/
 function cm_update_history_log_plugin_history() {
	 wp_add_dashboard_widget('cm_update_history_log_plugin_history','Update History','cm_update_history_log_plugin_history_content');
 }
 
 /**********************************************************
  * Populate Dashboard Widget
  *********************************************************/
 function cm_update_history_log_plugin_history_content() {
	 global $wpdb;
	 $table_name = $wpdb->prefix.'cm_update_history_log';
	 $update_history = $wpdb->get_results($wpdb->prepare("
		 SELECT *
		 FROM " .$table_name. "
		 ORDER BY time DESC "));
	 echo "<div style='max-height: 350px; overflow-y: scroll;'><table style='border: solid 1px black; border-collapse: collapse;'>
		 <tr>
			 <th style='border: solid 1px black; border-collapse: collapse; color: white; background: black; padding: 3px 5px; text-align: center;'>Timestamp</th>
			 <th style='border: solid 1px black; border-collapse: collapse; color: white; background: black; padding: 3px 5px; text-align: center;'>Action</th>
			 <th style='border: solid 1px black; border-collapse: collapse; color: white; background: black; padding: 3px 5px; text-align: center;'>Name</th>
		 </tr>";
	 foreach($update_history as $update) {
		 echo "
		 <tr>
			 <td style='border: solid 1px black; border-collapse: collapse; padding: 3px 5px; text-align: center;'>".$update->time."</td>
			 <td style='border: solid 1px black; border-collapse: collapse; padding: 3px 5px; text-align: center;'>".ucfirst($update->action)." ".ucfirst($update->type)."</td>
			 <td style='border: solid 1px black; border-collapse: collapse; padding: 3px 5px; text-align: center;'>".strtok($update->name,'/')."</td>
		 </tr>";
	 }
	 echo "</table></div>";
 }
 
 /**********************************************************
  * Initialize the database table
  *********************************************************/
 function cm_update_history_log_create_update_history_table() {
	 global $wpdb;
 
	 $charset_collate = $wpdb->get_charset_collate();
 
	 $table_name = $wpdb->prefix . 'cm_update_history_log';
 
	 $sql = "CREATE TABLE $table_name (
		 id mediumint(9) NOT NULL AUTO_INCREMENT,
		 time datetime DEFAULT '00-00-0000 00:00' NOT NULL,
		 action tinytext NOT NULL,
		 type text NOT NULL,
		 name text NOT NULL,
		 url varchar(55) DEFAULT '' NOT NULL,
		 PRIMARY KEY  (id)
	 ) $charset_collate;";
 
	 require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	 dbDelta( $sql );
 }
 
 function cm_update_history_log_create_admin_page() {
	 add_submenu_page(
		 'tools.php',
		 'Update History',
		 'CM Update History',
		 'manage_options',
		 'cm-update-history',
		 'cm_update_history_log_content'
	 );
 }
 
 function cm_update_history_log_content() {
	 global $wpdb;
	 $table_name = $wpdb->prefix.'cm_update_history_log';
	 $update_history = $wpdb->get_results($wpdb->prepare("
		 SELECT *
		 FROM " .$table_name. "
		 ORDER BY time DESC "));
	 echo "<h2>Update History</h2><div style='max-height: 80vh; overflow-y: scroll;'><table style='background: white; width: 98%; margin-top: 20px; border: solid 1px black; border-collapse: collapse;'>
		 <tr>
			 <th style='border: solid 1px black; border-collapse: collapse; color: white; background: black; padding: 3px 5px; text-align: center;'>Timestamp</th>
			 <th style='border: solid 1px black; border-collapse: collapse; color: white; background: black; padding: 3px 5px; text-align: center;'>Action</th>
			 <th style='border: solid 1px black; border-collapse: collapse; color: white; background: black; padding: 3px 5px; text-align: center;'>Name</th>
		 </tr>";
	 foreach($update_history as $update) {
		 echo "
		 <tr>
			 <td style='border: solid 1px black; border-collapse: collapse; padding: 3px 5px; text-align: center;'>".$update->time."</td>
			 <td style='border: solid 1px black; border-collapse: collapse; padding: 3px 5px; text-align: center;'>".ucfirst($update->action)." ".ucfirst($update->type)."</td>
			 <td style='border: solid 1px black; border-collapse: collapse; padding: 3px 5px; text-align: center;'>".strtok($update->name,'/')."</td>
		 </tr>";
	 }
	 echo "</table></div>";
 }
 
 ?>