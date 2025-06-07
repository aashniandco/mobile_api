<?php
namespace Aashni\MobileApi\Model;

use Aashni\MobileApi\Api\SolrInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Authorization\Model\UserContextInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;


class Solr implements SolrInterface
{
    protected $curl;
    protected $cartItemRepository;
    protected $quoteRepository;
    protected $userContext;
    protected $logger;
    protected $request;
    protected $resource;

    public function __construct(
        Curl $curl,
        CartItemRepositoryInterface $cartItemRepository,
        CartRepositoryInterface $quoteRepository,
        UserContextInterface $userContext,
        LoggerInterface $logger,
        RequestInterface $request,
        ResourceConnection $resource
    ) {
        $this->curl = $curl;
        $this->cartItemRepository = $cartItemRepository;
        $this->quoteRepository = $quoteRepository;
        $this->userContext = $userContext;
        $this->logger = $logger;
        $this->request = $request;
        $this->resource = $resource;
    }

   public function getShippingRate($countryId, $regionId, $weight) // No default for $weight
    {
        $this->logger->info(sprintf(
            'getShippingRate API called with countryId: %s, regionId: %s, raw weight input: %s',
            $countryId,
            $regionId,
            // Log the raw weight input for debugging, handle if it's not set for some reason
            // though Magento's WebAPI framework should ensure it's passed if defined in webapi.xml
            // and the method signature doesn't make it optional.
            var_export($weight, true) // var_export gives more detail than just casting
        ));

        // Validate that $countryId is provided and not empty
        if (empty($countryId)) {
            $this->logger->error('Country ID parameter is missing or empty.');
            return [
                'success' => false,
                'shipping_price' => null,
                'message' => 'Country ID parameter is required.'
            ];
        }

        // Validate that $regionId is provided and numeric (even if it's 0)
        if (!isset($regionId) || !is_numeric($regionId)) {
             $this->logger->error('Region ID parameter is missing or not numeric.');
            return [
                'success' => false,
                'shipping_price' => null,
                'message' => 'Region ID parameter is required and must be numeric.'
            ];
        }


        // Validate that weight is provided and is numeric
        if (!isset($weight) || !is_numeric($weight)) {
            $this->logger->error('Weight parameter is missing or not numeric.', ['raw_weight' => $weight]);
            return [
                'success' => false,
                'shipping_price' => null, // Ensure shipping_price is included even on error for consistency
                'message' => 'Weight parameter is required and must be numeric.'
            ];
        }

        // Sanitize weight: ensure it's a non-negative float
        $cartWeight = max(0.0, (double)$weight);
        $this->logger->info(sprintf('Sanitized cartWeight: %s', $cartWeight));

        try {
            $connection = $this->resource->getConnection();
            $tableName = $this->resource->getTableName('shipping_tablerate');

            // SQL to find the best matching rate
            // We look for the smallest condition_value (weight tier) that is >= our cart_weight
            $sqlTemplate = "SELECT price FROM `{$tableName}`
                            WHERE dest_country_id = :country_id
                            AND dest_region_id = :region_id
                            AND condition_name = :condition_name
                            AND condition_value >= :cart_weight
                            ORDER BY condition_value ASC
                            LIMIT 1";

            $bind = [
                'country_id' => $countryId,
                'region_id' => (int)$regionId, // Try specific region first
                'condition_name' => 'package_weight', // Standard Magento condition name for weight
                'cart_weight' => $cartWeight
            ];

            $this->logger->info('Attempting to fetch shipping rate with specific region and weight.', $bind);
            $price = $connection->fetchOne($sqlTemplate, $bind);

            // If no specific region match, try with region_id = 0 (all regions for the country)
            // but still considering the weight.
            if ($price === false && (int)$regionId !== 0) {
                $this->logger->info(
                    'No specific region match. Trying with region_id = 0 for the same country and weight.'
                );
                $bind['region_id'] = 0; // Fallback to 'all regions'
                $this->logger->info('Attempting to fetch shipping rate with region_id = 0 and weight.', $bind);
                $price = $connection->fetchOne($sqlTemplate, $bind);
            }

            if ($price === false) {
                $this->logger->info(
                    'No shipping rate found for criteria.',
                    ['countryId' => $countryId, 'regionId_attempted' => $bind['region_id'], 'cartWeight' => $cartWeight]
                );
                return [
                    'success' => true, // Operation was successful, but no rate found
                    'shipping_price' => null,
                    'message' => 'No shipping rate found for this destination and weight.'
                ];
            }

            $shippingPrice = (float)$price;
            $this->logger->info('Shipping rate found.', ['price' => $shippingPrice]);

            return [
                'success' => true,
                'shipping_price' => $shippingPrice,
                'message' => 'Shipping rate calculated successfully.' // Optional success message
            ];

        } catch (\Exception $e) {
            $this->logger->critical('Error in getShippingRate: ' . $e->getMessage(), ['exception' => $e]);
            return [
                'success' => false,
                'shipping_price' => null,
                'message' => 'An error occurred while fetching the shipping rate. Please try again later.'
            ];
        }
    }



public function getCartDetailsByCustomerId($customerId)
{
    $this->logger->info("getCartDetailsByCustomerId called with customer_id: " . $customerId);

    try {
        $connection = $this->resource->getConnection();
        $quoteTable = $this->resource->getTableName('quote');
        $customerTable = $this->resource->getTableName('customer_entity');
        $addressTable = $this->resource->getTableName('quote_address');
        $itemTable = $this->resource->getTableName('quote_item');

        $sql = "
            SELECT
                q.entity_id AS quote_id,
                q.is_active,
                ce.email AS customer_email,
                qa.weight AS total_cart_weight,
                qi.item_id,
                qi.product_id,
                qi.sku,
                qi.name AS product_name,
                qi.qty AS item_qty,
                qi.price AS item_original_price,
                qi.row_total AS item_row_total_after_discounts,
                qi.weight AS individual_item_weight,
                (qi.weight * qi.qty) AS item_row_weight
            FROM
                {$quoteTable} AS q
            JOIN
                {$customerTable} AS ce ON q.customer_id = ce.entity_id
            LEFT JOIN
                {$addressTable} AS qa ON q.entity_id = qa.quote_id AND qa.address_type = 'shipping'
            JOIN
                {$itemTable} AS qi ON q.entity_id = qi.quote_id
            WHERE
                q.customer_id = :customer_id
                AND q.is_active = 1
                AND qi.parent_item_id IS NULL
        ";

        $bind = ['customer_id' => (int)$customerId];
        $result = $connection->fetchAll($sql, $bind);

        return $result;
    } catch (\Exception $e) {
        $this->logger->error('Error in getCartDetailsByCustomerId: ' . $e->getMessage());
        throw new \Magento\Framework\Exception\LocalizedException(__('Unable to fetch cart details.'));
    }
}




public function getAllCountryCodes()
{
    try {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('directory_country');

        $sql = "SELECT country_id AS country_code FROM $table";
        $results = $connection->fetchAll($sql);

        return [
            'success' => true,
            'countries' => $results
        ];
    } catch (\Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}



    public function deleteCartItem()
    {
         $itemId = $this->request->getParam('item_id');

    if (!$itemId) {
        return ['success' => false, 'message' => 'Missing item_id'];
    }

    try {
        $customerId = $this->userContext->getUserId();
        if (!$customerId) {
            return ['success' => false, 'message' => 'User not authorized or not logged in'];
        }

        $quote = $this->quoteRepository->getActiveForCustomer($customerId);
        $item = $quote->getItemById($itemId);

        if (!$item) {
            return ['success' => false, 'message' => "Item ID $itemId not found in cart"];
        }

        $quote->removeItem($itemId)->collectTotals();
        $this->quoteRepository->save($quote);

        return ['success' => true, 'message' => "Item ID $itemId deleted successfully from cart"];
    } catch (\Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
    }



    public function getSolrData()
    {
        $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?q=*:*&fq=categories-store-1_url_path:%22designers%22&facet=true&facet.field=designer_name&facet.limit=-1";

        try {
            $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
            $this->curl->get($solrUrl);
            $response = $this->curl->getBody();
            return json_decode($response, true);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

      public function updateCartItemQty()
    {
        $itemId = $this->request->getParam('item_id');
        $qty = $this->request->getParam('qty');

        if (!$itemId || !$qty) {
            return [false, "Missing item_id or qty"];
        }

        try {
            $customerId = $this->userContext->getUserId();
            if (!$customerId) {
                return [false, "User not authorized or not logged in"];
            }

            $quote = $this->quoteRepository->getActiveForCustomer($customerId);
            $item = $quote->getItemById($itemId);

            if (!$item) {
                return [false, "Item ID $itemId not found in cart"];
            }

            $qty = (int)$qty;
            if ($qty < 1) {
                $qty = 1;
            }

            $item->setQty($qty);
            $quote->collectTotals();
            $this->quoteRepository->save($quote);

            $rowTotal = $item->getRowTotal();
            $subtotal = $quote->getSubtotal();

            return [
                true,
                "Quantity updated successfully",
                'qty' => $qty,
                'row_total' => $rowTotal,
                'subtotal' => $subtotal,
            ];
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return [false, 'Error: ' . $e->getMessage()];
        }
    } 

    public function getDesignerData(string $designerName)
    {
               $encodedDesignerName = urlencode($designerName);
               $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?q=*:*&fq=designer_name:%22{$encodedDesignerName}%22&fl=designer_name,prod_small_img,prod_thumb_img,short_desc,prod_desc,size_name,prod_sku,actual_price_1&rows=400&wt=json";
               
    
        try {
            $this->curl->setOption(CURLOPT_TIMEOUT, 60);
            $this->curl->get($solrUrl);
            $response = $this->curl->getBody();
            return json_decode($response, true);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
        echo "getDesignerData called>>";
    }
    
    public function getDesigners()
    {
    
        $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?%22%22q=*:*&fq=categories-store-1_url_path:%22designers%22%22%22&facet=true&facet.field=designer_name&facet.limit=-1";

        try {
            $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
            $this->curl->get($solrUrl);
            $response = $this->curl->getBody();
            return json_decode($response, true);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // New In Womens-clothing


 public function getNewInData()
    {
        $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(%20%20%20%20%20categories-store-1_id%3A(1372%201500%202295%202296%202297%202298%202299%202665%202666%202668%202670%202671%202672%202673%202676%202677%202681%202683%202732%202809%203038%203040%203049%203063%203067%203069%203091%203103%203105%203107%203109%203111%203121%203208%203210%203213%203215%203217%203258%203260%203303%203341%203364%203366%203765%205559%204046%204352%204353%204354%204460%204463%204476%205594)%20%20%20%20%20AND%20%20%20%20%20%20categories-store-1_name%3A(%20%20%20%20%20%20%20%20%20%22classic%20lehengas%22%20%22draped%20lehengas%22%20%22contemporary%20lehengas%22%20%22ruffle%20lehengas%22%20%22lighter%20lehengas%22%20%20%20%20%20%20%20%20%20%20%22printed%20lehengas%22%20%22floral%20lehengas%22%20%22festive%20lehengas%22%20%22bridal%20lehengas%22%20%22handwoven%20lehengas%22%20%20%20%20%20%20%20%20%20%22anarkalis%22%20%22sharara%20sets%22%20%22palazzo%20sets%22%20%22printed%20kurtas%22%20%22straight%20kurta%20sets%22%20%22dhoti%20kurtas%22%20%22kurtas%22%20%20%20%20%20%20%20%20%20%22handwoven%20kurta%20sets%22%20%22kurta%20sets%22%20%22classic%20sarees%22%20%22pre%20draped%20sarees%22%20%22saree%20gowns%22%20%22printed%20sarees%22%20%20%20%20%20%20%20%20%20%22pants%20and%20dhoti%20sarees%22%20%22handwoven%20sarees%22%20%22ruffle%20sarees%22%20%22striped%20sarees%22%20%22lehenga%20sarees%22%20%20%20%20%20%20%20%20%20%22maxi%20dresses%22%20%22midi%20dresses%22%20%22mini%20dresses%22%20%22silk%20dresses%22%20%22evening%20dresses%22%20%22day%20dresses%22%20%22floral%20dresses%22%20%20%20%20%20%20%20%20%20%22knee%20length%20dresses%22%20%22shift%20dresses%22%20%22cropped%22%20%22blouses%22%20%22shirts%22%20%22classic%20tops%22%20%22t%20shirts%22%20%22off%20the%20shoulder%22%20%20%20%20%20%20%20%20%20%22printed%22%20%22sweatshirts%22%20%22hoodies%22%20%22suits%22%20%22tracksuits%22%20%22jacket%20sets%22%20%22crop%20top%20sets%22%20%22skirt%20sets%22%20%22pant%20sets%22%20%20%20%20%20%20%20%20%20%22short%20sets%22%20%22lehenga%20skirts%22%20%22midi%22%20%22mini%22%20%22knee%20length%22%20%22embellished%22%20%22chudidars%22%20%22dhotis%22%20%22palazzos%22%20%20%20%20%20%20%20%20%20%22straight%20pants%22%20%22draped%20pants%22%20%22trousers%22%20%22feather%22%20%22tulle%22%20%22cape%22%20%22traditional%22%20%22embroidered%22%20%22cocktail%22%20%20%20%20%20%20%20%20%20%22tunic%20sets%22%20%22short%22%20%22long%22%20%22printed%22%20%22plain%22%20%22embellished%22%20%22dhoti%20kurtas%22%20%22printed%20kurtas%22%20%22contemporary%20kurtas%22%20%20%20%20%20%20%20%20%20%22plain%20kurtas%22%20%22palazzo%20sets%22%20%20%20%20%20)%20%20%20%20%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20)&q=*%3A*&rows=80000";

        try {
            $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
            $this->curl->get($solrUrl);
            $response = $this->curl->getBody();
            return json_decode($response, true);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
///Gender
public function getGenderData(string $genderName)
{
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60);

        // Check if comma exists (means multiple selected)
        if (strpos($genderName, ',') !== false) {
            $genderArray = explode(',', $genderName);
            $genderQuery = '(' . implode(' OR ', array_map(function($gender) {
                return '"' . trim($gender) . '"';
            }, $genderArray)) . ')';
        } else {
            // Single gender
            $genderQuery = '"' . trim($genderName) . '"';
        }

        $encodedGenderQuery = urlencode($genderQuery);

        $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?q=*:*&fq=gender_name:$encodedGenderQuery&fl=prod_name,actual_price_1,prod_small_img,prod_thumb_img,short_desc,prod_desc,size_name,prod_sku,gender_name&rows=400&wt=json";

        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);

    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


public function searchSolrData(array $payload)
{
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select";

    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60);
        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->post($solrUrl, json_encode($payload));
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getSolrSearch($queryParams)
{
    $query = isset($queryParams['query']) ? $queryParams['query'] : '*:*';
    $params = isset($queryParams['params']) ? $queryParams['params'] : [];

    $fl = isset($params['fl']) ? $params['fl'] : '*';
    $rows = isset($params['rows']) ? $params['rows'] : 10;

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?"
        . "q=" . urlencode($query)
        . "&fl=" . urlencode($fl)
        . "&rows=" . intval($rows)
        . "&wt=json";

    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60);
        $this->curl->get($solrUrl);
        return json_decode($this->curl->getBody(), true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}




       // New In Accessories


 public function getNewInAccessories()
 {
     $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(%20%20%20%20%20categories-store-1_id%3A(%20%20%20%20%20%20%20%20%201372%201501%202304%202305%202306%202613%202700%202701%202702%202703%202716%202717%202718%202721%202722%20%20%20%20%20%20%20%20%20%202723%202725%202726%202728%203699%203792%203902%205609%205570%205572%20%20%20%20%20)%20%20%20%20%20%20AND%20categories-store-1_name%3A(%20%20%20%20%20%20%20%20%20%22stoles%22%20%22dupattas%22%20%22shawls%22%20%22scarves%22%20%20%20%20%20%20%20%20%20%20%22clutch%20bags%22%20%22backpacks%22%20%22potlis%22%20%22tote%20bags%22%20%22trunks%22%20%22bangle%20box%22%20%22laptop%20bags%22%20%22wallets%22%20%20%20%20%20%20%20%20%20%22belts%22%20%22masks%22%20%20%20%20%20%20%20%20%20%22sandals%22%20%22wedges%22%20%22juttis%22%20%22heels%22%20%22sneakers%22%20%22kolhapuris%22%20%22mules%22%20%20%20%20%20)%20%20%20%20%20%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20)&q=*%3A*&rows=8000";
     try {
         $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
         $this->curl->get($solrUrl);
         $response = $this->curl->getBody();
         return json_decode($response, true);
     } catch (\Exception $e) {
         return ['error' => $e->getMessage()];
     }
 }

 public function getNewInWomenclothing_Lehengas(){

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&q=categories-store-1_id%3A(1372%20OR%201500%20OR%202295%20OR%202665%20OR%202666%20OR%203038%20OR%203049%20OR%203069%20OR%203107%20OR%203109%20OR%203111%20OR%203208%20OR%203210)%20%0AAND%20categories-store-1_name%3A(%22classic%20lehengas%22%20OR%20%22draped%20lehengas%22%20OR%20%22contemporary%20lehengas%22%20OR%20%22ruffle%20lehengas%22%20OR%20%22lighter%20lehengas%22%20OR%20%22printed%20lehengas%22%20OR%20%22floral%20lehengas%22%20OR%20%22festive%20lehengas%22%20OR%20%22bridal%20lehengas%22%20OR%20%22handwoven%20lehengas%22)%20%0AAND%20actual_price_1%3A%5B1%20TO%20*%5D%0A%0Afl%3Ddesigner_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&rows=9000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }


 public function getNewInWomenclothing_KurtaSets(){
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(categories-store-1_id%3A(1372%20OR%201500%20OR%204450%20OR%204451%20OR%204463%20OR%204476%20OR%205594%20)%20AND%20categories-store-1_name%3A(%22%22dhoti%20kurtas%22%22%20OR%20%22%22printed%20kurtas%22%22%20OR%20%22%22contemporary%20kurtas%20%22%22%20OR%20%22%22plain%20kurtas%20%22%22%20%20)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D)&q=*%3A*&rows=8000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }

 public function getNewInWomenclothing_Sarees(){
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&q=categories-store-1_id%3A(1372%20OR%201500%20OR%202297%20OR%202671%20OR%202672%20OR%202673%20OR%203103%20OR%203121%20OR%203213%20OR%203215%20OR%203217%20OR%203258)%0AAND%20categories-store-1_name%3A(%22classic%20sarees%22%20OR%20%22pre%20draped%20sarees%22%20OR%20%22saree%20gowns%22%20OR%20%22printed%20sarees%22%20%0AOR%20%22pants%20and%20dhoti%20sarees%22%20OR%20%22handwoven%20sarees%22%20OR%20%22ruffle%20sarees%22%20OR%20%22striped%20sarees%22%20OR%20%22lehenga%20sarees%22)%0AAND%20actual_price_1%3A%5B1%20TO%20*%5D&rows=8000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }



 
 public function getNewInWomenclothing_Tops(){
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(categories-store-1_id%3A(1372%20OR%201500%20OR%202299%20OR%202681%20OR%202683%20OR%202732%20OR%202809%20OR%203305%20OR%203306%20OR%203307%20OR%203364%20OR%203366)%20AND%20categories-store-1_name%3A(%22cropped%20%22%20OR%20%22blouses%22%20OR%20%22shirts%20%22%20OR%20%22classic%20tops%22%20%20OR%20%22t%20shirts%20%22%20OR%20%22off%20the%20shoulder%22%20OR%20%22printed%22%20OR%20%22sweatshirts%20%22%20OR%20%22hoodies%20%22)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D)&q=*%3A*&rows=15000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }


 public function getNewInWomenclothing_Kaftans(){
   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(categories-store-1_id%3A(1372%20OR%201500%20OR%204046%20OR%204352%20OR%204353%20OR%204354%20)%20AND%20categories-store-1_name%3A(%22%22plain%22%22%20OR%20%22%22embellished%20%22%22%20OR%20%22%22printed%22%22%20%20)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D)&q=*%3A*&rows=8000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }

 public function getNewInWomenclothing_Gowns(){
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(categories-store-1_id%3A(1372%20OR%201500%20OR%203264%20OR%203265%20OR%203268%20OR%203270%20OR%203272%20OR%203274%20OR%203954%20)%20AND%20categories-store-1_name%3A(%22%22feather%22%22%20OR%20%22%22tulle%22%22%20OR%20%22%22cape%22%22%20OR%20%22%22traditional%22%22OR%20%22%22embroidered%22%22%20OR%20%22%22cocktail%22%22%20)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D)&q=*%3A*&rows=8000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }


 public function getNewInWomenclothing_Pants(){
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(categories-store-1_id%3A(1372%20OR%201500%20OR%203247%20OR%203248%20OR%203251%20OR%203253%20OR%203255%20OR%203351%20OR%203368%20)%20AND%20categories-store-1_name%3A(%22%22chudidars%22%22%20OR%20%22%22dhotis%22%22%20OR%20%22%22palazzos%22%22%20OR%20%22%22straight%20pants%22%22OR%20%22%22draped%20pants%22%22%20OR%20%22%22trousers%22%22%20)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D)&q=*%3A*&rows=8000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }


 public function getNewInWomenclothing_TunicsKurtis(){
   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(categories-store-1_id%3A(1372%20OR%201500%20OR%203293%20OR%203294%20OR%203295%20OR%203296%20OR%203297)%20AND%20categories-store-1_name%3A(%22%22tunic%20sets%22%22%20OR%20%22%22short%22%22%20OR%20%22%22long%22%22%20OR%20%22%22printed%22%22%20)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D)&q=*%3A*&rows=8000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }


// pending in Postman test
 public function getNewInWomenclothing_Capes(){
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(categories-store-1_id%3A(1372%20OR%201500%20OR%204491%20OR%205517)%20%20AND%20categories-store-1_name%3A(%22%22cape%20sets%20%22%22%20)%20%20AND%20actual_price_1%3A%5B1%20TO%20*%5D)&q=*%3A*&rows=8000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }

 public function getNewInWomenclothing_Jumpsuits(){
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(categories-store-1_id%3A(1372%20OR%201500%20OR%203027%20OR%203241%20OR%203243%20OR%203245%20)%20AND%20categories-store-1_name%3A(%22%22embellished%20%20%22%22%20OR%20%22%22printed%22%22%20OR%20%22%22plain%22%22%20)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D)&q=*%3A*&rows=3000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }



 public function getNewInWomenclothing_Kurtas(){
  
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(categories-store-1_id%3A(1372%20OR%201500%20OR%204450%20OR%204451%20OR%204463%20OR%204476%20OR%205594%20)%20AND%20categories-store-1_name%3A(%22%22dhoti%20kurtas%22%22%20OR%20%22%22printed%20kurtas%22%22%20OR%20%22%22contemporary%20kurtas%20%22%22%20OR%20%22%22plain%20kurtas%20%22%22%20%20)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D)&q=*%3A*&rows=8000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }



 public function getNewInWomenclothing_Skirts(){
  
   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(categories-store-1_id%3A(1372%20OR%201500%20OR%203219%20OR%203220%20OR%203223%20OR%203225%20OR%203227%20OR%203229%20%20)%20AND%20categories-store-1_name%3A(%22%22lehenga%20skirts%22%22%20OR%20%22%22midi%22%22%20OR%20%22%22mini%22%22%20OR%20%22%22knee%20length%22%22OR%20%22%22embellished%22%22%20)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D)&q=*%3A*&rows=5000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }


 public function getNewInWomenclothing_PalazzoSets(){

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(categories-store-1_id%3A(1372%20OR%201500%20OR%204460)%20%20AND%20categories-store-1_name%3A(%22palazzo%20sets%22)%20%20AND%20actual_price_1%3A%5B1%20TO%20*%5D)&q=*%3A*&rows=8000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }


 public function getNewInWomenclothing_Beach(){

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(categories-store-1_id%3A(1372%20OR%201500%20OR%202596%20OR%203231%20OR%203233%20OR%203235%20OR%203237%20OR%203239%20OR%203370%20OR%203372)%20AND%20categories-store-1_name%3A(%22%22one%20piece%20%22%22%20OR%20%22%22bikinis%22%22%20OR%20%22%22bikinis%20bottoms%22%22%20OR%20%22%22cover%20ups%22%22%20%20OR%20%22%22beach%20dresses%20%22%22%20OR%20%22%22bikni%20tops%22%22%20OR%20%22%22sarongs%22%22)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D)&q=*%3A*&rows=5000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }


/////////////////*Accessories*/////////////////////////


public function getNewInAccessories_Bags(){
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(categories-store-1_id%3A(1372%20OR%201501%20OR%202304%20OR%202716%20OR%202717%20OR%202718%20OR%202721%20OR%202722%20OR%203902%20OR%205570%20OR%205572)%20%20AND%20categories-store-1_name%3A(%22%22clutch%20bags%22%22%20OR%20%22%22backpacks%22%22%20OR%20%22%22potlis%22%22OR%20%22%22tote%20bags%20%22%22OR%20%22%22trunks%20%22%22OR%20%22%22bangle%20box%22%22OR%20%22%22laptop%20bags%22%22OR%20%22%22wallets%22%22)%20%20AND%20actual_price_1%3A%5B1%20TO%20*%5D)&q=*%3A*&rows=8000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }

 public function getNewInAccessories_Shoes(){

    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(categories-store-1_id%3A(1372%20OR%201501%20OR%202306%20OR%202723%20OR%202725%20OR%202726%20OR%202728%20OR%203699%20OR%203792%20OR%205609)%20%20AND%20categories-store-1_name%3A(%22%22sandals%22%22%20OR%20%22%22wedges%20%22%22%20OR%20%22%22juttis%22%22OR%20%22%22heels%20%22%22OR%20%22%22sneakers%22%22OR%20%22%22kolhapuris%22%22OR%20%22%22mules%22%22)%20%20AND%20actual_price_1%3A%5B1%20TO%20*%5D)&q=*%3A*&rows=8000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }

 public function getNewInAccessories_Belts(){

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(categories-store-1_id%3A(1372%20OR%201501%20OR%202305%20)%20%20AND%20categories-store-1_name%3A(%22%22belts%22%22)%20%20AND%20actual_price_1%3A%5B1%20TO%20*%5D)&q=*%3A*&rows=8000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }

 public function getNewInAccessories_Masks(){
   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(categories-store-1_id%3A(1372%20OR%201501%20OR%202613)%20%20AND%20categories-store-1_name%3A(%22%22masks%22%22)%20%20AND%20actual_price_1%3A%5B1%20TO%20*%5D)&q=*%3A*&rows=8000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }



 /////////////////*Men*/////////////////////////

 public function getNewInMen_KurtaSets(){

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(categories-store-1_id%3A(1372%20OR%201977%20OR%202588%20%20OR%203077%20OR%203079%20OR%203315%20OR%203317%20)%20%20AND%20categories-store-1_name%3A(%22%22embellished%22%22%20OR%20%22%22plain%22%22%20OR%20%22%22printed%22%22OR%20%22%22avant-%20garde%22%22)%20%20AND%20actual_price_1%3A%5B1%20TO%20*%5D)&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }


 public function getNewInMen_Sherwanis(){

   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(categories-store-1_id%3A(1372%20OR%201977%20OR%202590%20%20OR%203081%20OR%203083%20OR%203085)%20%20AND%20categories-store-1_name%3A(%22%22heavy%20sherwanis%22%22%20OR%20%22%22light%20sherwanis%22%22OR%20%22%22printed%20sherwanis%22%22)%20%20AND%20actual_price_1%3A%5B1%20TO%20*%5D)&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }


 public function getNewInMen_Jackets(){

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(categories-store-1_id%3A(1372%20OR%201977%20OR%202595%20%20OR%204477%20OR%204479%20OR%204484%20OR%205601)%20%20AND%20categories-store-1_name%3A(%22%22formal%20jackets%22%22%20OR%20%22%22casual%20jackets%22%22OR%20%22%22avant-%20grade%22%22OR%20%22%22jacket%20sets%22%22)%20%20AND%20actual_price_1%3A%5B1%20TO%20*%5D)&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }


 public function getNewInMen_MenAccessories(){


    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(categories-store-1_id%3A(1372%20OR%201977%20OR%202591%20OR%202810%20OR%202811%20OR%202828%20OR%202829%20OR%202830%20OR%202831%20OR%203624%20OR%203746%20OR%203795%20OR%204105%20OR%204356%20OR%205614%20OR%206088)%20AND%20categories-store-1_name%3A(%22cufflinks%22%20OR%20%22pocket%20square%22%20OR%20%22headwear%22%20OR%20%22buttons%22%20OR%20%22lapel%20pins%20and%20collar%20tips%22%20OR%20%22kalangi%22%20OR%20%22brooches%22%20OR%20%22men%27s%20necklace%22%20OR%20%22gift%20boxes%22%20OR%20%22belts%22%20OR%20%22shawls%20%26%20stoles%22%20OR%20%22bracelets%22%20OR%20%22earrings%22)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D)&q=*%3A*&rows=10000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }


 public function getNewInMen_Kurtas(){

   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(categories-store-1_id%3A(1372%20OR%201977%20OR%202587%20OR%203051%20OR%203052%20OR%203053%20OR%203054)%20AND%20categories-store-1_name%3A(%22plain%22%20OR%20%22printed%22%20OR%20%22avant%20garde%22%20OR%20%22embellished%22)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D)&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }
 

 public function getNewInMen_Shirts(){

    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(categories-store-1_id%3A(1372%20OR%201977%20OR%202593%20%20OR%203724%20OR%203726%20)%20%20AND%20categories-store-1_name%3A(%22%22formal%20shirts%22%22%20OR%20%22%22casual%20shirts%22%22)%20%20AND%20actual_price_1%3A%5B1%20TO%20*%5D)&q=*%3A*&rows=10000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }

 public function getNewInMen_Bandis(){


    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(categories-store-1_id%3A(1372%20OR%201977%20OR%202589%20%20OR%203059%20OR%203060%20OR%205931)%20%20AND%20categories-store-1_name%3A(%22%22plain%22%22%20OR%20%22%22printed%22%22OR%20%22%22embellished%22%22)%20%20AND%20actual_price_1%3A%5B1%20TO%20*%5D)&q=*%3A*&rows=8000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }

 public function getNewInMen_Trousers(){

   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=(categories-store-1_id%3A(1372%20OR%201977%20OR%202594%20%20OR%201731)%20%20AND%20categories-store-1_name%3A(%22%22trousers%22%22%20)%20%20AND%20actual_price_1%3A%5B1%20TO%20*%5D)&q=*%3A*&rows=10000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }


 /////////////////*New In Jewellery*/////////////////////////

 public function getNewInJewellery_Earrings(){
   
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(1372%20OR%206023%20OR%206024%20OR%206222%20OR%206225%20OR%206228%20OR%206231%20OR%206233%20OR%206236)%20AND%20categories-store-1_name%3A(chandbalis%20OR%20jhumkas%20OR%20danglers%20OR%20studs%20OR%20hoops%20OR%20earcuffs)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }

 public function getNewInJewellery_BanglesBracelets(){
   
    

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_name%3A*bangle*&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }



 }



 

 public function getNewInJewellery_FineJewelry(){
   
    

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(1372%20OR%206023%20OR%206064%20OR%206065%20OR%206069%20OR%206072%20OR%206075%20OR%206077)%20AND%20categories-store-1_name%3A(tikkasandpassas%20OR%20bangle%20OR%20rings%20OR%20earrings%20OR%20necklaces%20)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }



 }





 public function getNewInJewellery_HandHarness(){
   
   

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_name%3Ahand%5C%20harness&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }



 }


 public function getNewInJewellery_Rings(){
   
   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(1372%20OR%206023%20OR%206038%20)%20AND%20categories-store-1_name%3A(rings)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D&q=*%3A*&rows=10000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }



 }

 


 public function getNewInJewellery_FootHarness(){
    
   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_name%3Afoot%5C%20harness&q=*%3A*&rows=10000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }



 }

 


 public function getNewInJewellery_Brooches(){

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(1372%20OR%206023%20OR%206036%20)%20AND%20categories-store-1_name%3A(brooches)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D&q=*%3A*&rows=10000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }



 }


 public function getNewInJewellery_Giftboxes(){
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_name%3Agift%5C%20boxes&q=*%3A*&rows=10000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }



 }

 /////////////////*KidsWeaar*/////////////////////////

 public function getNewInKidswear_KurtaSetsforBoys(){
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(3327)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }

 public function getNewInKidswear_Shararas(){

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(2145)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }

 public function getNewInKidswear_Dresses(){
  
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(2139%20OR%203332%20OR%203338%20OR%204161%20OR%204162%20)%20AND%20categories-store-1_name%3A(day%20dresses%2C%20party%20dresses%2Cfloral%20dresses%2Cruffle%20dresses)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }



 public function getNewInKidswear_KidsAccessories(){
   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(4045%20OR%204123%20OR%204148%20OR%204149%20OR%204150%20OR%204151%20OR%204152%20OR%204153%20OR%204154%20OR%204154)%20AND%20categories-store-1_name%3A(hair%20accessories%20%2C%20earrings%2Cpendant%2Cbracelets%2Cmaang%20tikka%2Chathphool%2Cnecklace%2Chair%20clips%2Chair%20band)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }


 public function getNewInKidswear_Shirts(){
   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(1978%20OR%204254%20)%20AND%20categories-store-1_name%3A(shirts)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }


 public function getNewInKidswear_Jackets(){
   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(1978%20OR%203336)%20AND%20categories-store-1_name%3A(jackets)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }

 public function getNewInKidswear_Coordset(){
 
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(%204419)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }






 public function getNewInKidswear_Anarkalis(){

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(%202137)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }


 public function getNewInKidswear_Gowns(){
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(%202140)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }

  
 public function getNewInKidswear_Achkan(){
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(%202143)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }


 public function getNewInKidswear_Bandhgalas(){
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(1978%20OR%202147)%20AND%20categories-store-1_name%3A(bandhgalas)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }


 

 public function getNewInKidswear_Dhotisets(){
  
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(%202146)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }

 

 public function getNewInKidswear_Jumpsuit(){

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(%204094)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }


 


 public function getNewInKidswear_Sherwanis(){
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(1978%20OR%202144)%20AND%20categories-store-1_name%3A(sherwanis)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }




 public function getNewInKidswear_Pants(){

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(4405)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }

 public function getNewInKidswear_Bags(){
  
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(5928)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }

 public function getNewInKidswear_Tops(){
   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(3762)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }


 public function getNewInKidswear_Skirts(){

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(4407)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }



 public function getNewInKidswear_Sarees(){
   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(2161)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D&q=*%3A*&rows=10000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }



 /////////////////*Theme*/////////////////////////

 public function getNewInTheme_Contemporary(){


   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Ctheme_name&fq=fq%3D(%20%20%20categories-store-1_id%3A(1372%201500%202295%202296%202297%202298%202299%202665%202666%202668%202670%202671%202672%202673%202676%202677%202681%202683%202732%202809%203038%203040%203049%203063%203067%203069%203091%203103%203105%203107%203109%203111%203121%203208%203210%203213%203215%203217%203258%203260%203303%203341%203364%203366%203765%205559%204046%204352%204353%204354%204460%204463%204476%205594)%20%20%20AND%20categories-store-1_name%3A(%20%20%20%20%20%22classic%20lehengas%22%20%22draped%20lehengas%22%20%22contemporary%20lehengas%22%20%22ruffle%20lehengas%22%20%22lighter%20lehengas%22%20%20%20%20%20%22printed%20lehengas%22%20%22floral%20lehengas%22%20%22festive%20lehengas%22%20%22bridal%20lehengas%22%20%22handwoven%20lehengas%22%20%20%20%20%20%22anarkalis%22%20%22sharara%20sets%22%20%22palazzo%20sets%22%20%22printed%20kurtas%22%20%22straight%20kurta%20sets%22%20%22dhoti%20kurtas%22%20%22kurtas%22%20%20%20%20%20%22handwoven%20kurta%20sets%22%20%22kurta%20sets%22%20%22classic%20sarees%22%20%22pre%20draped%20sarees%22%20%22saree%20gowns%22%20%22printed%20sarees%22%20%20%20%20%20%22pants%20and%20dhoti%20sarees%22%20%22handwoven%20sarees%22%20%22ruffle%20sarees%22%20%22striped%20sarees%22%20%22lehenga%20sarees%22%20%20%20%20%20%22maxi%20dresses%22%20%22midi%20dresses%22%20%22mini%20dresses%22%20%22silk%20dresses%22%20%22evening%20dresses%22%20%22day%20dresses%22%20%22floral%20dresses%22%20%20%20%20%20%22knee%20length%20dresses%22%20%22shift%20dresses%22%20%22cropped%22%20%22blouses%22%20%22shirts%22%20%22classic%20tops%22%20%22t%20shirts%22%20%22off%20the%20shoulder%22%20%20%20%20%20%22printed%22%20%22sweatshirts%22%20%22hoodies%22%20%22suits%22%20%22tracksuits%22%20%22jacket%20sets%22%20%22crop%20top%20sets%22%20%22skirt%20sets%22%20%22pant%20sets%22%20%20%20%20%20%22short%20sets%22%20%22lehenga%20skirts%22%20%22midi%22%20%22mini%22%20%22knee%20length%22%20%22embellished%22%20%22chudidars%22%20%22dhotis%22%20%22palazzos%22%20%20%20%20%20%22straight%20pants%22%20%22draped%20pants%22%20%22trousers%22%20%22feather%22%20%22tulle%22%20%22cape%22%20%22traditional%22%20%22embroidered%22%20%22cocktail%22%20%20%20%20%20%22tunic%20sets%22%20%22short%22%20%22long%22%20%22printed%22%20%22plain%22%20%22embellished%22%20%22dhoti%20kurtas%22%20%22printed%20kurtas%22%20%22contemporary%20kurtas%22%20%20%20%20%20%22plain%20kurtas%22%20%22palazzo%20sets%22%20%20%20)%20%20%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20%20%20AND%20theme_name%3A%22Contemporary%22%20)&q=*%3A*&rows=20000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }



 public function getNewInTheme_Ethnic(){


   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Ctheme_name&fq=fq%3D(%20%20%20categories-store-1_id%3A(1372%201500%202295%202296%202297%202298%202299%202665%202666%202668%202670%202671%202672%202673%202676%202677%202681%202683%202732%202809%203038%203040%203049%203063%203067%203069%203091%203103%203105%203107%203109%203111%203121%203208%203210%203213%203215%203217%203258%203260%203303%203341%203364%203366%203765%205559%204046%204352%204353%204354%204460%204463%204476%205594)%20%20%20AND%20categories-store-1_name%3A(%20%20%20%20%20%22classic%20lehengas%22%20%22draped%20lehengas%22%20%22contemporary%20lehengas%22%20%22ruffle%20lehengas%22%20%22lighter%20lehengas%22%20%20%20%20%20%22printed%20lehengas%22%20%22floral%20lehengas%22%20%22festive%20lehengas%22%20%22bridal%20lehengas%22%20%22handwoven%20lehengas%22%20%20%20%20%20%22anarkalis%22%20%22sharara%20sets%22%20%22palazzo%20sets%22%20%22printed%20kurtas%22%20%22straight%20kurta%20sets%22%20%22dhoti%20kurtas%22%20%22kurtas%22%20%20%20%20%20%22handwoven%20kurta%20sets%22%20%22kurta%20sets%22%20%22classic%20sarees%22%20%22pre%20draped%20sarees%22%20%22saree%20gowns%22%20%22printed%20sarees%22%20%20%20%20%20%22pants%20and%20dhoti%20sarees%22%20%22handwoven%20sarees%22%20%22ruffle%20sarees%22%20%22striped%20sarees%22%20%22lehenga%20sarees%22%20%20%20%20%20%22maxi%20dresses%22%20%22midi%20dresses%22%20%22mini%20dresses%22%20%22silk%20dresses%22%20%22evening%20dresses%22%20%22day%20dresses%22%20%22floral%20dresses%22%20%20%20%20%20%22knee%20length%20dresses%22%20%22shift%20dresses%22%20%22cropped%22%20%22blouses%22%20%22shirts%22%20%22classic%20tops%22%20%22t%20shirts%22%20%22off%20the%20shoulder%22%20%20%20%20%20%22printed%22%20%22sweatshirts%22%20%22hoodies%22%20%22suits%22%20%22tracksuits%22%20%22jacket%20sets%22%20%22crop%20top%20sets%22%20%22skirt%20sets%22%20%22pant%20sets%22%20%20%20%20%20%22short%20sets%22%20%22lehenga%20skirts%22%20%22midi%22%20%22mini%22%20%22knee%20length%22%20%22embellished%22%20%22chudidars%22%20%22dhotis%22%20%22palazzos%22%20%20%20%20%20%22straight%20pants%22%20%22draped%20pants%22%20%22trousers%22%20%22feather%22%20%22tulle%22%20%22cape%22%20%22traditional%22%20%22embroidered%22%20%22cocktail%22%20%20%20%20%20%22tunic%20sets%22%20%22short%22%20%22long%22%20%22printed%22%20%22plain%22%20%22embellished%22%20%22dhoti%20kurtas%22%20%22printed%20kurtas%22%20%22contemporary%20kurtas%22%20%20%20%20%20%22plain%20kurtas%22%20%22palazzo%20sets%22%20%20%20)%20%20%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20%20%20AND%20theme_name%3A%22Ethnic%22%20)&q=*%3A*&rows=20000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }



 /////////////////*NEWIN GENDER*/////////////////////////

 public function getNewInGender_Men(){


  
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(1977)%20AND%20categories-store-1_name%3A(%22men%22%20)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D&q=*%3A*&rows=10000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }

 public function getNewInGender_Women(){

    
 
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc&fq=categories-store-1_id%3A(1500)&q=*%3A*&rows=20000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

 }



/////////////////*NEWIN Color*/////////////////////////
/// for loop 


public function geNewInColor($colorName){

    $validColors = ['Black', 'Blue', 'Brown', 'Burgundy', 'Gold', 'Green', 'Grey', 'Metallic', 'Multicolor', 'Neutrals', 'Orange', 'Peach', 'Pink', 'Print', 'Purple', 'Red', 'Silver', 'White', 'Yellow'];

    if (!in_array($colorName, $validColors)) {
        return ['error' => 'Invalid color name'];
    }

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Ccolor_name&fq=categories-store-1_id%3A(1372)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20color_name%3A%22$colorName%22%20AND%20-color_name%3A%22Gold%22%20AND%20-color_name%3A%22White%22%20AND%20-color_name%3A%22Silver%22&q=*%3A*&rows=10000";

    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60);
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
}

}


