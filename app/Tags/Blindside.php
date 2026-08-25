<?php

namespace App\Tags;

use App\Support\Blindside as Editions;
use Statamic\Tags\Tags;

class Blindside extends Tags
{
    /**
     * The {{ blindside:login_email }} tag. The account the login form signs
     * in to, derived from the page the visitor wanted to open.
     */
    public function loginEmail()
    {
      return Editions::loginEmail($this->redirect());
    }

    /**
     * The {{ blindside:login_label }} tag, e.g. "Baustofftage 2027".
     */
    public function loginLabel()
    {
      return Editions::loginLabel($this->redirect());
    }

    /**
     * The {{ blindside:login_message }} tag – notice set while redirecting
     * a visitor who was signed in to another edition.
     */
    public function loginMessage()
    {
      return session('blindside.message');
    }

    /**
     * The {{ blindside:login_error }} tag – "wrong password" and friends.
     */
    public function loginError()
    {
      return optional(session('errors'))->first('email');
    }

    protected function redirect()
    {
      return request('redirect');
    }
}
