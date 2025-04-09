<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Block\Adminhtml\Form\Field;

use Magento\Framework\Serialize\Serializer\Json as SerializerJson;
use Magento\Store\Model\StoreManagerInterface;
use MageWorx\GiftCards\Api\Data\GiftCardsInterface;

class StoreIdForEmail
{
    /**
     * @var SerializerJson
     */
    protected $serializer;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * StoreIdForEmail constructor.
     *
     * @param SerializerJson $serializer
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(SerializerJson $serializer, StoreManagerInterface $storeManager)
    {
        $this->serializer   = $serializer;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $htmlIdPrefix
     * @param int $fieldValue
     * @return string
     */
    public function getAfterElementHtml($htmlIdPrefix, $fieldValue = 0)
    {
        $storeIdFieldId         = $htmlIdPrefix . GiftCardsInterface::STORE_ID;
        $storeIdForEmailFieldId = $htmlIdPrefix . GiftCardsInterface::STORE_ID_FOR_EMAIL;
        $websiteStores          = $this->serializer->serialize($this->getWebsiteStores());

        return '<script>require(["jquery", "prototype"], function(jQuery) {' . "\n"
            . "var websiteStores = {$websiteStores};\n"
            . "var fieldValue = {$fieldValue};\n"
            . "Event.observe('{$storeIdFieldId}', 'change', setCurrentStores);"
            . "setCurrentStores();"
            . 'function setCurrentStores() {'
            . "var storeIdSel         = $('{$storeIdFieldId}');"
            . "var storeIdForEmailSel = $('{$storeIdForEmailFieldId}');"
            . 'storeIdForEmailSel.innerHTML = "";'
            . "var storeIds = jQuery('#{$storeIdFieldId}').val();"
            . 'if (storeIds) {
                for (websiteId in websiteStores) {
                    websiteIncluded = false;
                    website         = websiteStores[websiteId];
                    groups          = website["groups"];
                  
                    for (groupId in groups) {
                        groupIncluded = false;
                        group         = groups[groupId];
                        stores        = group["stores"];
                        
                        for (i=0; i < stores.length; i++) {
                            if (storeIds.includes(stores[i]["id"] + "") || storeIds.includes("0")) {
                                if (!websiteIncluded) {
                                    wOptionGroup       = document.createElement("OPTGROUP");
                                    wOptionGroup.label = website["name"];
                                    storeIdForEmailSel.appendChild(wOptionGroup);
                                }
                                websiteIncluded = true;
                                
                                if (!groupIncluded) {
                                    optionGroup       = document.createElement("OPTGROUP");
                                    optionGroup.label = "\u00A0\u00A0" + group["name"];
                                    storeIdForEmailSel.appendChild(optionGroup);
                                }
                                groupIncluded = true;
                                
                                var option = document.createElement("option");
                                option.appendChild(document.createTextNode(stores[i]["name"]));
                                option.setAttribute("value", stores[i]["id"]);
                                
                                if (fieldValue === stores[i]["id"]) {
                                    option.setAttribute("selected", "selected");
                                }
                                
                                optionGroup.appendChild(option);
                            }
                        }
                    }
                }
            } else {
              var option = document.createElement("option");
              option.appendChild(document.createTextNode("' .
            __(
                '-- Please Select a Store Views --'
            ) . '"));
              storeIdForEmailSel.appendChild(option);
            }
        }
        });</script>';
    }

    /**
     * @return array
     */
    protected function getWebsiteStores()
    {
        $websiteStores = [];

        foreach ($this->storeManager->getWebsites() as $websiteId => $website) {
            $websiteStores[$websiteId] = [
                'name'   => $website->getName(),
                'groups' => []
            ];

            foreach ($website->getGroups() as $groupId => $group) {
                $websiteStores[$websiteId]['groups'][$groupId] = ['name' => $group->getName()];

                foreach ($group->getStores() as $storeId => $store) {
                    $websiteStores[$websiteId]['groups'][$groupId]['stores'][] = [
                        'id'   => $storeId,
                        'name' => $store->getName(),
                    ];
                }
            }
        }

        return $websiteStores;
    }
}
