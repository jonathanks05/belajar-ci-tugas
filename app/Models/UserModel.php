<?php
//Nama: Jonathan Karel
//NIM: A11.2020.13053

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'username', 'email', 'password', 'role', 'created_at', 'updated_at'
    ];
}