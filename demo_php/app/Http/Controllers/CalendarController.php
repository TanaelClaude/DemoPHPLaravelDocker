<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WeekValidation;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'week' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $weekStart = isset($validated['week'])
            ? CarbonImmutable::createFromFormat('!Y-m-d', $validated['week'])->startOfWeek(CarbonImmutable::MONDAY)
            : CarbonImmutable::now()->startOfWeek(CarbonImmutable::MONDAY);
        $days = collect(range(0, 4))->map(fn (int $offset) => $weekStart->addDays($offset));
        $weekEnd = $weekStart->addDays(4);

        $schedules = DB::table('work_schedules')
            ->whereDate('work_date', '>=', $weekStart->toDateString())
            ->whereDate('work_date', '<=', $weekEnd->toDateString())
            ->get()
            ->groupBy('user_id')
            ->map(fn ($rows) => $rows->keyBy(fn ($row) => CarbonImmutable::parse($row->work_date)->toDateString()));

        $validatedUserIds = WeekValidation::query()
            ->whereDate('week_start', $weekStart->toDateString())
            ->pluck('user_id')
            ->all();

        return view('calendar.index', [
            'days' => $days,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'previousWeek' => $weekStart->subWeek(),
            'nextWeek' => $weekStart->addWeek(),
            'users' => User::query()->orderBy('name')->get(),
            'schedules' => $schedules,
            'validatedUserIds' => $validatedUserIds,
            'currentUserId' => $request->user()->id,
        ]);
    }

    public function validateWeek(Request $request, string $weekStart): RedirectResponse
    {
        $weekStart = Validator::make(
            ['week_start' => $weekStart],
            ['week_start' => ['required', 'date_format:Y-m-d']],
        )->validate()['week_start'];
        $monday = CarbonImmutable::createFromFormat('!Y-m-d', $weekStart);

        if (! $monday || $monday->dayOfWeekIso !== 1) {
            throw ValidationException::withMessages([
                'schedule' => 'La semaine doit commencer un lundi.',
            ]);
        }

        $dates = collect(range(0, 4))
            ->map(fn (int $offset) => $monday->addDays($offset)->toDateString());
        $allowedKeys = $dates->all();
        $rules = ['schedule' => ['required', 'array']];

        foreach ($allowedKeys as $date) {
            $rules["schedule.{$date}"] = ['required', 'array:start,end'];
            $rules["schedule.{$date}.start"] = ['nullable', 'date_format:H:i'];
            $rules["schedule.{$date}.end"] = ['nullable', 'date_format:H:i'];
        }

        Validator::make($request->all(), $rules)->validate();
        $scheduleData = $request->input('schedule');
        $submittedDates = array_keys($scheduleData);

        if (array_diff($submittedDates, $allowedKeys) !== []) {
            throw ValidationException::withMessages([
                'schedule' => 'Seuls les jours de cette semaine peuvent être modifiés.',
            ]);
        }

        foreach ($allowedKeys as $date) {
            $start = $scheduleData[$date]['start'] ?? null;
            $end = $scheduleData[$date]['end'] ?? null;

            if (($start === null) !== ($end === null)) {
                throw ValidationException::withMessages([
                    "schedule.{$date}.end" => 'Indiquez les deux horaires ou laissez la journée vide.',
                ]);
            }

            if ($start !== null && $end <= $start) {
                throw ValidationException::withMessages([
                    "schedule.{$date}.end" => 'L’heure de fin doit être après l’heure de début.',
                ]);
            }
        }

        DB::transaction(function () use ($allowedKeys, $scheduleData, $monday, $request): void {
            $user = $request->user();

            foreach ($allowedKeys as $date) {
                $day = $scheduleData[$date];
                $start = $day['start'] ?? null;
                $end = $day['end'] ?? null;

                $existingSchedule = DB::table('work_schedules')
                    ->where('user_id', $user->id)
                    ->whereDate('work_date', $date)
                    ->first();

                if ($start === null || $end === null) {
                    if ($existingSchedule) {
                        DB::table('work_schedules')->where('id', $existingSchedule->id)->delete();
                    }

                    continue;
                }

                $timestamps = ['updated_at' => now()];
                $values = [
                    'work_date' => $date,
                    'starts_at' => $start,
                    'ends_at' => $end,
                    ...$timestamps,
                ];

                if ($existingSchedule) {
                    DB::table('work_schedules')->where('id', $existingSchedule->id)->update($values);
                } else {
                    DB::table('work_schedules')->insert([
                        'user_id' => $user->id,
                        ...$values,
                        'created_at' => now(),
                    ]);
                }
            }

            $existingValidation = DB::table('week_validations')
                ->where('user_id', $user->id)
                ->whereDate('week_start', $monday->toDateString())
                ->first();

            $validationValues = [
                'week_start' => $monday->toDateString(),
                'validated_at' => now(),
                'updated_at' => now(),
            ];

            if ($existingValidation) {
                DB::table('week_validations')
                    ->where('id', $existingValidation->id)
                    ->update($validationValues);
            } else {
                DB::table('week_validations')->insert([
                    'user_id' => $user->id,
                    ...$validationValues,
                    'created_at' => now(),
                ]);
            }
        });

        return redirect()
            ->route('calendar.index', ['week' => $monday->toDateString()])
            ->with('status', 'Votre semaine a été validée.');
    }
}
