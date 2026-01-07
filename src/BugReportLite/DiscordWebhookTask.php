<?php

namespace BugReportLite\Task;

use pocketmine\scheduler\AsyncTask;

class DiscordWebhookTask extends AsyncTask {

    public function __construct(
        private string $url,
        private array $reportData
    ) {}

    public function onRun(): void {
        $embed = [
            "title" => "Novo Bug Reportado",
            "color" => 16711680, // Vermelho
            "fields" => [
                ["name" => "Jogador", "value" => $this->reportData['player'], "inline" => true],
                ["name" => "Tipo", "value" => $this->reportData['type'], "inline" => true],
                ["name" => "Mundo", "value" => $this->reportData['world'], "inline" => true],
                ["name" => "Coordenadas", "value" => "{$this->reportData['x']}, {$this->reportData['y']}, {$this->reportData['z']}", "inline" => true],
                ["name" => "Descrição", "value" => $this->reportData['description']]
            ],
            "footer" => ["text" => "BugReportLite • ID: " . $this->reportData['id']]
        ];

        $payload = json_encode([
            "username" => "BugReport Bot",
            "embeds" => [$embed]
        ]);

        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => 5
            ]
        ];

        $context = stream_context_create($opts);
        @file_get_contents($this->url, false, $context);
    }
}
