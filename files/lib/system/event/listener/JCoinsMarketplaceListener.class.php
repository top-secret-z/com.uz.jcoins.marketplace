<?php
namespace marketplace\system\event\listener;
use marketplace\data\entry\Entry;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\user\jcoins\UserJCoinsStatementHandler;

/**
 * JCoins listener for marketplace entries.
 *
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.jcoins.marketplace
 */
class JCoinsMarketplaceListener implements IParameterizedEventListener {
	/**
	 * @inheritdoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (!MODULE_JCOINS) return;
		
		switch ($eventObj->getActionName()) {
			case 'triggerPublication':
				foreach ($eventObj->getObjects() as $object) {
					if (!$object->isDisabled && $object->userID) {
						$entry = $object->getDecoratedObject();
						
						switch ($entry->type) {
							case Entry::TYPE_EXCHANGE:
								UserJCoinsStatementHandler::getInstance()->create('com.uz.jcoins.statement.marketplace.exchange', $entry);
								break;
								
							case Entry::TYPE_OFFER:
								UserJCoinsStatementHandler::getInstance()->create('com.uz.jcoins.statement.marketplace.offer', $entry);
								break;
								
							case Entry::TYPE_SEARCH:
								UserJCoinsStatementHandler::getInstance()->create('com.uz.jcoins.statement.marketplace.search', $entry);
								break;
						}
					}
				}
				break;
				
				// 'enable' calls triggerPublication
				
			case 'disable':
				foreach ($eventObj->getObjects() as $object) {
					if ($object->userID) {
						$entry = $object->getDecoratedObject();
						
						switch ($entry->type) {
							case Entry::TYPE_EXCHANGE:
								UserJCoinsStatementHandler::getInstance()->revoke('com.uz.jcoins.statement.marketplace.exchange', $entry);
								break;
							
							case Entry::TYPE_OFFER:
								UserJCoinsStatementHandler::getInstance()->revoke('com.uz.jcoins.statement.marketplace.offer', $entry);
								break;
							
							case Entry::TYPE_SEARCH:
								UserJCoinsStatementHandler::getInstance()->revoke('com.uz.jcoins.statement.marketplace.search', $entry);
								break;
						}
					}
				}
				break;
				
			case 'delete':
				foreach ($eventObj->getObjects() as $object) {
					if (!$object->isDisabled && $object->userID) {
						$entry = $object->getDecoratedObject();
						
						switch ($entry->type) {
							case Entry::TYPE_EXCHANGE:
								UserJCoinsStatementHandler::getInstance()->revoke('com.uz.jcoins.statement.marketplace.exchange', $entry);
								break;
								
							case Entry::TYPE_OFFER:
								UserJCoinsStatementHandler::getInstance()->revoke('com.uz.jcoins.statement.marketplace.offer', $entry);
								break;
								
							case Entry::TYPE_SEARCH:
								UserJCoinsStatementHandler::getInstance()->revoke('com.uz.jcoins.statement.marketplace.search', $entry);
								break;
						}
					}
				}
				break;
		}
	}
}
