<?php

class ClassyOrg_CampaignFundraisingTeamLeadersWidget extends WP_Widget
{
    const ID = 'ClassyOrg_CampaignFundraisingTeamLeadersWidget';

    /**
     * Create instance of widget
     */
    public function __construct()
    {
        parent::__construct(self::ID, 'Classy.org: Campaign Fundraising Team Leaders');
    }

    /**
     * Draw form for widget options.
     *
     * @param array $instance
     * @return null
     */
    public function form($instance)
    {
        if ($instance) {
            $title = array_key_exists('title', $instance) ? $instance['title'] : '';
            $campaignId = array_key_exists('id', $instance) ? $instance['id'] : '';
            $count = array_key_exists('id', $instance) ? $instance['count'] : '';
        } else {
            $title = '';
            $campaignId = '';
            $count = 5;
        }

        echo '<div class="widget-content">';

        // Campaign ID
        echo '<p>'
            . '<label for="' . $this->get_field_name('id') . '">' . _e('Campaign ID:') . '</label>'
            . '<input class="widefat" id="' . $this->get_field_id('id')
            . '" name="' . $this->get_field_name('id') . '" type="text" value="'
            . esc_attr($campaignId) . '" placeholder="123456789" />'
            . '</p>';

        // Title
        echo '<p>'
            . '<label for="' . $this->get_field_name('title') . '">' . _e('Title:') . '</label>'
            . '<input class="widefat" id="' . $this->get_field_id('title')
            . '" name="' . $this->get_field_name('title') . '" type="text" value="'
            . esc_attr($title) . '" placeholder="My Campaign Title" />'
            . '</p>';

        // Count
        echo '<p>'
            . '<label for="' . $this->get_field_name('count') . '">' . _e('Count:') . '</label>'
            . '<input class="widefat" id="' . $this->get_field_id('count')
            . '" name="' . $this->get_field_name('count') . '" type="text" value="'
            . esc_attr($count) . '" placeholder="5" />'
            . '</p>';

        echo '</div>';

    }

    /**
     * Update settings
     *
     * @param array $newInstance
     * @param array $oldInstance
     * @return array
     */
    public function update($newInstance, $oldInstance)
    {
        $instance = $oldInstance;

        // FIXME: validate parameters
        $instance['id'] = strip_tags($newInstance['id']);
        $instance['title'] = strip_tags($newInstance['title']);
        $instance['count'] = (int)strip_tags($newInstance['count']);

        return $instance;
    }

    /**
     * Draw widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance)
    {
        // Defer to renderer which we also use for short codes
        $classyContent = new ClassyContent();
        $teams = $classyContent->campaignFundraisingTeams($instance['id'], $instance['count']);

        ClassyOrg::addStylesheet();
        echo self::render($teams, $instance);
    }

    /**
     * Generate HTML for campaign top fundraising teams
     *
     * @param $teams
     * @param $params
     * @return string
     */
    public static function render($teams, $params)
    {
        $widgetTemplate = <<<WIDGET_TEMPLATE

    <div class="classy-org-leaderboard classy-org-widget widget">
      %s
      <div class="classy-org-leaderboard_items">
        %s
      </div>
    </div>

WIDGET_TEMPLATE;

        $itemTemplate = <<<ITEM_TEMPLATE

    <div class="classy-org-leaderboard_item">
      <div class="classy-org-leaderboard_item-image">
        %s
      </div>
      <div class="classy-org-leaderboard_item-info">
        <span class="classy-org-leaderboard_item-info-label">%s</span>
      <div class="progress">
        <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuenow="70"
        aria-valuemin="0" aria-valuemax="100" style="width:%s">
        </div>
      </div>      
        <span class="classy-org-leaderboard_item-info-metric"><strong>$%s</strong> raised (%s)</span>
      </div>
    </div>

ITEM_TEMPLATE;

        if (!empty($params['title']))
        {
            $title = sprintf('<h4 class="classy-org-leaderboard_title">%s</h4>', esc_html($params['title']));
        } else
        {
            $title = '';
        }

        $itemsHtml = '';

        foreach ($teams as $team)
        {
          //write_log($team);
            $goal = $team['goal'];
            $total_raised = $team['total_raised'];
            $percent = round(( $total_raised / $goal ) * 100);
            $percent_meter = ($percent > 100) ? 100 : $percent;
            $thumbnail = (empty($team['logo_url'])) ? '<i class="fa fa-group fa-2x fa-inverse"></i>' : '<img src="'.$team['logo_url'].'"/>';
            $itemsHtml .= sprintf(
                $itemTemplate,
                $thumbnail,
                esc_html($team['name']),
                esc_html($percent_meter.'%'),                
                esc_html($total_raised),
                esc_html($percent.'%')
            );
        }

        $html = sprintf($widgetTemplate, $title, $itemsHtml);

        return $html;
    }
}