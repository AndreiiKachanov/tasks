<?php

namespace App\Model;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Jenssegers\Agent\Agent;

/**
 * Model for working with tasks
 */
class View extends BaseModel
{
    public static string $tableName = TABLE_PREFIX . "views";

    private static $instance;
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
        parent::__construct(self::$tableName, 'id');
    }

    /**
     * Singleton
     *
     * @return View
     */
    public static function instance(): View
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get task by id
     *
     * @param int $id
     * @return array
     */
    public function get(int $id): array
    {
        return parent::get($id);
    }

    /**
     * @param array $fields
     * @return false|string
     * @throws GuzzleException
     * @throws Exception
     */
    public function add(array $fields): false|string
    {
        $agent = new Agent();
        $deviceVersion = $agent->version($agent->device());
        $ipAddress = $fields['REMOTE_ADDR'] ?? null;
        $requestUri = $fields['REQUEST_URI'] ?? null;
        $dataToInsert = [
            'ip' => $ipAddress,
            'ip_info' => serialize($this->getIpAddressInfo($ipAddress)),
            'request_uri' => $requestUri,
            'browser' => (string)$agent->browser(),
            'platform' => (string)$agent->platform(),
            'device' => (string)$agent->device(),
            'device_version' => ($deviceVersion === false) ? null : $deviceVersion, // Получение версии мобильного устройства
            'is_mobile' => (int)$agent->isMobile(),
            'is_tablet' => (int)$agent->isTablet(),
            'is_desktop' => (int)$agent->isDesktop(),
            'is_robot' => (int)$agent->isRobot()
        ];

        return parent::add($dataToInsert);
    }

    /**
     * @param string $ipAddress
     * @return array|false[]
     * @throws GuzzleException
     */
    public function getIpAddressInfo(string $ipAddress): array
    {
        //$ipAddress = '8.8.8.8';
        $url = "https://ipwho.is/$ipAddress";
        try {
            $response = $this->client->request('GET', $url, [
                'objects' => 'type,city,flag,region,country,country_code',
                'output' => 'json'
            ]);
            $responseData = json_decode($response->getBody(), true);
            if ($responseData && isset($responseData['success']) && $responseData['success'] === true) {
                return [
                    'type' => $responseData['type'],
                    'city' => $responseData['city'],
                    'flag' => $responseData['flag']['img'],
                    'region' => $responseData['region'],
                    'country' => $responseData['country'],
                    'country_code' => $responseData['country_code'],
                ];
            } else {
                return ['success' => false];
            }
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}
