<?php

namespace DMK\T3socials\Backend\Form\Element;

use tx_rnbase_tests_BaseTestCase;

/**
 *  Copyright notice.
 *
 *  (c) Hannes Bochmann <dev@dmk-ebusiness.de>
 *  All rights reserved
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
 */

/**
 * DMK\T3socials\Backend\Form\Element$NetworkConfigFieldTest.
 *
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class NetworkConfigFieldTest extends tx_rnbase_tests_BaseTestCase
{
    /**
     * @group unit
     */
    public function testRenderWhenConfigAlreadySet()
    {
        $field = $this->getAccessibleMock(
            'DMK\\T3socials\\Backend\\Form\\Element\\NetworkConfigField',
            ['callRenderOnParent'], [], '', false
        );

        $field->_set('data', ['databaseRow' => ['config' => 'someInfo']]);

        $field
            ->expects(self::once())
            ->method('callRenderOnParent')
            ->willReturn('test');

        self::assertEquals('test', $field->render());
        self::assertEmpty($field->_get('data')['parameterArray']['itemFormElValue']);
    }

    /**
     * @group unit
     */
    public function testRenderWhenConfigEmptyButNoNetwork()
    {
        $field = $this->getAccessibleMock(
            'DMK\\T3socials\\Backend\\Form\\Element\\NetworkConfigField',
            ['callRenderOnParent'], [], '', false
        );

        $field->_set('data', ['databaseRow' => ['config' => '']]);

        $field
            ->expects(self::once())
            ->method('callRenderOnParent')
            ->willReturn('test');

        self::assertEquals('test', $field->render());
        self::assertEmpty($field->_get('data')['parameterArray']['itemFormElValue']);
    }

    /**
     * @group unit
     */
    public function testRenderWhenNoConfigAndNetwork()
    {
        self::markTestIncomplete(
            "Failed asserting that null matches expected 'twitter {\r\n".
            "useHybridAuthLib = 1\r\n".
            "access_token =\r\n".
            "access_token_secret =\r\n".
            "}'."
        );

        $field = $this->getAccessibleMock(
            'DMK\\T3socials\\Backend\\Form\\Element\\NetworkConfigField',
            ['callRenderOnParent'], [], '', false
        );

        $field->_set('data', ['databaseRow' => ['config' => '', 'network' => [0 => 'twitter']]]);

        $field
            ->expects(self::once())
            ->method('callRenderOnParent')
            ->willReturn('test');

        self::assertEquals('test', $field->render());
        self::assertEquals(
            'twitter {'.CRLF.
                '    useHybridAuthLib = 1'.CRLF.
                '    access_token ='.CRLF.
                '    access_token_secret ='.CRLF.
            '}',
            $field->_get('data')['parameterArray']['itemFormElValue']
        );
    }

    /**
     * @group unit
     */
    public function testRenderWhenNoConfigButUnknownNetwork()
    {
        $field = $this->getAccessibleMock(
            'DMK\\T3socials\\Backend\\Form\\Element\\NetworkConfigField',
            ['callRenderOnParent'], [], '', false
        );

        $field->_set('data', ['databaseRow' => ['config' => '', 'network' => [0 => 'unknown']]]);

        $field
            ->expects(self::once())
            ->method('callRenderOnParent')
            ->willReturn('test');

        self::assertEquals('test', $field->render());
        self::assertEmpty($field->_get('data')['parameterArray']['itemFormElValue']);
    }
}
