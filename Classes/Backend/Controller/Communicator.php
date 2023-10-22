<?php

namespace DMK\T3socials\Backend\Controller;

use DMK\T3socials\Backend\Handler\Trigger;
use Sys25\RnBase\Backend\Module\ExtendedModFunc;
use Sys25\RnBase\Utility\Misc;
use tx_rnbase;
use tx_t3socials_network_Config;

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

/**
 * Backend Modul für Nachrichtenversand.
 *
 * @author Rene Nitzsche <rene@system25.de>
 * @author Michael Wagner <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class Communicator extends ExtendedModFunc
{
    /**
     * Method getFuncId.
     *
     * @return  string
     */
    protected function getFuncId()
    {
        return 'communicator';
    }

    /**
     * It is possible to overwrite this method and return an array of tab functions.
     *
     * @return array
     */
    protected function getSubMenuItems()
    {
        $menuItems = tx_t3socials_network_Config::getNewtorkCommunicators();
        array_unshift(
            $menuItems,
            tx_rnbase::makeInstance(Trigger::class)
        );
        Misc::callHook(
            't3socials',
            'modCommunicator_tabItems',
            ['tabItems' => &$menuItems],
            $this
        );

        return $menuItems;
    }

    /**
     * Liefert false, wenn es keine SubSelectors gibt.
     * Sonst ein Array mit den ausgewählten Werten.
     *
     * @param string &$selectorStr
     *
     * @return array or false if not needed. Return empty array if no item found
     */
    protected function makeSubSelectors(&$selectorStr)
    {
        return false;
    }
}
