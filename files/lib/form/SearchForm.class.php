<?php
namespace wcf\form;
use wcf\data\search\Search;
use wcf\data\search\SearchAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\request\LinkHandler;
use wcf\system\search\SearchEngine;
use wcf\system\search\SearchKeywordManager;
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
	public $startDate = '';
	
	/**
	 * end date
	 * @var integer
	 */
	public $endDate = '';
	
	public $submit = false;
	public $nameExactly = 1;
	public $subjectOnly = 0;
	public $searchHash = '';
	public $results = array();
	public $searchData = array();
	public $searchID = 0;
	public $modifySearchID = 0;
	public $modifySearch = null;
	public $searchIndexCondition = null;
	public $additionalConditions = array();
	
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
		
		if (isset($_REQUEST['modify'])) {
			$this->modifySearchID = intval($_REQUEST['modify']);
			$this->modifySearch = new Search($this->modifySearchID);
			
			if (!$this->modifySearch->searchID || ($this->modifySearch->userID && $this->modifySearch->userID != WCF::getUser()->userID)) {
				throw new IllegalLinkException();
			}
			
			$this->searchData = unserialize($this->modifySearch->searchData);
			if (empty($this->searchData['alterable'])) {
				throw new IllegalLinkException();
			}
			$this->query = $this->searchData['query'];
			$this->sortOrder = $this->searchData['sortOrder'];
			$this->sortField = $this->searchData['sortField'];
			$this->nameExactly = $this->searchData['nameExactly'];
			$this->subjectOnly = $this->searchData['subjectOnly'];
			$this->startDate = $this->searchData['startDate'];
			$this->endDate = $this->searchData['endDate'];
			$this->username = $this->searchData['username'];
			$this->userID = $this->searchData['userID'];
			$this->selectedObjectTypes = $this->searchData['selectedObjectTypes'];
			
			if (count($_POST)) {
				$this->submit = true;
			}
		}
		
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
		$this->getConditions();
		
		// check query and author
		if (empty($this->query) && empty($this->username) && !$this->userID) {
			throw new UserInputException('q');
		}
		
		// build search hash
		$this->searchHash = StringUtil::getHash(serialize(array($this->query, $this->selectedObjectTypes, !$this->subjectOnly, $this->searchIndexCondition, $this->additionalConditions, $this->sortField.' '.$this->sortOrder, PACKAGE_ID)));
		
		// check search hash
		if (!empty($this->query)) {
			$parameters = array($this->searchHash, 'messages', TIME_NOW - 1800);
			if (WCF::getUser()->userID) $parameters[] = WCF::getUser()->userID;
			
			$sql = "SELECT	searchID
				FROM	wcf".WCF_N."_search
				WHERE	searchHash = ?
					AND searchType = ?
					AND searchTime > ?
					".(WCF::getUser()->userID ? 'AND userID = ?' : 'AND userID IS NULL');
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($parameters);
			$row = $statement->fetchArray();
			if ($row !== false) {
				HeaderUtil::redirect(LinkHandler::getInstance()->getLink('SearchResult', array('id' => $row['searchID']), 'highlight='.urlencode($this->query)));
				exit;
			}
		}
		
		// do search
		$this->results = SearchEngine::getInstance()->search($this->query, $this->selectedObjectTypes, $this->subjectOnly, $this->searchIndexCondition, $this->additionalConditions, $this->sortField.' '.$this->sortOrder);
		
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
		if ($this->modifySearchID) {
			$this->objectAction = new SearchAction(array($this->modifySearchID), 'update', array('data' => array(
				'searchData' => serialize($this->searchData),
				'searchTime' => TIME_NOW,
				'searchType' => 'messages',
				'searchHash' => $this->searchHash
			)));
			$this->objectAction->executeAction();
		}
		else {
			$this->objectAction = new SearchAction(array(), 'create', array('data' => array(
				'userID' => (WCF::getUser()->userID ?: null),
				'searchData' => serialize($this->searchData),
				'searchTime' => TIME_NOW,
				'searchType' => 'messages',
				'searchHash' => $this->searchHash
			)));
			$resultValues = $this->objectAction->executeAction();
			$this->searchID = $resultValues['returnValues']->searchID;
		}
		// save keyword
		if (!empty($this->query)) {
			SearchKeywordManager::getInstance()->add($this->query);
		}
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
	 * Gets the conditions for a search in the table of the selected object types.
	 */
	protected function getConditions() {
		if (!count($this->selectedObjectTypes)) {
			$this->selectedObjectTypes = array_keys(SearchEngine::getInstance()->getAvailableObjectTypes());
		}
		
		// default conditions
		$userIDs = $this->getUserIDs();
		$this->searchIndexCondition = new PreparedStatementConditionBuilder(false);
	
		// user ids
		if (count($userIDs)) {
			$this->searchIndexCondition->add('search_index.userID IN (?)', array($userIDs));
		}
		
		// dates
		if (($startDate = strtotime($this->startDate)) && ($endDate = strtotime($this->endDate))) {
			$this->searchIndexCondition->add('search_index.time BETWEEN ? AND ?', array(strtotime($startDate), strtotime($endDate)));
		}
		
		foreach ($this->selectedObjectTypes as $key => $objectTypeName) {
			$objectType = SearchEngine::getInstance()->getObjectType($objectTypeName);
			if ($objectType === null) {
				throw new SystemException('unknown search object type '.$objectTypeName);
			}
			
			try {
				if (!$objectType->isAccessible()) {
					throw new PermissionDeniedException();
				}
				
				// special conditions
				if (($conditionBuilder = $objectType->getConditions($this)) !== null) {
					$this->additionalConditions[$objectTypeName] = $conditionBuilder;
				}
			}
			catch (PermissionDeniedException $e) {
				unset($this->selectedObjectTypes[$key]);
				continue;
			}
		}
		
		if (!count($this->selectedObjectTypes)) {
			$this->throwNoMatchesException();
		}
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
