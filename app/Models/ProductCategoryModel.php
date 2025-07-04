<?php
// File: app/Models/ProductCategoryModel.php

namespace App\Models;

use CodeIgniter\Model;

class ProductCategoryModel extends Model
{
    protected $table            = 'product_category';
    protected $primaryKey       = 'id';
    
    // PASTIKAN properti ini berisi 'nama_kategori'
    protected $allowedFields    = ['nama_kategori'];

    // Aktifkan timestamps jika ada kolom created_at & updated_at
    protected $useTimestamps = true;
}
