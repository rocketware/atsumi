<?php
/* 	Example of use (within a controller):

	$paginate = new widget_Paginate(
				   array(	'resultsPerPage' => 16,
							'currentPage'	 => $page,
							'totalResults'	 => $articleCount,
							'navLength'		 => 7,
							'url'			 => '/articles/_[PAGE]/',
							'template'		 => widget_Paginate::TEMPLATE_ARROWS
						)
					);

	Example CSS:

		.pagination { float:right; }
		.pagination .pageItem, .pagination .pageEllipses { padding: 3px; }
		.pagination a { text-decoration: none;  }
		.pagination .currentPage { font-weight:bold; color:red; }
		.pagination .pageArrowDisabled { color:#999; }

 */

class widget_Paginate {

	/* format presets/templates */
	const TEMPLATE_CLASSIC 	= 1;
	const TEMPLATE_ARROWS 	= 2;
	const TEMPLATE_SIMPLE 	= 3;

	/* member variables used for pagination calculations */
	private $recordCount;
	private $pageCount;
	private $resultsPerPage = 10;
	private $currentPage 	= 0;
	private $navLength 		= 4;
	private $url;

	/* formatting options */
	protected $format;
	protected $formatNext;
	protected $formatPrevious;


	/* accepts settings at construct */
	public function __construct($settings = array()) {

		// set template
		if (array_key_exists('template',$settings))	$this->setFormatTemplate($settings['template']);
		else $this->setFormatTemplate();

		// formatting settings
		if (array_key_exists('previous',$settings)) $this->setPrevious($settings['previous']);
		if (array_key_exists('next',$settings)) $this->setNext($settings['next']);
		if (array_key_exists('format',$settings)) $this->setFormat($settings['format']);

		// data to calculate pagination
		if (array_key_exists('resultsPerPage',$settings)) $this->setResultsPerPage($settings['resultsPerPage']);
		if (array_key_exists('currentPage',$settings)) $this->setCurrentPage($settings['currentPage']);
		if (array_key_exists('totalResults',$settings)) $this->setTotalResults($settings['totalResults']);
		if (array_key_exists('navLength',$settings)) $this->setNavLength($settings['navLength']);
		if (array_key_exists('url',$settings)) $this->setUrl($settings['url']);

	}

	/* simply renders the pagination object */
	public function __toString() {
		return $this->render();
	}

	public function generateUrl($page) {
		return str_replace("[PAGE]", $page, $this->url);
	}

	/* returns total page count */
	public function getPageCount() {
		return $this->pageCount;
	}

	/* returns total page count */
	public function getTotalResults() {
		return $this->recordCount;
	}

	/* returns current page */
	public function getCurrentPage() {
		return $this->currentPage;
	}

	/* returns DB offset */
	public function getOffset() {
		if(is_null($this->currentPage) || is_null($this->resultsPerPage))
			throw new Exception("Not enough values for get offset");

		return (($this->currentPage-1) * $this->resultsPerPage);
	}

	/* returns DB limit */
	public function getLimit() {
		if(is_null($this->resultsPerPage))
				throw new Exception("Not enough values for get limit");

		return 	$this->resultsPerPage;
	}
	
	
	public function getResultsPerPage() {
		return 	$this->resultsPerPage;
	}
	

	/* set the format template (default is simple) */
	public function setFormatTemplate ($template = 3) {

		switch ($template) {
			default:
				throw new Exception ('Unknown pagination template');

			case self::TEMPLATE_CLASSIC:
				$this->format 			= '<div class="pagination">Page: [START][START_ELLIPSES][PAGES][END_ELLIPSES][END]</div>';
				$this->formatNext 		= array('', '');
				$this->formatPrevious 	= array('', '');
				break;

				case self::TEMPLATE_ARROWS:
					$this->format 			= '<div class="pagination">[PREVIOUS][START][START_ELLIPSES][PAGES][END_ELLIPSES][END][NEXT]</div>';
					$this->formatNext 		= array('<span class="pageArrow"><a href="[HREF]">&raquo;</a></span>', '<span class="pageArrowDisabled">&raquo;</span>');
					$this->formatPrevious 	= array('<span class="pageArrow"><a href="[HREF]">&laquo;</a></span>', '<span class="pageArrowDisabled">&laquo;</span>');
					break;
					
			case self::TEMPLATE_SIMPLE:
				$this->format 			= '<div class="pagination"><span class="priority-low">Page:</span> [START][START_ELLIPSES][PAGES][END_ELLIPSES][NEXT]</div>';
				$this->formatNext 		= array('<span class="pageArrow"><a href="[HREF]" class="button">Next</a></span>', '');
				break;

		}

	}

	/* set previous option */
	public function setPrevious ($in) {
		if (!is_array($in)) throw new Exception('Previous expected type array ($active, $inactive)');
		$this->formatPrevious = $in;
	}

	/* set next option */
	public function setNext ($in) {
		if (!is_array($in)) throw new Exception('Next expected type array ($active, $inactive)');
		$this->formatNext = $in;
	}

	/* set total results */
	public function setTotalResults($in) {
		if(!is_integer($in)) throw new Exception("Record Count must be of type Interger");
		$this->recordCount = $in;

		//if got the number of results per page then generate page count
		if(!is_null($this->resultsPerPage))	$this->setPageCountByRecords($in);
	}

