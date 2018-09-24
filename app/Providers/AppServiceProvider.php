<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register *.blade.yaml files as blade views.
        resolve(\Illuminate\View\Factory::class)->addExtension('blade.yaml', 'blade');

        // Add helper for migration foreign keys.
        \Illuminate\Database\Schema\Blueprint::macro('foreignId', function (
            string $column,
            string $referencedTable,
            string $referencedColumn = 'id',
            bool $nullable = false
        ) {
            $this->unsignedInteger($column)->nullable($nullable);
            $this->foreign($column)->references($referencedColumn)->on($referencedTable);
        });
        \Illuminate\Database\Schema\Blueprint::macro('foreignUuid', function (
            string $column,
            string $referencedTable,
            string $referencedColumn = 'id',
            bool $nullable = false
        ) {
            $this->uuid($column)->nullable($nullable);
            $this->foreign($column)->references($referencedColumn)->on($referencedTable);
        });

        // Custom morph map.
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'users' => \App\Models\User::class,
            'service_users' => \App\Models\ServiceUser::class,
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
