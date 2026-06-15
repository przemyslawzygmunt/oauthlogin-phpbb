<?php
/**
 *
 * Extend OAuth login. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, DSR! https://github.com/xchwarze
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dsr\oauthlogin\event;

use phpbb\language\language;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{

    /* @var language */
    protected $language;

    /**
     * Constructor
     *
     * @param language $language Language object
     */
    public function __construct(language $language)
    {
        $this->language = $language;
    }

    /**
     * Assign functions defined in this class to event listeners in the core
     *
     * @return array
     */
    static public function getSubscribedEvents()
    {
        return [
            'core.acp_board_config_edit_add' => 'acp_board_config_edit_add',
            'core.user_setup_after' => 'user_setup_after',
        ];
    }

    /**
     * Load extension language file during after user set up
     *
     * @return void
     */
    public function user_setup_after()
    {
        $this->language->add_lang('common', 'dsr/oauthlogin');
    }

    /**
     * Add SUPLA Cloud endpoint fields to the auth settings page.
     *
     * @param \phpbb\event\data $event Event object
     * @return void
     */
    public function acp_board_config_edit_add($event)
    {
        if ($event['mode'] !== 'auth') {
            return;
        }

        $display_vars = $event['display_vars'];
        $suplacloud_vars = [
            'legend_oauth_suplacloud' => 'AUTH_PROVIDER_OAUTH_SERVICE_SUPLACLOUD',
            'auth_oauth_suplacloud_auth_uri' => [
                'lang' => 'AUTH_PROVIDER_OAUTH_SUPLACLOUD_AUTH_URI',
                'validate' => 'string',
                'type' => 'text:80:255',
                'explain' => true,
            ],
            'auth_oauth_suplacloud_token_uri' => [
                'lang' => 'AUTH_PROVIDER_OAUTH_SUPLACLOUD_TOKEN_URI',
                'validate' => 'string',
                'type' => 'text:80:255',
                'explain' => true,
            ],
        ];

        $display_vars['vars'] = $this->insert_after_key($display_vars['vars'], 'auth_method', $suplacloud_vars);
        $event['display_vars'] = $display_vars;
    }

    /**
     * Insert an array segment after a specific key while preserving order.
     *
     * @param array $vars
     * @param string $after_key
     * @param array $insert
     * @return array
     */
    protected function insert_after_key(array $vars, $after_key, array $insert)
    {
        $result = [];

        foreach ($vars as $key => $value) {
            $result[$key] = $value;

            if ($key === $after_key) {
                $result += $insert;
            }
        }

        return $result;
    }
}
