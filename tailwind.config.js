import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#10b981', // Default primary color (emerald-500)
                    50: '#ecfdf5',
                    100: '#d1fae5',
                    200: '#a7f3d0',
                    500: '#10b981',
                    600: '#059669',
                    700: '#047857',
                    900: '#064e3b',
                },
                dark: '#0f172a',
            },
            animation: {
                'float': 'float 6s ease-in-out infinite',
                'float-delayed': 'float 6s ease-in-out 3s infinite',
                'fade-up': 'fadeUp 0.8s ease-out forwards',
            },
            keyframes: {
                float: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-20px)' },
                },
                fadeUp: {
                    '0%': { opacity: '0', transform: 'translateY(20px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                }
            },
            backgroundImage: {
                'gradient-to-r': 'linear-gradient(to right, var(--tw-gradient-stops))',
            },
            perspective: {
                '1000': '1000px',
            },
            transformStyle: {
                'preserve-3d': 'preserve-3d',
            },
        },
    },
    plugins: [],
    safelist: [
        // Ensure gradient classes are included
        'bg-gradient-to-r',
        'bg-gradient-to-br',
        'from-primary',
        'from-primary-500',
        'from-primary-700',
        'from-dark',
        'to-blue-500',
        'to-blue-600',
        'to-primary-700',
        'to-slate-900',
        'bg-clip-text',
        'text-transparent',
        // Ensure shadow colors are included
        'shadow-emerald-200',
        'shadow-primary-500',
        'shadow-primary-500/30',
        'shadow-primary-600/30',
        'shadow-slate-900/20',
        // Ensure opacity classes are included
        'opacity-25',
        'opacity-40',
        'opacity-10',
        'opacity-50',
        'opacity-90',
        // Primary color variants
        'bg-primary',
        'bg-primary-50',
        'bg-primary-100',
        'bg-primary-200',
        'bg-primary-500',
        'bg-primary-600',
        'bg-primary-700',
        'bg-primary-900',
        'text-primary-500',
        'text-primary-600',
        'text-primary-700',
        'hover:bg-primary-600',
        'hover:bg-primary-700',
        'hover:bg-primary-800',
        'hover:text-primary-600',
        // Dark color
        'bg-dark',
        'text-dark',
        'hover:bg-slate-800',
        // Animation classes
        'animate-float',
        'animate-float-delayed',
        'animate-fade-up',
        // Custom classes
        'glass-nav',
        'hero-pattern',
        'text-gradient',
        'reveal',
        'reveal.active',
        // Transform classes
        'perspective-1000',
        'rotate-y-12',
        'rotate-x-6',
        'hover:rotate-0',
        '-rotate-3',
        'hover:rotate-0',
        // Opacity variants
        'bg-white/10',
        'bg-white/20',
        'bg-primary-200/40',
        'bg-blue-200/40',
        // Height percentages
        'h-[40%]',
        'h-[50%]',
        'h-[60%]',
        'h-[70%]',
        'h-[80%]',
        'h-[85%]',
        'h-[600px]',
        // Width percentages
        'w-[500px]',
        'w-[400px]',
        'w-[85%]',
        // Text sizes
        'text-[10px]',
        'text-[9xl]',
        // Border radius
        'rounded-[3rem]',
        'rounded-bl-xl',
        'rounded-tr-2xl',
        // Z-index
        '-z-10',
        'z-10',
        'z-20',
        'z-30',
        // Positioning
        '-mr-10',
        '-mt-10',
        '-left-10',
        '-translate-y-1',
        'hover:-translate-y-1',
        'hover:-translate-y-2',
        'translate-x-10',
        'translate-y-10',
        'group-hover:translate-x-0',
        'group-hover:translate-y-0',
        // Grid classes
        'grid',
        'grid-cols-1',
        'grid-cols-2',
        'md:grid-cols-3',
        'md:grid-cols-4',
        'lg:grid-cols-2',
        'hero-section-grid',
    ],
};



