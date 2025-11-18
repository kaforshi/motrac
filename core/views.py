from django.shortcuts import render, redirect
from django.views.generic import TemplateView, CreateView, ListView, UpdateView, DeleteView, FormView
from django.contrib.auth import login
from django.contrib.auth.mixins import LoginRequiredMixin
from django.contrib import messages
from django.urls import reverse_lazy
from django.db.models import Sum, Q
from django.utils import timezone
from django.utils.safestring import mark_safe
from datetime import datetime, date, timedelta
from decimal import Decimal
import json
from .forms import CustomUserCreationForm, TransactionForm, TransferForm, CategoryForm
from .models import Transaction, Account, Category


def ensure_default_categories(user):
    """Memastikan user memiliki kategori default"""
    # Cek apakah user sudah punya kategori
    if Category.objects.filter(user=user).exists():
        return
    
    # Kategori default untuk Pemasukan
    default_income_categories = [
        'Gaji',
        'Bonus',
        'Investasi',
        'Lainnya'
    ]
    
    # Kategori default untuk Pengeluaran
    default_expense_categories = [
        'Makanan & Minuman',
        'Transportasi',
        'Belanja',
        'Hiburan',
        'Tagihan',
        'Kesehatan',
        'Pendidikan',
        'Lainnya'
    ]
    
    # Buat kategori pemasukan
    for category_name in default_income_categories:
        Category.objects.get_or_create(
            user=user,
            name=category_name,
            type='Pemasukan',
            defaults={'name': category_name, 'type': 'Pemasukan'}
        )
    
    # Buat kategori pengeluaran
    for category_name in default_expense_categories:
        Category.objects.get_or_create(
            user=user,
            name=category_name,
            type='Pengeluaran',
            defaults={'name': category_name, 'type': 'Pengeluaran'}
        )


class LandingPageView(TemplateView):
    template_name = 'pages/landing_page.html'


def signup_view(request):
    """View untuk registrasi pengguna baru"""
    if request.method == 'POST':
        form = CustomUserCreationForm(request.POST)
        if form.is_valid():
            user = form.save()
            login(request, user)
            messages.success(request, 'Registrasi berhasil! Selamat datang di Motrac.')
            return redirect('core:dashboard')
        else:
            messages.error(request, 'Terjadi kesalahan saat registrasi. Silakan periksa kembali data Anda.')
    else:
        form = CustomUserCreationForm()
    
    return render(request, 'registration/signup.html', {'form': form})


class TransactionCreateView(LoginRequiredMixin, CreateView):
    """View untuk membuat transaksi baru"""
    model = Transaction
    form_class = TransactionForm
    template_name = 'pages/dashboard.html'
    success_url = reverse_lazy('dashboard')
    
    def get_form_kwargs(self):
        """Pass user ke form untuk filter account dan category"""
        kwargs = super().get_form_kwargs()
        kwargs['user'] = self.request.user
        return kwargs
    
    def form_valid(self, form):
        """Isi field user secara otomatis"""
        form.instance.user = self.request.user
        messages.success(self.request, 'Transaksi berhasil ditambahkan!')
        return super().form_valid(form)


class TransactionListView(LoginRequiredMixin, ListView):
    """View untuk menampilkan daftar transaksi"""
    model = Transaction
    template_name = 'pages/dashboard.html'
    context_object_name = 'transactions'
    paginate_by = 10
    
    def get_queryset(self):
        """Filter transaksi berdasarkan user yang login"""
        return Transaction.objects.filter(user=self.request.user).select_related('account', 'category')


