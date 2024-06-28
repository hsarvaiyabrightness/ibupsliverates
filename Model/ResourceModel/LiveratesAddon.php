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
namespace UPS\Liverates\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class LiveratesAddon
 *
 * The resource model for the ups_liverates_addon
 */
class LiveratesAddon extends AbstractDb
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
        $this->_init('ups_liverates_addon', 'id');
    }
}
