<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TodaysArrival;
use App\Models\TodaysArrivalBranch;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TodaysArrivalController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $arrivals = TodaysArrival::with(['product', 'branch'])
            ->when($search, function($query, $search) {
                return $query->where('title', 'like', "%{$search}%")
                            ->orWhereHas('product', function($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%");
                            });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $branches = TodaysArrivalBranch::where('status', 1)->get();
        $products = Product::where('status', 1)->get();
        
        return view('admin-views.todays-arrival.index', compact('arrivals', 'branches', 'products', 'search'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'branch_id' => 'required|exists:todays_arrival_branches,id',
            'product_id' => 'required|exists:products,id',
            'main_poster' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'poster_images' => 'nullable|array|max:5',
            'poster_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'whatsapp_message_template' => 'nullable|string|max:1000',
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
            'branch_id' => $request->branch_id,
            'product_id' => $request->product_id,
            'main_poster' => $mainPoster,
            'poster_images' => json_encode($posterImages),
            'whatsapp_message_template' => $request->whatsapp_message_template ?? 'Hi! I\'m interested in {product_name} from today\'s arrival. Is it available?',
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