<?php

require_once(__DIR__ . '/ClassyContent.php');
require_once(__DIR__ . '/ClassyAPIClient.php');
require_once(__DIR__ . '/widgets/ClassyOrg_CampaignProgressWidget.php');
require_once(__DIR__ . '/widgets/ClassyOrg_CampaignOverviewWidget.php');
require_once(__DIR__ . '/widgets/ClassyOrg_CampaignFundraiserLeadersWidget.php');
require_once(__DIR__ . '/widgets/ClassyOrg_CampaignFundraisingTeamLeadersWidget.php');

/**
 * Plugin Name: Classy.org
 * Plugin URI: https://github.com/classy-org/classy-org-wp
 * Description: Classy Wordpress plugin for API version 2
 * Version: 0.1.0
 * Author: Classy
 * Author URI: https://github.com/classy-org/classy-org-wp
 * License: MIT
 */
class ClassyOrg
{
    const SETTINGS_GROUP = 'classy-org-settings';
    const CACHE_KEY_PREFIX = 'CLASSY_ORG';
    const VERSION = '0.1';
    const DB_VERSION = '0.1';

    public function __construct()
    {
        // Admin menus
        add_action('admin_menu', array($this, 'settingsMenu'));
        add_action('admin_init', array($this, 'settingsRegister'));

        // Short codes
        add_shortcode('classy-campaign-progress', array($this, 'shortcodeCampaignProgress'));
        add_shortcode('classy-campaign-overview', array($this, 'shortcodeCampaignOverview'));
        add_shortcode('classy-campaign-fundraiser-leaders', array($this, 'shortcodeCampaignFundraiserLeaders'));
        add_shortcode('classy-campaign-fundraising-teams-leaders', array($this, 'shortcodeCampaignFundraisingTeamLeaders'));

        // Widgets
        add_action('widgets_init', array($this, 'registerWidgets'));
    }

    /**
     * Register all of our widgets
     */
    public function registerWidgets()
    {
        register_widget('ClassyOrg_CampaignProgressWidget');
        register_widget('ClassyOrg_CampaignOverviewWidget');
        register_widget('ClassyOrg_CampaignFundraiserLeadersWidget');
        register_widget('ClassyOrg_CampaignFundraisingTeamLeadersWidget');
    }

    /**
     * Register settings menu in sidebar.
     */
    public function settingsMenu()
    {
        add_menu_page(
            'Classy.org Settings',
            'Classy.org',
            'administrator',
            self::SETTINGS_GROUP,
            array($this, 'settingsPage'),
            'dashicons-admin-generic'
        );
    }

    /**
     * Register settings group and keys.
     */
    public function settingsRegister()
    {
        register_setting(self::SETTINGS_GROUP, 'client_id');
        register_setting(self::SETTINGS_GROUP, 'client_secret');
    }

    /**
     * Settings page for configuring Classy.org API credentials.
     */
    public function settingsPage()
    {
        echo '<div class="wrap">'
            . '<h2>Classy.org API Credentials</h2>';

        echo '<p>Enter your Classy API Version 2 credentials below.</p>';
        echo '<p>See <a href="https://developers.classy.org">https://developers.classy.org</a> for more information.</p>';

        echo '<form method="post" action="options.php">';

        settings_fields(self::SETTINGS_GROUP);
        do_settings_sections(self::SETTINGS_GROUP);

        echo '<table class="form-table">'
            . '<tr valign="top">'
            . '  <th scope="row">Client ID</th>'
            . '  <td><input type="text" name="client_id" value="' . esc_attr(get_option('client_id')) . '"></td>'
            . '</tr>'
            . '<tr valign="top">'
            . '  <th scope="row">Client Secret</th>'
            . '  <td><input type="text" name="client_secret" value="' . esc_attr(get_option('client_secret'))  . '"></td>'
            . '</tr>'
            . '<tr><td>';

        submit_button('Save');

        echo '</td></tr></form>';
    }


    /**
     * Shortcode handler for generating a fundraiser (fundraising page) leaderboard.
     *
     * @param $attributes
     * @param $content
     * @return null|string
     */
    public function shortcodeCampaignFundraiserLeaders($attributes, $content)
    {
        if (array_key_exists('id', $attributes))
        {
            self::addStylesheet();

            $count = (array_key_exists('count', $attributes))
                ? (int)$attributes['count']
                : 5;
            $classyContent = new ClassyContent();
            $fundraisers = $classyContent->campaignFundraisers($attributes['id'], $count);
            $html = ClassyOrg_CampaignFundraiserLeadersWidget::render($fundraisers, $attributes);;

            return $html;

        } else
        {
            // No campaign ID provided, ignore.
            return null;
        }
    }

    /**
     * Shortcode handler for campaign overview.
     * 1. Total raised
     * 2. Number of donors
     * 3. Number of transactions
     * 4. Average transaction
     *
     * @param $attributes
     * @param $content
     * @return null|string
     */
    public function shortcodeCampaignOverview($attributes, $content)
    {
        if (array_key_exists('id', $attributes))
        {
            self::addStylesheet();

            $classyContent = new ClassyContent();
            $campaign = $classyContent->campaignOverview($attributes['id']);
            $html = ClassyOrg_CampaignOverviewWidget::renderTiles($campaign, $attributes);

            return $html;

        } else
        {
            // No campaign ID provided, ignore
            return null;
        }
    }

    /**
     * Shortcode handler for creating fundraising team leaderboards.
     *
     * @param $attributes
     * @param $content
     * @return null|string
     */
    public function shortcodeCampaignFundraisingTeamLeaders($attributes, $content)
    {
        if (array_key_exists('id', $attributes))
        {
            self::addStylesheet();

            $classyContent = new ClassyContent();
            $count = array_key_exists('count', $attributes) ? $attributes['count'] : 5;
            $fundraisingTeams = $classyContent->campaignFundraisingTeams($attributes['id'], $count);
            $html = ClassyOrg_CampaignFundraisingTeamLeadersWidget::render($fundraisingTeams, $attributes);

            return $html;

        } else
        {
            // No campaign ID, do nothing
            return null;
        }
    }

    /**
     * Shortcode handler for creating campaign progress meters.
     *
     * @param $attributes
     * @param $content
     * @return string
     */
    public function shortcodeCampaignProgress($attributes, $content)
    {
        if (array_key_exists('id', $attributes))
        {
            self::addStylesheet();

            // Valid ID, process
            $classyContent = new ClassyContent();
            $campaign = $classyContent->campaignOverview($attributes['id']);
            $html = ClassyOrg_CampaignProgressWidget::render($campaign, $attributes);

            return $html;

        } else
        {
            // No campaign ID provided, ignore
            return null;
        }
    }

    /**
     * Queue stylesheet in WP response.
     */
    public static function addStylesheet()
    {
        $file = plugin_dir_url(__FILE__) . '/css/classy_org.css/';
        wp_enqueue_style('classy_org', plugins_url('css/classy_org.css', __FILE__), array(), time());
        wp_enqueue_style('classy_org_icon_fonts', plugins_url('css/font-awesome.min.css', __FILE__), array(), time());
    }
}

// Create instance
$classyOrg = new ClassyOrg();
