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
        
        $arrivals = TodaysArrival::with(['arrivalBranch'])
            ->when($search, function($query, $search) {
                return $query->where('title', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $branches = TodaysArrivalBranch::where('is_active', 1)->get();
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
            'main_poster' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'poster_images' => 'nullable|array|max:5',
            'poster_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'whatsapp_message_template' => 'nullable|string|max:1000',
            'arrival_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $posterImages = [];
        $mainPoster = null;

        // Handle main poster upload
        if ($request->hasFile('main_poster')) {
            $mainPoster = $this->uploadImage($request->file('main_poster'), 'todays-arrival/main');
        }

        // Handle additional poster images
        if ($request->hasFile('poster_images')) {
            foreach ($request->file('poster_images') as $image) {
                if ($image->isValid()) {
                    $posterImages[] = $this->uploadImage($image, 'todays-arrival/poster');
                }
            }
        }

        TodaysArrival::create([
            'title' => $request->title,
            'description' => $request->description,
            'arrival_branch_id' => $request->arrival_branch_id,
            'product_ids' => $request->product_ids,
            'arrival_date' => $request->arrival_date,
            'main_poster' => $mainPoster,
            'poster_images' => $posterImages,
            'whatsapp_message_template' => $request->whatsapp_message_template ?? 'Hi! I\'m interested in {title} from today\'s arrival. Is it available?',
            'whatsapp_enabled' => $request->has('whatsapp_enabled'),
            'is_active' => true,
            'show_in_app' => $request->has('show_in_app'),
            'sort_order' => $request->sort_order ?? 0,
            'status' => 1,
        ]);

        return redirect()->route('admin.todays-arrival.add-new')
            ->with('success', translate('Today\'s arrival created successfully!'));
    }

    public function edit($id)
    {
        $arrival = TodaysArrival::findOrFail($id);
        $branches = TodaysArrivalBranch::where('status', 1)->get();
        $products = Product::where('status', 1)->get();
        
        return view('admin-views.todays-arrival.edit', compact('arrival', 'branches', 'products'));
    }

    public function update(Request $request, $id)
    {
        $arrival = TodaysArrival::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'branch_id' => 'required|exists:todays_arrival_branches,id',
            'product_id' => 'required|exists:products,id',
            'main_poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'poster_images' => 'nullable|array|max:5',
            'poster_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'whatsapp_message_template' => 'nullable|string|max:1000',
            'existing_images' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $posterImages = [];
        $mainPoster = $arrival->main_poster;
        
        // Handle existing images
        if ($request->has('existing_images')) {
            $posterImages = $request->existing_images;
        }

        // Handle main poster upload
        if ($request->hasFile('main_poster')) {
            // Delete old main poster
            if ($mainPoster) {
                $this->deleteImage($mainPoster, 'todays-arrival/main');
            }
            $mainPoster = $this->uploadImage($request->file('main_poster'), 'todays-arrival/main');
        }

        // Handle new poster images
        if ($request->hasFile('poster_images')) {
            foreach ($request->file('poster_images') as $image) {
                if ($image->isValid()) {
                    $posterImages[] = $this->uploadImage($image, 'todays-arrival/poster');
                }
            }
        }

        $arrival->update([
            'title' => $request->title,
            'branch_id' => $request->branch_id,
            'product_id' => $request->product_id,
            'main_poster' => $mainPoster,
            'poster_images' => json_encode($posterImages),
            'whatsapp_message_template' => $request->whatsapp_message_template ?? 'Hi! I\'m interested in {product_name} from today\'s arrival. Is it available?',
        ]);

        return redirect()->route('admin.todays-arrival.add-new')
            ->with('success', translate('Today\'s arrival updated successfully!'));
    }

    public function preview($id)
    {
        $arrival = TodaysArrival::with(['product', 'branch'])->findOrFail($id);
        return view('admin-views.todays-arrival.preview', compact('arrival'));
    }

    public function delete($id)
    {
        $arrival = TodaysArrival::findOrFail($id);

        // Delete main poster
        if ($arrival->main_poster) {
            $this->deleteImage($arrival->main_poster, 'todays-arrival/main');
        }

        // Delete poster images
        if ($arrival->poster_images) {
            $posterImages = json_decode($arrival->poster_images, true);
            if (is_array($posterImages)) {
                foreach ($posterImages as $image) {
                    $this->deleteImage($image, 'todays-arrival/poster');
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
        $arrival->update(['status' => $status]);

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