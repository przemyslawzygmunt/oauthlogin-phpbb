<?php
/**
 *
 * Extend OAuth login. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, DSR! https://github.com/xchwarze
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dsr\oauthlogin\auth\provider\oauth\service;

use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\OAuth2\Service\Suplacloud as SuplacloudService;
use phpbb\auth\provider\oauth\service\base;
use phpbb\auth\provider\oauth\service\exception;
use phpbb\config\config;
use phpbb\request\request_interface;

class suplacloud extends base
{
    /** @var config */
    protected $config;

    /** @var request_interface */
    protected $request;

    /**
     * Constructor.
     *
     * @param config $config Config object
     * @param request_interface $request Request object
     */
    public function __construct(config $config, request_interface $request)
    {
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function get_auth_scope()
    {
        return [
            'account_r',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function get_external_service_class()
    {
        return 'Suplacloud';
    }

    /**
     * {@inheritdoc}
     */
    public function get_service_credentials()
    {
        SuplacloudService::configureEndpoints(
            $this->config['auth_oauth_suplacloud_auth_uri'],
            $this->config['auth_oauth_suplacloud_token_uri']
        );

        return [
            'key' => $this->config['auth_oauth_suplacloud_key'],
            'secret' => $this->config['auth_oauth_suplacloud_secret'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function perform_auth_login()
    {
        if (!($this->service_provider instanceof SuplacloudService)) {
            throw new exception('AUTH_PROVIDER_OAUTH_ERROR_INVALID_SERVICE_TYPE');
        }

        try {
            // This was a callback request, get the token
            $token = $this->service_provider->requestAccessToken($this->request->variable('code', ''));
        } catch (TokenResponseException $e) {
            throw new exception('AUTH_PROVIDER_OAUTH_ERROR_REQUEST');
        }

        return $this->request_unique_id($token->getAccessToken());
    }

    /**
     * {@inheritdoc}
     */
    public function perform_token_auth()
    {
        if (!($this->service_provider instanceof SuplacloudService)) {
            throw new exception('AUTH_PROVIDER_OAUTH_ERROR_INVALID_SERVICE_TYPE');
        }

        return $this->request_unique_id();
    }

    /**
     * Requests the SUPLA Cloud user profile and extracts a stable identifier.
     *
     * @param string|null $access_token
     * @return string
     * @throws exception
     */
    protected function request_unique_id($access_token = null)
    {
        if ($access_token === null) {
            $access_token = $this->get_stored_access_token();
        }

        $target_server_uri = $this->get_target_server_uri($access_token);

        try {
            $result = (array) json_decode($this->service_provider->request($this->get_profile_uri($target_server_uri)), true);
        } catch (\OAuth\Common\Exception\Exception $e) {
            throw new exception('AUTH_PROVIDER_OAUTH_ERROR_REQUEST');
        }

        if (!empty($result['shortUniqueId'])) {
            return $target_server_uri . '#' . (string) $result['shortUniqueId'];
        }

        throw new exception('AUTH_PROVIDER_OAUTH_ERROR_REQUEST');
    }

    /**
     * Retrieves an access token already stored by phpBB OAuth.
     *
     * @return string
     * @throws exception
     */
    protected function get_stored_access_token()
    {
        try {
            $token = $this->service_provider->getStorage()->retrieveAccessToken($this->service_provider->service());
        } catch (\OAuth\Common\Storage\Exception\TokenNotFoundException $e) {
            throw new exception('AUTH_PROVIDER_OAUTH_ERROR_REQUEST');
        }

        return $token->getAccessToken();
    }

    /**
     * Builds profile URI from the target server address.
     *
     * @param string $target_server_uri
     * @return string
     */
    protected function get_profile_uri($target_server_uri)
    {
        return $target_server_uri . '/api/users/current';
    }

    /**
     * Extracts and decodes the target server address from the last token segment.
     *
     * @param string $access_token
     * @return string
     * @throws exception
     */
    protected function get_target_server_uri($access_token)
    {
        $parts = explode('.', $access_token);
        $encoded_server_uri = end($parts);

        if (count($parts) < 2 || !$encoded_server_uri) {
            throw new exception('AUTH_PROVIDER_OAUTH_ERROR_REQUEST');
        }

        $decoded_server_uri = trim($this->base64_url_decode($encoded_server_uri));

        if (!preg_match('#^https?://#i', $decoded_server_uri)) {
            throw new exception('AUTH_PROVIDER_OAUTH_ERROR_REQUEST');
        }

        return rtrim($decoded_server_uri, '/');
    }

    /**
     * Decodes a token segment encoded as base64 or base64url.
     *
     * @param string $value
     * @return string
     * @throws exception
     */
    protected function base64_url_decode($value)
    {
        $value = strtr($value, '-_', '+/');
        $value .= str_repeat('=', (4 - strlen($value) % 4) % 4);
        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            throw new exception('AUTH_PROVIDER_OAUTH_ERROR_REQUEST');
        }

        return $decoded;
    }
}
