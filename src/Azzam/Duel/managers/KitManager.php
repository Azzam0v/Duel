<?php

namespace Azzam\Duel\managers;

use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\PotionType;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

class KitManager
{
    /**
     * Attribue un kit au joueur en fonction du nom du kit.
     */
    public function giveKit(Player $player, string $kitName): void
    {
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getEffects()->clear();

        switch (strtolower($kitName)) {
            case "gapple":
                $this->giveGappleKit($player);
                break;
            case "potion":
                $this->givePotionKit($player);
                break;
            case "cheat":
                $this->giveCheatKit($player);
                break;
            default:
                $player->sendMessage("§cErreur : Kit inconnu.");
        }
    }

    private function giveGappleKit(Player $player): void
    {
        $sword = VanillaItems::DIAMOND_SWORD();
        $sword->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 4));
        $player->getInventory()->addItem($sword, VanillaItems::GOLDEN_APPLE()->setCount(5), VanillaItems::PUMPKIN_PIE());
        $this->equipDiamondArmor($player, 3);
    }

    private function givePotionKit(Player $player): void
    {
        $sword = VanillaItems::DIAMOND_SWORD();
        $sword->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 5));
        $player->getInventory()->addItem(
            $sword,
            VanillaItems::ENDER_PEARL()->setCount(8),
            VanillaItems::POTION()->setType(PotionType::SWIFTNESS()),
            VanillaItems::SPLASH_POTION()->setType(PotionType::STRONG_HEALING())->setCount(33)
        );
        $this->equipDiamondArmor($player, 3);
    }

    private function giveCheatKit(Player $player): void
    {
        $sword = VanillaItems::DIAMOND_SWORD();
        $sword->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 5));
        $player->getInventory()->addItem($sword, VanillaItems::ENCHANTED_GOLDEN_APPLE()->setCount(2));
        $this->equipDiamondArmor($player, 3);
    }

    /**
     * Équipe une armure en diamant avec un certain niveau de protection.
     */
    private function equipDiamondArmor(Player $player, int $protectionLevel): void
    {
        $armorItems = [
            VanillaItems::DIAMOND_HELMET(),
            VanillaItems::DIAMOND_CHESTPLATE(),
            VanillaItems::DIAMOND_LEGGINGS(),
            VanillaItems::DIAMOND_BOOTS()
        ];

        foreach ($armorItems as $armorItem) {
            $armorItem->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), $protectionLevel));
            $player->getArmorInventory()->addItem($armorItem);
        }
    }
}