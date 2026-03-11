import { useEffect } from 'react';

function IconX(props) {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" {...props}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M6 6l12 12M18 6L6 18" />
    </svg>
  );
}

function IconSave(props) {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" {...props}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
      <path strokeLinecap="round" strokeLinejoin="round" d="M17 21v-8H7v8M7 3v5h8" />
    </svg>
  );
}

function IconPhone(props) {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" {...props}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M22 16.92v3a2 2 0 01-2.18 2A19.8 19.8 0 013 5.18 2 2 0 015 3h3a2 2 0 012 1.72c.12.9.33 1.77.62 2.6a2 2 0 01-.45 2.11L9.1 10.9a16 16 0 006 6l1.47-1.19a2 2 0 012.11-.45c.83.29 1.7.5 2.6.62A2 2 0 0122 16.92z" />
    </svg>
  );
}

function IconMap(props) {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" {...props}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1118 0z" />
      <circle cx="12" cy="10" r="3" />
    </svg>
  );
}

function IconTag(props) {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" {...props}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M20.59 13.41L11 3H4v7l9.59 9.59a2 2 0 002.82 0l4.18-4.18a2 2 0 000-2.82z" />
      <line x1="7" y1="7" x2="7.01" y2="7" />
    </svg>
  );
}

function FloatingField({ label, name, type = 'text', value, onChange, icon, required, disabled, error }) {
  return (
    <div className="space-y-1">
      <div className="relative">
        <span className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">{icon}</span>
        <input
          id={name}
          name={name}
          type={type}
          value={value ?? ''}
          onChange={onChange}
          placeholder=" "
          required={required}
          disabled={disabled}
          className="peer h-12 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 disabled:cursor-not-allowed disabled:bg-slate-100"
        />
        <label
          htmlFor={name}
          className="pointer-events-none absolute left-10 top-1/2 -translate-y-1/2 rounded bg-white px-1 text-sm text-slate-500 transition-all peer-placeholder-shown:top-1/2 peer-focus:top-0 peer-focus:text-xs peer-focus:text-emerald-600 peer-[:not(:placeholder-shown)]:top-0 peer-[:not(:placeholder-shown)]:text-xs"
        >
          {label}
        </label>
      </div>
      {error ? <p className="text-xs text-rose-600">{error}</p> : <p className="h-4" />}
    </div>
  );
}

export default function CatalogoSaasModal({
  open,
  title = 'Agregar ARL',
  mode = 'create',
  loading = false,
  values,
  errors = {},
  onChange,
  onSubmit,
  onClose,
}) {
  useEffect(() => {
    if (!open) return;

    const onEsc = (event) => {
      if (event.key === 'Escape') onClose?.();
    };

    document.body.style.overflow = 'hidden';
    document.addEventListener('keydown', onEsc);

    return () => {
      document.body.style.overflow = '';
      document.removeEventListener('keydown', onEsc);
    };
  }, [open, onClose]);

  if (!open) return null;

  const isEdit = mode === 'edit';

  return (
    <div className="fixed inset-0 z-[120]">
      <div
        className="absolute inset-0 bg-slate-950/50 backdrop-blur-[3px] animate-[fadeIn_.18s_ease-out]"
        onClick={onClose}
      />

      <div className="absolute inset-0 grid place-items-center p-4">
        <section className="relative w-full max-w-4xl overflow-hidden rounded-2xl border border-white/30 bg-white shadow-[0_30px_80px_rgba(2,6,23,.35)] animate-[popIn_.22s_ease-out]">
          <div className="grid md:grid-cols-[300px_1fr]">
            <aside className="relative hidden overflow-hidden bg-gradient-to-br from-blue-900 via-blue-700 to-emerald-600 p-6 text-white md:block">
              <div className="absolute -left-12 -top-8 h-44 w-44 rounded-full bg-white/10 blur-2xl" />
              <div className="absolute -bottom-16 -right-14 h-56 w-56 rounded-full bg-emerald-200/20 blur-2xl" />

              <p className="text-xs uppercase tracking-[0.18em] text-blue-100/90">Catalogos Empresa</p>
              <h3 className="mt-3 text-2xl font-semibold leading-tight">{title}</h3>
              <p className="mt-3 text-sm text-blue-100/95">
                Interfaz premium para un flujo administrativo claro, rapido y moderno.
              </p>

              <div className="mt-8 space-y-3 text-xs">
                <div className="rounded-xl border border-white/20 bg-white/10 px-3 py-2">Validacion en tiempo real</div>
                <div className="rounded-xl border border-white/20 bg-white/10 px-3 py-2">Diseño orientado a SaaS</div>
                <div className="rounded-xl border border-white/20 bg-white/10 px-3 py-2">Experiencia consistente</div>
              </div>
            </aside>

            <div className="p-5 md:p-6">
              <header className="mb-5 flex items-start justify-between border-b border-slate-100 pb-4">
                <div>
                  <h4 className="text-xl font-semibold text-slate-900">{title}</h4>
                  <p className="mt-1 text-sm text-slate-500">Completa los campos para guardar el registro.</p>
                </div>

                <button
                  type="button"
                  onClick={onClose}
                  className="rounded-lg border border-slate-200 p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                  aria-label="Cerrar"
                >
                  <IconX className="h-4 w-4" />
                </button>
              </header>

              <form onSubmit={onSubmit} className="space-y-2">
                <FloatingField
                  label="Nombre"
                  name="nombre"
                  value={values?.nombre}
                  onChange={onChange}
                  required
                  disabled={loading}
                  error={errors?.nombre}
                  icon={<IconTag className="h-4 w-4" />}
                />

                <div className="grid gap-3 md:grid-cols-2">
                  <FloatingField
                    label="Telefono"
                    name="telefono"
                    value={values?.telefono}
                    onChange={onChange}
                    disabled={loading}
                    error={errors?.telefono}
                    icon={<IconPhone className="h-4 w-4" />}
                  />
                  <FloatingField
                    label="Direccion"
                    name="direccion"
                    value={values?.direccion}
                    onChange={onChange}
                    disabled={loading}
                    error={errors?.direccion}
                    icon={<IconMap className="h-4 w-4" />}
                  />
                </div>

                <label className="mt-2 inline-flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                  <input
                    type="checkbox"
                    name="estado"
                    checked={Boolean(values?.estado)}
                    onChange={onChange}
                    disabled={loading}
                    className="h-5 w-10 cursor-pointer appearance-none rounded-full bg-slate-300 transition before:relative before:left-0.5 before:top-0.5 before:block before:h-4 before:w-4 before:rounded-full before:bg-white before:transition checked:bg-emerald-500 checked:before:translate-x-5"
                  />
                  <span className="text-sm font-medium text-slate-700">Estado activo</span>
                </label>

                <footer className="mt-5 flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                  <button
                    type="button"
                    onClick={onClose}
                    className="rounded-xl border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200"
                  >
                    Cancelar
                  </button>

                  <button
                    type="submit"
                    disabled={loading}
                    className="inline-flex min-w-28 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                  >
                    <IconSave className="h-4 w-4" />
                    {loading ? 'Guardando...' : isEdit ? 'Actualizar' : 'Guardar'}
                  </button>
                </footer>
              </form>
            </div>
          </div>
        </section>
      </div>

      <style>{`
        @keyframes fadeIn {
          from { opacity: 0; }
          to { opacity: 1; }
        }

        @keyframes popIn {
          from { opacity: 0; transform: translateY(12px) scale(0.975); }
          to { opacity: 1; transform: translateY(0) scale(1); }
        }
      `}</style>
    </div>
  );
}
