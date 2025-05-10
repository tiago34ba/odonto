<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class FrontendRedirectController extends Controller
{
    /**
     * Redireciona para a URL do frontend.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToFrontend(): RedirectResponse
    {
        // Esta é a URL que você forneceu, inspirada no seu LoginController.
        $targetUrl = 'http://127.0.0.1:3000/';

        return redirect()->to($targetUrl);
    }
}
