@extends('layouts.app')

@section('title', 'Katalog - Tirta Kesuma')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-3">Katalog</h3>
    <ul class="breadcrumbs mb-3">
        <li class="nav-home">
            <a href="{{ route('home') }}">
                <i class="icon-home"></i>
            </a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Katalog</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- Header Controls -->
        <div class="catalog-header">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex gap-3 align-items-center flex-grow-1">
                    <!-- Search -->
                    <div class="search-catalog">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Cari barang...">
                    </div>

                    <!-- Category Filter -->
                    <select id="categoryFilter" class="filter-select">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Sort -->
                    <select id="sortFilter" class="filter-select">
                        <option value="">Terbaru</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>
                            Harga: Terendah
                        </option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>
                            Harga: Tertinggi
                        </option>
                        <option value="name_asc">Nama: A-Z</option>
                        <option value="name_desc">Nama: Z-A</option>
                    </select>
                </div>

                <!-- View Toggle -->
                <div class="view-toggle">
                    <button class="view-btn active" data-view="grid" title="Tampilan Grid">
                        <i class="fas fa-th"></i>
                    </button>
                    <button class="view-btn" data-view="list" title="Tampilan List">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Items Container -->
        @if($items->count() > 0)
            <div id="itemsContainer" class="items-grid">
                @foreach($items as $item)
                <div class="item-card"
                     data-category="{{ $item->category_id }}"
                     data-name="{{ strtolower($item->name) }}"
                     data-price="{{ $item->price_per_period }}">
                    <div class="item-card-inner">
                        <!-- Image -->
                        <div class="item-image">
                            @if($item->photo_url)
                                <img src="{{ asset('storage/' . $item->photo_url) }}" alt="{{ $item->name }}">
                            @else
                                <div class="item-placeholder">
                                    <i class="fas fa-box"></i>
                                </div>
                            @endif
                            @if($item->status === 'available')
                                <span class="status-badge">Tersedia</span>
                            @else
                                <span class="status-badge unavailable">Tidak Tersedia</span>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="item-content">
                            <div class="item-category">{{ $item->category?->name ?? 'Tanpa Kategori' }}</div>
                            <h3 class="item-title">{{ $item->name }}</h3>
                            <p class="item-description">{{ Str::limit($item->description, 100) }}</p>

                            <div class="item-footer">
                                <div class="item-price">
                                    <span class="price-label">Harga/Periode</span>
                                    <span class="price-value">Rp {{ number_format($item->price_per_period, 0, ',', '.') }}</span>
                                </div>
                                <a href="{{ route('catalog.show', $item->id) }}" class="btn-view">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-center">
                {{ $items->links() }}
            </div>
        @else
            <div class="empty-catalog">
                <i class="fas fa-inbox"></i>
                <h4>Tidak ada barang ditemukan</h4>
                <p>Coba ubah filter atau kata pencarian Anda</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
/* Catalog Header */
.catalog-header {
    background: #ffffff;
    padding: 1.5rem;
    border-radius: 12px;
    border: 1px solid #e9ecef;
    margin-bottom: 2rem;
}

.search-catalog {
    position: relative;
    flex: 1;
    max-width: 400px;
}

.search-catalog i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

.search-catalog input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.75rem;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    font-size: 0.9375rem;
    transition: all 0.2s ease;
}

.search-catalog input:focus {
    outline: none;
    border-color: #177dff;
    box-shadow: 0 0 0 3px rgba(23, 125, 255, 0.1);
}

.filter-select {
    padding: 0.75rem 1rem;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    font-size: 0.9375rem;
    min-width: 180px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.filter-select:focus {
    outline: none;
    border-color: #177dff;
    box-shadow: 0 0 0 3px rgba(23, 125, 255, 0.1);
}

.view-toggle {
    display: flex;
    gap: 0.5rem;
    background: #f8f9fa;
    padding: 0.25rem;
    border-radius: 8px;
}

.view-btn {
    padding: 0.625rem 1rem;
    border: none;
    background: transparent;
    color: #6c757d;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.view-btn:hover {
    color: #177dff;
}

.view-btn.active {
    background: #ffffff;
    color: #177dff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
}

/* Grid View */
.items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}

/* List View */
.items-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.items-list .item-card {
    max-width: 100%;
}

.items-list .item-card-inner {
    flex-direction: row;
}

.items-list .item-image {
    width: 200px;
    height: 150px;
    flex-shrink: 0;
}

