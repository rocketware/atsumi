<?php
/* 	EXAMPLE:

	$paginate = new widget_Paginate(
					   array(	"resultsPerPage" => 16,
								"currentPage"	 => $page,
								"totalResults"	 => $this->app->get_memorial->getTotalCandles(),
								"navLength"		 => 6,
								"url"			 => '/candles/[PAGE]/'
							)
						);
 */

class widget_Paginate {

	private $recordCount;
	private $pageCount;
	private $resultsPerPage = 10;
	private $currentPage = 0;
	private $navLength = 5;
	private $url;

	public function __construct($settings = array()) {

		if(array_key_exists('resultsPerPage',$settings))
			$this->setResultsPerPage($settings['resultsPerPage']);
			
		if(array_key_exists('currentPage',$settings))
			$this->setCurrentPage($settings['currentPage']);
			
		if(array_key_exists('totalResults',$settings))
			$this->setTotalResults($settings['totalResults']);
			
		if(array_key_exists('navLength',$settings))
			$this->setNavLength($settings['navLength']);
			
		if(array_key_exists('url',$settings))
			$this->setUrl($settings['url']);
			
	
		
	}
	
	public function getOffset() {
		if(is_null($this->currentPage) || is_null($this->resultsPerPage)) 
				throw new Exception("Not enough values for get offset");
	
		return 	($this->currentPage * $this->resultsPerPage);
	}
	public function getLimit() {
		if(is_null($this->resultsPerPage)) 
				throw new Exception("Not enough values for get limit");
	
		return 	$this->resultsPerPage;
	}

	public function setTotalResults($in) {
		if(!is_integer($in)) throw new Exception("Record Count must be of type Interger");
		$this->recordCount = $in;	
		
		//if got the number of results per page then generate page count
		if(!is_null($this->resultsPerPage))
			$this->setPageCountByRecords($in);	
	}
	
	public function setResultsPerPage($in) {
		if(!is_integer($in)) throw new Exception("Results per page must be of type Interger");
		$this->resultsPerPage = $in;	
		
		//if got the number of results per page then generate page count
		if(!is_null($this->resultsPerPage))
			$this->setPageCountByResultsPerPage($in);		
	}

	public function setCurrentPage($in) {
		if(!is_integer($in)) throw new Exception("Current page must be of type Interger");
		$this->currentPage = $in -1;		
		
	}
	public function setNavLength($in) {
		if(!is_integer($in)) throw new Exception("Nav Length must be of type Interger");
		$this->navLength = $in -1;		
	}
	public function setUrl($in) {
		if(!is_string($in)) throw new Exception("Nav URL must be of type string");
		$this->url = $in;
	}
	private function setPageCountByRecords($in) {
		if(is_null($this->resultsPerPage)) throw new Exception("Need results per page");
		$this->pageCount = ceil($in / $this->resultsPerPage);		
				
	}
	private function setPageCountByResultsPerPage($in) {
		if(is_null($this->resultsPerPage)) throw new Exception("Need results per page");
		$this->pageCount = ceil($this->recordCount / $in);			
	}
	
	private function generateUrl($page) {
		return str_replace("[PAGE]", $page, $this->url);
	}
	public function getPageCount() {
		return $this->pageCount;
	}
	public function getCurrentPage() {
		return $this->currentPage+1;
	}
	public function __toString() {
		if(!$this->pageCount) 
		return "<div class='paginate'><div class='pageItem preText'>page:</div><div class='pageItem currentPage'><strong>1</strong></div></div>";

		$output = "";
		$itemsPerSide 	=(($this->navLength-1) / 2);
		$itemStart 		=($this->currentPage < $itemsPerSide ? 0 : $this->currentPage - $itemsPerSide);
		
		// if we're not showing max items left of currentPage then give space to end
		$spareForEnd 	=($this->currentPage < $itemsPerSide) ? $itemsPerSide - $this->currentPage: 0; 
		
		$itemEnd 		=($this->pageCount-1 <($this->currentPage + $itemsPerSide +$spareForEnd) ? $this->pageCount -1  :($this->currentPage + $itemsPerSide+$spareForEnd));

		// if we're not showing max items right of currentPage then give space to start
		$spareForStart 	=($itemEnd - $this->currentPage < $itemsPerSide) ? $itemsPerSide -($itemEnd - $this->currentPage) : 0;

		// adjust to recaclulate start
		if($spareForStart)	$itemStart 	=($itemStart - $spareForStart <= 0 ? 
												0 : $itemStart - $spareForStart);
			

		// display the leading 1... 
		if($itemStart > 0)
			$output .= sprintf(" <div class='pageItem'>%s</div> <div class='pageJump'>...</div> ", 
								sprintf("<a href='%s'>%s</a>", 
											$this->generateUrl(1),
											1
										)
								);
		
		for($i = $itemStart; $i <= $itemEnd; $i++) {
			$output .= sprintf(" <div class='pageItem%s'>%s</div> ",
											($this->currentPage == $i) ? " currentPage" : '',
											($this->currentPage == $i) ? "<strong>".($i+1)."</strong>" : sprintf("<a href='%s'>%s</a>", $this->generateUrl($i+1),($i+1))
								);
		}
		
		//display the tailing ... $pageCount
		if($itemEnd < $this->pageCount-1)
			$output .= sprintf(" <div class='pageJump'>...</div>  <div class='pageItem'>%s</div> ", 
								sprintf("<a href='%s'>%s</a>", 
											$this->generateUrl($this->pageCount), 
											$this->pageCount
										)
								);

		
		return "<div class='paginate'><div class='pageItem preText'>page:</div>" . $output . "</div>";
	}
}


?>