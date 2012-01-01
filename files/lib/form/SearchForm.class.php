<?php
namespace wcf\form;
use wcf\data\search\SearchAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\request\LinkHandler;
use wcf\system\search\SearchEngine;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

class SearchForm extends RecaptchaForm {
	/**
	 * @see wcf\page\SortablePage::$sortField
	 */
	public $sortField = 'time';//SEARCH_DEFAULT_SORT_FIELD;
	
	/**
	 * @see wcf\page\SortablePage::$sortOrder
	 */
	public $sortOrder = 'DESC';//SEARCH_DEFAULT_SORT_ORDER;
	
	/**
	 * @see wcf\form\RecaptchaForm::$useCaptcha
	 */
	public $useCaptcha = false;//SEARCH_USE_CAPTCHA;
	
	/**
	 * search query
	 * @var string
	 */
	public $query = '';
	
	/**
	 * username
	 * @var string
	 */
	public $username = '';
	
	/**
	 * user id
	 * @var integer
	 */
	public $userID = 0;
	
	/**
	 * selected object types
	 * @var array<string>
	 */
	public $selectedObjectTypes = array();
	
	/**
	 * start date
	 * @var integer
	 */
	public $startDate = 0;
	
	/**
	 * end date
	 * @var integer
	 */
	public $endDate = 0;
	
	public $submit = false;
	public $nameExactly = 1;
	public $subjectOnly = 0;
	public $searchHash = '';
	public $results = array();
	public $searchData = array();
	public $searchID = 0;
	
	/**
	 * @see wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['q'])) $this->query = StringUtil::trim($_REQUEST['q']);
		if (isset($_REQUEST['username'])) $this->username = StringUtil::trim($_REQUEST['username']);
		if (isset($_REQUEST['userID'])) $this->userID = intval($_REQUEST['userID']);
		if (isset($_REQUEST['selectedObjectTypes']) && is_array($_REQUEST['selectedObjectTypes'])) $this->selectedObjectTypes = $_REQUEST['selectedObjectTypes'];
		$this->submit = (count($_POST) || !empty($this->query) || !empty($this->username) || $this->userID);
		
		// sort order
		if (isset($_REQUEST['sortField'])) {
			$this->sortField = $_REQUEST['sortField'];
		}
			
		switch ($this->sortField) {
			case 'subject':
			case 'time':
			case 'username': break;
			case 'relevance': if (!$this->submit || !empty($this->query)) break;
			default: 
				if (!$this->submit || !empty($this->query)) $this->sortField = 'relevance';
				else $this->sortField = 'time';
		}
		
		if (isset($_REQUEST['sortOrder'])) {
			$this->sortOrder = $_REQUEST['sortOrder'];
			switch ($this->sortOrder) {
				case 'ASC':
				case 'DESC': break;
				default: $this->sortOrder = 'DESC';
			}
		}
	}
	
	/**
	 * @see wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->nameExactly = 0;
		if (isset($_POST['nameExactly'])) $this->nameExactly = intval($_POST['nameExactly']);
		if (isset($_POST['subjectOnly'])) $this->subjectOnly = intval($_POST['subjectOnly']);
		if (isset($_POST['startDate'])) $this->startDate = strtotime($_POST['startDate']);
		if (isset($_POST['endDate'])) $this->endDate = strtotime($_POST['endDate']);
	}
	
	/**
	 * @see wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		// get search conditions
		$conditions = $this->getConditions();
		
		// check query and author
		if (empty($this->query) && empty($this->username) && !$this->userID) {
			throw new UserInputException('q');
		}
		
		// build search hash
		$this->searchHash = StringUtil::getHash(serialize(array($this->query, $this->selectedObjectTypes, !$this->subjectOnly, $conditions, $this->sortField.' '.$this->sortOrder, PACKAGE_ID)));
		
		// check search hash
		if (!empty($this->query)) {
			$sql = "SELECT	searchID
				FROM	wcf".WCF_N."_search
				WHERE	searchHash = ?
					AND userID = ?
					AND searchType = ?
					AND searchTime > ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($this->searchHash, WCF::getUser()->userID, 'messages', TIME_NOW - 1800));
			$row = $statement->fetchArray();
			if ($row !== false) {
				HeaderUtil::redirect(LinkHandler::getInstance()->getLink('SearchResult', array('id' => $row['searchID']), 'highlight='.urlencode($this->query)));
				exit;
			}
		}
		
		// do search
		$this->results = SearchEngine::getInstance()->search($this->query, $this->selectedObjectTypes, $this->subjectOnly, $conditions, $this->sortField.' '.$this->sortOrder);
		
		// result is empty
		if (count($this->results) == 0) {
			$this->throwNoMatchesException();
		}
	}
	
	/**
	 * Throws a NamedUserException on search failure.
	 */
	public function throwNoMatchesException() {
		if (empty($this->query)) throw new NamedUserException(WCF::getLanguage()->get('wcf.search.error.user.noMatches'));
		else throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.search.error.noMatches', array('query' => $this->query)));
	}
	
