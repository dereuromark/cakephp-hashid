<?php
namespace Hashid\Test\Model\Behavior;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Hashids\Hashids;
use Hashid\Model\Behavior\HashidBehavior;

class HashidBehaviorTest extends TestCase
{

    /**
     * @var array
     */
    public $fixtures = [
        'plugin.Hashid.Addresses', 'plugin.Hashid.Users', 'plugin.Hashid.Comments'
    ];

    /**
     * @var \Cake\ORM\Table;
     */
    public $Addresses;

    /**
     * @var \Cake\ORM\Table;
     */
    public $Users;

    /**
     * @var \Cake\ORM\Table;
     */
    public $Comments;

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        Configure::write('Hashid', [
                'debug' => false,
            ]);
        Configure::write('Security', [
                'salt' => '' // For testing
            ]);

        $this->Addresses = TableRegistry::get('Hashid.Addresses');
        $this->Addresses->addBehavior('Hashid.Hashid');

        $this->Users = TableRegistry::get('Hashid.Users');
        $this->Users->addBehavior('Hashid.Hashid');

        $this->Comments = TableRegistry::get('Hashid.Comments');
        $this->Comments->addBehavior('Hashid.Hashid');

        //$this->Tags = TableRegistry::get('Hashid.Tags');
        //$this->Tags->addBehavior('Hashid.Hashid');

