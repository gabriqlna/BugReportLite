<?php

namespace BugReportLite\Command;

use BugReportLite\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class BugListCommand extends Command {

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("buglist", "Listar bugs de hoje", "/buglist");
        $this->setPermission("bugreport.staff");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$this->testPermission($sender)) return false;

        $reports = $this->plugin->getReportManager()->getTodayReports();
        $count = count($reports);
        
        $msg = str_replace("{COUNT}", (string)$count, $this->plugin->getReportManager()->getMsg("staff-list-header"));
        $sender->sendMessage($msg);

        if ($count === 0) {
            $sender->sendMessage("§7Nenhum report hoje.");
            return true;
        }

        // Mostra os últimos 10
        $recent = array_slice($reports, -10);
        foreach ($recent as $report) {
            $line = $this->plugin->getReportManager()->getMsg("staff-list-format");
            $line = str_replace(
                ["{ID}", "{PLAYER}", "{TYPE}"],
                [$report['id'], $report['player'], $report['type']],
                $line
            );
            $sender->sendMessage($line);
        }
        $sender->sendMessage("§7Use /bugtp <id> para investigar.");

        return true;
    }
}
