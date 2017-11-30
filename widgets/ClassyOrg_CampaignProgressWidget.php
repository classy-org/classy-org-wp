<?php

class ClassyOrg_CampaignProgressWidget extends WP_Widget
{
    const ID = 'ClassyOrg_CampaignProgressWidget';

    /**
     * Create instance of widget
     */
    public function __construct()
    {
        $params = array(
            'classname' => self::ID,
            'description' => 'Displays progress bar for a Classy campaign.'
        );

        parent::__construct(self::ID, 'Classy.org: Campaign Progress Bar');
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
        } else {
            $title = '';
            $campaignId = '';
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

        // TODO: validate parameters
        $instance['id'] = strip_tags($newInstance['id']);
        $instance['title'] = strip_tags($newInstance['title']);

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
        $campaign = $classyContent->campaignOverview($instance['id']);

        ClassyOrg::addStylesheet();
        echo self::render($campaign, $instance);
    }

    /**
     * Generate HTML for campaign progress
     *
     * @param $campaign
     * @param $params
     * @return string
     */
    public static function render($campaign, $params)
    {
        $widgetTemplate = <<<WIDGET_TEMPLATE

        <div class="classy-org-progress classy-org-widget widget">
          %s
          <span class="classy-org-progress_raised">$%s</span>
            <span class="classy-org-progress_goal"> /
              <span class="sc-campaign-progress_goal_inner">%s</span>
            </span>
          <div class="classy-org-progress_bar-mask">
            <div class="classy-org-progress_bar-value" style="width: %s%%;"></div>
          </div>
        </div>

WIDGET_TEMPLATE;

        if (!empty($params['title']))
        {
            $title = sprintf('<h3 class="classy-org-progress_title">%s</h3>', esc_html($params['title']));

        } else
        {
            $title = '';
        }

        $goal = (empty($campaign['goal'])) ? 0 : $campaign['goal'];
        $totalRaised = (empty($campaign['overview']['total_gross_amount'])) ? 0 : ceil($campaign['overview']['total_gross_amount']);
        $percentToGoal = ($goal > 0) ? ceil(($totalRaised / $goal) * 100) : 0;

        $html = sprintf(
            $widgetTemplate,
            $title,
            number_format($totalRaised),
            number_format($goal),
            esc_html($percentToGoal)
        );

        return $html;
    }
}
