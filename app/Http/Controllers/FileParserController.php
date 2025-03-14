<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileParserController extends Controller
{
    public function index()
    {
        $uploadedFiles = $this->getUploadedFiles();
        return view('file-parser.index', compact('uploadedFiles'));
    }

    public function upload(Request $request)
    {
        if (!$request->hasFile('file_upload')) {
            return redirect()->route('home')->with('error', 'Tidak ada file yang diupload');
        }

        $uploadedFiles = $request->file('file_upload'); // Mendapatkan semua file yang diunggah
        $filePath = storage_path('app/public/uploads/');

        if (!file_exists($filePath)) {
            mkdir($filePath, 0777, true);
        }

        $processedFiles = [];

        foreach ($uploadedFiles as $uploadedFile) {
            // Lewati jika bukan file valid
            if (!$uploadedFile->isValid()) continue;

            // Dapatkan nama asli file
            $originalName = $uploadedFile->getClientOriginalName();

            // Periksa apakah item yang diunggah adalah sebuah folder
            if ($uploadedFile->getMimeType() === 'inode/directory') {
                continue; // Lewati folder, karena kita akan mengambil isinya
            }

            // Pastikan hanya file .txt yang diterima
            if ($uploadedFile->getClientOriginalExtension() !== 'txt') {
                continue;
            }

            // Simpan file
            $uploadedFile->move($filePath, $originalName);

            // Proses file txt
            $this->processTxtFile($filePath . $originalName, $originalName);

            $processedFiles[] = $originalName;
        }

        if (empty($processedFiles)) {
            return redirect()->route('home')->with('error', 'Tidak ada file TXT yang valid diupload');
        }

        return redirect()->route('home')->with('success', 'File berhasil diupload dan diproses: ' . implode(', ', $processedFiles));
    }
    
    private function processTxtFile($filePath, $fileName)
    {
        $targetDir = storage_path('app/public/parsed/' . $fileName);
        
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        // Baca konten file
        $content = file_get_contents($filePath);
        
        // Simpan konten asli
        file_put_contents($targetDir . '/' . $fileName, $content);
        
        // Simpan hasil parsing dalam format JSON untuk kemudahan membacanya nanti
        $parsedData = $this->parseFileContent($content);
        file_put_contents(
            $targetDir . '/' . pathinfo($fileName, PATHINFO_FILENAME) . '.json', 
            json_encode($parsedData, JSON_PRETTY_PRINT)
        );
    }

    private function parseFileContent($content)
    {
        $lines = explode("\n", $content);
        $result = ['D01' => ['data' => []], 'formName' => 'Data Tidak Diketahui']; // Default formName jika tidak ditemukan
    
        // Mapping kode ke nama form
        $formMapping = [
            '0000' => 'INFORMASI POKOK BPR',
            '0001' => 'DATA KEPEMILIKAN BPR',
            '0002' => 'DATA ANGGOTA DIREKSI DAN DEWAN KOMISARIS BPR',
            '0003' => 'DATA ORGAN PELAKSANA BPR',
            '0004' => 'DATA KANTOR BPR',
            '0005' => 'DATA PIHAK TERKAIT LAINNYA',
            '0006' => 'DATA MODAL DISETOR, MODAL SUMBANGAN, DAN DANA SETORAN MODAL - EKUITAS',
            '0007' => 'DAFTAR PINJAMAN YANG DITERIMA',
            '0008' => 'RASIO KEUANGAN TRIWULANAN',
            '0009' => 'DATA ANGGOTA DIREKSI DAN ANGGOTA DEWAN KOMISARIS YANG BERHENTI MENJABAT',
            '0010' => 'DATA ORGAN PELAKSANA BPR YANG BERHENTI MENJABAT',
            '0011' => 'DATA KANTOR KAS DAN TRANSAKSI PERBANKAN ELEKTRONIK',
            '0012' => 'DATA PENUTUPAN KANTOR DAN TRANSAKSI PERBANKAN ELEKTRONIK',
            '0013' => 'DOKUMEN PENDUKUNG',
            '0014' => 'DATA JENIS NASABAH DAN PRODUK SIMPANAN DI BPR',
            '0015' => 'LAPORAN DOKUMEN PENILAIAN RISIKO TPPU, TPPT, DAN/ATAU PPSPM',
            '0016' => 'RINCIAN TRANSAKSI TERKAIT PENILAIAN RISIKO TPPU, TPPT, DAN PPSPM',
            '0017' => 'DATA PIHAK LAWAN',
            '0018' => 'LAPORAN PERUBAHAN EKUITAS',
            '0019' => 'LAPORAN ARUS KAS',
            '0020' => 'STRUKTUR ORGANISASI',
            '0021' => 'STRUKTUR KELOMPOK USAHA',
            '0100' => 'LAPORAN POSISI KEUANGAN',
            '0101' => 'REKENING ADMINISTRATIF',
            '0200' => 'LAPORAN LABA RUGI',
            '0300' => 'DAFTAR KAS DALAM VALUTA ASING',
            '0400' => 'DAFTAR SURAT BERHARGA',
            '0500' => 'DAFTAR PENEMPATAN PADA BANK LAIN',
            '0600' => 'DAFTAR KREDIT YANG DIBERIKAN',
            '0601' => 'DAFTAR AGUNAN',
            '0602' => 'DAFTAR KREDIT SINDIKASI',
            '0700' => 'DAFTAR AGUNAN YANG DIAMBIL ALIH',
            '0800' => 'DAFTAR ASET TETAP, INVENTARIS DAN ASET TIDAK BERWUJUD',
            '0900' => 'RINCIAN ASET LAINNYA',
            '0901' => 'RINCIAN ASET LAINNYA LAIN-LAIN',
            '1000' => 'RINCIAN LIABILITAS SEGERA',
            '1100' => 'DAFTAR TABUNGAN',
            '1200' => 'DAFTAR DEPOSITO',
            '1300' => 'DAFTAR SIMPANAN DARI BANK LAIN',
            '1400' => 'RINCIAN LIABILITAS LAINNYA',
            '1401' => 'RINCIAN LIABILITAS LAINNYA LAIN-LAIN',
            '1500' => 'DAFTAR ASET PRODUKTIF YANG DIHAPUSBUKU',
            '1600' => 'DAFTAR PENYERTAAN MODAL',
            '1700' => 'DAFTAR PROPERTI TERBENGKALAI',
            '1800' => 'DAFTAR ASET KEUANGAN LAINNYA',
            '1900' => 'DAFTAR PERBEDAAN KUALITAS ASET PRODUKTIF'
        ];        
    
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
    
            // Ambil prefix (3 karakter pertama)
            $prefix = substr($line, 0, 3);
    
            // Ambil data dengan delimiter "|"
            $parts = explode('|', trim($line));
    
            // Jika H01, ambil kode tipe
            if ($prefix === "H01" && isset($parts[5])) {
                $code = $parts[5];
    
                // Cek apakah kode ada dalam mapping
                if (isset($formMapping[$code])) {
                    $result['formName'] = $formMapping[$code];
                }
                continue; // Abaikan H01 setelah mengambil kode
            }
    
            // Hanya simpan data dengan prefix "D01"
            if ($prefix === "D01") {
                $result['D01']['data'][] = $parts;
            }
        }
    
        return $result;
    }

    public function viewFile($filename)
    {
        $uploadedFiles = $this->getUploadedFiles();
        
        $filePath = storage_path('app/public/parsed/' . $filename . '/' . $filename);
        $jsonPath = storage_path('app/public/parsed/' . $filename . '/' . pathinfo($filename, PATHINFO_FILENAME) . '.json');
        
        $content = file_exists($filePath) ? file_get_contents($filePath) : null;
        $parsedData = file_exists($jsonPath) ? json_decode(file_get_contents($jsonPath), true) : null;
        
        return view('file-parser.view', compact('filename', 'content', 'parsedData', 'uploadedFiles'));
    }

    private function getUploadedFiles()
    {
        $path = storage_path('app/public/parsed');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
            return [];
        }
        
        $folders = array_diff(scandir($path), ['.', '..']);
        return $folders;
    }

    public function deleteFile($filename)
    {
        $filePath = storage_path('app/public/parsed/' . $filename);
        
        if (file_exists($filePath)) {
            // Hapus semua file di dalam folder
            array_map('unlink', glob("$filePath/*"));
            
            // Hapus folder
            rmdir($filePath);
            
            return redirect()->route('home')->with('success', 'File berhasil dihapus');
        }

        return redirect()->route('home')->with('error', 'File tidak ditemukan');
    }
}