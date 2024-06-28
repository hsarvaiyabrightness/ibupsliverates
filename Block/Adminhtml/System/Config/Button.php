<?php
/**
 * Button file
 *
 * @category   UPS
 * @package    UPS_Liverates
 * @subpackage Block
 * @link       https://www.ups.com
 * @since      1.0.0
 * @author UPS eCommerce Shipping Liverates
 */
namespace UPS\Liverates\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use UPS\Liverates\Helper\Data;
use Magento\Backend\Block\Template\Context;

/**
 * Class Button.
 *
 * This button action data
 */
class Button extends Field
{

    /**
     * Constant for Addon activation status.
     *
     * This constant represents the activation status of Addon.
     *
     * @var string
     */
    public const LIVERATES_ADDON_ACTIVE = "";
    
    /**
     * @var string
     */
    protected $_template = 'system/config/button.phtml';

    /**
     * @var string
     */
    protected $helperdata;

    /**
     * Constructor.
     *
     * @param Data    $helperdata Helper Data
     * @param Context $context    Context
     * @param array   $data       Additional data
     */
    public function __construct(
        Data $helperdata,
        Context $context,
        array $data = []
    ) {
        $this->helperdata = $helperdata;
        parent::__construct($context, $data);
    }

    /**
     * Unset scope
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope();

        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $isactive = $this->helperdata->getAdonconnect();
        $this->addData([
            'button_label' => $originalData['button_label'],
            'button_url'   => $this->getUrl($originalData['button_url'], ['_current' => true]),
            'html_id'      => $element->getHtmlId(),
            'active_status'=> $isactive,
        ]);
        return $this->_toHtml();
    }
}
