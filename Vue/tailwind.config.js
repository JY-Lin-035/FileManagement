/** @type {import('tailwindcss').Config} */ // 跟 IDE 交朋友
module.exports = { // 匯出物件
  content: [
    './src/**/*.{js,vue,ts,jsx,tsx}', // 掃描找出有用到的 class, 有用到才生成該 css
    './index.html',
  ],
  theme: {
    extend: {
      // 新增自訂顏色、字型大小、間距
    },
  },
  plugins: [
  ],
}