	public function setResultsPerPage($in) {
		if(!is_integer($in)) throw new Exception("Results per page must be of type Interger");
		$this->resultsPerPage = $in;

		//if got the number of results per page then generate page count
		if(!is_null($this->resultsPerPage))
			$this->setPageCountByResultsPerPage($in);
	}

	/* sets the current page (usually passed as a controller's parameter */
	public function setCurrentPage($in) {
		if(!is_integer($in)) throw new Exception("Current page must be of type Interger");
		$this->currentPage = $in;

	}

	/* set the required length of the nav, if this isn't odd it will +1 later (keeps current page in middle) */
	public function setNavLength($in) {
		if(!is_integer($in)) throw new Exception("Nav Length must be of type Interger");
		$this->navLength = $in;
	}

	/* set the URL that the pagination will use for the links */
	public function setUrl($in) {
		if(!is_string($in)) throw new Exception("Nav URL must be of type string");
		$this->url = $in;
	}

	/* set the format string for the pagination */
	public function setFormat($in) { $this->format = $in; }

	/* calculates the page count by number of records and results per page */
	private function setPageCountByRecords($in) {
		if(is_null($this->resultsPerPage)) throw new Exception("Need results per page");
		$this->pageCount = ceil($in / $this->resultsPerPage);
	}

	/* calculates the page count by the number of results and the record count */
	private function setPageCountByResultsPerPage($in) {
		if(is_null($this->resultsPerPage)) throw new Exception("Need results per page");
		$this->pageCount = ceil($this->recordCount / $in);
	}

	/* returns a html page link for inclusion in the pagination output */
	public function renderPageLink ($page) {

		return sf("<span class='pageItem%s'><a href='%s' class='button%s'>%s</a></span>",
					($this->currentPage == $page) ? " currentPage" : '',
					$this->generateUrl($page),
					($this->currentPage == $page) ? " on":'',
					number_format($page)
				);

	}

	/* renders the pagination widget */
	public function render () {

		preg_match_all('/(\[[A-Z\_]+\])/',$this->format, $options, PREG_PATTERN_ORDER);

		$options = array_flip($options[0]);

		// params: Start & End
		$start = $this->renderPageLink(1);
		$end = $this->getPageCount() < 2?'':$this->renderPageLink($this->getPageCount());

		
		// params: Pages links
		$pageLength = $this->navLength;
		if ($pageLength&1 ||  
			(array_key_exists('[START]', $options) && !array_key_exists('[END]', $options)) || 
			(!array_key_exists('[START]', $options) && array_key_exists('[END]', $options))
		) { } 
		
		else $pageLength++;

		if (array_key_exists('[START]', $options)) $pageLength--;
		if (array_key_exists('[END]', $options)) $pageLength--;
		$pageLinkStart = $this->currentPage - floor($pageLength/2);

		if ($pageLinkStart < 1) $pageLinkStart = 1;
		if (array_key_exists('[START]', $options) && $pageLinkStart == 1) $pageLinkStart = 2;

		$pageLinkEnd = $pageLinkStart + $pageLength - 1;
		if ($pageLinkEnd > $this->pageCount) $pageLinkEnd = $this->pageCount;

		// if we have the end param and we're on the last page get set pages -1
		if (array_key_exists('[END]', $options) && $pageLinkEnd == $this->pageCount) $pageLinkEnd--;

		// adjust start positino if we're near the end of the pagination
		if (($pageLinkEnd - $this->currentPage) < floor($pageLength/2))
			$pageLinkStart  = $pageLinkStart - (floor($pageLength/2) - ($pageLinkEnd - $this->currentPage));
		if ($pageLinkStart < 1) $pageLinkStart = 1;
		if (array_key_exists('[START]', $options) && $pageLinkStart == 1) $pageLinkStart = 2;

		
		$pages = '';
		if ($pageLinkStart <= $pageLinkEnd)
			for ($i = $pageLinkStart; $i <= $pageLinkEnd; $i++)
				$pages .= $this->renderPageLink($i);

		// params: start ellipses
		if (array_key_exists('[START_ELLIPSES]', $options) &&
			$pageLinkStart > 2) $startEllipses = '<span class="pageEllipses">...</span>';
			else $startEllipses = '';

		// params: end ellipses
		if (array_key_exists('[END_ELLIPSES]', $options) &&
			$pageLinkEnd < ($this->pageCount-1)) $endEllipses = '<span class="pageEllipses">...</span>';
			else $endEllipses = '';

		// params: next link
		if ($this->currentPage >= $this->pageCount) $next = $this->formatNext[1];
		else $next = str_replace('[HREF]',  $this->generateUrl($this->currentPage+1), $this->formatNext[0]);

		// params: previous link
		if ($this->currentPage == 1) $previous = $this->formatPrevious[1];
		else $previous = str_replace('[HREF]',  $this->generateUrl($this->currentPage-1), $this->formatPrevious[0]);

		// create final output
		$out = str_replace(
			array('[START]','[END]','[PAGES]','[START_ELLIPSES]', '[END_ELLIPSES]', '[PREVIOUS]', '[NEXT]'),
			array($start, $end, $pages, $startEllipses, $endEllipses, $previous, $next), $this->format);

		return $out;

	}

}


?>