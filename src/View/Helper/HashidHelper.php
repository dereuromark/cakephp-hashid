<?php

namespace Hashid\View\Helper;

use Cake\Core\Configure;
use Cake\View\Helper;
use Cake\View\View;
use Hashid\Model\HashidTrait;

class HashidHelper extends Helper {

	use HashidTrait;

	/**
	 * @var \Hashids\Hashids
	 */
	protected $_hashids;

	protected $_defaultConfig = [
		'salt' => null, // Please provide your own salt via Configure
		'debug' => null, // Auto-detect
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

		if ($this->_config['salt'] === null) {
			$this->_config['salt'] = Configure::read('Security.salt');
		}
		if ($this->_config['debug'] === null) {
			$this->_config['debug'] = Configure::read('debug');
		}
	}

}
