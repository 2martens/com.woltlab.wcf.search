<?php
namespace wcf\system\event\listener;
use wcf\system\event\IEventListener;
use wcf\system\WCF;

/**
 * Extends the daily system cleanup.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.search
 * @subpackage	system.event.listener
 * @category	Community Framework
 */
class SearchCleanUpListener implements IEventListener {
	/**
	 * @see	\wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		// get keyword average
		$sql = "SELECT 	AVG(searches) AS searches
			FROM	wcf".WCF_N."_search_keyword";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		if (($row = $statement->fetchArray()) !== false) {
			$average = floor($row['searches'] / 4);
			
			$sql = "DELETE FROM	wcf".WCF_N."_search_keyword
				WHERE		searches <= ?
						lastSearchTime < ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				floor($row['searches'] / 4),
				(TIME_NOW - 86400 * 30)
			));
		}
	}
}
