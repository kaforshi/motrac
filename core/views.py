from django.shortcuts import render, redirect
from django.views.generic import TemplateView, CreateView, ListView, UpdateView, DeleteView, FormView
from django.contrib.auth import login, logout
from django.contrib.auth.mixins import LoginRequiredMixin
from django.contrib import messages
from django.urls import reverse_lazy, reverse
from django.db.models import Sum, Q
from django.utils import timezone
from django.utils.safestring import mark_safe
from django.http import HttpResponse
from datetime import datetime, date
from decimal import Decimal
import json
from reportlab.lib.pagesizes import letter, A4
from reportlab.lib import colors
from reportlab.lib.units import inch
from reportlab.platypus import SimpleDocTemplate, Table, TableStyle, Paragraph, Spacer, PageBreak
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.enums import TA_CENTER, TA_RIGHT, TA_LEFT
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


def custom_logout_view(request):
    """Custom logout view yang redirect ke landing page"""
    logout(request)
    messages.success(request, 'Anda telah berhasil logout.')
    return redirect('landing')


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
                'id': account.id,
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
                'id': account.id,
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
                'id': category.id,
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
                'id': category.id,
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


# AJAX Views untuk Account
from django.http import JsonResponse
from django.views.decorators.http import require_http_methods
from django.contrib.auth.decorators import login_required
import json


@login_required
@require_http_methods(["POST"])
def create_account_ajax(request):
    """AJAX view untuk create account"""
    try:
        data = json.loads(request.body)
        
        account = Account.objects.create(
            user=request.user,
            name=data.get('name'),
            initial_balance=Decimal(data.get('initial_balance', '0.00'))
        )
        
        return JsonResponse({
            'success': True,
            'message': 'Dompet berhasil ditambahkan!',
            'account': {
                'id': account.id,
                'name': account.name,
                'initial_balance': str(account.initial_balance)
            }
        })
    except Exception as e:
        return JsonResponse({'error': str(e)}, status=400)


@login_required
@require_http_methods(["GET"])
def get_account_data(request, pk):
    """AJAX view untuk mendapatkan data account"""
    
    try:
        account = Account.objects.get(pk=pk, user=request.user)
        return JsonResponse({
            'id': account.id,
            'name': account.name,
            'initial_balance': str(account.initial_balance)
        })
    except Account.DoesNotExist:
        return JsonResponse({'error': 'Account not found'}, status=404)


@login_required
@require_http_methods(["POST"])
def update_account_ajax(request, pk):
    """AJAX view untuk update account"""
    
    try:
        account = Account.objects.get(pk=pk, user=request.user)
        data = json.loads(request.body)
        
        account.name = data.get('name', account.name)
        account.initial_balance = Decimal(data.get('initial_balance', account.initial_balance))
        account.save()
        
        return JsonResponse({
            'success': True,
            'message': 'Dompet berhasil diperbarui!',
            'account': {
                'id': account.id,
                'name': account.name,
                'initial_balance': str(account.initial_balance)
            }
        })
    except Account.DoesNotExist:
        return JsonResponse({'error': 'Account not found'}, status=404)
    except Exception as e:
        return JsonResponse({'error': str(e)}, status=400)


@login_required
@require_http_methods(["POST"])
def delete_account_ajax(request, pk):
    """AJAX view untuk delete account"""
    
    try:
        account = Account.objects.get(pk=pk, user=request.user)
        account_name = account.name
        account.delete()
        
        return JsonResponse({
            'success': True,
            'message': f'Dompet "{account_name}" berhasil dihapus!'
        })
    except Account.DoesNotExist:
        return JsonResponse({'error': 'Account not found'}, status=404)
    except Exception as e:
        return JsonResponse({'error': str(e)}, status=400)


# AJAX Views untuk Category
@login_required
@require_http_methods(["POST"])
def create_category_ajax(request):
    """AJAX view untuk create category"""
    try:
        data = json.loads(request.body)
        
        category = Category.objects.create(
            user=request.user,
            name=data.get('name'),
            type=data.get('type')
        )
        
        return JsonResponse({
            'success': True,
            'message': 'Kategori berhasil ditambahkan!',
            'category': {
                'id': category.id,
                'name': category.name,
                'type': category.type
            }
        })
    except Exception as e:
        return JsonResponse({'error': str(e)}, status=400)


@login_required
@require_http_methods(["GET"])
def get_category_data(request, pk):
    """AJAX view untuk mendapatkan data category"""
    
    try:
        category = Category.objects.get(pk=pk, user=request.user)
        return JsonResponse({
            'id': category.id,
            'name': category.name,
            'type': category.type
        })
    except Category.DoesNotExist:
        return JsonResponse({'error': 'Category not found'}, status=404)


