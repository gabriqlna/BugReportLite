<?php

namespace BugReportLite\Command;

use BugReportLite\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\world\Position;

class BugTpCommand extends Command {

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("bugtp", "Teleportar para um report", "/bugtp <id>");
        $this->setPermission("bugreport.staff");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) return false;
        if (!$this->testPermission($sender)) return false;

        if (!isset($args[0])) {
            $sender->sendMessage("§cUso: /bugtp <id>");
            return false;
        }

        $id = $args[0];
        $report = $this->plugin->getReportManager()->getReport($id);

        if ($report === null) {
            $sender->sendMessage($this->plugin->getReportManager()->getMsg("not-found"));
            return false;
        }

        $worldName = $report['world'];
        $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($worldName);

        // Tenta carregar se não estiver carregado
        if ($world === null) {
            $this->plugin->getServer()->getWorldManager()->loadWorld($worldName);
            $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($worldName);
        }

        if ($world === null) {
            $sender->sendMessage("§cMundo '$worldName' não existe ou não pôde ser carregado.");
            return false;
        }

        $pos = new Position($report['x'], $report['y'], $report['z'], $world);
        $sender->teleport($pos);
        
        $msg = str_replace("{ID}", $id, $this->plugin->getReportManager()->getMsg("tp-success"));
        $sender->sendMessage($msg);

        return true;
    }
}
