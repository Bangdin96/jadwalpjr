<?php
// /admin/manage_staff.php
if (!defined('ADMIN_PASSWORD')) die('Direct access not allowed'); // Keamanan

$data = read_data();
$staffDetails = $data['staffDetails'] ?? [];

// --- LOGIKA SIMPAN DATA PJR (DENGAN UPLOAD FOTO) ---
if (isset($_GET['action']) && $_GET['action'] === 'save_staff') {
    $nama_staff = $_POST['nama_staff'];
    $jabatan = $_POST['jabatan'];
    $current_photo = $_POST['current_photo_filename'];
    $target_dir = "../images/";

    if (isset($_FILES['new_photo']) && $_FILES['new_photo']['error'] === 0) {
        $filename_base = pathinfo($current_photo, PATHINFO_FILENAME);
        $filename_base = preg_replace('/-\d+$/', '', $filename_base);
        $new_extension = strtolower(pathinfo($_FILES['new_photo']['name'], PATHINFO_EXTENSION));
        $new_filename = $filename_base . '.' . $new_extension;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES['new_photo']['tmp_name'], $target_file)) {
            $old_file_path = $target_dir . $current_photo;
            if ($current_photo !== $new_filename && file_exists($old_file_path)) {
                unlink($old_file_path);
            }
            $current_photo = $new_filename;
        }
    }
    if (isset($data['staffDetails'][$nama_staff])) {
        $data['staffDetails'][$nama_staff]['jabatan'] = $jabatan;
        $data['staffDetails'][$nama_staff]['photo'] = $current_photo;
        save_data($data);
    }
    header('Location: index.php?page=staff&status=saved');
    exit;
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
        $current_photo_path = "../images/" . htmlspecialchars($item['photo']) . '?v=' . time();
  ?>
  <div class="bg-white shadow-xl rounded-xl overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-800"><?php echo $form_title; ?></h2>
    </div>
    <div class="p-6">
      <form action="?page=staff&action=save_staff" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-6" enctype="multipart/form-data">
        <div class="md:col-span-1">
            <label class="block text-sm font-medium text-gray-700 mb-2">Foto Saat Ini</label>
            <img
                id="photo-preview"
                src="<?php echo $current_photo_path; ?>"
                alt="Foto <?php echo htmlspecialchars($nama_staff); ?>"
                class="w-full h-auto rounded-lg object-cover border border-gray-200"
                onerror="this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iI2M3Y2FjYSI+PHBhdGggZD0iTTIgMmgxOHYyMEgyVjJ6bTAgMGwwIDBsMCAwem0xNy4wMyAwaC4wM3YuMDNIMjJWNC4wMmgtMi45NHptMCAxNS45N3YuMDNoMi45NXYtMi45NGgtMi45OHptLTIuOTYtLjl2Mi45N2gucTAyVjQuMDJIOS4wMnYxMi45N2g1LjA1ek00Ljk4IDQuMDJoMi45N1Y0SDIuMDJ2Mi45N2gyLjk2VjQuMDJ6bTAgMTUuOTdoMi45N1YyMkgxLjAyVjE3LjAzaDIuOTZ2Mi45NnptLTIuOTYtMi45NlY0LjAzSDJWMTcuMDZoMi45OHptMTcuMDQgMi45NlY0LjAzSDE5LjF2MTUuOTZoMi45NnpNMTIgNmEzIDMgMCAxMTAgNiAzIDMgMCAwMDAtNnptLTYuNSA5Yy4zMi0yLjE3IDIuMzktMy43NSA0LjY1LTMuNzVzNC4zMyAxLjU4IDQuNjUgMy43NUg1LjV6Ii8+PC9zdmc+'; this.classList.add('bg-gray-100');"
            >
        </div>
        <div class="md:col-span-2 space-y-6">
            <input type="hidden" name="nama_staff" value="<?php echo htmlspecialchars($nama_staff); ?>">
            <input type="hidden" name="current_photo_filename" value="<?php echo htmlspecialchars($item['photo']); ?>">
            <div>
              <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
              <input type="text" value="<?php echo htmlspecialchars($nama_staff); ?>" disabled class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-gray-100 rounded-md shadow-sm">
              <p class="mt-1 text-xs text-gray-500">Nama tidak dapat diubah (karena terikat ke jadwal).</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Jabatan</label>
              <input type="text" name="jabatan" value="<?php echo htmlspecialchars($item['jabatan']); ?>" required class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Ganti Foto (Opsional)</label>
              <input
                type="file"
                name="new_photo"
                accept="image/png, image/jpeg, image/gif"
                class="mt-1 block w-full text-sm text-gray-500
                  file:mr-4 file:py-2 file:px-4
                  file:rounded-full file:border-0
                  file:text-sm file:font-semibold
                  file:bg-blue-50 file:text-blue-700
                  hover:file:bg-blue-100
                "
                onchange="document.getElementById('photo-preview').src = window.URL.createObjectURL(this.files[0])"
              >
              <p class="mt-1 text-xs text-gray-500">Akan menimpa file lama (misal: 'dinar.jpg' akan ditimpa).</p>
            </div>
            <div class="flex justify-end gap-4">
              <a href="index.php?page=staff" class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                Batal
              </a>
              <button type="submit" class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                Simpan Perubahan
              </button>
            </div>
        </div>
      </form>
    </div>
  </div>
  <?php } ?>

<?php else: ?>
  <h1 class="text-3xl font-bold text-gray-800 mb-6">Kelola Data PJR</h1>

  <div class="bg-white shadow-xl rounded-xl overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-lg font-medium text-gray-800">Daftar Staf PJR (<?php echo count($staffDetails); ?>)</h3>
        </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-slate-100">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Foto</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Staf</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jabatan</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">File Foto</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php foreach ($staffDetails as $nama => $details): ?>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap">
                <img
                  src="../images/<?php echo htmlspecialchars($details['photo']); ?>?v=<?php echo time(); ?>"
                  alt="<?php echo htmlspecialchars($nama); ?>"
                  class="w-10 h-10 rounded-full object-cover"
                  onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                >
                <div
                  class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold"
                  style="display:none;"
                >
                  <?php echo strtoupper(substr($nama, 0, 1)); ?>
                </div>
              </td>
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
  </div>
<?php endif; ?>
