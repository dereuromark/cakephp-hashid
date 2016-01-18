<?php
namespace Hashid\Test\TestCase\View\Helper;

use Hashid\View\Helper\HashidHelper;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Cake\View\View;

/**
 *
 */
class HashidHelperTest extends TestCase {

	/**
	 * @var \Hashid\View\Helper\HashidHelper
	 */
	public $Hashid;

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
				'salt' => null // For testing
			]
		);

		$this->request = $this->getMock('Cake\Network\Request', []);
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
	}

}
