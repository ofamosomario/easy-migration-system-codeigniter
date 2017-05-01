<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2006 - 2012, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Migration Class
 *
 * All migrations should implement this, forces up() and down() and gives
 * access to the CI super-global.
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Reactor Engineers
 * @link
 */
class CI_Migration {

	protected $_migration_enabled = FALSE;
	protected $_migration_path = NULL;
	protected $_migration_version = 0;

	protected $_error_string = '';

	public function __construct($config = array())
	{
		# Only run this constructor on main library load
		if (get_parent_class($this) !== FALSE)
		{
			return;
		}

		foreach ($config as $key => $val)
		{
			$this->{'_' . $key} = $val;
		}

		log_message('debug', 'Migrations class initialized');

		// Are they trying to use migrations while it is disabled?
		if ($this->_migration_enabled !== TRUE)
		{
			show_error('Migrations has been loaded but is disabled or set up incorrectly.');
		}

		// If not set, set it
		$this->_migration_path == '' AND $this->_migration_path = APPPATH . 'migrations/';

		// Add trailing slash if not set
		$this->_migration_path = rtrim($this->_migration_path, '/').'/';

		// Load migration language
		$this->lang->load('migration');

		// They'll probably be using dbforge
		$this->load->dbforge();

		// If the migrations table is missing, make it
		if ( ! $this->db->table_exists('migrations'))
		{
			$this->dbforge->add_field(array(
				'version' => array('type' => 'INT', 'constraint' => 3),
			));

			$this->dbforge->create_table('migrations', TRUE);

			$this->db->insert('migrations', array('version' => 0));
		}
	}

        public function get_name($target_version){
		$start = $current_version = $target_version-1;
		$stop = $target_version;
                
		if ($target_version > $current_version)
		{
			// Moving Up
			++$start;
			++$stop;
			$step = 1;
		}
		else
		{
			// Moving Down
			$step = -1;
		}
                
		$method = ($step === 1) ? 'up' : 'down';
		$migrations = array();
                
		// We now prepare to actually DO the migrations
		// But first let's make sure that everything is the way it should be
		for ($i = $start; $i != $stop; $i += $step)
		{  
			$f = glob(sprintf($this->_migration_path . '%03d_*.php', $i));
			// Only one migration per step is permitted
			if (count($f) > 1)
			{
				$this->_error_string = sprintf($this->lang->line('migration_multiple_version'), $i);
				return FALSE;
			}

			if ( isset( $f[0] ) ) { 
				$name = basename($f[0], '.php');
			} else {
				return FALSE;
			}
        
		}   
                
                return $name;
        }
        
	// --------------------------------------------------------------------

	/**
	 * Migrate to a schema version
	 *
	 * Calls each migration step required to get to the schema version of
	 * choice
	 *
	 * @param	int	Target schema version
	 * @return	mixed	TRUE if already latest, FALSE if failed, int if upgraded
	 */
	public function version($target_version)
	{    
		$start = $current_version = $target_version-1;
		$stop = $target_version;
                
		if ($target_version > $current_version)
		{
			// Moving Up
			++$start;
			++$stop;
			$step = 1;
		}
		else
		{
			// Moving Down
			$step = -1;
		}
                
		$method = ($step === 1) ? 'up' : 'down';
		$migrations = array();
                
		// We now prepare to actually DO the migrations
		// But first let's make sure that everything is the way it should be
		for ($i = $start; $i != $stop; $i += $step)
		{  
			$f = glob(sprintf($this->_migration_path . '%03d_*.php', $i));
			// Only one migration per step is permitted
			if (count($f) > 1)
			{
				$this->_error_string = sprintf($this->lang->line('migration_multiple_version'), $i);
				return FALSE;
			}

			if ( isset( $f[0] ) ) { 
				$name = basename($f[0], '.php');
			} else {
				return FALSE;
			}

        
			// Filename validations
			if (preg_match('/^\d{3}_(\w+)$/', $name, $match))
			{
				$match[1] = strtolower($match[1]);

				// Cannot repeat a migration at different steps
				if (in_array($match[1], $migrations))
				{
					$this->_error_string = sprintf($this->lang->line('migration_multiple_version'), $match[1]);
					return FALSE;
				}

				include $f[0];
				$class = 'Migration_' . ucfirst($match[1]);

				if ( ! class_exists($class))
				{
					$this->_error_string = sprintf($this->lang->line('migration_class_doesnt_exist'), $class);
					return FALSE;
				}

				if ( ! is_callable(array($class, $method)))
				{
					$this->_error_string = sprintf($this->lang->line('migration_missing_'.$method.'_method'), $class);
					return FALSE;
				}

				$migrations[] = $match[1];
			}
		}

		log_message('debug', 'Migration atual: ' . $current_version);

		$version = $i + ($step == 1 ? -1 : 0);

		// If there is nothing to do so quit
		if ($migrations === array())
		{
			return TRUE;
		}

		log_message('debug', 'Migrating from ' . $method . ' to version ' . $version);
                
		// Loop through the migrations
	
			// Run the migration class
			$class = 'Migration_' . ucfirst(strtolower(end($migrations)));
			call_user_func(array(new $class, $method));

			$current_version += $step;
			$this->_update_version($current_version);
		

		log_message('debug', 'Finished migrating to '.$current_version);
                
		return end($migrations);
	}

