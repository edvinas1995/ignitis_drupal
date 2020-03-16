<?php 

namespace Drupal\municipalities_registration\Services;

class PostitService {
    
    protected $base_url = 'https://api.postit.lt/v2/';
    protected $api_key;
    protected $data = array();
    protected $responseStatusCode;
    
    public function __construct(string $api_key) {
        $this->api_key = $api_key;
    }
    
    public function getBaseUrl() :string
    {
        return $this->base_url;
    }
    
    public function getApiKey() :string
    {
        return $this->api_key;
    }
    
    public function getData()
    {
        return $this->data;
    }
    
    private function setData($data)
    {
        $this->data = $data;
    }
   
    public function getMunicipalities($params = array()) :bool
    {
        $total_page = 1;
        $data = array();
        $final_params = array_merge([
            'key' => $this->getApiKey(),
            'group' => 'municipality',
            'municipality' => '',
            'page' => 1,
            'limit' => 20
        ], $params);
                
        do {
            $resp = $this->getMunicipalitiesByQuery($final_params);
            if(false === $resp['success']) break;
            $total_page = $resp['page']['total'];
            $final_params['page']++;
            array_walk($resp['data'],function ($item, $key) use (&$data) {
                $data[] = $item;
            });
        } while ($total_page >= $final_params['page']);
        
        if(empty($data)){
            \Drupal::logger('municipalities_registration')->notice('PostitService ERR. Message: '. print_r($data, true));
            return false;
        }
        
        $this->setData($data);
        
        return true;
    }
    
    public function getMunicipalitiesByQuery(array $query) :array
    {
        $resp = \Drupal::httpClient()->request('GET', $this->getBaseUrl(), ['query' => $query]);
        $data = \json_decode($resp->getBody(), true);
        
        return $data;
    }
    
}