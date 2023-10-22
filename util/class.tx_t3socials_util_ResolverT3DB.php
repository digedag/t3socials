<?php
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

use Sys25\RnBase\Database\Connection;

/**
 * Resolver for default Typo3 Database.
 *
 * @author Michael Wagner <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class tx_t3socials_util_ResolverT3DB implements tx_t3socials_util_IResolver
{
    /**
     * Der Resolver lädt den zu indizierenden Datensatz auf der Datenbank. D.
     *
     * @param string $tableName
     * @param int $uid
     *
     * @return tx_t3socials_models_Base
     */
    public function getRecord($tableName, $uid)
    {
        $options = [];
        $options['wrapperclass'] = 'tx_t3socials_models_Base';
        $options['where'] = 'uid = '.(int) $uid;
        // wir wollen nur daten, welche auch im fe sichtbar sind!
        $options['enablefieldsfe'] = true;
        $rows = Connection::getInstance()->doSelect('*', $tableName, $options);
        /* @var $item tx_t3socials_models_Base */
        $item = empty($rows) ? null : reset($rows);
        // Den Tabellennamen im Model setzen.
        if ($item instanceof tx_t3socials_models_Base) {
            $item->setTableName($tableName);
        }

        return $item;
    }
}
