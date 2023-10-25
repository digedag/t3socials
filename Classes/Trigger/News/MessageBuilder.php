<?php

namespace DMK\T3socials\Trigger\News;

/***************************************************************
*  Copyright notice
*
 * (c) 2014-2023 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

use Sys25\RnBase\Utility\Misc;
use tx_t3socials_models_Base;
use tx_t3socials_models_IMessage;
use tx_t3socials_models_Message;
use tx_t3socials_models_Network;
use tx_t3socials_models_TriggerConfig;
use tx_t3socials_trigger_MessageBuilder;
use tx_t3socials_util_Link;

class MessageBuilder extends tx_t3socials_trigger_MessageBuilder
{
    /**
     * Erzeugt eine generische Nachricht für den versand über die Netzwerke.
     *
     * @param tx_t3socials_models_Message $message
     * @param tx_t3socials_models_Base $model
     *
     * @return tx_t3socials_models_IMessage
     */
    protected function buildMessage(
        tx_t3socials_models_Message $message,
        tx_t3socials_models_Base $model
    ) {
        $message->setHeadline($model->getTitle());
        $message->setIntro($model->getShort());
        $message->setMessage($model->getBodytext());
        $message->setData($model);
        $message->setMessageType('news');

        return $message;
    }

    /**
     * Spezielle Netzwerk und Triggerabhängige Dinge durchführen.
     *
     * @param tx_t3socials_models_IMessage &$message
     * @param tx_t3socials_models_Network $network
     * @param tx_t3socials_models_TriggerConfig $trigger
     *
     * @return void
     */
    public function prepareMessageForNetwork(
        tx_t3socials_models_IMessage $message,
        tx_t3socials_models_Network $network,
        tx_t3socials_models_TriggerConfig $trigger
    ) {
        $confId = $network->getNetwork().'.'.$trigger->getTriggerId().'.';

        Misc::prepareTSFE();

        $news = $message->getData();
        $config = $network->getConfigurations();
        $link = $config->createLink();
        // tx_ttnews[news]
        $link->designator('tx_news');
        $link->initByTS($config, $confId.'link.show.', ['tx_news' => $news->getUid()]);
        // wenn nicht anders konfiguriert, immer eine absolute url setzen!
        if (!$config->get($confId.'link.show.absurl')) {
            $link->setAbsUrl(true);
        }
        $url = tx_t3socials_util_Link::getRealUrlAbsUrlForLink($link);
        $message->setUrl($url);
    }
}
