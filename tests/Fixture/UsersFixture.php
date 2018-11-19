<?php
namespace Hashid\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 */
class UsersFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer'],
        'username' => ['type' => 'string', 'null' => false, 'default' => '', 'length' => 10],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]]
    ];

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'username' => 'Mr',
        ],
        [
            'username' => 'Mrs',
        ],
    ];
}
