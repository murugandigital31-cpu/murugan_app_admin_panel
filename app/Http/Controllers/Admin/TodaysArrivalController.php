<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TodaysArrival;
use App\Model\TodaysArrivalBranch;
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

        // Get Today's Arrival Branches (from todays_arrival_branches table)
        $branches = TodaysArrivalBranch::where('is_active', true)->get();
        
        // If no active branches, get all branches for testing
        if ($branches->isEmpty()) {
            $branches = TodaysArrivalBranch::all();
        }
        
        $products = Product::where('status', 1)->get();
        
        return view('admin-views.todays-arrival.index', compact('arrivals', 'branches', 'products', 'search'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'arrival_branch_id' => 'required|exists:todays_arrival_branches,id',
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
                if ($image && $image->isValid()) {
                    $posterImages[] = $this->uploadImage($image, 'arrivals');
                }
            }
        }

        // Create the arrival
        TodaysArrival::create([
            'title' => $request->title,
            'description' => $request->description,
            'arrival_date' => $request->arrival_date,
            'arrival_branch_id' => $request->arrival_branch_id,
            'product_ids' => $request->product_ids,
            'main_poster' => $mainPoster,
            'poster_images' => $posterImages,
            'whatsapp_message_template' => $request->whatsapp_message_template,
            'whatsapp_enabled' => $request->has('whatsapp_enabled'),
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
        $branches = TodaysArrivalBranch::where('is_active', true)->get();
        $products = Product::where('status', 1)->get();
        
        return view('admin-views.todays-arrival.edit', compact('arrival', 'branches', 'products'));
    }

    public function update(Request $request, $id)
    {
        $arrival = TodaysArrival::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'arrival_branch_id' => 'required|exists:todays_arrival_branches,id',
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
                if ($image && $image->isValid()) {
                    $posterImages[] = $this->uploadImage($image, 'arrivals');
                }
            }
        }

        // Ensure we don't exceed max images (5)
        if (count($posterImages) > 5) {
            $posterImages = array_slice($posterImages, 0, 5);
        }

        $arrival->update([
            'title' => $request->title,
            'description' => $request->description,
            'arrival_date' => $request->arrival_date,
            'arrival_branch_id' => $request->arrival_branch_id,
            'product_ids' => $request->product_ids,
            'main_poster' => $mainPoster,
            'poster_images' => $posterImages,
            'whatsapp_message_template' => $request->whatsapp_message_template,
            'whatsapp_enabled' => $request->has('whatsapp_enabled'),
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
        
        // Create public uploads directory for direct web access
        $publicUploadsPath = public_path('uploads/' . $path);
        if (!file_exists($publicUploadsPath)) {
            mkdir($publicUploadsPath, 0755, true);
        }
        
        // Store directly in public uploads directory
        $image->move($publicUploadsPath, $filename);
        
        // Also backup to Laravel storage
        $uploadedFile = $publicUploadsPath . '/' . $filename;
        $storagePath = 'public/' . $path . '/' . $filename;
        Storage::put($storagePath, file_get_contents($uploadedFile));
        
        return $filename;
    }

    /**
     * Delete image from storage
     */
    private function deleteImage($filename, $path)
    {
        // Delete from main storage
        if ($filename && Storage::exists('public/' . $path . '/' . $filename)) {
            Storage::delete('public/' . $path . '/' . $filename);
        }
        
        // Delete from public uploads directory (new path)
        $publicUploadFile = public_path('uploads/' . $path . '/' . $filename);
        if (file_exists($publicUploadFile)) {
            unlink($publicUploadFile);
        }
        
        // Also delete from old public storage location
        $publicStorageFile = public_path('storage/app/public/' . $path . '/' . $filename);
        if (file_exists($publicStorageFile)) {
            unlink($publicStorageFile);
        }
    }
}