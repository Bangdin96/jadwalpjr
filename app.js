// Variabel Global untuk data
let labData;
let staffDetails;
let holidays;
let specialSchedules;
let courseSchedule;
let schedulesByDayAndRoom = {}; // Untuk data yang sudah dioptimasi

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

// Fungsi helper untuk mendapatkan string tanggal YYYY-MM-DD lokal (Anti-UTC)
function getLocalDateString(date) {
    const year = date.getFullYear();
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const day = date.getDate().toString().padStart(2, '0');
    return `${year}-${month}-${day}`;
}


// --- FUNGSI LOGIKA TANGGAL ---

function isHoliday(date) {
    const dateStr = getLocalDateString(date); // Menggunakan helper baru
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

    const dateStr = getLocalDateString(date); // Menggunakan helper baru

    if (specialSchedules) {
        const special = specialSchedules.find(s => s.date === dateStr);
        if (special) { return pjrList[special.pjrIndex]; }
    }
    const workingDaySeq = getWorkingDaySequence(date);
    const pjrIndex = (workingDaySeq % pjrCount + pjrCount) % pjrCount;
    return pjrList[pjrIndex];
}

function createScheduleCard(labName, pjrName, isToday = false, date) {
    const cardClass = isToday ? 'bg-green-50 border-green-200 shadow-md' : 'bg-white border-gray-200';
    let photoPath = null;
    let initials = '?';
    let displayName = pjrName || 'Libur';
    let displayJabatan = 'Penanggung Jawab';
    if (pjrName && staffDetails[pjrName]) {
        const details = staffDetails[pjrName];
        photoPath = details.photo ? `images/${details.photo}` : null;
        initials = getInitials(pjrName);
        displayJabatan = details.jabatan || 'Staf';
    } else if (pjrName) {
        initials = getInitials(pjrName);
        displayJabatan = 'Staf';
    }

    const clickEvent = pjrName ? `showCourseSchedule('${labName}', '${date.toISOString()}')` : `event.stopPropagation()`;

    return `
          <div class="p-2 md:p-4 rounded-lg border-2 ${cardClass} fade-in hover:shadow-lg mobile-card ${pjrName ? 'schedule-card hover:scale-105' : ''}"
               onclick="${clickEvent}" ${pjrName ? 'role="button" tabindex="0"' : ''}>
              <div class="mb-2 md:mb-3">
                  <h3 class="font-bold text-gray-800 text-left text-xs md:text-sm leading-tight">${labName}</h3>
              </div>
              <div class="text-center">
                  <div class="photo-container">
                      ${photoPath ?
                          `<img src="${photoPath}" alt="${displayName}" loading="lazy" onload="this.style.opacity='1'" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" style="opacity:0;">
                          <div class="photo-fallback" style="display:none;">${initials}</div>`
                          :
                          `<div class="photo-fallback">${initials}</div>`
                      }
                  </div>
                  <p class="font-semibold text-xs md:text-sm text-gray-800 mb-1 leading-tight">${displayName}</p>
                  <p class="text-xs text-gray-600 leading-tight">${displayJabatan}</p>
              </div>
          </div>
      `;
}

