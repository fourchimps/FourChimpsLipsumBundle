<?php

namespace FourChimps\LipsumBundle\Service;

use RuntimeException;
use FourChimps\LipsumBundle\FourChimpsLipsumBundle;

/**
 * Enable us to wrap some caching around the Lipsum webservice. Note caching only 
 * works within a single request - its not relevant across multiple requests.
 *  
 * @author smasterman
 *
 */
class LoremIpsumGenerator/*DefaultController extends Controller*/
{
	private $startWithLoremIpsum = true;
	private $loremIpsumUrl = "http://www.lipsum.com/feed/xml";
	private $typeOfResponse = 0;
	private $numberOf= 0;
	private $cached = false;
	private static $cache= array();
	
    public function get($typeOfResponse='words', $numberOf=10, $startWithLoremIpsum=true, $cached=true)
    {
    	if (!count(self::$cache)) {
    		$this->warmCache();
    	}
    	
    	$this->numberOf = $numberOf;
    	$this->typeOfResponse = $typeOfResponse;
    	$this->startWithLoremIpsum = $startWithLoremIpsum;
    	$this->cached = $cached;
    	
    	$output = $this->getLipsumText();

    	return $output;
    }
    
    /**
     * Get 10 paragraphs. Split them by the period. Store these as sentences.
     * 
     * 
     */
    private function warmCache() {
    	$this->numberOf = 10;
    	$this->typeOfResponse = FourChimpsLipsumBundle::LIPSUM_PARAGRAPH;
    	$this->startWithLoremIpsum = 'yes';
    
    	$response = simplexml_load_file($this->getURL());
    	
    	if ($response->lipsum) {
    		self::$cache = explode('.', $response->lipsum);
    		array_walk(self::$cache, create_function('&$a', '$a = trim($a);'));
    	} else {
    		throw new RuntimeException("Lipsum generator did not return a response.");
    	}
    }
    
    private function getLipsumText() {
    	// Live request
    	if (!$this->cached) {
    		$response = simplexml_load_file($this->getURL());
    		if ($response->lipsum) {
    			return $response->lipsum;
    		} else {
    			throw new RuntimeException("Lipsum generator did not return a response.");
    		}	
    	}
    	
    	// cached request
    	switch ($this->typeOfResponse) {
    		case FourChimpsLipsumBundle::LIPSUM_BYTE: return $this->getBytes();
    		case FourChimpsLipsumBundle::LIPSUM_WORD: return $this->getWords();
    		case FourChimpsLipsumBundle::LIPSUM_SENTENCE: return $this->getSentences($this->numberOf);
    		case FourChimpsLipsumBundle::LIPSUM_PARAGRAPH: return $this->getParagraphs();
    		default:
    			throw new RuntimeException("Unknown request type: $this->typeOfResponse");
    		break;
    	}
    	
    }

    private function getBytes() {
    	$op = "";
    	$buffer = $this->startWithLoremIpsum ? self::$cache[0] : $this->getRandomSentence();
    	while (strlen($op) < $this->numberOf) {
    		if (strlen($op) + strlen($buffer) < $this->numberOf ) {
    			$op .= $buffer;
    			$buffer = $this->getRandomSentence() . '. ';
    		} else {
    			$op .= substr($buffer, 0, $this->numberOf - strlen($op)) . '.';
    		}
    	}
    	return trim($op);
    }

    private function getWords() {
    	$op = array();
    	$buffer = explode(' ', $this->startWithLoremIpsum ? self::$cache[0] : $this->getRandomSentence());
    	while (count($op) < $this->numberOf) {
    		$op[] = array_shift($buffer) ;
    		if (count($buffer) == 0) {
    			$op[count($op)-1] .=  '.';
    			$buffer = explode(' ', $this->getRandomSentence());
    		}
    	}
    	return implode(' ', $op) . '.';
    }

    private function getSentences($count, $forceRandomStart = false) {
    	$op = array();
    	if ($this->startWithLoremIpsum && !$forceRandomStart ) {
    		$op[] = self::$cache[0] . '.';
    	}
    	while (count($op) < $count) {
    		$op[] = $this->getRandomSentence() . '.';
    	}
    	return implode(' ', $op);
    }
    
    private function getParagraphs() {
    	$first = !$this->startWithLoremIpsum ;
    	$op = '';
    	for($i = 0 ; $i < $this->numberOf; $i++) {
    		$op .= $this->getSentences(rand(2,5), $first) . "\n\n";
    		$first = true;
    	}
    	return trim($op);
    }
    
    private function getRandomSentence() {
    	$index = rand(1, count(self::$cache)-1);
    	return self::$cache[$index] ;
    }
    
    private function getUrl()
    {
    	$st = $this->loremIpsumUrl;
    	$st .= "?amount=";
    	$st .= $this->numberOf;
    	$st .= "&what=";
    	$st .= $this->typeOfResponse;
    	$st .= "&start=";
    	$st .= $this->startWithLoremIpsum ? "yes" : "no";
    	return $st;
    }
}
