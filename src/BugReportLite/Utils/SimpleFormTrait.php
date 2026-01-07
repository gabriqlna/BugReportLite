<?php

namespace BugReportLite\Utils;

use pocketmine\form\Form;
use pocketmine\player\Player;

trait SimpleFormTrait {

    // Cria um Simple Form (Botões)
    public function createSimpleForm(callable $handler): Form {
        return new class($handler) implements Form {
            private $data = ["type" => "form", "title" => "", "content" => "", "buttons" => []];
            private $handler;

            public function __construct(callable $handler) { $this->handler = $handler; }
            public function setTitle(string $title): self { $this->data["title"] = $title; return $this; }
            public function setContent(string $content): self { $this->data["content"] = $content; return $this; }
            public function addButton(string $text, int $imageType = -1, string $imagePath = ""): self {
                $btn = ["text" => $text];
                if($imageType !== -1) $btn["image"] = ["type" => $imageType === 0 ? "path" : "url", "data" => $imagePath];
                $this->data["buttons"][] = $btn;
                return $this;
            }
            public function jsonSerialize(): mixed { return $this->data; }
            public function handleResponse(Player $player, mixed $data): void { ($this->handler)($player, $data); }
        };
    }

    // Cria um Custom Form (Inputs)
    public function createCustomForm(callable $handler): Form {
        return new class($handler) implements Form {
            private $data = ["type" => "custom_form", "title" => "", "content" => []];
            private $handler;

            public function __construct(callable $handler) { $this->handler = $handler; }
            public function setTitle(string $title): self { $this->data["title"] = $title; return $this; }
            public function addInput(string $text, string $placeholder = "", string $default = ""): self {
                $this->data["content"][] = ["type" => "input", "text" => $text, "placeholder" => $placeholder, "default" => $default];
                return $this;
            }
            public function addLabel(string $text): self {
                $this->data["content"][] = ["type" => "label", "text" => $text];
                return $this;
            }
            public function jsonSerialize(): mixed { return $this->data; }
            public function handleResponse(Player $player, mixed $data): void { ($this->handler)($player, $data); }
        };
    }

    // Cria um Modal Form (Sim/Não)
    public function createModalForm(callable $handler): Form {
        return new class($handler) implements Form {
            private $data = ["type" => "modal", "title" => "", "content" => "", "button1" => "", "button2" => ""];
            private $handler;

            public function __construct(callable $handler) { $this->handler = $handler; }
            public function setTitle(string $title): self { $this->data["title"] = $title; return $this; }
            public function setContent(string $content): self { $this->data["content"] = $content; return $this; }
            public function setButton1(string $text): self { $this->data["button1"] = $text; return $this; }
            public function setButton2(string $text): self { $this->data["button2"] = $text; return $this; }
            public function jsonSerialize(): mixed { return $this->data; }
            public function handleResponse(Player $player, mixed $data): void { ($this->handler)($player, $data); }
        };
    }
}
