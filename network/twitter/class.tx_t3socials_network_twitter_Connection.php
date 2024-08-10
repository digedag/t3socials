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
 * Twitter Connector.
 *
 * If you get an 401 Authentification error,
 * be shure in the twitter ap was an callback url defined!
 *     > Desktop applications only support the oauth_callback value 'oob'
 *
 * @author Rene Nitzsche <rene@system25.de>
 * @author Michael Wagner <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class tx_t3socials_network_twitter_Connection extends tx_t3socials_network_hybridauth_Connection
{
    /**
     * Liefert den Klassennamen der Message Builder Klasse.
     *
     * @return string
     */
    protected function getBuilderClass()
    {
        return 'tx_t3socials_network_twitter_MessageBuilder';
    }

    /**
     * Liefert die Konfiguration für HybridAuth.
     *
     * @return array
     */
    public function getHybridAuthConfig()
    {
        $config = parent::getHybridAuthConfig();

        // fallback for old config
        if (empty($config['keys']['key'])) {
            $config['keys']['key'] = $this->getConfigData('CONSUMER_KEY');
        }
        if (empty($config['keys']['secret'])) {
            $config['keys']['secret'] = $this->getConfigData('CONSUMER_SECRET');
        }

        if (empty($config['keys']['access_token']) && empty($config['keys']['access_token_secret'])) {
            $accessToken = $this->getConfigData('OAUTH_TOKEN');
            $accessTokenSecret = $this->getConfigData('OAUTH_SECRET');
            if ($accessToken && $accessTokenSecret) {
                $config['keys']['access_token'] = $accessToken;
                $config['keys']['access_token_secret'] = $accessTokenSecret;
            }
        }

        return $config;
    }

    /**
     * Post data.
     *
     * @param string $message
     *
     * @return void
     */
    public function setUserStatus($message)
    {
        // USE THE OLD @DEPRECATED TWITTER API?!?
        if (!$this->useHybridAuth()) {
            $this->sendTweet($message);
        } // THE PREFERRED/FEATURED HYBRIDAUTH API!!
        else {
            parent::setUserStatus($message);
        }
    }

    /* *** ****************************** *** *
     * *** ****************************** *** *
     * *** THE OLD DEPRECATED TWITTER API *** *
     * *** ****************************** *** *
     * *** ****************************** *** */

    /**
     * is the HybridAut active? (default is true).
     *
     * @deprecated
     *
     * @return bool
     */
    protected function useHybridAuth()
    {
        $config = $this->getNetwork()->getConfigurations();

        return $config->getBool('twitter.useHybridAuthLib', false, true);
    }

    /**
     * Prüft das Result nach Fehlern.
     *
     * @param stdClass $result
     *
     * @deprecated
     *
     * @return void
     * @throws Exception
     */
    protected function handleErrorsFromResult(stdClass $result)
    {
        $errors = $result->errors ? $result->errors : [];
        if (!empty($result->error)) {
            $errors[] = $result->error;
        }
        if (!empty($errors)) {
            $errMsg = [];
            foreach ($errors as $error) {
                $errMsg[] = is_object($error) ? $error->message.' (Code '.$error->code.')' : 'twitteroauth: '.$error;
            }
            throw new Exception(implode("\n", $errMsg));
        }
    }

    /**
     * Post data on Twitter using Curl.
     *
     * @param string $message
     *
     * @deprecated
     *
     * @return string|array
     */
    public function sendTweet($message)
    {
        if ($this->useHybridAuth()) {
            return $this->setUserStatus($message);
        }
        require_once 'twitteroauth/twitteroauth.php';

        $connection = $this->getConnection();
        $result = $connection->post('statuses/update', ['status' => $message]);

        $this->handleErrorsFromResult($result);

        tx_rnbase_util_Logger::info(
            'Tweet was posted to Twitter!',
            't3socials',
            ['Tweet' => $message, 'Account' => $this->getNetwork()->getName()]
        );

        return $result;
    }

    /**
     * liefert die OAuth Connection.
     *
     * @deprecated
     *
     * @return TwitterOAuth
     */
    private function getConnection()
    {
        if (!is_object($this->connection)) {
            $cred = $this->getCredentials($this->network);
            $this->connection = new TwitterOAuth(
                $cred['CONSUMER_KEY'],
                $cred['CONSUMER_SECRET'],
                $cred['OAUTH_TOKEN'],
                $cred['OAUTH_SECRET']
            );
        }

        return $this->connection;
    }

    /**
     * liefert die zugangsdaten für oauth.
     *
     * @param tx_t3socials_models_Network $network
     *
     * @deprecated
     *
     * @return array
     */
    private function getCredentials(tx_t3socials_models_Network $network)
    {
        $data = $network->getConfigData('twitter.');
        if (empty($data)) {
            throw new Exception('No credentials for twitter found! UID: '.$network->getUid());
        }

        return $data;
    }
}
