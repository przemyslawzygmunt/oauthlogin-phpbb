<?php
/**
 *
 * Extend OAuth login. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, DSR! https://github.com/xchwarze
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB')) {
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = [];
}

/**
 * Some characters you may want to copy&paste: ’ » “ ” …
 */
$lang = array_merge($lang, [
    'AUTH_PROVIDER_OAUTH_SERVICE_DISCORD' => 'Discord',
    'AUTH_PROVIDER_OAUTH_SERVICE_GITHUB' => 'GitHub',
    'AUTH_PROVIDER_OAUTH_SERVICE_MICROSOFT' => 'Microsoft',
    'AUTH_PROVIDER_OAUTH_SERVICE_REDDIT' => 'Reddit',
    'AUTH_PROVIDER_OAUTH_SERVICE_SUPLACLOUD' => 'cloud.supla.org',
    'AUTH_PROVIDER_OAUTH_SERVICE_WORDPRESS' => 'Wordpress',
    'AUTH_PROVIDER_OAUTH_SUPLACLOUD_AUTH_URI' => 'Your Web Authorization URI',
    'AUTH_PROVIDER_OAUTH_SUPLACLOUD_AUTH_URI_EXPLAIN' => 'Authorization endpoint provided by cloud.supla.org.',
    'AUTH_PROVIDER_OAUTH_SUPLACLOUD_TOKEN_URI' => 'Access Token URI',
    'AUTH_PROVIDER_OAUTH_SUPLACLOUD_TOKEN_URI_EXPLAIN' => 'Token endpoint provided by cloud.supla.org.',
    'LOGIN_LINK_ERROR_OAUTH_NO_ACCESS_TOKEN' => 'No ACCESS_TOKEN found for the selected service',
]);
