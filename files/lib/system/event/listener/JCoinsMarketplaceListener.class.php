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
use wcf\system\user\jcoins\UserJCoinsStatementHandler;

/**
 * JCoins listener for marketplace entries.
 */
class JCoinsMarketplaceListener implements IParameterizedEventListener
{
    /**
     * @inheritdoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        if (!MODULE_JCOINS) {
            return;
        }

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
