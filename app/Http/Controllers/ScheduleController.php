<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = Schedule::orderBy('appointment_date')->orderBy('appointment_time')->get();
        $rowClasses = [];
        foreach ($schedules as $schedule) {
            $rowClasses[$schedule->id] = $this->getRowClassForSchedule($schedule);
        }
        // Sort: red (past), orange (today), green (future)
        $schedules = $schedules->sortBy(function($schedule) use ($rowClasses) {
            if ($rowClasses[$schedule->id] === 'table-danger') return 0;
            if ($rowClasses[$schedule->id] === 'table-warning') return 1;
            if ($rowClasses[$schedule->id] === 'table-success') return 2;
            return 3;
        })->values();
        return view('schedule.schedule', compact('schedules', 'rowClasses'));
    }

    private function getRowClassForSchedule($schedule)
    {
        $today = \Carbon\Carbon::today();
        $date = \Carbon\Carbon::parse($schedule->appointment_date);
        if ($date->isToday()) {
            return 'table-warning'; // orange
        } elseif ($date->isPast()) {
            return 'table-danger'; // red
        } elseif ($date->isFuture()) {
            return 'table-success'; // green
        } else {
            return '';
        }
    }

    public function create()
    {
        return view('schedule.schedule-create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_num' => 'required|string|max:255',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        // Prevent past date/time
        $appointmentDate = $request->input('appointment_date');
        $appointmentTime = $request->input('appointment_time');
        $now = now();
        $appointmentDateTime = \Carbon\Carbon::parse("$appointmentDate $appointmentTime");
        if ($appointmentDateTime->lt($now)) {
            return back()->withInput()->withErrors(['appointment_time' => 'Cannot set appointment in the past.']);
        }
        
        \App\Models\Schedule::create($validated);
        return redirect()->route('schedule.index')->with('success', 'Schedule created successfully.');
    }

    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();
        return redirect()->route('schedule.index')->with('success', 'Schedule deleted successfully.');
    }
} 