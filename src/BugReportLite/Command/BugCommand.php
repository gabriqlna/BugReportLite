<?php

namespace BugReportLite\Command;

use BugReportLite\Main;
use BugReportLite\Utils\SimpleFormTrait;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class BugCommand extends Command {
    use SimpleFormTrait;

    private Main $plugin;
    private array $bugTypes = ["Terreno / Mapa", "Mobs / Entidades", "Item / Inventário", "Plugin / Sistema", "Outro"];

    public function __construct(Main $plugin) {
        parent::__construct("bug", "Reportar um erro", "/bug");
        $this->setPermission("bugreport.use");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) return false;
        if (!$this->testPermission($sender)) return false;

        if (!$this->plugin->getReportManager()->canReport($sender)) return false;

        $this->sendTypeForm($sender);
        return true;
    }

    // Passo 1: Selecionar Tipo
    private function sendTypeForm(Player $player): void {
        $form = $this->createSimpleForm(function (Player $player, $data) {
            if ($data === null) return;
            $type = $this->bugTypes[$data] ?? "Outro";
            $this->sendDescriptionForm($player, $type);
        });

        $form->setTitle("Reportar Bug - Passo 1/3");
        $form->setContent("Selecione a categoria que melhor descreve o problema:");
        foreach ($this->bugTypes as $type) {
            $form->addButton($type);
        }
        $player->sendForm($form);
    }

    // Passo 2: Descrição
    private function sendDescriptionForm(Player $player, string $type): void {
        $form = $this->createCustomForm(function (Player $player, $data) use ($type) {
            if ($data === null) return;
            $desc = trim($data[1] ?? ""); // Index 0 é label, 1 é input

            $min = $this->plugin->getConfig()->getNested("settings.desc-min-length", 10);
            if (strlen($desc) < $min) {
                $player->sendMessage($this->plugin->getReportManager()->getMsg("desc-too-short"));
                return;
            }

            $this->sendConfirmForm($player, $type, $desc);
        });

        $form->setTitle("Reportar Bug - Passo 2/3");
        $form->addLabel("Categoria: §e$type");
        $form->addInput("Descreva o bug (máx 200 carac):", "Ex: Ao quebrar bloco X, acontece Y...");
        $player->sendForm($form);
    }

    // Passo 3: Confirmação
    private function sendConfirmForm(Player $player, string $type, string $desc): void {
        $pos = $player->getPosition();
        $coords = sprintf("%d, %d, %d (%s)", $pos->getX(), $pos->getY(), $pos->getZ(), $pos->getWorld()->getFolderName());

        $content = "§lRESUMO DO REPORT:§r\n\n";
        $content .= "§eTipo:§f $type\n";
        $content .= "§eDescrição:§f $desc\n";
        $content .= "§eLocal:§f $coords\n\n";
        $content .= "Deseja confirmar o envio?";

        $form = $this->createModalForm(function (Player $player, $data) use ($type, $desc) {
            if ($data === true) {
                $this->plugin->getReportManager()->submitReport($player, $type, $desc);
            } else {
                $player->sendMessage($this->plugin->getReportManager()->getMsg("cancelled"));
            }
        });

        $form->setTitle("Confirmação - Passo 3/3");
        $form->setContent($content);
        $form->setButton1("Confirmar Envio");
        $form->setButton2("Cancelar");
        $player->sendForm($form);
    }
}
