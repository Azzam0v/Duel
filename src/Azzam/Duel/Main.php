<?php

namespace Azzam\Duel;

use Azzam\Duel\listeners\DuelListener;
use Azzam\Duel\Managers\DuelManager;
use Azzam\Duel\Managers\EconomyManager;
use Azzam\Duel\Managers\FormManager;
use Azzam\Duel\managers\TaskManager;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase
{
    private DuelManager $duelManager;
    private EconomyManager $economyManager;
    private FormManager $formManager;
    private TaskManager $taskManager;

    protected function onEnable(): void
    {
        $this->saveDefaultConfig();

        $this->duelManager = new DuelManager($this);
        $this->economyManager = new EconomyManager();
        $this->formManager = new FormManager($this, $this->duelManager);
        $this->taskManager = new TaskManager($this);

        // Enregistrement des événements
        $this->getServer()->getPluginManager()->registerEvents(new DuelListener(), $this);
    }

    public function getDuelManager(): DuelManager
    {
        return $this->duelManager;
    }

    public function getEconomyManager(): EconomyManager
    {
        return $this->economyManager;
    }

    public function getTaskManager(): TaskManager
    {
        return $this->taskManager;
    }

    public function getFormManager(): FormManager
    {
        return $this->formManager;
    }
}
