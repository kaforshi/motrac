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
    # Transfer URL
    path('transfer/', views.TransferView.as_view(), name='transfer'),
    # Category CRUD URLs
    path('categories/', views.CategoryListView.as_view(), name='category-list'),
    path('categories/new/', views.CategoryCreateView.as_view(), name='category-create'),
    path('categories/<int:pk>/edit/', views.CategoryUpdateView.as_view(), name='category-update'),
    path('categories/<int:pk>/delete/', views.CategoryDeleteView.as_view(), name='category-delete'),
]

