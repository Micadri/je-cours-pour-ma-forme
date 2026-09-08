/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#003355',
        accent: '#8cc63f',
        background: '#f4f4f4',
        surface: '#ffffff',
        text: '#333333',
        level: {
          warmup: '#e67e22',
          beginner: '#2980b9',
          intermediate: '#27ae60',
          advanced: '#c0392b',
          expert: '#00acc1'
        }
      },
      fontFamily: {
        heading: ['Oswald', 'sans-serif'],
        body: ['Inter', 'sans-serif'],
      },
      borderRadius: {
        cta: '4px',
      }
    },
  },
  plugins: [],
}