<?php

namespace App\Tags;

use Statamic\Tags\Tags;

class Uuid extends Tags
{
    /**
     * The {{ uuid }} tag.
     *
     * @return string|array
     */
    public function index()
    {
      // Return a UUID
      return \Str::uuid();
    }
}
