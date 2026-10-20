<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Calendrier de la semaine · Demo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="calendar-page">
    <header class="welcome-nav">
        <a class="brand" href="{{ route('home') }}">Demo<span>.</span></a>
        <nav class="calendar-nav-actions" aria-label="Navigation principale">
            <a class="nav-link" href="{{ route('home') }}">Mon espace</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="logout-button" type="submit">Se déconnecter</button>
            </form>
        </nav>
    </header>

    <main class="calendar-shell">
        <div class="calendar-heading">
            <div>
                <p class="eyebrow">PLANNING D’ÉQUIPE</p>
                <h1>Calendrier hebdomadaire</h1>
                <p class="calendar-description">Consultez les horaires de l’équipe et renseignez uniquement les vôtres.</p>
            </div>
            <div class="week-navigation" aria-label="Choisir une semaine">
                <a class="week-arrow" href="{{ route('calendar.index', ['week' => $previousWeek->toDateString()]) }}" aria-label="Semaine précédente">←</a>
                <div class="week-range">
                    <strong>{{ $weekStart->locale('fr')->isoFormat('D MMM') }} – {{ $weekEnd->locale('fr')->isoFormat('D MMM YYYY') }}</strong>
                    <span>{{ $weekStart->toDateString() === now()->startOfWeek(\Carbon\CarbonImmutable::MONDAY)->toDateString() ? 'Semaine en cours' : 'Semaine du lundi' }}</span>
                </div>
                <a class="week-arrow" href="{{ route('calendar.index', ['week' => $nextWeek->toDateString()]) }}" aria-label="Semaine suivante">→</a>
            </div>
        </div>

        @if (session('status'))
            <div class="calendar-success" role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="calendar-error" role="alert">{{ $errors->first() }}</div>
        @endif

        <section class="calendar-card" aria-label="Horaires de tous les membres">
            <form method="POST" action="{{ route('calendar.validate', ['weekStart' => $weekStart->toDateString()]) }}">
                @csrf
                <div class="calendar-scroll">
                    <table class="week-grid">
                        <thead>
                            <tr>
                                <th scope="col" class="member-column">Membre</th>
                                @foreach ($days as $day)
                                    <th scope="col" class="day-column {{ $day->isToday() ? 'today-column' : '' }}">
                                        <span>{{ $day->locale('fr')->isoFormat('dddd') }}</span>
                                        <strong>{{ $day->format('d/m') }}</strong>
                                        @if ($day->isToday()) <small>Aujourd’hui</small> @endif
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                @php
                                    $isCurrentUser = $user->id === $currentUserId;
                                    $userSchedules = $schedules->get($user->id, collect());
                                    $isWeekValidated = in_array($user->id, $validatedUserIds, true);
                                @endphp
                                <tr class="{{ $isCurrentUser ? 'own-calendar-row' : '' }}">
                                    <th scope="row" class="member-cell">
                                        <span class="member-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                                        <span class="member-info">
                                            <strong>{{ $isCurrentUser ? 'Vous' : $user->name }}</strong>
                                            @if ($isCurrentUser)
                                                <small>Votre calendrier</small>
                                            @elseif ($isWeekValidated)
                                                <small class="validated-label">Semaine validée</small>
                                            @else
                                                <small>Calendrier public</small>
                                            @endif
                                        </span>
                                    </th>
                                    @foreach ($days as $day)
                                        @php
                                            $date = $day->toDateString();
                                            $entry = $userSchedules->get($date);
                                        @endphp
                                        <td class="day-cell {{ $day->isToday() ? 'today-column' : '' }}">
                                            @if ($isCurrentUser)
                                                <div class="time-inputs">
                                                    <label class="visually-hidden" for="start-{{ $date }}">Début {{ $day->locale('fr')->isoFormat('dddd D MMMM') }}</label>
                                                    <input id="start-{{ $date }}" type="time" name="schedule[{{ $date }}][start]" value="{{ old("schedule.{$date}.start", $entry ? substr($entry->starts_at, 0, 5) : '') }}" aria-label="Début de journée {{ $day->locale('fr')->isoFormat('dddd D MMMM') }}">
                                                    <span aria-hidden="true">–</span>
                                                    <label class="visually-hidden" for="end-{{ $date }}">Fin {{ $day->locale('fr')->isoFormat('dddd D MMMM') }}</label>
                                                    <input id="end-{{ $date }}" type="time" name="schedule[{{ $date }}][end]" value="{{ old("schedule.{$date}.end", $entry ? substr($entry->ends_at, 0, 5) : '') }}" aria-label="Fin de journée {{ $day->locale('fr')->isoFormat('dddd D MMMM') }}">
                                                </div>
                                            @elseif ($entry)
                                                <span class="schedule-time">{{ substr($entry->starts_at, 0, 5) }} - {{ substr($entry->ends_at, 0, 5) }}</span>
                                            @else
                                                <span class="day-off">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="calendar-footer">
                    <p><strong>À vous :</strong> indiquez une heure de début et de fin. Laissez les deux champs vides pour un jour non travaillé.</p>
                    <div class="validation-action">
                        @if (in_array($currentUserId, $validatedUserIds, true))
                            <span class="validated-pill"><span aria-hidden="true">✓</span> Semaine validée</span>
                        @endif
                        <button class="validate-week-button" type="submit">Valider la semaine <span aria-hidden="true">→</span></button>
                    </div>
                </div>
            </form>
        </section>
        <p class="calendar-privacy">Les horaires des autres membres sont consultables en lecture seule. Seul votre calendrier peut être modifié par ce formulaire.</p>
    </main>
</body>
</html>
