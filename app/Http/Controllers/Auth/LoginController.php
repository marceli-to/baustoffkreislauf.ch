<?php
namespace App\Http\Controllers\Auth;
use Illuminate\Http\Request;
use Statamic\Auth\ThrottlesLogins;
use Statamic\Http\Controllers\CP\Auth\LoginController as StatamicLoginController;

class LoginController extends StatamicLoginController
{
  use ThrottlesLogins;

  protected function authenticated(Request $request, $user)
  {
    $redirect = $request->input('redirect');
    if ($redirect && url()->isValidUrl($redirect)) {
      return redirect()->to($redirect);
    }
    return redirect()->intended($this->redirectPath());
  }
}