/** @type {import('tailwindcss').Config} */
const path = require("path");

module.exports = {
  content: ["./src/**/*.{html,js,php}"],
  theme: {
    extend: {},
  },
  plugins: [require("flowbite/plugin")],
  purge: [path.join(__dirname, "./node_modules/flowbite/**/*.js")],
};
