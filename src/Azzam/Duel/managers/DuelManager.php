<?php

namespace Azzam\Duel\Managers;

use Azzam\Duel\Main;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\Position;
use pocketmine\world\World;

class DuelManager
{
    use SingletonTrait;
    private array $duelRequests = [];
    private array $activeDuels = [];
    private Main $plugin;
    private ?World $duelWorld = null;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        $this->loadDuelWorld();
    }

    /**
     * Obtient le monde de duel à partir de la configuration.
     * Si le monde n'existe pas, le serveur sera arrêté.
     */
    public function loadDuelWorld(): void
    {
        $worldName = $this->plugin->getConfig()->get("duel-world", "Duel");

        $worldManager = $this->plugin->getServer()->getWorldManager();
        $this->duelWorld = $worldManager->getWorldByName($worldName);

        if ($this->duelWorld === null) {
            if ($worldManager->loadWorld($worldName)) {
                $this->duelWorld = $worldManager->getWorldByName($worldName);
            } else {
                $this->plugin->getLogger()->error("Le monde de duel '$worldName' n'existe pas ou n'a pas pu être chargé. Fermeture du serveur...");
                $this->plugin->getServer()->shutdown();
            }
        }
    }

    /**
     * Retourne le monde de duel s'il est chargé et valide.
     */
    public function getDuelWorld(): ?World
    {
        return $this->duelWorld;
    }

    public function requestDuel(Player $player, Player $opponent, int $amount, string $kit): void
    {
        $this->duelRequests[$player->getName()] = [
            "opponent" => $opponent->getName(),
            "amount" => $amount,
            "kit" => $kit
        ];
        $player->sendMessage("§9>> §fDemande de duel envoyée à {$opponent->getName()} pour {$amount}$. Kit: {$kit}");
        $opponent->sendMessage("§9>> §f{$player->getName()} vous a défié en duel pour {$amount}$ avec le kit: {$kit}. Utilisez /duel accept ou /duel deny.");
    }

    public function acceptDuel(Player $player): void
    {
        $request = $this->duelRequests[$player->getName()] ?? null;
        if ($request) {
            $opponent = Server::getInstance()->getPlayerExact($request["opponent"]);
            if ($opponent) {
                $this->startDuel($player, $opponent, $request["amount"], $request["kit"]);
                unset($this->duelRequests[$player->getName()]);
            } else {
                $player->sendMessage("§9>> §fLe joueur n'est plus en ligne.");
            }
        } else {
            $player->sendMessage("§9>> §fAucune demande de duel trouvée.");
        }
    }

    public function denyDuel(Player $player): void
    {
        $request = $this->duelRequests[$player->getName()] ?? null;
        if ($request) {
            $opponent = Server::getInstance()->getPlayerExact($request["opponent"]);
            if ($opponent) {
                $opponent->sendMessage("§9>> §f{$player->getName()} a refusé votre duel.");
            }
            unset($this->duelRequests[$player->getName()]);
        } else {
            $player->sendMessage("§9>> §fAucune demande de duel trouvée.");
        }
    }

    private function startDuel(Player $player1, Player $player2, int $amount, string $kit): void
    {
        $world = $this->plugin->getServer()->getWorldManager()->getWorldByName("Duel");
        $player1->teleport(new Position(-48, 39, -32, $world));
        $player2->teleport(new Position(-48, 39, 8, $world));

        $this->activeDuels[$player1->getName()] = $player2->getName();
        $this->plugin->getTaskManager()->startDuelTask($player1);
        $this->plugin->getTaskManager()->startDuelTask($player2);
        $this->plugin->getTaskManager()->startDuelTimer();

        $player1->setHealth(20);
        $player2->setHealth(20);
    }

    public function handlePlayerJoin(Player $player): void
    {
        // Si le joueur est dans le monde "Duel", on s'assure qu'il n'a pas d'équipement.
        if ($player->getWorld()->getFolderName() === "Duel") {
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $player->getEffects()->clear();
        }
    }

    /**
     * Gère les actions à effectuer lorsqu'un joueur quitte le serveur.
     */
    public function handlePlayerQuit(Player $player): void
    {
        if (isset($this->activeDuels[$player->getName()])) {
            $opponentName = $this->activeDuels[$player->getName()];
            $opponent = Server::getInstance()->getPlayerExact($opponentName);

            if ($opponent !== null) {
                $opponent->sendMessage("§cVotre adversaire a quitté le duel. Vous avez gagné par forfait.");
                $this->endDuel($opponent, $player); // Termine le duel avec l'adversaire comme gagnant.
            }

            unset($this->activeDuels[$player->getName()]);
            unset($this->activeDuels[$opponentName]);
        }

        // Annuler les demandes de duel associées au joueur
        unset($this->duelRequests[$player->getName()]);
    }

    /**
     * Gère les actions à effectuer lorsqu'un joueur meurt pendant un duel.
     */
    public function handlePlayerDeath(Player $player): void
    {
        if (isset($this->activeDuels[$player->getName()])) {
            $opponentName = $this->activeDuels[$player->getName()];
            $opponent = Server::getInstance()->getPlayerExact($opponentName);

            if ($opponent !== null) {
                $opponent->sendMessage("§aVous avez gagné le duel !");
                $player->sendMessage("§cVous avez perdu le duel.");
                $this->endDuel($opponent, $player); // Termine le duel avec l'adversaire comme gagnant.
            }

            unset($this->activeDuels[$player->getName()]);
            unset($this->activeDuels[$opponentName]);
        }
    }

    public function endDuel(Player $winner, Player $loser): void
    {
        // Nettoyage des inventaires et des effets
        $winner->getInventory()->clearAll();
        $winner->getArmorInventory()->clearAll();
        $winner->getEffects()->clear();
        $winner->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());

        $loser->getInventory()->clearAll();
        $loser->getArmorInventory()->clearAll();
        $loser->getEffects()->clear();
        $loser->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());

        // Annuler les tâches liées au duel
        $this->plugin->getTaskManager()->cancelDuelTask($winner);
        $this->plugin->getTaskManager()->cancelDuelTask($loser);
        $this->plugin->getTaskManager()->cancelDuelTimer();

        // Retirer les joueurs des duels actifs
        unset($this->activeDuels[$winner->getName()]);
        unset($this->activeDuels[$loser->getName()]);
    }

    public function hasDuelRequest(Player $player): bool
    {
        return isset($this->duelRequests[$player->getName()]);
    }

    public function hasPendingRequest(Player $player): bool
    {
        return isset($this->duelRequests[$player->getName()]);
    }

    public function cancelDuelRequest(Player $player): void
    {
        unset($this->duelRequests[$player->getName()]);
    }
}
