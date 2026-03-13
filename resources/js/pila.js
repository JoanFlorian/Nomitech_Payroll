(function () {
    const table = document.getElementById('pilaTable');
    if (!table) {
        return;
    }

    const tbody = table.querySelector('tbody');
    const searchInput = document.getElementById('employeeSearch');
    const paginationInfo = document.getElementById('paginationInfo');
    const paginationList = document.getElementById('paginationList');
    const employeesCountBadge = document.getElementById('employeesCountBadge');

    const allRows = Array.from(tbody.querySelectorAll('tr')).filter((row) => row.children.length > 1);
    const pageSize = 6;
    let currentPage = 1;
    let filteredRows = [...allRows];

    function formatBadge(total) {
        employeesCountBadge.textContent = `${total} empleado${total === 1 ? '' : 's'}`;
    }

    function renderPagination(totalPages) {
        paginationList.innerHTML = '';
        if (totalPages <= 1) {
            return;
        }

        for (let i = 1; i <= totalPages; i += 1) {
            const li = document.createElement('li');
            li.className = `page-item ${i === currentPage ? 'active' : ''}`;

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'page-link';
            button.textContent = String(i);
            button.addEventListener('click', () => {
                currentPage = i;
                renderTable();
            });

            li.appendChild(button);
            paginationList.appendChild(li);
        }
    }

    function renderTable() {
        tbody.innerHTML = '';

        if (filteredRows.length === 0) {
            const noDataRow = document.createElement('tr');
            noDataRow.innerHTML = '<td colspan="8" class="text-center py-5 text-muted">Sin resultados para la busqueda actual.</td>';
            tbody.appendChild(noDataRow);
            paginationInfo.textContent = 'Mostrando 0 registros';
            paginationList.innerHTML = '';
            formatBadge(0);
            return;
        }

        const totalPages = Math.ceil(filteredRows.length / pageSize);
        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        const start = (currentPage - 1) * pageSize;
        const end = start + pageSize;
        const pageRows = filteredRows.slice(start, end);

        pageRows.forEach((row) => tbody.appendChild(row));

        paginationInfo.textContent = `Mostrando ${start + 1} a ${Math.min(end, filteredRows.length)} de ${filteredRows.length} registros`;
        renderPagination(totalPages);
        formatBadge(filteredRows.length);
    }

    function applyFilter() {
        const term = (searchInput.value || '').trim().toLowerCase();
        filteredRows = allRows.filter((row) => row.textContent.toLowerCase().includes(term));
        currentPage = 1;
        renderTable();
    }

    searchInput.addEventListener('input', applyFilter);
    renderTable();
})();
