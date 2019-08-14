<?php

use UnitedPrototype\GoogleAnalytics;

class Sluice_Connect_Model_Observer {

    // Add to cartd trecking
    public function hookToAddToCart($observer) {
        if (!Mage::helper('googleanalytics')->isGoogleAnalyticsAvailable()) {
            Mage::log("Google analytics doesn't install", 3, $errorLog);
            return;
        }
        $accountId = Mage::getStoreConfig(Mage_GoogleAnalytics_Helper_Data::XML_PATH_ACCOUNT);
        $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        if (empty($baseUrl)) {
            Mage::log("Base url is empty", 3, $errorLog);
            return;
        }

        $tracker = new GoogleAnalytics\Tracker($accountId, $baseUrl);
        $event = new GoogleAnalytics\Event("Cart", "Add", "Product");
        $tracker->trackEvent($event, null, null);
    }

    //config save hook
    public function hookSavePluginConfig($observer) {
        $errorLog = 'sluice_error.log';
        $sluiceEmail = 'knight@sluicehq.com';
        $sluiceApiUrl = 'http://sluicehq.com/api/v.php?version=2&method=SetMagentoApi';
        $userName = 'sluice-connect';
        $roleName = 'sluice-connect-role';

        $token = Mage::getStoreConfig('sluice_section/sluice_group/sluice_field', Mage::app()->getStore());
        if (empty($token)) {
            Mage::log("Empty token", 3, $errorLog);
            Mage::getSingleton('core/session')->addError("Empty token"); 
            return;
        }

        if (strlen(trim($token)) != 32) {
            Mage::log("Whrong token", 3, $errorLog);
            Mage::getSingleton('core/session')->addError("Whrong token"); 
            return;
        }

        try {
            $user = Mage::getModel('api/user')->setUsername($userName)->loadByUsername();
            if (!$user->userExists()) {
                $role = Mage::getModel("api/roles")->setName($roleName)->setRoleType('G')->save();
                Mage::getModel("api/rules")->setRoleId($role->getId())->setResources(array("all"))->saveRel();

                $apiKey = md5(uniqid(rand(), true));
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
            }
            $apiKey = $user->getApiKey();
        } catch (Exception $ex) {
            Mage::log("User/Role saving error" . $ex->getMessage(), 3, $errorLog);
            Mage::getSingleton('core/session')->addError("User/Role saving error");
            return;
        }

        try {
            $data = $arrayName = array(
                'username' => $userName,
                'apiKey' => $apiKey,
                'token' => trim($token));
            $sluiceApiUrl = $sluiceApiUrl . '&' . http_build_query($data);
            file_get_contents($sluiceApiUrl);
        } catch (Exception $ex) {
            Mage::log("Error is request to sluice " . $ex->getMessage(), 3, $errorLog);
            Mage::getSingleton('core/session')->addError("Error is request to sluice " . $ex->getMessage());
            return;
        }
    }
}
?>
