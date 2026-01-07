<?php

namespace BugReportLite;

use BugReportLite\Command\BugCommand;
use BugReportLite\Command\BugListCommand;
use BugReportLite\Command\BugTpCommand;
use BugReportLite\Manager\ReportManager;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase {

    private static self $instance;
    private ReportManager $reportManager;

    protected function onEnable(): void {
        self::$instance = $this;
        $this->saveDefaultConfig();

        $this->reportManager = new ReportManager($this);

        $this->getServer()->getCommandMap()->registerAll("bugreport", [
            new BugCommand($this),
            new BugListCommand($this),
            new BugTpCommand($this)
        ]);
        
        $this->getLogger()->info("BugReportLite ativado com sucesso.");
    }

    public static function getInstance(): self {
        return self::$instance;
    }

    public function getReportManager(): ReportManager {
        return $this->reportManager;
    }
}
