<?php
/**
 * Collection file
 *
 * @category   UPS
 * @package    UPS_Liverates
 * @subpackage Model
 * @link       https://www.ups.com
 * @since      1.0.0
 * @author UPS eCommerce Shipping Liverates
 */
namespace UPS\Liverates\Model\ResourceModel\LiveratesAddon;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * The collection of the ups_liverates_addon
 */
class Collection extends AbstractCollection
{

    /**
     * ID field name.
     *
     * Specifies the ID field name for the model.
     *
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Initialize resource model.
     *
     * This method is called upon object initialization.
     * It initializes the resource model and defines the model class and its resource model class.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \UPS\Liverates\Model\LiveratesAddon::class,
            \UPS\Liverates\Model\ResourceModel\LiveratesAddon::class
        );
    }
}