@login_required
@require_http_methods(["POST"])
def update_category_ajax(request, pk):
    """AJAX view untuk update category"""
    
    try:
        category = Category.objects.get(pk=pk, user=request.user)
        data = json.loads(request.body)
        
        category.name = data.get('name', category.name)
        category.type = data.get('type', category.type)
        category.save()
        
        return JsonResponse({
            'success': True,
            'message': 'Kategori berhasil diperbarui!',
            'category': {
                'id': category.id,
                'name': category.name,
                'type': category.type
            }
        })
    except Category.DoesNotExist:
        return JsonResponse({'error': 'Category not found'}, status=404)
    except Exception as e:
        return JsonResponse({'error': str(e)}, status=400)


@login_required
@require_http_methods(["POST"])
def delete_category_ajax(request, pk):
    """AJAX view untuk delete category"""
    
    try:
        category = Category.objects.get(pk=pk, user=request.user)
        category_name = category.name
        category.delete()
        
        return JsonResponse({
            'success': True,
            'message': f'Kategori "{category_name}" berhasil dihapus!'
        })
    except Category.DoesNotExist:
        return JsonResponse({'error': 'Category not found'}, status=404)
    except Exception as e:
        return JsonResponse({'error': str(e)}, status=400)


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


class TransactionHistoryView(LoginRequiredMixin, ListView):
    """View untuk menampilkan history transaksi dengan filter"""
    model = Transaction
    template_name = 'pages/transaction_history.html'
    context_object_name = 'transactions'
    paginate_by = 20
    
    def get_queryset(self):
        """Filter transaksi berdasarkan user dan parameter filter"""
        queryset = Transaction.objects.filter(
            user=self.request.user
        ).select_related('account', 'category')
        
        # Filter berdasarkan dompet
        account_id = self.request.GET.get('account')
        if account_id:
            try:
                account = Account.objects.get(id=account_id, user=self.request.user)
                queryset = queryset.filter(account=account)
            except Account.DoesNotExist:
                pass
        
        # Filter berdasarkan kategori
        category_id = self.request.GET.get('category')
        if category_id:
            try:
                category = Category.objects.get(id=category_id, user=self.request.user)
                queryset = queryset.filter(category=category)
            except Category.DoesNotExist:
                pass
        
        # Filter berdasarkan bulan dan tahun
        month = self.request.GET.get('month')
        year = self.request.GET.get('year')
        
        if month and year:
            try:
                month = int(month)
                year = int(year)
                # Filter berdasarkan bulan dan tahun
                queryset = queryset.filter(date__year=year, date__month=month)
            except (ValueError, TypeError):
                pass
        elif year:
            try:
                year = int(year)
                queryset = queryset.filter(date__year=year)
            except (ValueError, TypeError):
                pass
        
        return queryset.order_by('-date', '-created_at')
    
    def get_context_data(self, **kwargs):
        context = super().get_context_data(**kwargs)
        user = self.request.user
        
        # Siapkan data untuk dropdown filter
        context['accounts'] = Account.objects.filter(user=user).order_by('name')
        context['categories'] = Category.objects.filter(user=user).order_by('type', 'name')
        
        # Ambil nilai filter saat ini dari query parameters
        context['selected_account'] = self.request.GET.get('account', '')
        context['selected_category'] = self.request.GET.get('category', '')
        context['selected_month'] = self.request.GET.get('month', '')
        context['selected_year'] = self.request.GET.get('year', '')
        
        # Generate list tahun (dari tahun transaksi pertama sampai tahun sekarang)
        now = timezone.now()
        first_transaction = Transaction.objects.filter(user=user).order_by('date').first()
        if first_transaction:
            start_year = first_transaction.date.year
        else:
            start_year = now.year
        
        context['years'] = list(range(start_year, now.year + 1))
        context['months'] = [
            (1, 'Januari'), (2, 'Februari'), (3, 'Maret'), (4, 'April'),
            (5, 'Mei'), (6, 'Juni'), (7, 'Juli'), (8, 'Agustus'),
            (9, 'September'), (10, 'Oktober'), (11, 'November'), (12, 'Desember')
        ]
        
        # Hitung total pemasukan dan pengeluaran dari hasil filter
        queryset = self.get_queryset()
        total_income = queryset.filter(type='Pemasukan').aggregate(
            total=Sum('amount')
        )['total'] or Decimal('0.00')
        
        total_expense = queryset.filter(type='Pengeluaran').aggregate(
            total=Sum('amount')
        )['total'] or Decimal('0.00')
        
        context['total_income'] = total_income
        context['total_expense'] = total_expense
        context['net_balance'] = total_income - total_expense
        
        return context


