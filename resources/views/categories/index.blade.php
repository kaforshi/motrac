@extends('layouts.app')

@section('title', 'Categories')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Categories</h1>
            <button onclick="showAddCategoryModal('expense')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Add Category
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @foreach(['income', 'expense', 'transfer'] as $type)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 capitalize">{{ $type }} Categories</h2>
                    <div class="space-y-2 mb-4">
                        @foreach(($categories[$type] ?? []) as $category)
                            @if(!$category->parent_id)
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded">
                                    <div class="flex justify-between items-center mb-2">
                                        <div class="flex items-center gap-2">
                                            @if($category->color)
                                                <div class="w-4 h-4 rounded-full" style="background-color: {{ $category->color }}"></div>
                                            @endif
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $category->name }}</span>
                                            @if($category->icon)
                                                <span class="text-lg">{{ $category->icon }}</span>
                                            @endif
                                        </div>
                                        <div class="flex gap-2">
                                            <button onclick="showEditCategoryModal({{ $category->id }}, '{{ $category->name }}', '{{ $category->type }}', {{ $category->parent_id ?? 'null' }}, '{{ $category->icon ?? '' }}', '{{ $category->color ?? '' }}')" class="text-indigo-600 dark:text-indigo-400 text-sm hover:text-indigo-800 dark:hover:text-indigo-300">Edit</button>
                                            <form method="POST" action="{{ route('categories.destroy', $category->id) }}" class="inline" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 text-sm hover:text-red-800 dark:hover:text-red-300">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                    @if($category->children->count() > 0)
                                        <div class="mt-2 ml-4 space-y-1 border-l-2 border-gray-300 dark:border-gray-600 pl-2">
                                            @foreach($category->children as $child)
                                                <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center justify-between">
                                                    <span>
                                                        @if($child->icon)
                                                            <span class="mr-1">{{ $child->icon }}</span>
                                                        @endif
                                                        {{ $child->name }}
                                                    </span>
                                                    <form method="POST" action="{{ route('categories.destroy', $child->id) }}" class="inline" onsubmit="return confirm('Are you sure?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-500 dark:text-red-400 text-xs">×</button>
                                                    </form>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <button onclick="showAddCategoryModal('{{ $type }}')" class="w-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-4 py-2 rounded text-sm">
                        Add {{ ucfirst($type) }} Category
                    </button>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div id="addCategoryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-96 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Add Category</h3>
        <form method="POST" action="{{ route('categories.store') }}" id="addCategoryForm">
            @csrf
            <input type="hidden" name="type" id="categoryType">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label>
                <input type="text" name="name" id="categoryName" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Parent Category (Optional)</label>
                <select name="parent_id" id="parentCategory" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white px-3 py-2">
                    <option value="">None (Top Level)</option>
                </select>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Select a parent category to create a sub-category</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Icon (Optional)</label>
                <input type="text" name="icon" id="categoryIcon" placeholder="e.g., 💰, 🍔, 🚗" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white px-3 py-2">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">You can use emoji or icon name</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Color</label>
                <input type="color" name="color" id="categoryColor" value="#3B82F6" class="w-full h-10 rounded-md border-gray-300 dark:border-gray-700">
            </div>
            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">Add Category</button>
                <button type="button" onclick="closeAddCategoryModal()" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-md">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editCategoryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-96 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Edit Category</h3>
        <form method="POST" id="editCategoryForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="type" id="editCategoryType">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label>
                <input type="text" name="name" id="editCategoryName" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Parent Category (Optional)</label>
                <select name="parent_id" id="editParentCategory" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white px-3 py-2">
                    <option value="">None (Top Level)</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Icon (Optional)</label>
                <input type="text" name="icon" id="editCategoryIcon" placeholder="e.g., 💰, 🍔, 🚗" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Color</label>
                <input type="color" name="color" id="editCategoryColor" value="#3B82F6" class="w-full h-10 rounded-md border-gray-300 dark:border-gray-700">
            </div>
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" id="editCategoryActive" value="1" class="rounded border-gray-300 dark:border-gray-700">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
                </label>
            </div>
            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">Update Category</button>
                <button type="button" onclick="closeEditCategoryModal()" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-md">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
const parentCategories = @json($parentCategories);

function showAddCategoryModal(type) {
    document.getElementById('categoryType').value = type;
    document.getElementById('categoryName').value = '';
    document.getElementById('categoryIcon').value = '';
    document.getElementById('categoryColor').value = '#3B82F6';
    
    // Load parent categories of the same type
    const parentSelect = document.getElementById('parentCategory');
    parentSelect.innerHTML = '<option value="">None (Top Level)</option>';
    
    // Populate parent categories for the selected type
    if (parentCategories[type]) {
        parentCategories[type].forEach(function(category) {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.name;
            parentSelect.appendChild(option);
        });
    }
    
    document.getElementById('addCategoryModal').classList.remove('hidden');
}

function closeAddCategoryModal() {
    document.getElementById('addCategoryModal').classList.add('hidden');
    document.getElementById('addCategoryForm').reset();
}

function showEditCategoryModal(id, name, type, parentId, icon, color) {
    document.getElementById('editCategoryForm').action = '/categories/' + id;
    document.getElementById('editCategoryType').value = type;
    document.getElementById('editCategoryName').value = name;
    document.getElementById('editCategoryIcon').value = icon || '';
    document.getElementById('editCategoryColor').value = color || '#3B82F6';
    
    // Load parent categories of the same type
    const parentSelect = document.getElementById('editParentCategory');
    parentSelect.innerHTML = '<option value="">None (Top Level)</option>';
    
    // Populate parent categories for the selected type
    if (parentCategories[type]) {
        parentCategories[type].forEach(function(category) {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.name;
            if (parentId && category.id == parentId) {
                option.selected = true;
            }
            parentSelect.appendChild(option);
        });
    }
    
    document.getElementById('editCategoryModal').classList.remove('hidden');
}

function closeEditCategoryModal() {
    document.getElementById('editCategoryModal').classList.add('hidden');
    document.getElementById('editCategoryForm').reset();
}
</script>
@endsection

