<?php
// /admin/functions.php

define('DATA_FILE', '../data.json'); // Path ke file data.json Anda

function read_data() {
    if (!file_exists(DATA_FILE)) {
        die("Error: File data.json tidak ditemukan! Pastikan path-nya benar: " . DATA_FILE);
    }
    $json_string = file_get_contents(DATA_FILE);
    return json_decode($json_string, true);
}

function save_data($data) {
    // JSON_PRETTY_PRINT agar file json tetap rapi
    $json_string = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if (file_put_contents(DATA_FILE, $json_string)) {
        return true;
    }
    return false;
}
?>
