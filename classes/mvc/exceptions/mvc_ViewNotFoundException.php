<?php
class mvc_ViewNotFoundException extends atsumi_AbstractException { 
	
	private $viewName;
	
	public function __construct($message = null, $viewName, $code = 0) {
		$this->viewName = $viewName;
		parent::__construct($message, $code);
	}
	
	public function getInstructions($contentType) {
		switch($contentType) {
			default:
			case 'text/plain':
				return sf('--Description\nThe view class you specified could not be found. Please make sure you have named the class name the same as the filename and the class folder is loaded using Atsumi\'s load method.\n
--Example basic HTML view\n
	/*Save this as <strong>%s.php</strong> within a folder within classes/. Make sure you load the directory using Atsumi::load(); */
	class %s extends mvc_HtmlView {
		public function renderBodyContent() {
			/* ... put view code here ... */
		}
	}\n', $this->viewName);				
				break;
			case 'text/html':
						
				return sf('<h4>Description</h4><p>The view class you specified could not be found. Please make sure you have named the class name the same as the filename and the class folder is loaded using Atsumi\'s load method.</p>
<h4>Example basic HTML view</h4>
<pre class="code">
/*Save this as <strong>%s.php</strong> within a folder within classes/. Make sure you load the directory using Atsumi::load(); */
class %s extends mvc_HtmlView {
		public function renderBodyContent() {
			/* ... put view code here ... */
		}
}
</pre>', $this->viewName, $this->viewName);
				break;
			
		}
		
	}
	
	
}
?>