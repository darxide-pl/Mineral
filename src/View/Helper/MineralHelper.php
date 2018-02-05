<?php 

namespace Mineral\View\Helper;
use Cake\View\Helper;

class MineralHelper extends Helper
{
	/**
	 *	If true: ALL of content will be automatically minified on cake's `afterLayout()` event 
	 *	To cancel automatic minifying, use `$this->Mineral->disable()` method
	 *	Why we should have option to stop minifying? 
	 *		1) If we wanna stop minifying in one precisely targeted page
	 *		2) Its pretty useful, if we wanna minify only one or bunch of cake's elements. 
	 *			<?= $this->Mineral->minify($this->Element('footer')) ?>
	 *			<?= $this->Mineral->process($this->Element('menu') , [
	 *				'css' => TRUE
	 *			]) ?>
	 */
	protected $autoMinifying = TRUE;

	/**
	 *	Array of default options
	 */
	protected $options = [
		# removes <div style="color:red"></div> - I think this is hardcore option - bearing in mind ckeditor, practical only if every template was made by devs 
		'css' => FALSE,
		# removes <style></style> from output
		'style' => FALSE,
		# removes inline scripts content only (not <script src="/dunno.js"></script>), eg <script>alert('hello')</script> will be replaced with <script></script>
		'script' => FALSE, 
		#callback before any minifying
		'beforePruning' => FALSE,
		# callback after all minifying operations
		'afterPruning' => FALSE
	]; 

	/**
	 *	Available mime types
	 */
	protected $mimes = [
        'text/html' => 1,
        'text/xhtml' => 1,
	];

	/**
	 *	@return void
	 */
	public function initialize (array $config = []) {
		
		$this->options = $config += $this->options;
	}

	/**
	 *	Content processing
	 *	@param string $content 
	 *	@param array $config - array of options
	 */
	public function process (string $content, array $config = []) {

		$config += $this->options;
        if(is_callable($config['beforePruning'])) {
	        $content = $this->beforePruning($content, $config['beforePruning']);
	    }

        $content = $this->minify($content);

        if($config['css']) {
	        $content = $this->inlineCssPruning($content);
	    } 

	    if($config['style']) {
		    $content = $this->inlineStylePruning($content);
	    }

	    if($config['script']) {
	        $content = $this->inlineScriptPruning($content);
	    }

        if(is_callable($config['afterPruning'])) {
	        $content = $this->afterPruning($content, $config['afterPruning']);
	    }

        return $content;
	}

	/**
	 *	Take control over the output using cake's event
	 *	@return void
	 */
	public function afterLayout () {

		if(!$this->autoMinifying) {
			return;
		}

        $content = $this->_View->Blocks->get('content');
        $content = $this->process($content, $this->options);
        $this->_View->Blocks->set('content', $content);	
	}

	/**
	 *	Basic minification process, without cutting out any additional content
	 *	@param string $content
	 *	@return string	 
	 */
	public function minify (string $content) :string {

	    $search = [
	        '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
	        '/[^\S ]+\</s',     // strip whitespaces before tags, except space
	        '/(\s)+/s',         // shorten multiple whitespace sequences
	        '/<!--(.|\s)*?-->/' // Remove HTML comments
	    ];

	    $replace = [
	        '>',
	        '<',
	        '\\1',
	        ''
	    ];

	    return preg_replace($search, $replace, $content);
	}

	/**
	 * 	Appropiate method override default minifying settings declared in AppView for precisely aimed templates by writing $this->Mineral->process($config) in your template/helper
	 *	@example in your helper or in your template/element: `$this->Mineral->override($config)`
	 *	@return void
	 */
	public function override (array $config = []) {

		$this->options = $config += $this->options;
	}	

	/**
	 *	Stop automatically minifying content on cake's `afterLayout()` event
	 *	@return bool
	 */
	public function disable () :bool {
		
		$this->autoMinifying = FALSE;
		return TRUE;
	}

	/**
	 *	Enable automatically minifying content on cake's `afterLayout()` event
	 *	@return bool
	 */
	public function enable () :bool {
		
		$this->autoMinifying = TRUE;
		return TRUE;
	}

	/**
	 *	Remove inline css: <div style="color:red"></div>
	 */
	public function inlineCssPruning (string $content) :string {

    	return preg_replace('/style=".*?"/', '', $content);
	}

	/**
	 *	Cutting out <style></style> tags with content
	 *	@param string content 
	 *	@return string
	 */
	public function inlineStylePruning (string $content) :string {

    	return preg_replace('/<style.*?style>/is', '', $content);
	}

	/**
	 *	Cutting out only content
	 */
	public function inlineScriptPruning (string $content) :string {

    	return preg_replace('/(<script[^>]*>)(.*?)(<\/script>)/is', '$1$3', $content);
        return $content;
	}

	/**
	 *	Event before output puryfying
	 *	@param string $content	 
	 *	@param callable user function which takese control over content
	 *	@return string
	 */
	protected function beforePruning (string $content, callable $func) :string {

		return $func($content);
	}

	/**
	 *	"Event" after output puryfying
	 *	@param string $content
	 *	@param callable user function which takese control over content
	 *	@return string
	 */
	protected function afterPruning (string $content, callable $func) :string {

    	return $func($content);
	}
}