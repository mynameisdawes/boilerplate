<?php

namespace Vektor\OneCRM;

use OneCRM\APIClient;
use OneCRM\APIClient\Authentication;

class OneCRMClient
{
    public $client;
    private $api_prefix;

    private $client_id;

    private $client_secret;

    private $username;

    private $password;

    public function __construct()
    {
        $credentials = [
            'api_prefix' => 'endpoint prefix',
            'client_id' => 'client ID',
            'client_secret' => 'client secret',
            'username' => 'username',
            'password' => 'password',
        ];

        foreach ($credentials as $credential_key => $credential_print) {
            $this->{$credential_key} = config("onecrm.{$credential_key}");
            if (empty($this->{$credential_key})) {
                throw new \Exception("The 1CRM API {$credential_print} is missing");
            }
        }

        $options = [
            'scope' => 'read write profile',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'username' => $this->username,
            'password' => $this->password,
        ];

        $flow = new APIClient\AuthorizationFlow("{$this->api_prefix}api.php", $options);
        $access_token = $flow->init('password');
        $auth = new Authentication\OAuth($access_token);
        $client = new APIClient\Client("{$this->api_prefix}api.php", $auth);

        $this->client = $client;
    }
}
