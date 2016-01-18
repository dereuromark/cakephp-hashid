<?php

namespace Hashid\View\Helper;

use Cake\Core\Configure;
use Cake\View\Helper;
use Cake\View\View;
use Hashids\Hashids;

class HashidHelper extends Helper {

	/**
	 * @var array
	 */
	protected $_defaultConfig = [
		'salt' => true, // True for Security.salt Configure value or provide your own salt string
		'field' => null, // To populate upon find() and save()
		'tableField' => false, // To have a dedicated field in the table
		'debug' => null, // Auto-detect
		'first' => false, // Either true or 'first' or 'firstOrFail'
		'implementedFinders' => [
			'hashed' => 'findHashed',
		]
	];

	/**
	 * Constructor
	 *
	 * @param \Cake\View\View $View The View this helper is being attached to.
	 * @param array $config Configuration settings for the helper.
	 */
	public function __construct(View $View, array $config = []) {
		$defaults = (array)Configure::read('Hashid');
		parent::__construct($View, $config + $defaults);

		if ($this->_config['salt'] === true) {
			$this->_config['salt'] = Configure::read('Security.salt');
		}
		if ($this->_config['debug'] === null) {
			$this->_config['debug'] = Configure::read('debug');
		}
	}

	/**
	 * @param int $id
	 * @return string
	 */
	public function encodeId($id) {
		$hashid = $this->getHasher()->encode($id);
		if ($this->_config['debug']) {
			$hashid .= '-' . $id;
		}
		return $hashid;
	}

	/**
	 * @param string $hashid
	 * @return int
	 */
	public function decodeHashid($hashid) {
		if ($this->_config['debug']) {
			$hashid = substr($hashid, 0, strpos($hashid, '-'));
		}

		$ids = $this->getHasher()->decode($hashid);
		return array_shift($ids);
	}

	/**
	 * @return \Hashids\Hashids
	 */
	protected function getHasher() {
		if (isset($this->hashids)) {
			return $this->hashids;
		}
		$this->hashids = new Hashids($this->_config['salt']);

		return $this->hashids;
	}

}
