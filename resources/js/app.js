import './bootstrap';
import "jsvectormap/dist/jsvectormap.css";
import "flatpickr/dist/flatpickr.css";
import "dropzone/dist/dropzone.css";
import "../css/app.css";
import ApexCharts from "apexcharts";

import flatpickr from "flatpickr";
import Dropzone from "dropzone";

import "./components/calendar-init.js";
import "./components/image-resize";


/**
 * =================================================================
 * FUNGSI UTAMA: Inisialisasi Datepicker yang Dapat Digunakan Ulang
 * =================================================================
 * @param {string} elementId - ID dari elemen input datepicker.
 * @param {string} eventName - Nama event Livewire yang akan di-dispatch.
 * @param {Date|null} defaultDate - Tanggal default yang akan ditampilkan.
 */
const initDatepicker = (elementId, eventName, defaultDate = null) => {
    const datepickerEl = document.querySelector(elementId);
    if (!datepickerEl) return;

    // Hancurkan instance sebelumnya untuk mencegah duplikasi saat navigasi Livewire
    if (datepickerEl._flatpickr) {
        datepickerEl._flatpickr.destroy();
    }

    flatpickr(datepickerEl, {
        mode: "range",
        static: true,
        monthSelectorType: "static",
        dateFormat: "M j, Y",
        defaultDate: defaultDate || new Date(), // Gunakan tanggal yang diberikan atau hari ini
        conjunction: " to ", // SECARA EKSPLISIT tentukan pemisah agar konsisten
        onClose: (selectedDates, dateStr, instance) => {
            Livewire.dispatch(eventName, { date: dateStr });
        },
    });
};

// Init Dropzone
const dropzoneArea = document.querySelectorAll("#demo-upload");

if (dropzoneArea.length) {
    let myDropzone = new Dropzone("#demo-upload", { url: "/file/post" });
}

window.dashboardCharts = {};

window.initTripChart = (initialData) => {
    const tripChartEl = document.querySelector('#tripStatusBarChart');
    if (!tripChartEl) return;
    const options = {
        series: [{ name: 'Jumlah Trip', data: initialData.data || [] }],
        chart: { type: 'bar', height: 350, toolbar: { show: false } },
        plotOptions: { bar: { columnWidth: '55%', borderRadius: 4 } },
        dataLabels: { enabled: false },
        xaxis: { categories: initialData.labels || [] },
        colors: ["#3C50E0"],
    };
    const chart = new ApexCharts(tripChartEl, options);
    chart.render();
    window.dashboardCharts.tripChart = chart;
};

window.updateTripChart = (newData) => {
    if (window.dashboardCharts.tripChart) {
        window.dashboardCharts.tripChart.updateOptions({
            series: [{ data: newData.data || [] }],
            xaxis: { categories: newData.labels || [] }
        });
    }
};

const initDonutChart = () => {
    const roleChartEl = document.querySelector('#userRoleDonutChart');
    if (roleChartEl && roleChartEl.dataset.series && roleChartEl.dataset.labels) {
        const roleSeries = JSON.parse(roleChartEl.dataset.series);
        const roleLabels = JSON.parse(roleChartEl.dataset.labels);

        const userRoleDonutChartOptions = {
            series: roleSeries,
            chart: { type: 'donut', height: 380 },
            labels: roleLabels,
            colors: ["#3C50E0", "#6577F3", "#80CAEE"],
            legend: { show: true, position: 'bottom' },
            dataLabels: {
                enabled: true,
                dropShadow: {
                    enabled: false,
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                fontSize: '18px',
                                fontWeight: 600,
                            },
                            value: {
                                show: true,
                                fontSize: '24px',
                                fontWeight: 800,
                            }
                        }
                    }
                }
            },
            responsive: [{
                breakpoint: 640,
                options: {
                    chart: { width: 250 },
                    legend: { position: 'bottom' }
                }
            }]
        };
        if (roleChartEl.innerHTML) {
            roleChartEl.innerHTML = '';
        }
        const roleChart = new ApexCharts(roleChartEl, userRoleDonutChartOptions);
        roleChart.render();
    }
};

const initializeComponents = () => {
    initDatepicker('#trip-chart-datepicker', 'trip-date-updated', new Date());
    initDatepicker('#attendance-datepicker', 'date-updated', new Date());
    initDonutChart();
};

document.addEventListener("DOMContentLoaded", () => {
    initializeComponents();
});

document.addEventListener('livewire:navigated', () => {
    setTimeout(() => {
        initializeComponents();
    }, 50);
});



// Get the current year
const year = document.getElementById("year");
if (year) {
    year.textContent = new Date().getFullYear();
}

