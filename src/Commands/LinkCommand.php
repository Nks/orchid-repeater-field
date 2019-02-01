<?php

declare(strict_types=1);

namespace Nakukryskin\OrchidRepeaterField\Commands;

use Illuminate\Console\Command;

class LinkCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'orchid:repeater:link';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a symbolic link from "vendor/orchid-repeater-field" to "public/orchid_repeater"';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->updateGitIgnore();

        if (file_exists(public_path('orchid_repeater'))) {
            $this->error('The "public/orchid_repeater" directory already exists.');

            return;
        }

        $this->laravel->make('files')->link(realpath(ORCHID_REPEATER_FIELD_PACKAGE_PATH.'/public/'),
            public_path('orchid_repeater'));

        $this->info('The [public/orchid_repeater] directory has been linked.');
    }

    /**
     * Adding orchid_repeater to .gitignore.
     */
    private function updateGitIgnore(): void
    {
        if (! file_exists(app_path('../.gitignore'))) {
            $this->warn('Unable to locate ".gitignore".  Did you move this file?');
            $this->warn('A semantic link to public files was not added to the ignore list');

            return;
        }

        $str = file_get_contents(app_path('../.gitignore'));

        if ($str !== false && strpos($str, '/public/orchid_repeater') === false) {
            file_put_contents(app_path('../.gitignore'), $str.PHP_EOL.'/public/orchid_repeater'.PHP_EOL);
        }
    }
}
