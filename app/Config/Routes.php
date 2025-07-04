<?php
//Nama: Jonathan Karel
//NIM: A11.2020.13053
use App\Controllers\ProdukController;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Home::index', ['filter' => 'auth']);

$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::login', ['filter' => 'redirect']);
$routes->get('logout', 'AuthController::logout');

$routes->group('produk',['filter' => 'auth'], function ($routes) {
    $routes->get('','ProdukController::index');
    $routes->post('','ProdukController::create');
    $routes->post('edit/(:any)', 'ProdukController::edit/$1');
    $routes->get('delete/(:any)', 'ProdukController::delete/$1');
    $routes->get('download', 'ProdukController::download');
});

$routes->group('kategori', ['filter' => 'auth'], function ($routes) { 
    // URL: /kategori
    $routes->get('', 'KategoriController::index');

    // URL: /kategori/create (untuk form tambah)
    $routes->post('create', 'KategoriController::create');

    // URL: /kategori/update/1 (untuk form ubah)
    $routes->post('update/(:num)', 'KategoriController::update/$1');

    // URL: /kategori/delete/1 (untuk tombol hapus)
    // Menggunakan delete() lebih aman jika Anda pakai form dengan method spoofing
    $routes->delete('delete/(:num)', 'KategoriController::delete/$1'); 
    // Jika tombol hapus Anda hanya link <a> biasa, gunakan get()
    // $routes->get('delete/(:num)', 'KategoriController::delete/$1');
});


$routes->post('update_status', 'TransaksiController::updateStatus');
$routes->get('download', 'TransaksiController::download');

$routes->group('keranjang', ['filter' => 'auth'], function ($routes) {
    $routes->get('', 'TransaksiController::index');
    $routes->post('', 'TransaksiController::cart_add');
    $routes->post('edit', 'TransaksiController::cart_edit');
    $routes->get('delete/(:any)', 'TransaksiController::cart_delete/$1');
    $routes->get('clear', 'TransaksiController::cart_clear');
});

$routes->get('checkout', 'TransaksiController::checkout', ['filter' => 'auth']);
$routes->get('getcity', 'TransaksiController::getcity', ['filter' => 'auth']);
$routes->get('getcost', 'TransaksiController::getcost', ['filter' => 'auth']);
$routes->post('buy', 'TransaksiController::buy', ['filter' => 'auth']);

$routes->get('keranjang', 'TransaksiController::index', ['filter' => 'auth']);

$routes->get('faq', 'Home::faq', ['filter' => 'auth']);
$routes->get('profile', 'Home::profile', ['filter' => 'auth']);
$routes->get('transaksi', 'Home::transaksi', ['filter' => 'auth']);
$routes->get('contact', 'Home::contact', ['filter' => 'auth']);

$routes->group('api', function ($routes) {
    $routes->post('monthly', 'ApiController::monthly');
    $routes->post('yearly', 'ApiController::yearly');
});