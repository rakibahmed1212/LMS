import ApplicationLogo from '@/Components/ApplicationLogo';

export default function Brand({ light = false, className = '' }) {
    const textClass = light ? 'text-white' : 'text-slate-900';

    return (
        <span className={`inline-flex items-center gap-2.5 ${className}`}>
            <ApplicationLogo className="h-8 w-auto drop-shadow-sm" />
            <span className={`text-lg font-bold tracking-tight ${textClass}`}>
                Learn<span className="text-gradient">Sphere</span>
            </span>
        </span>
    );
}
