<?php

namespace Azzam\Duel\managers;

use pocketmine\player\Player;
use Terpz710\EconomyPE\Money;

class EconomyManager
{
    public function getBalance(Player $player): int
    {
        //utilisation de EconomyPE

        //TODO : pouvoir prendre en charge plusieurs api selon la config
        return Money::getInstance()->getMoneyPlayer($player);
    }

    public function deductMoney(Player $player, int $amount): bool
    {
        if ($this->getBalance($player) >= $amount) {
            Money::getInstance()->removeMoney($player, $amount);
            return true;
        }
        return false;
    }

    public function addMoney(Player $player, int $amount): void
    {
        Money::getInstance()->addMoney($player, $amount);
    }
}