class TransactionHistoryPDFView(LoginRequiredMixin, ListView):
    """View untuk generate PDF history transaksi"""
    model = Transaction
    
    def get_queryset(self):
        """Filter transaksi berdasarkan user dan parameter filter (sama seperti TransactionHistoryView)"""
        queryset = Transaction.objects.filter(
            user=self.request.user
        ).select_related('account', 'category')
        
        # Filter berdasarkan dompet
        account_id = self.request.GET.get('account')
        if account_id:
            try:
                account = Account.objects.get(id=account_id, user=self.request.user)
                queryset = queryset.filter(account=account)
            except Account.DoesNotExist:
                pass
        
        # Filter berdasarkan kategori
        category_id = self.request.GET.get('category')
        if category_id:
            try:
                category = Category.objects.get(id=category_id, user=self.request.user)
                queryset = queryset.filter(category=category)
            except Category.DoesNotExist:
                pass
        
        # Filter berdasarkan bulan dan tahun
        month = self.request.GET.get('month')
        year = self.request.GET.get('year')
        
        if month and year:
            try:
                month = int(month)
                year = int(year)
                queryset = queryset.filter(date__year=year, date__month=month)
            except (ValueError, TypeError):
                pass
        elif year:
            try:
                year = int(year)
                queryset = queryset.filter(date__year=year)
            except (ValueError, TypeError):
                pass
        
        return queryset.order_by('-date', '-created_at')
    
    def get(self, request, *args, **kwargs):
        """Generate PDF response"""
        queryset = self.get_queryset()
        user = request.user
        
        # Buat response dengan content type PDF
        response = HttpResponse(content_type='application/pdf')
        filename = f'history_transaksi_{datetime.now().strftime("%Y%m%d_%H%M%S")}.pdf'
        response['Content-Disposition'] = f'attachment; filename="{filename}"'
        
        # Buat PDF document
        doc = SimpleDocTemplate(response, pagesize=A4)
        story = []
        
        # Styles
        styles = getSampleStyleSheet()
        title_style = ParagraphStyle(
            'CustomTitle',
            parent=styles['Heading1'],
            fontSize=18,
            textColor=colors.HexColor('#4F46E5'),
            spaceAfter=12,
            alignment=TA_CENTER
        )
        heading_style = ParagraphStyle(
            'CustomHeading',
            parent=styles['Heading2'],
            fontSize=14,
            textColor=colors.HexColor('#1F2937'),
            spaceAfter=8
        )
        normal_style = styles['Normal']
        normal_style.fontSize = 10
        
        # Header
        story.append(Paragraph('History Transaksi', title_style))
        story.append(Spacer(1, 0.2*inch))
        
        # Info User dan Filter
        info_data = [
            ['User', user.username],
            ['Tanggal Generate', datetime.now().strftime('%d %B %Y %H:%M:%S')],
        ]
        
        # Tambahkan info filter jika ada
        account_id = request.GET.get('account')
        category_id = request.GET.get('category')
        month = request.GET.get('month')
        year = request.GET.get('year')
        
        if account_id:
            try:
                account = Account.objects.get(id=account_id, user=user)
                info_data.append(['Filter Dompet', account.name])
            except Account.DoesNotExist:
                pass
        
        if category_id:
            try:
                category = Category.objects.get(id=category_id, user=user)
                info_data.append(['Filter Kategori', f"{category.name} ({category.get_type_display()})"])
            except Category.DoesNotExist:
                pass
        
        if month and year:
            try:
                month_names = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
                info_data.append(['Filter Periode', f"{month_names[int(month)]} {year}"])
            except (ValueError, TypeError):
                pass
        elif year:
            info_data.append(['Filter Tahun', str(year)])
        
        info_table = Table(info_data, colWidths=[2*inch, 4*inch])
        info_table.setStyle(TableStyle([
            ('BACKGROUND', (0, 0), (0, -1), colors.HexColor('#F3F4F6')),
            ('TEXTCOLOR', (0, 0), (-1, -1), colors.black),
            ('ALIGN', (0, 0), (-1, -1), 'LEFT'),
            ('FONTNAME', (0, 0), (0, -1), 'Helvetica-Bold'),
            ('FONTNAME', (1, 0), (1, -1), 'Helvetica'),
            ('FONTSIZE', (0, 0), (-1, -1), 10),
            ('BOTTOMPADDING', (0, 0), (-1, -1), 8),
            ('TOPPADDING', (0, 0), (-1, -1), 8),
            ('GRID', (0, 0), (-1, -1), 0.5, colors.grey),
        ]))
        story.append(info_table)
        story.append(Spacer(1, 0.3*inch))
        
        # Summary
        total_income = queryset.filter(type='Pemasukan').aggregate(
            total=Sum('amount')
        )['total'] or Decimal('0.00')
        
        total_expense = queryset.filter(type='Pengeluaran').aggregate(
            total=Sum('amount')
        )['total'] or Decimal('0.00')
        
        net_balance = total_income - total_expense
        
        summary_data = [
            ['Total Pemasukan', f'Rp {total_income:,.2f}'],
            ['Total Pengeluaran', f'Rp {total_expense:,.2f}'],
            ['Saldo Bersih', f'Rp {net_balance:,.2f}'],
        ]
        
        summary_table = Table(summary_data, colWidths=[3*inch, 3*inch])
        summary_table.setStyle(TableStyle([
            ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor('#10B981')),
            ('BACKGROUND', (0, 1), (-1, 1), colors.HexColor('#EF4444')),
            ('BACKGROUND', (0, 2), (-1, 2), colors.HexColor('#4F46E5')),
            ('TEXTCOLOR', (0, 0), (-1, -1), colors.whitesmoke),
            ('ALIGN', (0, 0), (-1, -1), 'CENTER'),
            ('FONTNAME', (0, 0), (-1, -1), 'Helvetica-Bold'),
            ('FONTSIZE', (0, 0), (-1, -1), 11),
            ('BOTTOMPADDING', (0, 0), (-1, -1), 10),
            ('TOPPADDING', (0, 0), (-1, -1), 10),
        ]))
        story.append(Paragraph('Ringkasan', heading_style))
        story.append(summary_table)
        story.append(Spacer(1, 0.3*inch))
        
        # Daftar Transaksi
        story.append(Paragraph('Daftar Transaksi', heading_style))
        
        if queryset.exists():
            # Header tabel
            table_data = [['Tanggal', 'Tipe', 'Dompet', 'Kategori', 'Deskripsi', 'Jumlah']]
            
            # Data transaksi
            for transaction in queryset:
                tipe = 'Pemasukan' if transaction.type == 'Pemasukan' else 'Pengeluaran'
                jumlah = f"{'-' if transaction.type == 'Pengeluaran' else ''}Rp {transaction.amount:,.2f}"
                deskripsi = transaction.description[:30] + '...' if transaction.description and len(transaction.description) > 30 else (transaction.description or '-')
                
                table_data.append([
                    transaction.date.strftime('%d/%m/%Y'),
                    tipe,
                    transaction.account.name,
                    transaction.category.name,
                    deskripsi,
                    jumlah
                ])
            
            # Buat tabel
            transaction_table = Table(table_data, colWidths=[0.8*inch, 0.9*inch, 1*inch, 1*inch, 1.5*inch, 1*inch])
            transaction_table.setStyle(TableStyle([
                # Header style
                ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor('#4F46E5')),
                ('TEXTCOLOR', (0, 0), (-1, 0), colors.whitesmoke),
                ('ALIGN', (0, 0), (-1, -1), 'CENTER'),
                ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
                ('FONTSIZE', (0, 0), (-1, 0), 10),
                ('BOTTOMPADDING', (0, 0), (-1, 0), 10),
                ('TOPPADDING', (0, 0), (-1, 0), 10),
                # Data style
                ('FONTNAME', (0, 1), (-1, -1), 'Helvetica'),
                ('FONTSIZE', (0, 1), (-1, -1), 9),
                ('BOTTOMPADDING', (0, 1), (-1, -1), 6),
                ('TOPPADDING', (0, 1), (-1, -1), 6),
                # Alternating row colors
                ('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, colors.HexColor('#F9FAFB')]),
                # Grid
                ('GRID', (0, 0), (-1, -1), 0.5, colors.grey),
                # Text alignment
                ('ALIGN', (0, 1), (0, -1), 'LEFT'),  # Tanggal
                ('ALIGN', (5, 1), (5, -1), 'RIGHT'),  # Jumlah
            ]))
            
            story.append(transaction_table)
            story.append(Spacer(1, 0.2*inch))
            story.append(Paragraph(f'Total: {queryset.count()} transaksi', normal_style))
        else:
            story.append(Paragraph('Tidak ada transaksi yang sesuai dengan filter yang dipilih.', normal_style))
        
        # Build PDF
        doc.build(story)
        
        return response
