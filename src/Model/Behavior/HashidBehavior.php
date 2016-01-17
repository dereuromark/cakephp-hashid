<?php
namespace Hashid\Model\Behavior;

use Cake\Core\Configure;
use Cake\ORM\Behavior;
use Cake\ORM\Table;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\Event\Event;
use \ArrayObject;
use Hashids\Hashids;

/**
 * @author Mark Scherer
 * @licence MIT
 */
class HashidBehavior extends Behavior {

	const HID = 'hid';

	/**
	 * @var \Hashids\Hashids
	 */
	protected $hashids;

	protected $_defaultConfig = [
		'salt' => null, // Please provide your own salt via Configure
		'field' => null, // To populate upon find() and save()
		'tableField' => false, // To have a dedicated field in the table
		'first' => false, // Either true or 'first' or 'firstOrFail'
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
	 * @param Table $table The table this behavior is attached to.
	 * @param array $config The config for this behavior.
	 */
	public function __construct(Table $table, array $config = []) {
		$defaults = (array)Configure::read('Hashid');
		parent::__construct($table, $config + $defaults);

		$this->_table = $table;
	}

	/**
	 * @param Event $event
	 * @param Query $query
	 * @return void
	 */
	public function beforeFind(Event $event, Query $query) {
		$field = $this->_config['field'];
		if (!$field) {
			return;
		}

		$idField = $this->_table->primaryKey();

		$query->formatResults(function (\Cake\Datasource\ResultSetInterface $results) use ($field, $idField) {
			return $results->map(function ($row) use ($field, $idField) {
				if (!empty($row[$field]) || empty($row[$idField])) {
					return $row;
				}

				$row[$field] = $this->encodeId($row[$idField]);
				return $row;
			});
		});
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

		if ($this->encode($entity)) {
			$this->_table->save($entity, ['validate' => false]);
		}
	}

	/**
	 * Sets up hashid for model.
	 *
	 * @param \Cake\ORM\Entity $entity The entity that is going to be saved
	 * @return bool True if save should proceed, false otherwise
	 */
	public function encode(Entity $entity) {
		$idField = $this->_table->primaryKey();
		$id = $entity->get($idField);
		if (!$id) {
			return false;
		}

		$field = $this->_config['field'];
		$tableField = $this->_config['tableField'];

		if ($tableField === true) {
			$tableField = $field;
		}
		if (!$field && !$tableField) {
			return false;
		}

		$hashid = $this->encodeId($id);
		if ($field) {
			$entity->set($field, $hashid);
		}
		if ($tableField && $tableField !== $field) {
			$entity->set($tableField, $hashid);
		}

		return true;
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
		$tableField = $this->_config['tableField'];
		if ($tableField) {
			$query->where([$tableField => $options[HashidBehavior::HID]]);
		} else {
			$idField = $this->_table->primaryKey();
			$id = $this->decodeHashid($options[HashidBehavior::HID]);
			$query->where([$idField => $id]);
		}

		$first = $this->_config['first'] === true ? 'first' : $this->_config['first'];
		if (!$first || !empty($options['noFirst'])) {
			return $query;
		}
		return $query->first();
	}

	/**
	 * @param int $id
	 * @return string
	 */
	public function encodeId($id) {
		return $this->getHasher()->encode($id);
	}

	/**
	 * @param string $hashid
	 * @return int
	 */
	public function decodeHashid($hashid) {
		$ids = $this->getHasher()->decode($hashid);
		return array_shift($ids);
	}

}
