<?php
namespace wcf\data\search\keyword;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\WCF;

/**
 * Executes keyword-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.search
 * @subpackage	data.search.keyword
 * @category 	Community Framework
 */
class SearchKeywordAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\search\keyword\SearchKeywordEditor';
	
	protected $allowGuestAccess = array('getList');
	
	public function validateGetList() {}
	
	public function getList() {
		$searchString = $this->parameters['data']['searchString'];
		$list = array();
	
		// find users
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_search_keyword
			WHERE		keyword LIKE ?
			ORDER BY	searches DESC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($searchString.'%'));
		while ($row = $statement->fetchArray()) {
			$list[] = array(
				'label' => $row['keyword'],
				'objectID' => $row['keywordID']
			);
		}
	
		return $list;
	}
}
