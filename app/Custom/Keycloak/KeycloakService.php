<?php

namespace App\Custom\Keycloak;

use Error;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Exception\RequestException;
use PHPUnit\Event\Code\Throwable;

class KeycloakService
{
    protected $baseUrl;
    protected $realm;
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;


    public function __construct()
    {
        $this->baseUrl = config('services.keycloak.base_url');
        $this->realm = config('services.keycloak.realm');
        $this->clientId = config('services.keycloak.client_id');
        $this->clientSecret = config('services.keycloak.client_secret');
        $this->redirectUri = config('services.keycloak.redirect');
    }

    public function getToken()
    {
        $tokenEndpoint = "{$this->baseUrl}/realms/{$this->realm}/protocol/openid-connect/token";

        $client = new Client([
            'verify' => false,
        ]);

        try {
            $response = $client->post($tokenEndpoint, [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ],
            ]);

            return json_decode($response->getBody())->access_token;
        } catch (RequestException $e) {
            // Handle exception
            return $e->getMessage();
        }
    }

    public function createUser($user)
    {
        $token = $this->getToken();

        $userEndpoint = "{$this->baseUrl}/admin/realms/{$this->realm}/users";

        $client = new Client([
            'verify' => false,
        ]);

        try {
            $response = $client->post($userEndpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'emailVerified' => $user['emailVerified'] ?? true,
                    'firstName' =>  $user['firstName'],
                    'lastName' => $user['lastName'],
                    'enabled' => $user['enabled'] ?? true,
                    'credentials' => [
                        [
                            'type' => 'password',
                            'value' => $user['password'],
                            'temporary' => false,
                        ],
                    ],
                    'groups' => ['customer'],
                ],
            ]);

            return $response;
        } catch (RequestException $e) {
            // Handle exception
            return ['error' => $e->getMessage()];
        }
    }


    public function getUsers($params = [])
    {
        $url = $this->baseUrl . "/admin/realms/" . $this->realm . "/users";

        $token = $this->getToken();

        // Include the token in the Authorization header
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];

        $response = Http::withHeaders($headers)->withoutVerifying()->get($url, $params);

        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->json(); // Handle error response
        }
    }

    public function getUser($userId)
    {
        $url = $this->baseUrl . "/admin/realms/" . $this->realm . "/users/$userId";

        $token = $this->getToken();

        $userProfileMetadata = [];

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $queryParams = ['userProfileMetadata' => $userProfileMetadata];

        $response = Http::withHeaders($headers)->withoutVerifying()->get($url, $queryParams);

        // Check the response and handle accordingly
        if ($response->successful()) {
            return $response->json(); // User representation successfully retrieved
        } else {
            return $response->json(); // Handle error response
        }
    }

    public function deleteUser($userId)
    {
        $url = $this->baseUrl . "/admin/realms/" . $this->realm . "/users/$userId";

        $token = $this->getToken();

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $response = Http::withHeaders($headers)->withoutVerifying()->delete($url);

        // Check the response and handle accordingly
        if ($response->successful()) {
            return $response->json(); // User successfully deleted
        } else {
            return $response->json(); // Handle error response
        }
    }

    public function logoutUser($userId)
    {
        $url = $this->baseUrl . "/admin/realms/" . $this->realm . "/users/$userId/logout";

        $token = $this->getToken();

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $response = Http::withHeaders($headers)->withoutVerifying()->post($url);

        // Check the response and handle accordingly
        if ($response->successful()) {
            return $response->json(); // User successfully logged out
        } else {
            return $response->json(); // Handle error response
        }
    }

    public function getUserInfo($accessToken)
    {
        $url = config('services.keycloak.base_url') . "/realms/" . config('services.keycloak.realm') . "/protocol/openid-connect/userinfo";
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ];


        $response = Http::withHeaders($headers)->withoutVerifying()->get($url);

        if ($response->successful()) {
            return $response->json();
        } else {
            // Handle error response
            return null;
        }
    }

    public function getUserAccessToken($authorizationCode)
    {
        $tokenEndpoint = "{$this->baseUrl}/realms/{$this->realm}/protocol/openid-connect/token";

        try {
            $response = (new Client([
                'verify' => false,
            ]))->post($tokenEndpoint, [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'code' => $authorizationCode,
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'redirect_uri' => $this->redirectUri,
                ],
            ]);

            // Extract the access token from the response
            $accessToken = json_decode($response->getBody())->access_token;

            return $accessToken;
        } catch (\Throwable $th) {
            return "";
        }
    }


    //not working
    public function createRole($roleName, $description = null, $clientRole = true)
    {
        $token = $this->getToken();

        $url = "{$this->baseUrl}/admin/realms/{$this->realm}/clients/{$this->clientId}/roles";

        $client = new Client([
            'verify' => false,
        ]);

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'name' => $roleName,
                    'description' => $description,
                    'clientRole' => $clientRole,
                ],
            ]);

            return json_decode($response->getBody());
        } catch (RequestException $e) {
            // Handle exception
            return ['error' => $e->getMessage()];
        }
    }

    public function createGroup($groupName)
    {
        $token = $this->getToken();

        $groupsEndpoint = "{$this->baseUrl}/admin/realms/{$this->realm}/groups";

        $client = new Client([
            'verify' => false,
        ]);

        try {
            $response = $client->post($groupsEndpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'name' => $groupName,
                ],
            ]);

            return $response;
        } catch (RequestException $e) {
            // Handle exception
            return ['error' => $e->getMessage()];
        }
    }


    public function assignRoleToGroup($roleName, $groupId)
    {
        $token = $this->getToken();

        $url = "{$this->baseUrl}/admin/realms/{$this->realm}/roles/{$roleName}/composites";

        $client = new Client([
            'verify' => false
        ]);

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'realm' => $this->realm,
                    'clientRole' => true,
                    'composite' => true,
                    'id' => $groupId,
                ],
            ]);

            return json_decode($response->getBody());
        } catch (RequestException $e) {
            // Handle exception
            return ['error' => $e->getMessage()];
        }
    }

    public function tokenBasedLogin($username, $password)
    {

        $tokenEndpoint = "{$this->baseUrl}/realms/{$this->realm}/protocol/openid-connect/token";

        $response = Http::asForm()->withoutVerifying()->post($tokenEndpoint, [
            'grant_type' => 'password',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'username' => $username,
            'password' => $password,
            'scope' => 'openid',
        ]);

        try {
            return  $response->json()['access_token'];
        } catch (\Throwable $th) {
            return "";
        }
    }
}
