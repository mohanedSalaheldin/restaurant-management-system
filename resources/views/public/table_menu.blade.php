<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Table {{ $table->table_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .hero-banner {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            color: white;
            border-radius: 0 0 20px 20px;
        }

        .item-card-img {
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>

<body>

    <!-- Header & Table Greeting -->
    <div class="hero-banner p-4 shadow-sm mb-4">
        <div class="container text-center">
            <span class="badge bg-light text-primary px-3 py-2 rounded-pill fw-bold mb-2">
                {{ ucfirst($table->type) }} Table
            </span>
            <h2 class="fw-bold mb-1">Welcome to Table {{ $table->table_number }}</h2>
            <p class="small mb-3 text-light">Browse our delicious menu crafted with fresh ingredients.</p>

            <!-- Call Waiter Button -->
            <button class="btn btn-warning btn-sm rounded-pill fw-semibold px-3 shadow-sm"
                onclick="alert('Waiter has been notified! Someone will assist you shortly at Table {{ $table->table_number }}.')">
                <i class="bi bi-bell-fill me-1"></i> Call Waiter
            </button>
        </div>
    </div>

    <!-- Menu Sections -->
    <div class="container pb-5" style="max-width: 800px;">
        @foreach ($sections as $section)
            <div class="mb-4">
                <h4 class="fw-bold text-dark border-bottom pb-2 mb-3">
                    <i class="bi bi-bookmark-star-fill text-primary me-1"></i> {{ $section->name }}
                </h4>

                @foreach ($section->categories as $category)
                    <div class="mb-3">
                        <h6 class="text-secondary fw-semibold mb-2">{{ $category->name }}</h6>

                        @foreach ($category->subcategories as $sub)
                            @foreach ($sub->items as $item)
                                <div class="card shadow-sm border-0 mb-3 rounded-3">
                                    <div class="card-body p-3">
                                        <div class="row align-items-center">
                                            <div class="col-8">
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <h6 class="fw-bold mb-0 text-dark">{{ $item->name }}</h6>
                                                    @if ($item->special_tags)
                                                        @foreach ($item->special_tags as $tag)
                                                            <span class="badge bg-warning text-dark"
                                                                style="font-size: 0.7rem;">{{ $tag }}</span>
                                                        @endforeach
                                                    @endif
                                                </div>
                                                <p class="text-muted small mb-2">
                                                    {{ Str::limit($item->description, 60) }}</p>
                                                <span
                                                    class="fs-5 fw-bold text-success">${{ number_format($item->price, 2) }}</span>
                                            </div>
                                            <div class="col-4 text-end">
                                                <img src="{{ asset('storage/' . $item->image) }}"
                                                    class="img-fluid item-card-img w-100" alt="{{ $item->name }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

</body>

</html>
