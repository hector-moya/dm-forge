<?php

namespace App\Ai\Tools;

use App\Models\GameSession;
use App\Models\SessionLog;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetSessionLogs implements Tool
{
    public function __construct(
        protected GameSession $session,
    ) {}

    public function description(): Stringable|string
    {
        return 'Retrieve session log entries for the current game session. Can filter by log type (narrative, decision, combat, note).';
    }

    public function handle(Request $request): Stringable|string
    {
        $query = $this->session->sessionLogs()->orderBy('logged_at');

        $type = (string) $request->string('type');
        if ($type !== '') {
            $query->where('type', $type);
        }

        $limit = $request->integer('limit', 50);
        /** @var \Illuminate\Database\Eloquent\Collection<int, SessionLog> $logs */
        $logs = $query->limit($limit)->get();

        if ($logs->isEmpty()) {
            return 'No session log entries found.';
        }

        return $logs->map(function (SessionLog $log) {
            $time = $log->logged_at?->format('H:i:s') ?? 'N/A';
            $type = strtoupper($log->type);

            return "[{$time}] [{$type}] {$log->entry}";
        })->implode("\n");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => $schema->string()->enum(['narrative', 'decision', 'combat', 'note'])->description('Filter logs by type'),
            'limit' => $schema->integer()->min(1)->max(100)->description('Maximum number of log entries to return (default 50)'),
        ];
    }
}
