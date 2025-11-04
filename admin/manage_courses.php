<?php
// /admin/manage_courses.php
if (!defined('ADMIN_PASSWORD')) die('Direct access not allowed'); // Keamanan

// --- LOGIKA SIMPAN & HAPUS JADWAL KULIAH ---
$data = read_data();
$courseSchedule = $data['courseSchedule'] ?? [];

// Menangani Simpan (Edit atau Tambah Baru)
if (isset($_GET['action']) && $_GET['action'] === 'save_course') {
    $new_item = [
        "NO" => $_POST['id'] ?? count($courseSchedule) + 1,
        "RUANG" => $_POST['RUANG'],
        "HARI" => $_POST['HARI'],
        "JAM_MULAI" => substr($_POST['JAM_MULAI'], 0, 5) . ':00',
        "JAM_SELESAI" => substr($_POST['JAM_SELESAI'], 0, 5) . ':00',
        "PROGRAM_STUDI" => $_POST['PROGRAM_STUDI'],
        "MATA_KULIAH" => $_POST['MATA_KULIAH'],
        "KEBUTUHAN_APLIKASI" => $_POST['KEBUTUHAN_APLIKASI'],
        "DOSEN_PENGAMPU" => $_POST['DOSEN_PENGAMPU'],
        "KELAS" => $_POST['KELAS']
    ];

    if (isset($_POST['index']) && $_POST['index'] !== '') {
        $index = (int)$_POST['index'];
        $data['courseSchedule'][$index] = $new_item;
    } else {
        $data['courseSchedule'][] = $new_item;
    }

    save_data($data);

    $redirect_query = http_build_query([
        'page' => 'courses',
        'status' => 'saved',
        'filter_hari' => $_POST['filter_hari'] ?? '',
        'filter_ruang' => $_POST['filter_ruang'] ?? ''
    ]);
    header('Location: index.php?' . $redirect_query);
    exit;
}

// Menangani Hapus
if (isset($_GET['action']) && $_GET['action'] === 'delete_course' && isset($_GET['index'])) {
    $index = (int)$_GET['index'];
    if (isset($data['courseSchedule'][$index])) {
        array_splice($data['courseSchedule'], $index, 1);
        save_data($data);
    }

    $redirect_query = http_build_query([
        'page' => 'courses',
        'status' => 'deleted',
        'filter_hari' => $_GET['filter_hari'] ?? '',
        'filter_ruang' => $_GET['filter_ruang'] ?? ''
    ]);
    header('Location: index.php?' . $redirect_query);
    exit;
}

// --- LOGIKA FILTER ---
$unique_hari = array_unique(array_column($courseSchedule, 'HARI'));
sort($unique_hari);
$unique_ruang = array_unique(array_column($courseSchedule, 'RUANG'));
sort($unique_ruang);

$selected_hari = $_GET['filter_hari'] ?? '';
$selected_ruang = $_GET['filter_ruang'] ?? '';
$filteredSchedule = $courseSchedule;

if ($selected_hari) {
    $filteredSchedule = array_filter($filteredSchedule, function($item) use ($selected_hari) {
        return ($item['HARI'] ?? '') === $selected_hari;
    });
}
if ($selected_ruang) {
    $filteredSchedule = array_filter($filteredSchedule, function($item) use ($selected_ruang) {
        return ($item['RUANG'] ?? '') === $selected_ruang;
    });
}
// --- AKHIR LOGIKA FILTER ---

// Cek notifikasi status
if (isset($_GET['status']) && $_GET['status'] === 'saved') {
  echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
          <strong class="font-bold">Sukses!</strong>
          <span class="block sm:inline">Data jadwal berhasil disimpan.</span>
        </div>';
}
if (isset($_GET['status']) && $_GET['status'] === 'deleted') {
  echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
          <strong class="font-bold">Sukses!</strong>
          <span class="block sm:inline">Data jadwal berhasil dihapus.</span>
        </div>';
}
?>

