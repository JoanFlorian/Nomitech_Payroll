@extends('layouts.app')

@section('title', $catalogoConfig['label'])
@section('page-title', 'Catalogos de Empresa')

@push('styles')
@vite('resources/css/catalogos.css')
@endpush

@section('content')
<div class="catalog-board" data-catalogo-app>
    <div class="catalog-hero d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="catalog-hero-icon">
                <i class="bi bi-sliders fs-4"></i>
            </span>
            <div>
                <h2 class="h4 fw-bold mb-1">{{ $catalogoConfig['label'] }}</h2>
                <p class="mb-0 small text-white-50">Panel moderno para administrar registros del sistema y de tu empresa sin cruces de datos.</p>
            </div>
        </div>
        <a href="{{ route('admin.catalogos.index') }}" class="btn btn-light btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left"></i> Volver a catalogos
        </a>
    </div>

    <div class="row g-2 mb-3 mt-1">
        @foreach($catalogos as $slug => $item)
            <div class="col-6 col-md-4 col-xl-3">
                <a href="{{ route('admin.catalogos.show', $slug) }}"
                   class="catalog-grid-card catalog-{{ $slug }} {{ $slug === $catalogo ? 'is-active' : '' }}">
                    <div class="d-flex align-items-center gap-2">
                        <span class="icon-wrap">
                            <i class="bi {{ $item['icon'] }}"></i>
                        </span>
                        <div>
                            <div class="fw-semibold">{{ $item['label'] }}</div>
                            <small class="text-secondary">Administrar</small>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="catalog-toolbar d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
        <div class="input-group catalog-search" style="max-width: 390px;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input id="searchInput" type="text" class="form-control" placeholder="Buscar por nombre...">
        </div>
        <div class="d-flex align-items-center gap-2">
            <select id="perPage" class="form-select form-select-sm" style="width: auto;">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="30">30</option>
            </select>
            <button id="openCreateModal" class="btn btn-success btn-sm btn-add px-3">
                <i class="bi bi-plus-circle"></i> Agregar
            </button>
        </div>
    </div>

    <div class="catalog-table-wrap fade-in">
        <table class="table align-middle mb-0 catalog-table">
            <thead id="catalogTableHead"></thead>
            <tbody id="catalogTableBody">
                <tr>
                    <td colspan="{{ count($displayColumns) + 1 }}" class="text-center py-4 text-secondary">Cargando datos...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
        <small id="paginationInfo" class="text-secondary"></small>
        <div id="paginationControls" class="btn-group btn-group-sm" role="group"></div>
    </div>
</div>

