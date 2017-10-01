<?php namespace Look\Essentials\Classes;
	
/**
* Class for general helper methods
*/
class LookHelper {
	
	/**
	* Trim content to fit within a set number of words
	*
	* @param string $content
	* @param integer $numWords
	* @param boolean $returnHtml
	* @return array
	*/
	public static function trimByWords($content, $numWords = 35, $returnHtml = false) {
		// Strip any HTML tags
		$stripped_content = strip_tags($content);
		$stripped_content = preg_replace('/\\s+/', ' ', $stripped_content);
		
		// Force the content to only be at most $numWords long with an ellipse character attached to the end
		$words = explode(' ', $stripped_content, $numWords + 1);
		if(count($words) > $numWords) {
			array_pop($words);
			array_push($words, '…');
			$stripped_content = implode(' ', $words);
		}
		
		if ($returnHtml) {
			return '<p>' . $stripped_content . '</p>';
		} else { return $stripped_content; }
	}
}