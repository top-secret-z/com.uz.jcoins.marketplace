<?php
namespace marketplace\system\event\listener;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\user\jcoins\UserJCoinsStatementHandler;

/**
 * JCoins listener for marketplace rating.
 *
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.jcoins.marketplace
 */
class JCoinsMarketplaceRatingListener implements IParameterizedEventListener {
	/**
	 * @inheritdoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (!MODULE_JCOINS) return;
		
		if ($eventObj->getActionName() != 'create') return;
		
		$returnValues = $eventObj->getReturnValues();
		$rating = $returnValues['returnValues'];
		
		UserJCoinsStatementHandler::getInstance()->create('com.uz.jcoins.statement.marketplace.rating', $rating);
	}
}
