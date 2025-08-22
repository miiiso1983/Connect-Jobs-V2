# دليل النشر على Cloudways

## متطلبات السيرفر

- PHP 8.2 أو أحدث
- MySQL 8.0 أو أحدث
- Composer
- Node.js & NPM
- SSL Certificate

## خطوات النشر على Cloudways

### 1. إعداد السيرفر

1. **إنشاء تطبيق جديد:**
   - اختر PHP 8.2
   - اختر MySQL 8.0
   - حدد حجم السيرفر المناسب

2. **إعداد قاعدة البيانات:**
   ```sql
   CREATE DATABASE connect_jobs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

### 2. رفع الملفات

1. **استنساخ المشروع:**
   ```bash
   cd /home/master/applications/your-app/public_html
   git clone https://github.com/miiiso1983/Connect-Jobs-V2.git .
   ```

2. **تثبيت التبعيات:**
   ```bash
   composer install --optimize-autoloader --no-dev
   npm install
   npm run build
   ```

### 3. إعداد البيئة

1. **نسخ ملف البيئة:**
   ```bash
   cp .env.example .env
   ```

2. **تحديث ملف .env:**
   ```env
   APP_NAME="Connect Jobs"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.com
   
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=connect_jobs
   DB_USERNAME=your_db_user
   DB_PASSWORD=your_db_password
   
   MAIL_MAILER=smtp
   MAIL_HOST=your_smtp_host
   MAIL_PORT=587
   MAIL_USERNAME=your_email
   MAIL_PASSWORD=your_password
   MAIL_FROM_ADDRESS="noreply@your-domain.com"
   ```

3. **توليد مفتاح التطبيق:**
   ```bash
   php artisan key:generate
   ```

### 4. إعداد قاعدة البيانات

```bash
php artisan migrate --force
php artisan db:seed --force
```

### 5. إعداد الصلاحيات

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

### 6. إعداد Cron Jobs

أضف في crontab:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### 7. إعداد SSL

1. فعل SSL من لوحة تحكم Cloudways
2. تأكد من إعادة التوجيه من HTTP إلى HTTPS

### 8. تحسين الأداء

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## التحديثات المستقبلية

```bash
git pull origin main
composer install --optimize-autoloader --no-dev
npm install && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## استكشاف الأخطاء

### مشاكل شائعة:

1. **خطأ 500:**
   - تحقق من ملف `.env`
   - تحقق من صلاحيات المجلدات
   - راجع ملفات السجل

2. **مشاكل قاعدة البيانات:**
   - تأكد من صحة بيانات الاتصال
   - تحقق من وجود قاعدة البيانات

3. **مشاكل الأصول:**
   - تأكد من تشغيل `npm run build`
   - تحقق من مسار الأصول في `.env`

## الأمان

- تأكد من أن `APP_DEBUG=false` في الإنتاج
- استخدم HTTPS دائماً
- قم بتحديث التبعيات بانتظام
- راجع ملفات السجل دورياً
