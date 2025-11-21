# 檔案管理工具

這是一個基於 Laravel 與 Vue 所打造的前後端分離檔案管理工具

## 功能簡介

- 使用者 註冊 / 登入 / 登出 / 修改密碼、電子信箱
- 資料夾 新建/刪除
- 檔案
  - 上傳/下載/刪除
  - 建立/移除 共用連結

## 使用技術 

- 後端：Laravel 12
- 前端：Vue3、tailwind

## 環境建置

在開始建置前，請先安裝以下軟體與工具：
- php（8.2 - 8.4）
- Node.js（≥ 20.x LTS）
- Composer

### 1. 下載專案

```bash
git clone https://github.com/JY-Lin-035/FileManagement.git
cd FileManagement
```

### 2. Laravel

```bash
cd Laravel
cp .env.example .env
composer install
```

- 編輯 `.env` 檔案，設定以下項目：

```env
# 資料庫設定
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=your_port
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# 電子信箱（寄送驗證碼等）
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=your_email_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=your_email
MAIL_FROM_NAME="your_name"
```

- 建立資料表
```bash
php artisan migrate
```

- 啟動 Laravel
```bash
php artisan serve
```
或
```bash
php -S localhost:8000 -t public
```

- 啟動寄信佇列（於新視窗）
```bash
php artisan queue:work
```

### 3. Vue

```bash
cd Vue
npm install
```

- 啟動 Vue
```bash
npm run dev
```