<?php if (isset($_GET['action']) && ($_GET['action'] === 'add_course' || $_GET['action'] === 'edit_course')): ?>
  <?php
    $item = [
        "NO" => "", "RUANG" => "", "HARI" => "", "JAM_MULAI" => "", "JAM_SELESAI" => "",
        "PROGRAM_STUDI" => "", "MATA_KULIAH" => "", "KEBUTUHAN_APLIKASI" => "",
        "DOSEN_PENGAMPU" => "", "KELAS" => ""
    ];
    $form_title = "Tambah Jadwal Baru";
    $form_index = '';
    $filter_query = http_build_query(['page' => 'courses', 'filter_hari' => $selected_hari, 'filter_ruang' => $selected_ruang]);

    if ($_GET['action'] === 'edit_course' && isset($_GET['index'])) {
        $form_index = (int)$_GET['index'];
        if(isset($courseSchedule[$form_index])) {
            $item = $courseSchedule[$form_index];
            $form_title = "Edit Jadwal: " . htmlspecialchars($item['MATA_KULIAH']);
        }
    }

    $lab_list = array_keys($data['labs']);
    $day_list = ["SENIN", "SELASA", "RABU", "KAMIS", "JUMAT"];
  ?>
  <div class="bg-white shadow-xl rounded-lg overflow-hidden">
    <div class="p-6">
      <h2 class="text-2xl font-bold text-gray-800 mb-4"><?php echo $form_title; ?></h2>
      <form action="?page=courses&action=save_course" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <input type="hidden" name="index" value="<?php echo $form_index; ?>">
        <input type="hidden" name="filter_hari" value="<?php echo htmlspecialchars($selected_hari); ?>">
        <input type="hidden" name="filter_ruang" value="<?php echo htmlspecialchars($selected_ruang); ?>">

        <div>
          <label class="block text-sm font-medium text-gray-700">Hari</label>
          <select name="HARI" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            <?php foreach ($day_list as $hari): ?>
              <option value="<?php echo $hari; ?>" <?php echo (strtoupper($item['HARI'] ?? '') === $hari) ? 'selected' : ''; ?>>
                <?php echo $hari; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Ruangan</label>
          <select name="RUANG" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            <?php foreach ($lab_list as $lab): ?>
              <option value="<?php echo $lab; ?>" <?php echo (($item['RUANG'] ?? '') === $lab) ? 'selected' : ''; ?>>
                <?php echo $lab; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Jam Mulai</label>
          <input type="time" name="JAM_MULAI" value="<?php echo substr($item['JAM_MULAI'] ?? '', 0, 5); ?>" required class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Jam Selesai</label>
          <input type="time" name="JAM_SELESAI" value="<?php echo substr($item['JAM_SELESAI'] ?? '', 0, 5); ?>" required class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm">
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700">Mata Kuliah</label>
          <input type="text" name="MATA_KULIAH" value="<?php echo htmlspecialchars($item['MATA_KULIAH'] ?? ''); ?>" required class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm">
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700">Dosen Pengampu</label>
          <input type="text" name="DOSEN_PENGAMPU" value="<?php echo htmlspecialchars($item['DOSEN_PENGAMPU'] ?? ''); ?>" required class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Program Studi</label>
          <input type="text" name="PROGRAM_STUDI" value="<?php echo htmlspecialchars($item['PROGRAM_STUDI'] ?? ''); ?>" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Angkatan/Kelas</label>
          <input type="text" name="KELAS" value="<?php echo htmlspecialchars($item['KELAS'] ?? ''); ?>" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm">
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700">Kebutuhan Aplikasi</label>
          <textarea name="KEBUTUHAN_APLIKASI" rows="3" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm"><?php echo htmlspecialchars($item['KEBUTUHAN_APLIKASI'] ?? ''); ?></textarea>
        </div>

        <div class="md:col-span-2 flex justify-end gap-4">
          <a href="index.php?<?php echo $filter_query; ?>" class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            Batal
          </a>
          <button type="submit" class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
            Simpan Jadwal
          </button>
        </div>
      </form>
    </div>
  </div>

