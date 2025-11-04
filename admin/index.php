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
// Defaultnya adalah 'courses' (Jadwal Mata Kuliah)
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
<body class="bg-gray-100 min-h-screen">

<nav class="bg-blue-800 text-white shadow-md">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between h-16">
      <div class="flex items-center">
        <h1 class="text-xl font-bold">Admin Panel</h1>
      </div>
      <?php if ($is_logged_in): ?>
      <div class="flex items-center space-x-4">
        <a href="?page=courses" class="px-3 py-2 rounded-md text-sm font-medium <?php echo ($page === 'courses') ? 'bg-blue-900' : 'hover:bg-blue-700'; ?>">
          Jadwal Mata Kuliah
        </a>
        <a href="?page=staff" class="px-3 py-2 rounded-md text-sm font-medium <?php echo ($page === 'staff') ? 'bg-blue-900' : 'hover:bg-blue-700'; ?>">
          Data PJR
        </a>
        <a href="?page=holidays" class="px-3 py-2 rounded-md text-sm font-medium <?php echo ($page === 'holidays') ? 'bg-blue-900' : 'hover:bg-blue-700'; ?>">
          Hari Libur
        </a>
        <a href="?action=logout" class="px-3 py-2 rounded-md text-sm font-medium bg-red-600 hover:bg-red-700">Logout</a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</nav>

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
      // Memuat file halaman yang sesuai
      if ($page_file && file_exists($page_file)) {
          include $page_file;
      } else {
          echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">Error: File halaman tidak ditemukan.</div>';
      }
    ?>
  <?php endif; ?>
</div>

</body>
</html>
