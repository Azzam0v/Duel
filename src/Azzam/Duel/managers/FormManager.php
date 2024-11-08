<?php

namespace Azzam\Duel\managers;

use Azzam\Duel\Main;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

class FormManager
{
    private Main $plugin;
    private DuelManager $duelManager;

    use SingletonTrait;

    public function __construct($plugin, $duelManager)
    {
        $this->plugin = $plugin;
        $this->duelManager = $duelManager;
    }

    public function sendDuelRequestForm(Player $player): void
    {
        $form = new CustomForm(function (Player $player, array $data = null) {
            if ($data === null) return;
            $opponentName = $data[1];
            $amount = (int) $data[2];
            $kit = $data[3];

            $opponent = $this->plugin->getServer()->getPlayerExact($opponentName);
            if ($opponent && is_numeric($amount) && in_array($kit, ["Gapple", "Potion", "Cheat"])) {
                $this->duelManager->requestDuel($player, $opponent, $amount, $kit);
            } else {
                $player->sendMessage("§cErreur : vérifiez les informations saisies.");
            }
        });

        $form->setTitle("Duel");
        $form->addLabel("§fDemander un joueur en Duel");
        $form->addInput("§fEntrez §9le pseudo §fdu joueur.", "Pseudo");
        $form->addInput("§fEntrez §9la somme §fque vous voulez miser.", "5000");
        $form->addInput("§fChoix du Kit", "Gapple, Potion ou Cheat");

        $form->sendToPlayer($player);
    }
}
