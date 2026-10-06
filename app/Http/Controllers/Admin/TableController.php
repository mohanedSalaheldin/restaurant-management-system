<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TableRequest;
use App\Models\Table;
use App\Services\TableService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableController extends Controller
{
    public function __construct(
        protected TableService $tableService
    ) {}

    public function index(Request $request): View
    {
        $tables = Table::query()
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->type))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->orderBy('table_number')
            ->paginate(12)
            ->withQueryString();

        return view('admin.tables.index', compact('tables'));
    }

    public function create(): View
    {
        return view('admin.tables.create');
    }

    public function store(TableRequest $request): RedirectResponse
    {
        $this->tableService->createTable($request->validated());

        return redirect()->route('admin.tables.index')
            ->with('success', 'Table created successfully.');
    }

    public function edit(Table $table): View
    {
        return view('admin.tables.edit', compact('table'));
    }

    public function update(TableRequest $request, Table $table): RedirectResponse
    {
        $this->tableService->updateTable($table, $request->validated());

        return redirect()->route('admin.tables.index')
            ->with('success', 'Table updated successfully.');
    }

    public function destroy(Table $table): RedirectResponse
    {
        $this->tableService->deleteTable($table);

        return redirect()->route('admin.tables.index')
            ->with('success', 'Table deleted successfully.');
    }

    /**
     * إعادة توليد رمز الـ QR للطاولة
     */
    public function regenerateQr(Table $table): RedirectResponse
    {
        $this->tableService->regenerateQrToken($table);

        return back()->with('success', "QR Code for table {$table->table_number} regenerated successfully.");
    }
}
