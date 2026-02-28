import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
	            colors: {
	                brand: {
	                    primary: '#5B21B6',
	                    primaryLight: '#6D28D9',
	                    primaryDark: '#4C1D95',
	                    secondary: '#38BDF8',
	                    secondaryDark: '#0EA5E9',
	                    secondaryLight: '#7DD3FC',
	                },
	            },
        },
    },

	    daisyui: {
	        themes: [
	            {
	                brand: {
	                    primary: '#5B21B6',
	                    secondary: '#38BDF8',
	                    accent: '#0EA5E9',
	                    neutral: '#111827',
	                    'base-100': '#ffffff',
	                    'base-200': '#f1f5f9',
	                    'base-300': '#e2e8f0',
	                    info: '#0EA5E9',
	                    success: '#10B981',
	                    warning: '#F59E0B',
	                    error: '#EF4444',
	                },
	            },
	            {
	                'brand-dark': {
	                    primary: '#6D28D9',
	                    secondary: '#38BDF8',
	                    accent: '#0EA5E9',
	                    neutral: '#0B1220',
	                    'base-100': '#0f172a',
	                    'base-200': '#0b1220',
	                    'base-300': '#060a14',
	                    info: '#38BDF8',
	                    success: '#10B981',
	                    warning: '#F59E0B',
	                    error: '#EF4444',
	                },
	            },
	            "winter",
	            "business",
	        ],
	        darkTheme: "brand-dark",
	    },




    plugins: [forms, require('daisyui')],
};
