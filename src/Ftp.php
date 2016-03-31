<?php

namespace Rnr\Ftp;

use Curl\Curl;
use RuntimeException;

/**
 * @author Sergei Melnikov <me@rnr.name>
 */
class Ftp
{
    /** @var Curl */
    private $curl;
    private $login;
    private $password;
    private $host;
    private $port = 21;
    private $directory = '/';
    private $ssl = false;
    private $passive = true;

    public function __construct() {
        $this->curl = $this->createCurl();
    }
    /**
     * @return mixed
     */
    public function getCurl()
    {
        return $this->curl;
    }

    protected function createCurl()
    {
        return new Curl();
    }

    /**
     * @return mixed
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param mixed $login
     * @return Ftp
     */
    public function setLogin($login)
    {
        $this->login = $login;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     * @return Ftp
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param mixed $host
     * @return Ftp
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $port
     * @return Ftp
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @param string $directory
     * @return Ftp
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isSsl()
    {
        return $this->ssl;
    }

    /**
     * @param boolean $ssl
     * @return Ftp
     */
    public function setSsl($ssl)
    {
        $this->ssl = $ssl;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isPassive()
    {
        return $this->passive;
    }

    /**
     * @param boolean $passive
     * @return Ftp
     */
    public function setPassive($passive)
    {
        $this->passive = $passive;
        return $this;
    }

    public function flist($directory) {
        $this->connect();

        $url = $this->url($directory);

        $url = rtrim($url, '/') . '/';

        $this->curl->setOpt(CURLOPT_URL, $url);

        $this->exec();

        $files = array_map(function ($line) {
            $matches = [];

            if (!preg_match('/^([^\s]+)\s+([^\s]+)\s+([^\s]+)\s+([^\s]+)\s+([^\s]+)\s+' .
                '(.{3} \d{2} \d{2}:\d{2})\s+(.+)/', $line, $matches
            )) {
                throw new RuntimeException('Cannot parse data.');
            }

            $info = new FileInfo();

            $info->setName($matches[7])
                ->setDate($matches[6])
                ->setType(($matches[1][1] == 'd') ?
                    (FileInfo::DIRECTORY) :
                    (FileInfo::FILE)
                );

            return $info;
        }, array_filter(explode("\n", $this->curl->response)));

        return $files;
    }

    public function get($file) {
        $this->connect();

        $url = $this->url($file);

        $this->curl->setOpt(CURLOPT_URL, $url);

        $this->exec();

        return $this->curl->response;
    }

    public function put($file, $stream, $size) {
        $this->connect();

        $url = $this->url($file);

        $this->setOptions([
            CURLOPT_URL => $url,
            CURLOPT_UPLOAD => true,
            CURLOPT_INFILE => $stream,
            CURLOPT_INFILESIZE => $size
        ]);

        $this->exec();

        return $this->curl->response;
    }

    public function delete($file) {
        $url = $this->url();

        $this->setOptions([
            CURLOPT_URL => $url,
            CURLOPT_QUOTE => ["DELE {$file}"]
        ]);

        $this->exec();

        return $this->curl->response;
    }
    
    protected function exec() {
        $this->curl->_exec();
        
        if ($this->curl->error) {
            throw new FtpException($this->curl->error_message, $this->curl->error_code);
        }
    }

    public function connect() {
        $this->curl = $this->createCurl();

        $credentials = '';

        if (!empty($this->login)) {
            $credentials = $this->login;

            if (!empty($this->password)) {
                $credentials = "{$credentials}:{$this->password}";
            }
        }

        $this->setOptions([
            CURLOPT_USERPWD => $credentials,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
    }

    protected function setOptions($options) {
        foreach ($options as $option => $value) {
            $this->curl->setOpt($option, $value);
        }
    }

    protected function url($url = '') {
        $protocol = ($this->ssl) ? ("ftps") : ("ftp");
        return "{$protocol}://{$this->host}{$url}";
    }
}