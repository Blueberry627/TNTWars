<?php

namespace tntwars\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use tntwars\TNTWars;

class CreatorCommand extends Command
{
    /** @var TNTWars */
    private $plugin;

    public function __construct(TNTWars $plugin)
    {
        parent::__construct('tw', 'TNTWars command');
        $this->plugin = $plugin;
        $this->setPermission('tntwars.creator'); // Set the permission here
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if ($sender instanceof Player) {
            if ($sender->hasPermission('tntwars.creator')) { // Check if the player has the required permission
                // Your existing command logic here
            } else {
                $sender->sendMessage('§cYou do not have permission to use this command.');
            }
        } else {
            $sender->sendMessage('§cThe command can only be used by a player.');
        }
        return true;
    }
}