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

tx_rnbase::load('tx_t3socials_network_hybridauth_OAuthCall');

/**
 * HybridAut Utilities.
 *
 * @author Michael Wagner <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class tx_t3socials_network_HybridAuth
{
    /**
     * Enables the HybridAuth.LOG.
     * This Log will be written in the root of the t3socials extension.
     * tx_rnbase_util_Extensions::extPath('t3socials') . 'HybridAuth.LOG'.
     *
     * @var bool
     */
    public const DEBUG_ENABLED = false;

    /**
     * Liefert die Basiskonfiguration für HybridAuth.
     *
     * @param array $providers
     *
     * @return array
     */
    private static function getBasicConfiguration(array $providers = [])
    {
        $basic = [
            'base_url' => tx_t3socials_network_hybridauth_OAuthCall::getOAuthCallBaseUrl(0),
            // if you want to enable logging, set
            // 'debug_mode' to true  then provide
            // a writable file by the web server on "debug_file"
            'debug_mode' => false,
            'debug_file' => '',
            'providers' => $providers,
        ];
        if (self::DEBUG_ENABLED) {
            $basic['debug_mode'] = true;
            $basic['debug_file'] = tx_rnbase_util_Extensions::extPath('t3socials').'HybridAuth.LOG';
            touch($basic['debug_file']);
        }

        return $basic;
    }

    /**
     * lädt die HybridAuth Klassen.
     *
     * @return void
     */
    private static function loadHybridAuth()
    {
        if (!class_exists('Hybrid_Auth')) {
            require_once tx_rnbase_util_Extensions::extPath(
                't3socials',
                '/lib/hybridauth/Hybrid/Auth.php'
            );
            require_once tx_rnbase_util_Extensions::extPath(
                't3socials',
                '/lib/hybridauth/Hybrid/Endpoint.php'
            );
        }
    }

    /**
     * Erzeugt die HybridAuth Klasse.
     *
     * @param array $config
     *
     * @return Hybrid_Auth
     */
    private static function getHybridAuth($config)
    {
        self::loadHybridAuth();

        return new Hybrid_Auth($config);
    }

    /**
     * Meldet anhand von OAuth Token einen bestimmten Nutzer automatich an.
     *
     * @param string $providerId
     * @param string $token
     * @param string $secret
     *
     * @return void
     */
    private static function storeAccesToken($providerId, $token, $secret)
    {
        $key = 'hauth_session.'.$providerId.'.';
        self::loadHybridAuth();
        // store the keys
        Hybrid_Auth::storage()->set($key.'token.access_token', $token);
        Hybrid_Auth::storage()->set($key.'token.access_token_secret', $secret);
        // set the user as loged in!
        Hybrid_Auth::storage()->set($key.'is_logged_in', 1);
    }

    /**
     * Meldet anhand von OAuth Token einen bestimmten Nutzer automatich an.
     *
     * @param string $providerId
     * @param array $config
     *
     * @return void
     */
    private static function storeTokenByConfig($providerId, array $config = [])
    {
        if (isset($config['keys']['access_token'])
            || isset($config['keys']['access_token_secret'])
        ) {
            self::storeAccesToken(
                $providerId,
                $config['keys']['access_token'],
                $config['keys']['access_token_secret']
            );
        }
    }

    /**
     * liefert einen HybridAuth Provider für eine Provider-ID.
     *
     * @param string $providerId
     * @param array $config
     *
     * @return Hybrid_Provider_Adapter
     */
    public static function getProvider($providerId, array $config = [])
    {
        $allConfigurations = self::getBasicConfiguration(
            [$providerId => $config]
        );
        // aktuele network id an die base url anhängen
        $allConfigurations['base_url']
            = tx_t3socials_network_hybridauth_OAuthCall::getOAuthCallBaseUrl($config['networkUid']);

        $auth = self::getHybridAuth($allConfigurations);

        // ggf. OAUTH Token übergeben
        self::storeTokenByConfig($providerId, $config);

        // clear all auth session (only for tests, dont do that on production mode!)
        // self::clearHybridAuthSession();

        // we just return the provider without authentification!
        $adapter = $auth->getAdapter($providerId);

        return $adapter;
    }

    /**
     * Setzt die HybridAut Session zurück.
     * only for tests, dont do that on production mode!
     *
     * @return void
     */
    public static function clearHybridAuthSession()
    {
        $data = &$_SESSION;
        $data['HA::STORE'] = null;
        $data['HA::CONFIG'] = null;
        unset($data['HA::STORE']);
        unset($data['HA::CONFIG']);
    }
}

if (defined('TYPO3_MODE') &&
    $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3socials/network/class.tx_t3socials_network_HybridAuth.php']
) {
    include_once $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3socials/network/class.tx_t3socials_network_HybridAuth.php'];
}
