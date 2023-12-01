<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = new Collection([
                'index' => 'view tasks list',
                'show'=> 'view task',
                'edit' => 'edit task',
                'delete' => 'delete task',
            ]);

        $permissionsByRole = new Collection([
            'editor' => $permissions->all(),
            'viewer' => $permissions->only('index', 'show')->all()
        ]);

        $permissions->map(function ($permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'api']);
        });

        $permissionsByRole->map(function ($permissions, $role) {
            $role = Role::create(['name' => $role, 'guard_name' => 'api']);
            $role->givePermissionTo($permissions);
        });

         User::factory(10)->create()->each(function ($user) {
             $tasks = Task::factory(20)->make();
             $user->tasks()->saveMany($tasks);

             $user->roles()->attach(rand(1, Role::all()->pluck('id')->max()));
         });
    }
}
