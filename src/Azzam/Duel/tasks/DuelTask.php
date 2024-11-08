<?php

namespace Azzam\Duel\tasks;

use Azzam\Duel\Main;
use Azzam\Duel\Managers\KitManager;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use SxxCodezx\Sounds;

class DuelTask extends Task
{
    private Main $plugin;
    private Player $player;
    private int $timeLeft;
    private KitManager $kitManager;

    public function __construct(Main $plugin, Player $player)
    {
        $this->plugin = $plugin;
        $this->player = $player;
        $this->timeLeft = 10;
        $this->kitManager = new KitManager();

        $this->initializePlayer();
    }

    private function initializePlayer(): void
    {
        $this->player->setNoClientPredictions(true);
        $this->player->getInventory()->clearAll();
        $this->plugin->timertask[$this->player->getName()] = true;
    }

    public function onRun(): void
    {
        if (!$this->player->isOnline()) {
            $this->plugin->getTaskManager()->cancelDuelTask($this->player);
            return;
        }

        $this->showCountdown();
        if ($this->timeLeft === 1) {
            $this->beginDuel();
            $this->plugin->getTaskManager()->cancelDuelTask($this->player);
        }

        $this->timeLeft--;
    }

    private function showCountdown(): void
    {
        $titles = [6 => "§45", 5 => "§c4", 4 => "§63", 3 => "§e2", 2 => "§a1", 1 => "§9Duel"];
        if (isset($titles[$this->timeLeft])) {
            Sounds::addSound($this->player, 'note.bass', 50, 1);
            $this->player->sendTitle($titles[$this->timeLeft]);
            if ($this->timeLeft === 2) {
                $this->prepareInventory();
            }
        }
    }

    private function beginDuel(): void
    {
        $this->player->setNoClientPredictions(false);
        $this->plugin->timertask[$this->player->getName()] = false;
        $this->player->sendTitle("§9Duel", "§fDébut du Duel !");
    }

    private function prepareInventory(): void
    {
        $kit = $this->plugin->kit[$this->player->getName()] ?? '';
        $this->kitManager->giveKit($this->player, $kit);
    }
}
