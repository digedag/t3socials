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

/**
 * OAuth Klasse für die Authentifizierung für HybridAuth.
 * Kann über eID fürs FE und ajaxID fürs BE angesprochen werden.
 *
 * @author Michael Wagner <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class tx_t3socials_network_hybridauth_OAuthCall
{
    // prüft den status und fürt ggf. ein AUTHENTICATE durch
    public const OAUT_CALL_STATE = 'state';
    // prüft den status und fürt ggf. ein AUTHENTICATE durch
    public const OAUT_CALL_LOGOUT = 'logout';
    // authentifiziert sich mit dem service (reserviert für hybridauth)
    public const OAUT_CALL_AUTHENTICATE = 'authenticate';

    /**
     * Liefert die URL für die OAUTH.
     *
     * @param int $networkId
     * @param string $type
     *
     * @return string
     */
    public static function getOAuthCallBaseUrl($networkId, $type = self::OAUT_CALL_AUTHENTICATE)
    {
        // FE-URL
        if (TYPO3_MODE === 'FE') {
            $url = tx_rnbase_util_Misc::getIndpEnv('TYPO3_REQUEST_HOST').'/index.php?eID=t3socials-hybridauth';
        } // BE-URL
        else {
            $url = tx_rnbase_util_Misc::getIndpEnv('TYPO3_REQUEST_HOST').'/typo3/ajax.php?ajaxID=t3socials-hybridauth';
        }
        $url .= '&oAuthCallType='.$type;
        if ($networkId > 0) {
            $url .= '&network='.(int) $networkId;
        }

        return $url;
    }

    /**
     * ajaxID (BE) für autorizatioin redirects.
     *
     * @param array &$params
     * @param \TYPO3\CMS\Core\Http\AjaxRequestHandler &$ref
     *
     * @return void
     */
    public function ajaxId(array &$params, &$ref)
    {
        $content = $this->oAuthCall();
        $ref->setContentFormat('plain');
        $ref->setContent([$content]);
    }

    /**
     * eid (FE) für autorizatioin redirects.
     *
     * @return void
     */
    public function eID()
    {
        tx_rnbase::load('tx_rnbase_util_TYPO3');
        tx_rnbase::load('tx_rnbase_util_Typo3Classes');
        if (!tx_rnbase_util_TYPO3::isTYPO61OrHigher()) {
            $eidUtility = tx_rnbase_util_Typo3Classes::getEidUtilityClass();
            $eidUtility::connectDB();
        }
        $content = $this->oAuthCall();
        exit($content);
    }

    /**
     * eid (FE) für autorizatioin redirects.
     *
     * @return string
     */
    public function oAuthCall()
    {
        $networkId = (int) tx_rnbase_parameters::getPostOrGetParameter('network');
        $type = tx_rnbase_parameters::getPostOrGetParameter('oAuthCallType');
        if (!$networkId) {
            return '';
        }

        /* @var $network tx_t3socials_models_Network */
        $network = tx_t3socials_srv_ServiceRegistry::getNetworkService()->get($networkId);

        $networkConfig = tx_t3socials_network_Config::getNetworkConfig($network);
        $connection = tx_t3socials_network_Config::getNetworkConnection($network);

        /* @var $connection instanceof tx_t3socials_network_hybridauth_Interface */
        if (!$connection instanceof tx_t3socials_network_hybridauth_Interface) {
            throw new Exception('The connection "'.get_class($connection).'" has to implement the interface "tx_t3socials_network_hybridauth_Interface".');
        }

        try {
            $adapter = $connection->getProvider()->adapter;
            // es wurde der status angefragt
            if (self::OAUT_CALL_STATE === $type) {
                // Wenn angemeldet, Info ausgeben
                if ($adapter->isUserConnected()) {
                    $template = tx_rnbase_util_Network::getURL(
                        tx_rnbase_util_Extensions::extPath('t3socials').'/Resources/Private/Templates/eIDstatus.html'
                    );
                    $data = $network->getRecord();
                    $data['access_token'] = $adapter->token('access_token');
                    $data['access_token_secret'] = $adapter->token('access_token_secret');
                    $model = tx_rnbase::makeInstance(
                        'Tx_Rnbase_Domain_Model_Base',
                        $data
                    );
                    $marker = tx_rnbase::makeInstance('tx_rnbase_util_SimpleMarker');
                    $out = $marker->parseTemplate(
                        $template,
                        $model,
                        $network->getConfigurations()->getFormatter(),
                        'eid.state.',
                        'STATE'
                    );

                    return $out;
                } // Wenn nicht angemeldet, Anmeldeprozess durchführen!
                else {
                    $connection->getProvider()->login();

                    // Hybrid_Auth::authenticate($network->getNetwork());
                    return 'REDIRECT TO AUTH PAGE!';
                }
            } // abmelden
            elseif (self::OAUT_CALL_LOGOUT === $type) {
                $connection->getProvider()->logout();

                // @TODO: template erstellen.
                return
                '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" '.
                '"http://www.w3.org/TR/html4/loose.dtd"><html><head></head><body>'.
                '<h1>LOGGED OUT!</h1>'.
                '<script type="text/javascript">alert("You have been successfully logged out!");window.close();</script>'.
                '</body></html>';
            } // hybridauth login prozess
            elseif (self::OAUT_CALL_AUTHENTICATE === $type) {
                // die anfrage bearbeiten
                Hybrid_Endpoint::process();
            }
        } catch (Exception $e) {
            // @TODO: template erstellen.
            return
            '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" '.
            '"http://www.w3.org/TR/html4/loose.dtd"><html><head></head><body>'.
            '<h1>CONNECTION ERROR!</h1>'.
            '<script type="text/javascript">alert("'.$e->getMessage().'");window.close();</script>'.
            '</body></html>';
        }
    }
}

// eid Aufruf?
if (TYPO3_MODE === 'FE' && 't3socials-hybridauth' == tx_rnbase_parameters::getPostOrGetParameter('eID')) {
    $auth = tx_rnbase::makeInstance('tx_t3socials_network_hybridauth_OAuthCall');
    $auth->eID();
}
