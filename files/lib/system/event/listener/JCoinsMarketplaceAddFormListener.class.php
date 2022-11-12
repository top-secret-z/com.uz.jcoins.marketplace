<?php

/*
 * Copyright by Udo Zaydowicz.
 * Modified by SoftCreatR.dev.
 *
 * License: http://opensource.org/licenses/lgpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
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
 */
class JCoinsMarketplaceAddFormListener implements IParameterizedEventListener
{
    /**
     * instance of EntryAddForm
     */
    protected $eventObj;

    /**
     * @inheritdoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        if (!MODULE_JCOINS || JCOINS_ALLOW_NEGATIVE) {
            return;
        }
        if (!WCF::getSession()->getPermission('user.jcoins.canEarn') || !WCF::getSession()->getPermission('user.jcoins.canUse')) {
            return;
        }

        $this->eventObj = $eventObj;
        $this->{$eventName}();
    }

    /**
     * Handles the readParameters event.
     */
    protected function readParameters()
    {
        // check versus JCoins
        $statement = UserJCoinsStatementHandler::getInstance()->getStatementProcessorInstance('com.uz.jcoins.statement.marketplace.exchange');
        if ($statement->calculateAmount() >= 0 || ($statement->calculateAmount() * -1) <= WCF::getUser()->jCoinsAmount) {
            return;
        }

        $statement = UserJCoinsStatementHandler::getInstance()->getStatementProcessorInstance('com.uz.jcoins.statement.marketplace.offer');
        if ($statement->calculateAmount() >= 0 || ($statement->calculateAmount() * -1) <= WCF::getUser()->jCoinsAmount) {
            return;
        }

        $statement = UserJCoinsStatementHandler::getInstance()->getStatementProcessorInstance('com.uz.jcoins.statement.marketplace.search');
        if ($statement->calculateAmount() >= 0 || ($statement->calculateAmount() * -1) <= WCF::getUser()->jCoinsAmount) {
            return;
        }

        throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.jcoins.amount.tooLow'));
    }

    /**
     * Handles the validate event.
     */
    protected function validate()
    {
        $this->eventObj->form->getNodeById('type')->addValidator(new FormFieldValidator('type', static function (RadioButtonFormField $formField) {
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
