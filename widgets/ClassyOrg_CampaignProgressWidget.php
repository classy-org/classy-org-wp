<?php

class ClassyOrg_CampaignProgressWidget extends WP_Widget
{
    const ID = 'ClassyOrg_CampaignProgressWidget';
    const DEFAULT_BAR_COLOR = '#006505';
    const DEFAULT_BAR_BG_COLOR = '#e2e2e2';

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
            $barColor = array_key_exists('bar_color', $instance) ? $instance['bar_color'] : '';
            $barBgColor = array_key_exists('bar_bg_color', $instance) ? $instance['bar_bg_color'] : '';
            $campaignId = array_key_exists('id', $instance) ? $instance['id'] : '';
        } else {
            $title = '';
            $barColor = '#e2e2e2';
            $barBgColor = '#606060';
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

        // Bar color
        echo '<p>'
            . '<label for="' . $this->get_field_name('bar_color') . '">' . _e('Bar Color:') . '</label>'
            . '<input class="widefat" id="' . $this->get_field_id('bar_color')
            . '" name="' . $this->get_field_name('bar_color') . '" type="text" value="'
            . esc_attr($barColor) . '" placeholder="#e2e2e2"/>'
            . '</p>';

        // Bar background color
        echo '<p>'
            . '<label for="' . $this->get_field_name('bar_bg_color') . '">' . _e('Bar Background Color:') . '</label>'
            . '<input class="widefat" id="' . $this->get_field_id('bar_bg_color')
            . '" name="' . $this->get_field_name('bar_bg_color') . '" type="text" value="'
            . esc_attr($barBgColor) . '" placeholder="#606060" />'
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
        $instance['bar_color'] = strip_tags($newInstance['bar_color']);
        $instance['bar_bg_color'] = strip_tags($newInstance['bar_bg_color']);

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
          <div class="classy-org-progress_bar-mask" style="background-color: %s;">
            <div class="classy-org-progress_bar-value" style="width: %s; background-color: %s;"></div>
          </div>
        </div>

WIDGET_TEMPLATE;

        if (!empty($params['title']))
        {
            $title = sprintf('<h3 class="classy-org-progress_title">%s</h3>', $params['title']);

        } else
        {
            $title = '';
        }

        $goal = (empty($campaign['goal'])) ? 0 : $campaign['goal'];
        $totalRaised = (empty($campaign['overview']['total_gross_amount'])) ? 0 : ceil($campaign['overview']['total_gross_amount']);
        $percentToGoal = ceil(($totalRaised / $goal) * 100);
        $barColor = (empty($params['bar_color'])) ? self::DEFAULT_BAR_COLOR : $params['bar_color'];
        $barBgColor = (empty($params['bar_bg_color'])) ? self::DEFAULT_BAR_BG_COLOR : $params['bar_bg_color'];

        $html = sprintf(
            $widgetTemplate,
            $title,
            $totalRaised,
            $goal,
            $barBgColor,
            $percentToGoal,
            $barColor
        );

        return $html;
    }
}