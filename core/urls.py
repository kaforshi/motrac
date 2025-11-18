from django.urls import path
from . import views

app_name = 'core'

urlpatterns = [
    path('signup/', views.signup_view, name='signup'),
    path('dashboard/', views.DashboardView.as_view(), name='dashboard'),
    # Account CRUD URLs
    path('accounts/', views.AccountListView.as_view(), name='account_list'),
    path('accounts/create/', views.AccountCreateView.as_view(), name='account_create'),
    path('accounts/<int:pk>/update/', views.AccountUpdateView.as_view(), name='account_update'),
    path('accounts/<int:pk>/delete/', views.AccountDeleteView.as_view(), name='account_delete'),
    # Account AJAX URLs
    path('accounts/create-ajax/', views.create_account_ajax, name='account_create_ajax'),
    path('accounts/<int:pk>/data/', views.get_account_data, name='account_data'),
    path('accounts/<int:pk>/update-ajax/', views.update_account_ajax, name='account_update_ajax'),
    path('accounts/<int:pk>/delete-ajax/', views.delete_account_ajax, name='account_delete_ajax'),
    # Transfer URL
    path('transfer/', views.TransferView.as_view(), name='transfer'),
    # Category CRUD URLs
    path('categories/', views.CategoryListView.as_view(), name='category-list'),
    path('categories/new/', views.CategoryCreateView.as_view(), name='category-create'),
    path('categories/<int:pk>/edit/', views.CategoryUpdateView.as_view(), name='category-update'),
    path('categories/<int:pk>/delete/', views.CategoryDeleteView.as_view(), name='category-delete'),
    # Category AJAX URLs
    path('categories/create-ajax/', views.create_category_ajax, name='category_create_ajax'),
    path('categories/<int:pk>/data/', views.get_category_data, name='category_data'),
    path('categories/<int:pk>/update-ajax/', views.update_category_ajax, name='category_update_ajax'),
    path('categories/<int:pk>/delete-ajax/', views.delete_category_ajax, name='category_delete_ajax'),
    # Transaction History URL
    path('history/', views.TransactionHistoryView.as_view(), name='transaction-history'),
    path('history/pdf/', views.TransactionHistoryPDFView.as_view(), name='transaction-history-pdf'),
]

