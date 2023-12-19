<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Custom\Keycloak\KeycloakService;
use GuzzleHttp\Exception\RequestException;

class DBController extends Controller
{
    protected $keycloakService;

    public function __construct(KeycloakService $keycloakService)
    {
        $this->keycloakService = $keycloakService;
    }

    public function home()
    {

        // dd($this->keycloakService->getUsers(['username' => 'jeeven']));

        // dd($this->keycloakService->getUser("9ec4f475-6fbe-4e05-87e3-e279aee25dc6"));
        // dd($this->keycloakService->logoutUser("9ec4f475-6fbe-4e05-87e3-e279aee25dc6"));
        // dd($this->keycloakService->deleteUser("9ec4f475-6fbe-4e05-87e3-e279aee25dc6"));

        // 9ec4f475-6fbe-4e05-87e3-e279aee25dc6

        // dd($this->getTokenFromKeycloak());

        // Truncate the USERS table
        // User::truncate();

        // User::where('user_id', 3)->orWhereNull('user_id')->delete();

        // $data = [
        //     'user_id' => 2,
        //     'user_name' => 'mac',
        //     'user_email' => 'mac@gmail.com',
        //     'password' => 'password'
        // ];

        // $user = User::create($data);

        // $keycloakUser = [
        //     'username' => $user->user_name,
        //     'email' => $user->user_email,
        //     // 'emailVerified' => true,
        //     // 'enabled' => true,
        //     'firstName' => 'Mac',
        //     'lastName' => 'Alistair',
        //     'password' => $data['password'],
        // ];

        // dd($this->keycloakService->createUser($keycloakUser));

        $users = User::get();

        return view('home', compact('users'));
    }


    private function registerUserInKeycloak($user)
    {
        // https://118.91.168.23:8443/realms/crm/.well-known/uma2-configuration

        $keycloakBaseUrl = 'https://118.91.168.23:8443';
        $realm = 'crm';
        $tokenEndpoint = "$keycloakBaseUrl/realms/$realm/protocol/openid-connect/token";

        $clientId = 'crmClient';
        $clientSecret = 'J8dxKhfichEtnZLSYb1uer7IGPw5mJkX';

        $client = new Client([
            'verify' => false, // Specify the path to your CA certificate bundle
        ]);

        $response = $client->post($tokenEndpoint, [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ],
        ]);

        $token = json_decode($response->getBody())->access_token;

        try {
            $response = $client->post("$keycloakBaseUrl/admin/realms/$realm/users", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'username' => $user['user_name'],
                    'email' => $user['user_email'],
                    'emailVerified' => true,
                    'firstName' =>  'Jeeven',
                    'lastName' => 'Lamichhane',
                    'enabled' => true,
                    'credentials' => [
                        [
                            'type' => 'password',
                            'value' => $user['password'],
                            'temporary' => false,
                        ],
                    ],
                ],
            ]);
            return $response;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }

        // Handle the response accordingly
    }

    private function getTokenFromKeycloak()
    {
        $keycloakBaseUrl = 'https://118.91.168.23:8443/realms';
        $realm = 'crm';
        $tokenEndpoint = "$keycloakBaseUrl/$realm/protocol/openid-connect/token";

        $clientId = 'crmClient';
        $clientSecret = 'J8dxKhfichEtnZLSYb1uer7IGPw5mJkX';

        $client = new Client([
            'verify' => false, // Specify the path to your CA certificate bundle
        ]);

        try {
            $response = $client->post($tokenEndpoint, [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                ],
            ]);
            $token = json_decode($response->getBody())->access_token;

            return $token;
        } catch (RequestException $e) {
            // Handle exception
            return $e->getMessage();
        }
    }
}
