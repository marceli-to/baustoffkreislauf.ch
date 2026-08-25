<?php
namespace App\Auth\Protect;

use App\Support\Blindside;
use Statamic\Auth\Protect\Protectors\Authenticated;
use Statamic\Exceptions\ForbiddenHttpException;

/**
 * Like Statamic's "auth" protector, but additionally ties the page to the
 * account of its edition: the Baustofftage 2027 login only opens the 2027
 * pages, and so on. See config/blindside.php.
 */
class BlindsideProtector extends Authenticated
{
  public function protect()
  {
    parent::protect();

    if (!auth()->check()) {
      return;
    }

    $slug = Blindside::slugFromUrl($this->url);

    if (Blindside::mayOpen(auth()->user(), $slug)) {
      return;
    }

    // A guarded page that is missing from config/blindside.php has no account
    // it could belong to – sending the visitor to the login form would only
    // bounce them back here, so this is a dead end on purpose.
    $url = Blindside::editionForSlug($slug) ? $this->getLoginUrl() : null;

    if (!$url) {
      throw new ForbiddenHttpException();
    }

    // Signed in, but with the account of another edition. Everybody shares
    // these logins, so hand the visitor back to the form – it now asks for
    // the password of the edition this page belongs to.
    auth()->logout();

    session()->flash(
      'blindside.message',
      __('Diese Seite gehört zu einer anderen Veranstaltung. Bitte melden Sie sich mit dem dazugehörigen Passwort an.')
    );

    abort(redirect($url));
  }
}
