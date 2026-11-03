<?php

namespace App\Modifiers;

use Statamic\Modifiers\Modifier;

class VideoEmbed extends Modifier
{
  /**
   * Turn a YouTube or Vimeo link into a player URL that starts on load.
   * Returns null for anything else.
   */
  public function index($value, $params, $context)
  {
    $url = trim((string) $value);

    if ($url === '') {
      return null;
    }

    // youtube.com/watch?v=ID, youtu.be/ID, /shorts/ID, /embed/ID, /live/ID
    if (preg_match('~(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|shorts/|embed/|live/)|youtu\.be/)([\w-]{11})~i', $url, $m)) {
      return 'https://www.youtube-nocookie.com/embed/'.$m[1].'?autoplay=1&rel=0';
    }

    // vimeo.com/ID, vimeo.com/ID/HASH, player.vimeo.com/video/ID?h=HASH
    if (preg_match('~vimeo\.com/(?:video/)?(\d+)(?:/([\da-f]+))?(?:.*[?&]h=([\da-f]+))?~i', $url, $m)) {
      $hash = $m[2] ?? '' ?: ($m[3] ?? '');

      return 'https://player.vimeo.com/video/'.$m[1].'?autoplay=1'.($hash ? '&h='.$hash : '');
    }

    return null;
  }
}
