<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Symfony\Component\Yaml\Yaml;

class BackupCreate extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'cleanup:subscriptions';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Cleans up not confirmed newsletter subscriptions.';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $this->info('Starting cleanup...');

    // Get all files in storage/forms/newsletter
    $files = \File::files(storage_path('forms/newsletter'));

    // Loop through all files
    foreach ($files as $file)
    {
      $content = \File::get($file);
      $yaml = Yaml::parse($content);

      // check if key $yaml['confirmed'] is set and the file is older than 48 hours
      $deadline = time() - 48 * 60 * 60;
      if (!isset($yaml['confirmed']) && filemtime($file) < $deadline)
      {
        // Delete the file
        \File::delete($file);
        $this->info('Deleted ' . $file);
      }
    }
  }
}
