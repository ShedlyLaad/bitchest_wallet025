/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx}',
  ],
  theme: {
    extend: {
      colors: {
        theme: {
          success: 'var(--accent-green)',
          danger: 'var(--accent-red)',
          white: 'var(--bg)',
          primary: 'var(--blue-dark)',
          secondary: 'var(--blue)'
        }
      },
      backgroundColor: {
        theme: {
          success: 'var(--accent-green)',
          danger: 'var(--accent-red)',
          white: 'var(--bg)',
          primary: 'var(--blue-dark)',
          secondary: 'var(--blue)'
        }
      },
      borderColor: {
        theme: {
          success: 'var(--accent-green)',
          danger: 'var(--accent-red)',
          white: 'var(--bg)',
          primary: 'var(--blue-dark)',
          secondary: 'var(--blue)'
        }
      }
    }
  },
  plugins: [],
}

