<?php

namespace DMK\T3socials\Backend\Form\Element;

/***************************************************************
 *  Copyright notice
 *
 * (c) DMK E-BUSINESS GmbH <kontakt@dmk-ebusiness.de>
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
 * DMK\T3socials\Backend\Form\Element$NetworkConfigField.
 *
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class NetworkConfigField extends \TYPO3\CMS\Backend\Form\Element\TextElement
{
    /**
     * (non-PHPdoc).
     *
     * @see TYPO3\CMS\Backend\Form\Element\TextElement::render()
     */
    public function render()
    {
        // je nach Type gibt es verschiedene Vorlagen
        $network = $this->data['databaseRow']['network'][0];
        if (!$this->data['databaseRow']['config'] && $network) {
            try {
                $config = \tx_t3socials_network_Config::getNetworkConfig($network);
                $this->data['parameterArray']['itemFormElValue'] = $config->getDefaultConfiguration();
            } catch (\Exception $e) {
            }
        }

        return $this->callRenderOnParent();
    }

    /**
     * (non-PHPdoc).
     *
     * @see TYPO3\CMS\Backend\Form\Element\TextElement::render()
     */
    protected function callRenderOnParent()
    {
        return parent::render();
    }
}