// For Copy//
document.addEventListener("DOMContentLoaded", () => {
    const copyInput = document.getElementById("copy-input");
    if (copyInput) {
        // Select the copy button and input field
        const copyButton = document.getElementById("copy-button");
        const copyText = document.getElementById("copy-text");
        const websiteInput = document.getElementById("website-input");

        // Event listener for the copy button
        copyButton.addEventListener("click", () => {
            // Copy the input value to the clipboard
            navigator.clipboard.writeText(websiteInput.value).then(() => {
                // Change the text to "Copied"
                copyText.textContent = "Copied";

                // Reset the text back to "Copy" after 2 seconds
                setTimeout(() => {
                    copyText.textContent = "Copy";
                }, 2000);
            });
        });
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("search-input");
    const searchButton = document.getElementById("search-button");

    // Function to focus the search input
    function focusSearchInput() {
        searchInput.focus();
    }

    // Add click event listener to the search button
    searchButton.addEventListener("click", focusSearchInput);

    // Add keyboard event listener for Cmd+K (Mac) or Ctrl+K (Windows/Linux)
    document.addEventListener("keydown", function (event) {
        if ((event.metaKey || event.ctrlKey) && event.key === "k") {
            event.preventDefault(); // Prevent the default browser behavior
            focusSearchInput();
        }
    });

    // Add keyboard event listener for "/" key
    document.addEventListener("keydown", function (event) {
        if (event.key === "/" && document.activeElement !== searchInput) {
            event.preventDefault(); // Prevent the "/" character from being typed
            focusSearchInput();
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    // Pastikan kode ini hanya berjalan jika pengguna sudah login
    // Anda bisa menambahkan pengecekan elemen yang hanya ada jika user login, jika perlu
    if ('serviceWorker' in navigator && 'PushManager' in window) {
        navigator.serviceWorker.register('/service-worker.js').then(function (swReg) {
            console.log('Service Worker is registered', swReg);
            askForNotificationPermission(swReg);
        }).catch(function (error) {
            console.error('Service Worker Error', error);
        });
    }
});

function askForNotificationPermission(swReg) {
    Notification.requestPermission().then(function (result) {
        if (result === 'granted') {
            console.log('Notification permission granted.');
            subscribeUser(swReg);
        }
    });
}

function subscribeUser(swReg) {
    const vapidPublicKeyElement = document.querySelector('meta[name="vapid-public-key"]');

    if (!vapidPublicKeyElement) {
        console.log('VAPID public key meta tag not found. Skipping subscription.');
        return;
    }
    const vapidPublicKey = vapidPublicKeyElement.content;
    const applicationServerKey = urlBase64ToUint8Array(vapidPublicKey);

    swReg.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: applicationServerKey
    }).then(function(subscription) {
        console.log('User is subscribed:', subscription);
        sendSubscriptionToServer(subscription);
    }).catch(function(err) {
        console.log('Failed to subscribe the user: ', err);
    });
}

function sendSubscriptionToServer(subscription) {
    // Gunakan Axios yang sudah Anda setup di bootstrap.js
    window.axios.post('/push-subscribe', subscription)
        .then(function (response) {
            if (response.status === 200) {
                console.log('Push subscription saved.');
            }
        });
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

window.notificationMenu = function() {
    return {
        dropdownOpen: false,
        unread: [],
        read: [],
        unreadCount: 0,
        rootElement: document.querySelector('#notification-bell'),

        // Inisialisasi komponen
        init() {
            if (!this.rootElement) return;
            this.fetchNotifications();
        },

        // Mengambil data notifikasi dari server
        fetchNotifications() {
            const indexUrl = this.rootElement.dataset.indexUrl;
            axios.get(indexUrl)
                .then(response => {
                    this.unread = response.data.unread;
                    this.read = response.data.read;
                    this.unreadCount = response.data.unread_count;
                })
                .catch(error => console.error('Gagal mengambil notifikasi:', error));
        },

        // Membuka menu dan menandai notifikasi sebagai sudah dibaca
        openMenu() {
            this.dropdownOpen = !this.dropdownOpen;
            if (this.dropdownOpen && this.unreadCount > 0) {
                this.markAsRead();
            }
        },

        // Mengirim request untuk menandai notifikasi
        markAsRead() {
            const markAsReadUrl = this.rootElement.dataset.markAsReadUrl;
            axios.post(markAsReadUrl)
                .then(response => {
                    if (response.status === 200) {
                        this.unreadCount = 0; // Hilangkan indikator notif baru
                    }
                })
                .catch(error => console.error('Gagal menandai notifikasi:', error));
        },

        // Memformat tanggal agar mudah dibaca
        formatDate(dateString) {
            const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
            return new Date(dateString).toLocaleDateString('id-ID', options);
        }
    }
};

