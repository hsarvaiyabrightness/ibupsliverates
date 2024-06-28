<?php
/**
 * LiveratesAddon file
 *
 * @category   UPS
 * @package    UPS_Liverates
 * @subpackage Model
 * @link       https://www.ups.com
 * @since      1.0.0
 * @author UPS eCommerce Shipping Liverates
 */
namespace UPS\Liverates\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class LiveratesAddon
 *
 * The liverates table model
 */
class LiveratesAddon extends AbstractModel
{

    /**
     * Initialize resource model.
     *
     * This method is called upon object initialization.
     * It initializes the resource model for the current class.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\LiveratesAddon::class);
    }
}
