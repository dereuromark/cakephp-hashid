<?php

namespace Hashid\Model;

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
		$this->_hashids = new Hashids($this->_config['salt']);

		return $this->_hashids;
	}

}
