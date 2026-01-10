<?php

declare(strict_types=1);

namespace BugReportLite\Forms;

use pocketmine\form\Form;
use pocketmine\player\Player;

class CustomForm implements Form {
    private array $data = ["type" => "custom_form", "title" => "", "content" => []];
    private $callable;

    public function __construct(?callable $callable) {
        $this->callable = $callable;
    }

    public function setTitle(string $title): void { $this->data["title"] = $title; }
    public function addLabel(string $text): void { $this->data["content"][] = ["type" => "label", "text" => $text]; }
    public function addInput(string $text, string $placeholder = "", string $default = ""): void {
        $this->data["content"][] = ["type" => "input", "text" => $text, "placeholder" => $placeholder, "default" => $default];
    }

    public function handleResponse(Player $player, $data): void {
        $callable = $this->callable;
        if ($callable !== null) $callable($player, $data);
    }

    public function jsonSerialize(): array { return $this->data; }
}
