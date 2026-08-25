<?php
namespace App\Http\Controllers\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades\Asset;
use App\Http\Controllers\Controller;
use App\Support\Blindside;

class DownloadController extends Controller
{
  public function download($filename)
  {
    // Check if user is logged in
    if (!auth()->check()) {
      abort(403, 'Unauthorized');
    }

    // Check if the file belongs to the edition the user is logged in to
    if (!Blindside::mayDownload(auth()->user(), $filename)) {
      abort(403, 'Unauthorized');
    }

    // Get the file path
    $path = Storage::disk('assets')->path('protected/' . $filename);

    // Check if file exists
    if (!Storage::disk('assets')->exists('protected/' . $filename)) {
      abort(404);
    }

    // Serve the file
    return response()->file($path, [
      'Content-Disposition' => 'attachment; filename="' . $filename . '"'
    ]);
  }
}