        $this->Addresses->hasMany('Hashid.Comments');
        $this->Addresses->belongsTo('Hashid.Users');
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Addresses);
        TableRegistry::clear();
    }

    /**
     * @return void
     */
    public function testSave()
    {
        $data = [
            'city' => 'Foo'
        ];
        $address = $this->Addresses->newEntity($data);
        $res = $this->Addresses->save($address);
        $this->assertTrue((bool)$res);

        $this->assertNull($address->hash);
    }

    /**
     * @return void
     */
    public function testFind()
    {
        $this->Addresses->behaviors()->Hashid->setConfig('field', 'hashid');

        $data = [
            'city' => 'Foo'
        ];
        $address = $this->Addresses->newEntity($data);
        $res = $this->Addresses->save($address);

        $id = $address->id;
        $hasher = new Hashids();
        $hashid = $hasher->encode($id);

        $address = $this->Addresses->find('hashed', [HashidBehavior::HID => $hashid])->first();
        $this->assertTrue((bool)$address);
    }

    /**
     * @return void
     */
    public function testFindList()
    {
        $this->Addresses->behaviors()->Hashid->setConfig('field', 'hashid');
        $this->Addresses->setDisplayField('city');
        $this->Addresses->setPrimaryKey('id');

        $data = [
            'city' => 'Foo'
        ];
        $address = $this->Addresses->newEntity($data);
        $res = $this->Addresses->save($address);

        $id = $address->id;
        $hasher = new Hashids();
        $hashid = $hasher->encode($id);

        $addressList = $this->Addresses->find('list')->toArray();
        $this->assertSame('Foo', $addressList[$hashid]);
    }

    /**
     * @return void
     */
    public function testSaveDebugMode()
    {
        $this->Addresses->behaviors()->Hashid->setConfig('field', 'hashid');
        $this->Addresses->behaviors()->Hashid->setConfig('debug', true);

        $data = [
            'city' => 'Foo'
        ];
        $address = $this->Addresses->newEntity($data);
        $res = $this->Addresses->save($address);
        $this->assertTrue((bool)$res);

        $this->assertSame('l5-3', $address->hashid);
    }

    /**
     * @return void
     */
    public function testSaveDebugModeIdField()
    {
        $this->Addresses->behaviors()->Hashid->setConfig('debug', true);

        $data = [
            'city' => 'Foo'
        ];
        $address = $this->Addresses->newEntity($data);
        $res = $this->Addresses->save($address);
        $this->assertTrue((bool)$res);

        $this->assertSame('l5-3', $address->id);

        $address = $this->Addresses->get('l5-3');
        $address->city = 'Foo Foo';
        $res = $this->Addresses->save($address);
        $this->assertTrue((bool)$res);

        $address = $this->Addresses->get('l5-3');
        $this->assertSame('Foo Foo', $address->city);

        $address->city = 'Foo Bar';
        $res = $this->Addresses->save($address);
        $this->assertTrue((bool)$res);

        $address->city = 'Foo Baz';
        $res = $this->Addresses->save($address);
        $this->assertTrue((bool)$res);
    }

    /**
     * @return void
     */
    public function testFindDebugMode()
    {
        Configure::write('debug', true);
        $this->Addresses->removeBehavior('Hashid');
        $this->Addresses->addBehavior('Hashid.Hashid', ['field' => 'hashid', 'debug' => null]);

        $data = [
            'city' => 'Foo'
        ];
        $address = $this->Addresses->newEntity($data);
        $res = $this->Addresses->save($address);

        $id = $address->id;
        $hasher = new Hashids();
        $hashid = $hasher->encode($id) . '-' . $id;

        $address = $this->Addresses->find('hashed', [HashidBehavior::HID => $hashid])->first();
        $this->assertTrue((bool)$address);
    }

    /**
     * @return void
     */
    public function testSaveWithField()
    {
        $this->Addresses->behaviors()->Hashid->setConfig('field', 'hash');

        $data = [
            'city' => 'Foo'
        ];
        $address = $this->Addresses->newEntity($data);
        $res = $this->Addresses->save($address);
        $this->assertTrue((bool)$res);
        $this->assertSame(3, $address->id);

        $hasher = new Hashids();
        $expected = $hasher->encode($address->id);
        $this->assertEquals($expected, $address->hash);
    }

    /**
     * @return void
     */
    public function testFindHashedWithField()
    {
        $this->Addresses->behaviors()->Hashid->setConfig('field', 'hash');

        $data = [
            'city' => 'Foo'
        ];
        $address = $this->Addresses->newEntity($data);
        $res = $this->Addresses->save($address);

        $hashid = $address->hash;

        $address = $this->Addresses->find('hashed', [HashidBehavior::HID => $hashid])->first();
        $this->assertTrue((bool)$address);
    }

    /**
     * @return void
     */
    public function testFindWithFieldFalse()
    {
        $this->Addresses->behaviors()->Hashid->setConfig('field', false);

        $address = $this->Addresses->find()->where(['city' => 'NoHashId'])->first();
        $hashid = $this->Addresses->encodeId($address->id);

        $this->Addresses->behaviors()->Hashid->setConfig('field', 'hash');

        // Should also be included now
        $address = $this->Addresses->find()->where(['city' => 'NoHashId'])->first();
        $this->assertSame($hashid, $address->hash);

        // Should also be included now
        $address = $this->Addresses->get($address->id);
        $this->assertSame($hashid, $address->hash);
    }

    /**
     * @return void
     */
    public function testFindWithIdAsField()
    {
        $this->Addresses->behaviors()->Hashid->setConfig('field', 'id');

        $address = $this->Addresses->find()->where(['city' => 'NoHashId'])->first();
        $hashid = $this->Addresses->encodeId($address->getOriginal('id'));
        $this->assertSame($hashid, $address->id);

        $address = $this->Addresses->patchEntity($address, ['postal_code' => '678']);

        $result = $this->Addresses->save($address);
        $this->assertTrue((bool)$result);

        $address = $this->Addresses->find()->where(['city' => 'NoHashId'])->first();
        $this->assertSame($hashid, $address->id);
    }

    /**
     * @return void
     */
    public function testGet()
    {
        $hashid = 'jR';
        $address = $this->Addresses->get($hashid);
        $this->assertSame($hashid, $address->id);
    }

    /**
     * @return void
     */
    public function testFindHashed()
    {
        $address = $this->Addresses->find()->where(['id' => 'jR'])->firstOrFail();
        $this->assertTrue((bool)$address);
    }

    /**
     * @expectedException \Cake\Datasource\Exception\RecordNotFoundException
     * @return void
     */
    public function testFindHashedFail()
    {
        $this->Addresses->find()->where(['id' => 'jRx'])->firstOrFail();
    }

    /**
     * @return void
     */
    public function testFindFieldFalse()
    {
        $this->Addresses->behaviors()->Hashid->setConfig('field', false);

        $address = $this->Addresses->find()->where(['id' => 1])->firstOrFail();
        $this->assertTrue((bool)$address);
    }

    /**
     * @expectedException \Cake\Datasource\Exception\RecordNotFoundException
     * @return void
     */
    public function testFindHashedFail2()
    {
        $this->Addresses->find()->where(['id' => 1])->firstOrFail();
    }

    /**
     * @return void
     */
    public function testFindHashedWithFieldFirst()
    {
        $this->Addresses->behaviors()->Hashid->setConfig('field', 'hash');
        $this->Addresses->behaviors()->Hashid->setConfig('findFirst', true);

        $hashid = 'k5';
        $address = $this->Addresses->find('hashed', [HashidBehavior::HID => $hashid]);
        $this->assertSame(2, $address->id);
    }

    /**
     * @return void
     */
    public function testEncode()
    {
        $this->Addresses->behaviors()->Hashid->setConfig('field', 'hid');

        $address = $this->Addresses->newEntity();
        $this->Addresses->encode($address);

        $this->assertNull($address->hid);

        $address->id = 2;
        $this->Addresses->encode($address);

        $expected = 'k5';
        $this->assertSame($expected, $address->hid);
    }

    /**
     * @return void
     */
    public function testEncodeWithOptions()
    {
        $this->Addresses->behaviors()->Hashid->setConfig('field', 'hid');
        $this->Addresses->behaviors()->Hashid->setConfig('minHashLength', 20);
        $this->Addresses->behaviors()->Hashid->setConfig('alphabet', 'efghxyz123456789');

        $address = $this->Addresses->newEntity();
        $address->id = 2;
        $this->Addresses->encode($address);

        $expected = '63249351yx14x2z68758';
        $this->assertSame($expected, $address->hid);
    }

    /**
     * @return void
     */
    public function testRecursive()
    {
        $result = $this->Addresses->find()->contain([
            $this->Users->getAlias(),
            $this->Comments->getAlias(),
        ])->first();

        $hashid = 'jR';
        $this->assertSame($hashid, $result->id);

        $this->assertSame(1, $result->comments[0]->id);
        $this->assertSame(1, $result->user->id);

        $this->Addresses->behaviors()->Hashid->setConfig('recursive', true);

        $result = $this->Addresses->find()->contain([
            $this->Users->getAlias(),
            $this->Comments->getAlias(),
        ])->first();

        $hashid = 'jR';
        $this->assertSame($hashid, $result->id);
        $this->assertSame($hashid, $result->comments[0]->id);
        $this->assertSame($hashid, $result->user->id);
    }

    /**
     * testIsUniqueDomainRule method
     *
     * @return void
     */
    public function testIsUniqueDomainRule()
    {
        $data = [
            'city' => 'Foo',
        ];

        $address = $this->Addresses->newEntity($data);
        $result = $this->Addresses->save($address);
        $this->assertTrue((bool)$result);

        $rules = $this->Addresses->rulesChecker();
        $rules->add($rules->isUnique(['city']));

        $address = $this->Addresses->newEntity($data);
        $result = $this->Addresses->save($address);

        $this->assertFalse((bool)$result);
        $expected = [
            'city' => [
                '_isUnique' => 'This value is already in use',
            ],
        ];
        $this->assertEquals($expected, $address->getErrors('city'), print_r($address->getErrors('city'), true));
    }
}
