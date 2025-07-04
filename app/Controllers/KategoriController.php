<?php
// File: app/Controllers/KategoriController.php

namespace App\Controllers;

use App\Models\ProductCategoryModel;

class KategoriController extends BaseController
{
    protected $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new ProductCategoryModel();
    }

    // Menampilkan halaman utama
    public function index()
    {
        $data = [
            'kategori' => $this->categoryModel->findAll(),
        ];
        return view('v_kategori_produk', $data);
    }

    // Menangani form dari modal "Tambah Data"
    public function create()
    {
        // Ambil data dari form dengan kunci 'nama_kategori'
        $namaKategori = $this->request->getPost('nama_kategori');

        // Siapkan data untuk disimpan dengan kunci 'nama_kategori'
        $dataToSave = [
            'nama_kategori' => $namaKategori
        ];

        // Simpan data
        $this->categoryModel->save($dataToSave);

        session()->setFlashdata('pesan', 'Data berhasil ditambahkan.');
        return redirect()->to('/kategori'); // Sesuaikan URL jika berbeda
    }

    // Menangani form dari modal "Ubah Data"
    public function update($id)
    {
        // Ambil data dari form dengan kunci 'nama_kategori'
        $namaKategori = $this->request->getPost('nama_kategori');

        // Siapkan data untuk diupdate dengan kunci 'nama_kategori'
        $dataToUpdate = [
            'nama_kategori' => $namaKategori
        ];

        // Update data
        $this->categoryModel->update($id, $dataToUpdate);

        session()->setFlashdata('pesan', 'Data berhasil diubah.');
        return redirect()->to('/kategori');
    }

    // Menangani link "Hapus"
    public function delete($id)
    {
        $this->categoryModel->delete($id);
        
        session()->setFlashdata('pesan', 'Data berhasil dihapus.');
        return redirect()->to('/kategori');
    }
}
