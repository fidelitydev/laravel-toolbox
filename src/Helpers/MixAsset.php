<?php

namespace Knighttower\Toolbox\Helpers;

class MixAsset
{
    protected $manifest;
    protected $manifestPath;

    /**
     * Constructor method to initialize the manifest path.
     *
     * @param string|null $manifestPath
     */
    public function __construct(string|null $manifestPath = null)
    {
        if (!empty($manifestPath)) {
            $this->manifestPath = public_path($manifestPath);
            $this->loadManifest();
        }
    }

    /**
     * Set a custom path for the manifest file.
     *
     * @param string $path
     * @return $this
     */
    public function setManifestPath(string $path)
    {
        $this->manifestPath = public_path($path);
        $this->loadManifest();
        return $this;
    }

    /**
     * Load the manifest file contents.
     *
     * @return void
     * @throws \Exception
     */
    protected function loadManifest()
    {
        if (file_exists($this->manifestPath)) {
            $this->manifest = json_decode(file_get_contents($this->manifestPath), true);
        } else {
            throw new \Exception("Manifest file not found at: {$this->manifestPath}");
        }
    }

    /**
     * Get the asset URL from the manifest file.
     *
     * @param string $path
     * @return string|null
     */
    public function asset($path)
    {
        return !empty($this->manifest[$path]) ? asset($this->manifest[$path]) : null;
    }
}
