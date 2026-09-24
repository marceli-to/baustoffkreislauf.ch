<?php
namespace App\Http\Controllers\Protect;

use Statamic\Auth\Protect\Protectors\Password\Controller;
use Statamic\Facades\Site;

/**
 * The password form lives at /!/protect/password, outside any site prefix,
 * so Statamic only switches the locale and the layout still renders in the
 * default site. Switch the current site to the one of the protected page.
 */
class PasswordProtectController extends Controller
{
  public function show()
  {
    if ($tokenData = session('statamic:protect:password.tokens.'.request('token'))) {
      if ($site = Site::findByUrl($tokenData['url'])) {
        Site::setCurrent($site->handle());
      }
    }

    return parent::show();
  }
}
