<?php
/**
 *
 * Extend OAuth login. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, DSR! https://github.com/xchwarze
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace OAuth\OAuth2\Service;

use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\OAuth2\Token\StdOAuth2Token;

class Suplacloud extends AbstractService
{
    /**
     * Scope list
     */
    const SCOPE_ACCOUNT_R = 'account_r';

    /** @var string */
    protected static $authorizationEndpoint = 'https://cloud.supla.org/oauth/v2/auth';

    /** @var string */
    protected static $accessTokenEndpoint = 'https://cloud.supla.org/oauth/v2/token';

    public function __construct(
        CredentialsInterface  $credentials,
        ClientInterface       $httpClient,
        TokenStorageInterface $storage,
                              $scopes = array(),
        UriInterface          $baseApiUri = null
    ) {
        parent::__construct($credentials, $httpClient, $storage, $scopes, $baseApiUri, true);

    }

    /**
     * Configure endpoints before the service is created by OAuth\ServiceFactory.
     *
     * @param string $authorizationEndpoint
     * @param string $accessTokenEndpoint
     * @return void
     */
    public static function configureEndpoints($authorizationEndpoint, $accessTokenEndpoint)
    {
        if ($authorizationEndpoint) {
            static::$authorizationEndpoint = $authorizationEndpoint;
        }

        if ($accessTokenEndpoint) {
            static::$accessTokenEndpoint = $accessTokenEndpoint;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function service()
    {
        return 'Suplacloud';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri(static::$authorizationEndpoint);
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri(static::$accessTokenEndpoint);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthorizationMethod()
    {
        return static::AUTHORIZATION_METHOD_HEADER_BEARER;
    }

    /**
     * {@inheritdoc}
     */
    protected function parseAccessTokenResponse($responseBody)
    {
        $data = json_decode($responseBody, true);

        if (null === $data || !is_array($data)) {
            throw new TokenResponseException('Unable to parse response.');
        } elseif (isset($data['error'])) {
            throw new TokenResponseException('Error in retrieving token: "' . $data['error'] . '"');
        }

        $token = new StdOAuth2Token();
        $token->setAccessToken($data['access_token']);
        unset($data['access_token']);

        if (isset($data['expires_in'])) {
            $token->setLifeTime($data['expires_in']);
            unset($data['expires_in']);
        }

        if (isset($data['refresh_token'])) {
            $token->setRefreshToken($data['refresh_token']);
            unset($data['refresh_token']);
        }

        $token->setExtraParams($data);

        return $token;
    }
}
