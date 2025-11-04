<?php
// /admin/manage_holidays.php
if (!defined('ADMIN_PASSWORD')) die('Direct access not allowed'); // Keamanan

$data = read_data();
$holidays = $data['holidays'] ?? [];

if (isset($_GET['action']) && $_GET['action'] === 'add_holiday') {
    $new_holiday = ['date' => $_POST['date'], 'reason' => $_POST['reason']];
    $data['holidays'][] = $new_holiday;
    usort($data['holidays'], function($a, $b) {
        return strcmp($a['date'], $b['date']);
    });
    save_data($data);
    header('Location: index.php?page=holidays&status=saved');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'delete_holiday' && isset($_GET['index'])) {
    $index = (int)$_GET['index'];
    if (isset($data['holidays'][$index])) {
        array_splice($data['holidays'], $index, 1);
        save_data($data);
    }
    header('Location: index.php?page=holidays&status=deleted');
    exit;
}
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Kelola Hari Libur</h1>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  <div class="lg:col-span-1">
    <div class="bg-white shadow-xl rounded-xl overflow-hidden">
      <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-800">Tambah Hari Libur</h2>
      </div>
      <form action="?page=holidays&action=add_holiday" method="POST" class="p-6 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Tanggal</label>
          <input type="date" name="date" required class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Keterangan</label>
          <input type="text" name="reason" placeholder="Contoh: Libur Nasional" required class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm">
        </div>
        <div class="flex justify-end">
          <button type="submit" class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
            Tambah
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="lg:col-span-2">
    <div class="bg-white shadow-xl rounded-xl overflow-hidden">
      <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-800">Daftar Hari Libur Aktif</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-slate-100">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($holidays)): ?>
              <tr>
                <td colspan="3" class="px-6 py-10 text-center text-gray-500">Belum ada data hari libur.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($holidays as $index => $holiday): ?>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($holiday['date']); ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($holiday['reason']); ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <a href="?page=holidays&action=delete_holiday&index=<?php echo $index; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Anda yakin ingin menghapus hari libur ini?');">Hapus</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>
