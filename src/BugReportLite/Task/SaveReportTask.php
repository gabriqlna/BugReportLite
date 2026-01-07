<?php

namespace BugReportLite\Task;

use pocketmine\scheduler\AsyncTask;

class SaveReportTask extends AsyncTask {

    public function __construct(
        private string $filePath,
        private array $newData
    ) {}

    public function onRun(): void {
        $currentData = [];
        // Se o arquivo já existe, lê o conteúdo atual
        if (file_exists($this->filePath)) {
            $content = file_get_contents($this->filePath);
            if ($content) {
                $decoded = json_decode($content, true);
                if (is_array($decoded)) {
                    $currentData = $decoded;
                }
            }
        }

        // Adiciona novo report usando o ID como chave
        $currentData[$this->newData['id']] = $this->newData;

        // Salva de volta
        file_put_contents($this->filePath, json_encode($currentData, JSON_PRETTY_PRINT));
    }
}
