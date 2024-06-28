<?php
/**
 * ConnectionInterface file
 *
 * @category   UPS
 * @package    UPS_Liverates
 * @subpackage Api
 * @link       https://www.ups.com
 * @since      1.0.0
 * @author UPS eCommerce Shipping Liverates
 */
namespace UPS\Liverates\Api;

/**
 * Class ConnectionInterface.
 *
 * The module api to set connection id and instance id 
 */
interface ConnectionInterface
{
    /**
     * Removes a connection based on instance ID, store ID, and connection ID.
     *
     * @param string $store_id The connection ID to remove.
     * @return array An array indicating the status of the operation.
     */
    public function getRemoveConnection($store_id);

    /**
     * Retrieve something by instance ID.
     *
     * @param string $store_id The instance ID to search for.
     * @return string The retrieved something.
     */
    public function getByInstanceId($store_id);

    /**
     * Set connection ID based on instance ID and store ID.
     *
     * @param int $store_id The store ID.
     * @param string $connection_id The connection id.
     * @return array The connection data if successful, otherwise an array with success set to false and a message.
     */
    public function updateConnection($store_id, $connection_id);

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
    public function createConnection($instance_id, $connection_id, $store_id, $email, $api_key, $api_secret, $status);
}
