<?php
namespace JeanKassio\Sioner;

require_once(dirname(__FILE__) . '/scraper/vendor/autoload.php');

use Symfony\Component\Panther\Client;
use Facebook\WebDriver\WebDriverElement;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\DomCrawler;
use Symfony\Component\Panther\DomCrawler\Image;
use Symfony\Component\Panther\DomCrawler\Link;
use Symfony\Component\CssSelector\CssSelectorConverter;
use DOMDocument;


class MetadataExtractor{

    private $link;
    private $seconds;
    private $size;
    private $biggersize;
    private $website;
    private $check;

    public function __construct(public string $_link, public int $_seconds = 2, public $_size = [200,200], public $_check = ['website', 'title', 'image', 'description']){
		
		$_SERVER['PANTHER_CHROME_ARGUMENTS'] = true;
		$_SERVER['PANTHER_NO_SANDBOX'] = true;

        $this->link = $_link;
        $this->seconds = $_seconds;
        $this->size = $_size;
        $this->check = $_check;
		
    }
	
	public function ExtractMetadata(){
		
		$t_seconds = $this->seconds;
		
		$this->seconds = 0;
		
		$response = $this->StartNavigation();
		
		$i=0;
		
		foreach($response as $key=>$res){
			
			if(in_array($key, $this->check) AND !is_null($res)){
				$i++;
			}
			
		}
		
		if($i < count($this->check)){
			
			$this->seconds = $t_seconds;
			
			$this->browser->request('GET', $this->link);
			
			$response = $this->StartExtraction();
			
			return $response;
			
		}else{

			return $response;
			
		}
		
	}
	
	private function StartNavigation(){
		
		$this->browser = Client::createChromeClient(null, ['--no-sandbox','--disable-dev-shm-usage','--headless'], ['capabilities' => ['acceptInsecureCerts' => true]]);
	
		$this->browser->start();
		
		$this->browser->request('GET', $this->link);
		
		return $this->StartExtraction();
		
	}
	
	private function StartExtraction(){
		
		$this->browser->wait(($this->seconds * 1000 * 5), ($this->seconds * 1000));
		usleep(($this->seconds * 1000));
		
		$this->website = parse_url($this->browser->getCurrentURL())['host'];
		
		$response = array();
		
		$response['domain'] = $this->website;
		
		$crawler = new Crawler($this->browser->getCrawler()->html());
		
		$document = new DOMDocument();
        @$document->loadHTML($crawler->html());
		
		foreach($this->GetModelSearch() as $attr=>$elem){
			
			if(is_null($elem)){
				continue;
			}
			
			$unformat = null;
			
			if(is_array($elem)){
					
				foreach($elem as $idi=>$el){
					
					if(!isset($response[$attr]) || $response[$attr] == null || $response[$attr] == ""){
						
						$crawler->filter($el)->each(function(Crawler $node) use($idi, $document, $attr, &$response){
					
							if(!isset($response[$attr]) || $response[$attr] == null || $response[$attr] == ""){
								$response[$attr] = $this->FilterResults($idi, $node, $document);
							}
						
						});
						
					}
					
				}
			
			}else{
				
				$crawler->filter($elem)->each(function(Crawler $node) use($attr, $document, &$response){
					
					if(!isset($response[$attr]) || $response[$attr] == null || $response[$attr] == ""){
						$response[$attr] = $this->FilterResults($attr, $node, $document);
					}
				
				});
				
			}
			
		}
		
		if((!isset($response['image']) OR is_null($response['image'])) AND (!is_null($this->biggersize) AND count($this->biggersize) > 0)){
			$response['image'] = ($this->biggersize["path"] ?? "");
		}
		if(!isset($response['icon']) OR is_null($response['icon'])){
			$response['icon'] = ("https://". $this->website ."/favicon.ico" ?? "");
		}
		
		return $response;
		
	}
	
