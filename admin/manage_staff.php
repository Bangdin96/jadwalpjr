<?php
// /admin/manage_staff.php
if (!defined('ADMIN_PASSWORD')) die('Direct access not allowed'); // Keamanan

// --- LOGIKA SIMPAN DATA PJR ---
$data = read_data();
$staffDetails = $data['staffDetails'] ?? [];
$labs = $data['labs'] ?? [];

// Menangani Simpan (Hanya Edit)
if (isset($_GET['action']) && $_GET['action'] === 'save_staff') {
    $nama_staff = $_POST['nama_staff']; // Ini adalah key
    $jabatan = $_POST['jabatan'];
    $photo = $_POST['photo'];

    if (isset($data['staffDetails'][$nama_staff])) {
        $data['staffDetails'][$nama_staff]['jabatan'] = $jabatan;
        $data['staffDetails'][$nama_staff]['photo'] = $photo;
        save_data($data);
    }

    header('Location: index.php?page=staff&status=saved');
    exit;
}

// Cek notifikasi status
if (isset($_GET['status']) && $_GET['status'] === 'saved') {
  echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
          <strong class="font-bold">Sukses!</strong>
          <span class="block sm:inline">Data PJR berhasil diperbarui.</span>
        </div>';
}
?>

<?php if (isset($_GET['action']) && $_GET['action'] === 'edit_staff'): ?>
  <?php
    $nama_staff = $_GET['nama'] ?? '';
    $item = $staffDetails[$nama_staff] ?? null;

    if (!$item) {
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Error: Staf tidak ditemukan.</div>';
    } else {
        $form_title = "Edit Data: " . htmlspecialchars($nama_staff);
  ?>
  <div class="bg-white shadow-xl rounded-lg overflow-hidden">
    <div class="p-6">
      <h2 class="text-2xl font-bold text-gray-800 mb-4"><?php echo $form_title; ?></h2>
      <form action="?page=staff&action=save_staff" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <input type="hidden" name="nama_staff" value="<?php echo htmlspecialchars($nama_staff); ?>">

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
          <input type="text" value="<?php echo htmlspecialchars($nama_staff); ?>" disabled class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-gray-100 rounded-md shadow-sm">
          <p class="mt-1 text-xs text-gray-500">Nama tidak dapat diubah karena terkait dengan data PJR di ruangan.</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Jabatan</label>
          <input type="text" name="jabatan" value="<?php echo htmlspecialchars($item['jabatan']); ?>" required class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Nama File Foto (e.g., dinar.jpg)</label>
          <input type="text" name="photo" value="<?php echo htmlspecialchars($item['photo']); ?>" required class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm">
        </div>

        <div class="md:col-span-2 flex justify-end gap-4">
          <a href="index.php?page=staff" class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            Batal
          </a>
          <button type="submit" class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
            Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
  <?php } ?>

<?php else: ?>
  <div class="mb-4 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-gray-800">Daftar PJR (Staf)</h2>
    <p class="text-sm text-gray-600">Total: <?php echo count($staffDetails); ?> Staf</p>
  </div>

  <div class="bg-white shadow-xl rounded-lg overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Staf</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jabatan</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">File Foto</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <?php foreach ($staffDetails as $nama => $details): ?>
          <tr>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($nama); ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($details['jabatan']); ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($details['photo']); ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <a href="?page=staff&action=edit_staff&nama=<?php echo urlencode($nama); ?>" class="text-blue-600 hover:text-blue-900">Edit</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
