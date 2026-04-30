/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './vendor/filament/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#f0f5ff',
                    100: '#e0eaff',
                    200: '#c7d6ff',
                    300: '#a3baff',
                    400: '#7a94ff',
                    500: '#5365ff',
                    600: '#383fff',
                    700: '#2c31e6',
                    800: '#2529bc',
                    900: '#232895',
                    950: '#0f1f45', // Custom Navy
                },
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
}
