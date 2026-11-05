<?php

namespace SAHamid\BI\Directors;

/**
 * Small file cache for authorized dashboard results.
 *
 * Opt-in: only the web endpoint passes one to DirectorsService, so tests and tools always run the
 * real queries. Keys are built by the service and include the database, the user, the user's
 * permissions and the request, so one user's result can never be served to another. Every failure
 * (unwritable directory, bad file) is silent and simply means "no cache".
 */
class ResultCache
{
    private $directory;

    public function __construct($directory = null)
    {
        $this->directory = $directory !== null ? $directory : rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'sahamid-directors-cache';
    }

    /** @return array|null array('data' => mixed, 'age' => seconds) when fresh, else null. */
    public function get($key, $ttlSeconds)
    {
        $file = $this->path($key);
        $modified = @filemtime($file);
        if ($modified === false || time() - $modified > $ttlSeconds) { return null; }
        $raw = @file_get_contents($file);
        $data = $raw === false ? null : json_decode($raw, true);
        return is_array($data) && array_key_exists('d', $data) ? array('data' => $data['d'], 'age' => time() - $modified) : null;
    }

    public function put($key, $data)
    {
        if (!is_dir($this->directory) && !@mkdir($this->directory, 0700, true) && !is_dir($this->directory)) { return; }
        $json = json_encode(array('d' => $data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json === false) { return; }
        $temporary = @tempnam($this->directory, 'tmp');
        if ($temporary === false) { return; }
        if (@file_put_contents($temporary, $json) === false || !@rename($temporary, $this->path($key))) { @unlink($temporary); return; }
        if (mt_rand(1, 40) === 1) { $this->sweep(); }
    }

    private function path($key)
    {
        return $this->directory . DIRECTORY_SEPARATOR . preg_replace('/[^a-f0-9]/', '', $key) . '.json';
    }

    /** Remove files older than an hour. */
    private function sweep()
    {
        foreach ((array) @glob($this->directory . DIRECTORY_SEPARATOR . '*.json') as $file) {
            if (time() - (int) @filemtime($file) > 3600) { @unlink($file); }
        }
    }
}
