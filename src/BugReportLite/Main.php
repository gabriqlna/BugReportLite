<?php

declare(strict_types=1);

namespace BugReportLite;

use pocketmine\plugin\PluginBase;
use BugReportLite\Command\BugCommand;
use pocketmine\scheduler\AsyncTask;
use pocketmine\player\Player;
use pocketmine\Server;

class Main extends PluginBase {

    // Coloque a URL do seu Webhook aqui (Entre aspas)
    private string $webhookUrl = "SUA_URL_DO_DISCORD_AQUI";

    protected function onEnable(): void {
        $this->saveDefaultConfig();
        
        // Se tiver URL na config, usa ela, senão usa a hardcoded
        if($this->getConfig()->get("webhook-url")) {
            $this->webhookUrl = $this->getConfig()->get("webhook-url");
        }

        $this->getServer()->getCommandMap()->register("bug", new BugCommand($this));
        $this->getLogger()->info("BugReportLite ativado!");
    }

    public function sendToDiscord(Player $player, string $report): void {
        if (empty($this->webhookUrl) || str_contains($this->webhookUrl, "SUA_URL")) {
            $this->getLogger()->warning("Webhook do Discord não configurado!");
            return;
        }

        // Cria a tarefa assíncrona para enviar os dados
        $task = new class($this->webhookUrl, $player->getName(), $report) extends AsyncTask {
            private string $url;
            private string $player;
            private string $msg;

            public function __construct(string $url, string $player, string $msg) {
                $this->url = $url;
                $this->player = $player;
                $this->msg = $msg;
            }

            public function onRun(): void {
                $data = [
                    "content" => "",
                    "embeds" => [
                        [
                            "title" => "🐛 Novo Bug Reportado",
                            "color" => 16711680, // Vermelho
                            "fields" => [
                                ["name" => "Jogador", "value" => $this->player, "inline" => true],
                                ["name" => "Reporte", "value" => $this->msg]
                            ],
                            "footer" => ["text" => "BugReportLite"]
                        ]
                    ]
                ];

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $this->url);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_exec($curl);
                curl_close($curl);
            }
        };

        $this->getServer()->getAsyncPool()->submitTask($task);
    }
}
