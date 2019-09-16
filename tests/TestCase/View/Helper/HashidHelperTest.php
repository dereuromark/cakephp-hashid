<?php
namespace Hashid\Test\TestCase\View\Helper;

use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use Hashid\View\Helper\HashidHelper;

class HashidHelperTest extends TestCase {

	/**
	 * @var \Hashid\View\Helper\HashidHelper
	 */
	public $Hashid;

	/**
	 * @var \Cake\Http\ServerRequest
	 */
	protected $request;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		Configure::write('Hashid', [
				'debug' => false,
			]
		);
		Configure::write('Security', [
				'salt' => '' // For testing
			]
		);

		$this->request = $this->getMockBuilder(ServerRequest::class, [])->getMock();
		$this->view = new View($this->request);
		$this->Hashid = new HashidHelper($this->view);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();

		unset($this->Hashid);
	}

	/**
	 * @return void
	 */
	public function testEncodeDecode() {
		$id = 1;

		$hashid = $this->Hashid->encodeId($id);
		$this->assertSame('jR', $hashid);

		$newId = $this->Hashid->decodeHashid($hashid);
		$this->assertSame($id, $newId);

		$id = 999000000;
		$hashid = $this->Hashid->encodeId($id);
		$this->assertTrue(strlen($hashid) > 6);

		$newId = $this->Hashid->decodeHashid($hashid);
		$this->assertSame($id, $newId);
	}

	/**
	 * For some x64 systems this can lead to empty results, as they cannot work with strings
	 * larger than 1 billion or sth...
	 *
	 * @return void
	 */
	public function testEncodeDecodeMax() {
		$id = PHP_INT_MAX;

		$hashid = $this->Hashid->encodeId($id);
		$this->assertTrue(strlen($hashid) > 6);
	}

	/**
	 * @return void
	 */
	public function testEncodeDecodeSalt() {
		Configure::write('Security.salt', 'foobar');
		$this->Hashid = new HashidHelper($this->view);

		$id = 1;

		$hashid = $this->Hashid->encodeId($id);
		$this->assertSame('3B', $hashid);

		$newId = $this->Hashid->decodeHashid($hashid);
		$this->assertSame($id, $newId);
	}

	/**
	 * @return void
	 */
	public function testEncodeDecodeDebug() {
		Configure::write('Hashid.debug', true);
		$this->Hashid = new HashidHelper($this->view);

		$id = 1;

		$hashid = $this->Hashid->encodeId($id);
		$this->assertSame('jR-1', $hashid);

		$newId = $this->Hashid->decodeHashid($hashid);
		$this->assertSame($id, $newId);
	}

}
