<?php
namespace Hashid\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class TagsFixture extends TestFixture {

	/**
	 * Fields
	 *
	 * @var array
	 */
	public $fields = [
		'id' => ['type' => 'integer'],
		'name' => ['type' => 'string', 'null' => false, 'default' => '', 'length' => 100, 'comment' => ''],
		'_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
	];

	/**
	 * Records
	 *
	 * @var array
	 */
	public $records = [
		[
			'name' => 'Other',
		],
		[
			'name' => 'Another',
		],
	];

}
