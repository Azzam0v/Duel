<?php

namespace Azzam\Duel\commands;

use Azzam\Duel\Main;
use Azzam\Duel\Managers\DuelManager;
use Azzam\Duel\managers\FormManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;

class DuelCommand extends Command implements PluginOwned
{
    private Main $plugin;
    private DuelManager $duelManager;

    public function __construct(Main $plugin)
    {
        parent::__construct("duel", "Commande pour gérer les duels", "/duel [accept|deny|cancel|tp]", ["duel"]);
        $this->plugin = $plugin;
        $this->duelManager = $plugin->getDuelManager();
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§cCette commande est seulement disponible pour les joueurs.");
            return false;
        }

        if (empty($args)) {
            FormManager::getInstance()->sendDuelRequestForm($sender);
            return true;
        }

        switch (strtolower($args[0])) {
            case "accept":
                $this->handleAccept($sender);
                break;
            case "deny":
                $this->handleDeny($sender);
                break;
            case "cancel":
                $this->handleCancel($sender);
                break;
            case "tp":
                $this->handleTeleport($sender);
                break;
            default:
                $sender->sendMessage("§cUtilisation: /duel [accept|deny|cancel|tp]");
        }

        return true;
    }

    private function handleAccept(Player $player): void
    {
        if ($this->duelManager->hasDuelRequest($player)) {
            $this->duelManager->acceptDuel($player);
        } else {
            $player->sendMessage("§cVous n'avez aucune demande de duel en attente.");
        }
    }

    private function handleDeny(Player $player): void
    {
        if ($this->duelManager->hasDuelRequest($player)) {
            $this->duelManager->denyDuel($player);
        } else {
            $player->sendMessage("§cVous n'avez aucune demande de duel en attente.");
        }
    }

    private function handleCancel(Player $player): void
    {
        if ($this->duelManager->hasPendingRequest($player)) {
            $this->duelManager->cancelDuelRequest($player);
            $player->sendMessage("§aVotre demande de duel a été annulée.");
        } else {
            $player->sendMessage("§cVous n'avez aucune demande de duel en cours.");
        }
    }

    private function handleTeleport(Player $player): void
    {
        if ($player->getGamemode() === GameMode::CREATIVE) {
            //TODO : lier a la config
            $world = $this->plugin->getServer()->getWorldManager()->getWorldByName("Duel");
            if ($world !== null) {
                $player->teleport($world->getSafeSpawn());
                $player->sendMessage("§aVous avez été téléporté dans le monde Duel.");
            } else {
                $player->sendMessage("§cLe monde Duel n'est pas chargé.");
            }
        } else {
            $player->sendMessage("§cVous devez être en mode créatif pour utiliser cette commande.");
        }
    }

    public function getOwningPlugin(): Main
    {
        return $this->plugin;
    }
}
