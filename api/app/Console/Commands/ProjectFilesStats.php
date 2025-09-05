<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProjectFilesStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:project-files-stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $directory = base_path();
        $file_count = 0;
        $total_size = 0;

        function get_dir_size($path, &$file_count, &$total_size): void
        {
            $files = scandir($path);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $filepath = $path . '/' . $file;
                    if (is_file($filepath)) {
                        $file_count++;
                        $total_size += filesize($filepath);
                    } elseif (is_dir($filepath)) {
                        get_dir_size($filepath, $file_count, $total_size);
                    }
                }
            }
        }

        get_dir_size($directory, $file_count, $total_size);

        echo "Path: " . $directory . "\n";
        echo "Files count: " . $file_count . "\n";
        echo "Total size: " . $total_size . " bites\n";
    }
}
