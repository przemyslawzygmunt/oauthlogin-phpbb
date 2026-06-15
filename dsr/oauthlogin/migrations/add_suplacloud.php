<?php
/**
 *
 * Extend OAuth login. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, DSR! https://github.com/xchwarze
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dsr\oauthlogin\migrations;

use phpbb\db\migration\migration;

class add_suplacloud extends migration
{
    /**
     * {@inheritdoc}
     */
    static public function depends_on()
    {
        return ['\dsr\oauthlogin\migrations\install_config'];
    }

    /**
     * {@inheritdoc}
     */
    public function effectively_installed()
    {
        return $this->config->offsetExists('auth_oauth_suplacloud_token_uri');
    }

    /**
     * {@inheritdoc}
     */
    public function update_data()
    {
        return [
            ['config.add', ['auth_oauth_suplacloud_key', '']],
            ['config.add', ['auth_oauth_suplacloud_secret', '']],
            ['config.add', ['auth_oauth_suplacloud_auth_uri', 'https://cloud.supla.org/oauth/v2/auth']],
            ['config.add', ['auth_oauth_suplacloud_token_uri', 'https://cloud.supla.org/oauth/v2/token']],
        ];
    }
}
