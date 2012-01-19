<?php
namespace wcf\system\search;
use wcf\form\IForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * All searchable object types should implement this interface. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.search
 * @subpackage	system.search
 * @category 	Community Framework
 */
interface ISearchableObjectType {
	/**
	 * Caches the data for the given object ids.
	 * 
	 * @param	array		$objectIDs
	 * @param	array		$additionalData
	 */
	public function cacheMessageData(array $objectIDs, array $additionalData = null);
	
	/**
	 * Returns the data for the given object id.
	 * 
	 * @param	integer		$objectID
	 * @return	array
	 */
	public function getMessageData($objectID);
	
	/**
	 * Shows the form part of this object type.
	 * 
	 * @param	wcf\form\IForm		$form		instance of the form class where the search has taken place
	 */
	public function show(IForm $form = null);
	
	/**
	 * Returns the search conditions of this message type.
	 * 
	 * @param	wcf\system\database\util\PreparedStatementConditionBuilder	$conditionBuilder
	 * @param	wcf\form\IForm							$form
	 */
	public function getConditions(PreparedStatementConditionBuilder $conditionBuilder, IForm $form = null);
	
	/**
	 * Provides the ability to add additional joins to sql search query. 
	 * 
	 * @return	string
	 */
	public function getJoins();
	
	/**
	 * Returns the database field name of the message id.
	 * 
	 * @return	string
	 */
	public function getIDFieldName();
	
	/**
	 * Returns additional search information.
	 * 
	 * @return	mixed
	 */
	public function getAdditionalData();
	
	/**
	 * Returns true, if the current user can use this searchable object type.
	 * 
	 * @return	boolean
	 */
	public function isAccessible();
	
	/**
	 * Returns the name of the form template for this object type.
	 * 
	 * @return	string
	 */
	public function getFormTemplateName();
	
	/**
	 * Returns the name of the result page template for this object type.
	 * 
	 * @return	string
	 */
	public function getResultTemplateName();
}
