<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'Jose Daniel Grijalba',
            'email' => 'jose.jdgo97@gmail.com',
            'password' => bcrypt('123123123')
        ]);

        //Usuario administrador
        $rol = Role::create(['name' => 'administrador']);
        $permisos = Permission::pluck('id','id')->all();
        $rol->syncPermissions($permisos);
        //$user = User::find(1);
        $user->assignRole('administrador'); 
        // User::create([
        //     'name'=>'admin',
        //     //'sexo'=> 'M',
        //     //'telefono'=>'314852684',
        //     'email'=> 'admin.admin@gmail.com',
        //     'email_verified_at' => now(),
        //     'password'=> bcrypt('admin'),
        // ])->assignRole('admin');

        // User::create([
        //     'name'=>'Jose Daniel Grijalba Osorio',
        //     //'sexo'=> 'M',
        //     //'telefono'=>'314852684',
        //     'email'=> 'jose.jdgo97@gmail.com',
        //     'email_verified_at' => now(),
        //     'password'=> bcrypt('123123123'),
        // ])->assignRole('admin');


        // User::create([
        //     'name'=>'Juan David Grijalba Osorio',
        //     //'sexo'=> 'M',
        //     //'telefono'=>'314852685',
        //     'email'=> 'juandavidgo1997@gmail.com',
        //     'email_verified_at' => now(),
        //     'password'=> bcrypt('123123123'),
        // ])->assignRole('funcionario');

        // User::create([
        //     'name'=>'Hebron funcionario',
        //     //'sexo'=> 'M',
        //     //'telefono'=>'314852686',
        //     'email'=> 'hebron.customer@gmail.com',
        //     'email_verified_at' => now(),
        //     'password'=> bcrypt('123123123'),
        // ])->assignRole('funcionario');

        // User::create([
        //     'name'=>'Mario',
        //     //'sexo'=> 'M',
        //     //'telefono'=>'314852567',
        //     'email'=> 'mario@gmail.com',
        //     'email_verified_at' => now(),
        //     'password'=> bcrypt('123123123'),
        // ])->assignRole('funcionario');

        // User::create([
        //     'name'=>'Alejandro',
        //     //'sexo'=> 'M',
        //     //'telefono'=>'314852568',
        //     'email'=> 'alejo@gmail.com',
        //     'email_verified_at' => now(),
        //     'password'=> bcrypt('123123123'),
        // ])->assignRole('funcionario');
        // User::create([
        //     'name'=>'Luigi Mangione',
        //     //'sexo'=> 'M',
        //     //'telefono'=>'314852568',
        //     'email'=> 'luigi7@gmail.com',
        //     'email_verified_at' => now(),
        //     'password'=> bcrypt('123123123'),
        // ])->assignRole('funcionario');

        // User::factory(9)->create();
    }
}
