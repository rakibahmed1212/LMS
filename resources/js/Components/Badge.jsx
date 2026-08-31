const styles = {
    active: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    trial: 'bg-blue-50 text-blue-700 ring-blue-200',
    past_due: 'bg-amber-50 text-amber-700 ring-amber-200',
    expired: 'bg-slate-100 text-slate-500 ring-slate-200',
    cancelled: 'bg-rose-50 text-rose-700 ring-rose-200',
    pending: 'bg-slate-100 text-slate-500 ring-slate-200',
    paused: 'bg-yellow-50 text-yellow-700 ring-yellow-200',
    blocked: 'bg-rose-50 text-rose-700 ring-rose-200',
    default: 'bg-slate-100 text-slate-600 ring-slate-200',
};

export default function Badge({ status, children, ...props }) {
    const label = children ?? status?.replace('_', ' ');

    return (
        <span
            {...props}
            className={`badge capitalize ring-1 ring-inset ${
                styles[status] ?? styles.default
            }`}
        >
            {label}
        </span>
    );
}
