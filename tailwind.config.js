export default {
  content: [
    "./resources/js/**/*.{js,vue}",
    "./resources/views/**/*.blade.php",
    "./tests/Support/resources/views/**/*.blade.php",
  ],
  theme: {
    extend: {
      spacing: {
        "3xlSpace": "96px",
        "2xlSpace": "64px",
        xlSpace: "32px",
        lgSpace: "24px",
        mdSpace: "16px",
        smSpace: "12px",
        xsSpace: "8px",
        "2xsSpace": "4px",
        "3xsSpace": "2px",
      },
    },
  },
  plugins: [],
};
