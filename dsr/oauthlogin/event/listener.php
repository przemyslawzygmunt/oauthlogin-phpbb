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
use phpbb\template\template;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{

    /* @var language */
    protected $language;

    /* @var template */
    protected $template;

    /**
     * Constructor
     *
     * @param language $language Language object
     * @param template $template Template object
     */
    public function __construct(language $language, template $template)
    {
        $this->language = $language;
        $this->template = $template;
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
            'core.login_box_modify_template_data' => 'login_box_modify_template_data',
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
     * Move cloud.supla.org to the first OAuth login button position.
     *
     * @return void
     */
    public function login_box_modify_template_data()
    {
        $this->move_oauth_service_first('suplacloud');
    }

    /**
     * Move an OAuth service row to the beginning of the login template block.
     *
     * @param string $service_name
     * @return void
     */
    protected function move_oauth_service_first($service_name)
    {
        $last_row = $this->template->retrieve_block_vars('oauth', ['S_ROW_COUNT']);

        if (!isset($last_row['S_ROW_COUNT'])) {
            return;
        }

        for ($i = 0, $count = (int) $last_row['S_ROW_COUNT'] + 1; $i < $count; $i++) {
            $row = $this->template->retrieve_block_vars('oauth[' . $i . ']', []);

            if (empty($row['REDIRECT_URL']) || strpos($row['REDIRECT_URL'], $service_name) === false) {
                continue;
            }

            if ($i === 0) {
                return;
            }

            $this->template->alter_block_array('oauth', [], $i, 'delete');
            $this->template->alter_block_array('oauth', $row, 0, 'insert');
            return;
        }
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
