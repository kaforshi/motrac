from django import forms
from django.contrib.auth.forms import UserCreationForm
from django.contrib.auth.models import User
from django.db.models import Sum
from decimal import Decimal
from .models import Transaction, Account, Category


class CustomUserCreationForm(UserCreationForm):
    """Form untuk registrasi pengguna baru"""
    email = forms.EmailField(
        required=True,
        widget=forms.EmailInput(attrs={
            'class': 'appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm',
            'placeholder': 'Email'
        })
    )
    first_name = forms.CharField(
        max_length=30,
        required=False,
        widget=forms.TextInput(attrs={
            'class': 'appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm',
            'placeholder': 'Nama Depan'
        })
    )
    last_name = forms.CharField(
        max_length=30,
        required=False,
        widget=forms.TextInput(attrs={
            'class': 'appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm',
            'placeholder': 'Nama Belakang'
        })
    )

    class Meta:
        model = User
        fields = ('username', 'email', 'first_name', 'last_name', 'password1', 'password2')
        widgets = {
            'username': forms.TextInput(attrs={
                'class': 'appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm',
                'placeholder': 'Username'
            }),
            'password1': forms.PasswordInput(attrs={
                'class': 'appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm',
                'placeholder': 'Password'
            }),
            'password2': forms.PasswordInput(attrs={
                'class': 'appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm',
                'placeholder': 'Konfirmasi Password'
            }),
        }

    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        # Update class untuk password fields
        self.fields['password1'].widget.attrs.update({
            'class': 'appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm',
            'placeholder': 'Password'
        })
        self.fields['password2'].widget.attrs.update({
            'class': 'appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm',
            'placeholder': 'Konfirmasi Password'
        })

    def save(self, commit=True):
        # Panggil parent save dengan commit=False untuk mendapatkan user object
        user = super().save(commit=False)
        # Set field tambahan
        user.email = self.cleaned_data['email']
        if self.cleaned_data.get('first_name'):
            user.first_name = self.cleaned_data['first_name']
        if self.cleaned_data.get('last_name'):
            user.last_name = self.cleaned_data['last_name']
        # Jika commit=True, simpan user
        if commit:
            # UserCreationForm sudah handle password via set_password()
            # Simpan user - Django akan otomatis melakukan insert jika user.pk is None
            user.save()
        return user


class TransactionForm(forms.ModelForm):
    """Form untuk membuat dan mengedit transaksi"""
    
    class Meta:
        model = Transaction
        fields = ['account', 'category', 'amount', 'type', 'date', 'description']
        widgets = {
            'account': forms.Select(attrs={
                'class': 'appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm'
            }),
            'category': forms.Select(attrs={
                'class': 'appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm'
            }),
            'amount': forms.NumberInput(attrs={
                'class': 'appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm',
                'step': '0.01',
                'min': '0.01',
                'placeholder': '0.00'
            }),
            'type': forms.Select(attrs={
                'class': 'appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm'
            }),
            'date': forms.DateInput(attrs={
                'class': 'appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm',
                'type': 'date'
            }),
            'description': forms.Textarea(attrs={
                'class': 'appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm',
                'rows': 3,
                'placeholder': 'Deskripsi transaksi (opsional)'
            }),
        }
    
    def __init__(self, *args, **kwargs):
        user = kwargs.pop('user', None)
        super().__init__(*args, **kwargs)
        # Filter account dan category berdasarkan user
        if user:
            self.fields['account'].queryset = Account.objects.filter(user=user)
            self.fields['category'].queryset = Category.objects.filter(user=user)


class TransferForm(forms.Form):
    """Form untuk transfer antar akun"""
    from_account = forms.ModelChoiceField(
        queryset=Account.objects.none(),
        label='Dari Dompet',
        widget=forms.Select(attrs={
            'class': 'appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm'
        })
    )
    to_account = forms.ModelChoiceField(
        queryset=Account.objects.none(),
        label='Ke Dompet',
        widget=forms.Select(attrs={
            'class': 'appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm'
        })
    )
    amount = forms.DecimalField(
        max_digits=15,
        decimal_places=2,
        min_value=Decimal('0.01'),
        label='Jumlah Transfer',
        widget=forms.NumberInput(attrs={
            'class': 'appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm',
            'step': '0.01',
            'min': '0.01',
            'placeholder': '0.00'
        })
    )
    description = forms.CharField(
        required=False,
        label='Deskripsi (Opsional)',
        widget=forms.Textarea(attrs={
            'class': 'appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm',
            'rows': 3,
            'placeholder': 'Catatan transfer (opsional)'
        })
    )
    
    def __init__(self, *args, **kwargs):
        user = kwargs.pop('user', None)
        super().__init__(*args, **kwargs)
        # Filter account berdasarkan user
        if user:
            accounts = Account.objects.filter(user=user)
            self.fields['from_account'].queryset = accounts
            self.fields['to_account'].queryset = accounts
    
    def clean(self):
        cleaned_data = super().clean()
        from_account = cleaned_data.get('from_account')
        to_account = cleaned_data.get('to_account')
        amount = cleaned_data.get('amount')
        
        # Validasi: from_account dan to_account harus berbeda
        if from_account and to_account and from_account == to_account:
            raise forms.ValidationError('Dompet asal dan tujuan tidak boleh sama.')
        
        # Validasi: saldo from_account harus cukup
        if from_account and amount:
            # Hitung saldo dari account
            initial_balance = from_account.initial_balance
            total_income = Transaction.objects.filter(
                account=from_account,
                type='Pemasukan'
            ).aggregate(total=Sum('amount'))['total'] or Decimal('0.00')
            total_expense = Transaction.objects.filter(
                account=from_account,
                type='Pengeluaran'
            ).aggregate(total=Sum('amount'))['total'] or Decimal('0.00')
            
            current_balance = initial_balance + total_income - total_expense
            
            if amount > current_balance:
                raise forms.ValidationError(
                    f'Saldo tidak cukup. Saldo saat ini: Rp {current_balance:.2f}'
                )
        
        return cleaned_data


class CategoryForm(forms.ModelForm):
    """Form untuk membuat dan mengedit kategori"""
    
    class Meta:
        model = Category
        fields = ['name', 'type']
        widgets = {
            'name': forms.TextInput(attrs={
                'class': 'appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm',
                'placeholder': 'Nama kategori'
            }),
            'type': forms.Select(attrs={
                'class': 'appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm'
            }),
        }

