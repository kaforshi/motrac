from django.db.models.signals import post_save
from django.dispatch import receiver
from django.contrib.auth.models import User
from .models import Category


@receiver(post_save, sender=User)
def create_default_categories(sender, instance, created, **kwargs):
    """Membuat kategori default saat user baru dibuat"""
    if created:
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
                user=instance,
                name=category_name,
                type='Pemasukan',
                defaults={'name': category_name, 'type': 'Pemasukan'}
            )
        
        # Buat kategori pengeluaran
        for category_name in default_expense_categories:
            Category.objects.get_or_create(
                user=instance,
                name=category_name,
                type='Pengeluaran',
                defaults={'name': category_name, 'type': 'Pengeluaran'}
            )