<?php else: ?>
  <div class="bg-white p-4 rounded-lg shadow-md mb-6">
    <form action="index.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <input type="hidden" name="page" value="courses">
      <div>
        <label for="filter_hari" class="block text-sm font-medium text-gray-700">Filter Hari</label>
        <select name="filter_hari" id="filter_hari" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
          <option value="">Semua Hari</option>
          <?php foreach ($unique_hari as $hari): ?>
            <option value="<?php echo $hari; ?>" <?php echo ($selected_hari === $hari) ? 'selected' : ''; ?>>
              <?php echo $hari; ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="filter_ruang" class="block text-sm font-medium text-gray-700">Filter Ruangan</label>
        <select name="filter_ruang" id="filter_ruang" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
          <option value="">Semua Ruangan</option>
          <?php foreach ($unique_ruang as $ruang): ?>
            <option value="<?php echo $ruang; ?>" <?php echo ($selected_ruang === $ruang) ? 'selected' : ''; ?>>
              <?php echo $ruang; ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="flex items-end gap-2">
        <button type="submit" class="py-2 px-4 rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
          Filter
        </button>
        <a href="index.php?page=courses" class="py-2 px-4 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300">
          Reset
        </a>
      </div>
    </form>
  </div>

  <div class="mb-4 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-gray-800">Daftar Jadwal Mata Kuliah</h2>
    <a href="?page=courses&action=add_course&<?php echo http_build_query(['filter_hari' => $selected_hari, 'filter_ruang' => $selected_ruang]); ?>" class="py-2 px-4 rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
      + Tambah Jadwal Baru
    </a>
  </div>

  <div class="bg-white shadow-xl rounded-lg overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hari</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jam</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ruang</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mata Kuliah</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dosen</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <?php foreach ($filteredSchedule as $index => $item): ?>
          <tr>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($item['HARI'] ?? ''); ?></td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500"><?php echo substr($item['JAM_MULAI'] ?? '', 0, 5) . ' - ' . substr($item['JAM_SELESAI'] ?? '', 0, 5); ?></td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($item['RUANG'] ?? ''); ?></td>
            <td class="px-4 py-3 text-sm text-gray-900 font-medium"><?php echo htmlspecialchars($item['MATA_KULIAH'] ?? ''); ?></td>
            <td class="px-4 py-3 text-sm text-gray-500"><?php echo htmlspecialchars($item['DOSEN_PENGAMPU'] ?? ''); ?></td>
            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
              <?php
                $action_query = http_build_query([
                    'page' => 'courses',
                    'action' => 'edit_course',
                    'index' => $index,
                    'filter_hari' => $selected_hari,
                    'filter_ruang' => $selected_ruang
                ]);
                $delete_query = http_build_query([
                    'page' => 'courses',
                    'action' => 'delete_course',
                    'index' => $index,
                    'filter_hari' => $selected_hari,
                    'filter_ruang' => $selected_ruang
                ]);
              ?>
              <a href="?<?php echo $action_query; ?>" class="text-blue-600 hover:text-blue-900">Edit</a>
              <a href="?<?php echo $delete_query; ?>" class="text-red-600 hover:text-red-900 ml-4" onclick="return confirm('Anda yakin ingin menghapus jadwal ini?');">Hapus</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($filteredSchedule)): ?>
          <tr>
            <td colspan="6" class="px-4 py-10 text-center text-gray-500">
              <?php if ($selected_hari || $selected_ruang): ?>
                Tidak ada jadwal yang cocok dengan filter Anda.
              <?php else: ?>
                Tidak ada data jadwal mata kuliah.
              <?php endif; ?>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
