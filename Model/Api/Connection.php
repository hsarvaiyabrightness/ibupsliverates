<?php
/**
 * Connection file
 *
 * @category   UPS
 * @package    UPS_Liverates
 * @subpackage Api
 * @link       https://www.ups.com
 * @since      1.0.0
 * @author UPS eCommerce Shipping Liverates
 */
namespace UPS\Liverates\Model\Api;

use UPS\Liverates\Api\ConnectionInterface;
use UPS\Liverates\Model\LiveratesAddon;

/**
 * Class Connection
 * The connettion API for the save connection in table
 */
class Connection implements ConnectionInterface
{

    /**
     * @var LiveratesAddon
     */
    private $liveratesFactory;

    /**
     * Response
     */
    protected $response = ['success' => false];// @codingStandardsIgnoreLine

    /**
     * Constructor.
     *
     * @param LiveratesAddon $liveratesFactory LiveRate Addon
     */
    public function __construct(
        LiveratesAddon $liveratesFactory
    ) {
        $this->liveratesFactory = $liveratesFactory;
    }

    /**
     * Removes a connection based on instance ID, store ID, and connection ID.
     *
     * @param string $store_id The connection ID to remove.
     * @return array An array indicating the status of the operation.
     */
    public function getRemoveConnection($store_id)
    {
        try {
            if ($store_id != "") {
                $data = $this->liveratesFactory->getCollection()->addFieldToFilter('store_id', $store_id);
                if ($data->getFirstItem()->getId()) {
                    if ($data->getFirstItem()->getConnectionId() != "") {
                        $id = $data->getFirstItem()->getId();
                        $this->liveratesFactory->load($id)->delete();
                        $response = [
                            'status'=> true,
                            'data'=> "Successfully delete Connection",
                            'error'=> false
                        ];

                    } else {
                        $response = [
                            'status'=> true,
                            'data'=> "Already Removed Connection",
                            'error'=> false
                        ];
                    }
                } else {
                    $response = [
                        'status'=> true,
                        'data'=> "No records found to remove connection",
                        'error'=> false
                    ];
                }

            } else {
                $response = [
                    'status'=> true,
                    'data'=> "The Connection Id is missing in passed data.",
                    'error'=> false
                ];
            }
            return json_encode($response);
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
            return json_encode($response);
        }
        $response =  ['success' => false, 'message' => 'Some parameters are missing.'];
        return json_encode($response);
    }

    /**
     * Retrieve something by instance ID.
     *
     * @param string $store_id The instance ID to search for.
     * @return string The retrieved something.
     */
    public function getByInstanceId($store_id)
    {
        try {
            if ($store_id != "") {
                $data = $this->liveratesFactory->getCollection()
                    ->addFieldToFilter('store_id', $store_id)
                    ->addFieldToSelect(['email', 'instance_id', 'connection_id', 'store_id', 'api_key', 'api_secret', 'status']);// @codingStandardsIgnoreLine
                if (count($data) > 0) {
                    $response = $data->getData();
                } else {
                    $response = [
                        'status'=> true,
                        'data'=> "No Records Found",
                        'error'=> false
                    ];
                }
            } else {
                $response = [
                    'status'=> true,
                    'data'=> "instance_id is missing in passed data",
                    'error'=> false
                ];
            }
        } catch (\Exception $e) {
            $response =  ['success' => false, 'message' => $e->getMessage()];
        }
        return json_encode($response);
    }

    /**
     * Set connection ID based on instance ID and store ID.
     *
     * @param int $store_id The store ID.
     * @param string $connection_id The connection id.
     * @return array The connection data if successful, otherwise an array with success set to false and a message.
     */
    public function updateConnection($store_id, $connection_id)
    {
        try {
            if ($store_id != "" && $connection_id != "") {
                $data = $this->liveratesFactory->getCollection()->addFieldToFilter('store_id', $store_id);

                if ($data->getFirstItem()->getId()) {
                    foreach ($data as $key => $item) {
                        if ($item->getConnectionId() != $connection_id) {
                            $item->setConnectionId($connection_id);
                            $item->setStatus('1');
                            $item->save();
                                
                            $response['status'] = true;
                            $response['data'] = "connection_id save Successfully";
                            $response['error'] = false;
                        } else {
                            $response = [
                                'status'=> true,
                                'data'=> "connection_id save already",
                                'error'=> false
                            ];
                        }
                    }
                } else {
                    $response['status'] = true;
                    $response['data'] = "Connection data not found";
                    $response['error'] = false;

                }
            } else {
                $response = ['success' => false, 'message' => 'missing some field value.'];
            }
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
        }
        return json_encode($response);
    }

    /**
     * Set connection ID based on instance ID, store ID, email, API key, and status.
     *
     * @param string $instance_id The instance ID.
     * @param string $connection_id The connection ID.
     * @param int $store_id The store ID.
     * @param string $email The email associated with the connection.
     * @param string $api_key The API key used for authentication.
     * @param string $api_secret The API secret used for authentication.
     * @param string $status The status of the connection (e.g., "active", "inactive").
     * @return array The connection data if successful, otherwise an array with success set to false and a message.
     */
    public function createConnection($instance_id, $connection_id, $store_id, $email, $api_key, $api_secret, $status)
    {
        try {
            if ($instance_id != "" && $connection_id != "" && $store_id != "" && $email != "" && $api_key !="" && $api_secret != "" && $status != "") {// @codingStandardsIgnoreLine
                $data = $this->liveratesFactory->getCollection()
                ->addFieldToFilter('instance_id', $instance_id)
                ->addFieldToFilter('connection_id', $connection_id)
                ->addFieldToFilter('email', $email)
                ->addFieldToFilter('store_id', $store_id);
                if ($data->getFirstItem()->getId()) {

                    $response = [
                        'status'=> true,
                        'data'=> "The connection_id already saved",
                        'error'=> false
                    ];
                    
                } else {
                    $saveData = [
                        'email' => $email,
                        'instance_id' => $instance_id,
                        'connection_id' => $connection_id,
                        'store_id' => $store_id,
                        'api_key' => $api_key,
                        'api_secret' => $api_secret,
                        'status' => $status,
                    ];
                    $saveLiverates = $this->liveratesFactory->setData($saveData);
                    $saveLiverates->save();
                    $response = [
                        'status'=> true,
                        'message'=> "The liverates addon connection saved Successfully",
                        'data'=> $saveData,
                        'error'=> false
                    ];
                }
                return $response;
            } else {
                return ['success' => false, 'message' => 'missing some field value.'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
