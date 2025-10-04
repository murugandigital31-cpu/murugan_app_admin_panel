<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TodaysArrivalBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TodaysArrivalBranchController extends Controller
{
    /**
     * Display a listing of branches
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $branches = TodaysArrivalBranch::withCount('todaysArrivals')
                                      ->when($search, function($query, $search) {
                                          return $query->where('name', 'like', "%{$search}%")
                                                      ->orWhere('whatsapp_number', 'like', "%{$search}%");
                                      })
                                      ->orderBy('name')
                                      ->paginate(10);
        
        return view('admin-views.todays-arrival-branch.index', compact('branches', 'search'));
    }

    /**
     * Store a newly created branch
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:todays_arrival_branches,name',
            'whatsapp_number' => 'required|string|max:20',
            'contact_person' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
        ]);

        // Format WhatsApp number
        $validated['whatsapp_number'] = $this->formatWhatsappNumber($validated['whatsapp_number']);
        $validated['status'] = 1; // Active by default

        TodaysArrivalBranch::create($validated);

        return redirect()->route('admin.todays-arrival-branch.add-new')
                        ->with('success', translate('Branch created successfully!'));
    }

    /**
     * Show the form for editing the specified branch
     */
    public function edit($id)
    {
        $branch = TodaysArrivalBranch::findOrFail($id);
        return view('admin-views.todays-arrival-branch.edit', compact('branch'));
    }

    /**
     * Update the specified branch
     */
    public function update(Request $request, $id)
    {
        $branch = TodaysArrivalBranch::findOrFail($id);
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('todays_arrival_branches')->ignore($branch->id)],
            'whatsapp_number' => 'required|string|max:20',
            'contact_person' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
        ]);

        // Format WhatsApp number
        $validated['whatsapp_number'] = $this->formatWhatsappNumber($validated['whatsapp_number']);

        $branch->update($validated);

        return redirect()->route('admin.todays-arrival-branch.add-new')
                        ->with('success', translate('Branch updated successfully!'));
    }

    /**
     * Remove the specified branch
     */
    public function delete($id)
    {
        $branch = TodaysArrivalBranch::findOrFail($id);
        
        // Check if branch has any arrivals
        if ($branch->todaysArrivals()->count() > 0) {
            return redirect()->back()
                           ->with('error', translate('Cannot delete branch with existing arrivals. Please reassign or delete arrivals first.'));
        }

        $branch->delete();

        return redirect()->route('admin.todays-arrival-branch.add-new')
                        ->with('success', translate('Branch deleted successfully!'));
    }

    /**
     * Toggle branch status
     */
    public function status($id, $status)
    {
        $branch = TodaysArrivalBranch::findOrFail($id);
        $branch->update(['status' => $status]);

        $statusText = $status ? translate('activated') : translate('deactivated');
        return redirect()->back()
                        ->with('success', translate('Branch') . ' ' . $statusText . ' ' . translate('successfully!'));
    }

    /**
     * Format WhatsApp number to international format
     */
    private function formatWhatsappNumber($number)
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $number);
        
        // Add country code if not present (assuming UAE +971)
        if (!str_starts_with($cleaned, '971') && !str_starts_with($cleaned, '+971')) {
            if (str_starts_with($cleaned, '0')) {
                $cleaned = '971' . substr($cleaned, 1);
            } else {
                $cleaned = '971' . $cleaned;
            }
        }
        
        return $cleaned;
    }
}