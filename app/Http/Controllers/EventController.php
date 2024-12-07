<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
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


}