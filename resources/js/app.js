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


// Init flatpickr
flatpickr(".datepicker", {
    mode: "range",
    static: true,
    monthSelectorType: "static",
    dateFormat: "M j, Y",
    defaultDate: [new Date().setDate(new Date().getDate() - 6), new Date()],
    prevArrow:
        '<svg class="stroke-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15.25 6L9 12.25L15.25 18.5" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    nextArrow:
        '<svg class="stroke-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.75 19L15 12.75L8.75 6.5" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    onReady: (selectedDates, dateStr, instance) => {
        // eslint-disable-next-line no-param-reassign
        instance.element.value = dateStr.replace("to", "-");
        const customClass = instance.element.getAttribute("data-class");
        instance.calendarContainer.classList.add(customClass);
    },
    onChange: (selectedDates, dateStr, instance) => {
        // eslint-disable-next-line no-param-reassign
        instance.element.value = dateStr.replace("to", "-");
    },
});

// Init Dropzone
const dropzoneArea = document.querySelectorAll("#demo-upload");

if (dropzoneArea.length) {
    let myDropzone = new Dropzone("#demo-upload", { url: "/file/post" });
}

const initDashboardCharts = () => {
    // Grafik 1: Status Perjalanan (Bar Chart)
    const tripChartEl = document.querySelector('#tripStatusBarChart');
    if (tripChartEl && tripChartEl.dataset.series && tripChartEl.dataset.labels) {
        const tripSeries = JSON.parse(tripChartEl.dataset.series);
        const tripLabels = JSON.parse(tripChartEl.dataset.labels);

        const tripStatusBarChartOptions = {
            series: [{
                name: 'Jumlah Trip',
                data: tripSeries
            }],
            chart: { type: 'bar', height: 350, toolbar: { show: false } },
            plotOptions: { bar: { horizontal: false, columnWidth: '55%', borderRadius: 4 } },
            dataLabels: { enabled: false },
            xaxis: { categories: tripLabels }
        };
        // Hapus chart lama jika ada untuk mencegah duplikasi
        if (tripChartEl.innerHTML) {
            tripChartEl.innerHTML = '';
        }
        const tripChart = new ApexCharts(tripChartEl, tripStatusBarChartOptions);
        tripChart.render();
    }

    // Grafik 2: Distribusi Role (Donut Chart)
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
        // Hapus chart lama jika ada
        if (roleChartEl.innerHTML) {
            roleChartEl.innerHTML = '';
        }
        const roleChart = new ApexCharts(roleChartEl, userRoleDonutChartOptions);
        roleChart.render();
    }
};

const initializeDashboardComponents = () => {
    // ... (kode flatpickr untuk tabel absensi yang sudah ada)

    // Panggil fungsi inisialisasi grafik dashboard
    initDashboardCharts();
};

// Panggil saat halaman pertama kali dimuat (initial load)
document.addEventListener("DOMContentLoaded", () => {
    initializeDashboardComponents();
});

// Panggil lagi setiap kali Livewire selesai navigasi
document.addEventListener('livewire:navigated', () => {
    // Tambahkan sedikit delay untuk memastikan DOM sudah siap
    setTimeout(() => {
        initializeDashboardComponents();
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

