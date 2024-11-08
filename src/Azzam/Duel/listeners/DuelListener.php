<?php

namespace Azzam\Duel\listeners;

use Azzam\Duel\managers\DuelManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;

class DuelListener implements Listener
{
    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        DuelManager::getInstance()->handlePlayerJoin($player);
    }

    public function onQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        DuelManager::getInstance()->handlePlayerQuit($player);
    }

    public function onPlayerDeath(PlayerDeathEvent $event): void
    {
        $player = $event->getPlayer();
        DuelManager::getInstance()->handlePlayerDeath($player);
    }

    public function onDrop(PlayerDropItemEvent $event): void
    {
        //TODO : lier a la config
        if ($event->getPlayer()->getWorld()->getFolderName() === "Duel") {
            $event->cancel();
        }
    }
}
