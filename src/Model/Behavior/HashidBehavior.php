<?php
namespace Hashid\Model\Behavior;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Hashid\Model\HashidTrait;

/**
 * @author Mark Scherer
 * @license MIT
 */
class HashidBehavior extends Behavior {

	use HashidTrait;

	const HID = 'hid';

	/**
	 * @var \Hashids\Hashids
	 */
	protected $_hashids;

	/**
	 * @var \Cake\ORM\Table
	 */
	protected $_table;

	/**
	 * @var array|string
	 */
	protected $_primaryKey;

	protected $_defaultConfig = [
		'salt' => null, // Please provide your own salt via Configure key 'Security.salt'
		'field' => null, // To populate upon find() and save(), false to deactivate
		'debug' => null, // Auto-detect from Configure::read('debug')
		'recursive' => false, // Also transform nested entities
		'findFirst' => false, // Either true or 'first' or 'firstOrFail'
		'implementedFinders' => [
			'hashed' => 'findHashed',
		]
	];

	/**
	 * Constructor
	 *
	 * Merges config with the default and store in the config property
	 *
	 * Does not retain a reference to the Table object. If you need this
	 * you should override the constructor.
	 *
	 * @param \Cake\ORM\Table $table The table this behavior is attached to.
	 * @param array $config The config for this behavior.
	 */
	public function __construct(Table $table, array $config = []) {
		$defaults = (array)Configure::read('Hashid');
		parent::__construct($table, $config + $defaults);

		$this->_table = $table;
		$this->_primaryKey = $table->primaryKey();

		if ($this->_config['salt'] === null) {
			$this->_config['salt'] = Configure::read('Security.salt') ? sha1(Configure::read('Security.salt')) : null;
		}
		if ($this->_config['debug'] === null) {
			$this->_config['debug'] = Configure::read('debug');
		}
		if ($this->_config['field'] === null) {
			$this->_config['field'] = $this->_primaryKey;
		}
	}

	/**
	 * @param \Cake\Event\Event $event
	 * @param \Cake\ORM\Query $query
	 * @param \ArrayObject $options
	 * @param bool $primary
	 * @return void
	 */
	public function beforeFind(Event $event, Query $query, ArrayObject $options, $primary) {
		if (!$primary) {
			return;
		}

		$field = $this->_config['field'];
		if (!$field) {
			return;
		}

		$query->find('hashed');

		$idField = $this->_primaryKey;
		if ($field === $idField) {
			$query->traverseExpressions(function ($expression) {
				if (method_exists($expression, 'getField')
					&& ($expression->getField() === $this->_primaryKey || $expression->getField() === $this->_table->alias() . '.' . $this->_primaryKey)
				) {
					$expression->setValue($this->decodeHashid($expression->getValue()));
				}
				return $expression;
			});
		}

		if (!$this->_config['recursive']) {
			return;
		}

		foreach ($this->_table->associations() as $association) {
			if ($association->target()->hasBehavior('Hashid') && $association->finder() === 'all') {
				$association->finder('hashed');
			}
		}
	}

	/**
	 * @param \Cake\Event\Event $event The beforeSave event that was fired
	 * @param \Cake\ORM\Entity $entity The entity that is going to be saved
	 * @param \ArrayObject $options the options passed to the save method
	 * @return void
	 */
	public function afterSave(Event $event, Entity $entity, ArrayObject $options) {
		if (!$entity->isNew()) {
			return;
		}

		$this->encode($entity);
	}

	/**
	 * Sets up hashid for model.
	 *
	 * @param \Cake\ORM\Entity $entity The entity that is going to be saved
	 * @return bool True if save should proceed, false otherwise
	 */
	public function encode(Entity $entity) {
		$idField = $this->_primaryKey;
		$id = $entity->get($idField);
		if (!$id) {
			return false;
		}

		$field = $this->_config['field'];
		if (!$field) {
			return false;
		}

		$hashid = $this->encodeId($id);

		$entity->set($field, $hashid);
		$entity->dirty($field, false);

		return true;
	}

	/**
	 * Custom finder for hashids field.
	 *
	 * Options:
	 * - hid (required), best to use HashidBehavior::HID constant
	 * - noFirst (optional, to leave the query open for adjustments, no first() called)
	 *
	 * @param \Cake\ORM\Query $query Query.
	 * @param array $options Array of options as described above
	 * @return \Cake\ORM\Query
	 */
	public function findHashed(Query $query, array $options) {
		$field = $this->_config['field'];
		if (!$field) {
			return $query;
		}

		$idField = $this->_primaryKey;

		$query->formatResults(function ($results) use ($field, $idField) {
			return $results->map(function ($row) use ($field, $idField) {
				if (empty($row[$idField])) {
					return $row;
				}

				$row[$field] = $this->encodeId($row[$idField]);
				$row->dirty($field, false);
				return $row;
			});
		});

		if (!empty($options[HashidBehavior::HID])) {
			$id = $this->decodeHashid($options[HashidBehavior::HID]);
			$query->where([$idField => $id]);
		}

		$first = $this->_config['findFirst'] === true ? 'first' : $this->_config['findFirst'];
		if (!$first || !empty($options['noFirst'])) {
			return $query;
		}
		return $query->first();
	}

}
