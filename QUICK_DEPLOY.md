# 🚀 النشر السريع - Connect Jobs V2

## خطوات النشر على Cloudways (5 دقائق)

### 1. إعداد السيرفر
```bash
# في لوحة تحكم Cloudways:
# - إنشاء تطبيق PHP 8.2 + MySQL 8.0
# - تسجيل بيانات الاتصال
```

### 2. الاتصال والتحميل
```bash
# SSH إلى السيرفر
ssh master@YOUR_SERVER_IP

# الانتقال لمجلد التطبيق
cd /home/master/applications/YOUR_APP/public_html

# حذف الملفات الافتراضية
rm -rf *

# تحميل المشروع
git clone https://github.com/miiiso1983/Connect-Jobs-V2.git .
```

### 3. النشر التلقائي
```bash
# تشغيل سكريبت النشر
./deploy.sh
```

### 4. إعداد .env
```bash
# تحرير ملف البيئة
nano .env

# تحديث البيانات التالية:
APP_URL=https://your-domain.com
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

### 5. إعداد النطاق والSSL
```bash
# في لوحة تحكم Cloudways:
# - إضافة النطاق في Domain Management
# - تفعيل SSL Certificate
```

### 6. Cron Job
```bash
# إضافة في Cloudways Cron Jobs:
* * * * * cd /home/master/applications/YOUR_APP/public_html && php artisan schedule:run >> /dev/null 2>&1
```

## ✅ تم! الموقع جاهز

**بيانات المدير:**
- البريد: admin@connectjobs.com  
- كلمة المرور: password123

⚠️ **غير كلمة المرور فوراً!**

## 🔗 الروابط
- **GitHub:** https://github.com/miiiso1983/Connect-Jobs-V2
- **التوثيق الكامل:** CLOUDWAYS_SETUP.md
