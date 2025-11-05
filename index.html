<?php
// /admin/index.php
session_start();
define('ADMIN_PASSWORD', 'admin123'); // <-- GANTI PASSWORD INI!
include_once 'functions.php';

// --- LOGIKA LOGIN & LOGOUT ---
$is_logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['loggedin'] = true;
        $is_logged_in = true;
        header('Location: index.php'); // Redirect agar bersih dari POST
        exit;
    } else {
        $login_error = "Password salah!";
    }
}

// Tentukan halaman mana yang akan dimuat
$page = $_GET['page'] ?? 'courses';
$page_file = '';

if ($is_logged_in) {
    switch ($page) {
        case 'staff':
            $page_file = 'manage_staff.php';
            break;
        case 'holidays':
            $page_file = 'manage_holidays.php';
            break;
        case 'courses':
        default:
            $page_file = 'manage_courses.php';
            break;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel - Jadwal PJR</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-200 min-h-screen">

<nav class="bg-slate-800 text-white shadow-md">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between h-16">
      <div class="flex items-center">
        <h1 class="text-xl font-bold">Admin Panel Jadwal</h1>
      </div>
      <?php if ($is_logged_in): ?>
      <div class="flex items-center space-x-4">
        <a href="?page=courses" class="px-3 py-2 rounded-md text-sm font-medium <?php echo ($page === 'courses') ? 'bg-slate-900' : 'hover:bg-slate-700'; ?>">
          Jadwal Mata Kuliah
        </a>
        <a href="?page=staff" class="px-3 py-2 rounded-md text-sm font-medium <?php echo ($page === 'staff') ? 'bg-slate-900' : 'hover:bg-slate-700'; ?>">
          Data PJR
        </a>
        <a href="?page=holidays" class="px-3 py-2 rounded-md text-sm font-medium <?php echo ($page === 'holidays') ? 'bg-slate-900' : 'hover:bg-slate-700'; ?>">
          Hari Libur
        </a>
        <a href="?action=logout" class="px-3 py-2 rounded-md text-sm font-medium bg-red-600 hover:bg-red-700">Logout</a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div id="toast-container" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50 transition-opacity duration-300 ease-out opacity-0 pointer-events-none">
    <div id="toast-success" class="hidden bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 text-center max-w-sm mx-auto transform transition-all duration-300 ease-out scale-95">
        <svg class="w-16 h-16 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <p id="toast-success-msg" class="mt-4 text-xl font-medium text-gray-900 dark:text-white">Data berhasil disimpan!</p>
    </div>
    <div id="toast-error" class="hidden bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 text-center max-w-sm mx-auto transform transition-all duration-300 ease-out scale-95">
        <svg class="w-16 h-16 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <p id="toast-error-msg" class="mt-4 text-xl font-medium text-gray-900 dark:text-white">Data berhasil dihapus.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
  <?php if (!$is_logged_in): ?>
    <div class="flex items-center justify-center min-h-[60vh]">
      <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-xl shadow-lg">
        <h2 class="text-2xl font-bold text-center text-gray-900">Silakan Login</h2>
        <form class="space-y-6" action="index.php" method="POST">
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <div class="mt-1">
              <input id="password" name="password" type="password" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
          </div>
          <?php if (isset($login_error)): ?>
            <p class="text-sm text-red-600"><?php echo $login_error; ?></p>
          <?php endif; ?>
          <div>
            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
              Login
            </button>
          </div>
        </form>
      </div>
    </div>
  <?php else: ?>
    <?php
      if ($page_file && file_exists($page_file)) {
          include $page_file;
      } else {
          echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">Error: File halaman tidak ditemukan.</div>';
      }
    ?>
  <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');

        if (status) {
            let container = document.getElementById('toast-container');
            let toastToShow = null;
            let toastMessage = '';

            if (status === 'saved') {
                toastToShow = document.getElementById('toast-success');
                toastMessage = 'Data berhasil disimpan!';
                document.getElementById('toast-success-msg').textContent = toastMessage;
            } else if (status === 'deleted') {
                toastToShow = document.getElementById('toast-error');
                toastMessage = 'Data berhasil dihapus.';
                document.getElementById('toast-error-msg').textContent = toastMessage;
            }

            if (toastToShow) {
                const toastContent = toastToShow.querySelector('.transform') || toastToShow;

                container.classList.remove('opacity-0', 'pointer-events-none');
                toastToShow.classList.remove('hidden');

                setTimeout(() => {
                    toastContent.classList.remove('scale-95');
                    toastContent.classList.add('scale-100');
                }, 50);

                setTimeout(() => {
                    container.classList.add('opacity-0');
                    toastContent.classList.add('scale-95');
                    toastContent.classList.remove('scale-100');
                }, 1500);

                setTimeout(() => {
                    container.classList.add('pointer-events-none');
                    toastToShow.classList.add('hidden');
                }, 1800);

                urlParams.delete('status');
                const newUrl = window.location.pathname + '?' + urlParams.toString();
                history.replaceState(null, '', newUrl);
            }
        }
    });
</script>

</body>
</html>
