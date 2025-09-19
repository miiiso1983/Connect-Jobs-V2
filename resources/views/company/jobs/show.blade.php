{{-- Legacy content (accidentally committed) is commented out below. Clean template follows after this block. --}}
{{--

# إنشاء صفحة ترحيب جديدة وجذابة
cat > resources/views/welcome.blade.php << 'EOF'
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connect Jobs - منصة الوظائف الرائدة</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- DaisyUI + Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { font-family: 'Cairo', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-hover { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="navbar bg-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto">
            <div class="flex-1">
                <a href="/" class="btn btn-ghost text-xl font-bold text-primary">
                    <i class="fas fa-briefcase ml-2"></i>
                    Connect Jobs
                </a>
            </div>
            <div class="flex-none">
                <ul class="menu menu-horizontal px-1">
                    <li><a href="/jobs" class="font-medium">الوظائف</a></li>
                    <li><a href="/companies" class="font-medium">الشركات</a></li>
                    <li><a href="/login" class="btn btn-outline btn-primary btn-sm">تسجيل الدخول</a></li>
                    <li><a href="/register" class="btn btn-primary btn-sm mr-2">إنشاء حساب</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="gradient-bg text-white py-20">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-5xl md:text-6xl font-bold mb-6">
                اعثر على وظيفة أحلامك
            </h1>
            <p class="text-xl md:text-2xl mb-8 opacity-90">
                منصة الوظائف الرائدة التي تربط المواهب بأفضل الفرص
            </p>

            <!-- Search Box -->
            <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-2xl p-6">
                <form class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text" placeholder="ابحث عن وظيفة..."
                               class="input input-bordered w-full text-gray-800 text-lg">
                    </div>
                    <div class="flex-1">
                        <select class="select select-bordered w-full text-gray-800 text-lg">
                            <option>اختر المدينة</option>
                            <option>الرياض</option>
                            <option>جدة</option>
                            <option>الدمام</option>
                            <option>مكة</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg px-8">
                        <i class="fas fa-search ml-2"></i>
                        بحث
                    </button>
                </form>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mt-16">
                <div class="text-center">
                    <div class="text-4xl font-bold">1000+</div>
                    <div class="text-lg opacity-80">وظيفة متاحة</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold">500+</div>
                    <div class="text-lg opacity-80">شركة</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold">5000+</div>
                    <div class="text-lg opacity-80">باحث عن عمل</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold">95%</div>
                    <div class="text-lg opacity-80">معدل النجاح</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">لماذا Connect Jobs؟</h2>
                <p class="text-xl text-gray-600">نحن نقدم أفضل تجربة للباحثين عن عمل والشركات</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="card bg-base-100 shadow-xl card-hover">
                    <div class="card-body text-center">
                        <div class="text-5xl text-primary mb-4">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="card-title justify-center text-2xl mb-4">بحث ذكي</h3>
                        <p class="text-gray-600">
                            نظام بحث متطور يساعدك في العثور على الوظائف المناسبة لمهاراتك وخبراتك
                        </p>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-xl card-hover">
                    <div class="card-body text-center">
                        <div class="text-5xl text-primary mb-4">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h3 class="card-title justify-center text-2xl mb-4">مطابقة دقيقة</h3>
                        <p class="text-gray-600">
                            نربط بين المواهب والشركات بناءً على التوافق في المهارات والمتطلبات
                        </p>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-xl card-hover">
                    <div class="card-body text-center">
                        <div class="text-5xl text-primary mb-4">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h3 class="card-title justify-center text-2xl mb-4">نمو مهني</h3>
                        <p class="text-gray-600">
                            نساعدك في تطوير مسارك المهني من خلال فرص عمل متنوعة ونصائح مهنية
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Latest Jobs Section -->
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">أحدث الوظائف</h2>
                <p class="text-xl text-gray-600">اكتشف أحدث الفرص الوظيفية المتاحة</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Job Card 1 -->
                <div class="card bg-white shadow-lg card-hover">
                    <div class="card-body">
                        <div class="flex items-center mb-4">
                            <div class="avatar placeholder ml-4">
                                <div class="bg-primary text-white rounded-full w-12">
                                    <span class="text-xl">T</span>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg">مطور ويب</h3>
                                <p class="text-gray-600">شركة التقنية</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="badge badge-primary">دوام كامل</span>
                            <span class="badge badge-outline">الرياض</span>
                        </div>
                        <p class="text-gray-600 mb-4">نبحث عن مطور ويب محترف للانضمام لفريقنا...</p>
                        <div class="card-actions justify-between items-center">
                            <span class="text-primary font-bold">5000 - 8000 ريال</span>
                            <button class="btn btn-primary btn-sm">تقدم الآن</button>
                        </div>
                    </div>
                </div>

                <!-- Job Card 2 -->
                <div class="card bg-white shadow-lg card-hover">
                    <div class="card-body">
                        <div class="flex items-center mb-4">
                            <div class="avatar placeholder ml-4">
                                <div class="bg-secondary text-white rounded-full w-12">
                                    <span class="text-xl">M</span>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg">مصمم جرافيك</h3>
                                <p class="text-gray-600">وكالة الإبداع</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="badge badge-secondary">دوام جزئي</span>
                            <span class="badge badge-outline">جدة</span>
                        </div>
                        <p class="text-gray-600 mb-4">مطلوب مصمم جرافيك مبدع للعمل على مشاريع متنوعة...</p>
                        <div class="card-actions justify-between items-center">
                            <span class="text-primary font-bold">3000 - 5000 ريال</span>
                            <button class="btn btn-primary btn-sm">تقدم الآن</button>
                        </div>
                    </div>
                </div>

                <!-- Job Card 3 -->
                <div class="card bg-white shadow-lg card-hover">
                    <div class="card-body">
                        <div class="flex items-center mb-4">
                            <div class="avatar placeholder ml-4">
                                <div class="bg-accent text-white rounded-full w-12">
                                    <span class="text-xl">S</span>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg">مختص تسويق</h3>
                                <p class="text-gray-600">شركة النمو</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="badge badge-accent">دوام كامل</span>
                            <span class="badge badge-outline">الدمام</span>
                        </div>
                        <p class="text-gray-600 mb-4">نبحث عن مختص تسويق رقمي لتطوير استراتيجياتنا...</p>
                        <div class="card-actions justify-between items-center">
                            <span class="text-primary font-bold">6000 - 9000 ريال</span>
                            <button class="btn btn-primary btn-sm">تقدم الآن</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-12">
                <a href="/jobs" class="btn btn-primary btn-lg">
                    عرض جميع الوظائف
                    <i class="fas fa-arrow-left mr-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="gradient-bg text-white py-20">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl font-bold mb-6">ابدأ رحلتك المهنية اليوم</h2>
            <p class="text-xl mb-8 opacity-90">
                انضم إلى آلاف الباحثين عن عمل الذين وجدوا وظائف أحلامهم معنا
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/register?type=jobseeker" class="btn btn-white btn-lg">
                    <i class="fas fa-user ml-2"></i>
                    سجل كباحث عن عمل
                </a>
                <a href="/register?type=company" class="btn btn-outline btn-lg text-white border-white hover:bg-white hover:text-primary">
                    <i class="fas fa-building ml-2"></i>
                    سجل كشركة
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">Connect Jobs</h3>
                    <p class="text-gray-400">
                        منصة الوظائف الرائدة التي تربط المواهب بأفضل الفرص في السوق
                    </p>
                </div>
                <div>
                    <h4 class="font-bold mb-4">روابط سريعة</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="/jobs" class="hover:text-white">الوظائف</a></li>
                        <li><a href="/companies" class="hover:text-white">الشركات</a></li>
                        <li><a href="/about" class="hover:text-white">من نحن</a></li>
                        <li><a href="/contact" class="hover:text-white">اتصل بنا</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">للباحثين عن عمل</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="/register" class="hover:text-white">إنشاء حساب</a></li>
                        <li><a href="/jobs" class="hover:text-white">تصفح الوظائف</a></li>
                        <li><a href="/profile" class="hover:text-white">الملف الشخصي</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">للشركات</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="/register?type=company" class="hover:text-white">سجل شركتك</a></li>
                        <li><a href="/post-job" class="hover:text-white">انشر وظيفة</a></li>
                        <li><a href="/pricing" class="hover:text-white">الأسعار</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2025 Connect Jobs. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </footer>
</body>
--}}

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold">{{ $job->title }}</h1>
                <div class="text-sm text-gray-600">{{ $job->province }} · <span class="uppercase">#{{ $job->id }}</span></div>
            </div>
            <span class="badge {{ $job->status==='open' ? 'badge-success' : 'badge-ghost' }}">{{ $job->status }}</span>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-3">
                <h2 class="font-semibold">تفاصيل الوظيفة</h2>
                <div class="text-gray-700 dark:text-gray-200">
                    @if($job->requirements)
                        <div class="mt-2">
                            <h3 class="font-semibold">المتطلبات</h3>
                            <p class="whitespace-pre-line">{{ $job->requirements }}</p>
                        </div>
                    @endif
                    <div class="mt-2">
                        <h3 class="font-semibold">الوصف</h3>
                        <p class="whitespace-pre-line">{{ $job->description }}</p>
                    </div>
                    @if($job->jd_file)
                        <div class="mt-3">
                            <a href="{{ Storage::url($job->jd_file) }}" class="link link-primary" target="_blank">تحميل الوصف الوظيفي (PDF/DOC)</a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                <h2 class="font-semibold mb-3">أحدث المتقدمين</h2>
                @if($latestApplicants->isEmpty())
                    <div class="text-gray-500">لا يوجد متقدمون مؤخراً.</div>
                @else
                    <div class="divide-y divide-base-200">
                        @foreach($latestApplicants as $app)
                            <div class="py-3 flex items-center justify-between">
                                <div>
                                    <div class="font-medium">{{ $app->jobSeeker->full_name ?? 'باحث' }}</div>
                                    <div class="text-xs text-gray-500">تاريخ التقديم: {{ $app->applied_at?->format('Y-m-d H:i') }}</div>
                                </div>
                                @if($app->matching_percentage !== null)
                                    <span class="badge badge-outline">التطابق {{ (int) $app->matching_percentage }}%</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <aside class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                <h3 class="font-semibold mb-2">ملخص</h3>
                <ul class="text-sm space-y-1">
                    <li>الحالة: <span class="badge {{ $job->status==='open' ? 'badge-success' : 'badge-ghost' }}">{{ $job->status }}</span></li>
                    <li>المحافظة: {{ $job->province }}</li>
                    <li>آخر تحديث: {{ $job->updated_at?->format('Y-m-d') }}</li>
                </ul>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                <h3 class="font-semibold mb-2">إحصاءات سريعة</h3>
                <div class="text-sm space-y-2">
                    <div>متوسط التطابق: <span class="font-semibold">{{ number_format($stats['avg_match'] ?? 0, 0) }}%</span></div>
                    <div class="mt-2">
                        <div class="font-semibold">الأكثر حسب المحافظة</div>
                        <ul class="list-disc mr-5 mt-1">
                            @foreach(($stats['by_province'] ?? []) as $row)
                                <li>{{ $row->province ?: 'غير محدد' }} ({{ $row->c }})</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="mt-2">
                        <div class="font-semibold">الأكثر حسب التخصص</div>
                        <ul class="list-disc mr-5 mt-1">
                            @foreach(($stats['by_speciality'] ?? []) as $row)
                                <li>{{ $row->speciality ?: 'غير محدد' }} ({{ $row->c }})</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</x-app-layout>

