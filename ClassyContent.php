<?php

/**
 * Class ClassyContent
 *
 * Manage fetching/caching of API content.
 */
class ClassyContent
{
    const EXPIRATION_IN_MINUTES = 10;

    private $apiClient;

    public function __construct()
    {
        $this->apiClient = ClassyAPIClient::getInstance(get_option('client_id'), get_option('client_secret'));
    }

    /**
     * Fetch campaign overview from API
     * @param $campaignId
     * @return array|bool|mixed
     */
    public function campaignOverview($campaignId)
    {
        $cacheKey = ClassyOrg::CACHE_KEY_PREFIX . '_CAMPAIGN_OVERVIEWS' . $campaignId;
        $result = get_transient($cacheKey);

        if ($result === false)
        {
            $campaign = $this->apiClient->request('/campaigns/' . $campaignId);
            $overview = $this->apiClient->request('/campaigns/' . $campaignId . '/overview');
            $result = json_decode($campaign, true);
            //write_log( $result );
           $result['overview'] = json_decode($overview, true);

            set_transient($cacheKey, $result, $this->getExpiration());
        }

        return $result;
    }

    /**
     * Fetch campaign fundraisers from API
     *
     * @param integer $campaignId ID of campaign to pull
     * @param integer $count Number of records to return
     * @return array|bool|mixed
     */
    public function campaignFundraisers($campaignId, $count = 5)
    {
        $cacheKey = ClassyOrg::CACHE_KEY_PREFIX . '_CAMPAIGN_FUNDRAISERS_' . $campaignId;
        $result = get_transient($cacheKey);

        if ($result === false)
        {
            $params = array(
                'aggregates' => 'true',
                'sort'       => 'total_raised:desc',
                'per_page'   => $count,
                'filter'     => 'status=active'
            );
            $fundraisers = $this->apiClient->request(
                '/campaigns/' . $campaignId . '/fundraising-pages',
                'GET',
                $params
            );
            $result = json_decode($fundraisers, true);

            // Pluck off relevant bits
            $result = $result['data'];

            set_transient($cacheKey, $result, $this->getExpiration());
        }

        return $result;
    }

    /**
     * ADDED - Fetch campaign list from API
     *
     * @param integer $orgId ID of organization to pull
     * @param integer $count Number of records to return
     * @return array|bool|mixed
     */
    public function campaignList($count)
    {
        $orgId = get_option( 'organization_id' );
        $cacheKey = ClassyOrg::CACHE_KEY_PREFIX . '_CAMPAIGN_LIST_' . $orgId;
        $result = get_transient($cacheKey);

        if ($result === false)
        {
            $date = gmdate("Y-m-d\TH:i:s\Z");
            $params = array(
                'aggregates' => 'true',
                'per_page'   => $count,
                'sort'       => 'ended_at:asc',
                'filter'     => 'status=active,type=ticketed,ended_at>'.$date
            );
            $campaigns = $this->apiClient->request(
                '/organizations/' . $orgId . '/campaigns',
                'GET',
                $params
            );
            $result = json_decode($campaigns, true);

            // Pluck off relevant bits
            $result = $result['data'];

            set_transient($cacheKey, $result, $this->getExpiration());
        }

        return $result;
    }

    /**
     * ADDED - Fetch campaign list from API to create WP pages
     *
     * @param integer $orgId ID of organization to pull
     * @param integer $count Number of records to return
     * @return array|bool|mixed
     */
    public function createEventPages($count)
    {
        $orgId = get_option( 'organization_id' );
        $cacheKey = ClassyOrg::CACHE_KEY_PREFIX . '_EVENT_PAGES_' . $orgId;
        $result = get_transient($cacheKey);

        if ($result === false)
        {
            $date = gmdate("Y-m-d\TH:i:s\Z");
            //write_log('Todays date is: '.$date);
            $params = array(
                'aggregates' => 'true',
                'per_page'   => $count,
                'sort'       => 'ended_at:asc',
                'filter'     => 'status=active,started_at>'.$date
            );
            $campaigns = $this->apiClient->request(
                '/organizations/' . $orgId . '/campaigns',
                'GET',
                $params
            );
            $result = json_decode($campaigns, true);

            // Pluck off relevant bits
            $result = $result['data'];

            set_transient($cacheKey, $result, $this->getExpiration());
        }

        return $result;
    }

