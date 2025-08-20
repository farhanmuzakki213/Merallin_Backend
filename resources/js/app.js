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

