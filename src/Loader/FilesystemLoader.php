<?php declare(strict_types=1);

namespace Mustache\Loader;

use Mustache\Contract\Loader;
use Mustache\Exception\RuntimeException;
use Mustache\Exception\UnknownTemplateException;
use Mustache\Source\FilesystemSource;

/**
 * Mustache Template filesystem Loader implementation.
 *
 * A FilesystemLoader instance loads Mustache Template source from the filesystem by name:
 *
 *     $loader = new \Mustache\Contract\Loader\FilesystemLoader(dirname(__FILE__).'/views');
 *     $tpl = $loader->load('foo'); // equivalent to `file_get_contents(dirname(__FILE__).'/views/foo.mustache');
 *
 * This is probably the most useful Mustache Loader implementation. It can be used for partials and normal Templates:
 *
 *     $m = new Mustache(array(
 *          'loader'          => new \Mustache\Contract\Loader\FilesystemLoader(dirname(__FILE__).'/views'),
 *          'partials_loader' => new \Mustache\Contract\Loader\FilesystemLoader(dirname(__FILE__).'/views/partials'),
 *     ));
 */
class FilesystemLoader implements Loader
{
    private false|string $baseDir;
    private string $extension = '.mustache';
    private array $templates = [];

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
     * @param string $baseDir Base directory containing Mustache template files
     * @param array $options Array of Loader options (default: array())
     * @throws RuntimeException if $baseDir does not exist
     *
     */
    public function __construct(string $baseDir, array $options = [])
    {
        $this->baseDir = $baseDir;

        if (!str_contains($this->baseDir, '://')) {
            $this->baseDir = realpath($this->baseDir);
        }

        if ($this->shouldCheckPath() && !is_dir((string)$this->baseDir)) {
            throw new RuntimeException(sprintf('FilesystemLoader baseDir must be a directory: %s', $baseDir));
        }

        if (array_key_exists('extension', $options)) {
            if (empty($options['extension'])) {
                $this->extension = '';
            } else {
                $this->extension = '.' . ltrim($options['extension'], '.');
            }
        }
    }

    /**
     * Load a Template by name.
     *
     *     $loader = new \Mustache\Contract\Loader\FilesystemLoader(dirname(__FILE__).'/views');
     *     $loader->load('admin/dashboard'); // loads "./views/admin/dashboard.mustache";
     *
     * @param string $name
     *
     * @return string|FilesystemSource  Mustache Template source
     */
    public function load($name): string|FilesystemSource
    {
        if (!isset($this->templates[$name])) {
            $this->templates[$name] = $this->loadFile($name);
        }

        return $this->templates[$name];
    }

    /**
     * Helper function for loading a Mustache file by name.
     *
     * @param string $name
     *
     * @return string|FilesystemSource Mustache Template source
     */
    protected function loadFile(string $name): string|FilesystemSource
    {
        $fileName = $this->getFileName($name);

        if ($this->shouldCheckPath() && !file_exists($fileName)) {
            throw new UnknownTemplateException($name);
        }

        $content = file_get_contents($fileName);
        if ($content === false) {
            return '';
        }

        return $content;
    }

    /**
     * Helper function for getting a Mustache template file name.
     *
     * @param string $name
     *
     * @return string Template file name
     */
    protected function getFileName(string $name): string
    {
        $fileName = $this->baseDir . '/' . $name;
        if (substr($fileName, 0 - strlen($this->extension)) !== $this->extension) {
            $fileName .= $this->extension;
        }

        return $fileName;
    }

    /**
     * Only check if baseDir is a directory and requested templates are files if
     * baseDir is using the filesystem stream wrapper.
     *
     * @return bool Whether to check `is_dir` and `file_exists`
     */
    protected function shouldCheckPath(): bool
    {
        $baseDir = (string)$this->baseDir;
        return !str_contains($baseDir, '://') || str_starts_with($baseDir, 'file://');
    }
}
