<?php

namespace tntwars\arena;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use tntwars\commands\CreatorCommand;
use tntwars\commands\PlayerCommand;
use tntwars\TNTWars;
use tntwars\utils\PluginUtils;
use function array_flip;
use function array_keys;
use function closedir;
use function count;
use function file_exists;
use function is_dir;
use function max;
use function mkdir;
use function opendir;
use function readdir;
use function scandir;
use function str_replace;

class Arena
{
    const STATUS_ENABLE = 'on';
    const STATUS_INGAME = 'ingame';

    const BROADCAST_MESSAGE = 0;
    const BROADCAST_POPUP = 1;
    const BROADCAST_SOUND = 2;
    const BROADCAST_TITLE = 7;

    const MODE_BUILDER = 3;
    const MODE_BATTLE = 4;

    /**
     * Initialize class
     */
    public function ini(): void
    {
        if (!file_exists(TNTWars::getInstance()->getDataFolder())) {
            @mkdir(TNTWars::getInstance()->getDataFolder());
        }

        $pluginInstance = TNTWars::getInstance();
        $commandMap = TNTWars::getInstance()->getServer()->getCommandMap();
        $commandMap->register('tw', new CreatorCommand($pluginInstance));
        $commandMap->register('tntwars', new PlayerCommand($pluginInstance));

        $this->loadAll();
        TNTWars::getInstance()->getServer()->getLogger()->info('§a[TNTWars] Have been found (' . $this->getMaxArenas() . ') arena(s) have been loaded');
    }

    /**
     * @param string $name
     * @param int $team
     * @param int $task
     */
    private function sendWinning(string $arena, int $team, int $task): void
    {
        switch ($team) {
            case Team::BLUE_TEAM:
                TNTWars::getInstance()->getScheduler()->cancelTask($task);
                TNTWars::getInstance()->getScheduler()->scheduleRepeatingTask(new CelebrationTask($arena), 15);
                foreach (TNTWars::getInstance()->getServer()->getLevelByName($this->getLevel($arena))->getPlayers() as $player) {
                    if (!isset(TNTWars::getData()->getPlayersBlueTeam($arena)[$player->getName()])) {
                        $player->setGamemode(3);
                        $player->sendTitle('§4Game Over', '§9Blue §awin', 2);
                    } else {
                        $player->sendTitle('§6Victory', '§7You won the game', 2);
                        TNTWars::getInstance()->getScheduler()->scheduleRepeatingTask(new DefaultAnimation($player), 15);
                    }
                }
                break;
            case Team::RED_TEAM:
                TNTWars::getInstance()->getScheduler()->cancelTask($task);
                TNTWars::getInstance()->getScheduler()->scheduleRepeatingTask(new CelebrationTask($arena), 15);
                foreach (TNTWars::getInstance()->getServer()->getLevelByName($this->getLevel($arena))->getPlayers() as $player) {
                    if (!isset(TNTWars::getData()->getPlayersRedTeam($arena)[$player->getName()])) {
                        $player->setGamemode(3);
                        $player->sendTitle('§4Game Over', '§cRed §awin', 2);
                    } else {
                        $player->sendTitle('§6Victory', '§7You won the game', 2);
                        TNTWars::getInstance()->getScheduler()->scheduleRepeatingTask(new DefaultAnimation($player), 15);
                    }
                }
                break;
            case Team::NULL_TEAM:
                TNTWars::getInstance()->getScheduler()->cancelTask($task);
                foreach (TNTWars::getInstance()->getServer()->getLevelByName($this->getLevel($arena))->getPlayers() as $player) {
                    $player->sendTitle('§cGame Over', '§a', 2);
                    $this->quit($arena, $player);
                }
                $this->load($arena);
                break;
        }
    }
}