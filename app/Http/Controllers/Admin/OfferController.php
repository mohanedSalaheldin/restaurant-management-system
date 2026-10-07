<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OfferRequest;
use App\Models\MenuItem;
use App\Models\Offer;
use App\Services\OfferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfferController extends Controller
{
    public function __construct(
        protected OfferService $offerService
    ) {}

    public function index(Request $request): View
    {
        $offers = Offer::withCount('items')
            ->when($request->filled('status'), function ($q) use ($request) {
                if ($request->status === 'active') {
                    $q->where('status', true);
                } elseif ($request->status === 'inactive') {
                    $q->where('status', false);
                }
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.offers.index', compact('offers'));
    }

    public function create(): View
    {
        $items = MenuItem::where('availability', 'available')->orderBy('name')->get();
        return view('admin.offers.create', compact('items'));
    }

    public function store(OfferRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $itemIds = $validated['items'];
        unset($validated['items']);

        $this->offerService->createOffer($validated, $itemIds);

        return redirect()->route('admin.offers.index')->with('success', 'Offer created successfully.');
    }

    public function edit(Offer $offer): View
    {
        $items = MenuItem::where('availability', 'available')->orderBy('name')->get();
        $selectedItems = $offer->items->pluck('id')->toArray();

        return view('admin.offers.edit', compact('offer', 'items', 'selectedItems'));
    }

    public function update(OfferRequest $request, Offer $offer): RedirectResponse
    {
        $validated = $request->validated();
        $itemIds = $validated['items'];
        unset($validated['items']);

        $this->offerService->updateOffer($offer, $validated, $itemIds);

        return redirect()->route('admin.offers.index')->with('success', 'Offer updated successfully.');
    }

    public function destroy(Offer $offer): RedirectResponse
    {
        $this->offerService->deleteOffer($offer);

        return redirect()->route('admin.offers.index')->with('success', 'Offer deleted successfully.');
    }
}
