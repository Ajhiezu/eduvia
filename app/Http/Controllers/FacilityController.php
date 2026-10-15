<?php

namespace App\Http\Controllers;

use App\Models\FacilityIncident;
use App\Models\FacilityReservation;
use App\Models\School;
use App\Models\SchoolRoom;
use App\Services\SchoolOperationsService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FacilityController extends Controller
{
    // =========================================================================
    // ROOMS & LABORATORIES
    // =========================================================================

    public function roomsIndex(Request $request): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $type = $request->query('type');
        $search = $request->query('search');

        $rooms = SchoolRoom::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($search, function ($q, $s) {
                $q->where(function ($sub) use ($s) {
                    $sub->where('name', 'like', "%{$s}%")
                        ->orWhere('code', 'like', "%{$s}%")
                        ->orWhere('location', 'like', "%{$s}%");
                });
            })
            ->withCount(['reservations' => fn ($q) => $q->where('status', 'approved')])
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $roomTypes = [
            'laboratory' => 'Laboratorium',
            'classroom' => 'Ruang Kelas',
            'hall' => 'Aula / Gedung Serbaguna',
            'sports_field' => 'Fasilitas Olahraga',
            'meeting_room' => 'Ruang Rapat',
            'workshop' => 'Bengkel / Workshop SMK',
            'library_room' => 'Ruang Perpustakaan',
        ];

        return view('operations.facilities.rooms.index', compact('rooms', 'roomTypes', 'type', 'search'));
    }

    public function roomCreate(): View
    {
        $roomTypes = [
            'laboratory' => 'Laboratorium',
            'classroom' => 'Ruang Kelas',
            'hall' => 'Aula / Gedung Serbaguna',
            'sports_field' => 'Fasilitas Olahraga',
            'meeting_room' => 'Ruang Rapat',
            'workshop' => 'Bengkel / Workshop SMK',
            'library_room' => 'Ruang Perpustakaan',
        ];

        return view('operations.facilities.rooms.create', compact('roomTypes'));
    }

    public function roomStore(Request $request): RedirectResponse
    {
        $school = School::current();
        abort_if(! $school, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:50'],
            'type' => ['required', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'location' => ['nullable', 'string', 'max:150'],
            'manager_name' => ['nullable', 'string', 'max:150'],
            'facilities_description' => ['nullable', 'string'],
        ]);

        $room = SchoolRoom::create([
            'school_id' => $school->id,
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'type' => $validated['type'],
            'capacity' => $validated['capacity'],
            'location' => $validated['location'] ?? null,
            'manager_name' => $validated['manager_name'] ?? null,
            'facilities_description' => $validated['facilities_description'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('operations.facilities.rooms.show', $room)
            ->with('status', "Ruangan / fasilitas '{$room->name}' berhasil didaftarkan.");
    }

    public function roomShow(SchoolRoom $room): View
    {
        $school = School::current();
        abort_if($room->school_id !== $school?->id, 403);

        $upcomingReservations = $room->reservations()
            ->where('reservation_date', '>=', Carbon::today())
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->with('user')
            ->orderBy('reservation_date')
            ->orderBy('start_time')
            ->get();

        $incidents = $room->incidents()
            ->with('reporter')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('operations.facilities.rooms.show', compact('room', 'upcomingReservations', 'incidents'));
    }

    public function roomEdit(SchoolRoom $room): View
    {
        $school = School::current();
        abort_if($room->school_id !== $school?->id, 403);

        $roomTypes = [
            'laboratory' => 'Laboratorium',
            'classroom' => 'Ruang Kelas',
            'hall' => 'Aula / Gedung Serbaguna',
            'sports_field' => 'Fasilitas Olahraga',
            'meeting_room' => 'Ruang Rapat',
            'workshop' => 'Bengkel / Workshop SMK',
            'library_room' => 'Ruang Perpustakaan',
        ];

        return view('operations.facilities.rooms.edit', compact('room', 'roomTypes'));
    }

    public function roomUpdate(Request $request, SchoolRoom $room): RedirectResponse
    {
        $school = School::current();
        abort_if($room->school_id !== $school?->id, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:50'],
            'type' => ['required', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'location' => ['nullable', 'string', 'max:150'],
            'manager_name' => ['nullable', 'string', 'max:150'],
            'facilities_description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $room->update($validated);

        return redirect()->route('operations.facilities.rooms.show', $room)
            ->with('status', "Data fasilitas '{$room->name}' berhasil diperbarui.");
    }

    public function roomDestroy(SchoolRoom $room): RedirectResponse
    {
        $school = School::current();
        abort_if($room->school_id !== $school?->id, 403);

        $activeReservations = $room->reservations()
            ->where('reservation_date', '>=', Carbon::today())
            ->where('status', 'approved')
            ->count();

        if ($activeReservations > 0) {
            return redirect()->back()->with('error', 'Ruangan tidak dapat dihapus karena memiliki jadwal pemakaian aktif yang telah disetujui.');
        }

        $room->delete();

        return redirect()->route('operations.facilities.rooms.index')
            ->with('status', 'Fasilitas / ruangan berhasil dihapus.');
    }

    // =========================================================================
    // RESERVATIONS & SCHEDULING
    // =========================================================================

    public function reservationsIndex(Request $request): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $roomId = $request->query('room_id');
        $status = $request->query('status');
        $date = $request->query('date');

        $query = FacilityReservation::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->with(['room', 'user', 'approver'])
            ->when($roomId, fn ($q) => $q->where('school_room_id', $roomId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($date, fn ($q) => $q->where('reservation_date', $date));

        $reservations = $query->orderBy('reservation_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(15)
            ->withQueryString();

        $rooms = SchoolRoom::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $pendingCount = FacilityReservation::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('status', 'pending')
            ->count();

        return view('operations.facilities.reservations.index', compact('reservations', 'rooms', 'roomId', 'status', 'date', 'pendingCount'));
    }

    public function reservationCreate(): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $rooms = SchoolRoom::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('operations.facilities.reservations.create', compact('rooms'));
    }

    public function reservationStore(Request $request, SchoolOperationsService $service): RedirectResponse
    {
        $school = School::current();
        abort_if(! $school, 403);

        $request->validate([
            'school_room_id' => ['required', 'exists:school_rooms,id'],
            'reservation_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'purpose' => ['required', 'string', 'max:255'],
            'expected_participants' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $reservation = $service->reserveFacility(
            $school,
            $request->user(),
            (int) $request->input('school_room_id'),
            $request->input('reservation_date'),
            $request->input('start_time'),
            $request->input('end_time'),
            $request->input('purpose'),
            (int) $request->input('expected_participants'),
            $request->input('notes')
        );

        return redirect()->route('operations.facilities.reservations.index')
            ->with('status', "Pengajuan peminjaman ruangan '{$reservation->room->name}' untuk tanggal {$reservation->reservation_date->format('d/m/Y')} berhasil diajukan dan menunggu persetujuan.");
    }

    public function reservationApprove(FacilityReservation $reservation, SchoolOperationsService $service): RedirectResponse
    {
        $school = School::current();
        abort_if($reservation->school_id !== $school?->id, 403);

        $service->approveReservation($reservation, auth()->user());

        return redirect()->back()
            ->with('status', "Reservasi ruangan '{$reservation->room->name}' telah berhasil disetujui.");
    }

    public function reservationReject(Request $request, FacilityReservation $reservation, SchoolOperationsService $service): RedirectResponse
    {
        $school = School::current();
        abort_if($reservation->school_id !== $school?->id, 403);

        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $service->rejectReservation($reservation, $request->input('rejection_reason'), $request->user());

        return redirect()->back()
            ->with('status', "Reservasi ruangan '{$reservation->room->name}' telah ditolak.");
    }

    public function reservationCancel(FacilityReservation $reservation, SchoolOperationsService $service): RedirectResponse
    {
        $school = School::current();
        abort_if($reservation->school_id !== $school?->id, 403);

        // Allow cancellation if user is the reserver or has manage permission
        $user = auth()->user();
        if ($reservation->user_id !== $user->id && ! $user->hasRole(['super_admin', 'school_admin', 'staff'])) {
            abort(403, 'Anda tidak berhak membatalkan reservasi ini.');
        }

        $service->cancelReservation($reservation, $user);

        return redirect()->back()
            ->with('status', 'Reservasi ruangan berhasil dibatalkan.');
    }

    // =========================================================================
    // INCIDENTS & MAINTENANCE
    // =========================================================================

    public function incidentsIndex(Request $request): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $incidents = FacilityIncident::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->with(['room', 'reporter'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $rooms = SchoolRoom::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('operations.facilities.incidents.index', compact('incidents', 'rooms'));
    }

    public function incidentStore(Request $request): RedirectResponse
    {
        $school = School::current();
        abort_if(! $school, 403);

        $validated = $request->validate([
            'school_room_id' => ['required', 'exists:school_rooms,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
        ]);

        FacilityIncident::create([
            'school_id' => $school->id,
            'school_room_id' => $validated['school_room_id'],
            'reported_by' => $request->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'status' => 'reported',
        ]);

        return redirect()->route('operations.facilities.incidents.index')
            ->with('status', 'Laporan kerusakan / insiden fasilitas berhasil didaftarkan.');
    }

    public function incidentUpdate(Request $request, FacilityIncident $incident): RedirectResponse
    {
        $school = School::current();
        abort_if($incident->school_id !== $school?->id, 403);

        $validated = $request->validate([
            'status' => ['required', 'in:reported,in_progress,resolved,closed'],
            'resolution_notes' => ['nullable', 'string'],
        ]);

        if ($validated['status'] === 'resolved' && ! $incident->resolved_at) {
            $validated['resolved_at'] = now();
        }

        $incident->update($validated);

        return redirect()->route('operations.facilities.incidents.index')
            ->with('status', 'Status penanganan kerusakan fasilitas berhasil diperbarui.');
    }
}
