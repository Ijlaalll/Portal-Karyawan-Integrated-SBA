<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Facility;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Services\RoomService;

class RoomController extends Controller
{
    public function __construct(
        protected RoomService $roomService
    ) {}

    /**
     * Display a listing of rooms.
     */
    public function index()
    {
        $rooms = Room::with('facilities')->paginate(10);
        return view('admin.rooms.index', compact('rooms'));
    }

    /**
     * Show the form for creating a new room.
     */
    public function create()
    {
        $facilities = Facility::all();
        return view('admin.rooms.create', compact('facilities'));
    }

    /**
     * Store a newly created room in storage.
     */
    public function store(StoreRoomRequest $request)
    {
        $this->roomService->createRoom($request->validated());

        return redirect()
            ->route('admin.rooms.index')
            ->with('success', 'Room created successfully.');
    }

    /**
     * Display the specified room.
     */
    public function show(Room $room)
    {
        $room->load('facilities');
        return view('admin.rooms.show', compact('room'));
    }

    /**
     * Show the form for editing the specified room.
     */
    public function edit(Room $room)
    {
        $room->load('facilities');
        $facilities = Facility::all();
        return view('admin.rooms.edit', compact('room', 'facilities'));
    }

    /**
     * Update the specified room in storage.
     */
    public function update(UpdateRoomRequest $request, Room $room)
    {
        $this->roomService->updateRoom($room, $request->validated());

        return redirect()
            ->route('admin.rooms.index')
            ->with('success', 'Room updated successfully.');
    }
}
