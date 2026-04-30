import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                // Body — refined humanist sans
                sans: ['"Inter Tight"', ...defaultTheme.fontFamily.sans],
                // Display — sharp editorial serif w/ optical-size axis
                display: ['Fraunces', ...defaultTheme.fontFamily.serif],
                // Logo-specific — delicate, narrow garamond italic
                logo: ['"Cormorant Garamond"', ...defaultTheme.fontFamily.serif],
                // Small caps / labels — same as body, used w/ tracking utilities
                mono: ['"JetBrains Mono"', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                // Editorial Scholar palette
                ivory: {
                    DEFAULT: '#F5F1E8',
                    50: '#FBF9F4',
                    100: '#F5F1E8',
                    200: '#ECE5D2',
                    300: '#DDD3B8',
                },
                ink: {
                    DEFAULT: '#1A1814',
                    soft: '#4A4740',
                    mute: '#6B675F',
                    50: '#6B675F',
                    100: '#4A4740',
                    200: '#2E2B26',
                    300: '#1A1814',
                },
                oxblood: {
                    DEFAULT: '#7A1F2B',
                    light: '#9B2D3B',
                    dark: '#5C161F',
                },
                rule: '#1A18141A', // 10% ink — for hairlines
            },
            letterSpacing: {
                label: '0.18em',
            },
        },
    },

    plugins: [forms],
};