// Example function for each color filter

public function getNewInColor_Black() {
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Ccolor_name&fq=categories-store-1_id%3A(1372)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20color_name%3A%22Black%22%20AND%20-color_name%3A%22Gold%22%20AND%20-color_name%3A%22White%22%20AND%20-color_name%3A%22Silver%22&q=*%3A*&rows=10000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInColor_Blue() {
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Ccolor_name&fq=categories-store-1_id%3A(1372)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20color_name%3A%22Blue%22%20AND%20-color_name%3A%22Gold%22%20AND%20-color_name%3A%22White%22%20AND%20-color_name%3A%22Silver%22&q=*%3A*&rows=10000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInColor_Brown() {
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Ccolor_name&fq=categories-store-1_id%3A(1372)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20color_name%3A%22Brown%22%20AND%20-color_name%3A%22Gold%22%20AND%20-color_name%3A%22White%22%20AND%20-color_name%3A%22Silver%22&q=*%3A*&rows=10000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInColor_Burgundy() {
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Ccolor_name&fq=categories-store-1_id%3A(1372)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20color_name%3A%22Burgundy%22%20AND%20-color_name%3A%22Gold%22%20AND%20-color_name%3A%22White%22%20AND%20-color_name%3A%22Silver%22&q=*%3A*&rows=10000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInColor_Gold() {
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Ccolor_name&fq=categories-store-1_id%3A(1372)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20color_name%3A%22Gold%22%20AND%20-color_name%3A%22Black%22%20AND%20-color_name%3A%22White%22%20AND%20-color_name%3A%22Silver%22&q=*%3A*&rows=10000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInColor_Green() {
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Ccolor_name&fq=categories-store-1_id%3A(1372)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20color_name%3A%22Green%22%20AND%20-color_name%3A%22Gold%22%20AND%20-color_name%3A%22White%22%20AND%20-color_name%3A%22Silver%22&q=*%3A*&rows=10000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInColor_Grey() {
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Ccolor_name&fq=categories-store-1_id%3A(1372)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20color_name%3A%22Grey%22%20AND%20-color_name%3A%22Gold%22%20AND%20-color_name%3A%22White%22%20AND%20-color_name%3A%22Silver%22&q=*%3A*&rows=10000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInColor_Metallic() {
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Ccolor_name&fq=categories-store-1_id%3A(1372)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20color_name%3A%22Metallic%22%20AND%20-color_name%3A%22Gold%22%20AND%20-color_name%3A%22White%22%20AND%20-color_name%3A%22Silver%22&q=*%3A*&rows=10000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInColor_Red() {
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Ccolor_name&fq=categories-store-1_id%3A(1372)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20color_name%3A%22Red%22%20AND%20-color_name%3A%22Gold%22%20AND%20-color_name%3A%22White%22%20AND%20-color_name%3A%22Silver%22&q=*%3A*&rows=10000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInColor_Yellow() {
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Ccolor_name&fq=categories-store-1_id%3A(1372)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20color_name%3A%22Yellow%22%20AND%20-color_name%3A%22Gold%22%20AND%20-color_name%3A%22Blue%22%20AND%20-color_name%3A%22Black%22&q=*%3A*&rows=10000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInColor_White() {
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Ccolor_name&fq=categories-store-1_id%3A(1372)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20color_name%3A%22White%22%20AND%20-color_name%3A%22Gold%22%20AND%20-color_name%3A%22Blue%22%20AND%20-color_name%3A%22Black%22&q=*%3A*&rows=10000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInColor_Pink() {

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Ccolor_name&fq=categories-store-1_id%3A(1372)%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20color_name%3A%22Pink%22%20AND%20-color_name%3A%22Gold%22%20AND%20-color_name%3A%22White%22%20AND%20-color_name%3A%22Silver%22&q=*%3A*&rows=10000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}



/////////////////*NEWIN Size*/////////////////////////

public function getNewInSize_XXSmall() {

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22XXSmall%22&q=*%3A*&rows=20000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_XSmall() {
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22XSmall%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_Small(){
   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Small%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_Medium(){

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Medium%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_Large() {
   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Large%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


public function getNewInSize_XLarge() {
  
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22XLarge%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}
public function getNewInSize_XXLarge() {
  
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22XXLarge%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}
public function getNewInSize_4XLarge() {

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%224XLarge%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}
public function getNewInSize_5XLarge() {
   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%225XLarge%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}
public function getNewInSize_CustomMade() {

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Custom%20Made%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}
public function getNewInSize_FreeSize() {

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Free%20Size%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}
public function getNewInSize_EuroSize32() {
   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Euro%20Size%2032%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}
public function getNewInSize_EuroSize33() {

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Euro%20Size%2033%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}
public function getNewInSize_EuroSize34() {
   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Euro%20Size%2034%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}
public function getNewInSize_EuroSize35() {
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Euro%20Size%2035%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}
public function getNewInSize_EuroSize36() {
  
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Euro%20Size%2036%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}
public function getNewInSize_EuroSize37() {
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Euro%20Size%2037%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


public function getNewInSize_EuroSize38() {
  
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Euro%20Size%2038%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}
public function getNewInSize_EuroSize39() {
   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Euro%20Size%2039%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}
public function getNewInSize_EuroSize40() {
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Euro%20Size%2040%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}
public function getNewInSize_EuroSize41() {
  
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Euro%20Size%2041%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}
public function getNewInSize_EuroSize42() {
   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Euro%20Size%2042%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_EuroSize43() {
  
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Euro%20Size%2043%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_EuroSize44() {
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Euro%20Size%2044%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_EuroSize45() {
   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Euro%20Size%2045%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_EuroSize46() {
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Euro%20Size%2046%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_EuroSize47() {
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Euro%20Size%2047%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_EuroSize48() {

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Euro%20Size%2048%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_EuroSize49() {

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Euro%20Size%2049%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_BangleSize22(){

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Bangle%20Size-%202.2%5C%22%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


public function getNewInSize_BangleSize24() {
   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Bangle%20Size-%202.4%5C%22%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


public function getNewInSize_BangleSize26() {
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Bangle%20Size-%202.6%5C%22%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


public function getNewInSize_BangleSize28() {
   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%22Bangle%20Size-%202.8%5C%22%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


public function getNewInSize_6_12Months() {
  
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%226-12%20Months%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_1_2Years() {
   
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%221-2%20Years%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_2_3Years() {
  
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%222-3%20Years%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_3_4Years() {
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%223-4%20Years%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_4_5Years() {
  
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%224-5%20Years%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_5_6Years() {
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%225-6%20Years%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_6_7Years() {
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%226-7%20Years%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_7_8Years() {
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%227-8%20Years%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_8_9Years() {
  
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%228-9%20Years%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_9_10Years() {
    
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%229-10%20Years%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_10_11Years() {
 
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%2210-11%20Years%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_11_12Years() {
  
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%2211-12%20Years%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_12_13Years() {
 
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%2212-13%20Years%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_13_14Years() {
  
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%2213-14%20Years%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_14_15Years() {
 
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%2214-15%20Years%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInSize_15_16Years() {
  
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2Csize_name&fq=categories-store-1_id%3A1372%20AND%20actual_price_1%3A%5B1%20TO%20*%5D%20AND%20size_name%3A%2215-16%20Years%22&q=*%3A*&rows=20000&wt=json";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


/////////////////*NEWIN Delivery*/////////////////////////

public function getNewInDelivery_Immediate() {
  
    

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2C%20child_delivery_time&fq=child_delivery_time%3A%22Immediate%22%20AND%20-child_delivery_time%3A%221-2%20Weeks%22%20AND%20-child_delivery_time%3A%222-4%20Weeks%22%20AND%20-child_delivery_time%3A%224-6%20Weeks%22%20AND%20-child_delivery_time%3A%226-8%20Weeks%22%20AND%20-child_delivery_time%3A%22%3E8%20Weeks%22&q=*%3A*&rows=10000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


public function getNewInDelivery_1_2Weeks() {
  


    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2C%20child_delivery_time&fq=child_delivery_time%3A%221-2%20Weeks%22%20AND%20-child_delivery_time%3A%22Immediate%22%20AND%20-child_delivery_time%3A%222-4%20Weeks%22%20AND%20-child_delivery_time%3A%224-6%20Weeks%22%20AND%20-child_delivery_time%3A%226-8%20Weeks%22%20AND%20-child_delivery_time%3A%22%3E8%20Weeks%22&q=*%3A*&rows=10000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


public function getNewInDelivery_2_4Weeks() {
  


    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2C%20child_delivery_time&fq=child_delivery_time%3A%222-4%20Weeks%22%20AND%20-child_delivery_time%3A%22Immediate%22%20AND%20-child_delivery_time%3A%221-2%20Weeks%22%20AND%20-child_delivery_time%3A%224-6%20Weeks%22%20AND%20-child_delivery_time%3A%226-8%20Weeks%22%20AND%20-child_delivery_time%3A%22%3E8%20Weeks%22&q=*%3A*&rows=20000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


public function getNewInDelivery_4_6Weeks() {
   

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2C%20child_delivery_time&fq=child_delivery_time%3A%224-6%20Weeks%22%20AND%20-child_delivery_time%3A%22Immediate%22%20AND%20-child_delivery_time%3A%221-2%20Weeks%22%20AND%20-child_delivery_time%3A%222-4%20Weeks%22%20AND%20-child_delivery_time%3A%226-8%20Weeks%22%20AND%20-child_delivery_time%3A%22%3E8%20Weeks%22&q=*%3A*&rows=20000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInDelivery_6_8Weeks() {
   
 
    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2C%20child_delivery_time&fq=child_delivery_time%3A%226-8%20Weeks%22%20AND%20-child_delivery_time%3A%22Immediate%22%20AND%20-child_delivery_time%3A%221-2%20Weeks%22%20AND%20-child_delivery_time%3A%222-4%20Weeks%22%20AND%20-child_delivery_time%3A%224-6%20Weeks%22%20AND%20-child_delivery_time%3A%22%3E8%20Weeks%22&q=*%3A*&rows=20000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

public function getNewInDelivery_8Weeks() {

    $solrUrl = "http://127.0.0.1:8983/solr/aashni_dev/select?fl=designer_name%2Cactual_price_1%2Cprod_name%2Cprod_en_id%2Cprod_sku%2Cprod_small_img%2Cprod_thumb_img%2Cshort_desc%2C%20child_delivery_time&fq=child_delivery_time%3A%22%3E%208%20Weeks%22%20AND%20-child_delivery_time%3A%22Immediate%22%20AND%20-child_delivery_time%3A%221-2%20Weeks%22%20AND%20-child_delivery_time%3A%222-4%20Weeks%22%20AND%20-child_delivery_time%3A%224-6%20Weeks%22%20AND%20-child_delivery_time%3A%226-8%20Weeks%22&q=*%3A*&rows=20000";
    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 60); 
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}


public function getThemeData(string $themes, string $categoryId)
{
    // Convert the comma-separated theme list into an array and trim extra spaces
    $themeArray = array_map('trim', explode(',', $themes));

    // Validate themes
    if (empty($themeArray)) {
        return ['error' => 'No themes provided'];
    }

    // Build theme filter query
    if (count($themeArray) === 1) {
        // Only one theme selected: no OR, simple query
        $themeFilter = 'theme_name:"' . $themeArray[0] . '"';
    } else {
        // Multiple themes selected: wrap with ( ) and OR
        $themeFilter = '(' . implode(' OR ', array_map(function ($theme) {
            return 'theme_name:"' . $theme . '"';
        }, $themeArray)) . ')';
    }

    // Base Solr URL
    $baseSolrUrl = 'http://130.61.224.212:8983/solr/aashni_dev/select';

    // Build query parameters
    $queryParams = [
        'q' => '*:*',
        'fl' => 'designer_name,actual_price_1,prod_name,prod_en_id,prod_sku,prod_small_img,prod_thumb_img,short_desc,theme_name',
        'wt' => 'json',
        'rows' => 200,
        'fq' => [

            $themeFilter,
            "categories-store-1_id:$categoryId"
        ]
    ];

    // Build query string manually for multiple fq
    $queryString = http_build_query([
        'q' => $queryParams['q'],
        'fl' => $queryParams['fl'],
        'wt' => $queryParams['wt'],
        'rows' => $queryParams['rows'],
    ]);

    foreach ($queryParams['fq'] as $fq) {
        $queryString .= '&fq=' . urlencode($fq);
    }

    $solrUrl = $baseSolrUrl . '?' . $queryString;

    try {
        $this->curl->setOption(CURLOPT_TIMEOUT, 120);
        $this->curl->get($solrUrl);
        $response = $this->curl->getBody();
        return json_decode($response, true);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }

    // echo "Print theme";
}






}