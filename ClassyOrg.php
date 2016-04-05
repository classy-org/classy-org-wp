<?php

require_once(__DIR__ . '/ClassyContent.php');
require_once(__DIR__ . '/ClassyAPIClient.php');

/**
 * Plugin Name: Classy.org
 * Plugin URI: https://developers.classy.org (@FIXME)
 * Description: Classy Wordpress Plugin Extraordinaire (@FIXME)
 * Version: 0.1.0
 * Author: Classy
 * Author URI: https://developers.classy.org (@FIXME)
 * License: (@FIXME)
 */
class ClassyOrg
{
    const SETTINGS_GROUP = 'classy-org-settings';
    const DB_VERSION = '0.1';

    public function __construct()
    {
        // Activation hooks
        register_activation_hook(__FILE__, array('ClassyOrg', 'activate'));
        register_deactivation_hook(__FILE__, array('ClassyOrg', 'deactivate'));

        // Admin menus
        add_action('admin_menu', array($this, 'settingsMenu'));
        add_action('admin_init', array($this, 'settingsRegister'));

        // Short codes
        add_shortcode('classy-campaign-progress', array($this, 'shortcodeCampaignProgress'));
        add_shortcode('classy-campaign-overview', array($this, 'shortcodeCampaignOverview'));
    }

    /**
     * Activate plugin
     */
    public function activate()
    {

    }

    /**
     * Deactivate plugin
     */
    public function deactivate()
    {

    }

    /**
     * Register settings menu in sidebar.
     */
    public function settingsMenu()
    {
        add_object_page(
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

        echo '<p>@FIXME explanation of what is going on here</p>';

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
            $apiClient = ClassyAPIClient::getInstance(get_option('client_id'), get_option('client_secret'));
            $classyContent = new ClassyContent($apiClient);
            $campaign = $classyContent->campaignOverview($attributes['id']);
            $averageTransaction = round($campaign['overview']['total_gross_amount'] / $campaign['overview']['transactions_count'], 2);

            // FIXME: enqueue styles and make overridable
            $html = <<<HTML

            <style>
                .sc-campaign-overview_breakdown-item {
                    padding: 10px 0 15px;
                    border-bottom: 2px dotted #ccc;
                }
                .sc-campaign-overview_breakdown-stat {
                    font-size: 24px;
                    font-weight: 600;
                    margin: 0 0 5px;
                    font-family: "Open Sans", Arial, Helvetica, sans-serif;
                }
                .sc-campaign-overview_breakdown-label {
                    color: #aaa;
                    font-size: 12px;
                    font-weight: 500;
                    font-family: "Open Sans", Arial, Helvetica, sans-serif;
                }
            </style>

            <div class="sc-campaign-overview_breakdown">
                <div class="sc-campaign-overview_breakdown-item">
                    <strong class="sc-campaign-overview_breakdown-stat">\${$campaign['overview']['total_gross_amount']}</strong>
                    <span class="sc-campaign-overview_breakdown-label">Gross Transactions</span>
                </div>
                <div class="sc-campaign-overview_breakdown-item">
                    <strong class="sc-campaign-overview_breakdown-stat">{$campaign['overview']['donors_count']}</strong>
                    <span class="sc-campaign-overview_breakdown-label">Donors</span>
                </div>
                <div class="sc-campaign-overview_breakdown-item">
                    <strong class="sc-campaign-overview_breakdown-stat">{$campaign['overview']['transactions_count']}</strong>
                    <span class="sc-campaign-overview_breakdown-label">Transactions</span>
                </div>
                <div class="sc-campaign-overview_breakdown-item">
                    <strong class="sc-campaign-overview_breakdown-stat">\${$averageTransaction}</strong>
                    <span class="sc-campaign-overview_breakdown-label">Average Transaction</span>
                </div>
            </div>


HTML;

            return $html;

        } else
        {
            // No campaign ID provided, ignore
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
            // Valid ID, process
            $apiClient = ClassyAPIClient::getInstance(get_option('client_id'), get_option('client_secret'));
            $classyContent = new ClassyContent($apiClient);

            $color = (array_key_exists('color', $attributes))
                ? $attributes['color']
                : '#030303';
            $campaign = $classyContent->campaignOverview($attributes['id']);

            $gross = round($campaign['overview']['total_gross_amount'], 0);
            $percentToGoal = round(($campaign['overview']['total_gross_amount'] / $campaign['goal']) * 100.00, 0);

            // FIXME: enqueue styles and make overridable
            $html = <<<HTML

                <style>
                    .sc-campaign-progress::after {
                        clear: both;
                        content: "";
                        display: table;
                    }
                    .sc-campaign-progress_raised {
                        font-weight: 700;
                        color: #232a2f;
                        font-size: 1.5em;
                    }
                    .sc-campaign-progress_goal {
                        font-size: .6em;
                        color: #727e83;
                        font-family: "Open Sans", Arial, Helvetica, sans-serif;
                    }
                    .sc-campaign-progress_bar-mask {
                        width: 100%;
                        height: 15px;
                        background-color: #e2e2e2;
                        border-radius: 15px;
                        overflow: hidden;
                        margin: 10px 0 0;
                    }
                    .sc-campaign-progress_bar-value {
                        height: 100%;
                        border-radius: 15px;
                        transition: width 300ms ease;
                    }
                </style>

                <div class="sc-campaign-progress">
                    <strong class="sc-campaign-progress_raised">\$$gross</strong>
                    <span class="sc-campaign-progress_goal"> / <span class="sc-campaign-progress_goal-inner">\${$campaign['goal']}</span></span>
                    <div class="sc-campaign-progress_bar-mask">
                        <div class="sc-campaign-progress_bar-value" style="width: $percentToGoal%; background-color: $color;"></div>
                    </div>
                </div>

HTML;
            
            return $html;

        } else
        {
            // No campaign ID provided, ignore
            return null;
        }
    }
}

// Create instance
$classyOrg = new ClassyOrg();