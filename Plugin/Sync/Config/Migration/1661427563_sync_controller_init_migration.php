<?php
class SyncControllerInitMigration extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = '';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
            'create_table' => [
                'sync_connections' => [
                    'id' => ['type' => 'integer', 'key' => 'primary'],
                    'name' => ['type' => 'string'],
                    'src_driver' => ['type' => 'string'],
                    'src_config' => ['type' => 'text'],
                    'dst_driver' => ['type' => 'string'],
                    'dst_config' => ['type' => 'text'],
                ],
                'sync_connection_fields' => [
                    'id' => ['type' => 'integer', 'key' => 'primary'],
                    'sync_connection_id' => ['type' => 'integer'],
                    'src_field' => ['type' => 'string'],
                    'dst_field' => ['type' => 'string'],
                    'src_config' => ['type' => 'text'],
                    'dst_config' => ['type' => 'text'],
                ],
            ],
		),
		'down' => array(
            'drop_table' => ['sync_connections', 'sync_connection_fields'],
		),
	);

/**
 * Before migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return boolean Should process continue
 */
	public function before($direction) {
		return true;
	}

/**
 * After migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return boolean Should process continue
 */
	public function after($direction) {
		return true;
	}
}
