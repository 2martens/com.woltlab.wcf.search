<?php
namespace wcf\system\search;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * SearchEngine searches for given query in the selected object types.
 *
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.search
 * @subpackage	system.search
 * @category 	Community Framework
 */
class SearchEngine extends SingletonFactory {
	/**
	 * list of available object types
	 * @var array
	 */
	protected $availableObjectTypes = array();
	
	/**
	 * @see wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		// get available object types
		$this->availableObjectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.searchableObjectType');
		
		// get processors
		foreach ($this->availableObjectTypes as &$objectType) {
			$objectType = $objectType->getProcessor();
		}
	}
	
	/**
	 * Returns a list of available object types.
	 * 
	 * @return array
	 */
	public function getAvailableObjectTypes() {
		return $this->availableObjectTypes;
	}
	
	public function getObjectType($objectTypeName) {
		if (isset($this->availableObjectTypes[$objectTypeName])) return $this->availableObjectTypes[$objectTypeName];
		
		return null;
	}
	
	public function search($q, array $objectTypes, $subjectOnly = false, array $additionalConditions = array(), $orderBy = 'time DESC', $limit = 1000) {
		// build search query
		$sql = '';
		$parameters = array();
		foreach ($objectTypes as $objectTypeName) {
			$objectType = $this->getObjectType($objectTypeName);
			if (!empty($sql)) $sql .= "\nUNION\n";
			
			$sql .= "(	SELECT		search_index.*,
							'".$objectTypeName."' AS objectType
					FROM 		wcf".WCF_N."_search_index search_index
							".$objectType->getJoins()."
					WHERE		".(!empty($q) ? "MATCH (search_index.subject".(!$subjectOnly ? ', search_index.message, search_index.metaData' : '').") AGAINST (? IN BOOLEAN MODE)" : "")."
							".((isset($additionalConditions[$objectTypeName]) && $additionalConditions[$objectTypeName]->__toString()) ? " ".(!empty($q) ? "AND" : "")." (".$additionalConditions[$objectTypeName].")" : "")."
			)";
			
			$parameters[] = $q;
			if (isset($additionalConditions[$objectTypeName])) $parameters = array_merge($parameters, $additionalConditions[$objectTypeName]->getParameters());
		}
		if (empty($sql)) {
			throw new SystemException('no object types given');
		}
		
		if (!empty($orderBy)) {
			$sql .= " ORDER BY " . $orderBy;
		}
		
		// send search query
		$messages = array();
		$statement = WCF::getDB()->prepareStatement($sql, $limit);
		$statement->execute($parameters);
		while ($row = $statement->fetchArray()) {
			$messages[] = array('objectID' => $row['objectID'], 'objectType' => $row['objectType']);
		}
		
		return $messages;
	}
}