# Easy Migratoin System for Codeigniter

We also are using: https://github.com/rowend/codeigniter-migrations-mysql, for more information, clink in the link.

### How to Instal:

- Clone the repository.
- Change this line: 'PAHT_TO/application/migrations' for your current path.
- Create the migration_logs table.

```sh
CREATE TABLE migration_logs (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255) NOT NULL,
    version VARCHAR(5) NOT NULL
)
```

### Features:

 - Visual Migration System for Codeigniter.
 - Create FK and relationship betweens table.
 - Search for migrations in your migration folder easly.

### Improvements:

- Error views

### Example to create table:

```sh

<?php
    class 001_chocolate extends MyMigration {

        var $table;
        
        function __construct() {
            $this->table = 'chocolate';
        }

        function up() {
            $id = array(
                'type' => 'int',
                'constraint' => 10,
                'unsigned' => TRUE,
                'null' => FALSE,
                'auto_increment' => TRUE,
                'primary_key' => TRUE, //primary_key
            );
            $name = array(
                'type' => 'varchar',
                'constraint' => 40,
                'unique' => TRUE, //unique field
            );
            $color = array(
                'type' => 'varchar',
                'constraint' => 10,
            );
            $flavor = array(
                'type' => 'varchar',
                'constraint' => 10,
            );
            $provider_id = array(
                'type' => 'int',
                'constraint' => 10,
                'unsigned' => TRUE,
                'null' => FALSE,
                'foreign_key' => array( //relationship
                    'table' => 'provider' // table to
                    'field' => 'id' // field to
                )
            );
            $fields = array(
                'id' => $id,
                'name' => $name,
                'color' => $color,
                'flavor' => $flavor
                'provider_id' => $provider_id
            );
            $config = array(
                'table' => $this->table,
                'fields' => $fields,
                'innodb' => TRUE //InnoDB
            );
            $this->create_table($config);
        }

        function down() {
            $this->dbforge->drop_table($this->table);
        }
    }
?>
```
