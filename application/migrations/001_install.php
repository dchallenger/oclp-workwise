<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * config table schema
 * 
 * @version 1
 */

class Migration_Install extends CI_Migration
{		
	private $_mysql_path = ''; 

	public function up()
	{		
		$this->_mysql_path = $this->db->mysql_path;
		$sql_path          = $this->db->install_file;		

		$this->_drop_tables();

		if (!file_exists($sql_path)) {
			show_error('Install script missing. Make sure the file "' . $sql_path . '" exists.');
		} else {
			$mysqli = new mysqli($this->db->hostname, $this->db->username, $this->db->password, $this->db->database);

			/* check connection */
			if (mysqli_connect_errno()) {
			    show_error("Connect failed: %s\n", mysqli_connect_error());			 
			}
						
			$command = $this->_mysql_path . ' -u ' . $this->db->username;

			if ($this->db->password != '') {
				$command .= ' -p ' . $this->db->password;
			}
			
			$command .= ' ' . $this->db->database . ' < ' . $sql_path;
						
			exec($command);		
		}
	}

	public function down()
	{
		$this->_drop_tables();
	}

	private function _drop_tables() {
		$tables = $this->db->list_tables();

		foreach ($tables as $table) {
			// Drop all tables except for session table because our setup will not work with the session table.
			if ($table != $this->db->dbprefix . $this->config->item('sess_table_name')) {								
				// Disable keys so we don't get foreign key check errors. Don't need them since we are going to redump the whole schema later.
				$this->db->query('ALTER TABLE ' . $table . ' DISABLE KEYS;');
				$this->db->query('SET FOREIGN_KEY_CHECKS=0;');
				$this->db->query('DROP TABLE IF EXISTS ' . $this->db->_escape_identifiers($table));		
			}
		}		
	}
}