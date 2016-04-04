<?php

/**
 * Class ClassyContent
 *
 * Manage fetching/caching of API content.
 */
class ClassyContent
{
    const EXPIRATION_IN_MINUTES = 10;
    const CLASSY_CACHE_GROUP = 'classy-org';

    private $apiClient;

    /**
     * @param ClassyAPIClient $client
     */
    public function __construct(ClassyAPIClient $client)
    {
        $this->apiClient = $client;
    }

    /**
     * Fetch campaign overview from API
     * @param $campaignId
     * @return array|bool|mixed
     */
    public function campaignOverview($campaignId)
    {
        $cacheKey = 'CAMPAIGN_OVERVIEW_' . $campaignId;
        $result = wp_cache_get($cacheKey, self::CLASSY_CACHE_GROUP);

        if ($result === false)
        {
            $campaign = $this->apiClient->request('/campaigns/' . $campaignId);
            $overview = $this->apiClient->request('/campaigns/' . $campaignId . '/overview');

            $result = json_decode($campaign, true);
            $result['overview'] = json_decode($overview, true);

            wp_cache_set($cacheKey, $result, self::CLASSY_CACHE_GROUP, $this->getExpiration());
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