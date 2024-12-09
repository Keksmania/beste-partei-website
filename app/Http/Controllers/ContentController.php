<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

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
        // Retrieve query parameters for pagination
        $offset = $request->query('offset', 0);
        $limit = $request->query('limit', 20);

        // Fetch events from the database with pagination
        $eventsQuery = Event::orderBy('date', 'desc'); // Sort by date
        $total = $eventsQuery->count(); // Total number of events
        $events = $eventsQuery->offset($offset)->limit($limit)->get();

        // Return JSON response
        return response()->json([
            'events' => $events,
            'total' => $total,
        ]);
    }

    public function getPost(Request $request){
        $event = Event::where("id", $request->route("id"))->first(); // Fetch a single event
        if ($event) {
            return view("post",  ["event" => $event]); // Pass the event's description to the view
        } else {
            abort(403, "Post not found!");
        }
    }
    


}