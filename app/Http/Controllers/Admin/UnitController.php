<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\Unit;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UnitController extends Controller
{
    /**
     * Display the unit management page.
     *
     * @return Application|Factory|View
     */
    public function index(): View|Factory|Application
    {
        $units = Unit::ordered()->get();
        return view('admin-views.business-settings.unit-management', compact('units'));
    }

    /**
     * Store a newly created unit.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'unit_name' => 'required|string|max:50',
            'unit_short_name' => 'required|string|max:20|unique:units,unit_short_name',
            'unit_type' => 'required|in:weight,volume,length,piece,other'
        ], [
            'unit_name.required' => translate('Unit name is required'),
            'unit_short_name.required' => translate('Short name is required'),
            'unit_short_name.unique' => translate('This short name already exists'),
            'unit_type.required' => translate('Unit type is required'),
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Get the highest sort order and add 1
            $maxSortOrder = Unit::max('sort_order') ?? 0;

            Unit::create([
                'unit_name' => $request->unit_name,
                'unit_short_name' => strtolower(trim($request->unit_short_name)),
                'unit_type' => $request->unit_type,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => $maxSortOrder + 1,
            ]);

            Toastr::success(translate('Unit added successfully!'));
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error(translate('Failed to add unit. Please try again.'));
            return redirect()->back()->withInput();
        }
    }

    /**
     * Get unit data for editing.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function edit($id): JsonResponse
    {
        try {
            $unit = Unit::findOrFail($id);

            if ($unit->is_default) {
                return response()->json([
                    'success' => false,
                    'message' => translate('Default units cannot be edited!')
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $unit
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => translate('Unit not found!')
            ], 404);
        }
    }

    /**
     * Update the specified unit.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'unit_id' => 'required|exists:units,id',
            'unit_name' => 'required|string|max:50',
            'unit_short_name' => 'required|string|max:20|unique:units,unit_short_name,' . $request->unit_id,
            'unit_type' => 'required|in:weight,volume,length,piece,other'
        ], [
            'unit_name.required' => translate('Unit name is required'),
            'unit_short_name.required' => translate('Short name is required'),
            'unit_short_name.unique' => translate('This short name already exists'),
            'unit_type.required' => translate('Unit type is required'),
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $unit = Unit::findOrFail($request->unit_id);

            if ($unit->is_default) {
                Toastr::error(translate('Default units cannot be modified!'));
                return redirect()->back();
            }

            $unit->update([
                'unit_name' => $request->unit_name,
                'unit_short_name' => strtolower(trim($request->unit_short_name)),
                'unit_type' => $request->unit_type,
            ]);

            Toastr::success(translate('Unit updated successfully!'));
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error(translate('Failed to update unit. Please try again.'));
            return redirect()->back()->withInput();
        }
    }

    /**
     * Toggle the status of the specified unit.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function toggleStatus($id): JsonResponse
    {
        try {
            $unit = Unit::findOrFail($id);

            if ($unit->is_default) {
                return response()->json([
                    'success' => false,
                    'message' => translate('Default units cannot be deactivated!')
                ], 403);
            }

            $unit->is_active = !$unit->is_active;
            $unit->save();

            $status = $unit->is_active ? translate('activated') : translate('deactivated');

            return response()->json([
                'success' => true,
                'message' => translate('Unit') . ' ' . $status . ' ' . translate('successfully!'),
                'is_active' => $unit->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => translate('Failed to update status. Please try again.')
            ], 500);
        }
    }

    /**
     * Remove the specified unit.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $unit = Unit::findOrFail($id);

            if ($unit->is_default) {
                return response()->json([
                    'success' => false,
                    'message' => translate('Default units cannot be deleted!')
                ], 403);
            }

            // Check if unit is being used by any products
            if (!$unit->canBeDeleted()) {
                return response()->json([
                    'success' => false,
                    'message' => translate('This unit is being used by products and cannot be deleted!')
                ], 403);
            }

            $unit->delete();

            return response()->json([
                'success' => true,
                'message' => translate('Unit deleted successfully!')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => translate('Failed to delete unit. Please try again.')
            ], 500);
        }
    }

    /**
     * Update the sort order of units.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSortOrder(Request $request): JsonResponse
    {
        try {
            $units = $request->units; // Array of [id => sort_order]

            foreach ($units as $id => $sortOrder) {
                Unit::where('id', $id)->update(['sort_order' => $sortOrder]);
            }

            return response()->json([
                'success' => true,
                'message' => translate('Sort order updated successfully!')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => translate('Failed to update sort order. Please try again.')
            ], 500);
        }
    }
}

