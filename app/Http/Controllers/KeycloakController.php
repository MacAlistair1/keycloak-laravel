<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Custom\Keycloak\KeycloakService;
use Laravel\Socialite\Facades\Socialite;

class KeycloakController extends Controller
{
    protected $baseUrl;
    protected $realm;
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $logoutRedirect;
    protected $keycloakService;


    public function __construct(KeycloakService $keycloakService)
    {
        $this->keycloakService = $keycloakService;
        $this->baseUrl = config('services.keycloak.base_url');
        $this->realm = config('services.keycloak.realm');
        $this->clientId = config('services.keycloak.client_id');
        $this->clientSecret = config('services.keycloak.client_secret');
        $this->logoutRedirect = config('services.keycloak.logout_redirect');
        $this->redirectUri = config('services.keycloak.redirect');
    }

    public function redirectToKeycloak()
    {

        $url = "{$this->baseUrl}/realms/{$this->realm}/protocol/openid-connect/auth?client_id={$this->clientId}&redirect_uri={$this->redirectUri}&response_type=code&scope=openid";

        return redirect($url);
    }

    public function handleKeycloakCallback(Request $request)
    {

        // Extract the access token from the response
        $accessToken = $this->keycloakService->getUserAccessToken($request->code);

        if ($accessToken) {
            $keycloakUser = $this->keycloakService->getUserInfo($accessToken);

            //[
            // "sub" => "d6d772cb-78d1-4c5d-8aa5-df9f775f6db1"
            // "email_verified" => true
            // "name" => "Jeeven Lamichhane"
            // "preferred_username" => "jeeven"
            // "given_name" => "Jeeven"
            // "family_name" => "Lamichhane"
            // "email" => "lamichhaneaj@gmail.com"
            //]
            $user = User::where('user_name', $keycloakUser['preferred_username'])->first();
            if ($user) {
                Auth::login($user);

                return redirect(route('user.dashboard'))->with(['success' => 'Login successful.']);
            }

            return redirect('/')->with(['error' => 'Customer Not Found.']);
        } else {
            return redirect('/')->with(['error' => 'Login failed.']);
        }
    }

    public function logout()
    {
        $url = "{$this->baseUrl}/realms/{$this->realm}/protocol/openid-connect/logout?post_logout_redirect_uri={$this->logoutRedirect}&client_id={$this->clientId}";

        return redirect($url);
    }

    public function logoutCallBack()
    {
        Auth::logout();
        return redirect('/')->with(['success', 'Successfully logout.']);
    }
}
