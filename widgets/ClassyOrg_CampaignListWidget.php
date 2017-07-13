<?php

class ClassyOrg_CampaignListWidget extends WP_Widget
{
    const ID = 'ClassyOrg_CampaignListWidget';

    /**
     * Create instance of widget
     */
    public function __construct()
    {
        parent::__construct(self::ID, 'Classy.org: Campaign List');
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
            $orgID = array_key_exists('id', $instance) ? $instance['id'] : '';
            $count = array_key_exists('count', $instance) ? $instance['count'] : '';
        } else {
            $title = '';
            $orgID = '';
            $count = 5;
        }

        echo '<div class="widget-content">';

        // Campaign ID
        echo '<p>'
            . '<label for="' . $this->get_field_name('id') . '">' . _e('Organization ID:') . '</label>'
            . '<input class="widefat" id="' . $this->get_field_id('id')
            . '" name="' . $this->get_field_name('id') . '" type="text" value="'
            . esc_attr($orgID) . '" placeholder="123456789" />'
            . '</p>';

        // Title
        echo '<p>'
            . '<label for="' . $this->get_field_name('title') . '">' . _e('Title:') . '</label>'
            . '<input class="widefat" id="' . $this->get_field_id('title')
            . '" name="' . $this->get_field_name('title') . '" type="text" value="'
            . esc_attr($title) . '" />'
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
        $campaigns = $classyContent->campaignList($instance['id'], $instance['count']);

        ClassyOrg::addStylesheet();
        echo self::render($campaigns, $instance);
    }

    /**
     * Generate HTML for campaign top fundraising teams
     *
     * @param $teams
     * @param $params
     * @return string
     */
    public static function render($campaigns, $params)
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
        <i class="fa fa-group fa-2x fa-inverse"></i>
      </div>
      <div class="classy-org-leaderboard_item-info">
        <span class="classy-org-leaderboard_item-info-label">%s</span>
        <span class="classy-org-leaderboard_item-info-metric"><a href="https://classy.org/%s/events/%s/e%s" target="_blank">Visit</a></span>
      </div>
      <div class="classy-org-leaderboard_item-info">
        <span class="classy-org-leaderboard_item-info-label">Ticket Types</span>
        <span class="classy-org-leaderboard_item-info-metric">%s</span>
      </div>
    </div>

ITEM_TEMPLATE;

        if (!empty($params['title']))
        {
            $title = sprintf('<h3 class="classy-org-leaderboard_title">%s</h3>', esc_html($params['title']));
        } else
        {
            $title = '';
        }

        $itemsHtml = '';
        foreach ($campaigns as $campaign)
        {
            $classyContent = new ClassyContent();
            $ticket_types = $classyContent->campaignTicketTypes($campaign['id']);

            $types = Array();
            foreach($ticket_types as $t) $types[] = $t['name'];
            $list = implode(", ",$types);

            $itemsHtml .= sprintf(
                $itemTemplate,
                esc_html($campaign['name']),
                str_replace(' ', '-', $campaign['city']),
                str_replace(' ', '-', $campaign['name']),
                esc_html($campaign['id']),
                esc_html($list)
            );
        }

        $html = sprintf($widgetTemplate, $title, $itemsHtml);

        return $html;
    }
}