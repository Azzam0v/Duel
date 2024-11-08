<?php

namespace Azzam\Duel\tasks;

use Azzam\Duel\Main;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use SxxCodezx\Sounds;

class DuelTimer extends Task
{
    private Main $plugin;
    private int $timeRemaining;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        $this->timeRemaining = 190;
    }

    public function onRun(): void
    {
        $world = Server::getInstance()->getWorldManager()->getWorldByName("Duel");
        if ($this->timeRemaining <= 0) {
            $this->endDuelAsDraw($world);
            $this->plugin->getTaskManager()->cancelDuelTimer();
            return;
        }

        $this->notifyPlayers($world);
        $this->timeRemaining--;
    }

    private function notifyPlayers($world): void
    {
        $notificationTimes = [180, 120, 60, 30, 10, 3, 2, 1];
        foreach ($world->getPlayers() as $player) {
            if (in_array($this->timeRemaining, $notificationTimes)) {
                Sounds::addSound($player, 'note.bass', 50, 1);
                $title = ($this->timeRemaining <= 10) ? "§9{$this->timeRemaining} secondes" : null;
                $player->sendMessage("§9>> §fPlus que §9{$this->timeRemaining} secondes §favant la fin du duel !");
                if ($title) {
                    $player->sendTitle($title);
                }
            }
        }
    }

    private function endDuelAsDraw($world): void
    {
        foreach ($world->getPlayers() as $player) {
            $this->resetPlayerState($player);
            $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName("AzzamSpawn")->getSafeSpawn());
            $player->sendTitle("§9Duel", "§fMatch nul !");
        }
        unset($this->plugin->money2);
    }

    private function resetPlayerState(Player $player): void
    {
        $player->getInventory()->clearAll();
        $this->clearArmor($player);
        $player->setNoClientPredictions(false);
        $player->getEffects()->clear();
    }

    private function clearArmor(Player $player): void
    {
        $armorInventory = $player->getArmorInventory();
        $armorInventory->setHelmet(VanillaItems::AIR());
        $armorInventory->setChestplate(VanillaItems::AIR());
        $armorInventory->setLeggings(VanillaItems::AIR());
        $armorInventory->setBoots(VanillaItems::AIR());
    }
}
