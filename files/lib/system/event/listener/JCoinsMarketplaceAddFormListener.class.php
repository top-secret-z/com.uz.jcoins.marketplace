<?php
namespace marketplace\system\event\listener;
use marketplace\data\entry\Entry;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\exception\NamedUserException;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\user\jcoins\UserJCoinsStatementHandler;
use wcf\system\WCF;

/**
 * Checks whether the user has enough JCoins to add a new marketplace entry.
 *
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.jcoins.marketplace
 */
class JCoinsMarketplaceAddFormListener implements IParameterizedEventListener {
	/**
	 * instance of EntryAddForm
	 */
	protected $eventObj;
	
	/**
	 * @inheritdoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (!MODULE_JCOINS || JCOINS_ALLOW_NEGATIVE) return;
		if (!WCF::getSession()->getPermission('user.jcoins.canEarn') || !WCF::getSession()->getPermission('user.jcoins.canUse')) return;
		
		$this->eventObj = $eventObj;
		$this->$eventName();
	}
	
	/**
	 * Handles the readParameters event.
	 */
	protected function readParameters() {
		// check versus JCoins
		$statement = UserJCoinsStatementHandler::getInstance()->getStatementProcessorInstance('com.uz.jcoins.statement.marketplace.exchange');
		if ($statement->calculateAmount() >= 0 || ($statement->calculateAmount() * -1) <= WCF::getUser()->jCoinsAmount) return;
		
		$statement = UserJCoinsStatementHandler::getInstance()->getStatementProcessorInstance('com.uz.jcoins.statement.marketplace.offer');
		if ($statement->calculateAmount() >= 0 || ($statement->calculateAmount() * -1) <= WCF::getUser()->jCoinsAmount) return;
		
		$statement = UserJCoinsStatementHandler::getInstance()->getStatementProcessorInstance('com.uz.jcoins.statement.marketplace.search');
		if ($statement->calculateAmount() >= 0 || ($statement->calculateAmount() * -1) <= WCF::getUser()->jCoinsAmount) return;
		
		throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.jcoins.amount.tooLow'));
	}
	
	/**
	 * Handles the validate event.
	 */
	protected function validate() {
		$this->eventObj->form->getNodeById('type')->addValidator(new FormFieldValidator('type', function(RadioButtonFormField $formField) {
			$type = $formField->getDocument()->getNodeById('type');
			
			switch ($type->getSaveValue()) {
				case Entry::TYPE_EXCHANGE:
					$statement = UserJCoinsStatementHandler::getInstance()->getStatementProcessorInstance('com.uz.jcoins.statement.marketplace.exchange');
					if ($statement->calculateAmount() < 0 && ($statement->calculateAmount() * -1) > WCF::getUser()->jCoinsAmount) {
						$type->addValidationError(
							new FormFieldValidationError(
								'type',
								'marketplace.entry.type.error.insufficientJCoins'
							)
						);
					}
					break;
					
				case Entry::TYPE_OFFER:
					$statement = UserJCoinsStatementHandler::getInstance()->getStatementProcessorInstance('com.uz.jcoins.statement.marketplace.offer');
					if ($statement->calculateAmount() < 0 && ($statement->calculateAmount() * -1) > WCF::getUser()->jCoinsAmount) {
						$type->addValidationError(
							new FormFieldValidationError(
								'type',
								'marketplace.entry.type.error.insufficientJCoins'
							)
						);
					}
					break;
					
				case Entry::TYPE_SEARCH:
					$statement = UserJCoinsStatementHandler::getInstance()->getStatementProcessorInstance('com.uz.jcoins.statement.marketplace.search');
					if ($statement->calculateAmount() < 0 && ($statement->calculateAmount() * -1) > WCF::getUser()->jCoinsAmount) {
						$type->addValidationError(
							new FormFieldValidationError(
								'type',
								'marketplace.entry.type.error.insufficientJCoins'
							)
						);
					}
					break;
			}
		}));
	}
}