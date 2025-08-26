<?php

namespace App\Http\Controllers;

use App\Models\User;
use OneLogin\Saml2\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Storage;

class SamlController extends Controller
{
    private function getSamlAuth(): Auth
    {
        $settings = require base_path('app/Saml/settings.php');
        return new Auth($settings);
    }

    public function login()
    {
        $auth = $this->getSamlAuth();
        return redirect($auth->login());
    }

    public function acs(Request $request)
    {

        $auth = $this->getSamlAuth();
        $auth->processResponse();

        if (!$auth->isAuthenticated()) {
            abort(401, 'SAML Authentication failed');
        }

        // You can get user data here
        $samlUserData = $auth->getAttributes();
        $userEmail = $auth->getNameId();

        $user = User::where('email', $userEmail)->first();

        if (!$user) {
           // return view('auth.login', compact('about'));
            abort(403, 'Unauthorized User');
        }

        FacadesAuth::login($user);

         return redirect('/admin');
    }

    public function metadata()
    {
        $auth = $this->getSamlAuth();
        $metadata = $auth->getSettings()->getSPMetadata();

        header('Content-Type: application/xml');
        return response($metadata);
    }

    public function logout()
    {
        $auth = $this->getSamlAuth();
        return redirect($auth->logout());
    }
}
