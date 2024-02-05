/** @type {import('tailwindcss').Config} */
// eslint-disable-next-line no-undef
module.exports = {
  important: false,
  corePlugins: {
    preflight: false,
  },
  content: [
    './js/src/**/*.{js,ts,jsx,tsx}',
    './inc/custom/class/class-shortcode.php',
    './inc/custom/class/class-product-addon.php',
    './inc/templates/single-product/simple.php',
    './inc/templates/single-product/variable.php',
  ],
  theme: {
    extend: {
      colors: {
        primary: '#1677ff',
      },
      screens: {
        sm: '576px', // iphone SE
        md: '810px', // ipad 直向
        lg: '1080px', // ipad 橫向
        xl: '1280px', // mac air
        xxl: '1440px',
      },
    },
  },
  plugins: [],
  safelist: [],
}
