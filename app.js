// Variabel Global untuk data
let labData;
let staffDetails;
let holidays;
let specialSchedules;
let courseSchedule;
let schedulesByDayAndRoom = {};

let currentViewDate = new Date();

// Konstanta
const rotationStartDate = new Date('2025-11-03');
rotationStartDate.setHours(0, 0, 0, 0);
const NAMA_HARI = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

// --- FUNGSI UTILITAS ---

function getInitials(name) {
    if (!name) return '?';
    const words = name.split(' ');
    if (words.length >= 2) {
        return (words[0].charAt(0) + words[1].charAt(0)).toUpperCase();
    }
    return name.charAt(0).toUpperCase();
}

function formatDate(date) {
    const days = NAMA_HARI;
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return `${days[date.getDay()]}, ${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
}

function formatJam(jamStr) {
    if (!jamStr) return '';
    const parts = jamStr.split(':');
    const hour = parts[0].padStart(2, '0');
    const minute = parts[1].padStart(2, '0');
    return `${hour}:${minute}`;
}

function getLocalDateString(date) {
    const year = date.getFullYear();
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const day = date.getDate().toString().padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function createTagHTML(appString) {
    if (!appString || appString.trim() === '') {
        return '<p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada info aplikasi.</p>';
    }
    const apps = appString.split(',').map(app => app.trim());

    let html = '<div class="flex flex-wrap gap-1 mt-2">';
    apps.forEach(app => {
        if (app) {
            html += `<span class="app-tag">${app}</span>`;
        }
    });
    html += '</div>';
    return html;
}


// --- FUNGSI LOGIKA TANGGAL ---

function isHoliday(date) {
    const dateStr = getLocalDateString(date);
    return holidays && holidays.some(holiday => holiday.date === dateStr);
}

function isWeekend(date) {
    const day = date.getDay();
    return day === 0 || day === 6;
}

function isWorkingDay(date) {
    return !isWeekend(date) && !isHoliday(date);
}

function getWorkingDaySequence(targetDate) {
    const target = new Date(targetDate);
    target.setHours(0, 0, 0, 0);
    const startDate = new Date(rotationStartDate);
    if (target.getTime() === startDate.getTime()) { return 0; }
    if (target.getTime() < startDate.getTime()) {
        let workingDayCount = 0;
        let currentDate = new Date(startDate);
        while (currentDate > target) {
            currentDate.setDate(currentDate.getDate() - 1);
            if (isWorkingDay(currentDate)) { workingDayCount--; }
        }
        return workingDayCount;
    }
    let workingDayCount = 0;
    let currentDate = new Date(startDate);
    while (currentDate < target) {
        if (isWorkingDay(currentDate)) { workingDayCount++; }
        currentDate.setDate(currentDate.getDate() + 1);
    }
    if (isWorkingDay(target)) {
         return workingDayCount;
    } else {
         let lastWorkingDayCount = workingDayCount;
         let tempDate = new Date(target);
         while (!isWorkingDay(tempDate) && tempDate >= startDate) {
             tempDate.setDate(tempDate.getDate() - 1);
             if (isWorkingDay(tempDate)) { lastWorkingDayCount--; }
         }
         return lastWorkingDayCount;
    }
}

// --- FUNGSI INTI APLIKASI ---

function getPJRForDate(labName, date) {
    if (!isWorkingDay(date)) return null;
    const pjrList = labData[labName];
    const pjrCount = pjrList.length;
    const target = new Date(date);
    target.setHours(0, 0, 0, 0);

    const dateStr = getLocalDateString(date);

    if (specialSchedules) {
        const special = specialSchedules.find(s => s.date === dateStr);
        if (special) { return pjrList[special.pjrIndex]; }
    }
    const workingDaySeq = getWorkingDaySequence(date);
    const pjrIndex = (workingDaySeq % pjrCount + pjrCount) % pjrCount;
    return pjrList[pjrIndex];
}

function createScheduleCard(labName, pjrName, isToday = false, date) {
    const cardClass = isToday
        ? 'bg-green-50 border-green-200 shadow-md dark:bg-green-900 dark:border-green-700'
        : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700';

    let photoPath = null;
    let initials = '?';
    let displayName = pjrName || 'Libur';
    let displayJabatan = 'Penanggung Jawab';
    if (pjrName && staffDetails[pjrName]) {
        const details = staffDetails[pjrName];
        // === PERUBAHAN DI SINI ===
        // Menghapus ?v=${new Date().getTime()} agar gambar di-cache
        photoPath = details.photo ? `images/${details.photo}` : null;
        // === AKHIR PERUBAHAN ===
        initials = getInitials(pjrName);
        displayJabatan = details.jabatan || 'Staf';
    } else if (pjrName) {
        initials = getInitials(pjrName);
        displayJabatan = 'Staf';
    }

    const clickEvent = pjrName ? `showCourseSchedule('${labName}', '${date.toISOString()}')` : `event.stopPropagation()`;

    return `
          <div class="p-2 md:p-4 rounded-lg border-2 ${cardClass} fade-in hover:shadow-lg mobile-card ${pjrName ? 'schedule-card hover:scale-105 hover:border-red-500' : ''}"
               onclick="${clickEvent}" ${pjrName ? 'role="button" tabindex="0"' : ''}>
              <div class="mb-2 md:mb-3">
                  <h3 class="font-bold text-gray-800 dark:text-white text-left text-xs md:text-sm leading-tight">${labName}</h3>
              </div>
              <div class="text-center">
                  <div class="photo-container dark:border-gray-600 dark:bg-gray-700">
                      ${photoPath ?
                          `<img src="${photoPath}" alt="${displayName}" loading="lazy" onload="this.style.opacity='1'" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" style="opacity:0;">
                          <div class="photo-fallback" style="display:none;">${initials}</div>`
                          :
                          `<div class="photo-fallback dark:bg-gray-600 dark:text-gray-300">${initials}</div>`
                      }
                  </div>
                  <p class="font-semibold text-xs md:text-sm text-gray-800 dark:text-white mb-1 leading-tight">${displayName}</p>
                  <p class="text-xs text-gray-600 dark:text-gray-400 leading-tight">${displayJabatan}</p>
              </div>
          </div>
      `;
}

function updateScheduleDisplay() {
    if (!labData) return;

    document.getElementById('schedule-view').classList.remove('hidden');
    document.getElementById('loading-spinner').classList.add('hidden');

    const firstDay = new Date(currentViewDate);
    const secondDay = new Date(currentViewDate);
    secondDay.setDate(secondDay.getDate() + 1);

    const today = new Date();
    today.setHours(0,0,0,0);

    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);

    const dayAfterTomorrow = new Date(today);
    dayAfterTomorrow.setDate(dayAfterTomorrow.getDate() + 2);

    const firstDayNormalized = new Date(firstDay);
    firstDayNormalized.setHours(0,0,0,0);

    const secondDayNormalized = new Date(secondDay);
    secondDayNormalized.setHours(0,0,0,0);

    const isFirstDayToday = firstDayNormalized.getTime() === today.getTime();
    const isFirstDayTomorrow = firstDayNormalized.getTime() === tomorrow.getTime();
    const isFirstDayDayAfterTomorrow = firstDayNormalized.getTime() === dayAfterTomorrow.getTime();

    const isSecondDayToday = secondDayNormalized.getTime() === today.getTime();
    const isSecondDayTomorrow = secondDayNormalized.getTime() === tomorrow.getTime();
    const isSecondDayDayAfterTomorrow = secondDayNormalized.getTime() === dayAfterTomorrow.getTime();

    document.getElementById('date-range').textContent =
        `${formatDate(firstDay)} - ${formatDate(secondDay)}`;
    document.getElementById('first-day-date').textContent = formatDate(firstDay);
    document.getElementById('second-day-date').textContent = formatDate(secondDay);

    const firstBadge = document.getElementById('first-day-badge');
    const secondBadge = document.getElementById('second-day-badge');
    const firstDayContainer = firstBadge.closest('.bg-white, .dark\\:bg-gray-800');
    const secondDayContainer = secondBadge.closest('.bg-white, .dark\\:bg-gray-800');


    firstDayContainer.className = 'bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 md:p-6 border-l-4';
    secondDayContainer.className = 'bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 md:p-6 border-l-4';

    const badgeClasses = "px-2 md:px-3 py-1 rounded-full text-xs md:text-sm sm:mr-3 self-start";

    if (isFirstDayToday) {
        firstBadge.textContent = 'HARI INI';
        firstBadge.className = `bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 ${badgeClasses}`;
        firstDayContainer.classList.add('border-green-500');
    } else if (isFirstDayTomorrow) {
        firstBadge.textContent = 'BESOK';
        firstBadge.className = `bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 ${badgeClasses}`;
        firstDayContainer.classList.add('border-yellow-500');
    } else if (isFirstDayDayAfterTomorrow) {
        firstBadge.textContent = 'BESOK LUSA';
        firstBadge.className = `bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200 ${badgeClasses}`;
        firstDayContainer.classList.add('border-orange-500');
    } else if (firstDayNormalized < today) {
        firstBadge.textContent = 'SEBELUMNYA';
        firstBadge.className = `bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 ${badgeClasses}`;
        firstDayContainer.classList.add('border-gray-400');
    } else {
        firstBadge.textContent = 'AKAN DATANG';
        firstBadge.className = `bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 ${badgeClasses}`;
        firstDayContainer.classList.add('border-red-500');
    }

    if (isSecondDayToday) {
        secondBadge.textContent = 'HARI INI';
        secondBadge.className = `bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 ${badgeClasses}`;
        secondDayContainer.classList.add('border-green-500');
    } else if (isSecondDayTomorrow) {
        secondBadge.textContent = 'BESOK';
        secondBadge.className = `bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 ${badgeClasses}`;
        secondDayContainer.classList.add('border-yellow-500');
    } else if (isSecondDayDayAfterTomorrow) {
        secondBadge.textContent = 'BESOK LUSA';
        secondBadge.className = `bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200 ${badgeClasses}`;
        secondDayContainer.classList.add('border-orange-500');
    } else {
        secondBadge.textContent = 'AKAN DATANG';
        secondBadge.className = `bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 ${badgeClasses}`;
        secondDayContainer.classList.add('border-red-500');
    }

    const firstContainer = document.getElementById('first-day-schedule');
    let firstHTML = '';
    if (isWorkingDay(firstDay)) {
        Object.keys(labData).forEach(labName => {
            const pjr = getPJRForDate(labName, firstDay);
            firstHTML += createScheduleCard(labName, pjr, isFirstDayToday, firstDay);
        });
    } else {
        const reason = getHolidayReason(firstDay);
        firstHTML = `<div class="col-span-full text-center py-8">
            <div class="text-6xl mb-4">🏖️</div>
            <p class="text-xl font-semibold text-gray-600 dark:text-gray-300">Hari Libur</p>
            <p class="text-gray-500 dark:text-gray-400">${reason}</p>
        </div>`;
    }
    firstContainer.innerHTML = firstHTML;

    const secondContainer = document.getElementById('second-day-schedule');
    let secondHTML = '';
    if (isWorkingDay(secondDay)) {
        Object.keys(labData).forEach(labName => {
            const pjr = getPJRForDate(labName, secondDay);
            secondHTML += createScheduleCard(labName, pjr, isSecondDayToday, secondDay);
        });
    } else {
        const reason = getHolidayReason(secondDay);
        secondHTML = `<div class="col-span-full text-center py-8">
            <div class="text-6xl mb-4">🏖️</div>
            <p class="text-xl font-semibold text-gray-600 dark:text-gray-300">Hari Libur</p>
            <p class="text-gray-500 dark:text-gray-400">${reason}</p>
        </div>`;
    }
    secondContainer.innerHTML = secondHTML;
}

function getHolidayReason(date) {
    if (isWeekend(date)) return 'Akhir pekan';
    const dateStr = getLocalDateString(date);
    const holiday = holidays.find(h => h.date === dateStr);
    return holiday ? holiday.reason : 'Tidak ada jadwal PJR';
}

// --- FUNGSI NAVIGASI ---

function goToPreviousDay() {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const currentViewNormalized = new Date(currentViewDate);
    currentViewNormalized.setHours(0, 0, 0, 0);
    if (currentViewNormalized.getTime() <= today.getTime()) {
        showPrevLimitModal();
    } else {
        currentViewDate.setDate(currentViewDate.getDate() - 1);
        updateScheduleDisplay();
    }
}

function goToNextDay() {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const limitDate = new Date(today);
    limitDate.setDate(limitDate.getDate() + 2);
    const nextDay = new Date(currentViewDate);
    nextDay.setDate(nextDay.getDate() + 1);
    nextDay.setHours(0, 0, 0, 0);
    if (nextDay.getTime() > limitDate.getTime()) {
        showLimitModal();
    } else {
        currentViewDate.setDate(currentViewDate.getDate() + 1);
        updateScheduleDisplay();
    }
}

function goToToday() {
    currentViewDate = new Date();
    currentViewDate.setHours(0, 0, 0, 0);
    updateScheduleDisplay();
}

// --- FUNGSI MODAL ---

function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.classList.remove('opacity-0', 'pointer-events-none');
    requestAnimationFrame(() => {
        modal.querySelector('.transform').classList.remove('scale-95');
    });
    document.body.style.overflow = 'hidden';
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.classList.add('opacity-0');
    modal.querySelector('.transform').classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('pointer-events-none');
        const anyModalOpen =
            !document.getElementById('notification-modal').classList.contains('opacity-0') ||
            !document.getElementById('limit-alert-modal').classList.contains('opacity-0') ||
            !document.getElementById('prev-limit-modal').classList.contains('opacity-0') ||
            !document.getElementById('course-schedule-modal').classList.contains('opacity-0') ||
            !document.getElementById('search-modal').classList.contains('opacity-0');

        if (!anyModalOpen) {
            document.body.style.overflow = '';
        }
    }, 300);
}

function showLimitModal() { showModal('limit-alert-modal'); }
function hideLimitModal() { hideModal('limit-alert-modal'); }
function showPrevLimitModal() { showModal('prev-limit-modal'); }
function hidePrevLimitModal() { hideModal('prev-limit-modal'); }
function showCourseModal() { showModal('course-schedule-modal'); }
function hideCourseModal() { hideModal('course-schedule-modal'); }

// === PERUBAHAN: Fungsi Modal Pencarian ===
function showSearchModal() {
    showModal('search-modal');
    // Pindahkan fokus ke input di dalam modal
    document.getElementById('search-modal-query-input').focus();
}
function hideSearchModal() {
    hideModal('search-modal');
    // Pindahkan fokus kembali ke input utama
    document.getElementById('search-input').focus();
}
// === AKHIR PERUBAHAN ===


function showCourseSchedule(labName, isoDate) {
    const date = new Date(isoDate);
    const namaHari = NAMA_HARI[date.getDay()].toLowerCase();

    const jadwalTersaring = schedulesByDayAndRoom[namaHari][labName.trim()] || [];

    const titleEl = document.getElementById('modal-lab-title');
    const dateEl = document.getElementById('modal-lab-date');
    const contentEl = document.getElementById('modal-lab-content');

    titleEl.textContent = `Jadwal Ruangan ${labName}`;
    dateEl.textContent = formatDate(date);

    if (jadwalTersaring.length > 0) {
        let contentHTML = '<div class="space-y-4">';
        jadwalTersaring.forEach(item => {
            contentHTML += `
                <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border dark:border-gray-700">
                    <p class="font-bold text-red-700 dark:text-red-500">${item.MATA_KULIAH} (${item.KELAS || 'N/A'})</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">${item.DOSEN_PENGAMPU}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">${formatJam(item.JAM_MULAI)} - ${formatJam(item.JAM_SELESAI)}</p>
                    ${createTagHTML(item.KEBUTUHAN_APLIKASI)}
                </div>
            `;
        });
        contentHTML += '</div>';
        contentEl.innerHTML = contentHTML;
    } else {
        contentEl.innerHTML = `
            <div class="text-center py-10">
                <svg class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <p class="text-gray-500 dark:text-gray-400 font-medium">Tidak ada jadwal mata kuliah di ruangan ini pada hari ${NAMA_HARI[date.getDay()]}.</p>
            </div>
        `;
    }
    showCourseModal();
}

// === PERUBAHAN: Logika Pencarian Diperbarui ===

// Fungsi ini HANYA me-render hasil, tidak lebih
function displaySearchResults(results) {
    const contentEl = document.getElementById('search-modal-content');

    if (results.length === 0) {
        contentEl.innerHTML = `
            <div class="text-center py-10">
                <svg class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 01-5.656-5.656l3.535-3.536a4 4 0 015.656 0l-3.535 3.536m-3.536 3.536l3.535 3.536a4 4 0 010 5.656l-3.535-3.536a4 4 0 010-5.656z"></path></svg>
                <p class="text-gray-500 dark:text-gray-400 font-medium">Tidak ada jadwal ditemukan.</p>
            </div>
        `;
        return;
    }

    const groupedResults = {};
    results.forEach(item => {
        const hari = item.HARI.toUpperCase();
        if (!groupedResults[hari]) {
            groupedResults[hari] = [];
        }
        groupedResults[hari].push(item);
    });

    let contentHTML = '<div class="space-y-6">';
    const sortedDays = Object.keys(groupedResults).sort((a, b) => {
        return NAMA_HARI.indexOf(a) - NAMA_HARI.indexOf(b);
    });

    sortedDays.forEach(hari => {
        contentHTML += `<div><h3 class="search-result-group">${hari}</h3><div class="space-y-3 mt-3">`;

        groupedResults[hari].forEach(item => {
            contentHTML += `
                <div class="search-result-item">
                    <p class="font-bold text-red-700 dark:text-red-500">${item.MATA_KULIAH} (${item.KELAS || 'N/A'})</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300"><span class="font-medium">Dosen:</span> ${item.DOSEN_PENGAMPU}</p>
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-sm text-gray-500 dark:text-gray-400 font-medium">${formatJam(item.JAM_MULAI)} - ${formatJam(item.JAM_SELESAI)}</span>
                        <span class="font-semibold text-green-600 dark:text-green-500">${item.RUANG}</span>
                    </div>
                </div>
            `;
        });

        contentHTML += `</div></div>`;
    });

    contentHTML += '</div>';
    contentEl.innerHTML = contentHTML;
}

// Fungsi ini menangani logika pencarian
function handleSearch(query) {
    const normalizedQuery = query.toLowerCase().trim();

    if (normalizedQuery.length < 3) {
        hideModal('search-modal'); // Sembunyikan jika query terlalu pendek
        return;
    }

    if (!courseSchedule) return; // Data belum siap

    const results = courseSchedule.filter(item => {
        return (item.MATA_KULIAH?.toLowerCase() || '').includes(normalizedQuery) ||
               (item.DOSEN_PENGAMPU?.toLowerCase() || '').includes(normalizedQuery) ||
               (item.KELAS?.toLowerCase() || '').includes(normalizedQuery);
    });

    displaySearchResults(results);
    showSearchModal();
}

// Fungsi ini menyinkronkan kedua input
function syncSearchInputs(event) {
    const query = event.target.value;

    // Update input yang *lain*
    if (event.target.id === 'search-input') {
        document.getElementById('search-modal-query-input').value = query;
    } else {
        document.getElementById('search-input').value = query;
    }

    // Jalankan pencarian
    handleSearch(query);
}
// === AKHIR LOGIKA PENCARIAN ===


// --- INISIALISASI APLIKASI ---

document.addEventListener('DOMContentLoaded', async function() {
    try {
        // === PERUBAHAN DI SINI ===
        // Menghapus ?v=... agar service-worker bisa meng-cache
        const response = await fetch('data.json');
        // === AKHIR PERUBAHAN ===

        if (!response.ok) {
            throw new Error(`Gagal memuat data: ${response.statusText}`);
        }
        const data = await response.json();

        labData = data.labs;
        staffDetails = data.staffDetails;
        holidays = data.holidays;
        specialSchedules = data.specialSchedules;
        courseSchedule = data.courseSchedule || [];

        NAMA_HARI.forEach(hari => {
            schedulesByDayAndRoom[hari.toLowerCase()] = {};
        });
        courseSchedule.forEach(item => {
            const hari = item.HARI ? item.HARI.toLowerCase() : '';
            const ruang = item.RUANG ? item.RUANG.trim() : '';
            if (!hari || !ruang) return;
            if (!schedulesByDayAndRoom[hari]) {
                schedulesByDayAndRoom[hari] = {};
            }
            if (!schedulesByDayAndRoom[hari][ruang]) {
                schedulesByDayAndRoom[hari][ruang] = [];
            }
            schedulesByDayAndRoom[hari][ruang].push(item);
        });
        for (const hari in schedulesByDayAndRoom) {
            for (const ruang in schedulesByDayAndRoom[hari]) {
                schedulesByDayAndRoom[hari][ruang].sort((a, b) => {
                    return formatJam(a.JAM_MULAI).localeCompare(formatJam(b.JAM_MULAI));
                });
            }
        }

        showModal('notification-modal');

        currentViewDate = new Date();
        currentViewDate.setHours(0, 0, 0, 0);

        updateScheduleDisplay();

        // === EVENT LISTENERS ===
        document.getElementById('prev-btn').addEventListener('click', goToPreviousDay);
        document.getElementById('next-btn').addEventListener('click', goToNextDay);
        document.getElementById('today-btn').addEventListener('click', goToToday);

        // Modal Listeners
        document.getElementById('modal-close-btn').addEventListener('click', () => hideModal('notification-modal'));
        document.getElementById('limit-modal-close-btn').addEventListener('click', hideLimitModal);
        document.getElementById('prev-limit-modal-close-btn').addEventListener('click', hidePrevLimitModal);
        document.getElementById('course-modal-close-btn').addEventListener('click', hideCourseModal);
        document.getElementById('course-modal-close-btn-x').addEventListener('click', hideCourseModal);
        document.getElementById('search-modal-close-btn').addEventListener('click', hideSearchModal);
        document.getElementById('search-modal-close-btn-x').addEventListener('click', hideSearchModal);

        // === PERUBAHAN: Listener untuk kedua input pencarian ===
        document.getElementById('search-input').addEventListener('input', syncSearchInputs);
        document.getElementById('search-modal-query-input').addEventListener('input', syncSearchInputs);

        // Listener untuk menutup modal saat input utama dikosongkan
        document.getElementById('search-input').addEventListener('search', (e) => {
            if (e.target.value === '') {
                hideSearchModal();
            }
        });
        // === AKHIR PERUBAHAN ===

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                hideModal('notification-modal');
                hideLimitModal();
                hidePrevLimitModal();
                hideCourseModal();
                hideSearchModal();
            }
        });

        // FAB Listeners
        const fabButton = document.getElementById('fab-button');
        const fabPopup = document.getElementById('fab-popup');
        const fabCloseBtn = document.getElementById('fab-close-btn');

        if (fabButton) {
            fabButton.addEventListener('click', () => {
                fabPopup.classList.toggle('opacity-0');
                fabPopup.classList.toggle('scale-95');
                fabPopup.classList.toggle('opacity-100');
                fabPopup.classList.toggle('scale-100');
                fabPopup.classList.toggle('pointer-events-none');
            });
        }

        if (fabCloseBtn) {
            fabCloseBtn.addEventListener('click', () => {
                fabPopup.classList.add('opacity-0');
                fabPopup.classList.add('scale-95');
                fabPopup.classList.remove('opacity-100');
                fabPopup.classList.remove('scale-100');
                fabPopup.classList.add('pointer-events-none');
            });
        }

        // Dark Mode Logic (Default Light)
        const toggleBtn = document.getElementById('dark-mode-toggle');
        const sunIcon = document.getElementById('sun-icon');
        const moonIcon = document.getElementById('moon-icon');

        const setDarkMode = (isDark) => {
            if (isDark) {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
                sunIcon.classList.remove('hidden');
                moonIcon.classList.add('hidden');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
                sunIcon.classList.add('hidden');
                moonIcon.classList.remove('hidden');
            }
        };

        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            setDarkMode(true);
        } else {
            setDarkMode(false);
        }

        toggleBtn.addEventListener('click', () => {
            setDarkMode(!document.documentElement.classList.contains('dark'));
        });

    } catch (error) {
        console.error('Error memuat data jadwal:', error);
        document.getElementById('loading-spinner').innerHTML =
            `<p class="text-red-600 font-bold">Error: Gagal memuat data jadwal.</p>
             <p class="text-gray-600">Pastikan file 'data.json' ada di folder yang sama.</p>`;
    }
});
