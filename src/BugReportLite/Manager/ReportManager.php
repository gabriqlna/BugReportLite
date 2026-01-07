<?php

namespace BugReportLite\Manager;

use BugReportLite\Main;
use BugReportLite\Task\DiscordWebhookTask;
use BugReportLite\Task\SaveReportTask;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;

class ReportManager {

    private Main $plugin;
    private array $cooldowns = [];
    private array $dailyCounts = [];
    private array $cache = []; // Cache simples dos reports do dia atual para o comando /buglist

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        // Cria pasta de reports se não existir
        if(!is_dir($this->plugin->getDataFolder() . "reports")) {
            mkdir($this->plugin->getDataFolder() . "reports");
        }
        $this->loadTodayCache();
    }

    public function canReport(Player $player): bool {
        $name = $player->getName();
        $limit = $this->plugin->getConfig()->getNested("settings.daily-limit", 5);
        
        // Verifica Limite Diário
        if (($this->dailyCounts[$name] ?? 0) >= $limit) {
            $player->sendMessage($this->getMsg("daily-limit"));
            return false;
        }

        // Verifica Cooldown
        if (isset($this->cooldowns[$name])) {
            $remaining = $this->cooldowns[$name] - time();
            if ($remaining > 0) {
                $player->sendMessage(str_replace("{TIME}", (string)$remaining, $this->getMsg("cooldown")));
                return false;
            }
        }
        return true;
    }

            public function submitReport(\pocketmine\player\Player $player, string $type, string $description): void {
        $name = $player->getName();
        $date = date("Y-m-d");
        
        // CORREÇÃO: Definindo o ID logo no começo
        $id = substr(uniqid(), -7); 
        
        $data = [
            "id" => $id,
            "player" => $name,
            "uuid" => $player->getUniqueId()->toString(),
            "type" => $type,
            "description" => $description,
            "world" => $player->getWorld()->getFolderName(),
            "x" => (int)floor($player->getPosition()->getX()),
            "y" => (int)floor($player->getPosition()->getY()),
            "z" => (int)floor($player->getPosition()->getZ()),
            "timestamp" => date("H:i:s")
        ];

        // 1. Atualiza cache local e contadores
        $this->cache[$id] = $data;
        $this->dailyCounts[$name] = ($this->dailyCounts[$name] ?? 0) + 1;
        $this->cooldowns[$name] = time() + $this->plugin->getConfig()->getNested("settings.cooldown", 300);

        // Serializamos os dados para evitar o erro de Thread-Safe
        $serialized = serialize($data);

        // 2. Salva Assincronamente (JSON)
        $filePath = $this->plugin->getDataFolder() . "reports/" . $date . ".json";
        $this->plugin->getServer()->getAsyncPool()->submitTask(new \BugReportLite\Task\SaveReportTask($filePath, $serialized));

        // 3. Envia Webhook (Opcional)
        if ($this->plugin->getConfig()->getNested("discord.enabled")) {
            $url = $this->plugin->getConfig()->getNested("discord.webhook-url");
            if(!empty($url) && $url !== "https://discord.com/api/webhooks/..."){
                $this->plugin->getServer()->getAsyncPool()->submitTask(new \BugReportLite\Task\DiscordWebhookTask($url, $serialized));
            }
        }

        $player->sendMessage($this->getMsg("success"));
    }



    public function getTodayReports(): array {
        return $this->cache;
    }

    public function getReport(string $id): ?array {
        // Procura no cache de hoje
        if (isset($this->cache[$id])) return $this->cache[$id];
        
        // Implementação futura: buscar em arquivos antigos se necessário
        return null;
    }

    private function loadTodayCache(): void {
        $date = date("Y-m-d");
        $path = $this->plugin->getDataFolder() . "reports/" . $date . ".json";
        if (file_exists($path)) {
            $content = file_get_contents($path);
            $json = json_decode($content, true);
            if (is_array($json)) {
                $this->cache = $json;
            }
        }
    }

    public function getMsg(string $key): string {
        $prefix = $this->plugin->getConfig()->getNested("messages.prefix");
        return $prefix . $this->plugin->getConfig()->getNested("messages." . $key);
    }
}

