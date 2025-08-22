# Connect Jobs

<p align="center">
    <img src="/public/images/logo.svg" width="200" alt="Connect Jobs Logo">
</p>

<p align="center">
منصة الوظائف الرائدة التي تربط المواهب بالفرص
</p>

## حول Connect Jobs

Connect Jobs هي منصة وظائف عصرية مصممة لربط الشركات بأفضل المواهب والكفاءات. نحن نؤمن بأن العثور على الوظيفة المناسبة أو الموظف المثالي يجب أن يكون عملية سهلة وفعالة.

### الميزات الرئيسية

- **للشركات:**
  - إنشاء وإدارة الوظائف بسهولة
  - فلترة المتقدمين حسب المهارات والموقع
  - نظام تقييم ومطابقة ذكي
  - إدارة طلبات التوظيف

- **للباحثين عن عمل:**
  - إنشاء ملف شخصي مفصل
  - البحث عن الوظائف المناسبة
  - التقديم المباشر للوظائف
  - تتبع حالة الطلبات

### التقنيات المستخدمة

- **Backend:** Laravel 12
- **Frontend:** Blade Templates + Alpine.js
- **Styling:** Tailwind CSS + DaisyUI
- **Database:** MySQL
- **Authentication:** Laravel Breeze


## التثبيت والإعداد

### المتطلبات

- PHP 8.2 أو أحدث
- Composer
- Node.js & NPM
- MySQL

### خطوات التثبيت

1. **استنساخ المشروع:**
   ```bash
   git clone <repository-url>
   cd connect-jobs
   ```

2. **تثبيت التبعيات:**
   ```bash
   composer install
   npm install
   ```

3. **إعداد البيئة:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **إعداد قاعدة البيانات:**
   - قم بإنشاء قاعدة بيانات MySQL
   - حدث ملف `.env` بمعلومات قاعدة البيانات
   ```bash
   php artisan migrate --seed
   ```

5. **بناء الأصول:**
   ```bash
   npm run build
   ```

6. **تشغيل الخادم:**
   ```bash
   php artisan serve
   ```

## الاستخدام

### للشركات
1. سجل حساب جديد كشركة
2. أكمل ملف الشركة
3. ابدأ في إنشاء الوظائف
4. راجع المتقدمين وقم بإدارة الطلبات

### للباحثين عن عمل
1. سجل حساب جديد كباحث عن عمل
2. أكمل ملفك الشخصي
3. ابحث عن الوظائف المناسبة
4. قدم على الوظائف التي تهمك

## المساهمة

نرحب بمساهماتكم! يرجى اتباع الخطوات التالية:

1. Fork المشروع
2. أنشئ branch جديد للميزة
3. اكتب الكود مع التوثيق المناسب
4. اختبر التغييرات
5. أرسل Pull Request

## الترخيص

هذا المشروع مرخص تحت [MIT License](https://opensource.org/licenses/MIT).
# Connect-Jobs-V2
