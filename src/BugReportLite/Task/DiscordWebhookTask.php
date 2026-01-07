<?php

namespace BugReportLite\Task;

use pocketmine\scheduler\AsyncTask;

class DiscordWebhookTask extends AsyncTask {

    public function __construct(
        private string $url,
        private string $serializedData // Mudou de array para string
    ) {}

    public function onRun(): void {
        // Converte a string de volta para array
        $reportData = unserialize($this->serializedData);

        $embed = [
            "title" => "Novo Bug Reportado",
            "color" => 16711680, // Vermelho
            "fields" => [
                ["name" => "Jogador", "value" => $reportData['player'], "inline" => true],
                ["name" => "Tipo", "value" => $reportData['type'], "inline" => true],
                ["name" => "Mundo", "value" => $reportData['world'], "inline" => true],
                ["name" => "Coordenadas", "value" => "{$reportData['x']}, {$reportData['y']}, {$reportData['z']}", "inline" => true],
                ["name" => "Descrição", "value" => $reportData['description']]
            ],
            "footer" => ["text" => "BugReportLite • ID: " . $reportData['id']]
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

