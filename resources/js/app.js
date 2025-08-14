import './bootstrap';
import "jsvectormap/dist/jsvectormap.css";
import "flatpickr/dist/flatpickr.css";
import "dropzone/dist/dropzone.css";
import "../css/app.css";

import flatpickr from "flatpickr";
import Dropzone from "dropzone";

import chart01 from "./components/charts/chart-01";
import chart02 from "./components/charts/chart-02";
import chart03 from "./components/charts/chart-03";
import map01 from "./components/map-01";
import "./components/calendar-init.js";
import "./components/image-resize";

window.chart01 = chart01;
window.chart02 = chart02;
window.chart03 = chart03;
window.map01 = map01;

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

// 1. Buat fungsi terpisah untuk inisialisasi komponen dasbor
const initializeDashboardComponents = () => {
    // ++ AWAL KODE BARU: Inisialisasi datepicker untuk tabel absensi
    const datepickerEl = document.querySelector("#attendance-datepicker");

    // Hanya jalankan jika elemen ada di halaman
    if (datepickerEl) {
        // Hancurkan instance flatpickr sebelumnya jika ada untuk mencegah duplikasi
        if (datepickerEl._flatpickr) {
            datepickerEl._flatpickr.destroy();
        }

        flatpickr(datepickerEl, {
            mode: "range",
            dateFormat: "M j, Y",
            onClose: function(selectedDates, dateStr, instance) {
                if (!dateStr) return;

                // Temukan komponen Livewire induk dari elemen input
                const componentEl = instance.element.closest('[wire\\:id]');
                if (componentEl) {
                    const component = Livewire.find(componentEl.getAttribute('wire:id'));
                    // Kirim event ke komponen Livewire
                    component.dispatch('date-updated', { date: dateStr });
                }
            },
            plugins: [
                new (function() {
                    return function(fp) {
                        return {
                            onReady: function() {
                                // Jangan tambahkan tombol jika sudah ada
                                if (fp.calendarContainer.querySelector(".flatpickr-clear")) {
                                    return;
                                }

                                const clearButton = document.createElement("button");
                                clearButton.className = "flatpickr-button flatpickr-clear";
                                clearButton.textContent = "Clear";
                                clearButton.type = "button";

                                clearButton.addEventListener("click", function(e) {
                                    e.stopPropagation();

                                    const componentEl = fp.element.closest('[wire\\:id]');
                                    if (componentEl) {
                                        const component = Livewire.find(componentEl.getAttribute('wire:id'));
                                        fp.clear();
                                        // Kirim event dengan nilai kosong untuk mereset filter
                                        component.dispatch('date-updated', { date: '' });
                                    }
                                });

                                fp.calendarContainer.appendChild(clearButton);
                            }
                        }
                    }
                })()
            ]
        });
    }
    // ++ AKHIR KODE BARU
};

// Panggil saat halaman pertama kali dimuat (initial load)
document.addEventListener("DOMContentLoaded", () => {
    initializeDashboardComponents();
});

// 3. Panggil lagi setiap kali Livewire selesai navigasi
document.addEventListener('livewire:navigated', () => {
    initializeDashboardComponents();
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