	/**
	 * @see wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// get additional data
		$additionalData = array();
		foreach (SearchEngine::getInstance()->getAvailableObjectTypes() as $objectTypeName => $objectType) {
			if (($data = $objectType->getAdditionalData()) !== null) {
				$additionalData[$objectTypeName] = $data;
			}
		}
		
		// save result in database
		$this->searchData = array(
			'packageID' => PACKAGE_ID,
			'query' => $this->query,
			'results' => $this->results,
			'additionalData' => $additionalData,
			'sortField' => $this->sortField,
			'sortOrder' => $this->sortOrder,
			'nameExactly' => $this->nameExactly,
			'subjectOnly' => $this->subjectOnly,
			'startDate' => $this->startDate,
			'endDate' => $this->endDate,
			'username' => $this->username,
			'userID' => $this->userID,
			'selectedObjectTypes' => $this->selectedObjectTypes,
			'alterable' => (!$this->userID ? 1 : 0)
		);
		
		/*if ($this->searchID) {
			$sql = "UPDATE	wcf".WCF_N."_search
				SET	searchData = '".escapeString(serialize($this->searchData))."',
					searchDate = ".TIME_NOW.",
					searchType = 'messages',
					searchHash = '".$this->searchHash."'
				WHERE	searchID = ".$this->searchID;
			WCF::getDB()->sendQuery($sql);
		}
		else {*/
			$searchAction = new SearchAction(array(), 'create', array('data' => array(
				'userID' => WCF::getUser()->userID,
				'searchData' => serialize($this->searchData),
				'searchTime' => TIME_NOW,
				'searchType' => 'messages',
				'searchHash' => $this->searchHash
			)));	
			$resultValues = $searchAction->executeAction();
			$this->searchID = $resultValues['returnValues']->searchID;
		//}
		$this->saved();
		
		// forward to result page
		HeaderUtil::redirect(LinkHandler::getInstance()->getLink('SearchResult', array('id' => $this->searchID), 'highlight='.urlencode($this->query)));
		exit;
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		// init form
		foreach (SearchEngine::getInstance()->getAvailableObjectTypes() as $objectType) $objectType->show($this);
		
		WCF::getTPL()->assign(array(
			'query' => $this->query,
			'subjectOnly' => $this->subjectOnly,
			'username' => $this->username,
			'nameExactly' => $this->nameExactly,
			'startDate' => $this->startDate,
			'endDate' => $this->endDate,
			'sortField' => $this->sortField,
			'sortOrder' => $this->sortOrder,
			'selectedObjectTypes' => $this->selectedObjectTypes,
			'objectTypes' => SearchEngine::getInstance()->getAvailableObjectTypes()
		));
	}
	
	/**
	 * @see wcf\page\IPage::show()
	 */
	public function show() {
		if (!count($_POST) && $this->submit) {
			if ($this->userID) $this->useCaptcha = false;
			$this->submit();
		}
		
		parent::show();
	}
	
	/**
	 * Returns the conditions for a search in the table of the selected object types.
	 */
	protected function getConditions() {
		$conditions = array();
		
		if (!count($this->selectedObjectTypes)) {
			$this->selectedObjectTypes = array_keys(SearchEngine::getInstance()->getAvailableObjectTypes());
		}
		
		// get user ids
		$userIDs = $this->getUserIDs();
		
		foreach ($this->selectedObjectTypes as $key => $objectTypeName) {
			$objectType = SearchEngine::getInstance()->getObjectType($objectTypeName);
			if ($objectType === null) {
				throw new SystemException('unknown search object type '.$objectTypeName);
			}
			
			$conditionBuilder = new PreparedStatementConditionBuilder(false);
			try {
				if (!$objectType->isAccessible()) {
					throw new PermissionDeniedException();
				}

				// default conditions
				// user ids
				if (count($userIDs)) {
					$conditionBuilder->add('search_index.userID IN (?)', array($userIDs));
				}
				
				// dates
				if (($startDate = strtotime($this->startDate)) && ($endDate = strtotime($this->endDate))) {
					$conditionBuilder->add('search_index.time BETWEEN ? AND ?', array(strtotime($startDate), strtotime($endDate)));
				}
				
				// special conditions
				$objectType->getConditions($conditionBuilder);
			}
			catch (PermissionDeniedException $e) {
				unset($this->selectedObjectTypes[$key]);
				continue;
			}
			
			$conditions[$objectTypeName] = $conditionBuilder;
		}
		
		if (!count($this->selectedObjectTypes)) {
			$this->throwNoMatchesException();
		}
		
		return $conditions;
	}
	
	/**
	 * Returns user ids.
	 * 
	 * @return 	array<integer>
	 */
	public function getUserIDs() {
		$userIDs = array();
			
		// username
		if (!empty($this->username)) {
			$sql = "SELECT	userID
				FROM	wcf".WCF_N."_user
				WHERE	username ".($this->nameExactly ? "= ?" : "LIKE ?");
			$statement = WCF::getDB()->prepareStatement($sql, 100);
			$statement->execute(array($this->username.(!$this->nameExactly ? '%' : '')));
			while ($row = $statement->fetchArray()) {
				$this->userIDs[] = $row['userID'];
			}
			
			if (!count($this->userIDs)) {
				$this->throwNoMatchesException();
			}
		}
		
		// userID
		if ($this->userID) {
			$userIDs[] = $this->userID;
		}
		
		return $userIDs;
	}
}