class DashboardView(LoginRequiredMixin, TemplateView):
    """View untuk dashboard yang menggabungkan form create dan list transaksi"""
    template_name = 'pages/dashboard.html'
    
    def get_context_data(self, **kwargs):
        context = super().get_context_data(**kwargs)
        user = self.request.user
        
        # Pastikan user punya kategori default
        ensure_default_categories(user)
        
        # Tambahkan form untuk create transaction
        context['form'] = TransactionForm(user=user)
        
        # Tambahkan list transaksi
        context['transactions'] = Transaction.objects.filter(
            user=user
        ).select_related('account', 'category')[:10]  # Limit 10 terakhir
        
        # Hitung total balance dari semua akun
        accounts = Account.objects.filter(user=user)
        total_initial_balance = accounts.aggregate(
            total=Sum('initial_balance')
        )['total'] or Decimal('0.00')
        
        # Hitung total pemasukan dan pengeluaran dari transaksi
        total_income_all = Transaction.objects.filter(
            user=user, type='Pemasukan'
        ).aggregate(total=Sum('amount'))['total'] or Decimal('0.00')
        
        total_expense_all = Transaction.objects.filter(
            user=user, type='Pengeluaran'
        ).aggregate(total=Sum('amount'))['total'] or Decimal('0.00')
        
        context['total_balance'] = total_initial_balance + total_income_all - total_expense_all
        
        # Hitung total pemasukan dan pengeluaran bulan ini
        now = timezone.now()
        start_of_month = datetime(now.year, now.month, 1).date()
        # Hitung akhir bulan (tanggal 1 bulan berikutnya)
        if now.month == 12:
            end_of_month = datetime(now.year + 1, 1, 1).date()
        else:
            end_of_month = datetime(now.year, now.month + 1, 1).date()
        
        total_income_month = Transaction.objects.filter(
            user=user,
            type='Pemasukan',
            date__gte=start_of_month,
            date__lt=end_of_month
        ).aggregate(total=Sum('amount'))['total'] or Decimal('0.00')
        
        total_expense_month = Transaction.objects.filter(
            user=user,
            type='Pengeluaran',
            date__gte=start_of_month,
            date__lt=end_of_month
        ).aggregate(total=Sum('amount'))['total'] or Decimal('0.00')
        
        context['total_income_month'] = total_income_month
        context['total_expense_month'] = total_expense_month
        
        # Siapkan data untuk Chart 1 Bulan Terakhir (per hari)
        monthly_labels = []
        monthly_income = []
        monthly_expense = []
        
        # Hitung 30 hari terakhir
        today = now.date()
        for i in range(29, -1, -1):  # 30 hari terakhir
            target_date = today - timedelta(days=i)
            date_start = target_date
            date_end = target_date + timedelta(days=1)
            
            # Label: format tanggal
            date_label = target_date.strftime('%d %b')
            monthly_labels.append(date_label)
            
            # Hitung pemasukan hari ini
            income = Transaction.objects.filter(
                user=user,
                type='Pemasukan',
                date__gte=date_start,
                date__lt=date_end
            ).aggregate(total=Sum('amount'))['total'] or Decimal('0.00')
            monthly_income.append(float(income))
            
            # Hitung pengeluaran hari ini
            expense = Transaction.objects.filter(
                user=user,
                type='Pengeluaran',
                date__gte=date_start,
                date__lt=date_end
            ).aggregate(total=Sum('amount'))['total'] or Decimal('0.00')
            monthly_expense.append(float(expense))
        
        context['monthly_labels'] = mark_safe(json.dumps(monthly_labels))
        context['monthly_income'] = mark_safe(json.dumps(monthly_income))
        context['monthly_expense'] = mark_safe(json.dumps(monthly_expense))
        
        # Siapkan data untuk Chart 1 Tahun Terakhir (per bulan)
        yearly_labels = []
        yearly_income = []
        yearly_expense = []
        
        # Hitung 12 bulan terakhir (1 tahun)
        for i in range(11, -1, -1):  # 12 bulan terakhir
            # Hitung bulan target (i bulan yang lalu dari bulan ini)
            if now.month - i <= 0:
                target_month = now.month - i + 12
                target_year = now.year - 1
            else:
                target_month = now.month - i
                target_year = now.year
            
            month_start = datetime(target_year, target_month, 1).date()
            if target_month == 12:
                month_end = datetime(target_year + 1, 1, 1).date()
            else:
                month_end = datetime(target_year, target_month + 1, 1).date()
            
            # Nama bulan
            month_name = month_start.strftime('%b %Y')
            yearly_labels.append(month_name)
            
            # Hitung pemasukan bulan ini
            income = Transaction.objects.filter(
                user=user,
                type='Pemasukan',
                date__gte=month_start,
                date__lt=month_end
            ).aggregate(total=Sum('amount'))['total'] or Decimal('0.00')
            yearly_income.append(float(income))
            
            # Hitung pengeluaran bulan ini
            expense = Transaction.objects.filter(
                user=user,
                type='Pengeluaran',
                date__gte=month_start,
                date__lt=month_end
            ).aggregate(total=Sum('amount'))['total'] or Decimal('0.00')
            yearly_expense.append(float(expense))
        
        context['yearly_labels'] = mark_safe(json.dumps(yearly_labels))
        context['yearly_income'] = mark_safe(json.dumps(yearly_income))
        context['yearly_expense'] = mark_safe(json.dumps(yearly_expense))
        
        # Siapkan data untuk Chart per Dompet (Bulan Ini)
        accounts = Account.objects.filter(user=user)
        account_chart_data_monthly = []
        
        for account in accounts:
            # Hitung pemasukan dan pengeluaran untuk dompet ini (bulan ini)
            account_income = Transaction.objects.filter(
                user=user,
                account=account,
                type='Pemasukan',
                date__gte=start_of_month,
                date__lt=end_of_month
            ).aggregate(total=Sum('amount'))['total'] or Decimal('0.00')
            
            account_expense = Transaction.objects.filter(
                user=user,
                account=account,
                type='Pengeluaran',
                date__gte=start_of_month,
                date__lt=end_of_month
            ).aggregate(total=Sum('amount'))['total'] or Decimal('0.00')
            
            account_chart_data_monthly.append({
                'name': account.name,
                'income': float(account_income),
                'expense': float(account_expense)
            })
        
        # Siapkan data untuk Chart per Dompet (Tahun Ini)
        year_start = datetime(now.year, 1, 1).date()
        year_end = datetime(now.year + 1, 1, 1).date()
        
        account_chart_data_yearly = []
        for account in accounts:
            # Hitung pemasukan dan pengeluaran untuk dompet ini (tahun ini)
            account_income = Transaction.objects.filter(
                user=user,
                account=account,
                type='Pemasukan',
                date__gte=year_start,
                date__lt=year_end
            ).aggregate(total=Sum('amount'))['total'] or Decimal('0.00')
            
            account_expense = Transaction.objects.filter(
                user=user,
                account=account,
                type='Pengeluaran',
                date__gte=year_start,
                date__lt=year_end
            ).aggregate(total=Sum('amount'))['total'] or Decimal('0.00')
            
            account_chart_data_yearly.append({
                'name': account.name,
                'income': float(account_income),
                'expense': float(account_expense)
            })
        
        context['account_chart_data_monthly'] = mark_safe(json.dumps(account_chart_data_monthly))
        context['account_chart_data_yearly'] = mark_safe(json.dumps(account_chart_data_yearly))
        
        # Siapkan data untuk Chart per Kategori (Bulan Ini)
        categories = Category.objects.filter(user=user)
        category_chart_data_monthly = []
        
        for category in categories:
            # Hitung pemasukan atau pengeluaran untuk kategori ini (bulan ini)
            category_total = Transaction.objects.filter(
                user=user,
                category=category,
                date__gte=start_of_month,
                date__lt=end_of_month
            ).aggregate(total=Sum('amount'))['total'] or Decimal('0.00')
            
            category_chart_data_monthly.append({
                'name': category.name,
                'type': category.type,
                'total': float(category_total)
            })
        
        # Siapkan data untuk Chart per Kategori (Tahun Ini)
        category_chart_data_yearly = []
        for category in categories:
            # Hitung pemasukan atau pengeluaran untuk kategori ini (tahun ini)
            category_total = Transaction.objects.filter(
                user=user,
                category=category,
                date__gte=year_start,
                date__lt=year_end
            ).aggregate(total=Sum('amount'))['total'] or Decimal('0.00')
            
            category_chart_data_yearly.append({
                'name': category.name,
                'type': category.type,
                'total': float(category_total)
            })
        
        context['category_chart_data_monthly'] = mark_safe(json.dumps(category_chart_data_monthly))
        context['category_chart_data_yearly'] = mark_safe(json.dumps(category_chart_data_yearly))
        
        # Siapkan data untuk dropdown filter
        # Data semua dompet untuk dropdown
        accounts_list = [{'id': acc.id, 'name': acc.name} for acc in accounts]
        context['accounts_list'] = mark_safe(json.dumps(accounts_list))
        
        # Data semua kategori untuk dropdown
        categories_list = [{'id': cat.id, 'name': cat.name, 'type': cat.type} for cat in categories]
        context['categories_list'] = mark_safe(json.dumps(categories_list))
        
        # Siapkan data detail untuk chart per dompet (dengan filter kategori)
        # Data per dompet per kategori untuk bulan ini
        account_category_data_monthly = {}
        for account in accounts:
            account_category_data_monthly[account.id] = {}
            for category in categories:
                income = Transaction.objects.filter(
                    user=user,
                    account=account,
                    category=category,
                    type='Pemasukan',
                    date__gte=start_of_month,
                    date__lt=end_of_month
                ).aggregate(total=Sum('amount'))['total'] or Decimal('0.00')
                
                expense = Transaction.objects.filter(
                    user=user,
                    account=account,
                    category=category,
                    type='Pengeluaran',
                    date__gte=start_of_month,
                    date__lt=end_of_month
                ).aggregate(total=Sum('amount'))['total'] or Decimal('0.00')
                
                account_category_data_monthly[account.id][category.id] = {
                    'income': float(income),
                    'expense': float(expense)
                }
        
        # Data per dompet per kategori untuk tahun ini
        account_category_data_yearly = {}
        for account in accounts:
            account_category_data_yearly[account.id] = {}
            for category in categories:
                income = Transaction.objects.filter(
                    user=user,
                    account=account,
                    category=category,
                    type='Pemasukan',
                    date__gte=year_start,
                    date__lt=year_end
                ).aggregate(total=Sum('amount'))['total'] or Decimal('0.00')
                
                expense = Transaction.objects.filter(
                    user=user,
                    account=account,
                    category=category,
                    type='Pengeluaran',
                    date__gte=year_start,
                    date__lt=year_end
                ).aggregate(total=Sum('amount'))['total'] or Decimal('0.00')
                
                account_category_data_yearly[account.id][category.id] = {
                    'income': float(income),
                    'expense': float(expense)
                }
        
        context['account_category_data_monthly'] = mark_safe(json.dumps(account_category_data_monthly))
        context['account_category_data_yearly'] = mark_safe(json.dumps(account_category_data_yearly))
        
        # Siapkan data detail untuk chart per kategori (dengan filter dompet)
        # Data per kategori per dompet untuk bulan ini
        category_account_data_monthly = {}
        for category in categories:
            category_account_data_monthly[category.id] = {}
            for account in accounts:
                total = Transaction.objects.filter(
                    user=user,
                    account=account,
                    category=category,
                    date__gte=start_of_month,
                    date__lt=end_of_month
                ).aggregate(total=Sum('amount'))['total'] or Decimal('0.00')
                
                category_account_data_monthly[category.id][account.id] = {
                    'total': float(total)
                }
        
        # Data per kategori per dompet untuk tahun ini
        category_account_data_yearly = {}
        for category in categories:
            category_account_data_yearly[category.id] = {}
            for account in accounts:
                total = Transaction.objects.filter(
                    user=user,
                    account=account,
                    category=category,
                    date__gte=year_start,
                    date__lt=year_end
                ).aggregate(total=Sum('amount'))['total'] or Decimal('0.00')
                
                category_account_data_yearly[category.id][account.id] = {
                    'total': float(total)
                }
        
        context['category_account_data_monthly'] = mark_safe(json.dumps(category_account_data_monthly))
        context['category_account_data_yearly'] = mark_safe(json.dumps(category_account_data_yearly))
        
        return context
    
    def post(self, request, *args, **kwargs):
        """Handle POST request untuk create transaction"""
        form = TransactionForm(request.POST, user=request.user)
        if form.is_valid():
            transaction = form.save(commit=False)
            transaction.user = request.user
            transaction.save()
            messages.success(request, 'Transaksi berhasil ditambahkan!')
            return redirect('core:dashboard')
        else:
            # Jika form tidak valid, tampilkan error dan render ulang
            context = self.get_context_data()
            context['form'] = form
            return render(request, self.template_name, context)


