<?php
// /counter.php

// 1. Tentukan lokasi file penyimpanan
$dataFile = 'visits.json';
$todayDate = date('Y-m-d');

// 2. Siapkan data default jika file tidak ada
$defaultData = [
    'total' => 0,
    'today' => 0,
    'date' => $todayDate
];

// 3. Baca data yang ada
if (file_exists($dataFile)) {
    $data = json_decode(file_get_contents($dataFile), true);
    if (!$data) {
        $data = $defaultData; // Atasi jika file JSON rusak/kosong
    }
} else {
    $data = $defaultData;
}

// 4. Logika Penghitungan
// Tambah total kunjungan
$data['total'] = ($data['total'] ?? 0) + 1;

// Cek kunjungan harian
if (isset($data['date']) && $data['date'] == $todayDate) {
    // Jika tanggalnya masih sama (hari ini)
    $data['today'] = ($data['today'] ?? 0) + 1;
} else {
    // Jika tanggalnya beda (hari baru)
    $data['today'] = 1; // Reset hitungan harian
    $data['date'] = $todayDate; // Perbarui tanggal
}

// 5. Simpan data baru
file_put_contents($dataFile, json_encode($data));

// 6. Kirim data kembali ke JavaScript
// Penting: Cegah browser menyimpan cache file ini
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

echo json_encode($data);
exit;
?>