.items-list .item-content {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.items-list .item-footer {
    margin-top: auto;
}

/* Item Card */
.item-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.item-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    transform: translateY(-4px);
}

.item-card-inner {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.item-image {
    position: relative;
    width: 100%;
    height: 220px;
    background: #f8f9fa;
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
}

.item-placeholder i {
    font-size: 3rem;
    color: #adb5bd;
}

.status-badge {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    padding: 0.375rem 0.75rem;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    color: #28a745;
    border: 1px solid rgba(40, 167, 69, 0.2);
}

.status-badge.unavailable {
    color: #6c757d;
    border-color: rgba(108, 117, 125, 0.2);
}

.item-content {
    padding: 1.25rem;
}

.item-category {
    font-size: 0.8125rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.item-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.item-description {
    font-size: 0.875rem;
    color: #6c757d;
    line-height: 1.6;
    margin-bottom: 1rem;
    min-height: 3rem;
}

.item-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid #f1f3f5;
}

.item-price {
    display: flex;
    flex-direction: column;
}

.price-label {
    font-size: 0.75rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.price-value {
    font-size: 1.125rem;
    font-weight: 700;
    color: #212529;
}

.btn-view {
    padding: 0.5rem 1.25rem;
    background: #177dff;
    color: #ffffff;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    display: inline-block;
}

.btn-view:hover {
    background: #0c5fd7;
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(23, 125, 255, 0.3);
}

/* Empty State */
.empty-catalog {
    text-align: center;
    padding: 4rem 2rem;
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
}

.empty-catalog i {
    font-size: 4rem;
    color: #dee2e6;
    margin-bottom: 1.5rem;
}

.empty-catalog h4 {
    color: #495057;
    margin-bottom: 0.5rem;
}

.empty-catalog p {
    color: #6c757d;
    margin: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .items-grid {
        grid-template-columns: 1fr;
    }

    .catalog-header .d-flex {
        flex-direction: column;
        gap: 1rem;
    }

    .search-catalog {
        max-width: 100%;
    }

    .filter-select {
        width: 100%;
    }

    .items-list .item-card-inner {
        flex-direction: column;
    }

    .items-list .item-image {
        width: 100%;
        height: 200px;
    }
}
</style>
@endpush

@push('scripts')
<script>
// View Toggle
const viewButtons = document.querySelectorAll('.view-btn');
const itemsContainer = document.getElementById('itemsContainer');

viewButtons.forEach(btn => {
    btn.addEventListener('click', function() {
        viewButtons.forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const view = this.getAttribute('data-view');
        if (view === 'grid') {
            itemsContainer.className = 'items-grid';
        } else {
            itemsContainer.className = 'items-list';
        }

        // Save preference
        localStorage.setItem('catalogView', view);
    });
});

// Restore view preference
const savedView = localStorage.getItem('catalogView');
if (savedView) {
    const btn = document.querySelector(`[data-view="${savedView}"]`);
    if (btn) {
        btn.click();
    }
}

// Filter functionality
const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');
const sortFilter = document.getElementById('sortFilter');

function filterItems() {
    const searchTerm = searchInput.value.toLowerCase();
    const selectedCategory = categoryFilter.value;
    const items = Array.from(document.querySelectorAll('.item-card'));

    items.forEach(item => {
        const name = item.getAttribute('data-name');
        const category = item.getAttribute('data-category');

        const matchesSearch = !searchTerm || name.includes(searchTerm);
        const matchesCategory = !selectedCategory || category === selectedCategory;

        if (matchesSearch && matchesCategory) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });

    sortItems();
}

function sortItems() {
    const sortValue = sortFilter.value;
    const items = Array.from(document.querySelectorAll('.item-card'));
    const container = document.getElementById('itemsContainer');

    items.sort((a, b) => {
        if (sortValue === 'price_asc') {
            return parseFloat(a.getAttribute('data-price')) - parseFloat(b.getAttribute('data-price'));
        } else if (sortValue === 'price_desc') {
            return parseFloat(b.getAttribute('data-price')) - parseFloat(a.getAttribute('data-price'));
        } else if (sortValue === 'name_asc') {
            return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name'));
        } else if (sortValue === 'name_desc') {
            return b.getAttribute('data-name').localeCompare(a.getAttribute('data-name'));
        }
        return 0;
    });

    items.forEach(item => container.appendChild(item));
}

// Event listeners
searchInput.addEventListener('input', filterItems);
categoryFilter.addEventListener('change', filterItems);
sortFilter.addEventListener('change', filterItems);
</script>
@endpush