# Account CRUD Views
class AccountListView(LoginRequiredMixin, ListView):
    """View untuk menampilkan daftar akun/dompet"""
    model = Account
    template_name = 'accounts/account_list.html'
    context_object_name = 'accounts'
    
    def get_queryset(self):
        """Filter akun berdasarkan user yang login"""
        return Account.objects.filter(user=self.request.user)


class AccountCreateView(LoginRequiredMixin, CreateView):
    """View untuk membuat akun/dompet baru"""
    model = Account
    template_name = 'accounts/account_form.html'
    fields = ['name', 'initial_balance']
    success_url = reverse_lazy('core:account_list')
    
    def form_valid(self, form):
        """Isi field user secara otomatis"""
        form.instance.user = self.request.user
        messages.success(self.request, 'Dompet berhasil ditambahkan!')
        return super().form_valid(form)


class AccountUpdateView(LoginRequiredMixin, UpdateView):
    """View untuk mengedit akun/dompet"""
    model = Account
    template_name = 'accounts/account_form.html'
    fields = ['name', 'initial_balance']
    success_url = reverse_lazy('core:account_list')
    
    def get_queryset(self):
        """Hanya user yang punya akun ini yang bisa edit"""
        return Account.objects.filter(user=self.request.user)
    
    def form_valid(self, form):
        """Tampilkan pesan sukses setelah update"""
        messages.success(self.request, 'Dompet berhasil diperbarui!')
        return super().form_valid(form)


