    public function submitReport(Player $player, string $type, string $description): void {
        // ... (código anterior de criação do array $data permanece igual) ...
        
        $data = [
            "id" => $id,
            "player" => $name,
            "uuid" => $player->getUniqueId()->toString(),
            "type" => $type,
            "description" => $description,
            "world" => $player->getWorld()->getFolderName(),
            "x" => floor($player->getPosition()->getX()),
            "y" => floor($player->getPosition()->getY()),
            "z" => floor($player->getPosition()->getZ()),
            "timestamp" => date("H:i:s")
        ];

        // 1. Atualiza cache local e contadores
        $this->cache[$id] = $data;
        $this->dailyCounts[$name] = ($this->dailyCounts[$name] ?? 0) + 1;
        $this->cooldowns[$name] = time() + $this->plugin->getConfig()->getNested("settings.cooldown", 300);

        // AQUI ESTÁ A CORREÇÃO: Usamos serialize($data)
        
        // 2. Salva Assincronamente (JSON)
        $filePath = $this->plugin->getDataFolder() . "reports/" . $date . ".json";
        $this->plugin->getServer()->getAsyncPool()->submitTask(new SaveReportTask($filePath, serialize($data)));

        // 3. Envia Webhook (Opcional)
        if ($this->plugin->getConfig()->getNested("discord.enabled")) {
            $url = $this->plugin->getConfig()->getNested("discord.webhook-url");
            // AQUI TAMBÉM: serialize($data)
            $this->plugin->getServer()->getAsyncPool()->submitTask(new DiscordWebhookTask($url, serialize($data)));
        }

        $player->sendMessage($this->getMsg("success"));
    }

