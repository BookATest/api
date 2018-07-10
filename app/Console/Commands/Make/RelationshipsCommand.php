<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\Command;

class RelationshipsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:relationships {model : The name of the Model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Model relationships trait';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $model = $this->argument('model');
        $fileContents = <<<"EOT"
<?php

namespace App\Models\Relationships;

trait {$model}Relationships
{
    //
}

EOT;

        file_put_contents(app_path('Models/Relationships/' . $model . 'Relationships.php'), $fileContents);

        $this->info('Model relationships trait created successfully.');
    }
}
