<?php
namespace App\Http\Controllers\Auth;
use Illuminate\Http\Request;
use Statamic\Auth\ThrottlesLogins;
use Statamic\Facades\Entry;
use Statamic\Http\Controllers\CP\Auth\LoginController as StatamicLoginController;
use App\Support\Blindside;

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

  /**
   * Without a redirect target, send the visitor to the first page of their
   * edition instead of the control panel – these accounts have no CP access.
   */
  public function redirectPath()
  {
    $user = auth()->user();

    if ($user && $user->isSuper()) {
      return parent::redirectPath();
    }

    if ($edition = Blindside::editionForUser($user)) {
      if ($url = $this->firstPageUrl($edition)) {
        return $url;
      }
    }

    return '/';
  }

  protected function firstPageUrl($edition)
  {
    foreach (config("blindside.editions.{$edition}.pages", []) as $pattern) {
      $entry = Entry::query()
        ->where('collection', 'pages')
        ->where('slug', 'like', str_replace('*', '%', $pattern))
        ->first();

      if ($entry) {
        return $entry->url();
      }
    }

    return null;
  }
}
