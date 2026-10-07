@extends('layouts.app')

@section('title', 'Create Offer')
@section('page_title', 'Create New Offer')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Offer Configurations</h6>
                    <a href="{{ route('admin.offers.index') }}" class="btn btn-sm btn-outline-secondary">Back to List</a>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.offers.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Offer Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                                    placeholder="e.g. Weekend Special, Happy Hour" maxlength="100" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2" maxlength="500">{{ old('description') }}</textarea>
                            </div>

                            <!-- Discount Settings -->
                            <div class="col-md-6">
                                <label class="form-label">Discount Type <span class="text-danger">*</span></label>
                                <select name="discount_type" class="form-select" required>
                                    <option value="percentage"
                                        {{ old('discount_type') === 'percentage' ? 'selected' : '' }}>Percentage (%)
                                    </option>
                                    <option value="fixed" {{ old('discount_type') === 'fixed' ? 'selected' : '' }}>Fixed
                                        Amount ($)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Discount Value <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="discount_value"
                                    class="form-control" value="{{ old('discount_value') }}" placeholder="e.g. 20 or 5.00"
                                    required>
                            </div>

                            <!-- Dates -->
                            <div class="col-md-6">
                                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control"
                                    value="{{ old('start_date', date('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">End Date <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control"
                                    value="{{ old('end_date', date('Y-m-d', strtotime('+7 days'))) }}" required>
                            </div>

                            <!-- Times -->
                            <div class="col-md-6">
                                <label class="form-label">Start Time (Optional)</label>
                                <input type="time" name="start_time" class="form-control"
                                    value="{{ old('start_time') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">End Time (Optional)</label>
                                <input type="time" name="end_time" class="form-control" value="{{ old('end_time') }}">
                            </div>

                            <!-- Applicable Days -->
                            <div class="col-12">
                                <label class="form-label d-block fw-semibold">Applicable Days (Leave empty to apply all
                                    days)</label>
                                @php
                                    $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                                @endphp
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach ($days as $day)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="applicable_days[]"
                                                value="{{ $day }}" id="day_{{ $day }}"
                                                {{ is_array(old('applicable_days')) && in_array($day, old('applicable_days')) ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="day_{{ $day }}">{{ $day }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Applicable Items Multi-Select -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Applicable Menu Items <span
                                        class="text-danger">*</span></label>
                                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                    <div class="row g-2">
                                        @foreach ($items as $item)
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="items[]"
                                                        value="{{ $item->id }}" id="item_{{ $item->id }}"
                                                        {{ is_array(old('items')) && in_array($item->id, old('items')) ? 'checked' : '' }}>
                                                    <label class="form-check-label d-flex justify-content-between pe-3"
                                                        for="item_{{ $item->id }}">
                                                        <span>{{ $item->name }}</span>
                                                        <span
                                                            class="text-muted">${{ number_format($item->price, 2) }}</span>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Toggles -->
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="status" id="statusSwitch"
                                        value="1" {{ old('status', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="statusSwitch">Active Status</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="display_on_menu"
                                        id="badgeSwitch" value="1"
                                        {{ old('display_on_menu', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="badgeSwitch">Display "OFFER" Badge on
                                        Menu</label>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-success w-100">Save Offer</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