class AccountDeleteView(LoginRequiredMixin, DeleteView):
    """View untuk menghapus akun/dompet"""
    model = Account
    template_name = 'accounts/account_confirm_delete.html'
    success_url = reverse_lazy('core:account_list')
    
    def get_queryset(self):
        """Hanya user yang punya akun ini yang bisa hapus"""
        return Account.objects.filter(user=self.request.user)
    
    def delete(self, request, *args, **kwargs):
        """Tampilkan pesan sukses setelah delete"""
        messages.success(self.request, 'Dompet berhasil dihapus!')
        return super().delete(request, *args, **kwargs)


class TransferView(LoginRequiredMixin, FormView):
    """View untuk transfer antar akun"""
    template_name = 'accounts/transfer_form.html'
    form_class = TransferForm
    success_url = reverse_lazy('core:dashboard')
    
    def get_form_kwargs(self):
        """Pass user ke form untuk filter account"""
        kwargs = super().get_form_kwargs()
        kwargs['user'] = self.request.user
        return kwargs
    
    def form_valid(self, form):
        """Proses transfer: buat 2 transaksi"""
        from_account = form.cleaned_data['from_account']
        to_account = form.cleaned_data['to_account']
        amount = form.cleaned_data['amount']
        description = form.cleaned_data.get('description', '')
        
        user = self.request.user
        today = date.today()
        
        # Dapatkan atau buat kategori "Transfer"
        transfer_category_expense, _ = Category.objects.get_or_create(
            user=user,
            name='Transfer',
            type='Pengeluaran',
            defaults={'name': 'Transfer', 'type': 'Pengeluaran'}
        )
        
        transfer_category_income, _ = Category.objects.get_or_create(
            user=user,
            name='Transfer',
            type='Pemasukan',
            defaults={'name': 'Transfer', 'type': 'Pemasukan'}
        )
        
        # Buat transaksi pengeluaran dari from_account
        expense_transaction = Transaction.objects.create(
            user=user,
            account=from_account,
            category=transfer_category_expense,
            amount=amount,
            type='Pengeluaran',
            date=today,
            description=f'Transfer ke {to_account.name}. {description}' if description else f'Transfer ke {to_account.name}'
        )
        
        # Buat transaksi pemasukan ke to_account
        income_transaction = Transaction.objects.create(
            user=user,
            account=to_account,
            category=transfer_category_income,
            amount=amount,
            type='Pemasukan',
            date=today,
            description=f'Transfer dari {from_account.name}. {description}' if description else f'Transfer dari {from_account.name}'
        )
        
        messages.success(
            self.request,
            f'Transfer berhasil! Rp {amount:.2f} dari {from_account.name} ke {to_account.name}.'
        )
        
        return super().form_valid(form)


