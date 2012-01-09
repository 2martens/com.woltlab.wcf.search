<?php
namespace wcf\system\search;
use wcf\data\search\keyword\SearchKeywordAction;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

class SearchKeywordManager extends SingletonFactory {
	public function add($keyword) {
		$keyword = static::simplifyKeyword($keyword);
		
		// search existing entry
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_search_keyword
			WHERE	keyword = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($keyword));
		if (($row = $statement->fetchArray()) !== false) {
			$action = new SearchKeywordAction(array($row['keywordID']), 'update', array('data' => array(
				'searches' => $row['searches'] + 1,
				'lastSearchTime' => TIME_NOW
			)));
			$action->executeAction();
		}
		else {
			$action = new SearchKeywordAction(array(), 'create', array('data' => array(
				'keyword' => $keyword,
				'searches' => 1,
				'lastSearchTime' => TIME_NOW
			)));
			$action->executeAction();
		}
	}
	
	public static function simplifyKeyword($keyword) {
		// TODO: do something useful
		
		return $keyword;
	}
}