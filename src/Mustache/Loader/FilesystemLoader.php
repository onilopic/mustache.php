<?php

namespace Mustache\Loader;

use Mustache\Loader;

/**
 * Mustache Template filesystem Loader implementation.
 *
 * An ArrayLoader instance loads Mustache Template source from the filesystem by name:
 *
 *     $loader = new FilesystemLoader(__DIR__.'/views');
 *     $tpl = $loader->load('foo'); // equivalent to `file_get_contents(__DIR__.'/views/foo.mustache');
 *
 * This is probably the most useful Mustache Loader implementation. It can be used for partials and normal Templates:
 *
 *     $m = new Mustache(array(
 *          'loader'          => new FilesystemLoader(__DIR__.'/views'),
 *          'partials_loader' => new FilesystemLoader(__DIR__.'/views/partials'),
 *     ));
 *
 * @implements Loader
 */
class FilesystemLoader implements Loader {
	private $baseDir;
	private $extension = '.mustache';
	private $templates = array();

	/**
	 * Mustache filesystem Loader constructor.
	 *
	 * Passing an $options array allows overriding certain Loader options during instantiation:
	 *
	 *     $options = array(
	 *         // The filename extension used for Mustache templates. Defaults to '.mustache'
	 *         'extension' => '.ms',
	 *     );
	 *
	 * @throws \RuntimeException if $baseDir does not exist.
	 *
	 * @param string $baseDir Base directory containing Mustache template files.
	 * @param array  $options Array of Loader options (default: array())
	 */
	public function __construct($baseDir, array $options = array()) {
		$this->baseDir = rtrim(realpath($baseDir), '/');

		if (!is_dir($this->baseDir)) {
			throw new \RuntimeException('FilesystemLoader baseDir must be a directory: '.$baseDir);
		}

		if (isset($options['extension'])) {
			$this->extension = '.' . ltrim($options['extension'], '.');
		}
	}

	/**
	 * Load a Template by name.
	 *
	 *     $loader = new FilesystemLoader(__DIR__.'/views');
	 *     $loader->load('admin/dashboard'); // loads "./views/admin/dashboard.mustache";
	 *
	 * @param  string $name
	 *
	 * @return string Mustache Template source
	 */
	public function load($name) {
		if (!isset($this->templates[$name])) {
			$this->templates[$name] = $this->loadFile($name);
		}

		return $this->templates[$name];
	}

	/**
	 * Helper function for loading a Mustache file by name.
	 *
	 * @throws \InvalidArgumentException if a template file is not found.
	 *
	 * @param string $name
	 *
	 * @return string Mustache Template source
	 */
	private function loadFile($name) {
		$fileName = $this->getFileName($name);

		if (!file_exists($fileName)) {
			throw new \InvalidArgumentException('Template '.$name.' not found.');
		}

		return file_get_contents($fileName);
	}

	/**
	 * Helper function for getting a Mustache template file name.
	 *
	 * @param string $name
	 *
	 * @return string Template file name
	 */
	private function getFileName($name) {
		$fileName = $this->baseDir . '/' . $name;
		if (substr($fileName, 0 - strlen($this->extension)) !== $this->extension) {
			$fileName .= $this->extension;
		}

		return $fileName;
	}
}