<?php
require_once(Mage::getBaseDir('lib').'/GA-client/GA-client.php');
use UnitedPrototype\GoogleAnalytics;
class Sluice_Connect_Model_Observer {

    public $errorLog = 'sluice_connect.log';
    // Add to cart trecking
    public function hookToAddToCart($observer) {

        if (!Mage::helper('googleanalytics')->isGoogleAnalyticsAvailable()) {
            Mage::log("Google analytics doesn't install", 3, $this->errorLog);
            return;
        }
        $accountId = Mage::getStoreConfig(Mage_GoogleAnalytics_Helper_Data::XML_PATH_ACCOUNT);
        $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        if (empty($baseUrl)) {
            Mage::log("Base url is empty", 3, $this->errorLog);
            return;
        }

        try{
            $tracker = new GoogleAnalytics\Tracker($accountId, $baseUrl);
            $event = new GoogleAnalytics\Event("Cart", "Add", "Product");
            $visitor = new GoogleAnalytics\Visitor();
            $visitor->setIpAddress($_SERVER['REMOTE_ADDR']);
            $visitor->setUserAgent($_SERVER['HTTP_USER_AGENT']);
            $visitor->setScreenResolution('1024x768');
            $session = new GoogleAnalytics\Session();
            $tracker->trackEvent($event, $session, $visitor);
        } catch (Exception $e) {
            Mage::log('Tracking sluice connect '.$e->getMessage(), 3, $this->errorLog);
            return;
        }
    }

    //config save hook
    public function hookSavePluginConfig($observer) {
        $this->errorLog = 'sluice_error.log';
        $sluiceApiUrl = 'http://sluicehq.com/api/v.php?version=2&method=SetMagentoApi';

        $websiteCode = Mage::getSingleton('adminhtml/config_data')->getWebsite();
        $websiteId = Mage::getModel('core/website')->load($websiteCode)->getId();
        $store = Mage::app()->getWebsite($websiteId)->getDefaultStore();


        if(empty($websiteId)){
            $websiteId = 'main';
        }
        $userName = 'sluice-connect-website#'.$websiteId;
        $roleName = 'sluice-connect-role-website#'.$websiteId;
        $sluiceEmail = 'sluice_email_'.$websiteId.'@sluicehq.com';

        $token = Mage::getStoreConfig('sluice_section/sluice_group/sluice_field', $store);
        if (empty($token)) {
            Mage::log("Empty token", 3, $this->errorLog);
            Mage::getSingleton('core/session')->addError("Empty token"); 
            return;
        }

        if (strlen(trim($token)) != 32) {
            Mage::log("Whrong token", 3, $this->errorLog);
            Mage::getSingleton('core/session')->addError("Wrong token");
            return;
        }

        try {
            $user = Mage::getModel('api/user')->setUsername($userName)->loadByUsername();
            if (!$user->userExists()) {
                $role = Mage::getModel("api/roles")->setName($roleName)->setRoleType('G')->save();
                Mage::getModel("api/rules")->setRoleId($role->getId())->setResources(array("all"))->saveRel();

                $apiKey = md5(time());
                $user = Mage::getModel('api/user');
                $user->setData(array(
                    'username' => $userName,
                    'firstname' => $userName,
                    'lastname' => $userName,
                    'email' => $sluiceEmail,
                    'api_key' => $apiKey,
                    'api_key_confirmation' => $apiKey,
                    'is_active' => 1,
                    'user_roles' => '',
                    'assigned_user_role' => '',
                    'role_name' => '',
                    'roles' => array($role->getId())
                ));
                $user->save()->load($user->getId());
                $user->setRoleIds(array($role->getId()))->setRoleUserId($user->getUserId())->saveRelations();
                Mage::getModel('core/config')->saveConfig('sluice_section/sluice_group/api_field', $apiKey );
                Mage::getConfig()->reinit();
                Mage::app()->reinitStores();
            }
            $apiKey = Mage::getStoreConfig('sluice_section/sluice_group/api_field', $store);
            if(empty($apiKey)){
                Mage::log("Apy key is empty" . $ex->getMessage(), 3, $this->errorLog);
                Mage::getSingleton('core/session')->addError("Please delete 'sluice-connect' user and try agan");
                return;
            }
        } catch (Exception $ex) {
            Mage::log("User/Role saving error" . $ex->getMessage(), 3, $this->errorLog);
            Mage::getSingleton('core/session')->addError("User/Role saving error");
            return;
        }

        try {
            $data = $arrayName = array(
                'username' => $userName,
                'apiKey' => $apiKey,
                'token' => trim($token),
                'url'=>$store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)
            );

            $sluiceApiUrl = $sluiceApiUrl . '&' . http_build_query($data);
            Mage::log("Sended data: " . $sluiceApiUrl , 3, $this->errorLog);
            file_get_contents($sluiceApiUrl);
        } catch (Exception $ex) {
            Mage::log("Error is request to sluice " . $ex->getMessage(), 3, $this->errorLog);
            Mage::getSingleton('core/session')->addError("Error is request to sluice " . $ex->getMessage());
            return;
        }
    }
}
