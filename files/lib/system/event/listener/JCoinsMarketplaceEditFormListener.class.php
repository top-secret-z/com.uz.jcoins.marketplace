<?php
namespace marketplace\system\event\listener;
use marketplace\data\entry\Entry;
use marketplace\form\EntryEditForm;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\user\jcoins\UserJCoinsStatementHandler;
use wcf\system\WCF;

/**
 * Checks whether the user has enough JCoins to change a marketplace entry.
 *
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.jcoins.marketplace
 */
class JCoinsMarketplaceEditFormListener implements IParameterizedEventListener {
	/**
	 * instance of EntryEditForm
	 */
	protected $eventObj;
	
	/**
	 * @inheritdoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		$this->eventObj = $eventObj;
		$this->$eventName();
	}
	
	/**
	 * Handles the validate event.
	 */
	protected function validate() {
		if (!($this->eventObj instanceof EntryEditForm)) return;
		if (!MODULE_JCOINS || JCOINS_ALLOW_NEGATIVE) return;
		
		// add validator
		$this->eventObj->form->getNodeById('type')->addValidator(new FormFieldValidator('type', function(RadioButtonFormField $formField) {
			// check entry, type and user data
			$entry = $this->eventObj->formObject;
			if (!$entry->userID) return;
			if ($entry->userID != WCF::getUser()->userID) return;
			
			$userProfile = new UserProfile(new User($entry->userID));
			if (!$userProfile->getPermission('user.jcoins.canEarn') || !$userProfile->getPermission('user.jcoins.canUse')) return;
			
			// get type data
			$type = $formField->getDocument()->getNodeById('type');
			$oldType = $entry->type;
			$newType = $type->getSaveValue();
			if ($oldType == $newType) return;
			
			// set user JCoins after revoke
			$amount = 0;
			switch ($oldType) {
				case Entry::TYPE_EXCHANGE:
					$statement = UserJCoinsStatementHandler::getInstance()->getStatementProcessorInstance('com.uz.jcoins.statement.marketplace.exchange');
					$amount = -1 * $statement->calculateAmount() + $userProfile->jCoinsAmount;
					break;
					
				case Entry::TYPE_OFFER:
					$statement = UserJCoinsStatementHandler::getInstance()->getStatementProcessorInstance('com.uz.jcoins.statement.marketplace.offer');
					$amount = -1 * $statement->calculateAmount() + $userProfile->jCoinsAmount;
					break;
					
				case Entry::TYPE_SEARCH:
					$statement = UserJCoinsStatementHandler::getInstance()->getStatementProcessorInstance('com.uz.jcoins.statement.marketplace.search');
					$amount = -1 * $statement->calculateAmount() + $userProfile->jCoinsAmount;
					break;
			}
			
			switch ($type->getSaveValue()) {
				case Entry::TYPE_EXCHANGE:
					$statement = UserJCoinsStatementHandler::getInstance()->getStatementProcessorInstance('com.uz.jcoins.statement.marketplace.exchange');
					if ($statement->calculateAmount() < 0 && ($statement->calculateAmount() * -1) > $amount) {
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
					if ($statement->calculateAmount() < 0 && ($statement->calculateAmount() * -1) > $amount) {
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
					if ($statement->calculateAmount() < 0 && ($statement->calculateAmount() * -1) > $amount) {
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
	
	/**
	 * Handles the save event.
	 */
	protected function save() {
		if (!($this->eventObj instanceof EntryEditForm)) return;
		if (!MODULE_JCOINS) return;
		
		// check entry, type and user data
		$entry = $this->eventObj->formObject;
		if (!$entry->userID) return;
		$userProfile = new UserProfile(new User($entry->userID));
		if (!$userProfile->getPermission('user.jcoins.canEarn') || !$userProfile->getPermission('user.jcoins.canUse')) return;
		
		// get type data
		$oldType = $entry->type;
		$type = $this->eventObj->form->getNodeById('type');
		$newType = $type->getSaveValue();
		if ($oldType == $newType) return;
		
		// revoke and create
		switch ($oldType) {
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
		
		switch ($newType) {
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