    /**
     * ADDED - Fetch campaign ticket types from API
     *
     * @param integer $campaignId ID of organization to pull
     * @param integer $count Number of records to return
     * @return array|bool|mixed
     */
    public function campaignTicketTypes($campaignID)
    {
        $cacheKey = ClassyOrg::CACHE_KEY_PREFIX . '_CAMPAIGN_TICKET_TYPES_' . $campaignID;
        $result = get_transient($cacheKey);

        if ($result === false)
        {
            $params = array(
                'aggregates' => 'true'
            );
            $ticket_types = $this->apiClient->request(
                '/campaigns/' . $campaignID . '/ticket-types',
                'GET',
                $params
            );
            $result = json_decode($ticket_types, true);

            // Pluck off relevant bits
            $result = $result['data'];

            set_transient($cacheKey, $result, $this->getExpiration());
        }

        return $result;
    }

    /**
     * ADDED - Fetch campaign transactions from API
     *
     * @param integer $campaignId ID of organization to pull
     * @param text $email to retrieve
     * @return array|bool|mixed
     */
    public function campaignTransactions($campaignID, $email)
    {
        $params = array(
            'aggregates' => 'true',
            'filter'    => 'email='.$email
        );
        $transactions = $this->apiClient->request(
            '/campaigns/' . $campaignID . '/registrations',
            'GET',
            $params
        );
        $result = json_decode($transactions, true);

        // Pluck off relevant bits
        $result = $result['data'];
        return json_encode($result);
    }

    /**
     * ADDED - Create campaign fundraising page
     *
     * @param integer $campaignId ID of organization to pull
     * @param integer $memberID Number of records to return
     * @return array|bool|mixed
     */
    public function createFundraiserPage($campaignID, $memberID, $goal)
    {
        $params = array(
            'filter'    => 'member_id=5018748,goal=500'
        );
        $fundraiser = $this->apiClient->request(
            '/campaigns/'.$campaignID.'/fundraising-teams',
            'POST',
            $params
        );
        $result = json_decode($fundraiser, true);
        // Pluck off relevant bits
        $result = $result['data'];
        //write_log( json_encode($result) );
    }

    /**
     * ADDED - Fetch campaign transactions from API
     *
     * @param integer $campaignId ID of organization to pull
     * @param integer $count Number of records to return
     * @return array|bool|mixed
     */
    public function campaignMember($memberID)
    {
        $cacheKey = ClassyOrg::CACHE_KEY_PREFIX . '_CAMPAIGN_MEMBER_' . $memberID;
        $result = get_transient($cacheKey);

        if ($result === false)
        {
            $this_member = $this->apiClient->request(
                '/members/' . $memberID,
                'GET'
            );
            $result = json_decode($this_member, true);
            //write_log($result['id']);
            // Pluck off relevant bits
            $result = $result['id'];

            set_transient($cacheKey, $result, $this->getExpiration());
        }

        return $result;
    }

    /**
     * Fetch campaign fundraising teams from API.
     *
     * @param $campaignId
     * @param int $count
     * @return array|mixed
     */
    public function campaignFundraisingTeams($campaignId, $count = 5)
    {
        $cacheKey = ClassyOrg::CACHE_KEY_PREFIX . '_CAMPAIGN_FUNDRAISING_TEAMS_' . $campaignId;
        $result = get_transient($cacheKey);

        if ($result === false)
        {
            $params = array(
                'aggregates' => 'true',
                'sort' => 'total_raised:desc',
                'per_page' => $count,
                'filter'    => 'status=active'
            );

            $fundraisingPages = $this->apiClient->request(
                '/campaigns/' . $campaignId . '/fundraising-teams'
                , 'GET', 
                $params
            );
            $result = json_decode($fundraisingPages, true);

            $result = $result['data'];

            set_transient($cacheKey, $result, $this->getExpiration());
        }

        return $result;
    }

    /**
     * Simple helper for expiration policy.
     * @return int
     */
    private function getExpiration()
    {
        return (self::EXPIRATION_IN_MINUTES * 60);
    }
}