<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class TransaksiController extends BaseController
{
    protected $cart;
    protected $transaction;
    protected $transaction_detail;
    protected $client; // Guzzle HTTP Client
    protected $apiKey;

    public function __construct()
    {
        helper('number');
        helper('form');
        $this->cart = \Config\Services::cart();
        $this->transaction = new TransactionModel();
        $this->transaction_detail = new TransactionDetailModel();
        
        // Inisialisasi Guzzle Client
        $this->client = new \GuzzleHttp\Client();
        
        // Ambil API Key dari file .env. Pastikan Anda sudah mengaturnya.
        $this->apiKey = env('COST_KEY'); 
    }

    // Menampilkan halaman keranjang (sudah benar)
    public function index()
    {
        $data['items'] = $this->cart->contents();
        $data['total'] = $this->cart->total();
        return view('v_keranjang', $data);
    }

    // Menambahkan item ke keranjang (sudah benar)
    public function cart_add()
    {
        $this->cart->insert([
            'id'      => $this->request->getPost('id'),
            'qty'     => 1,
            'price'   => $this->request->getPost('harga'),
            'name'    => $this->request->getPost('nama'),
            'options' => ['foto' => $this->request->getPost('foto')]
        ]);
        session()->setflashdata('success', 'Produk berhasil ditambahkan ke keranjang. (<a href="' . base_url('keranjang') . '">Lihat</a>)');
        return redirect()->to(base_url('/'));
    }

    // Mengosongkan keranjang (sudah benar)
    public function cart_clear()
    {
        $this->cart->destroy();
        session()->setflashdata('success', 'Keranjang Berhasil Dikosongkan');
        return redirect()->to(base_url('keranjang'));
    }

    // Mengedit kuantitas di keranjang (sudah benar)
    public function cart_edit()
    {
        $i = 1;
        foreach ($this->cart->contents() as $value) {
            $this->cart->update([
                'rowid' => $value['rowid'],
                'qty'   => $this->request->getPost('qty' . $i++)
            ]);
        }
        session()->setflashdata('success', 'Keranjang Berhasil Diedit');
        return redirect()->to(base_url('keranjang'));
    }

    // Menghapus item dari keranjang (sudah benar)
    public function cart_delete($rowid)
    {
        $this->cart->remove($rowid);
        session()->setflashdata('success', 'Item Berhasil Dihapus dari Keranjang');
        return redirect()->to(base_url('keranjang'));
    }

    // Menampilkan halaman checkout
    public function checkout()
    {
        $data['items'] = $this->cart->contents();
        $data['total'] = $this->cart->total();
        // Pengambilan data provinsi/kota sekarang dilakukan via AJAX, 
        // jadi tidak perlu dimuat di sini.
        return view('v_checkout', $data);
    }

    // Mencari lokasi tujuan (untuk AJAX di halaman checkout)
    public function getLocation()
    {
        $search = $this->request->getGet('search');
        try {
            $response = $this->client->request(
                'GET', 
                'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination', [
                    'query' => [
                        'search' => $search,
                        'limit' => 50,
                    ],
                    'headers' => [
                        'accept' => 'application/json',
                        'key' => $this->apiKey,
                    ],
                ]
            );
            $body = json_decode($response->getBody(), true); 
            return $this->response->setJSON($body['data']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode(500);
        }
    }

    // Menghitung ongkos kirim (untuk AJAX di halaman checkout)
    public function getCost()
    { 
        $destination = $this->request->getGet('destination');
        try {
            $response = $this->client->request(
                'POST', 
                'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
                    'multipart' => [
                        ['name' => 'origin', 'contents' => '64999'], // Asal: PEDURUNGAN TENGAH
                        ['name' => 'destination', 'contents' => $destination],
                        ['name' => 'weight', 'contents' => '1000'], // Berat: 1000 gram
                        ['name' => 'courier', 'contents' => 'jne'], // Kurir: JNE
                    ],
                    'headers' => [
                        'accept' => 'application/json',
                        'key' => $this->apiKey,
                    ],
                ]
            );
            $body = json_decode($response->getBody(), true); 
            return $this->response->setJSON($body['data']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode(500);
        }
    }

    // Memproses pembelian (sudah benar)
    public function buy()
    {
        if ($this->request->getPost()) { 
            $dataForm = [
                'username' => $this->request->getPost('username'),
                'total_harga' => $this->request->getPost('total_harga'),
                'alamat' => $this->request->getPost('alamat'),
                'ongkir' => $this->request->getPost('ongkir'),
                'status' => 0,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ];

            $this->transaction->insert($dataForm);
            $last_insert_id = $this->transaction->getInsertID();

            foreach ($this->cart->contents() as $value) {
                $dataFormDetail = [
                    'transaction_id' => $last_insert_id,
                    'product_id' => $value['id'],
                    'jumlah' => $value['qty'],
                    'diskon' => 0,
                    'subtotal_harga' => $value['qty'] * $value['price'],
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s")
                ];
                $this->transaction_detail->insert($dataFormDetail);
            }
            $this->cart->destroy();
            return redirect()->to(base_url('profile'));
        }
    }

    // Mengupdate status transaksi (sudah benar)
    public function updateStatus()
    {
        $id_transaksi = $this->request->getPost('id_transaksi');
        $status = $this->request->getPost('status');
        $data = ['status' => $status];

        if ($this->transaction->update($id_transaksi, $data)) {
            session()->setFlashdata('success', 'Data Berhasil Diubah.');
            return redirect()->to('/transaksi');
        } else {
            session()->setFlashdata('error', 'Gagal memperbarui status transaksi.');
            return redirect()->back();
        }
    }

    // Mengunduh laporan transaksi (sudah benar)
    public function download()
    {
        $transactions = $this->transaction->findAll();
        $html = view('v_transaksiPDF', ['transactions' => $transactions]);
        $filename = date('y-m-d-H-i-s') . '-transaksi';
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($filename);
    }
}
