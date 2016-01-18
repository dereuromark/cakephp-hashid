<?php
namespace Hashid\Test\Model\Behavior;

use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;
use Hashid\Model\Behavior\HashidBehavior;
use Hashids\Hashids;

class HashidBehaviorTest extends TestCase {

	/**
	 * @var array
	 */
	public $fixtures = [
		'plugin.Hashid.Addresses'
	];

	/**
	 * @var \Cake\ORM\Table;
	 */
	public $Addresses;

	/**
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$this->Addresses = TableRegistry::get('Hashid.Addresses');
		$this->Addresses->addBehavior('Hashid.Hashid');
	}

	/**
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();

		unset($this->Addresses);
		TableRegistry::clear();
	}

	/**
	 * @return void
	 */
	public function testSave() {
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
	public function testFind() {
		$data = [
			'city' => 'Foo'
		];
		$address = $this->Addresses->newEntity($data);
		$res = $this->Addresses->save($address);

		$id = $address->id;
		$hasher = new Hashids();
		$hashid = $hasher->encode($id);

		$address = $this->Addresses->find('hashed', [HashidBehavior::HID => $hashid]);
		$this->assertTrue((bool)$address);
	}

	/**
	 * @return void
	 */
	public function testSaveWithField() {
		$this->Addresses->behaviors()->Hashid->config('field', 'hash');

		$data = [
			'city' => 'Foo'
		];
		$address = $this->Addresses->newEntity($data);
		$res = $this->Addresses->save($address);
		$this->assertTrue((bool)$res);

		$hasher = new Hashids();
		$expected = $hasher->encode($address->id);
		$this->assertEquals($expected, $address->hash);
	}

	/**
	 * @return void
	 */
	public function testFindHashedWithField() {
		$this->Addresses->behaviors()->Hashid->config('field', 'hash');

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
	public function testFindWithField() {
		$address = $this->Addresses->find()->where(['city' => 'NoHashId'])->first();
		$hashid = $this->Addresses->encodeId($address->id);

		$this->Addresses->behaviors()->Hashid->config('field', 'hash');

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
	public function testSaveWithTableField() {
		$this->Addresses->behaviors()->Hashid->config('tableField', 'hash');

		$data = [
			'city' => 'FooBar'
		];
		$address = $this->Addresses->newEntity($data);
		$res = $this->Addresses->save($address);
		$this->assertTrue((bool)$res);

		$hasher = new Hashids();
		$expected = $hasher->encode($address->id);
		$this->assertEquals($expected, $address->hash);
	}

	/**
	 * @return void
	 */
	public function testFindByTableField() {
		$this->Addresses->behaviors()->Hashid->config('tableField', 'hash');

		$hash = 'jR';
		$address = $this->Addresses->find('hashed', [HashidBehavior::HID => $hash])->first();
		$this->assertTrue((bool)$address);

		$hasher = new Hashids();
		$ids = $hasher->decode($address->hash);
		$id = array_shift($ids);
		$this->assertEquals($address->id, $id);
	}

	/**
	 * @return void
	*/
	public function testFindHashed() {
		$address = $this->Addresses->find('hashed', [HashidBehavior::HID => 'jR'])->firstOrFail();
		$this->assertTrue((bool)$address);
	}

	/**
	 * @expectedException \Cake\Datasource\Exception\RecordNotFoundException
	 * @return void
	 */
	public function testFindHashedFail() {
		$this->Addresses->find('hashed', [HashidBehavior::HID => 'jRx'])->firstOrFail();
	}

	/**
	 * @return void
	 */
	public function testFindHashedWithFieldFirst() {
		$this->Addresses->behaviors()->Hashid->config('field', 'hash');
		$this->Addresses->behaviors()->Hashid->config('first', true);

		$hashid = 'k5';
		$address = $this->Addresses->find('hashed', [HashidBehavior::HID => $hashid]);
		$this->assertSame(2, $address->id);
	}

	/**
	 * @return void
	 */
	public function testEncode() {
		$this->Addresses->behaviors()->Hashid->config('field', 'hid');

		$address = $this->Addresses->newEntity();
		$this->Addresses->encode($address);

		$this->assertNull($address->hid);

		$address->id = 2;
		$this->Addresses->encode($address);

		$expected = 'k5';
		$this->assertSame($expected, $address->hid);
	}

}
