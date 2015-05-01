<?php
namespace emote;

use PHPInsight\Sentiment;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\plugin\PluginBase;
use volt\api\DynamicPage;
use volt\api\MonitoredWebsiteData;
use volt\api\StaticPage;
use volt\api\WebsiteData;

/**
 * Class Emote
 * @package emote
 * @volt-api micro
 */
class Emote extends PluginBase implements Listener{
    /** @var  Sentiment */
    private $sentiment;
    private $currentValues;
    private $dataStore;
    public function onEnable(){
        $this->currentValues = ["neu" => 0, "pos" => 0, "neg" => 0];
        $this->sentiment = new Sentiment();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        (new StaticPage("/emote", $this))->setContent(file_get_contents($this->getFile() . "resources/index.html"));
        $this->dataStore = new MonitoredWebsiteData($this);
        $this->dataStore["emote"] = ["currentSentiment" => 0];
    }
    public function onPlayerChat(PlayerChatEvent $event){
        $sentiment = $this->sentiment->score($event->getMessage());
        $this->currentValues["pos"] += $sentiment["pos"];
        $this->currentValues["neu"] += $sentiment["neu"];
        $this->currentValues["neg"] += $sentiment["neg"];
        $this->updatePage();
    }
    private function updatePage(){
        $this->getLogger()->info("Updating");
        arsort($this->currentValues);
        switch(key($this->currentValues)){
            case 'pos':
                $this->dataStore["emote"] = ["currentSentiment" => 1];
                break;
            case 'neu':
                $this->dataStore["emote"] = ["currentSentiment" => 0];
                break;
            case 'neg':
                $this->dataStore["emote"] = ["currentSentiment" => -1];
                break;
        }
    }
}
