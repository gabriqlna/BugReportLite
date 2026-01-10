<?php

declare(strict_types=1);

namespace BugReportLite\Command;

use BugReportLite\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use BugReportLite\Forms\SimpleForm;
use BugReportLite\Forms\CustomForm;
use BugReportLite\Forms\ModalForm;

class BugCommand extends Command {

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("bug", "Reportar um erro", "/bug", ["report"]);
        $this->setPermission("bugreportlite.use");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§cUse este comando in-game.");
            return false;
        }

        $this->openMainMenu($sender);
        return true;
    }

    // Erros das linhas 41-44 corrigidos (SimpleForm)
    public function openMainMenu(Player $player): void {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null) return;

            if ($data === 0) {
                $this->openReportForm($player);
            }
        });

        $form->setTitle("§l§cBug Report"); // Linha 41
        $form->setContent("§7Encontrou um problema? Ajude-nos a corrigir!\n\nSelecione uma opção abaixo:"); // Linha 42
        $form->addButton("§cReportar Erro\n§8Clique para escrever", 0, "textures/ui/feedback"); // Linha 44
        $form->addButton("§cFechar");

        $player->sendForm($form);
    }

    // Erros das linhas 64-66 corrigidos (CustomForm)
    public function openReportForm(Player $player): void {
        $form = new CustomForm(function (Player $player, ?array $data) {
            if ($data === null) return;

            $bugDescription = $data[1]; // O índice 0 é o Label, 1 é o Input
            
            if (empty(trim($bugDescription))) {
                $player->sendMessage("§cVocê precisa escrever algo.");
                return;
            }

            // Confirmação antes de enviar
            $this->openConfirmForm($player, $bugDescription);
        });

        $form->setTitle("§l§cDescrever Erro"); // Linha 64
        $form->addLabel("§7Por favor, descreva o bug com detalhes (onde aconteceu, o que você fez):"); // Linha 65
        $form->addInput("Descrição:", "Ex: O bloco de pedra sumiu..."); // Linha 66

        $player->sendForm($form);
    }

    // Erros das linhas 89-92 corrigidos (ModalForm)
    public function openConfirmForm(Player $player, string $report): void {
        $form = new ModalForm(function (Player $player, ?bool $data) use ($report) {
            if ($data === null) return;

            if ($data === true) {
                // Envia para o Webhook (Lógica no Main)
                $this->plugin->sendToDiscord($player, $report);
                $player->sendMessage("§aSeu reporte foi enviado com sucesso! Obrigado.");
            } else {
                $player->sendMessage("§cEnvio cancelado.");
            }
        });

        $form->setTitle("§l§eConfirmar Envio"); // Linha 89
        $form->setContent("§7Você deseja enviar este reporte?\n\n§f" . $report); // Linha 90
        $form->setButton1("§aSim, Enviar"); // Linha 91
        $form->setButton2("§cCancelar"); // Linha 92

        $player->sendForm($form);
    }
}

