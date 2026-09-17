/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    // Pas de police distante (CLAUDE.md : zéro CDN) : pile système uniquement.
    theme: {
        extend: {},
    },
    plugins: [],
};
