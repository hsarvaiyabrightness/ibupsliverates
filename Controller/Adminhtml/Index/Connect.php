<?php
/**
 * Connect file
 *
 * @category   UPS
 * @package    UPS_Liverates
 * @subpackage Controller
 * @link       https://www.ups.com
 * @since      1.0.0
 * @author UPS eCommerce Shipping Liverates
 */
namespace UPS\Liverates\Controller\Adminhtml\Index;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Area;
use Magento\Store\Model\Store;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use UPS\Liverates\Helper\Data;
use UPS\Dashboard\Helper\Data as CoreData;

/**
 * Class Connect
 * Main class that used for the connection UPS dashboard
 */
class Connect extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var Data
     */
    protected $data;

    /**
     * Constructor.
     *
     * @param JsonFactory      $resultJsonFactory JSON result factory
     * @param RequestInterface $request           Request interface
     * @param ConfigInterface  $config            Configuration interface
     * @param Data             $data              Data
     * @param Context          $context           Context
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        ConfigInterface $config,
        Data $data,
        Context $context
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->config = $config;
        $this->data = $data;
        parent::__construct($context);
    }

    /**
     * Main function to perform the logic to connection UPS dashboard
     *
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {

        try {

            $conn_status = $this->request->getParam('conn_status');

            if ($conn_status == '1') {
                
                $type = $this->request->getParam('type');
                
                if ($type == 'upsliverates_deactivate') {
                    $this->data->deactiveAddonconnection();
                    $data = [
                        'status'=> true,
                        'type' => 'upsliverates_deactivated',
                        'data'=> 'UPS Core Deactivated',
                        'error'=> false,
                        'message' => __('Successfully Disconnected')
                    ];
                }

                if ($type == 'upsliverates_delete') {
                    $this->data->deleteAddonconnection();
                    $data = [
                        'status'=> true,
                        'type' => 'upsliverates_delete',
                        'data'=> '',
                        'error'=> false,
                        'message' => __('Live Rate Addon Removed Successfully')
                    ];
                }

                if ($type == 'upsliverates_reactive') {
                    $this->data->reactiveAddonconnection();
                    $data = [
                        'status'=> true,
                        'type' => 'upsliverates_reactive',
                        'data'=> 'Live Re-activated',
                        'error'=> false,
                        'message' => __('Connected Successfully'),
                        'redirect_url' => ''
                    ];
                }

                if ($type == 'upsliverates_reconnect') {

                    $endpoint = Data::END_POINT_URL;
                    $instance_id = Data::ADDON_INSTANCE_ID;
                    $callback_url = Data::CALLBACK_URL;
                    $apiKey = $this->data->generateJWTApiSecret();
                    $apiSecret = $this->data->generateJWTApiSecret();
                    $this->data->setReConnectAddonSession($apiKey, $apiSecret);

                    $storeData = $this->data->getStoreDetails();
                    $storeId = $storeData['storeId'];

                    $core_instance_id = CoreData::ADDON_INSTANCE_ID;
                    $unique_identifier = $this->data->getStoreManager()->getStore()->getBaseUrl();
                    $redirectUrl = $endpoint."/connectivity/instances/".$instance_id."/connections/shipping/auth/v2/oauth?apiKey=".$apiKey."&apiSecret=".$apiSecret."&callbackUrl=".$callback_url."&store_id=".$storeId."&core_instance_id=".$core_instance_id."&core_unique_identifier=".$unique_identifier; // @codingStandardsIgnoreLine
                    
                    $this->data->logInfo("auth url => ".$redirectUrl);

                    if ($this->data->getAdonconnect() == 'pending') {

                        $data = [
                            'status'=> true,
                            'type' => 'pending',
                            'data'=> 'Connecting, Please Wait',
                            'error'=> false,
                            'message' => __('Connecting to UPS Shipping Live Rate Service. Please Wait...'),
                            'redirect_url' => $redirectUrl
                        ];
                        
                    } elseif ($this->data->getAdonconnect() == 'reactive') {

                        $data = [
                            'status'=> true,
                            'type' => 'reactive',
                            'data'=> 'Re-activate Add-On',
                            'error'=> false,
                            'message' => __('UPS shipping live rate service disabled'),
                            'redirect_url' => ''
                        ];
                        
                    } elseif ($this->data->getAdonconnect() == 'connected') {
                        
                        $data = [
                            'status'=> true,
                            'type' => 'addon_success',
                            'data'=> 'UPS Core activated',
                            'error'=> false,
                            'message' => __('Connected Successfully'),
                            'redirect_url' => $redirectUrl
                        ];

                    } else {
                        $data = [
                            'status'=> true,
                            'type' => 'pending',
                            'data'=> 'Connecting, Please Wait',
                            'error'=> false,
                            'message' => __('Connecting to UPS Shipping Live Rate Service. Please Wait...'),
                            'redirect_url' => $redirectUrl
                        ];
                    }
                }

                if ($type == 'addon_connect') {

                    $endpoint = Data::END_POINT_URL;
                    $instance_id = Data::ADDON_INSTANCE_ID;
                    $callback_url = Data::CALLBACK_URL;
                    $apiKey = $this->data->generateJWTApiSecret();
                    $apiSecret = $this->data->generateJWTApiSecret();
                    $this->data->saveConnectionProcess($apiKey, $apiSecret);

                    $storeData = $this->data->getStoreDetails();
                    $storeId = $storeData['storeId'];

                    $core_instance_id = CoreData::ADDON_INSTANCE_ID;
                    $unique_identifier = $this->data->getStoreManager()->getStore()->getBaseUrl();

                    $redirectUrl = $endpoint."/connectivity/instances/".$instance_id."/connections/shipping/auth/v2/oauth?apiKey=".$apiKey."&apiSecret=".$apiSecret."&callbackUrl=".$callback_url."&store_id=".$storeId."&core_instance_id=".$core_instance_id."&core_unique_identifier=".$unique_identifier; // @codingStandardsIgnoreLine
                    
                    if ($this->data->getAdonconnect() == 'pending') {

                        $data = [
                            'status'=> true,
                            'type' => 'pending',
                            'data'=> 'Connecting, Please Wait',
                            'error'=> false,
                            'message' => __('Connecting to UPS Shipping Live Rate Service. Please Wait...'),
                            'redirect_url' => $redirectUrl
                        ];
                        
                    } elseif ($this->data->getAdonconnect() == 'reactive') {

                        $data = [
                            'status'=> true,
                            'type' => 'reactive',
                            'data'=> 'Re-activate Add-On',
                            'error'=> false,
                            'message' => __('UPS shipping live rate service disabled'),
                            'redirect_url' => ''
                        ];
                        
                    } elseif ($this->data->getAdonconnect() == 'connected') {
                        
                        $data = [
                            'status'=> true,
                            'type' => 'addon_success',
                            'data'=> 'UPS Core activated',
                            'error'=> false,
                            'message' => __('Connected Successfully'),
                            'redirect_url' => $redirectUrl
                        ];

                    } else {
                        $data = [
                            'status'=> true,
                            'type' => 'pending',
                            'data'=> 'Connecting, Please Wait',
                            'error'=> false,
                            'message' => __('Connecting to UPS Shipping Live Rate Service. Please Wait...'),
                            'redirect_url' => $redirectUrl
                        ];
                    }
                }
            } else {
                $data = [
                    'status'=> false,
                    'type' => 'dashboardactive',
                    'data'=> 'UPS Core Not activated',
                    'error'=> false,
                    'message' => __('Something went wrong..!')
                ];
            }

        } catch (\Exception $e) {
            $data = [
               'status'=> false,
               'data' => $e->getMessage(),
               'error'=> true,
               'message' => __('Something went wrong..!')
            ];
        }

        $result = $this->resultJsonFactory->create();
        return $result->setData($data);
    }
}
