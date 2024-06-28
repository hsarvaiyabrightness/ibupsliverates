<?php
/**
 * Data file
 *
 * @category   UPS
 * @package    UPS_Liverates
 * @subpackage Helper
 * @link       https://www.ups.com
 * @since      1.0.0
 * @author UPS eCommerce Shipping Liverates
 */
namespace UPS\Liverates\Helper;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\State;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Backend\Model\Auth\Session as AdminSession;
use UPS\Liverates\Model\LiveratesAddon;
use UPS\Liverates\Model\ResourceModel\LiveratesAddon\CollectionFactory as AddonCollection;
use Psr\Log\LoggerInterface;
use Firebase\JWT\JWT;
use Magento\Framework\Stdlib\DateTime\DateTime;
use UPS\Dashboard\Helper\Data as CoreHelper;

/**
 * Class Data.
 *
 * This helper files is for the general purpose
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const LIVERATES_ADDON_CONNECTION_URL = 'https://st01.api.itembase.com/connectivity/instances/2a460a69-dd8b-4aab-9e29-170831db4b00/connections/shipping/auth/v2?testMode=false';// @codingStandardsIgnoreLine
    public const ADDON_INSTANCE_ID = '2a460a69-dd8b-4aab-9e29-170831db4b00';
    public const LIVERATES_ADDON_ACTIVE = 'ups_dashboard/liverates/addon_active';
    public const END_POINT_URL = 'https://st01.api.itembase.com';
    public const CALLBACK_URL = 'https://st01-ups.dashboardlink.com/connect/magento2-live-rates/success';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfigInterface;
    
    /**
     * @var WriterInterface
     */
    protected $writerInterface;
    
    /**
     * @var typeListInterface
     */
    protected $typeListInterface;
    
    /**
     * @var StoreManager
     */
    protected $storeManager;
    
    /**
     * @var State
     */
    protected $state;

    /**
     * @var ProductMetadata
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\Module\ResourceInterface
     */

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $adminSession;

    /**
     * @var \UPS\Liverates\Model\LiveratesAddon
     */
    protected $liveratesLiveratesaddon;

    /**
     * @var \UPS\Liverates\Model\ResourceModel\LiveratesAddon\CollectionFactory
     */
    protected $addoncollection;

    /**
     * Log LoggerInterface
     * @var \UPS\Liverates\Logger\Logger
     */
    protected $customLogger;

    /**
     * Date Time// @codingStandardsIgnoreLine
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * Constructor.
     *
     * @param Context                   $context                    Context
     * @param ScopeConfigInterface      $scopeConfigInterface       Scope Config Interface
     * @param WriterInterface           $writerInterface            Writer Interface
     * @param TypeListInterface         $typeListInterface          Type List Interface
     * @param StoreManagerInterface     $storeManager               Store Manager Interface
     * @param State                     $state                      State
     * @param Curl                      $curl                       Curl
     * @param AdminSession              $adminSession               Admin Session
     * @param LiveratesAddon            $liveratesLiveratesaddon    Live Rate Adon
     * @param AddonCollection           $addoncollection            Addon Collection
     * @param \Psr\Log\LoggerInterface  $customLogger               Custom Logger Interface
     * @param DateTime                  $dateTime                   dateTime
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfigInterface,
        WriterInterface $writerInterface,
        TypeListInterface $typeListInterface,
        StoreManagerInterface $storeManager,
        State $state,
        Curl $curl,
        AdminSession $adminSession,
        LiveratesAddon $liveratesLiveratesaddon,
        AddonCollection $addoncollection,
        \Psr\Log\LoggerInterface $customLogger,
        DateTime $dateTime
    ) {
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->writerInterface = $writerInterface;
        $this->typeListInterface = $typeListInterface;
        $this->storeManager = $storeManager;
        $this->state = $state;
        $this->curl = $curl;
        $this->adminSession = $adminSession;
        $this->liveratesLiveratesaddon = $liveratesLiveratesaddon;
        $this->addoncollection = $addoncollection;
        $this->customLogger            = $customLogger;
        $this->dateTime = $dateTime;
        parent::__construct($context);
    }

    /**
     * Log information message.
     *
     * @param string $message       Message to be logged
     * @param bool   $useSeparator  Whether to use separator or not (default: false)
     * @return void
     */
    public function logInfo($message, $useSeparator = false)
    {
        #$this->customLogger->pushHandler( new \Monolog\Handler\StreamHandler( BP. '/var/log/ups_addons/ups_liverates.log') );// @codingStandardsIgnoreLine
        $this->customLogger->info($message);
    }

    /**
     * Log information message.
     *
     * @param string $message       Message to be logged
     * @param bool   $useSeparator  Whether to use separator or not (default: false)
     * @return void
     */
    public function logError($message, $useSeparator = false)
    {
        #$this->customLogger->pushHandler( new \Monolog\Handler\StreamHandler( BP. '/var/log/ups_addons/ups_liverates.log') );// @codingStandardsIgnoreLine
        $this->customLogger->error($message);
    }

    /**
     * Create get store data
     *
     * @return string
     */
    public function getStoreDetails()
    {

        if ($this->state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $request = $this->_request;
            $storeId = (int) $request->getParam('store', 0);
        } else {
            $storeId = $this->storeManager->getStore()->getStoreId();
        }

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;

        $data = [];
        $data['storeId'] = $storeId;
        $data['storeScope'] = $storeScope;
        return $data;
    }

    /**
     * Save connection process.
     *
     * This method is responsible for saving the API key and API secret for the connection process.
     *
     * @param string $apiKey    API key
     * @param string $apiSecret API secret
     * @return void
     */
    public function saveConnectionProcess($apiKey, $apiSecret)
    {

        $this->typeListInterface->cleanType('config');
        
        $storeData = $this->getStoreDetails();
        $storeId = $storeData['storeId'];
        $storeScope = $storeData['storeScope'];
        
        $url = self::LIVERATES_ADDON_CONNECTION_URL;

        $addonCollection = $this->addoncollection->create();
        $addonCollection->addFieldToSelect('*')
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('instance_id', self::ADDON_INSTANCE_ID);
        
        if (count($addonCollection) == 0) {

            $liveratesmodel = $this->liveratesLiveratesaddon;
            $addondata = [
                'api_key'=> $apiKey,
                'api_secret'=> $apiSecret,
                'email'=> $this->getAdminEmail(),
                'instance_id'=> self::ADDON_INSTANCE_ID,
                'connection_id'=> "",
                'store_id'=> $storeId,
                'status'=> '0'
            ];
            $liveratesmodel->setData($addondata);
            $liveratesmodel->save();
            $this->typeListInterface->cleanType('config');
            $this->logInfo("Live rate connected successfully");
            
            return [
                'response' => $addondata
            ];

        } else {
            $this->logInfo("Already Live rate connected");
            return [
                'response' => 'true',
                'status' => '200'
            ];
        }
    }

    /**
     * Generate UUID 4 secret
     *
     * @return string
     */
    public function generateApiSecret()
    {

        //@codingStandardsIgnoreStart
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
        //@codingStandardsIgnoreEnd
    }

    /**
     * Generate JWT 4 secret
     *
     * @return string
     */
    public function generateJWTApiSecret()
    {
        $keyLength = 32;
        return bin2hex(random_bytes($keyLength));
    }

    /**
     * Get admin logged-in user's email
     *
     * @return string|null
     */
    public function getAdminEmail()
    {
        $user = $this->adminSession->getUser();
        if ($user) {
            return $user->getEmail();
        }
        return null;
    }

    /**
     * Save a configuration value.
     *
     * @param string $path The configuration path.
     * @param mixed $value The value to be saved.
     * @param string $storeScope The store scope (default: \Magento\Store\Model\ScopeInterface::SCOPE_STORE).
     * @param int|null $storeId The store ID (default: null).
     * @return void
     */
    public function saveConfigValue($path, $value, $storeScope, $storeId)
    {
        $this->writerInterface->save($path, $value, $storeScope, $storeId);
        $this->typeListInterface->cleanType('config');
    }

    /**
     * Create get config
     *
     * @return string
     */
    public function getConfigValue()
    {
        $this->typeListInterface->cleanType('config');
        
        $storeData = $this->getStoreDetails();
        $storeId = $storeData['storeId'];
        $storeScope = $storeData['storeScope'];

        return $this->scopeConfigInterface->getValue(self::LIVERATES_ADDON_ACTIVE, $storeScope, $storeId);
    }

    /**
     * Create get config
     *
     * @return string
     */
    public function getConfigValueFont()
    {
        $this->typeListInterface->cleanType('config');
        $storeId = $this->storeManager->getStore()->getId();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        return $this->scopeConfigInterface->getValue(self::LIVERATES_ADDON_ACTIVE, $storeScope, $storeId);
    }

    /**
     * Remove Add On Connection
     *
     * @return string
     */
    public function deactiveAddonconnection()
    {
        $this->typeListInterface->cleanType('config');
        $storeData = $this->getStoreDetails();
        $storeId = $storeData['storeId'];
        $storeScope = $storeData['storeScope'];
        $addonCollection = $this->addoncollection->create();
        $addonCollection->addFieldToSelect('*')
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('instance_id', self::ADDON_INSTANCE_ID);
        $addonCount = $addonCollection->getSize();
        if ($addonCollection->getFirstItem()->getId()) {
            $data =  $addonCollection->getData();
            $id =  $addonCollection->getFirstItem()->getId();
            $this->liveratesLiveratesaddon->load($id)->setStatus('0')->save();
            $this->saveConfigValue(self::LIVERATES_ADDON_ACTIVE, '0', $storeScope, $storeId);
            $this->typeListInterface->cleanType('config');
        }
    }

    /**
     * Remove Add On Connection
     *
     * @return string
     */
    public function reactiveAddonconnection()
    {
        $this->typeListInterface->cleanType('config');
        $storeData = $this->getStoreDetails();
        $storeId = $storeData['storeId'];
        $storeScope = $storeData['storeScope'];
        
        $addonCollection = $this->addoncollection->create();
        $addonCollection->addFieldToSelect('*')
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('instance_id', self::ADDON_INSTANCE_ID);
        $addonCount = $addonCollection->getSize();
        if ($addonCollection->getFirstItem()->getId()) {
            $data =  $addonCollection->getData();
            $id =  $addonCollection->getFirstItem()->getId();
            $this->liveratesLiveratesaddon->load($id)->setStatus('1')->save();
            $this->saveConfigValue(self::LIVERATES_ADDON_ACTIVE, '1', $storeScope, $storeId);
            $this->typeListInterface->cleanType('config');
        }
    }

    /**
     * Re connect Add On Connection
     *
     * @param string $apiKey The API key for the addon connection.
     * @param string $apiSecret The API secret for the addon connection.
     * @return void
     */
    public function setReConnectAddonSession($apiKey, $apiSecret)
    {

        $this->typeListInterface->cleanType('config');

        $storeData = $this->getStoreDetails();
        $storeId = $storeData['storeId'];

        $data = $this->liveratesLiveratesaddon->getCollection()
        ->addFieldToFilter('instance_id', self::ADDON_INSTANCE_ID)
        ->addFieldToFilter('store_id', $storeId);

        if ($data->getFirstItem()->getId()) {

            $addondata = [
                'api_key'=> $apiKey,
                'api_secret'=> $apiSecret,
                'email'=> $this->getAdminEmail(),
                'instance_id'=> self::ADDON_INSTANCE_ID,
                'connection_id'=> "",
                'store_id'=> $storeId,
                'status'=> '0'
            ];

            $currentTimestamp = $this->dateTime->gmtTimestamp();

            foreach ($data as $key => $item) {
                $item->setApiKey($apiKey);
                $item->setApiSecret($apiSecret);
                $item->setEmail($this->getAdminEmail());
                $item->setInstanceId(self::ADDON_INSTANCE_ID);
                $item->setConnectionId('');
                $item->setCreatedAt($currentTimestamp);
                $item->setStatus('0');
                $item->save();
            }
        }

        $this->typeListInterface->cleanType('config');
        $this->logInfo("Live rate re - connected successfully");
        $this->logInfo(json_encode($addondata));
        
        return [
            'response' => $addondata
        ];
    }

    /**
     * Remove Add On Connection
     *
     * @return string
     */
    public function deleteAddonconnection()
    {
        $this->typeListInterface->cleanType('config');

        $storeData = $this->getStoreDetails();
        $storeId = $storeData['storeId'];
        $storeScope = $storeData['storeScope'];
        
        $addonCollection = $this->addoncollection->create();
        $addonCollection->addFieldToSelect('*')
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('instance_id', self::ADDON_INSTANCE_ID);

        $addonCount = $addonCollection->getSize();

        if ($addonCollection->getFirstItem()->getId()) {
            $data =  $addonCollection->getData();
            $id =  $addonCollection->getFirstItem()->getId();
            $this->liveratesLiveratesaddon->load($id)->delete();
            $this->saveConfigValue(self::LIVERATES_ADDON_ACTIVE, '0', $storeScope, $storeId);
            $this->typeListInterface->cleanType('config');

        }
    }

    /**
     * Retrieve connection data based on store scope and store ID.
     *
     * @param string $storeScope The store scope.
     * @param int|null $storeId The store ID.
     * @return array An array containing connection data.
     */
    public function getConnectionData($storeScope, $storeId)
    {
        $this->typeListInterface->cleanType('config');
        $addonCollection = $this->addoncollection->create();
        $addonCollection->addFieldToSelect('*')
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('instance_id', self::ADDON_INSTANCE_ID)
            ->addFieldToFilter('status', '1');
        $addonCount = $addonCollection->getSize();

        if ($addonCount > 0) {
            foreach ($addonCollection->getData() as $key => $value) {
                return $value;
            }
        } else {
            return false;
        }
    }

    /**
     * Get store and table data
     *
     * @return  int
     */
    public function getAdonconnect()
    {
        $storeData = $this->getStoreDetails();
        $storeId = $storeData['storeId'];
        $storeScope = $storeData['storeScope'];

        $addonCollection = $this->addoncollection->create();
        $addonCollection->addFieldToSelect('*')
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('instance_id', self::ADDON_INSTANCE_ID);
        $addonCount = $addonCollection->getSize();

        $data = [];
        if ($addonCount > 0) {
            foreach ($addonCollection as $key => $value) {

                $created_at = $value['created_at'];
                $connection_id = $value['connection_id'];
                $createdAtDateTime = new \DateTime($created_at);
                $currentDateTime = new \DateTime($this->dateTime->gmtDate());
                $interval = $currentDateTime->diff($createdAtDateTime);
                $differenceInMinutes = $interval->i;

                if ($value['connection_id'] != "" && $value['status'] == '1') {
                    $connectionStatus = 'connected';
                } elseif ($value['connection_id'] == "" && $value['status'] == '0' && $differenceInMinutes < 4) {
                    $connectionStatus = 'pending';
                } elseif ($value['connection_id'] != "" && $value['status'] == '0') {
                    $connectionStatus = 'reactive';
                } elseif ($connection_id == "" && $differenceInMinutes > 4) {
                    $connectionStatus = 'session_expired';
                } else {
                    $connectionStatus = 'pending';
                }

                return $connectionStatus;
                
            }
        } else {
            return $connectionStatus = 'empty';
        }
    }

    /**
     * Generates a JWT token.
     *
     * @param string $apiKey The API key for integration
     * @param string $apiSecret The API secret for integration
     * @return string The generated JWT token
     */
    public function createJwtToken($apiKey, $apiSecret)
    {
        $payload = [
            'iss' => $apiKey,
            'iat' => strtotime('now'),
            'exp' => strtotime('+4 minutes'),
            'aud' => 'itembase',
            'sub' => '',
        ];
        $token   = JWT::encode($payload, $apiSecret, 'HS256');
        $this->logInfo(print_r(" \n JWT token => ".$token, true));// @codingStandardsIgnoreLine
        return (!empty($token)) ? $token : '';
    }

    /**
     * Retrieve the store manager instance.
     *
     * @return StoreManagerInterface The store manager instance.
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * Get Core Connection
     *
     * @return  int
     */
    public function getCoreConnection()
    {
        $storeData = $this->getStoreDetails();
        $storeId = $storeData['storeId'];
        $storeScope = $storeData['storeScope'];
        return $this->scopeConfigInterface->getValue(CoreHelper::CONNECT_STATUS, $storeScope, $storeId);// @codingStandardsIgnoreLine
    }
}
