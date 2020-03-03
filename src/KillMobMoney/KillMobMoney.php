<?php

 /** @peptiGamer author
 */

namespace KillMobMoney;

use pocketmine\event\entity\{EntityDeathEvent, EntityDamageByEntityEvent};
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

use onebone\economyapi\EconomyAPI;

class KillMobMoney extends PluginBase implements Listener{
      /** @var \onebone\economyapi\EconomyAPI */
    private $economy;
    private $pureEntitiesX;

  public function onEnable() : void{
    if(!is_dir($this->getDataFolder())){
      @mkdir($this->getDataFolder());
    }
     if ($this->checkDependents()) {
    $this->saveDefaultConfig();
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }
}
      /**
     * @return bool
     */
    public function checkDependents() : bool{
        $this->economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
        if (is_null($this->economy)) {
            $this->getLogger()->critical("EconomyAPI is required. Plugin disabled.");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return false;
        }
        $this->pureEntitiesX = $this->getServer()->getPluginManager()->getPlugin("PureEntitiesX");
        if (!$this->pureEntitiesX) {
            $this->getLogger()->info("PureEntitiesX is not installed on your server system. This is an optional plugin to allow you to use the Mob AI system with this plugin. Disabling AI System."); //Will add more AI plugin systems once more become stable and are working.
            return false;
        }
        return true;
    }

          /**
           * @param EntityDeathEvent $event
           */
          public function onEntityDeath(EntityDeathEvent $event) : void{
          $mobvictim = $event->getEntity();
    if($mobvictim->getLastDamageCause() instanceof EntityDamageByEntityEvent){
      if($mobvictim->getLastDamageCause()->getDamager() instanceof Player){
        if(empty($this->getConfig()->get("worlds", [])) or in_array($mobvictim->getLevel()->getName(), $this->getConfig()->get("worlds", []))){
          $killerplayer = $mobvictim->getLastDamageCause()->getDamager();

          if(!EconomyAPI::getInstance()->addMoney($killerplayer, $this->getConfig()->get("mob-money", 2))){
            $this->getLogger()->error("Failed to add money due to EconomyAPI error");
            return;
          }
          if($this->getConfig()->getNested("messages.enable", true)){
            $message = str_replace("%MOB_MONEY%", $this->getConfig()->get("mob-money", 2), $this->getConfig()->getNested("messages.mobmessage", "§e§l(MOBKILL)§r §6You have earned §e%MOB_MONEY% §6for killing §e%MOB%"));
            $message = str_replace("%MOB%", $mobvictim->getName(), $message);
            $killerplayer->sendMessage($message);
          }
        }
      }
    }
  }
}
