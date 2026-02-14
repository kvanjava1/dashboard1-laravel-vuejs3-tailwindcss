/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.ts",
        "./resources/**/*.vue",
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                "primary": "#3b82f6",
                "primary-light": "#60a5fa",
                "primary-dark": "#1d4ed8",
                "secondary": "#8b5cf6",
                "accent": "#94a3b8",
                "background-light": "#ffffff",
                "background-lighter": "#f8fafc",
                "background-dark": "#0f172a",
                "surface-light": "#f1f5f9",
                "surface-dark": "#1e293b",
                "surface-darker": "#020617",
                "border-light": "#e2e8f0",
                "border-dark": "#334155",
                "success": "#10b981",
                "warning": "#f59e0b",
                "danger": "#ef4444",
                "info": "#0ea5e9"
            },
            fontFamily: {
                "display": ["Manrope", "sans-serif"]
            },
            borderRadius: {
                none: '0px',
                sm: '0px',
                DEFAULT: '0px',
                md: '0px',
                lg: '0px',
                xl: '0px',
                '2xl': '0px',
                '3xl': '0px',
                full: '9999px'
            },
            boxShadow: {
                'soft': '0 4px 20px rgba(0, 0, 0, 0.05)',
                'medium': '0 8px 30px rgba(0, 0, 0, 0.08)',
                'hard': '0 20px 60px rgba(0, 0, 0, 0.12)',
                'inner-light': 'inset 0 2px 4px rgba(255, 255, 255, 0.5)',
                'inner-dark': 'inset 0 2px 4px rgba(0, 0, 0, 0.05)',
            },
            animation: {
                'float': 'float 6s ease-in-out infinite',
                'pulse-gentle': 'pulse-gentle 2s ease-in-out infinite',
                'slide-in': 'slide-in 0.3s ease-out forwards',
                'fade-in': 'fade-in 0.2s ease-out forwards',
            },
            keyframes: {
                float: {
                    '0%, 100%': { transform: 'translateY(0px)' },
                    '50%': { transform: 'translateY(-10px)' },
                },
                'pulse-gentle': {
                    '0%, 100%': { opacity: 1 },
                    '50%': { opacity: 0.7 },
                },
                'slide-in': {
                    '0%': { transform: 'translateX(-100%)' },
                    '100%': { transform: 'translateX(0)' },
                },
                'fade-in': {
                    '0%': { opacity: 0 },
                    '100%': { opacity: 1 },
                }
            }
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/container-queries'),
    ],
}