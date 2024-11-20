// Script for handling search and filter functionality
document.addEventListener('DOMContentLoaded', function () {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('table tbody tr');

    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const searchTerm = searchInput.value.toLowerCase();
            tableRows.forEach(row => {
                const rowText = row.innerText.toLowerCase();
                row.style.display = rowText.includes(searchTerm) ? '' : 'none';
            });
        });
    }

    // Filter functionality
    const filterSelect = document.getElementById('filterSelect');

    if (filterSelect) {
        filterSelect.addEventListener('change', function () {
            const filterValue = filterSelect.value;
            tableRows.forEach(row => {
                const roleCellText = row.querySelector('td:nth-child(3)').innerText.toLowerCase();
                row.style.display = filterValue === 'all' || roleCellText === filterValue ? '' : 'none';
            });
        });
    }

    // Form validation example (for Add/Edit forms)
    const form = document.querySelector('form');

    if (form) {
        form.addEventListener('submit', function (event) {
            const inputs = form.querySelectorAll('input, select');
            let isValid = true;

            inputs.forEach(input => {
                if (input.hasAttribute('required') && input.value.trim() === '') {
                    isValid = false;
                    input.classList.add('error');
                } else {
                    input.classList.remove('error');
                }
            });

            if (!isValid) {
                event.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    }
});
