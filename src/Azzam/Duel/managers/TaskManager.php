<?php

namespace Azzam\Duel\managers;

use Azzam\Duel\Main;
use Azzam\Duel\tasks\DuelTask;
use Azzam\Duel\tasks\DuelTimer;
use pocketmine\player\Player;
use pocketmine\scheduler\TaskHandler;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\utils\SingletonTrait;

class TaskManager
{
    use SingletonTrait;

    private Main $plugin;
    private TaskScheduler $scheduler;
    private array $taskHandlers = [];

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        $this->scheduler = $plugin->getScheduler();
    }

    /**
     * Démarre la tâche DuelTask pour un joueur spécifique.
     */
    public function startDuelTask(Player $player): void
    {
        $task = new DuelTask($this->plugin, $player);
        $this->taskHandlers[$player->getName()]["duel"] = $this->scheduler->scheduleRepeatingTask($task, 20);
    }

    /**
     * Démarre la tâche DuelTimer pour gérer le temps total d'un duel.
     */
    public function startDuelTimer(): void
    {
        $task = new DuelTimer($this->plugin);
        $this->taskHandlers["duel_timer"] = $this->scheduler->scheduleRepeatingTask($task, 20);
    }

    /**
     * Annule la tâche DuelTask d'un joueur spécifique.
     */
    public function cancelDuelTask(Player $player): void
    {
        if (isset($this->taskHandlers[$player->getName()]["duel"])) {
            $this->taskHandlers[$player->getName()]["duel"]->cancel();
            unset($this->taskHandlers[$player->getName()]["duel"]);
        }
    }

    /**
     * Annule la tâche DuelTimer globale.
     */
    public function cancelDuelTimer(): void
    {
        if (isset($this->taskHandlers["duel_timer"])) {
            $this->taskHandlers["duel_timer"]->cancel();
            unset($this->taskHandlers["duel_timer"]);
        }
    }

    /**
     * Annule toutes les tâches associées à un joueur, par exemple lors de sa déconnexion.
     */
    public function cancelAllTasksForPlayer(Player $player): void
    {
        if (isset($this->taskHandlers[$player->getName()])) {
            foreach ($this->taskHandlers[$player->getName()] as $handler) {
                $handler->cancel();
            }
            unset($this->taskHandlers[$player->getName()]);
        }
    }

    /**
     * Annule toutes les tâches programmées.
     */
    public function cancelAllTasks(): void
    {
        foreach ($this->taskHandlers as $handlers) {
            foreach ($handlers as $handler) {
                $handler->cancel();
            }
        }
        $this->taskHandlers = [];
    }
}
