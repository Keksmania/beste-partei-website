<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    /**
     * Handle the API request for events.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $page = $request->query('page', 1);
        $limit = 10; // Limit per page
        $offset = ($page - 1) * $limit;

        $eventsQuery = Event::query();

        if ($search) {
            $eventsQuery->where('name', 'like', '%' . $search . '%');
        }

        $total = $eventsQuery->count();
        $events = $eventsQuery->orderBy('date', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        // Transform events to include the image URL
        $eventsTransformed = $events->map(function ($event) {
            return [
                'id' => $event->id,
                'name' => $event->name,
                'date' => $event->date,
                'description' => $event->description,
                'image' => $event->thumbnail
                    ? asset($event->thumbnail)
                    : url('/images/1.jpg'), // Placeholder image
            ];
        });

        return response()->json([
            'events' => $eventsTransformed,
            'current_page' => $page,
            'total_pages' => ceil($total / $limit),
        ]);
    }



    /**
     * Handle the request for a specific post.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function getPost(Request $request)
    {
        $event = Event::where("id", $request->route("id"))->first();
        if ($event) {
            return view("post", ["event" => $event]);
        } else {
            abort(403, "Post not found!");
        }
    }

    public function getPostApi(Request $request)
    {

            $event = Event::where("id", $request->route("id"))->first();
            if ($event) {
                return response()->json(["event" => $event]);
            } else {
                abort(403, "Post not found!");
            }
        
    }


    

    /**
     * Store a new event.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
     {
         $request->validate([
             'name' => 'required|string|max:255',
             'date' => 'required|date',
             'description' => 'required|string',
             'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
         ]);
     
         $imagePath = null;
         $thumbnailPath = null;
     
         if ($request->hasFile('image')) {
             $image = $request->file('image');
             $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
     
             // Store the original image
             $imagePath = $image->storeAs('images', $imageName, 'public');
     
             // Create and store the thumbnail
             $imageResource = null;
     
             // Load the image resource based on file type
             switch ($image->getClientOriginalExtension()) {
                 case 'jpeg':
                 case 'jpg':
                     $imageResource = imagecreatefromjpeg($image->getRealPath());
                     break;
                 case 'png':
                     $imageResource = imagecreatefrompng($image->getRealPath());
                     break;
                 case 'gif':
                     $imageResource = imagecreatefromgif($image->getRealPath());
                     break;
                 case 'webp':
                     $imageResource = imagecreatefromwebp($image->getRealPath());
                     break;
             }
     
             if ($imageResource) {
                 // Get the original dimensions
                 $originalWidth = imagesx($imageResource);
                 $originalHeight = imagesy($imageResource);
     
                 // Set the maximum dimensions for scaling
                 $maxWidth = 250;
                 $maxHeight = 250;
     
                 // Calculate the new scaled dimensions while preserving the aspect ratio
                 if ($originalWidth > $originalHeight) {
                     $scaledWidth = $maxWidth;
                     $scaledHeight = (int) ($originalHeight * ($maxWidth / $originalWidth));
                 } else {
                     $scaledHeight = $maxHeight;
                     $scaledWidth = (int) ($originalWidth * ($maxHeight / $originalHeight));
                 }
     
                 // Create a blank canvas for the scaled image
                 $scaledResource = imagecreatetruecolor($scaledWidth, $scaledHeight);
     
                 // Copy the original image onto the scaled canvas
                 imagecopyresampled(
                     $scaledResource,
                     $imageResource,
                     0,
                     0,
                     0,
                     0,
                     $scaledWidth,
                     $scaledHeight,
                     $originalWidth,
                     $originalHeight
                 );
     
                 // Calculate the crop position (center of the scaled image)
                 $cropX = max(0, ($scaledWidth - $maxWidth) / 2);
                 $cropY = max(0, ($scaledHeight - $maxHeight) / 2);
     
                 // Create a blank canvas for the final cropped thumbnail (250x150)
                 $thumbnailResource = imagecreatetruecolor($maxWidth, $maxHeight);
     
                 // Crop the center of the scaled image into the 250x150 canvas
                 imagecopyresampled(
                     $thumbnailResource,
                     $scaledResource,
                     0,
                     0,
                     $cropX,
                     $cropY,
                     $maxWidth,
                     $maxHeight,
                     $maxWidth,
                     $maxHeight
                 );
     
                 // Save the thumbnail to the storage path
                 $thumbnailPath = 'images/thumbnails/' . $imageName;
                 $thumbnailFullPath = public_path($thumbnailPath);
     
                 // Save based on file type
                 switch ($image->getClientOriginalExtension()) {
                     case 'jpeg':
                     case 'jpg':
                         imagejpeg($thumbnailResource, $thumbnailFullPath);
                         break;
                     case 'png':
                         imagepng($thumbnailResource, $thumbnailFullPath);
                         break;
                     case 'gif':
                         imagegif($thumbnailResource, $thumbnailFullPath);
                         break;
                     case 'webp':
                         imagewebp($thumbnailResource, $thumbnailFullPath);
                         break;
                 }
     
                 // Free resources
                 imagedestroy($imageResource);
                 imagedestroy($scaledResource);
                 imagedestroy($thumbnailResource);
             }
         }
     
         $event = Event::create([
             'name' => $request->name,
             'date' => $request->date,
             'description' => $request->description,
             'image' => $imagePath,
             'thumbnail' => $thumbnailPath,
         ]);
     
         return response()->json([
             'success' => true,
             'message' => 'Event created successfully!',
             'event' => $event->makeHidden(['image']), // Exclude the `image` attribute if necessary
         ], 201);
     }






        public function update(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'date' => 'required|date',
        'description' => 'required|string',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);

    $event = Event::findOrFail($id);

    $imagePath = $event->image;
    $thumbnailPath = $event->thumbnail;

    if ($request->hasFile('image')) {
        // Delete old image and thumbnail if they exist
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
        if ($thumbnailPath && Storage::disk('public')->exists($thumbnailPath)) {
            Storage::disk('public')->delete($thumbnailPath);
        }

        $image = $request->file('image');
        $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();

        // Store the original image
        $imagePath = $image->storeAs('images', $imageName, 'public');

        // Create and store the thumbnail
        $imageResource = null;

        // Load the image resource based on file type
        switch ($image->getClientOriginalExtension()) {
            case 'jpeg':
            case 'jpg':
                $imageResource = imagecreatefromjpeg($image->getRealPath());
                break;
            case 'png':
                $imageResource = imagecreatefrompng($image->getRealPath());
                break;
            case 'gif':
                $imageResource = imagecreatefromgif($image->getRealPath());
                break;
            case 'webp':
                $imageResource = imagecreatefromwebp($image->getRealPath());
                break;
        }

        if ($imageResource) {
            // Get the original dimensions
            $originalWidth = imagesx($imageResource);
            $originalHeight = imagesy($imageResource);

            // Set the maximum dimensions for scaling
            $maxWidth = 250;
            $maxHeight = 250;

            // Calculate the new scaled dimensions while preserving the aspect ratio
            if ($originalWidth > $originalHeight) {
                $scaledWidth = $maxWidth;
                $scaledHeight = (int)($originalHeight * ($maxWidth / $originalWidth));
            } else {
                $scaledHeight = $maxHeight;
                $scaledWidth = (int)($originalWidth * ($maxHeight / $originalHeight));
            }

            // Create a blank canvas for the scaled image
            $scaledResource = imagecreatetruecolor($scaledWidth, $scaledHeight);

            // Copy the original image onto the scaled canvas
            imagecopyresampled(
                $scaledResource,
                $imageResource,
                0, 0, 0, 0,
                $scaledWidth, $scaledHeight,
                $originalWidth, $originalHeight
            );

            // Calculate the crop position (center of the scaled image)
            $cropX = max(0, ($scaledWidth - $maxWidth) / 2);
            $cropY = max(0, ($scaledHeight - $maxHeight) / 2);

            // Create a blank canvas for the final cropped thumbnail (250x150)
            $thumbnailResource = imagecreatetruecolor($maxWidth, $maxHeight);

            // Crop the center of the scaled image into the 250x150 canvas
            imagecopyresampled(
                $thumbnailResource,
                $scaledResource,
                0, 0, $cropX, $cropY,
                $maxWidth, $maxHeight,
                $maxWidth, $maxHeight
            );

            // Save the thumbnail to the storage path
            $thumbnailPath = 'images/thumbnails/' . $imageName;
            $thumbnailFullPath = public_path($thumbnailPath);

            // Save based on file type
            switch ($image->getClientOriginalExtension()) {
                case 'jpeg':
                case 'jpg':
                    imagejpeg($thumbnailResource, $thumbnailFullPath);
                    break;
                case 'png':
                    imagepng($thumbnailResource, $thumbnailFullPath);
                    break;
                case 'gif':
                    imagegif($thumbnailResource, $thumbnailFullPath);
                    break;
                case 'webp':
                    imagewebp($thumbnailResource, $thumbnailFullPath);
                    break;
            }

            // Free resources
            imagedestroy($imageResource);
            imagedestroy($scaledResource);
            imagedestroy($thumbnailResource);
        }
    }

    $event->update([
        'name' => $request->name,
        'date' => $request->date,
        'description' => $request->description,
        'image' => $imagePath,
        'thumbnail' => $thumbnailPath,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Event updated successfully!',
        'event' => $event->makeHidden(['image']),
    ]);
}

   /**
     * Delete an event.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $event = Event::findOrFail($id);

        if ($event->image && $event->image !== 'images/1.jpg') {
            if (Storage::disk('public')->exists($event->image)) {
                Storage::disk('public')->delete($event->image);
            }
        }

        if ($event->thumbnail && $event->thumbnail !== 'images/1.jpg') {
            if (Storage::disk('public')->exists($event->thumbnail)) {
                Storage::disk('public')->delete($event->thumbnail);
            }
        }

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully!',
        ]);
    }
}
