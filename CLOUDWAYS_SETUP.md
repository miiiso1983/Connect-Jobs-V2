# دليل النشر على Cloudways - Connect Jobs V2

## 🚀 خطوات النشر السريع

### 1. إعداد السيرفر على Cloudways

1. **إنشاء تطبيق جديد:**
   - اختر **PHP 8.2**
   - اختر **MySQL 8.0**
   - حدد حجم السيرفر (1GB RAM كحد أدنى)
   - اختر المنطقة الجغرافية المناسبة

2. **الحصول على بيانات الاتصال:**
   - عنوان IP السيرفر
   - اسم المستخدم وكلمة المرور
   - بيانات قاعدة البيانات

### 2. الاتصال بالسيرفر

```bash
# الاتصال عبر SSH
ssh master@YOUR_SERVER_IP

# الانتقال لمجلد التطبيق
cd /home/master/applications/YOUR_APP_NAME/public_html
```

### 3. تحميل المشروع

```bash
# حذف الملفات الافتراضية
rm -rf *

# استنساخ المشروع من GitHub
git clone https://github.com/miiiso1983/Connect-Jobs-V2.git .

# إعطاء الصلاحيات المناسبة
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 4. تثبيت التبعيات

```bash
# تثبيت Composer dependencies
composer install --optimize-autoloader --no-dev

# تثبيت Node.js dependencies
npm install

# بناء الأصول
npm run build
```

### 5. إعداد البيئة

```bash
# نسخ ملف البيئة
cp .env.example .env

# تحرير ملف البيئة
nano .env
```

**محتوى ملف .env للإنتاج:**
```env
APP_NAME="Connect Jobs"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://your-domain.com

APP_LOCALE=ar
APP_FALLBACK_LOCALE=en

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=YOUR_DB_NAME
DB_USERNAME=YOUR_DB_USER
DB_PASSWORD=YOUR_DB_PASSWORD

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="Connect Jobs"

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

### 6. إعداد قاعدة البيانات

```bash
# توليد مفتاح التطبيق
php artisan key:generate

# تشغيل الهجرات
php artisan migrate --force

# تشغيل البذور (إنشاء المدير)
php artisan db:seed --force
```

### 7. تحسين الأداء

```bash
# تخزين التكوين مؤقتاً
php artisan config:cache

# تخزين المسارات مؤقتاً
php artisan route:cache

# تخزين العروض مؤقتاً
php artisan view:cache

# تحسين عام
php artisan optimize
```

### 8. إعداد SSL

1. في لوحة تحكم Cloudways:
   - اذهب إلى **SSL Certificate**
   - فعل **Let's Encrypt SSL**
   - أو ارفع شهادة SSL مخصصة

2. تأكد من إعادة التوجيه من HTTP إلى HTTPS

### 9. إعداد Cron Jobs

في لوحة تحكم Cloudways، أضف Cron Job:
```bash
* * * * * cd /home/master/applications/YOUR_APP_NAME/public_html && php artisan schedule:run >> /dev/null 2>&1
```

### 10. إعداد النطاق

1. في إعدادات النطاق، وجه النطاق إلى IP السيرفر
2. في Cloudways، أضف النطاق في **Domain Management**

## 🔧 إعدادات إضافية

### تحسين PHP

في **PHP Settings** في Cloudways:
```ini
memory_limit = 512M
max_execution_time = 300
upload_max_filesize = 64M
post_max_size = 64M
```

### إعداد Redis (اختياري)

```bash
# في ملف .env
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## 📊 مراقبة الأداء

### ملفات السجل

```bash
# مراقبة السجلات
tail -f storage/logs/laravel.log

# مراقبة سجلات الخادم
tail -f /var/log/nginx/error.log
```

### النسخ الاحتياطي

إعداد نسخ احتياطية تلقائية في Cloudways:
- نسخة احتياطية يومية لقاعدة البيانات
- نسخة احتياطية أسبوعية للملفات

## 🔄 التحديثات المستقبلية

```bash
# سحب التحديثات من GitHub
git pull origin main

# تحديث التبعيات
composer install --optimize-autoloader --no-dev
npm install && npm run build

# تشغيل الهجرات الجديدة
php artisan migrate --force

# إعادة تخزين التكوين مؤقتاً
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## 🆔 بيانات المدير الافتراضي

بعد تشغيل `php artisan db:seed`:
- **البريد الإلكتروني:** admin@connectjobs.com
- **كلمة المرور:** password123

⚠️ **مهم:** غير كلمة مرور المدير فوراً بعد أول تسجيل دخول!

## 🔗 روابط مفيدة

- **GitHub Repository:** https://github.com/miiiso1983/Connect-Jobs-V2
- **Cloudways Documentation:** https://support.cloudways.com/
- **Laravel Documentation:** https://laravel.com/docs

---

✅ **المشروع جاهز للنشر!** اتبع هذه الخطوات بالترتيب وستحصل على موقع Connect Jobs يعمل بكفاءة على Cloudways.
