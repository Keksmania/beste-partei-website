<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Label\Font\OpenSans;
use Endroid\QrCode\Label\LabelAlignment;

class ContentController extends Controller
{
    /**event_key
     * Handle the API request for events.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

     public function index(Request $request)
     {
         $search = $request->query('search', '');
         $page = $request->query('page', 1);
         $limit = $request->query('limit', 10); // Limit per page
         $year = $request->query('year', null);
         $month = $request->query('month', null);
         $isEvents = $request->query('events', 'false') === 'true';
     
         if ($isEvents) {
             $eventsQuery = Event::with('post'); // Ensure the post relationship is eager-loaded
     
             if ($search) {
                 $eventsQuery->whereHas('post', function ($query) use ($search) {
                     $query->where('name', 'like', '%' . $search . '%');
                 });
             }
             if ($year) {
                 $eventsQuery->whereYear('date', $year);
             }
             if ($month) {
                 $eventsQuery->whereMonth('date', $month);
             }
     
             $total = $eventsQuery->count();
             $events = $eventsQuery->orderBy('date', 'desc')
                 ->offset(($page - 1) * $limit)
                 ->limit($limit)
                 ->get();
     
             // Transform events to include the image URL
             $eventsTransformed = $events->map(function ($event) {
                 $post = $event->post;
     
                 if ($post) {
                     return [
                         'id' => $event->post_id,
                         'name' => $post->name,
                         'date' => $event->date,
                         'image' => $post->thumbnail
                             ? asset($post->thumbnail)
                             : url('/images/1.jpg'), // Placeholder image
                     ];
                 }
                 
                 return null;
             })->filter();
     
             return response()->json([
                 'events' => $eventsTransformed,
                 'current_page' => $page,
                 'total' => $total,
             ]);
         } else {
             $postsQuery = Post::query(); // Query posts directly
     
             if ($search) {
                 $postsQuery->where('name', 'like', '%' . $search . '%');
             }
     
             $total = $postsQuery->count();
             $posts = $postsQuery->orderBy('created_at', 'desc')
                 ->offset(($page - 1) * $limit)
                 ->limit($limit)
                 ->get();
     
             // Transform posts to include event-related data if the post is an event
             $postsTransformed = $posts->map(function ($post) {
                 $event = Event::where('post_id', $post->id)->first();
                 return [
                     'id' => $post->id,
                     'name' => $post->name,
                     'date' => $event ? $event->date : $post->created_at->format('Y-m-d'),
                     'is_event' => $event ? true : false,
                     'event_id' => $event ? $event->id : null,
                 ];
             });
     
             return response()->json([
                 'posts' => $postsTransformed,
                 'current_page' => $page,
                 'total' => $total,
             ]);
         }
     }

     public function getPostCount(Request $request)
     {
         $year = $request->query('year', null);
         $month = $request->query('month', null);
         $countPosts = $request->query('post', 'false') === 'true';
         $countEvents = $request->query('event', 'false') === 'true';
     
         if ($countEvents && !$countPosts) {
             // Count only events using the event's date instead of the post's created_at
             $eventsQuery = Event::query();
             if ($year) {
                 $eventsQuery->whereYear('date', $year);
             }
             if ($month) {
                 $eventsQuery->whereMonth('date', $month);
             }
             $total = $eventsQuery->count();
         } elseif ($countPosts && !$countEvents) {
             // Count only posts that are not events using the post's created_at date
             $postsQuery = Post::query();
             if ($year) {
                 $postsQuery->whereYear('created_at', $year);
             }
             if ($month) {
                 $postsQuery->whereMonth('created_at', $month);
             }
             $total = $postsQuery->whereDoesntHave('event')->count();
         } else {
             // If both parameters are true or both are false, count all posts using created_at filter
             $postsQuery = Post::query();
             if ($year) {
                 $postsQuery->whereYear('created_at', $year);
             }
             if ($month) {
                 $postsQuery->whereMonth('created_at', $month);
             }
             $total = $postsQuery->count();
         }
     
         return response()->json([
             'total' => $total
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
         $postId = $request->route("id");
         $post = Post::find($postId);
     
         if ($post) {
             // Fetch the event associated with the post
             $event = Event::where('post_id', $postId)->first();
     
             // Determine the date to use
             $post->date = $event ? $event->date : $post->created_at;
     
             return view("post", [
                 "post" => $post,
             ]);
         } else {
             abort(403, "Post not found!");
         }
     }

    public function getPostApi(Request $request)
    {
        $post = Post::where("id", $request->route("id"))->first();
        if ($post) {
            // Fetch the event associated with the post
            $event = Event::where('post_id', $request->route("id"))->first();
     
            // Determine the date to use
            $post->is_event = $event ? true : false;
            $post->date = $event ? $event->date : $post->created_at;
            return response()->json(["post" => $post]);
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
            $data = $request->validate([
            'name'      => 'required|string|max:255',
            'description'=> 'required|string',
            'is_event'  => 'required|boolean',
            'date'      => 'required_if:is_event,true|nullable|date',
            'image'     => 'nullable|image|max:2048',
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
     
         // Create the post
         $post = Post::create([
             'name' => $request->name,
             'description' => $request->description,
             'image' => $imagePath,
             'thumbnail' => $thumbnailPath,
         ]);

         if ($request->is_event) {
             // Generate a unique key for the event
             $key = Str::uuid();
     
             $event = Event::create([
                 'post_id' => $post->id,
                 'date' => $request->date,
                 'key' => $key,
             ]);
     
             // Create the QR code directory if it does not exist
             $qrCodeDir = public_path('images/qrcode');
     
             // Generate the QR code
             $url = url("/api/events/attend/markAttendanceQr?key={$key}");
             $builder = new Builder(
                writer: new PngWriter(),
                writerOptions: [],
                validateResult: false,
                data: $url ,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                size: 300,
                margin: 10,
                roundBlockSizeMode: RoundBlockSizeMode::Margin,
                labelText: 'Anwesenheit bestätigen',
                labelFont: new OpenSans(20),
                labelAlignment: LabelAlignment::Center
            );
            
            $result = $builder->build();
     
             $qrCodePath = $qrCodeDir . '/' . $key . '.png';
             $result->saveToFile($qrCodePath);
     
             return response()->json([
                 'success' => true,
                 'message' => 'Event created successfully!',
                 'event' => $event->makeHidden(['image']), // Exclude the `image` attribute if necessary
                 'qr_code_url' => asset('images/qrcode/' . $key . '.png'),
             ], 201);
         }
     
         return response()->json([
             'success' => true,
             'message' => 'Post created successfully!',
             'post' => $post,
         ], 201);
     }
     

     public function downloadQrCode($eventId)
     {
         $event = Event::findOrFail($eventId);
         $qrCodePath = public_path('images/qrcode/' . $event->key . '.png');
     
         if (!file_exists($qrCodePath)) {
             abort(404, 'QR Code not found');
         }
     
         return response()->download($qrCodePath, $event->name . '_qrcode.png');
     }



        public function update(Request $request, $id)
{
    $request->validate([
        'name'      => 'required|string|max:255',
        'description'=> 'required|string',
        'is_event'  => 'required|boolean',
        'date'      => 'required_if:is_event,true|nullable|date',
        'image'     => 'nullable|image|max:2048',
    ]);

    $post = Post::findOrFail($id);
    $event = Event::where('post_id', $post->id)->first();

    $imagePath = $post->image;
    $thumbnailPath = $post->thumbnail;

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

    $post->update([
        'name' => $request->name,
        'description' => $request->description,
        'image' => $imagePath,
        'thumbnail' => $thumbnailPath,
    ]);

    if ($request->is_event) {
        if ($event) {
            $event->update([
                'date' => $request->date,
            ]);
        } else {
            // Generate a unique key for the event
            $key = Str::uuid();

            Event::create([
                'post_id' => $post->id,
                'date' => $request->date,
                'key' => $key,
            ]);
        }
    } elseif ($event) {
        $event->delete();
    }

    return response()->json([
        'success' => true,
        'message' => 'Post updated successfully!',
        'post' => $post,
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
        $post = Post::findOrFail($id);
        $event = Event::where('post_id', $post->id)->first();

        // Delete associated event if it exists
        if ($event) {
            $event->delete();
        }

        // Delete the post's image and thumbnail if they exist
        if ($post->image && Storage::disk('public')->exists($post->image)) {
            Storage::disk('public')->delete($post->image);
        }
        if ($post->thumbnail && Storage::disk('public')->exists($post->thumbnail)) {
            Storage::disk('public')->delete($post->thumbnail);
        }

        // Delete the post
        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post and associated event deleted successfully!',
        ]);
    }

    public function markAttendance(Request $request, $postId)
{
    $userId = $request->input('user_id');
    $event = Event::with('users:id,name,email')->where('post_id', $postId)->firstOrFail();

    // Check if the user is already marked as attending
    $alreadyAttending = DB::table('event_user')
        ->where('event_id', $event->id)
        ->where('user_id', $userId)
        ->exists();

    if ($alreadyAttending) {
        return response()->json([
            'success' => false,
            'message' => 'User is already marked as attending.',
        ], 400);
    }

    // Mark attendance
    $event->users()->attach($userId, ['attended_at' => now()]);

    return response()->json([
        'success' => true,
        'message' => 'Attendance marked successfully.',
    ]);
}



    public function markAttendanceQr(Request $request)
    {
        $key = $request->input('key');
    
        if ($key) {
            $event = Event::where("key", $key)->first();
    
            if ($event) {
                // Check if the event date is today
                $eventDate = $event->date;
                $currentDate = now()->format('Y-m-d');
    
                if ($eventDate !== $currentDate) {
                    return redirect('/?result=fail');
                }
    
                if (Auth::guest()) {
                    // Store the intended URL and redirect to login with the key parameter
                    return redirect()->guest(route('login', ['key' => $key]));
                } else {
                    // User is logged in, mark attendance
                    $userId = Auth::id();
                    // Check if the user is already marked as attending
                    $alreadyAttending = DB::table('event_user')
                        ->where('event_id', $event->id)
                        ->where('user_id', $userId)
                        ->exists();
    
                    if ($alreadyAttending) {
                        return redirect('/?result=fail');
                    }
    
                    // Mark attendance
                    $event->users()->attach($userId, ['attended_at' => now()]);
    
                    return redirect('/?result=success');
                }
            } else {
                return redirect('/?result=fail');
            }
        }
    
        return redirect('/?result=fail');
    }
/**
 * Remove a user from an event (unattend).
 *
 * @param Request $request
 * @param int $eventId
 * @return \Illuminate\Http\JsonResponse
 */
public function removeAttendance(Request $request, $postId)
{
    $userId = $request->input('user_id');
    $event = Event::with('users:id,name,email')->where('post_id', $postId)->firstOrFail();

    // Detach the user from the event
    $event->users()->detach($userId);

    return response()->json([
        'success' => true,
        'message' => 'Attendance removed successfully.',
    ]);
}

/**
 * Get the list of attendees for an event.
 *
 * @param int $eventId
 * @return \Illuminate\Http\JsonResponse
 */
public function getAttendees($postId)
{

    $event = Event::with('users:id,name,email')->where('post_id', $postId)->firstOrFail();

    $event->users->transform(function ($user) {
        $user->email = Crypt::decryptString($user->email);
        return $user;
    });

    
    return response()->json([
        'success' => true,
        'attendees' => $event->users,
    ]);
}



}