	private function FilterResults($attr, $node, $document){
		
		switch($attr){
			
			case "canonical": 
				return $node->attr("href");						
			break;
			
			case "description": 
				return ((!is_null($el = $node->attr("content")) AND $el != "" ) ? $el : (($elt = $this->ParseMetatags($document, "description")) ? $elt : null));
			break;
			
			case "og-description": 
				return ((!is_null($el = $node->attr("content")) AND $el != "" ) ? $el : (($elt = $this->ParseMetatags($document, "og:description")) ? $elt : null));
			break;
			
			case "title": 
				return ((!is_null($el = $node->text()) AND $el != "" ) ? $el : (($elt = $document->getElementsByTagName('title')[0]) ? $elt->textContent : null) );
			break;
			
			case "og-title":
				return ((!is_null($el = $node->attr("content")) AND $el != "" ) ? $el : (($elt = $this->ParseMetatags($document, "og:title")) ? $elt : null));
			break;
			
			case "image":
				return ((!is_null($el = $node->getUri()) AND $el != "" ) ? ($this->GetInfosImage($el) ? $el : null) : null);
			break;
			
			case "og-image":
				return ((!is_null($el = $node->attr("content")) AND $el != "" ) ? ($this->GetInfosImage($el) ? $el : null) : (($elt = $this->ParseMetatags($document, "og:image")) ? $elt : null));
			break;
			
			case "keywords":
				return ((!is_null($el = $node->attr("content")) AND $el != "" ) ? $el : (($elt = $this->ParseMetatags($document, "keywords")) ? $elt : null));
			break;
			
			case "apple":
				return ((!is_null($el = $node->attr("href")) AND $el != "" ) ? $el : (($elt = $this->ParseMetatags($document, "icon")) ? $elt : null));
			break;
				
			case "icon":
				return ((!is_null($el = $node->attr("href")) AND $el != "" ) ? $el : (($elt = $this->ParseMetatags($document, "apple-touch-icon")) ? $elt : null));
			break;
				
			case "author":
				return ((!is_null($el = $node->attr("content")) AND $el != "" ) ? $el : (($elt = $this->ParseMetatags($document, "author")) ? $elt : null));
			break;
			
			case "og-author":
				return ((!is_null($el = $node->attr("content")) AND $el != "" ) ? $el : (($elt = $this->ParseMetatags($document, "og:author")) ? $elt : null));
			break;
			
			case "copyright":
				return ((!is_null($el = $node->attr("content")) AND $el != "" ) ? $el : (($elt = $this->ParseMetatags($document, "copyright")) ? $elt : null));
			break;
			
		}
		
	}
	
	private function GetInfosImage($image){
		
		if(!is_array($this->size)){
			return "The Width_X_Height size image definition must be in the format of Boolean Array [width, height] // Default: [200,200]";
		}
		
		$min_width = $this->size[0];
		$min_height = $this->size[1];
		
		list($width, $height, $type, $attr) = getimagesize($image);
		
		if((!isset($this->biggersize["size"])) OR ($this->biggersize["size"] < ($width * $height))){
			
			$this->biggersize = array(
				"size" => $width * $height,
				"path" => $image
			);
			
		}
		
		return (($width >= $min_width) AND ($height >= $min_height));
		
	}
	
	private function ParseMetatags($dom, $value){
		
		$metas = $dom->getElementsByTagName('meta');
		
		for($i=0; $i<$metas->length; $i++){ 
			$meta = $metas->item($i); 
			 
			if($meta->getAttribute('property') == $value){ 
				return $meta->getAttribute('content');
			}elseif($meta->getAttribute('name') == $value){
				return $meta->getAttribute('content');
			}elseif($meta->getAttribute('rel') == $value){
				return $meta->getAttribute('href');
			}
		}
	
	}
	
	private function GetModelSearch(){
		return array(
            'canonical' => '[rel="canonical"]',
            'title' => array(
				'og-title' => '[property="og:title"]',
				'title' => 'title'
			),
            'image' => array(
				'og-image' => '[property="og:image"]',
				'image' => 'img'				
			),
            'description' => array(
				'og-description' => '[property="og:description"]',
				'description' => '[name="description"]'
			),
            'keywords' => '[name="keywords"]',
            'icon' => array(
				'apple' => '[rel="apple-touch-icon"]',
				'icon' => 'icon'
			),
            'author' => array(
				'author' => '[property="og:author"]',
				'og-author' => '[name="author"]'
			),
            'copyright' => '[name="copyright"]'
        );
	}
	
}