function updateScheduleDisplay() {
    // Pastikan data sudah dimuat sebelum mencoba me-render
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
    const firstDayContainer = firstBadge.closest('.bg-white');
    const secondDayContainer = secondBadge.closest('.bg-white');

    firstDayContainer.className = 'bg-white rounded-xl shadow-lg p-4 md:p-6 border-l-4';
    secondDayContainer.className = 'bg-white rounded-xl shadow-lg p-4 md:p-6 border-l-4';

    const badgeClasses = "px-2 md:px-3 py-1 rounded-full text-xs md:text-sm sm:mr-3 self-start";

    if (isFirstDayToday) {
        firstBadge.textContent = 'HARI INI';
        firstBadge.className = `bg-green-100 text-green-800 ${badgeClasses}`;
        firstDayContainer.classList.add('border-green-500');
    } else if (isFirstDayTomorrow) {
        firstBadge.textContent = 'BESOK';
        firstBadge.className = `bg-yellow-100 text-yellow-800 ${badgeClasses}`;
        firstDayContainer.classList.add('border-yellow-500');
    } else if (isFirstDayDayAfterTomorrow) {
        firstBadge.textContent = 'BESOK LUSA';
        firstBadge.className = `bg-orange-100 text-orange-800 ${badgeClasses}`;
        firstDayContainer.classList.add('border-orange-500');
    } else if (firstDayNormalized < today) {
        firstBadge.textContent = 'SEBELUMNYA';
        firstBadge.className = `bg-gray-100 text-gray-800 ${badgeClasses}`;
        firstDayContainer.classList.add('border-gray-400');
    } else {
        firstBadge.textContent = 'AKAN DATANG';
        firstBadge.className = `bg-red-100 text-red-800 ${badgeClasses}`;
        firstDayContainer.classList.add('border-red-500');
    }

    if (isSecondDayToday) {
        secondBadge.textContent = 'HARI INI';
        secondBadge.className = `bg-green-100 text-green-800 ${badgeClasses}`;
        secondDayContainer.classList.add('border-green-500');
    } else if (isSecondDayTomorrow) {
        secondBadge.textContent = 'BESOK';
        secondBadge.className = `bg-yellow-100 text-yellow-800 ${badgeClasses}`;
        secondDayContainer.classList.add('border-yellow-500');
    } else if (isSecondDayDayAfterTomorrow) {
        secondBadge.textContent = 'BESOK LUSA';
        secondBadge.className = `bg-orange-100 text-orange-800 ${badgeClasses}`;
        secondDayContainer.classList.add('border-orange-500');
    } else {
        secondBadge.textContent = 'AKAN DATANG';
        secondBadge.className = `bg-red-100 text-red-800 ${badgeClasses}`;
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
            <p class="text-xl font-semibold text-gray-600">Hari Libur</p>
            <p class="text-gray-500">${reason}</p>
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
            <p class="text-xl font-semibold text-gray-600">Hari Libur</p>
            <p class="text-gray-500">${reason}</p>
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
            !document.getElementById('course-schedule-modal').classList.contains('opacity-0');

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
        let contentHTML = '<div class="space-y-3">';
        jadwalTersaring.forEach(item => {
            contentHTML += `
                <div class="p-3 bg-gray-50 rounded-lg border">
                    <p class="font-bold text-red-700">${item.MATA_KULIAH} (${item.KELAS || 'N/A'})</p>
                    <p class="text-sm text-gray-700">${item.DOSEN_PENGAMPU}</p>
                    <p class="text-sm text-gray-500 font-medium">${formatJam(item.JAM_MULAI)} - ${formatJam(item.JAM_SELESAI)}</p>
                </div>
            `;
        });
        contentHTML += '</div>';
        contentEl.innerHTML = contentHTML;
    } else {
        contentEl.innerHTML = `
            <div class="text-center py-10">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <p class="text-gray-500 font-medium">Tidak ada jadwal mata kuliah di ruangan ini pada hari ${NAMA_HARI[date.getDay()]}.</p>
            </div>
        `;
    }
    showCourseModal();
}

// --- INISIALISASI APLIKASI ---

document.addEventListener('DOMContentLoaded', async function() {
    try {
        // PERBAIKAN: Hapus cache busting ?v=... agar Service Worker bisa mengambil alih
        const response = await fetch('data.json');

        if (!response.ok) {
            throw new Error(`Gagal memuat data: ${response.statusText}`);
        }
        const data = await response.json();

        // 1. Muat semua data ke variabel global
        labData = data.labs;
        staffDetails = data.staffDetails;
        holidays = data.holidays;
        specialSchedules = data.specialSchedules;
        courseSchedule = data.courseSchedule || [];

        // 2. Optimasi: Proses data jadwal kuliah
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

        // 3. Tampilkan modal notifikasi awal
        showModal('notification-modal');

        // 4. Atur tanggal dan muat jadwal PJR
        currentViewDate = new Date();
        currentViewDate.setHours(0, 0, 0, 0);

        updateScheduleDisplay();

        // 5. Atur semua event listener
        document.getElementById('prev-btn').addEventListener('click', goToPreviousDay);
        document.getElementById('next-btn').addEventListener('click', goToNextDay);
        document.getElementById('today-btn').addEventListener('click', goToToday);

        document.getElementById('modal-close-btn').addEventListener('click', () => hideModal('notification-modal'));
        document.getElementById('limit-modal-close-btn').addEventListener('click', hideLimitModal);
        document.getElementById('prev-limit-modal-close-btn').addEventListener('click', hidePrevLimitModal);
        document.getElementById('course-modal-close-btn').addEventListener('click', hideCourseModal);
        document.getElementById('course-modal-close-btn-x').addEventListener('click', hideCourseModal);

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                hideModal('notification-modal');
                hideLimitModal();
                hidePrevLimitModal();
                hideCourseModal();
            }
        });

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

    } catch (error) {
        console.error('Error memuat data jadwal:', error);
        document.getElementById('loading-spinner').innerHTML =
            `<p class="text-red-600 font-bold">Error: Gagal memuat data jadwal.</p>
             <p class="text-gray-600">Pastikan file 'data.json' ada di folder yang sama.</p>`;
    }
});
