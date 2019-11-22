<?php
namespace Hashid\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AddressFixture
 */
class CommentsFixture extends TestFixture {

	/**
	 * Fields
	 *
	 * @var array
	 */
	public $fields = [
		'id' => ['type' => 'integer'],
		'address_id' => ['type' => 'integer', 'null' => true, 'default' => null],
		'comment' => ['type' => 'string', 'null' => false, 'default' => '', 'length' => 100, 'comment' => ''],
		'_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
	];

	/**
	 * Records
	 *
	 * @var array
	 */
	public $records = [
		[
			'address_id' => 1,
			'comment' => 'Abc',
		],
		[
			//'id' => '2', // ID 2 => 'k5'
			'address_id' => 2,
			'comment' => 'Def',
		],
	];

}
