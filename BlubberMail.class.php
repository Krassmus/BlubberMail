<?php

require_once dirname(__file__)."/models/MailProcessor.class.php";

class BlubberMail extends StudIPPlugin implements SystemPlugin {
    
    public function __construct() {
        parent::__construct();
        if ($GLOBALS['user']->id !== "nobody") {
            if (!Navigation::hasItem("/links/settings/blubber")) {
                $settings_tab = new Navigation(_("Blubber"), PluginEngine::getURL($this, array(), "settings"));
                Navigation::addItem("/links/settings/blubber", $settings_tab);
            }
            $settings_tab = new AutoNavigation(_("Mails"), PluginEngine::getURL($this, array(), "settings"));
            Navigation::addItem("/links/settings/blubber/mails", $settings_tab);
        }
        NotificationCenter::addObserver(MailProcessor::getInstance(), "sendBlubberMails", "PostingHasSaved");
    }
    
    public function settings_action() {
        if (Request::isPost()) {
            $config = UserConfig::get($GLOBALS['user']->id);
            $config->store('BLUBBER_MAX_USER_NOTIFICATIONS', Request::option("BLUBBER_MAX_USER_NOTIFICATIONS"));
            var_dump(Request::get("BLUBBER_MAX_USER_NOTIFICATIONS"));
            $config->store('BLUBBER_USER_STREAM_ABO', implode(",", Request::getArray("streams")));
            PageLayout::postMessage(MessageBox::success(_("Daten erfolgreich gespeichert.")));
        }
        PageLayout::setTabNavigation('/links/settings');
        $template = $this->getTemplate("mails.php");
        echo $template->render();
    }
    
    protected function getTemplate($template_file_name, $layout = "without_infobox") {
        if (!$this->template_factory) {
            $this->template_factory = new Flexi_TemplateFactory(dirname(__file__)."/templates");
        }
        $template = $this->template_factory->open($template_file_name);
        if ($layout) {
            if (method_exists($this, "getDisplayName")) {
                PageLayout::setTitle($this->getDisplayName());
            } else {
                PageLayout::setTitle(get_class($this));
            }
            $template->set_layout($GLOBALS['template_factory']->open($layout === "without_infobox" ? 'layouts/base_without_infobox' : 'layouts/base'));
        }
        return $template;
    }
}
