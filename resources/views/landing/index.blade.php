<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Motrac - Smart Money Tracker</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: '#10B981', // Emerald 500
                        secondary: '#3B82F6', // Blue 500
                        dark: '#1E293B',
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans text-slate-800 bg-gray-50">

    <!-- Navbar -->
    <nav class="bg-white border-b border-gray-100 fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <!-- Logo -->
                <a href="{{ route('landing') }}" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center text-white font-bold">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <span class="text-xl font-bold text-dark tracking-tight">Motrac</span>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-8 text-sm font-medium text-gray-500">
                    <a href="#features" class="hover:text-primary transition">Fitur</a>
                    <a href="#faq" class="hover:text-primary transition">FAQ</a>
                </div>

                <!-- Auth Buttons -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-primary transition">Masuk</a>
                    <a href="{{ route('register') }}" class="bg-primary hover:bg-emerald-600 text-white text-sm font-medium px-5 py-2.5 rounded-full transition shadow-lg shadow-emerald-200">
                        Daftar Gratis
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto text-center lg:text-left grid lg:grid-cols-2 gap-12 items-center">
            <!-- Text Content -->
            <div class="space-y-6">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-dark leading-tight">
                    Atur Keuangan <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-blue-500">Tanpa Pusing.</span>
                </h1>
                <p class="text-lg text-gray-500 max-w-lg mx-auto lg:mx-0">
                    Lupakan catatan manual yang membosankan. Monitor arus kas, dompet digital, dan investasi Anda dalam satu dashboard pintar.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start pt-4">
                    <a href="{{ route('register') }}" class="inline-block bg-dark hover:bg-gray-800 text-white px-8 py-3.5 rounded-xl font-medium shadow-xl transition transform hover:-translate-y-1">
                        Mulai Tracking Sekarang
                    </a>
                    <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl font-medium text-gray-600 border border-gray-200 hover:bg-gray-50 transition">
                        Masuk Sekarang
                    </a>
                </div>
                <p class="text-sm text-gray-400">Gratis selamanya untuk fitur dasar. Tanpa kartu kredit.</p>
            </div>

            <!-- Visual Content (Mockup) -->
            <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-primary to-blue-600 rounded-2xl blur opacity-25 group-hover:opacity-40 transition duration-1000 group-hover:duration-200"></div>
                <div class="relative bg-white border border-gray-200 rounded-2xl shadow-2xl overflow-hidden">
                    <!-- Mockup Header -->
                    <div class="bg-gray-50 border-b border-gray-100 p-4 flex gap-2">
                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                        <div class="w-3 h-3 rounded-full bg-green-400"></div>
                    </div>
                    <!-- Mockup Body -->
                    <div class="p-6 grid gap-6 bg-white">
                        <div class="flex justify-between items-end">
                            <div>
                                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Total Balance</p>
                                <h3 class="text-3xl font-bold text-dark mt-1">Rp 15.450.000</h3>
                            </div>
                            <span class="bg-emerald-100 text-emerald-700 text-xs px-2 py-1 rounded font-bold">+12%</span>
                        </div>
                        <!-- Mock Bars -->
                        <div class="flex items-end gap-2 h-32 mt-4">
                            <div class="w-full bg-gray-100 rounded-t-lg h-[40%] hover:bg-primary transition-colors"></div>
                            <div class="w-full bg-gray-100 rounded-t-lg h-[60%] hover:bg-primary transition-colors"></div>
                            <div class="w-full bg-gray-100 rounded-t-lg h-[30%] hover:bg-primary transition-colors"></div>
                            <div class="w-full bg-primary rounded-t-lg h-[80%] shadow-lg shadow-emerald-200"></div>
                            <div class="w-full bg-gray-100 rounded-t-lg h-[50%] hover:bg-primary transition-colors"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Grid -->
    <section id="features" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl font-bold text-dark mb-4">Semua yang Anda butuhkan</h2>
                <p class="text-gray-500">Kami membuat fitur canggih menjadi sederhana, agar Anda bisa fokus pada tujuan finansial Anda.</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-500 mb-6 text-xl">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <h3 class="text-xl font-bold text-dark mb-3">Multi-Wallet Sync</h3>
                    <p class="text-gray-500 leading-relaxed">Kelola Tunai, BCA, Gopay, dan OVO dalam satu layar. Tidak perlu buka banyak aplikasi.</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-primary mb-6 text-xl">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <h3 class="text-xl font-bold text-dark mb-3">Smart Budgeting</h3>
                    <p class="text-gray-500 leading-relaxed">Pasang alarm batas belanja per kategori. Kami akan ingatkan sebelum dompet Anda jebol.</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="w-12 h-12 bg-rose-50 rounded-xl flex items-center justify-center text-rose-500 mb-6 text-xl">
                        <i class="fa-solid fa-file-invoice"></i>
                    </div>
                    <h3 class="text-xl font-bold text-dark mb-3">Visual Reports</h3>
                    <p class="text-gray-500 leading-relaxed">Pahami ke mana uang Anda pergi lewat grafik intuitif dan ekspor data ke Excel/PDF.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-20 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-dark mb-4">Pertanyaan yang Sering Diajukan</h2>
                <p class="text-gray-500">Temukan jawaban untuk pertanyaan umum tentang Motrac</p>
            </div>
            
            <div class="space-y-4">
                <!-- FAQ Item 1 -->
                <div class="bg-gray-50 rounded-xl border border-gray-100 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 flex justify-between items-center text-left hover:bg-gray-100 transition" onclick="toggleFaq(this)">
                        <span class="font-semibold text-dark">Apakah Motrac benar-benar gratis?</span>
                        <i class="fa-solid fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed">Ya, Motrac menawarkan fitur dasar secara gratis selamanya. Anda dapat mencatat transaksi, mengelola dompet, membuat kategori, dan melihat laporan dasar tanpa biaya apapun. Tidak diperlukan kartu kredit untuk memulai.</p>
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="bg-gray-50 rounded-xl border border-gray-100 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 flex justify-between items-center text-left hover:bg-gray-100 transition" onclick="toggleFaq(this)">
                        <span class="font-semibold text-dark">Bagaimana cara mengimpor data dari aplikasi lain?</span>
                        <i class="fa-solid fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed">Saat ini, Anda dapat mengimpor data secara manual melalui fitur Export/Import CSV. Kami sedang mengembangkan integrasi langsung dengan aplikasi keuangan populer. Untuk bantuan lebih lanjut, silakan hubungi tim support kami.</p>
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="bg-gray-50 rounded-xl border border-gray-100 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 flex justify-between items-center text-left hover:bg-gray-100 transition" onclick="toggleFaq(this)">
                        <span class="font-semibold text-dark">Apakah data saya aman dan terenkripsi?</span>
                        <i class="fa-solid fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed">Keamanan data adalah prioritas utama kami. Semua data Anda dienkripsi menggunakan teknologi SSL/TLS dan disimpan dengan aman. Kami tidak pernah membagikan informasi pribadi Anda kepada pihak ketiga tanpa izin.</p>
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="bg-gray-50 rounded-xl border border-gray-100 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 flex justify-between items-center text-left hover:bg-gray-100 transition" onclick="toggleFaq(this)">
                        <span class="font-semibold text-dark">Bisakah saya menggunakan Motrac di beberapa perangkat?</span>
                        <i class="fa-solid fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed">Tentu saja! Motrac adalah aplikasi berbasis web yang responsif, sehingga dapat diakses dari desktop, tablet, atau smartphone. Data Anda akan tersinkronisasi secara real-time di semua perangkat yang Anda gunakan untuk login.</p>
                    </div>
                </div>

                <!-- FAQ Item 5 -->
                <div class="bg-gray-50 rounded-xl border border-gray-100 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 flex justify-between items-center text-left hover:bg-gray-100 transition" onclick="toggleFaq(this)">
                        <span class="font-semibold text-dark">Apakah ada batasan jumlah transaksi yang bisa dicatat?</span>
                        <i class="fa-solid fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed">Tidak ada batasan jumlah transaksi untuk akun gratis. Anda dapat mencatat sebanyak mungkin transaksi yang Anda butuhkan. Semua fitur pencatatan, pelaporan, dan analisis tersedia tanpa batas.</p>
                    </div>
                </div>

                <!-- FAQ Item 6 -->
                <div class="bg-gray-50 rounded-xl border border-gray-100 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 flex justify-between items-center text-left hover:bg-gray-100 transition" onclick="toggleFaq(this)">
                        <span class="font-semibold text-dark">Bagaimana cara menghapus akun saya?</span>
                        <i class="fa-solid fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed">Anda dapat menghapus akun kapan saja melalui halaman Pengaturan. Setelah menghapus akun, semua data Anda akan dihapus secara permanen dan tidak dapat dipulihkan. Pastikan untuk mengekspor data penting terlebih dahulu jika diperlukan.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-white border-t border-gray-200 py-8">
        <div class="max-w-7xl mx-auto px-4 text-center text-gray-400 text-sm">
            &copy; 2024 Motrac Money Tracker. All rights reserved.
        </div>
    </footer>

    <!-- FAQ JavaScript -->
    <script>
        function toggleFaq(button) {
            const content = button.nextElementSibling;
            const icon = button.querySelector('i');
            const isOpen = !content.classList.contains('hidden');
            
            // Close all other FAQs
            document.querySelectorAll('.faq-content').forEach(item => {
                if (item !== content) {
                    item.classList.add('hidden');
                }
            });
            document.querySelectorAll('.faq-toggle i').forEach(item => {
                if (item !== icon) {
                    item.classList.remove('rotate-180');
                }
            });
            
            // Toggle current FAQ
            if (isOpen) {
                content.classList.add('hidden');
                icon.classList.remove('rotate-180');
            } else {
                content.classList.remove('hidden');
                icon.classList.add('rotate-180');
            }
        }

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>

</body>
</html>
