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
        if (file_exists(public_path('orchid_repeater'))) {
            $this->error('The "public/orchid_repeater" directory already exists.');

            return;
        }

        $this->laravel->make('files')->link(realpath(ORCHID_REPEATER_FIELD_PACKAGE_PATH . '/public/'),
            public_path('orchid_repeater'));

        $this->info('The [public/orchid_repeater] directory has been linked.');
    }
}
