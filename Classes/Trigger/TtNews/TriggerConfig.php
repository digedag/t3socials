<?php

namespace DMK\T3socials\Trigger\TtNews;

use tx_t3socials_models_TriggerConfig;

/***************************************************************
*  Copyright notice
*
 * (c) 2014 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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


/**
 * Model einer trigger Konfiguration.
 *
 * @author Michael Wagner <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class TriggerConfig extends tx_t3socials_models_TriggerConfig
{
    /**
     * Initialisiert die Konfiguration für das Netzwerk.
     *
     * @return void
     */
    protected function initConfig()
    {
        parent::initConfig();
        $this->setProperty('trigger_id', 'news');
        $this->setProperty('table', 'tt_news');
        $this->setProperty('message_builder', MessageBuilder::class);
    }
}

