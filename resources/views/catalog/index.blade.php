@extends('layouts.app')

@section('title', 'Catalog - Web Sewa')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-3">Catalog</h3>
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
            <a href="#">Catalog</a>
        </li>
    </ul>
</div>

<div class="row">
    <!-- Filters Sidebar -->
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Filters</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('catalog.index') }}">
                    <!-- Search -->
                    <div class="mb-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search"
                               placeholder="Search items..." value="{{ request('search') }}">
                    </div>

                    <!-- Category Filter -->
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sort -->
                    <div class="mb-3">
                        <label for="sort" class="form-label">Sort By</label>
                        <select class="form-select" id="sort" name="sort">
                            <option value="">Latest</option>
                            <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>
                                Price: Low to High
                            </option>
                            <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>
                                Price: High to Low
                            </option>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-filter"></i> Apply Filters
                        </button>
                        <a href="{{ route('catalog.index') }}" class="btn btn-secondary">
                            <i class="fa fa-times"></i> Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Items Grid -->
    <div class="col-md-9">
        @if($items->count() > 0)
            <div class="row">
                @foreach($items as $item)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                             style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fa fa-box fa-4x text-white"></i>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <span class="badge badge-info mb-2 align-self-start">
                                {{ $item->category?->name ?? 'Uncategorized' }}
                            </span>
                            <h5 class="card-title">{{ $item->name }}</h5>
                            <p class="card-text text-muted small flex-grow-1">
                                {{ Str::limit($item->description, 80) }}
                            </p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <small class="text-muted d-block">Price per Day</small>
                                        <h5 class="mb-0 text-primary">
                                            Rp {{ number_format($item->price_per_period, 0, ',', '.') }}
                                        </h5>
                                    </div>
                                    <span class="badge badge-success">Available</span>
                                </div>
                                <div class="d-grid gap-2">
                                    <a href="{{ route('catalog.show', $item->id) }}" class="btn btn-primary btn-sm">
                                        <i class="fa fa-eye"></i> View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $items->links() }}
            </div>
        @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fa fa-inbox fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No items found</h5>
                    <p class="text-muted">Try adjusting your filters or search term</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
</style>
@endpush
