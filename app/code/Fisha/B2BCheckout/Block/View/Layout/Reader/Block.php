<?php

namespace Fisha\B2BCheckout\Block\View\Layout\Reader;

use Magento\Framework\View\Layout;
use Magento\Framework\Data\Argument\InterpreterInterface;

class Block extends Layout\Reader\Block
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    protected $_scopeResolver;

    /**
     * Constructor
     *
     * @param Layout\ScheduledStructure\Helper $helper
     * @param Layout\Argument\Parser $argumentParser
     * @param Layout\ReaderPool $readerPool
     * @param InterpreterInterface $argumentInterpreter
     * @param string|null $scopeType
     * @param \Magento\Framework\App\Config\ScopeConfigInterface
     * @param \Magento\Framework\App\ScopeResolverInterface
     */
    public function __construct(
        Layout\ScheduledStructure\Helper $helper,
        Layout\Argument\Parser $argumentParser,
        Layout\ReaderPool $readerPool,
        InterpreterInterface $argumentInterpreter,
        $scopeType = null,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ScopeResolverInterface $scopeResolver
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_scopeResolver = $scopeResolver;
        parent::__construct($helper, $argumentParser, $readerPool, $argumentInterpreter, $scopeType);
    }

    /**
     * Schedule reference data
     *
     * @param Layout\ScheduledStructure $scheduledStructure
     * @param Layout\Element $currentElement
     * @return void
     */
    protected function scheduleReference(
        Layout\ScheduledStructure $scheduledStructure,
        Layout\Element $currentElement
    ) {
        $elementName = $currentElement->getAttribute('name');
        $elementRemove = filter_var($currentElement->getAttribute('remove'), FILTER_VALIDATE_BOOLEAN);
        if ($elementRemove) {
            $configPath = (string)$currentElement->getAttribute('ifconfig');
            if (empty($configPath)
                || $this->_scopeConfig->isSetFlag($configPath, $this->scopeType, $this->_scopeResolver->getScope())
            ) {
                $scheduledStructure->setElementToRemoveList($elementName);
            }
        } else {
            $data = $scheduledStructure->getStructureElementData($elementName, []);
            $data['attributes'] = $this->mergeBlockAttributes($data, $currentElement);
            $this->updateScheduledData($currentElement, $data);
            $this->evaluateArguments($currentElement, $data);
            $scheduledStructure->setStructureElementData($elementName, $data);
        }
    }

}
