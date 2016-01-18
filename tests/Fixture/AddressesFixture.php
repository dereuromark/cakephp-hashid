<?php
namespace Hashid\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AddressFixture
 *
 */
class AddressesFixture extends TestFixture {

	/**
	 * Fields
	 *
	 * @var array
	 */
	public $fields = [
		'id' => ['type' => 'integer'],
		'user_id' => ['type' => 'integer', 'null' => true, 'default' => null],
		'street' => ['type' => 'string', 'null' => false, 'default' => '', 'length' => 100, 'comment' => ''],
		'postal_code' => ['type' => 'string', 'null' => false, 'default' => '', 'length' => 10],
		'city' => ['type' => 'string', 'null' => false, 'default' => '', 'length' => 100],
		'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
		'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
		'_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]]
	];

	/**
	 * Records
	 *
	 * @var array
	 */
	public $records = [
		[
			//'id' => '1', // ID 2 => 'jR'
			'user_id' => 1,
			'street' => 'Langstrasse 10',
			'postal_code' => '101010',
			'city' => 'München',
			'created' => '2011-04-21 16:50:05',
			'modified' => '2011-10-07 17:42:27',
		],
		[
			//'id' => '2', // ID 2 => 'k5'
			'user_id' => 2,
			'street' => 'Xyz 20',
			'postal_code' => '123',
			'city' => 'NoHashId',
			'created' => '2011-04-21 16:50:05',
			'modified' => '2011-10-07 17:42:27',
		],
	];
}
