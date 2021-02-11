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

tx_rnbase::load('tx_t3socials_trigger_MessageBuilder');
tx_rnbase::load('tx_t3socials_tests_BaseTestCase');

/**
 * Message Builder Testcase.
 *
 * @author Michael Wagner <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class tx_t3socials_tests_trigger_MessageBuilderTest extends tx_t3socials_tests_BaseTestCase
{
    /**
     * Test build Method.
     *
     * @group unit
     * @test
     *
     * @return void
     */
    public function testBuildGenericMessageWithTrigger()
    {
        $builder = $this->getMockForAbstractClass('tx_t3socials_trigger_MessageBuilder');
        $builder->expects($this->once())->method('buildMessage')->will($this->returnArgument(0));

        $triger = tx_rnbase::makeInstance(
            'tx_t3socials_models_TriggerConfig',
            [
                'table' => 'pages',
            ]
        );
        /* @var $model tx_t3socials_models_Base */
        $model = tx_rnbase::makeInstance(
            'tx_t3socials_models_Base',
            [
                'uid' => '5',
            ]
        );
        $model->setTableName('pages');

        $builder->setTrigger($triger);

        $message = $builder->buildGenericMessage($model);

        $this->assertInstanceOf('tx_t3socials_models_IMessage', $message);
        $this->assertEquals('pages', $message->getMessageType());
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3socials/tests/network/class.tx_t3socials_tests_network_MessageBuilderTest.php']) {
    include_once $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3socials/tests/network/class.tx_t3socials_tests_network_MessageBuilderTest.php'];
}
