<?php
/**
 * Custom file
 *
 * @category   UPS
 * @package    UPS_Liverates
 * @subpackage Logger
 * @link       https://www.ups.com
 * @since      1.0.0
 * @author UPS eCommerce Shipping Liverates
 */
namespace UPS\Liverates\Logger\Handler;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

/**
 * Custom logger class.
 *
 * This class extends the Base logger class and defines specific configurations.
 */
class Custom extends Base
{
    /**
     * Log file name.
     *
     * Specifies the file path where logs will be stored.
     *
     * @var string
     */
    protected $fileName = '/var/log/ups_addons/ups_liverates.log';

    /**
     * Logger type.
     *
     * Specifies the logging level for this logger.
     *
     * @var int
     */
    protected $loggerType = Logger::INFO;
}
