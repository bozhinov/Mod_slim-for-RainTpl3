<?php

/**
*  SlimTPL 1.0 - a RainTpl fork
*  --------
*  maintained by Momchil Bozhinov (momchil@bojinov.info)
*  ------------
*/

class SlimTpl {

	// variables
	public $vars = [];

	// configuration
	protected $config = [
		'charset' => 'UTF-8',
		'tpl_dir' => 'templates/',
		'cache_dir' => 'cache/',
		'auto_escape' => true,
		'remove_comments' => false
	];

	public function configure($my_conf)
	{
		(!is_array($my_conf)) AND die("Invalid config");

		foreach ($my_conf as $my=>$val){
			if (isset($this->config[$my])){
				$this->config[$my] = $val;
			}
		}
	}

	/**
	* Draw the template
	*
	* @param string $filePath: name of the template file
	* @param bool $toString: if the method should return a string or echo the output
	*
	* @return void, string: depending of the $toString
	*/
	public function draw($filePath, $returnString = FALSE)
	{
		extract($this->vars);
		ob_start();

		// set paths
		$fileName = basename($filePath);
		$filePath = $this->config['tpl_dir'] . $fileName . '.html';
		$filePathCached = $this->config['cache_dir'] . $fileName . ".rtpl.php";
		$fileTime = (int)filemtime($filePath);
		$fileTimeCached = (int)@filemtime($filePathCached);

		// Check if template exists (although there are other reasons for this to be false)
		if ($fileTime == 0) {
			die('Template ' . $fileName . ' not found!');
		}

		// Compile the template if the original has been updated 
		if ($fileTimeCached == 0 || $fileTimeCached < $fileTime) {
			require_once("Parser.php");
			$html = (new Parser($this->config))->compileFile($filePath);
			$html = str_replace("?>\n", "?>\n\n", $html);
			file_put_contents($filePathCached, $html);
		}

		require $filePathCached;
		$output = ob_get_clean();

		if ($returnString){
			return $output;
		} else {
			echo $output;
		}

	}

	/**
	* Assign variable
	* eg.     $t->assign('name','mickey');
	*
	* @param mixed $variable Name of template variable or associative array name/value
	* @param mixed $value value assigned to this variable. Not set if variable_name is an associative array
	*
	*/
	public function assign($variable, $value = null)
	{
		if (is_array($variable)){
			$this->vars = $variable + $this->vars;
		} else {
			$this->vars[$variable] = $value;
		}
	}

	public function assign_my_defines()
	{
		$defines = get_defined_constants(true);
		if (isset($defines['user'])){
			foreach($defines['user'] as $variable => $value){
				$this->vars[$variable] = $value;
			}
		}
	}

}

?>