<div class="modal fade catalog-modal" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content catalog-modal-content rounded-4 border-0 shadow">
            <div class="modal-header catalog-modal-header border-0">
                <h5 class="modal-title fw-bold d-flex align-items-center mb-0">
                    <span class="catalog-modal-icon"><i class="bi bi-plus-circle"></i></span>
                    <span>
                        Agregar {{ $catalogoConfig['label'] }}
                        <small class="d-block catalog-modal-subtitle">Completa la informacion para crear un nuevo registro.</small>
                    </span>
                </h5>
                <button type="button" class="btn-close catalog-close-btn" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body catalog-modal-body pt-0">
                <form id="createForm" class="catalog-modal-form d-grid gap-3">
                    @foreach($formFields as $field)
                        @if($field['type'] === 'switch')
                            <div class="field-full">
                                <div class="catalog-switch-card">
                                    <div>
                                        <label class="catalog-switch-title" for="create_{{ $field['key'] }}">{{ $field['label'] }}</label>
                                        <p class="catalog-switch-subtitle mb-0">Define si este registro estara disponible en el sistema.</p>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input catalog-switch-input" type="checkbox" role="switch"
                                               name="{{ $field['key'] }}" id="create_{{ $field['key'] }}"
                                               data-field="{{ $field['key'] }}"
                                               {{ $field['default'] ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div class="invalid-feedback d-block" data-error="{{ $field['key'] }}"></div>
                            </div>
                        @else
                            @php
                                $iconByField = [
                                    'nombre' => 'bi-card-text',
                                    'telefono' => 'bi-telephone',
                                    'direccion' => 'bi-geo-alt',
                                    'descripcion' => 'bi-text-paragraph',
                                ];
                                $fieldIcon = $iconByField[$field['key']] ?? 'bi-pencil-square';
                            @endphp
                            <div class="{{ $field['key'] === 'nombre' ? 'field-full' : '' }}">
                                <div class="input-group input-group-modern">
                                    <span class="input-group-text"><i class="bi {{ $fieldIcon }}"></i></span>
                                    <div class="form-floating flex-grow-1">
                                        <input type="{{ $field['type'] }}" name="{{ $field['key'] }}"
                                               id="create_{{ $field['key'] }}_input"
                                               class="form-control catalog-input"
                                               placeholder="{{ $field['label'] }}"
                                               data-field="{{ $field['key'] }}"
                                               {{ $field['required'] ? 'required' : '' }} maxlength="255">
                                        <label for="create_{{ $field['key'] }}_input">{{ $field['label'] }}</label>
                                    </div>
                                </div>
                                <div class="invalid-feedback d-block" data-error="{{ $field['key'] }}"></div>
                            </div>
                        @endif
                    @endforeach
                </form>
            </div>
            <div class="modal-footer catalog-modal-footer border-0">
                <button type="button" class="btn catalog-btn catalog-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="saveCreateBtn" class="btn catalog-btn catalog-btn-primary px-3">
                    <span class="btn-text">Guardar</span>
                    <span class="loader d-none mini-loader"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade catalog-modal" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content catalog-modal-content rounded-4 border-0 shadow">
            <div class="modal-header catalog-modal-header border-0">
                <h5 class="modal-title fw-bold d-flex align-items-center mb-0">
                    <span class="catalog-modal-icon"><i class="bi bi-pencil-square"></i></span>
                    <span>
                        Editar {{ $catalogoConfig['label'] }}
                        <small class="d-block catalog-modal-subtitle">Actualiza la informacion del registro seleccionado.</small>
                    </span>
                </h5>
                <button type="button" class="btn-close catalog-close-btn" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body catalog-modal-body pt-0">
                <form id="editForm" class="catalog-modal-form d-grid gap-3">
                    <input type="hidden" name="id">
                    @foreach($formFields as $field)
                        @if($field['type'] === 'switch')
                            <div class="field-full">
                                <div class="catalog-switch-card">
                                    <div>
                                        <label class="catalog-switch-title" for="edit_{{ $field['key'] }}">{{ $field['label'] }}</label>
                                        <p class="catalog-switch-subtitle mb-0">Define si este registro estara disponible en el sistema.</p>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input catalog-switch-input" type="checkbox" role="switch"
                                               name="{{ $field['key'] }}" id="edit_{{ $field['key'] }}"
                                               data-field="{{ $field['key'] }}">
                                    </div>
                                </div>
                                <div class="invalid-feedback d-block" data-error="{{ $field['key'] }}"></div>
                            </div>
                        @else
                            @php
                                $iconByField = [
                                    'nombre' => 'bi-card-text',
                                    'telefono' => 'bi-telephone',
                                    'direccion' => 'bi-geo-alt',
                                    'descripcion' => 'bi-text-paragraph',
                                ];
                                $fieldIcon = $iconByField[$field['key']] ?? 'bi-pencil-square';
                            @endphp
                            <div class="{{ $field['key'] === 'nombre' ? 'field-full' : '' }}">
                                <div class="input-group input-group-modern">
                                    <span class="input-group-text"><i class="bi {{ $fieldIcon }}"></i></span>
                                    <div class="form-floating flex-grow-1">
                                        <input type="{{ $field['type'] }}" name="{{ $field['key'] }}"
                                               id="edit_{{ $field['key'] }}_input"
                                               class="form-control catalog-input"
                                               placeholder="{{ $field['label'] }}"
                                               data-field="{{ $field['key'] }}"
                                               {{ $field['required'] ? 'required' : '' }} maxlength="255">
                                        <label for="edit_{{ $field['key'] }}_input">{{ $field['label'] }}</label>
                                    </div>
                                </div>
                                <div class="invalid-feedback d-block" data-error="{{ $field['key'] }}"></div>
                            </div>
                        @endif
                    @endforeach
                </form>
                <div id="editReadonlyMsg" class="alert alert-secondary d-none mt-2 mb-0">
                    Este registro es del sistema y no se puede editar.
                </div>
            </div>
            <div class="modal-footer catalog-modal-footer border-0">
                <button type="button" class="btn catalog-btn catalog-btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" id="saveEditBtn" class="btn catalog-btn catalog-btn-primary px-3">
                    <span class="btn-text">Actualizar</span>
                    <span class="loader d-none mini-loader"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
    <div id="catalogToast" class="toast align-items-center text-bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="catalogToastBody"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (() => {
        const catalogo = @json($catalogo);
        const primaryKey = @json($catalogoConfig['primary_key']);
        const displayColumns = @json($displayColumns);
        const formFields = @json($formFields);
        const routes = {
            data: @json(route('admin.catalogos.data', ['catalogo' => $catalogo])),
            store: @json(route('admin.catalogos.store', ['catalogo' => $catalogo])),
            update: @json(route('admin.catalogos.update', ['catalogo' => $catalogo, 'id' => '__id__'])),
            toggle: @json(route('admin.catalogos.toggle-estado', ['catalogo' => $catalogo, 'id' => '__id__'])),
        };

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const tableHead = document.getElementById('catalogTableHead');
        const tableBody = document.getElementById('catalogTableBody');
        const paginationInfo = document.getElementById('paginationInfo');
        const paginationControls = document.getElementById('paginationControls');
        const searchInput = document.getElementById('searchInput');
        const perPage = document.getElementById('perPage');
        const toastEl = document.getElementById('catalogToast');
        const toastBody = document.getElementById('catalogToastBody');
        const toast = new bootstrap.Toast(toastEl, { delay: 2800 });

        const createModalEl = document.getElementById('createModal');
        const editModalEl = document.getElementById('editModal');
        if (createModalEl.parentElement !== document.body) {
            document.body.appendChild(createModalEl);
        }
        if (editModalEl.parentElement !== document.body) {
            document.body.appendChild(editModalEl);
        }

        const createModal = new bootstrap.Modal(createModalEl);
        const editModal = new bootstrap.Modal(editModalEl);

        const createForm = document.getElementById('createForm');
        const editForm = document.getElementById('editForm');
        const saveCreateBtn = document.getElementById('saveCreateBtn');
        const saveEditBtn = document.getElementById('saveEditBtn');
        const openCreateModal = document.getElementById('openCreateModal');
        const editReadonlyMsg = document.getElementById('editReadonlyMsg');

        let state = {
            page: 1,
            q: '',
            perPage: Number(perPage.value),
            rows: [],
        };

        const setLoadingBtn = (btn, loading) => {
            btn.disabled = loading;
            btn.querySelector('.btn-text')?.classList.toggle('d-none', loading);
            btn.querySelector('.loader')?.classList.toggle('d-none', !loading);
        };

        const notify = (message, type = 'dark') => {
            toastEl.className = `toast align-items-center text-bg-${type} border-0`;
            toastBody.textContent = message;
            toast.show();
        };

        const confirmFallback = ({ title, text, confirmText = 'Aceptar', cancelText = 'Cancelar' }) => {
            let wrapper = document.getElementById('catalogConfirmFallbackWrapper');

            if (!wrapper) {
                wrapper = document.createElement('div');
                wrapper.id = 'catalogConfirmFallbackWrapper';
                wrapper.innerHTML = `
                    <div class="modal fade" id="catalogConfirmFallback" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4 border-0 shadow-lg">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-bold" id="catalogConfirmFallbackTitle"></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body pt-1">
                                    <p class="mb-0 text-secondary" id="catalogConfirmFallbackText"></p>
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-outline-secondary" id="catalogConfirmFallbackCancel"></button>
                                    <button type="button" class="btn btn-success" id="catalogConfirmFallbackOk"></button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(wrapper);
            }

            const modalEl = document.getElementById('catalogConfirmFallback');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            const titleEl = document.getElementById('catalogConfirmFallbackTitle');
            const textEl = document.getElementById('catalogConfirmFallbackText');
            const okBtn = document.getElementById('catalogConfirmFallbackOk');
            const cancelBtn = document.getElementById('catalogConfirmFallbackCancel');

            titleEl.textContent = title || 'Confirmar accion';
            textEl.textContent = text || 'Deseas continuar?';
            okBtn.textContent = confirmText;
            cancelBtn.textContent = cancelText;

            return new Promise((resolve) => {
                let resolved = false;

                const cleanup = () => {
                    okBtn.removeEventListener('click', onOk);
                    cancelBtn.removeEventListener('click', onCancel);
                    modalEl.removeEventListener('hidden.bs.modal', onHidden);
                };

                const finish = (value) => {
                    if (resolved) return;
                    resolved = true;
                    cleanup();
                    resolve(value);
                };

                const onOk = () => {
                    finish(true);
                    modal.hide();
                };

                const onCancel = () => {
                    finish(false);
                    modal.hide();
                };

                const onHidden = () => {
                    finish(false);
                };

                okBtn.addEventListener('click', onOk);
                cancelBtn.addEventListener('click', onCancel);
                modalEl.addEventListener('hidden.bs.modal', onHidden);

                modal.show();
            });
        };

        const confirmAction = async ({
            title,
            text,
            confirmText = 'Si, continuar',
            cancelText = 'Cancelar',
            icon = 'question',
        }) => {
            if (typeof Swal === 'undefined') {
                return confirmFallback({ title, text, confirmText, cancelText });
            }

            const result = await Swal.fire({
                title,
                text,
                icon,
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: cancelText,
                reverseButtons: true,
                focusCancel: true,
                buttonsStyling: false,
                customClass: {
                    popup: 'rounded-4 shadow-lg border border-light',
                    title: 'h5 mb-2',
                    htmlContainer: 'text-secondary',
                    confirmButton: 'btn btn-success px-3 py-2 me-2',
                    cancelButton: 'btn btn-outline-secondary px-3 py-2',
                },
            });

            return result.isConfirmed;
        };

        const request = async (url, options = {}) => {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    ...options.headers,
                },
                ...options,
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                const msg = data?.message || 'No se pudo completar la operacion.';
                const err = new Error(msg);
                err.data = data;
                throw err;
            }

            return data;
        };

        const clearErrors = (form) => {
            form.querySelectorAll('[data-error]').forEach(el => el.textContent = '');
        };

        const showErrors = (form, errors = {}) => {
            Object.keys(errors).forEach((key) => {
                const el = form.querySelector(`[data-error="${key}"]`);
                if (el) {
                    el.textContent = errors[key][0] || '';
                }
            });
        };

        const statusBadge = (estado) => {
            return estado
                ? '<span class="pill-status bg-success-subtle text-success-emphasis">Activo</span>'
                : '<span class="pill-status bg-danger-subtle text-danger-emphasis">Inactivo</span>';
        };

        const origenBadge = (origen) => {
            if (origen === 'system') {
                return '<span class="badge rounded-pill text-bg-primary">Sistema</span>';
            }
            return '<span class="badge rounded-pill text-bg-success">Empresa</span>';
        };

        const renderTableHead = () => {
            const headers = displayColumns.map((column, index) => {
                const extraClass = index === 0 ? 'class="px-3"' : '';
                return `<th ${extraClass}>${column.label}</th>`;
            }).join('');

            tableHead.innerHTML = `
                <tr>
                    ${headers}
                    <th class="text-end pe-3">ACCIONES</th>
                </tr>
            `;
        };

        const renderCell = (row, key) => {
            if (key === 'origen') {
                return origenBadge(row.origen);
            }

            if (key === 'estado') {
                const disabled = row.editable ? '' : 'disabled';
                return `
                    <div class="d-flex align-items-center gap-2">
                        ${statusBadge(Boolean(row.estado))}
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input js-toggle" type="checkbox" data-id="${row.id}" ${row.estado ? 'checked' : ''} ${disabled}>
                        </div>
                    </div>
                `;
            }

            if (key === 'empresa_nit') {
                return `<small class="text-secondary">${row.empresa_nit || 'Global'}</small>`;
            }

            const raw = row[key];
            if (raw === null || raw === undefined || raw === '') {
                return '<span class="text-secondary">-</span>';
            }

            if (typeof raw === 'boolean') {
                return raw ? 'Si' : 'No';
            }

            return String(raw);
        };

        const renderTable = () => {
            if (!state.rows.length) {
                tableBody.innerHTML = `<tr><td colspan="${displayColumns.length + 1}" class="text-center py-4 text-secondary">No hay resultados.</td></tr>`;
                return;
            }

            tableBody.innerHTML = state.rows.map((row) => {
                const disabled = row.editable ? '' : 'disabled';
                const columnsHtml = displayColumns.map((column, index) => {
                    const value = renderCell(row, column.key);
                    const extraClass = index === 0 ? ' class="px-3 fw-semibold"' : '';
                    return `<td${extraClass}>${value}</td>`;
                }).join('');

                return `
                    <tr>
                        ${columnsHtml}
                        <td class="text-end pe-3">
                            <button class="btn btn-sm btn-edit js-edit" data-id="${row.id}" ${disabled}>
                                <i class="bi bi-pencil-square"></i> Editar
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        };

        const renderPagination = (meta) => {
            paginationInfo.textContent = `Pagina ${meta.current_page} de ${meta.last_page} | ${meta.total} registros`;

            const pages = [];
            const start = Math.max(1, meta.current_page - 2);
            const end = Math.min(meta.last_page, meta.current_page + 2);
            for (let i = start; i <= end; i++) pages.push(i);

            paginationControls.innerHTML = `
                <button class="btn btn-outline-secondary" ${meta.current_page <= 1 ? 'disabled' : ''} data-page="${meta.current_page - 1}">Anterior</button>
                ${pages.map((p) => `<button class="btn ${p === meta.current_page ? 'btn-dark' : 'btn-outline-secondary'}" data-page="${p}">${p}</button>`).join('')}
                <button class="btn btn-outline-secondary" ${meta.current_page >= meta.last_page ? 'disabled' : ''} data-page="${meta.current_page + 1}">Siguiente</button>
            `;
        };

        const loadData = async () => {
            tableBody.innerHTML = `<tr><td colspan="${displayColumns.length + 1}" class="text-center py-4 text-secondary"><span class="mini-loader me-2"></span>Cargando...</td></tr>`;

            const params = new URLSearchParams({
                q: state.q,
                per_page: String(state.perPage),
                page: String(state.page),
            });

            try {
                const response = await request(`${routes.data}?${params.toString()}`);
                state.rows = response.data.map((row) => ({ ...row, id: row[primaryKey] }));
                renderTable();
                renderPagination(response.meta);
            } catch (error) {
                tableBody.innerHTML = `<tr><td colspan="${displayColumns.length + 1}" class="text-center py-4 text-danger">Error al cargar datos.</td></tr>`;
                notify(error.message, 'danger');
            }
        };

        const serializeForm = (form) => {
            const payload = {};

            formFields.forEach((field) => {
                const input = form.querySelector(`[data-field="${field.key}"]`);
                if (!input) return;

                if (field.type === 'switch') {
                    payload[field.key] = input.checked ? 1 : 0;
                } else {
                    payload[field.key] = String(input.value || '').trim();
                }
            });

            return payload;
        };

        const openEditModal = (row) => {
            const editable = !!row.editable;
            editForm.elements.id.value = row.id;

            formFields.forEach((field) => {
                const input = editForm.querySelector(`[data-field="${field.key}"]`);
                if (!input) return;

                if (field.type === 'switch') {
                    input.checked = Boolean(row[field.key]);
                } else {
                    input.value = row[field.key] ?? '';
                }

                input.disabled = !editable;
            });

            saveEditBtn.classList.toggle('d-none', !editable);
            editReadonlyMsg.classList.toggle('d-none', editable);

            clearErrors(editForm);
            editModal.show();
        };

        openCreateModal.addEventListener('click', () => {
            createForm.reset();
            formFields.forEach((field) => {
                const input = createForm.querySelector(`[data-field="${field.key}"]`);
                if (!input) return;

                if (field.type === 'switch') {
                    input.checked = Boolean(field.default);
                }
            });
            clearErrors(createForm);
            createModal.show();
        });

        saveCreateBtn.addEventListener('click', async () => {
            const confirmed = await confirmAction({
                title: 'Crear registro',
                text: 'Estas seguro de crear este registro?',
                confirmText: 'Si, crear',
                icon: 'question',
            });
            if (!confirmed) return;

            clearErrors(createForm);
            setLoadingBtn(saveCreateBtn, true);

            try {
                await request(routes.store, {
                    method: 'POST',
                    body: JSON.stringify(serializeForm(createForm)),
                });
                createModal.hide();
                notify('Registro creado con exito.', 'success');
                await loadData();
            } catch (error) {
                showErrors(createForm, error.data?.errors || {});
                notify(error.message, 'danger');
            } finally {
                setLoadingBtn(saveCreateBtn, false);
            }
        });

        saveEditBtn.addEventListener('click', async () => {
            const confirmed = await confirmAction({
                title: 'Actualizar registro',
                text: 'Confirmas actualizar este registro?',
                confirmText: 'Si, actualizar',
                icon: 'info',
            });
            if (!confirmed) return;

            clearErrors(editForm);
            setLoadingBtn(saveEditBtn, true);

            try {
                const id = editForm.elements.id.value;
                await request(routes.update.replace('__id__', id), {
                    method: 'PUT',
                    body: JSON.stringify(serializeForm(editForm)),
                });
                editModal.hide();
                notify('Registro actualizado correctamente.', 'success');
                await loadData();
            } catch (error) {
                showErrors(editForm, error.data?.errors || {});
                notify(error.message, 'danger');
            } finally {
                setLoadingBtn(saveEditBtn, false);
            }
        });

        tableBody.addEventListener('click', async (event) => {
            const editBtn = event.target.closest('.js-edit');
            if (editBtn) {
                const id = Number(editBtn.dataset.id);
                const row = state.rows.find((item) => Number(item.id) === id);
                if (row) openEditModal(row);
                return;
            }

            const toggle = event.target.closest('.js-toggle');
            if (toggle) {
                const id = Number(toggle.dataset.id);
                const checked = !!toggle.checked;

                const confirmed = await confirmAction({
                    title: 'Cambiar estado',
                    text: 'Deseas cambiar el estado de este registro?',
                    confirmText: 'Si, cambiar',
                    icon: 'warning',
                });

                if (!confirmed) {
                    toggle.checked = !checked;
                    return;
                }

                try {
                    await request(routes.toggle.replace('__id__', id), {
                        method: 'PATCH',
                        body: JSON.stringify({ estado: checked ? 1 : 0 }),
                    });
                    notify('Estado actualizado.', 'success');
                    await loadData();
                } catch (error) {
                    toggle.checked = !checked;
                    notify(error.message, 'danger');
                }
            }
        });

        paginationControls.addEventListener('click', (event) => {
            const btn = event.target.closest('button[data-page]');
            if (!btn) return;
            state.page = Number(btn.dataset.page);
            loadData();
        });

        let searchTimeout;
        searchInput.addEventListener('input', (event) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                state.q = String(event.target.value || '').trim();
                state.page = 1;
                loadData();
            }, 280);
        });

        perPage.addEventListener('change', () => {
            state.perPage = Number(perPage.value);
            state.page = 1;
            loadData();
        });

        renderTableHead();
        loadData();
    })();
</script>
@endpush
