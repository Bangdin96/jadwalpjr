<?php
// /admin/manage_courses.php
if (!defined('ADMIN_PASSWORD')) die('Direct access not allowed'); // Keamanan
?>

<style>
.tag-input-container { display: flex; flex-wrap: wrap; gap: 8px; padding: 8px; border: 1px solid #d1d5db; border-radius: 0.375rem; min-height: 42px; background-color: #f9fafb; }
.tag { display: inline-flex; align-items: center; padding: 4px 12px; background-color: #dbeafe; color: #1e40af; border-radius: 9999px; font-size: 14px; font-weight: 500; }
.tag-remove { margin-left: 8px; cursor: pointer; font-weight: bold; color: #93c5fd; }
.tag-remove:hover { color: #1e40af; }
/* Style untuk tombol pagination */
.pagination-link {
    display: inline-block;
    padding: 8px 14px;
    border-radius: 0.375rem;
    font-size: 14px;
    font-weight: 500;
    transition: background-color 0.2s;
}
.pagination-link-active {
    background-color: #3b82f6; /* bg-blue-600 */
    color: white;
}
.pagination-link-default {
    background-color: #f3f4f6; /* bg-gray-100 */
    color: #374151; /* text-gray-700 */
    border: 1px solid #e5e7eb; /* border-gray-200 */
}
.pagination-link-default:hover {
    background-color: #e5e7eb; /* hover:bg-gray-200 */
}
.pagination-link-disabled {
    background-color: #f9fafb; /* bg-gray-50 */
    color: #9ca3af; /* text-gray-400 */
    cursor: not-allowed;
    border: 1px solid #e5e7eb;
}
</style>

<?php
// --- LOGIKA SIMPAN & HAPUS JADWAL KULIAH ---
$data = read_data();
$courseSchedule = $data['courseSchedule'] ?? [];

// Menangani Simpan (Edit atau Tambah Baru)
if (isset($_GET['action']) && $_GET['action'] === 'save_course') {
    $new_item = [
        "NO" => $_POST['id'] ?? count($courseSchedule) + 1, "RUANG" => $_POST['RUANG'], "HARI" => $_POST['HARI'],
        "JAM_MULAI" => substr($_POST['JAM_MULAI'], 0, 5) . ':00', "JAM_SELESAI" => substr($_POST['JAM_SELESAI'], 0, 5) . ':00',
        "PROGRAM_STUDI" => $_POST['PROGRAM_STUDI'], "MATA_KULIAH" => $_POST['MATA_KULIAH'],
        "KEBUTUHAN_APLIKASI" => $_POST['KEBUTUHAN_APLIKASI'], "DOSEN_PENGAMPU" => $_POST['DOSEN_PENGAMPU'], "KELAS" => $_POST['KELAS']
    ];
    if (isset($_POST['index']) && $_POST['index'] !== '') {
        $data['courseSchedule'][(int)$_POST['index']] = $new_item;
    } else {
        $data['courseSchedule'][] = $new_item;
    }
    save_data($data);

    // PERUBAHAN: Tambahkan limit & p ke redirect
    $redirect_query = http_build_query([
        'page' => 'courses',
        'status' => 'saved',
        'filter_hari' => $_POST['filter_hari'] ?? '',
        'filter_ruang' => $_POST['filter_ruang'] ?? '',
        'limit' => $_POST['limit'] ?? 10,
        'p' => $_POST['p'] ?? 1
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

    // PERUBAHAN: Tambahkan limit & p ke redirect
    $redirect_query = http_build_query([
        'page' => 'courses',
        'status' => 'deleted',
        'filter_hari' => $_GET['filter_hari'] ?? '',
        'filter_ruang' => $_GET['filter_ruang'] ?? '',
        'limit' => $_GET['limit'] ?? 10,
        'p' => $_GET['p'] ?? 1
    ]);
    header('Location: index.php?' . $redirect_query);
    exit;
}

// --- LOGIKA FILTER ---
$unique_hari = array_unique(array_column($courseSchedule, 'HARI')); sort($unique_hari);
$unique_ruang = array_unique(array_column($courseSchedule, 'RUANG')); sort($unique_ruang);

$selected_hari = $_GET['filter_hari'] ?? '';
$selected_ruang = $_GET['filter_ruang'] ?? '';
$filteredSchedule = $courseSchedule;

if ($selected_hari) { $filteredSchedule = array_filter($filteredSchedule, function($item) use ($selected_hari) { return ($item['HARI'] ?? '') === $selected_hari; }); }
if ($selected_ruang) { $filteredSchedule = array_filter($filteredSchedule, function($item) use ($selected_ruang) { return ($item['RUANG'] ?? '') === $selected_ruang; }); }

// === LOGIKA PAGINATION BARU ===
$limit_options = [5, 10, 20, 50, 100];
$limit = (int)($_GET['limit'] ?? 10);
if (!in_array($limit, $limit_options)) $limit = 10;

$total_items = count($filteredSchedule);
$total_pages = ceil($total_items / $limit);
$page = (int)($_GET['p'] ?? 1);
if ($page < 1) $page = 1;
if ($page > $total_pages && $total_pages > 0) $page = $total_pages;

$offset = ($page - 1) * $limit;

// Ambil data untuk halaman ini, DENGAN MEMPERTAHANKAN INDEX ASLI (array_slice true)
$paginatedSchedule = array_slice($filteredSchedule, $offset, $limit, true);
// === AKHIR LOGIKA PAGINATION ===
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
    // PERUBAHAN: Kirim limit & p ke link Batal
    $filter_query = http_build_query([
        'page' => 'courses',
        'filter_hari' => $selected_hari,
        'filter_ruang' => $selected_ruang,
        'limit' => $limit,
        'p' => $page
    ]);

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
        <input type="hidden" name="limit" value="<?php echo $limit; ?>">
        <input type="hidden" name="p" value="<?php echo $page; ?>">

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
          <input type="hidden" name="KEBUTUHAN_APLIKASI" id="kebutuhan_aplikasi_hidden" value="<?php echo htmlspecialchars($item['KEBUTUHAN_APLIKASI'] ?? ''); ?>">
          <div id="tags-container" class="tag-input-container mt-1">
          </div>
          <div class="flex mt-2">
            <input type="text" id="new-tag-input" placeholder="Ketik aplikasi, pisah dgn koma..." class="flex-1 py-2 px-3 border border-gray-300 rounded-l-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <button type="button" id="add-tag-btn" class="py-2 px-4 border border-transparent rounded-r-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
              Tambah
            </button>
          </div>
          <p class="mt-1 text-xs text-gray-500">Pisahkan dengan koma (,) untuk menambah beberapa aplikasi sekaligus.</p>
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
        <select name="filter_hari" id="filter_hari" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" onchange="this.form.submit()">
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
        <select name="filter_ruang" id="filter_ruang" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" onchange="this.form.submit()">
          <option value="">Semua Ruangan</option>
          <?php foreach ($unique_ruang as $ruang): ?>
            <option value="<?php echo $ruang; ?>" <?php echo ($selected_ruang === $ruang) ? 'selected' : ''; ?>>
              <?php echo $ruang; ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="limit" class="block text-sm font-medium text-gray-700">Tampilkan per</label>
        <select name="limit" id="limit" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" onchange="this.form.submit()">
          <?php foreach ($limit_options as $opt): ?>
            <option value="<?php echo $opt; ?>" <?php echo ($limit === $opt) ? 'selected' : ''; ?>>
              <?php echo $opt; ?> baris
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="flex items-end gap-2">
        <a href="index.php?page=courses" class="py-2 px-4 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300">
          Reset
        </a>
      </div>
    </form>
  </div>

  <div class="bg-white shadow-xl rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
      <h3 class="text-lg font-medium text-gray-800">Daftar Jadwal (Total: <?php echo $total_items; ?>)</h3>
      <a href="?page=courses&action=add_course&<?php echo http_build_query(['filter_hari' => $selected_hari, 'filter_ruang' => $selected_ruang, 'limit' => $limit, 'p' => $page]); ?>" class="py-2 px-4 rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
        + Tambah Jadwal Baru
      </a>
    </div>
    <div class="overflow-x-auto">
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
          <?php foreach ($paginatedSchedule as $index => $item): ?>
            <tr>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($item['HARI'] ?? ''); ?></td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500"><?php echo substr($item['JAM_MULAI'] ?? '', 0, 5) . ' - ' . substr($item['JAM_SELESAI'] ?? '', 0, 5); ?></td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($item['RUANG'] ?? ''); ?></td>
              <td class="px-4 py-3 text-sm text-gray-900 font-medium"><?php echo htmlspecialchars($item['MATA_KULIAH'] ?? ''); ?></td>
              <td class="px-4 py-3 text-sm text-gray-500"><?php echo htmlspecialchars($item['DOSEN_PENGAMPU'] ?? ''); ?></td>
              <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                <?php
                  // PERUBAHAN: Tambahkan limit & p ke link aksi
                  $action_query = http_build_query([
                      'page' => 'courses', 'action' => 'edit_course', 'index' => $index,
                      'filter_hari' => $selected_hari, 'filter_ruang' => $selected_ruang,
                      'limit' => $limit, 'p' => $page
                  ]);
                  $delete_query = http_build_query([
                      'page' => 'courses', 'action' => 'delete_course', 'index' => $index,
                      'filter_hari' => $selected_hari, 'filter_ruang' => $selected_ruang,
                      'limit' => $limit, 'p' => $page
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

    <?php if ($total_pages > 1): ?>
    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
      <p class="text-sm text-gray-700">
        Menampilkan
        <span class="font-medium"><?php echo $offset + 1; ?></span>
        -
        <span class="font-medium"><?php echo min($offset + $limit, $total_items); ?></span>
        dari
        <span class="font-medium"><?php echo $total_items; ?></span>
        hasil
      </p>

      <div class="flex items-center space-x-2">
        <?php
          // Query string dasar untuk link pagination
          $base_query = http_build_query([
              'page' => 'courses',
              'filter_hari' => $selected_hari,
              'filter_ruang' => $selected_ruang,
              'limit' => $limit
          ]);
        ?>

        <?php if ($page > 1): ?>
          <a href="?<?php echo $base_query . '&p=' . ($page - 1); ?>" class="pagination-link pagination-link-default">Previous</a>
        <?php else: ?>
          <span class="pagination-link pagination-link-disabled">Previous</span>
        <?php endif; ?>

        <span class="text-sm text-gray-700">
          Halaman <span class="font-medium"><?php echo $page; ?></span> dari <span class="font-medium"><?php echo $total_pages; ?></span>
        </span>

        <?php if ($page < $total_pages): ?>
          <a href="?<?php echo $base_query . '&p=' . ($page + 1); ?>" class="pagination-link pagination-link-default">Next</a>
        <?php else: ?>
          <span class="pagination-link pagination-link-disabled">Next</span>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['action']) && ($_GET['action'] === 'add_course' || $_GET['action'] === 'edit_course')): ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const hiddenInput = document.getElementById('kebutuhan_aplikasi_hidden');
        const tagsContainer = document.getElementById('tags-container');
        const newTagInput = document.getElementById('new-tag-input');
        const addTagBtn = document.getElementById('add-tag-btn');
        if (!hiddenInput || !tagsContainer || !newTagInput || !addTagBtn) return;

        const updateHiddenInput = () => {
            const tags = [];
            tagsContainer.querySelectorAll('.tag-text').forEach(tagEl => {
                tags.push(tagEl.textContent);
            });
            hiddenInput.value = tags.join(', ');
        };
        const createTag = (text) => {
            const trimmedText = text.trim();
            if (!trimmedText) return;
            const tagEl = document.createElement('span');
            tagEl.className = 'tag';
            const textEl = document.createElement('span');
            textEl.className = 'tag-text';
            textEl.textContent = trimmedText;
            const removeEl = document.createElement('span');
            removeEl.className = 'tag-remove';
            removeEl.innerHTML = '&times;';
            removeEl.setAttribute('role', 'button');
            removeEl.setAttribute('aria-label', `Hapus tag ${trimmedText}`);
            removeEl.onclick = () => {
                tagEl.remove();
                updateHiddenInput();
            };
            tagEl.appendChild(textEl);
            tagEl.appendChild(removeEl);
            tagsContainer.appendChild(tagEl);
        };
        const addNewTag = () => {
            const newTags = newTagInput.value.split(',');
            newTags.forEach(tagText => {
                createTag(tagText);
            });
            newTagInput.value = '';
            updateHiddenInput();
            newTagInput.focus();
        };
        if (hiddenInput.value) {
            const initialTags = hiddenInput.value.split(',');
            initialTags.forEach(tagText => {
                createTag(tagText);
            });
        }
        addTagBtn.addEventListener('click', addNewTag);
        newTagInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                addNewTag();
            }
        });
    });
</script>
<?php endif; ?>