# Category Views
class CategoryListView(LoginRequiredMixin, ListView):
    """View untuk menampilkan daftar kategori"""
    model = Category
    template_name = 'pages/category_list.html'
    context_object_name = 'categories'
    
    def get_queryset(self):
        """Filter kategori berdasarkan user yang login"""
        # Pastikan user punya kategori default
        ensure_default_categories(self.request.user)
        return Category.objects.filter(user=self.request.user)


class CategoryCreateView(LoginRequiredMixin, CreateView):
    """View untuk membuat kategori baru"""
    model = Category
    form_class = CategoryForm
    template_name = 'pages/category_form.html'
    success_url = reverse_lazy('core:category-list')
    
    def form_valid(self, form):
        """Isi field user secara otomatis"""
        form.instance.user = self.request.user
        messages.success(self.request, 'Kategori berhasil ditambahkan!')
        return super().form_valid(form)


class CategoryUpdateView(LoginRequiredMixin, UpdateView):
    """View untuk mengedit kategori"""
    model = Category
    form_class = CategoryForm
    template_name = 'pages/category_form.html'
    success_url = reverse_lazy('core:category-list')
    
    def get_queryset(self):
        """Hanya user yang punya kategori ini yang bisa edit"""
        return Category.objects.filter(user=self.request.user)
    
    def form_valid(self, form):
        """Tampilkan pesan sukses setelah update"""
        messages.success(self.request, 'Kategori berhasil diperbarui!')
        return super().form_valid(form)


class CategoryDeleteView(LoginRequiredMixin, DeleteView):
    """View untuk menghapus kategori"""
    model = Category
    template_name = 'pages/category_confirm_delete.html'
    success_url = reverse_lazy('core:category-list')
    
    def get_queryset(self):
        """Hanya user yang punya kategori ini yang bisa hapus"""
        return Category.objects.filter(user=self.request.user)
    
    def delete(self, request, *args, **kwargs):
        """Tampilkan pesan sukses setelah delete"""
        messages.success(self.request, 'Kategori berhasil dihapus!')
        return super().delete(request, *args, **kwargs)
