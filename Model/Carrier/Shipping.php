<?php
/**
 * Shipping file
 *
 * @category   UPS
 * @package    UPS_Liverates
 * @subpackage Model
 * @link       https://www.ups.com
 * @since      1.0.0
 * @author UPS eCommerce Shipping Liverates
 */
namespace UPS\Liverates\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use UPS\Liverates\Helper\Data as DataHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Directory\Model\RegionFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Cart;

/**
 * Class Shipping
 * The class is use for the set shipping method.
 */
class Shipping extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'upsaddonshippingliverates';

    /**
     * @var ResultFactory
     */
    protected $rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $rateMethodFactory;

    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfigInterface;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $regionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productloader;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * Constructor.
     *
     * @param ScopeConfigInterface              $scopeConfig            Scope Config Interface
     * @param ErrorFactory                      $rateErrorFactory       Rate Error Factory
     * @param \Psr\Log\LoggerInterface          $logger                 Logger Interface
     * @param ResultFactory                     $rateResultFactory      Rate Result Factory
     * @param MethodFactory                     $rateMethodFactory      Rate Method Factory
     * @param DataHelper                        $dataHelper             Data Helper
     * @param ScopeConfigInterface              $scopeConfigInterface   Scope Config Interface
     * @param StoreManagerInterface             $storeManager           Store Manager Interface
     * @param Curl                              $curl                   Curl
     * @param RegionFactory                     $regionFactory         regionFactory
     * @param ProductFactory                    $productloader         Product factory
     * @param Cart                              $cart                   Cart Data
     * @param array                             $data                   Additional Data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        DataHelper $dataHelper,
        ScopeConfigInterface $scopeConfigInterface,
        StoreManagerInterface $storeManager,
        Curl $curl,
        RegionFactory $regionFactory,
        ProductFactory $productloader,
        Cart $cart,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->dataHelper = $dataHelper;
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->storeManager = $storeManager;
        $this->curl = $curl;
        $this->regionFactory = $regionFactory;
        $this->productloader = $productloader;
        $this->cart = $cart;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Get allowed methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * Get Shipping Price
     *
     * @return float
     */
    private function getShippingPrice()
    {
        $configPrice = $this->getConfigData('price');
        $shippingPrice = $this->getFinalPriceWithHandlingFee($configPrice);
        return $shippingPrice;
    }

    /**
     * The function use for collect rate
     *
     * @param RateRequest $request The rate request object containing shipping details.
     * @return RateResult|bool A rate result object containing shipping rates or false if rates cannot be calculated.
     */
    public function collectRates(RateRequest $request)// @codingStandardsIgnoreLine
    {
        try {
            $result = $this->rateResultFactory->create();
            $_quote = $this->cart->getQuote();
            $this->dataHelper->logInfo("=============== UPS Live rate API ================\n");

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
            $isAddonActive = $this->scopeConfigInterface->getValue(DataHelper::LIVERATES_ADDON_ACTIVE, $storeScope, $this->getStoreId()); // @codingStandardsIgnoreLine

            $connetcionsData = $this->dataHelper->getConnectionData($storeScope, $this->getStoreId());
            if ($connetcionsData == false) {
                return false;
            }

            // checking core connection
            if ($this->dataHelper->getCoreConnection() != '1') {
                $this->dataHelper->logError("UPS Core is not connected");
                return false;
            }

            $api_key = $connetcionsData['api_key'];
            $api_secret = $connetcionsData['api_secret'];
            $isAddonActive = $connetcionsData['status'];

            if (class_exists('Firebase\JWT\JWT')) {// @codingStandardsIgnoreLine
                $jwtBearerToken = $this->dataHelper->createJwtToken($api_key, $api_secret);
            } else {
                $this->dataHelper->logError("class:Firebase\JWT\JWT does not exist");
                return false;
            }

            $instanceId = DataHelper::ADDON_INSTANCE_ID;
            $IbEndpointApiUrl = DataHelper::END_POINT_URL;
            $connectionId  = $connetcionsData['connection_id'];

            if ($isAddonActive != '1') {
                return false;
            }

            if (!$this->getConfigFlag('active')) {
                return false;
            }

            $storeCurrecy = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
            $weightUnit = $this->scopeConfigInterface->getValue('general/locale/weight_unit', $storeScope, $this->getStoreId());// @codingStandardsIgnoreLine
            $endpointUrl = $IbEndpointApiUrl."/connectivity/instances/".$instanceId."/connections/".$connectionId."/shipping/api/v2/rate?testMode=false";// @codingStandardsIgnoreLine

            // from address
            $fromName = $this->scopeConfigInterface->getValue('general/store_information/name');
            $fromCity = $this->scopeConfigInterface->getValue('general/store_information/city');
            $fromCountry = $this->scopeConfigInterface->getValue('general/store_information/country_id');
            $fromRegion = $this->scopeConfigInterface->getValue('general/store_information/region_id');
            $fromStreet1 = $this->scopeConfigInterface->getValue('general/store_information/street_line1');
            $fromStreet2 = $this->scopeConfigInterface->getValue('general/store_information/street_line2');
            $fromPostcode = $this->scopeConfigInterface->getValue('general/store_information/postcode');
            $fromPhone = $this->scopeConfigInterface->getValue('general/store_information/phone');

            $shipperRegionCode = '';
            if (is_numeric($fromRegion)) {
                $shipperRegion = $this->regionFactory->create()->load($fromRegion);
                $shipperRegionCode = $shipperRegion->getCode();
            }
            // from address end

            // to address start
            $destCountryId = $request->getDestCountryId();
            $destCountry = $request->getDestCountry();
            $destRegion = $request->getDestRegionId();
            $destRegionCode = $request->getDestRegionCode();
            $destFullStreet = $request->getDestStreet();
            $destStreet = "";
            $destSuburb = "";
            $destCity = $request->getDestCity();
            $destPostcode = $request->getDestPostcode();
            if ($destFullStreet != null && $destFullStreet != "") {
                $destFullStreetArray = explode("\n", $destFullStreet);
                $count = count($destFullStreetArray);
                if ($count > 0 && $destFullStreetArray[0] !== false) {
                    $destStreet = $destFullStreetArray[0];
                }
                if ($count > 1 && $destFullStreetArray[1] !== false) {
                    $destSuburb = $destFullStreetArray[1];
                }
            }
            // to address end
            $totalWeight = 0;
            $numOfPieces = 0;
            $productitemsJson = '';
            $items = $_quote->getAllVisibleItems();
            $totalItems = count($items);
            foreach ($items as $key => $item) {
                
                $product = $item->getProduct();
                $productTypeId = $product->getTypeId();
                if ($productTypeId !== \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL && $productTypeId !== \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {// @codingStandardsIgnoreLine
                
                    $weight = $product->getWeight();
                    $name = str_replace('"', '\\"', $item->getName());
                    $sku = str_replace('"', '\\"', $item->getSku());
                    $weight = (float)$item->getWeight();
                    $price = (float)$item->getPrice();
                    $quantity = (int)$item->getQty();
                    $totalWeight = $totalWeight+($weight*$quantity);
                    $numOfPieces = $numOfPieces+$quantity;

                    if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {// @codingStandardsIgnoreLine
                        
                        $productId = $item->getProductId();
                        $_product = $this->productloader->create()->loadByAttribute('sku', $item->getSku());
                        $name = str_replace('"', '\\"', $_product->getName());
                    }

                    $hsCode = '';
                    if ($product->getData('hsCode') != "") {
                        $hsCode = $product->getData('hsCode');
                        $productitemsJson = $productitemsJson . '{
                            "description": "' . $name . '",
                            "quantity": "'. $quantity . '",
                            "weight": "'.$weight.'",
                            "value": "'.$price.'",
                            "hsCode": "'.$product->getData('hsCode').'",
                            "originCountry": "",
                            "sku": "' . $sku . '",
                            "currency": "'.$storeCurrecy.'"
                        }';
                    } else {
                        $productitemsJson = $productitemsJson . '{
                            "description": "' . $name . '",
                            "quantity": "'. $quantity . '",
                            "weight": "'.$weight.'",
                            "value": "'.$price.'",
                            "originCountry": "",
                            "sku": "' . $sku . '",
                            "currency": "'.$storeCurrecy.'"
                        }';
                    }

                    if ($key < $totalItems - 1) {
                        $productitemsJson .= ',';
                    }
                }

            }
            
            $quoteId = $_quote->getId();
            $numOfPieces = (int)$_quote->getItemsQty();
            $subTotal = (float)$_quote->getSubtotal();
            $grandTotal = (float)$_quote->getGrandTotal();
            $firstName = '';
            $lastName = '';
            if ($_quote->getShippingAddress()->getFirstname()) {
                $firstName = $_quote->getShippingAddress()->getFirstname();
            }
            if ($_quote->getShippingAddress()->getLastname()) {
                $lastName = $_quote->getShippingAddress()->getLastname();
            }
            $sendtoName = $firstName.' '.$lastName;

            if ( $firstName == "" && $lastName == "" || $destCity == "" || $destPostcode == "" || $destCountryId == "" ) {
                return false;
            }
            
            $postData = '
            {
                "shipperReference": "",
                "transactionId": "",
                "transactionSrc": "",
                "shipper": {
                    "name": "'.$fromName.'",
                    "shipperNumber": null,
                    "address": {
                        "city": "'.$fromCity.'",
                        "state": "'.$shipperRegionCode.'",
                        "zip": "'.$fromPostcode.'",
                        "country": "'.$fromCountry.'",
                        "addressLine1": "'.$fromStreet1.'",
                        "addressLine2": "'.$fromStreet2.'"
                    }
                },
                "sendFrom": {
                    "name": "'.$fromName.'",
                    "address": {
                        "city": "'.$fromCity.'",
                        "state": "'.$shipperRegionCode.'",
                        "zip": "'.$fromPostcode.'",
                        "country": "'.$fromCountry.'",
                        "addressLine1": "'.$fromStreet1.'",
                        "addressLine2": "'.$fromStreet2.'"
                    }
                },
                "sendTo": {
                    "name": "'.$sendtoName.'",
                    "idNumber": null,
                    "address": {
                        "city": "'.$destCity.'",
                        "state": "'.$destRegionCode.'",
                        "zip": "'.$destPostcode.'",
                        "country": "'.$destCountryId.'",
                        "addressLine1": "'.$destStreet.'",
                        "addressLine2": "'.$destSuburb.'"
                    }
                },
                "shipmentChargeType": "",
                "serviceCode": null,
                "serviceDescription": null,
                "numOfPieces": "'.$numOfPieces.'",
                "weight": "'.$totalWeight.'",
                "weightUnit": "'.$weightUnit.'",
                "totalPrice": '.$subTotal.',
                "currency": "'.$storeCurrecy.'",
                "customsInfo": {
                    "customsCertify": false,
                    "customsSigner": null,
                    "contentsType": null,
                    "contentsExplanation": null,
                    "restrictionComments": null,
                    "restrictionType": null,
                    "eelPfc": null,
                    "declaration": null,
                    "nonDeliveryOption": null,
                    "customsItems": [ '.$productitemsJson.' ]
                }
            }';

            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => '*/*',
                'Authorization' => 'Bearer ' . $jwtBearerToken
            ];

            $this->dataHelper->logInfo(print_r("request Data => ".$postData, true));// @codingStandardsIgnoreLine

            $this->curl->setHeaders($headers);
            $this->curl->post($endpointUrl, $postData);

            $response = $this->curl->getBody();

            $this->dataHelper->logInfo(print_r(" Response Data => ".$response, true));// @codingStandardsIgnoreLine
    
            $this->dataHelper->logInfo(" Live Rate API Running..!");

            if ($response !== null) {

                $json_obj = json_decode($response);

                if ($json_obj !== null && isset($json_obj->rates)) {
                    
                    $rates_obj = $json_obj->rates;
                    $rates_count = count($rates_obj);
                    
                    if ($rates_count > 0) {
                        $this->dataHelper->logInfo("Live rates API fetch rates");
                    
                        foreach ($rates_obj as $rate) {
                            if (is_object($rate)) {

                                $carierTitle = $this->getConfigData('title');
                                if ($rate->deliveryDate != null) {
                                    $deliveryText = __("Estimated Delivery Date:");
                                    $deliveryDate = $rate->deliveryDate;
                                    $carierTitle = $carierTitle." &nbsp;&nbsp;&nbsp;&nbsp;\t <strong>".$deliveryText."</strong> ".$deliveryDate;// @codingStandardsIgnoreLine
                                }

                                $method = $this->rateMethodFactory->create();
                                $method->setCarrier($this->_code);
                                $method->setCarrierTitle($carierTitle);
                                $method->setMethod($rate->serviceCode);
                                $method->setMethodTitle($rate->serviceDescription);
                                $method->setPrice($rate->totalCharge);
                                $method->setCost(0);
                                $result->append($method);
                            }
                        }
                    } else {
                        $this->dataHelper->logInfo("Live Rates not found for selected address");
                        return false;
                    }
                } else {
                    $this->dataHelper->logInfo("Live rate response object rates not found");
                    return false;
                }
            } else {
                $this->dataHelper->logInfo("Live rate response null");
                return false;
            }
        } catch (\Exception $e) {
            $this->dataHelper->logError($e->getMessage());
            return false;
        }
        return $result;
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
}
