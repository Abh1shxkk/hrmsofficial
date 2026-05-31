<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $canManageHolidays = auth()->user()->hasAnyRole(['super_admin', 'hr_admin']);
        $query = Holiday::query();
        $calendarMonth = (int) $request->get('month', now()->month);
        $calendarYear = (int) $request->get('year', now()->year);

        if ($year = $request->get('year')) {
            $query->whereYear('date', $year);
        }
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $holidays = $query->orderBy('date')->get();
        $calendarHolidays = Holiday::whereYear('date', $calendarYear)
            ->whereMonth('date', $calendarMonth)
            ->orderBy('date')
            ->get()
            ->groupBy(fn ($holiday) => $holiday->date->toDateString());
        $calendarWeeks = $this->calendarWeeks($calendarMonth, $calendarYear, $calendarHolidays);
        $summary = Holiday::whereYear('date', $calendarYear)
            ->get()
            ->groupBy('type')
            ->map->count();
        $editHoliday = $canManageHolidays && $request->filled('edit')
            ? Holiday::find($request->get('edit'))
            : null;

        return view('holidays.index', compact(
            'holidays',
            'calendarMonth',
            'calendarYear',
            'calendarWeeks',
            'calendarHolidays',
            'summary',
            'editHoliday',
            'canManageHolidays'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'date' => 'required|date',
            'type' => 'required|in:national,regional,company',
        ]);

        if (Holiday::whereDate('date', $request->date)->exists()) {
            return back()
                ->withErrors(['date' => 'A holiday already exists for this date.'])
                ->withInput();
        }

        Holiday::create($request->only('name', 'date', 'type'));

        return back()->with('success', 'Holiday added.');
    }

    public function update(Request $request, Holiday $holiday)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:national,regional,company',
        ]);

        $holiday->update($request->only('name', 'type'));

        return redirect()
            ->route('holidays.index', ['month' => $holiday->date->month, 'year' => $holiday->date->year])
            ->with('success', 'Holiday updated.');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();
        return back()->with('success', 'Holiday removed.');
    }

    private function calendarWeeks(int $month, int $year, $holidays): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $end = Carbon::create($year, $month, 1)->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $weeks = [];
        $weekIndex = 0;

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $weeks[$weekIndex][] = [
                'date' => $date->copy(),
                'in_month' => $date->month === $month,
                'is_today' => $date->isToday(),
                'holidays' => $holidays->get($date->toDateString(), collect()),
            ];

            // Start new week after Sunday
            if ($date->isSunday()) {
                $weekIndex++;
            }
        }

        return array_values($weeks);
    }
}
