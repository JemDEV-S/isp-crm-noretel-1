<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Entities\User;
use Modules\Core\Entities\Role;
use Modules\Core\Entities\Permission;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Crear rol de administrador
        $adminRole = Role::create([
            'name' => 'Administrador',
            'description' => 'Rol con acceso completo al sistema',
            'active' => true,
            'default_permissions' => null
        ]);
        
        // Crear permisos para todos los módulos
        $modules = [
            'users' => ['view', 'create', 'edit', 'delete', 'manage'],
            'roles' => ['view', 'create', 'edit', 'delete', 'manage'],
            'configuration' => ['view', 'edit', 'manage'],
            'notifications' => ['view', 'create', 'manage'],
            'workflows' => ['view', 'create', 'edit', 'execute', 'manage'],
            'security' => ['view', 'edit', 'manage'],
            'audit' => ['view', 'export', 'manage']
        ];
        
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                Permission::create([
                    'role_id' => $adminRole->id,
                    'module' => $module,
                    'action' => $action,
                    'allowed' => true,
                    'conditions' => null
                ]);
            }
        }
        
        // Crear usuario administrador
        $admin = User::create([
            'username' => 'admin2',
            'email' => 'admin2@example.com',
            'password' => Hash::make('Admin123!'),
            'status' => 'active',
            'requires_2fa' => false,
            'email_verified_at' => now()
        ]);
        
        // Asignar rol al usuario
        $admin->roles()->attach($adminRole->id);
        
        $this->command->info('Usuario administrador creado con éxito. Credenciales:');
        $this->command->info('   Usuario: admin');
        $this->command->info('   Correo: admin@example.com');
        $this->command->info('   Contraseña: Admin123!');
    }
}