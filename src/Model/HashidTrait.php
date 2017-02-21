<?php

namespace Hashid\Model;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Utility\Hash;
use Hashids\Hashids;

/**
 * This trait can be used on any class that wants to have hashid support.
 *
 * The using class needs
 * - $_defaultConfig field
 * - $_config field
 * - config() logic via InstanceConfigTrait
 */
trait HashidTrait {

	/**
	 * @param int $id
	 * @return string
	 */
	public function encodeId($id) {
		if ($id < 1 || !is_int($id)) {
			throw new RecordNotFoundException('Invalid integer, the id must be >= 1.');
		}

		$hashid = $this->_getHasher()->encode($id);

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
		if (is_array($hashid)) {
			foreach ($hashid as $k => $v) {
				$hashid[$k] = $this->decodeHashid($v);
			}
			return $hashid;
		}
		if ($this->_config['debug']) {
			$hashid = substr($hashid, 0, strpos($hashid, '-'));
		}

		$ids = $this->_getHasher()->decode($hashid);
		return array_shift($ids);
	}

	/**
	 * @return \Hashids\Hashids
	 */
	protected function _getHasher() {
		if (isset($this->_hashids)) {
			return $this->_hashids;
		}
		$this->_hashids = new Hashids($this->_config['salt'], Hash::get($this->_config, 'minHashLength', 0), Hash::get($this->_config, 'alphabet', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'));

		return $this->_hashids;
	}

}
