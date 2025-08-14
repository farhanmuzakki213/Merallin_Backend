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

function dataTableThree() {
    return {
        search: "",
        sortColumn: "name",
        sortDirection: "asc",
        currentPage: 1,
        perPage: 10,
        data: [{
            id: 1,
            name: "Farhan",
            email: "farhan@example.com",
            position: "Web Developer",
            office: "Bandung",
            status: "Hired",
            salary: "$120,000"
        },
        {
            id: 2,
            name: "Budi Santoso",
            email: "budi.s@example.com",
            position: "Project Manager",
            office: "Jakarta",
            status: "In Progress",
            salary: "$150,000"
        },
        {
            id: 3,
            name: "Citra Lestari",
            email: "citra.l@example.com",
            position: "UI/UX Designer",
            office: "Surabaya",
            status: "Pending",
            salary: "$95,000"
        },
        {
            id: 4,
            name: "Dewi Anjani",
            email: "dewi.a@example.com",
            position: "Backend Developer",
            office: "Yogyakarta",
            status: "Hired",
            salary: "$110,000"
        },
        {
            id: 5,
            name: "Eko Prasetyo",
            email: "eko.p@example.com",
            position: "Frontend Developer",
            office: "Semarang",
            status: "Hired",
            salary: "$105,000"
        },
        {
            id: 6,
            name: "Fajar Nugraha",
            email: "fajar.n@example.com",
            position: "DevOps Engineer",
            office: "Bandung",
            status: "In Progress",
            salary: "$130,000"
        },
        {
            id: 7,
            name: "Gita Permata",
            email: "gita.p@example.com",
            position: "Data Scientist",
            office: "Medan",
            status: "Pending",
            salary: "$140,000"
        }
        ],
        get pagesAroundCurrent() {
            let pages = [];
            const start = Math.max(1, this.currentPage - 1);
            const end = Math.min(this.totalPages, this.currentPage + 1);
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            return pages;
        },
        get filteredData() {
            const s = this.search.toLowerCase();
            return this.data.filter(p => p.name.toLowerCase().includes(s) || p.position.toLowerCase().includes(
                s) || p.office.toLowerCase().includes(s)).sort((a, b) => {
                    const mod = this.sortDirection === 'asc' ? 1 : -1;
                    if (a[this.sortColumn] < b[this.sortColumn]) return -1 * mod;
                    if (a[this.sortColumn] > b[this.sortColumn]) return 1 * mod;
                    return 0;
                });
        },
        get paginatedData() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredData.slice(start, start + this.perPage);
        },
        get totalEntries() {
            return this.filteredData.length;
        },
        get startEntry() {
            return (this.currentPage - 1) * this.perPage + 1;
        },
        get endEntry() {
            const end = this.currentPage * this.perPage;
            return end > this.totalEntries ? this.totalEntries : end;
        },
        get totalPages() {
            return Math.ceil(this.filteredData.length / this.perPage);
        },
        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) this.currentPage = page;
        },
        nextPage() {
            if (this.currentPage < this.totalPages) this.currentPage++;
        },
        prevPage() {
            if (this.currentPage > 1) this.currentPage--;
        },
        sortBy(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === "asc" ? "desc" : "asc";
            } else {
                this.sortColumn = column;
                this.sortDirection = "asc";
            }
        }
    };
}
window.dataTableThree = dataTableThree;
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

