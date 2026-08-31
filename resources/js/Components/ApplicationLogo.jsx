export default function ApplicationLogo({ className = 'h-9 w-auto', ...props }) {
    return (
        <svg
            {...props}
            className={className}
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
        >
            <defs>
                <linearGradient
                    id="ls-grad"
                    x1="4"
                    y1="3"
                    x2="20"
                    y2="21"
                    gradientUnits="userSpaceOnUse"
                >
                    <stop stopColor="#6366f1" />
                    <stop offset="0.5" stopColor="#8b5cf6" />
                    <stop offset="1" stopColor="#a855f7" />
                </linearGradient>
            </defs>
            <path
                d="M12 3 1.5 8.5 12 14l8.5-4.1V16a1 1 0 0 0 2 0V8.5L12 3Z"
                fill="url(#ls-grad)"
            />
            <path
                d="M5 10.6V16c0 1.7 3.1 3 7 3s7-1.3 7-3v-5.4l-7 3.37-7-3.37Z"
                fill="url(#ls-grad)"
                opacity="0.85"
            />
        </svg>
    );
}
