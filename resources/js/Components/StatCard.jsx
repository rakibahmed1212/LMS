export default function StatCard({
    label,
    value,
    icon: Icon,
    accent = 'text-slate-900',
    iconClass = 'bg-indigo-50 text-indigo-600',
}) {
    return (
        <div className="card flex items-start gap-4 p-5">
            {Icon && (
                <span
                    className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ${iconClass}`}
                >
                    <Icon className="h-5 w-5" />
                </span>
            )}
            <div className="min-w-0">
                <p className="truncate text-sm font-medium text-slate-500">
                    {label}
                </p>
                <p
                    className={`mt-1 truncate text-2xl font-bold ${accent}`}
                >
                    {value}
                </p>
            </div>
        </div>
    );
}
