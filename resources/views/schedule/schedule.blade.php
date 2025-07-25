@extends('layouts.app')

@section('title', 'Schedules')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Schedules</h1>
        <a href="{{ route('schedule.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add New Schedule
        </a>
    </div>

    <!-- Schedules Table -->
    <div class="card">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Contact Number</th>
                            <th>Time</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Location</th>
                            <th class="text-center" style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                        <tr class="{{ $rowClasses[$schedule->id] ?? '' }}">
                            <td class="align-middle">{{ $schedule->name }}</td>
                            <td class="align-middle">{{ $schedule->contact_num }}</td>
                            <td class="align-middle">
                                {{ \Carbon\Carbon::parse($schedule->appointment_time)->format('h:i A') }}
                            </td>
                            <td class="align-middle">{{ $schedule->appointment_date}}</td>
                            <td class="align-middle">{{ $schedule->description ?? '-'}}</td>
                            <td class="align-middle">{{ $schedule->location ?? '-' }}</td>
                            <td class="align-middle text-center">
                                <div class="btn-group" role="group">
                                    <form action="{{ route('schedule.destroy', $schedule->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this schedule?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-calendar2-x fs-1 text-muted"></i>
                                <p class="mt-3 mb-2 h5">No schedules found.</p>
                                <p class="text-muted">Add a new schedule to get started.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Showing {{ $schedules->count() }} schedule(s)
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 