	// --------------------------------------------------------------------
        
	/**
	 * Set's the schema to the latest migration
	 *
	 * @return	mixed	true if already latest, false if failed, int if upgraded
	 */
	public function latest()
	{
		if ( ! $migrations = $this->find_migrations())
		{
			$this->_error_string = $this->lang->line('migration_none_found');
			return false;
		}

		$last_migration = basename(end($migrations));

		// Calculate the last migration step from existing migration
		// filenames and procceed to the standard version migration
		return $this->version((int) substr($last_migration, 0, 3));
	}

	// --------------------------------------------------------------------

	/**
	 * Set's the schema to the migration version set in config
	 *
	 * @return	mixed	true if already current, false if failed, int if upgraded
	 */
	public function current()
	{
		return $this->version($this->_migration_version);
	}

	// --------------------------------------------------------------------

	/**
	 * Error string
	 *
	 * @return	string	Error message returned as a string
	 */
	public function error_string()
	{
		return $this->_error_string;
	}

	// --------------------------------------------------------------------

	/**
	 * Set's the schema to the latest migration
	 *
	 * @return	mixed	true if already latest, false if failed, int if upgraded
	 */
	protected function find_migrations()
	{
		// Load all *_*.php files in the migrations path
		$files = glob($this->_migration_path . '*_*.php');
		$file_count = count($files);

		for ($i = 0; $i < $file_count; $i++)
		{
			// Mark wrongly formatted files as false for later filtering
			$name = basename($files[$i], '.php');
			if ( ! preg_match('/^\d{3}_(\w+)$/', $name))
			{
				$files[$i] = FALSE;
			}
		}

		sort($files);
		return $files;
	}

	// --------------------------------------------------------------------

	/**
	 * Retrieves current schema version
	 *
	 * @return	int	Current Migration
	 */
	protected function _get_version()
	{
		$row = $this->db->get('migrations')->row();
		return $row ? $row->version : 0;
	}

	// --------------------------------------------------------------------

	/**
	 * Stores the current schema version
	 *
	 * @param	int	Migration reached
	 * @return	bool
	 */
	protected function _update_version($migrations)
	{
		return $this->db->update('migrations', array(
			'version' => $migrations
		));
	}

	// --------------------------------------------------------------------

	/**
	 * Enable the use of CI super-global
	 *
	 * @param	mixed	$var
	 * @return	mixed
	 */
	public function __get($var)
	{
		return get_instance()->$var;
	}
    
    function primary_key($fields=array()) {
        foreach ($fields as $key => $field) {
            if(isset($field['primary_key']) && $field['primary_key'] === TRUE) {
                $this->dbforge->add_key($key, TRUE);
            }
        }
    }

    function innodb($config) {
        if(isset($config['innodb']) && $config['innodb'] === TRUE){
            $table = $config['table'];
            $sql = "ALTER TABLE  `$table` ENGINE = INNODB";
            $this->db->query($sql);
        }
    }

    function unique($table='', $fields=array() ) {
        foreach ($fields as $key => $field) {
            if(isset($field['unique']) && $field['unique'] === TRUE) {
                $sql = "ALTER TABLE
                            `$table`
                        ADD UNIQUE (`$key`)";
                $this->db->query($sql);
            }
        }
    }

    function foreigns_keys($table, $fields=array()) {
        foreach ($fields as $key => $field) {
            $table_help = "$table".'_FK_';
            if(isset($field['foreign_key']) && $field['foreign_key']) {
                $to_table = $field['foreign_key']['table'];
                $to_field = $field['foreign_key']['field'];
                $tipo_up = $field['foreign_key']['update'];
                $tipo_del = $field['foreign_key']['delete'];
                $sql = "ALTER TABLE  `$table`
                        ADD CONSTRAINT `$table_help"."$key`
                        FOREIGN KEY (`$key`)
                        REFERENCES `$to_table` (`$to_field`)
                        ON UPDATE $tipo_up
                        ON DELETE $tipo_del";
                $this->db->query($sql);
            }
        }
    }

    function create_table($config) {
        $fields = $config['fields'];
        $table = $config['table'];
        
        $this->dbforge->add_field($fields);
        $this->primary_key($fields);
        $this->dbforge->create_table($table, TRUE);
        $this->innodb($config);
        $this->unique($table, $fields);
        $this->foreigns_keys($table, $fields);
    }

    // https://github.com/rowend/codeigniter-migrations-mysql
        
}

/* End of file Migration.php */
/* Location: ./system/libraries/Migration.php */