<?php
namespace wcf\system\search;
use wcf\form\IForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * This class provides default implementations for the ISearchableObjectType interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.search
 * @subpackage	system.search
 * @category 	Community Framework
 */
abstract class AbstractSearchableObjectType implements ISearchableObjectType {
	/**
	 * @see wcf\system\search\ISearchableObjectType::show()
	 */
	public function show(IForm $form = null) {}
	
	/**
	 * @see wcf\system\search\ISearchableObjectType::getConditions()
	 */
	public function getConditions(PreparedStatementConditionBuilder $conditionBuilder, IForm $form = null) {}
	
	/**
	 * @see wcf\system\search\ISearchableObjectType::getJoins()
	 */
	public function getJoins() {
		return '';
	}

	/**
	 * @see wcf\system\search\ISearchableObjectType::getIDFieldName()
	 */
	public function getIDFieldName() {
		return '';
	}
	
	/**
	 * @see wcf\system\search\ISearchableObjectType::getAdditionalData()
	 */
	public function getAdditionalData() {
		return null;
	}
	
	/**
	 * @see wcf\system\search\ISearchableObjectType::isAccessible()
	 */
	public function isAccessible() {
		return true;
	}
	
	/**
	 * @see wcf\system\search\ISearchableObjectType::getFormTemplateName()
	 */
	public function getFormTemplateName() {
		return '';
	}
}
