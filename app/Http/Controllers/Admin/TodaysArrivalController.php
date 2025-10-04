<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\TodaysArrival;
use App\Model\Branch;
use App\Model\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TodaysArrivalController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $arrivals = TodaysArrival::when($search, function($query, $search) {
                return $query->where('title', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $branches = Branch::where('status', 1)->get();
        $products = Product::where('status', 1)->get();
        
        return view('admin-views.todays-arrival.index', compact('arrivals', 'branches', 'products', 'search'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'branch_ids' => 'required|array',
            'branch_ids.*' => 'exists:branches,id',
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'arrival_date' => 'required|date',
            'main_poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'poster_images' => 'nullable|array|max:5',
            'poster_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Handle main poster upload
        $mainPoster = null;
        if ($request->hasFile('main_poster')) {
            $mainPoster = $this->uploadImage($request->file('main_poster'), 'arrivals');
        }

        // Handle poster images upload
        $posterImages = [];
        if ($request->hasFile('poster_images')) {
            foreach ($request->file('poster_images') as $image) {
                $posterImages[] = $this->uploadImage($image, 'arrivals');
            }
        }

        // Create the arrival
        TodaysArrival::create([
            'title' => $request->title,
            'description' => $request->description,
            'arrival_date' => $request->arrival_date,
            'branch_id' => json_encode($request->branch_ids),
            'product_ids' => json_encode($request->product_ids),
            'main_poster' => $mainPoster,
            'poster_images' => json_encode($posterImages),
            'is_active' => true,
            'show_in_app' => $request->has('show_in_app'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.todays-arrival.add-new')
            ->with('success', translate('Today\'s arrival created successfully!'));
    }

    public function edit($id)
    {
        $arrival = TodaysArrival::findOrFail($id);
        $branches = Branch::where('status', 1)->get();
        $products = Product::where('status', 1)->get();
        
        return view('admin-views.todays-arrival.edit', compact('arrival', 'branches', 'products'));
    }

    public function update(Request $request, $id)
    {
        $arrival = TodaysArrival::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'branch_ids' => 'required|array',
            'branch_ids.*' => 'exists:branches,id',
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'arrival_date' => 'required|date',
            'main_poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'poster_images' => 'nullable|array|max:5',
            'poster_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'existing_images' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Handle main poster
        $mainPoster = $arrival->main_poster;
        if ($request->hasFile('main_poster')) {
            // Delete old main poster
            if ($mainPoster) {
                $this->deleteImage($mainPoster, 'arrivals');
            }
            $mainPoster = $this->uploadImage($request->file('main_poster'), 'arrivals');
        }

        $posterImages = $arrival->poster_images ? $arrival->poster_images : [];
        
        // Handle existing images
        if ($request->has('existing_images')) {
            $posterImages = $request->existing_images;
        }

        // Handle new poster images
        if ($request->hasFile('poster_images')) {
            foreach ($request->file('poster_images') as $image) {
                if ($image->isValid()) {
                    $posterImages[] = $this->uploadImage($image, 'arrivals');
                }
            }
        }

        $arrival->update([
            'title' => $request->title,
            'description' => $request->description,
            'arrival_date' => $request->arrival_date,
            'branch_id' => json_encode($request->branch_ids),
            'product_ids' => json_encode($request->product_ids),
            'main_poster' => $mainPoster,
            'poster_images' => json_encode($posterImages),
            'show_in_app' => $request->has('show_in_app'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.todays-arrival.add-new')
            ->with('success', translate('Today\'s arrival updated successfully!'));
    }

    public function preview($id)
    {
        $arrival = TodaysArrival::findOrFail($id);
        return view('admin-views.todays-arrival.preview', compact('arrival'));
    }

    public function delete($id)
    {
        $arrival = TodaysArrival::findOrFail($id);

        // Delete main poster
        if ($arrival->main_poster) {
            $this->deleteImage($arrival->main_poster, 'arrivals');
        }

        // Delete poster images
        if ($arrival->poster_images) {
            $posterImages = is_array($arrival->poster_images) ? $arrival->poster_images : json_decode($arrival->poster_images, true);
            if (is_array($posterImages)) {
                foreach ($posterImages as $image) {
                    $this->deleteImage($image, 'arrivals');
                }
            }
        }

        $arrival->delete();

        return redirect()->route('admin.todays-arrival.add-new')
            ->with('success', translate('Today\'s arrival deleted successfully!'));
    }

    public function status($id, $status)
    {
        $arrival = TodaysArrival::findOrFail($id);
        $arrival->update(['is_active' => $status]);

        $statusText = $status ? translate('activated') : translate('deactivated');
        return redirect()->back()
            ->with('success', translate('Today\'s arrival') . ' ' . $statusText . ' ' . translate('successfully!'));
    }

    /**
     * Upload image to storage
     */
    private function uploadImage($image, $path)
    {
        $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        $image->storeAs('public/' . $path, $filename);
        return $filename;
    }

    /**
     * Delete image from storage
     */
    private function deleteImage($filename, $path)
    {
        if ($filename && Storage::exists('public/' . $path . '/' . $filename)) {
            Storage::delete('public/' . $path . '/' . $filename);
        }
    }
}